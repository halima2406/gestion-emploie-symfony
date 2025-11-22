<?php
namespace App\DTO;

final class EmployeListDto
{
    public string $numero;
    public string $nomComplet;
    public ?string $telephone = null;
    public \DateTimeImmutable $createAt;
    public string $departement;
    public bool $isDeleted;
    public string $photo; // ✅ Ajouté ici

    public function __construct(
        string $numero,
        string $nomComplet,
        ?string $telephone,
        \DateTimeImmutable $createAt,
        string $departement,
        bool $isDeleted,
        string $photo // ✅ Ajouté ici
    ) {
        $this->numero      = $numero;
        $this->nomComplet  = $nomComplet;
        $this->telephone   = $telephone;
        $this->createAt    = $createAt;
        $this->departement = $departement;
        $this->isDeleted   = $isDeleted;
        $this->photo       = $photo;
    }

    /** Exemple de fabrique depuis l’entity */
    public static function fromEntity(\App\Entity\Employe $e): self
    {
        $basePath = '/uploads/';

        $photo = $e->getPhoto()
            ? $basePath . $e->getPhoto()
            : $basePath . 'default.png';

        return new self(
            $e->getNumero(),
            (string) $e->getNomComplet(),
            $e->getTelephone(),
            $e->getCreateAt() ?? new \DateTimeImmutable(),
            $e->getDepartement()?->getNom() ?? '',
            (bool) $e->isDeleted(),
            $photo
        );
    }
}