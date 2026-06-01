<?php

namespace App\Model;

use App\Model\Repository\PostReactionRepository;
use App\Model\Repository\CommentReactionRepository;
use App\Model\DTO\ReactionDTO;

class ReactionFasade {
    private const EMOJI_MAP = [
        '😀' => 'grinning_face',
        '😁' => 'beaming_face_with_smiling_eyes',
        '😂' => 'joy',
        '😃' => 'smiley',
        '😄' => 'smile',
        '😅' => 'sweat_smile',
        '😆' => 'grinning_face_with_sweat',
        '😉' => 'winking_face',
        '😊' => 'smiling_face_with_smiling_eyes',
        '😋' => 'face_savoring_food',
        '😎' => 'smiling_face_with_sunglasses',
        '😍' => 'smiling_face_with_heart_eyes',
        '😘' => 'face_blowing_a_kiss',
        '😗' => 'kissing_face',
        '😚' => 'kissing_face_with_closed_eyes',
        '😙' => 'kissing_face_with_smiling_eyes',
        '😜' => 'winking_face_with_tongue',
        '😝' => 'squinting_face_with_tongue',
        '😛' => 'face_with_tongue',
        '😳' => 'flushed_face',
        '😌' => 'relieved_face',
        '😔' => 'pensive_face',
        '😓' => 'downcast_face_with_sweat',
        '😪' => 'sleepy_face',
        '😒' => 'unamused_face',
        '😡' => 'pouting_face',
        '😠' => 'angry_face',
        '😲' => 'astonished_face',
        '😨' => 'fearful_face',
        '😰' => 'anxious_face_with_sweat',
        '😧' => 'anguished_face',
        '😢' => 'crying_face',
        '😮' => 'face_with_open_mouth',
        '😬' => 'grimacing_face',
        '😱' => 'face_screaming_in_fear',
        '😞' => 'disappointed_face',
        '😵' => 'dizzy_face',
        '😷' => 'face_with_medical_mask',
        '🤐' => 'zipper_mouth_face',
        '🤑' => 'money_mouth_face',
        '🤒' => 'face_with_thermometer',
        '🤓' => 'nerd_face',
        '🤩' => 'star_struck',
        '🤨' => 'face_with_raised_eyebrow',
        '😐' => 'neutral_face',
        '😑' => 'expressionless_face',
        '😶' => 'face_with_mouth_covered',
        '🙂' => 'slightly_smiling_face',
        '🙁' => 'slightly_frowning_face',
        '☺️' => 'white_smiling_face',
        '😏' => 'smirking_face',
        '😣' => 'persevering_face',
        '😥' => 'sad_but_relieved_face',
        '😫' => 'tired_face',
        '😤' => 'face_with_steam_from_nose',
        '😖' => 'confounded_face',
        '😕' => 'confused_face',
        '🤔' => 'thinking_face',
        '💀' => 'skull',
        '👍' => 'thumbsup',
        '👎' => 'thumbsdown',
        '✌️' => 'victory_hand',
        '🖖' => 'vulcan_salute',
        '✋' => 'raised_hand',
        '🖐️' => 'hand_with_fingers_splayed',
        '👌' => 'ok_hand',
        '👏' => 'clapping_hands',
        '🙌' => 'raising_hands',
        '👐' => 'open_hands',
        '🤲' => 'open_palms',
        '🤝' => 'handshake',
        '🙏' => 'folded_hands',
        '💅' => 'nail_polish',
        '💪' => 'flexed_biceps',
        '🦵' => 'leg',
        '🦶' => 'foot',
        '👂' => 'ear',
        '👃' => 'nose',
        '🧠' => 'brain',
        '🦷' => 'tooth',
        '🦴' => 'bone',
        '❤️' => 'heart',
        '💛' => 'yellow_heart',
        '💚' => 'green_heart',
        '💙' => 'blue_heart',
        '💜' => 'purple_heart',
        '🖤' => 'black_heart',
        '🤍' => 'white_heart',
        '🤎' => 'brown_heart',
        '🧡' => 'orange_heart',
        '💔' => 'broken_heart',
        '💕' => 'two_hearts',
        '💞' => 'revolving_hearts',
        '💓' => 'beating_heart',
        '💗' => 'growing_heart',
        '💖' => 'sparkling_heart',
        '💘' => 'cupid',
        '💝' => 'heart_with_ribbon',
        '💟' => 'heart_decoration',
        '💌' => 'love_letter',
        '💋' => 'kiss_mark',
        '💯' => 'hundred_points',
        '💢' => 'anger_symbol',
        '💥' => 'collision',
        '💫' => 'dizzy',
        '💦' => 'water_droplets',
        '💨' => 'dashing_away',
        '🕳️' => 'hole',
        '💬' => 'speech_balloon',
        '🗨️' => 'speech_balloon',
        '🗯️' => 'right_anger_bubble',
        '💭' => 'thought_balloon',
        '💤' => 'zzz',
        '🔥' => 'fire',
        '🎉' => 'party_popper',
        '🎊' => 'confetti_ball',
        '🎈' => 'balloon',
        '🎀' => 'ribbon',
        '🎁' => 'gift',
        '🎂' => 'birthday_cake',
        '🎆' => 'fireworks',
        '🎇' => 'sparkler',
        '🎃' => 'jack_o_lantern',
        '🎄' => 'christmas_tree',
        '⭐' => 'star',
        '🌟' => 'glowing_star',
        '✨' => 'sparkles',
    ];

