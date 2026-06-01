<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Http\FileUpload;

class ImageManager
{
    private string $storageDir;

    public function __construct(string $storageDir)
    {
        $this->storageDir = $storageDir;
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function saveFromUpload(FileUpload $file): string
    {
        $extension = pathinfo($file->getSanitizedName(), PATHINFO_EXTENSION);
        $fileName = 'img_' . uniqid() . '.' . $extension;
        $file->move($this->storageDir . '/' . $fileName);
        return $fileName;
    }

    public function saveFromUrl(string $url): string
    {
        $context = stream_context_create(['http' => ['header' => "User-Agent: Mozilla/5.0\r\n"]]);
        $imageContent = @file_get_contents($url, false, $context);

        if ($imageContent === false) {
            throw new \Exception('Obrázek se nepodařilo stáhnout.');
        }

        $extension = pathinfo((string)parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $fileName = 'img_' . uniqid() . '.' . $extension;

        file_put_contents($this->storageDir . '/' . $fileName, $imageContent);
        return $fileName;
    }
}