<?php
declare(strict_types=1);

namespace App\Model\DTO;

class OrderDTO{
    public function __construct(
        public string $orderNumber,
        public string $item,
        public float $price,
        public int $durationMonths,
        public int $userId,
        public ?\DateTimeImmutable $createdAt = null,
        public ?int $id = null
    ){}
}