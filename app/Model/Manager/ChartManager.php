<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\DTO\ChartDataDTO;
use App\Model\DTO\PostDTO;
use App\Model\DTO\CommentDTO;

class ChartManager
{
    /**
     * @param PostDTO[] $posts
     * @param CommentDTO[] $comments
     */
    public function generateStatistics(array $posts, array $comments): ChartDataDTO
    {
        $timeData = [];

        foreach ($posts as $post) {
            if ($post->createdAt) $timeData[$post->createdAt->format('Y-m-d')] = ['posts' => 0, 'comments' => 0];
        }
        foreach ($comments as $comment) {
            if ($comment->createdAt) $timeData[$comment->createdAt->format('Y-m-d')] = ['posts' => 0, 'comments' => 0];
        }

        if (empty($timeData)) {
            return new ChartDataDTO([], [], []);
        }

        $keys = array_keys($timeData);
        $currentDate = new \DateTime(min($keys));
        $maxDate = new \DateTime(max($keys));

        while ($currentDate <= $maxDate) {
            $timeData[$currentDate->format('Y-m-d')] ??= ['posts' => 0, 'comments' => 0];
            $currentDate->modify('+1 day');
        }

        foreach ($posts as $post) {
            if ($post->createdAt) $timeData[$post->createdAt->format('Y-m-d')]['posts']++;
        }
        foreach ($comments as $comment) {
            if ($comment->createdAt) $timeData[$comment->createdAt->format('Y-m-d')]['comments']++;
        }

        ksort($timeData);

        $labels = []; $pData = []; $cData = [];
        foreach ($timeData as $dateKey => $counts) {
            $dateObj = \DateTime::createFromFormat('Y-m-d', $dateKey);
            if ($dateObj) {
                $labels[] = $dateObj->format('j. n. Y');
                $pData[] = $counts['posts'];
                $cData[] = $counts['comments'];
            }
        }

        return new ChartDataDTO($labels, $pData, $cData);
    }
}