<?php
    declare(strict_types=1);

    namespace App\Model;

    use Nette\Database\Explorer;
    use Nette\Database\Table\Selection;
    use Nette\Database\Table\ActiveRow;

    abstract class BaseRepository {

        public function __construct(
            protected Explorer $database
        ){}

        abstract protected function getTableName(): string;
        /**
         * @return array<string, mixed>
         */
        abstract protected function mapDTOtoArray(object $dto): array;

        public function findById(int $id):? ActiveRow{
            return $this->database->table($this->getTableName())->get($id);
        }

        public function findAll(): Selection{
            return $this->database->table($this->getTableName());
        }

        public function save(?int $id, object $dto): ActiveRow
        {
            $data = $this->mapDTOToArray($dto);

            if ($id !== null) {
                $row = $this->findById($id);
                if (!$row) {
                    throw new \RuntimeException(sprintf('Record with ID %d not found in table %s', $id, $this->getTableName()));
                }
                $row->update($data);
                return $row;
            }

            $row = $this->database->table($this->getTableName())->insert($data);
            if (!$row instanceof ActiveRow) {
                throw new \RuntimeException(sprintf('Failed to insert record into table %s', $this->getTableName()));
            }
            return $row;
        }

        public function delete(int $id): void
        {
            $row = $this->findById($id);
            if ($row) {
                $row->delete();
            }
        }
    }