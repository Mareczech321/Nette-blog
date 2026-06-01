<?php
declare(strict_types=1);

namespace App\Model\Service;

class EncryptionService
{
    private const ALGORITHM = 'aes-256-gcm';
    private const TAG_LENGTH = 16;
    private const IV_LENGTH = 12;

    /**
     * Encrypt a message using AES-256-GCM
     *
     * @param string $plaintext The message to encrypt
     * @param string $key The encryption key (should be 32 bytes for AES-256)
     * @return array{encrypted: string, iv: string, tag: string} Encrypted data with IV and authentication tag
     */
    public function encrypt(string $plaintext, string $key): array
    {
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        $encrypted = openssl_encrypt(
            $plaintext,
            self::ALGORITHM,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return [
            'encrypted' => base64_encode($encrypted),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
        ];
    }

    /**
     * Decrypt a message using AES-256-GCM
     *
     * @param string $encrypted Base64-encoded encrypted data
     * @param string $iv Base64-encoded initialization vector
     * @param string $tag Base64-encoded authentication tag
     * @param string $key The decryption key (should be 32 bytes for AES-256)
     * @return string The decrypted plaintext
     */
    public function decrypt(string $encrypted, string $iv, string $tag, string $key): string
    {
        $ciphertext = base64_decode($encrypted, true);
        $decodedIv = base64_decode($iv, true);
        $decodedTag = base64_decode($tag, true);

        if ($ciphertext === false || $decodedIv === false || $decodedTag === false) {
            throw new \RuntimeException('Failed to decode encryption data');
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::ALGORITHM,
            $key,
            OPENSSL_RAW_DATA,
            $decodedIv,
            $decodedTag
        );

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed - authentication tag verification failed or invalid key');
        }

        return $plaintext;
    }

    /**
     * Generate a conversation-specific encryption key from two user IDs
     * This ensures each conversation pair has a unique encryption key
     *
     * @param int $userId1 First user ID
     * @param int $userId2 Second user ID
     * @param string $appSecret Shared application secret (from config)
     * @return string 32-byte AES-256 key
     */
    public function generateConversationKey(int $userId1, int $userId2, string $appSecret): string
    {
        $sortedIds = $userId1 < $userId2 ? "$userId1:$userId2" : "$userId2:$userId1";
        $keyMaterial = hash_hmac('sha256', $sortedIds, $appSecret, true);

        return hash('sha256', $keyMaterial, true);
    }

    /**
     * Get AES key length in bytes
     */
    public static function getKeyLength(): int
    {
        return 32;
    }

    /**
     * Get IV length in bytes
     */
    public static function getIvLength(): int
    {
        return self::IV_LENGTH;
    }
}
