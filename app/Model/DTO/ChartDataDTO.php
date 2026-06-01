<?php
declare(strict_types=1);

namespace App\Model\DTO;

class ChartDataDTO
{
    /**
     * @param string[] $labels
     * @param int[] $postsData
     * @param int[] $commentsData
     */
    public function __construct(
        public array $labels,
        public array $postsData,
        public array $commentsData
    ) {}
}