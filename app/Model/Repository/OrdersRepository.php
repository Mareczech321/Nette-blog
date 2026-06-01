<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\DTO\OrderDTO;
use App\Model\Mapper\OrderMapper;
use App\Model\Enum\OrdersColumns;

class OrdersRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return OrdersColumns::TABLE_NAME->value;
    }

    /**
     * @param object $dto
     * @return array<string, mixed>
     */
    protected function mapDTOtoArray(object $dto): array
    {
        /** @var OrderDTO $dto */
        return [
            OrdersColumns::USER_ID->value => $dto->userId,
            OrdersColumns::ORDER_NUMBER->value => $dto->orderNumber,
            OrdersColumns::ITEM->value => $dto->item,
            OrdersColumns::PRICE->value => $dto->price,
            OrdersColumns::DURATION_MONTHS->value => $dto->durationMonths,
            OrdersColumns::CREATED_AT->value => $dto->createdAt,
            OrdersColumns::ID->value => $dto->id
        ];
    }

    public function findLatestByUserId(int $userId): ?OrderDTO
    {
        $row = $this->database->table(OrdersColumns::TABLE_NAME->value)
            ->where(OrdersColumns::USER_ID->value, $userId)
            ->order('id DESC')
            ->fetch();

        if (!$row) {
            return null;
        }

        $createdAtRaw = $row->{OrdersColumns::CREATED_AT->value};
        $createdAt = $createdAtRaw instanceof \DateTimeInterface
            ? \DateTimeImmutable::createFromInterface($createdAtRaw)
            : null;

        return new OrderDTO(
            orderNumber: (string) $row->{OrdersColumns::ORDER_NUMBER->value},
            item: (string) $row->{OrdersColumns::ITEM->value},
            price: (float) $row->{OrdersColumns::PRICE->value},
            durationMonths: $row->{OrdersColumns::DURATION_MONTHS->value},
            userId: (int) $row->{OrdersColumns::USER_ID->value},
            createdAt: $createdAt,
            id: (int) $row->{OrdersColumns::ID->value}
        );
    }

    /**
     * @param int $userId
     * @return OrderDTO[]
     */
    public function findAllByUserId(int $userId): array
    {
        $rows = $this->database->table(OrdersColumns::TABLE_NAME->value)
            ->where(OrdersColumns::USER_ID->value, $userId)
            ->order('id DESC')
            ->fetchAll();

        $orders = [];
        $mapper = new OrderMapper();
        foreach ($rows as $row) {
            $orders[] = $mapper->map($row);
        }

        return $orders;
    }

    public function getTotalSpentByUserId(int $userId): float
    {
        $sum = $this->database->table(OrdersColumns::TABLE_NAME->value)
            ->where(OrdersColumns::USER_ID->value, $userId)
            ->sum(OrdersColumns::PRICE->value);

        return is_numeric($sum) ? (float) $sum : 0.0;
    }

    public function getTotalMonthsByUserId(int $userId): int
    {
        $sum = $this->database->table(OrdersColumns::TABLE_NAME->value)
            ->where(OrdersColumns::USER_ID->value, $userId)
            ->sum(OrdersColumns::DURATION_MONTHS->value);

        return is_numeric($sum) ? (int) $sum : 0;
    }
}