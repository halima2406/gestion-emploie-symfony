<?php

namespace App\DTO;

use App\Entity\Departements;

class EmployeSearchFormDto
{
    public ?string $numero = null;
    //public ?bool $isArchived = null;
    public ?string $statut = null;
    public ?Departements $departement = null;



    /** Helper: renvoie null (pas de filtre), true = archivé, false = actif */
    /*public function archivedRequested(): ?bool
    {
        if ($this->statut === null || $this->statut === '') {
            return null;
        }
        return $this->statut === 'archive';
    }*/
}