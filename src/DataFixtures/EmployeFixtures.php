<?php

namespace App\DataFixtures;

use App\Entity\Employe;
use App\Repository\DepartementsRepository;
use App\Repository\EmployeRepository;
use App\Service\GenerateNumeroService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EmployeFixtures extends Fixture
{
    public function __construct(
        private readonly DepartementsRepository $departementRepository,
        private readonly EmployeRepository $employeRepository,
        private readonly GenerateNumeroService $gen,
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $departements = $this->departementRepository->findAll();
        $n = 1;


        if (!$this->employeRepository->findOneBy(['email' => 'admin@example.test'])) {
            $dep = $departements[0] ?? null;
            if ($dep) {
                $admin = new Employe();
                $admin->setNumero('ADMIN001');
                $admin->setNomComplet('Administrateur Principal');
                $admin->setTelephone('771111111');
                $admin->setCreateAt(new \DateTimeImmutable());
                $admin->setIsArchived(false);
                $admin->setIsDeleted(false);
                $admin->setDepartement($dep);
                $admin->setEmail('admin@example.test');
                $admin->setRoles(['ROLE_ADMIN']); // ← ROLE ADMIN
                $admin->setPassword($this->hasher->hashPassword($admin, '123'));
                
                $manager->persist($admin);
            }
        }

        // --- 1) Génération par boucle (emails UNIQUES) -----------------------
        foreach ($departements as $departement) {
            for ($i = 1; $i <= 9; $i++) {
                $e = new Employe();

                // Numéro unique
                do {
                    $numero = $this->gen->generateCodeEmploye();
                } while ($this->employeRepository->findOneBy(['numero' => $numero]));
                $e->setNumero($numero);

                $e->setNomComplet("Employe {$i} du departement {$departement->getNom()}");

                // Téléphone factice conforme (9 chiffres)
                $prefixes = ['77','78','76','75','70'];
                $prefix   = $prefixes[$n % count($prefixes)];
                $tel      = $prefix . str_pad((string)$n, 7, '0', STR_PAD_LEFT);
                $n++;
                $e->setTelephone($tel);

                $e->setCreateAt(new \DateTimeImmutable());
                $e->setIsArchived(false);
                $e->setIsDeleted(false);
                $e->setDepartement($departement);

                // ---- Champs hérités de User (héritage JOINED)
                // => email UNIQUE lié au numéro
                $email = strtolower($numero) . '@example.test';
                $e->setEmail($email);
                $e->setRoles(['ROLE_EMPLOYE']);
                $e->setPassword($this->hasher->hashPassword($e, '123'));

                $manager->persist($e);
            }
        }

        // --- 2) Compte de test fixe : test@example.test / 123 ----------------
        if (!$this->employeRepository->findOneBy(['email' => 'test@example.test'])) {
            // on prend le 1er département dispo ou on en crée un au besoin
            $dep = $departements[0] ?? null;
            if (!$dep) {
                // si jamais aucun département, on abandonne proprement
                // (ou crée un département ici selon ton modèle)
                $manager->flush();
                return;
            }

            $e = new Employe();
            $e->setNumero('EMPTEST001');
            $e->setNomComplet('Employe Test');
            $e->setTelephone('771234567');
            $e->setCreateAt(new \DateTimeImmutable());
            $e->setIsArchived(false);
            $e->setIsDeleted(false);
            $e->setDepartement($dep);

            $e->setEmail('test@example.test');
            $e->setRoles(['ROLE_EMPLOYE']);
            $e->setPassword($this->hasher->hashPassword($e, '123'));

            $manager->persist($e);
        }

        $manager->flush();
    }
}