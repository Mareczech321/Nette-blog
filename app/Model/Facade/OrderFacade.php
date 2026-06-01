<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\DTO\OrderDTO;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

final class OrderFacade
{
    public function __construct(
        private OrdersRepository $ordersRepository
    ) {}

    public function createOrder(OrderDTO $orderDTO): void
    {
        $this->ordersRepository->save(null, $orderDTO);
    }

    public function getLatestOrderByUser(int $userId): ?OrderDTO
    {
        return $this->ordersRepository->findLatestByUserId($userId);
    }

    /**
     * @return OrderDTO[]
     */
    public function getAllOrdersByUser(int $userId): array
    {
        return $this->ordersRepository->findAllByUserId($userId);
    }


    /**
     * @return array{totalSpent: float, totalMonths: int}
     */
    public function getUserStats(int $userId): array
    {
        return [
            'totalSpent' => $this->ordersRepository->getTotalSpentByUserId($userId),
            'totalMonths' => $this->ordersRepository->getTotalMonthsByUserId($userId)
        ];
    }

    public function getOrderById(int $orderId): ?ActiveRow {
        return $this->ordersRepository->findById($orderId);
    }
}