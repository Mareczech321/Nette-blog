<?php

namespace App\Model;

use App\Model\Repository\PremiumPlanRepository;
use App\Model\DTO\PremiumPlanDTO;
final class PremiumFacade
{
    public function __construct(
        private PremiumPlanRepository $premiumPlanRepository
    ) {}

    /**
     * @return PremiumPlanDTO[]
     */
    public function getAllPlans(): array
    {
        $plans = [];
        foreach ($this->premiumPlanRepository->getPlans() as $dto) {
            $plans[$dto->code] = $dto;
        }
        return $plans;
    }
}