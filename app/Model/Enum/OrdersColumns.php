<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum OrdersColumns: string
{
    case TABLE_NAME = 'orders';
    case ID = 'id';
    case ORDER_NUMBER = 'order_number';
    case ITEM = 'item';
    case PRICE = 'price';
    case USER_ID = 'user_id';
    case CREATED_AT = 'created_at';
    case DURATION_MONTHS = 'duration_months';
}
