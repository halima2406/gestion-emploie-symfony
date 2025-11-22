<?php

namespace App\DTO;

use App\Entity\Departements;
use DateTimeImmutable;

class DepartementListDto
{
    public int $id;
    public string $name;
    public bool $isArchived;
    public int $nbreEmploye = 0;
    public DateTimeImmutable $createAt;

    public function getNom(): string
    {
        return $this->name;
    }

    /*public static function fromEntitie(Departements $entity): DepartementListDto
    {
        $dto = new DepartementListDto();
        $dto->id = $entity->getId();
        $dto->name = $entity->getNom();
        $dto->isArchived = $entity->isArchived();
        $dto->createAt = $entity->getCreateAt();
        $dto->nbreEmploye = count($entity->getEmployes());

        return $dto;
    }*/


    public static function fromEntitie(Departements $entity): self
    {
        $dto = new self();
        $dto->id          = $entity->getId();
        $dto->name        = $entity->getNom();
        $dto->isArchived  = (bool) $entity->isDeleted();
        $dto->createAt    = $entity->getCreateAt();
        $dto->nbreEmploye = $entity->getEmployes()->count();

        return $dto;
    }

    public static function fromEntities(array $entities): array
    {
        return array_map(function (Departements $entity) {
            return self::fromEntitie($entity);
        }, $entities);
    }
}

