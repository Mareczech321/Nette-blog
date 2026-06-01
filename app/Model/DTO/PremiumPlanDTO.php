<?php
declare(strict_types=1);

namespace App\Model\DTO;

class PremiumPlanDTO
{
    /**
     * @param string[] $features
     */
    public function __construct(
        public string $code,
        public string $name,
        public int $price,
        public string $duration,
        public string $info,
        public array $features
    ) {}
}