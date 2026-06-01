<?php
namespace App\Model\Repository;

use App\Model\BaseRepository;
use Nette\Database\Table\ActiveRow;
use App\Model\DTO\PremiumPlanDTO;
use App\Model\Enum\PremiumPlanColumns;

class PremiumPlanRepository extends BaseRepository {

    protected function getTableName(): string
    {
        return PremiumPlanColumns::TABLE_NAME->value;
    }

    /**
     * @param object $dto
     * @return array<string, mixed>
     */
    protected function mapDTOtoArray(object $dto): array
    {
        /** @var PremiumPlanDTO $dto */
        return [
            PremiumPlanColumns::CODE->value     => $dto->code,
            PremiumPlanColumns::NAME->value     => $dto->name,
            PremiumPlanColumns::PRICE->value    => $dto->price,
            PremiumPlanColumns::DURATION->value => $dto->duration,
            PremiumPlanColumns::INFO->value     => $dto->info,
            PremiumPlanColumns::FEATURES->value => json_encode($dto->features),
        ];
    }

    /**
     * @return PremiumPlanDTO[]
     */
    public function getPlans(): array
    {
        $rows = $this->database->table(PremiumPlanColumns::TABLE_NAME->value)->fetchAll();
        $dtos = [];
        foreach ($rows as $row) {
            $dtos[] = $this->mapRowToDTO($row);
        }
        return $dtos;
    }

    public function mapRowToDTO(ActiveRow $row): PremiumPlanDTO
    {
        $rawFeatures = json_decode((string)$row->{PremiumPlanColumns::FEATURES->value}, true);

        $features = is_array($rawFeatures) ? array_map('strval', $rawFeatures) : [];

        return new PremiumPlanDTO(
            code:     (string)$row->{PremiumPlanColumns::CODE->value},
            name:     (string)$row->{PremiumPlanColumns::NAME->value},
            price:    (int)$row->{PremiumPlanColumns::PRICE->value},
            duration: (string)$row->{PremiumPlanColumns::DURATION->value},
            info:     (string)$row->{PremiumPlanColumns::INFO->value},
            features: $features
        );
    }
}