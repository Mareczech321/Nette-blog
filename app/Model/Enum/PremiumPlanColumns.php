<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum PremiumPlanColumns: string
{
    case TABLE_NAME = 'premium_plans';
    case CODE = 'code';
    case NAME = 'name';
    case PRICE = 'price';
    case DURATION = 'duration';
    case INFO = 'info';
    case FEATURES = 'features';
}