    public function __construct(
        private PostReactionRepository $postReactionRepository,
        private CommentReactionRepository $commentReactionRepository
    ) {}

    private function emojiToString(string $emoji): string {
        return self::EMOJI_MAP[$emoji] ?? $emoji;
    }

    private function stringToEmoji(string $emojiString): string {
        $reverseMap = array_flip(self::EMOJI_MAP);
        return $reverseMap[$emojiString] ?? $emojiString;
    }

    public function addCommentEmojiReaction(int $commentId, int $userId, string $emoji): void {
        $emojiString = $this->emojiToString($emoji);
        $sameEmoji = $this->commentReactionRepository->findByUserAndEmoji($commentId, $userId, $emojiString);
        if ($sameEmoji) {
            $sameEmoji->delete();
            return;
        }

        $dto = new ReactionDTO(0, $userId, $emojiString, null, $commentId);
        $this->commentReactionRepository->save(null, $dto);
    }

    public function addPostEmojiReaction(int $postId, int $userId, string $emoji): void {
        $emojiString = $this->emojiToString($emoji);
        
        $sameEmoji = $this->postReactionRepository->findByUserAndEmoji($postId, $userId, $emojiString);
        
        if ($sameEmoji) {
            $sameEmoji->delete();
            return;
        }

        $dto = new ReactionDTO(0, $userId, $emojiString, $postId, null);
        $this->postReactionRepository->save(null, $dto);
    }

    /**
     * @return array<\Nette\Database\IRow>
     */
    public function getCommentReactions(int $commentId): array {
        return $this->commentReactionRepository->getCounts($commentId);
    }

    /**
     * @return array<\Nette\Database\IRow>
     */
    public function getPostReactions(int $post): array {
        return $this->postReactionRepository->getCounts($post);
    }

    /**
     * @return array<\Nette\Database\IRow>
     */
    public function getPostEmojiCounts(int $postId): array
    {
        $counts = $this->postReactionRepository->getCounts($postId);
        foreach ($counts as $count) {
            $count->emoji = $this->stringToEmoji($count->emoji);
        }
        return $counts;
    }

    /**
     * @return array<\Nette\Database\IRow>
     */
    public function getCommentEmojiCounts(int $commentId): array
    {
        $counts = $this->commentReactionRepository->getCounts($commentId);
        foreach ($counts as $count) {
            $count->emoji = $this->stringToEmoji($count->emoji);
        }
        return $counts;
    }

    public function hasUserReactedToPost(int $postId, int $userId, string $emoji): bool
    {
        $emojiString = $this->emojiToString($emoji);
        return $this->postReactionRepository->findByUserAndEmoji($postId, $userId, $emojiString) !== null;
    }

    public function hasUserReactedToComment(int $commentId, int $userId, string $emoji): bool
    {
        $emojiString = $this->emojiToString($emoji);
        return $this->commentReactionRepository->findByUserAndEmoji($commentId, $userId, $emojiString) !== null;
    }
}


