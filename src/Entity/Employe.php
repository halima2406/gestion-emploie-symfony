<?php

namespace App\Entity;

use App\Repository\EmployeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: EmployeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Employe extends User
{
   
    #[ORM\Column(length: 30)]
    private string $numero;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: "Le nom et le prénom sont obligatoires")]
    #[Assert\Length(min: 4, minMessage: "Le nom et le prénom doivent avoir au moins 4 caractères")]
    private ?string $nomComplet = null;

    #[ORM\Column(length: 15, unique: true, nullable: false)]
    #[Assert\NotBlank(message: "Le téléphone de l'employé est obligatoire")]
    #[Assert\Regex(
        pattern: "/^(77|78|76|75|70)[0-9]{7}$/",
        message: "Le numéro de téléphone '{{ value }}' n'est pas valide. Il doit contenir 9 chiffres et commencer par 70, 75, 76, 77 ou 78."
    )]
    private ?string $telephone = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updateAt = null;

    #[ORM\Column(name: "is_deleted", options: ["default" => false])]
    private bool $isDeleted = false;

    #[ORM\Column(name: "is_archived", options: ["default" => false])]
    private bool $isArchived = false;

    #[ORM\ManyToOne(inversedBy: 'employes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le département est obligatoire")]
    private ?Departements $departement = null;

    #[ORM\Column(nullable: true)]
    #[Assert\LessThanOrEqual(
        value: "today",
        message: "La date d'embauche ne doit pas être supérieure à la date du jour."
    )]
    private ?\DateTimeImmutable $embaucheAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $adresse = null;

    // === Fichier photo (persisté) ===
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    // === Champ non persisté pour l'upload + validation ===
    //#[Assert\NotNull(message: "La photo de l'employé est obligatoire")]
   
    private ?UploadedFile $photoFile = null;

    public function __construct()
    {
        $this->isArchived = false;
    }

    #[ORM\PrePersist]
    public function initDates(): void
    {
        if ($this->createAt === null) {
            $this->createAt = new \DateTimeImmutable();
        }
    }

    // --- getters/setters standard ---

    public function getNumero(): string { return $this->numero; }
    public function setNumero(string $numero): self { $this->numero = $numero; return $this; }

    public function getNomComplet(): ?string { return $this->nomComplet; }
    public function setNomComplet(string $nomComplet): self { $this->nomComplet = $nomComplet; return $this; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(string $telephone): self { $this->telephone = $telephone; return $this; }

    public function getCreateAt(): ?\DateTimeImmutable { return $this->createAt; }
    public function setCreateAt(\DateTimeImmutable $createAt): self { $this->createAt = $createAt; return $this; }

    public function getUpdateAt(): ?\DateTimeImmutable { return $this->updateAt; }
    public function setUpdateAt(?\DateTimeImmutable $updateAt): self { $this->updateAt = $updateAt; return $this; }

    public function isDeleted(): bool { return $this->isDeleted; }
    public function setIsDeleted(bool $isDeleted): self { $this->isDeleted = $isDeleted; return $this; }

    public function isArchived(): bool { return $this->isArchived; }
    public function setIsArchived(bool $isArchived): self { $this->isArchived = $isArchived; return $this; }

    public function getDepartement(): ?Departements { return $this->departement; }
    public function setDepartement(?Departements $departement): self { $this->departement = $departement; return $this; }

    public function getEmbaucheAt(): ?\DateTimeImmutable { return $this->embaucheAt; }
    public function setEmbaucheAt(?\DateTimeImmutable $embaucheAt): self { $this->embaucheAt = $embaucheAt; return $this; }

    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): self { $this->adresse = $adresse; return $this; }

    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): self { $this->photo = $photo; return $this; }

    // --- photoFile (non persisté) ---
    public function getPhotoFile(): ?UploadedFile { return $this->photoFile; }
    public function setPhotoFile(?UploadedFile $file): self { $this->photoFile = $file; return $this; }

    public function __toString(): string
    {
        return $this->numero.' - '.($this->nomComplet ?? '');
    }
}