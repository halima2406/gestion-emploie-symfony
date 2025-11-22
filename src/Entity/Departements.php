<?php

namespace App\Entity;

use App\Repository\DepartementsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity; 

#[ORM\Entity(repositoryClass: DepartementsRepository::class)]

#[UniqueEntity(
    fields: ['nom'],
    message: 'Le département {{ value }} existe déjà.'
)]
class Departements
{

    


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[Assert\NotBlank(message: "Le nom du département est obligatoire.")]
    #[Assert\Length(
        min:3,
        max:100,
        minMessage:"Le nom du département doit avoir au moins {{ limit }} caracteres",
        maxMessage:"Le nom du département doit avoir au plus {{ limit }} caracteres"

    )]


    #[ORM\Column(length: 200, unique: true, name: 'departement_name')]
    private ?string $nom = null;

    #[ORM\Column(name:"create_at")]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column(name:"update_at" , nullable:true)]
    private ?\DateTimeImmutable $updateAt = null;

    #[ORM\Column(name:"is_deleted" , options: ["default"=>false])]
    private ?bool $isDeleted = null;

    /**
     * @var Collection<int, Employe>
     */
    #[ORM\OneToMany(targetEntity: Employe::class, mappedBy: 'departement')]
    private Collection $employes;

    public function __construct()
    {
        $this->employes = new ArrayCollection();

        $this->createAt  = new \DateTimeImmutable();
        $this->isDeleted = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeImmutable $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * @return Collection<int, Employe>
     */
    public function getEmployes(): Collection
    {
        return $this->employes;
    }

    public function addEmploye(Employe $employe): static
    {
        if (!$this->employes->contains($employe)) {
            $this->employes->add($employe);
            $employe->setDepartement($this);
        }

        return $this;
    }

    public function removeEmploye(Employe $employe): static
    {
        if ($this->employes->removeElement($employe)) {
            // set the owning side to null (unless already changed)
            if ($employe->getDepartement() === $this) {
                $employe->setDepartement(null);
            }
        }

        return $this;
    }

    public function isArchived(): bool
{
    // si tu veux que "archivé" == "supprimé logiquement"
    return (bool) $this->isDeleted;
}
    public function setArchived(bool $archived): static
    {
        // si tu veux que "archivé" == "supprimé logiquement"
        $this->isDeleted = $archived;

        return $this;
    }
}
