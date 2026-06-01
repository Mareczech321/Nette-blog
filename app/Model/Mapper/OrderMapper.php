<?php
declare(strict_types=1);

namespace App\Model\Mapper;

use App\Model\DTO\OrderDTO;
use Nette\Database\Table\ActiveRow;
use App\Model\DTO\PremiumPlanDTO;

class OrderMapper
{
    public function __construct() {}

    public function map(ActiveRow $row): OrderDTO
    {
        $createdAt = $row->created_at instanceof \DateTimeInterface
            ? \DateTimeImmutable::createFromInterface($row->created_at)
            : null;

        $months = is_scalar($row->duration_months) ? (int) $row->duration_months : 1;

        return new OrderDTO(
            orderNumber: is_scalar($row->order_number) ? (string) $row->order_number : '',
            item: is_scalar($row->item) ? (string) $row->item : '',
            price: is_scalar($row->price) ? (float) $row->price : 0.0,
            durationMonths: $months,
            userId: is_scalar($row->user_id) ? (int) $row->user_id : 0,
            createdAt: $createdAt, // NOVÉ
            id: is_scalar($row->id) ? (int) $row->id : 0
        );
    }

    public function createFromPlan(PremiumPlanDTO $plan, int $userId): OrderDTO
    {
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $months = 1;
        if ($plan->duration === 'add_3_months') {
            $months = 3;
        } elseif ($plan->duration === 'add_year') {
            $months = 12;
        }

        return new OrderDTO(
            orderNumber: $orderNumber,
            item: $plan->name,
            price: (float) $plan->price,
            durationMonths: $months,
            userId: $userId,
            createdAt: null,
            id: null
        );
    }
}