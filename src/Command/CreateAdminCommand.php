<?php

namespace App\Command;

use App\Entity\Employe;
use App\Repository\DepartementsRepository;
use App\Repository\EmployeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Créer un compte administrateur',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly EmployeRepository $employeRepo,
        private readonly DepartementsRepository $deptRepo,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Email de l\'admin', 'admin@example.test')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Mot de passe', '123')
            ->addOption('nom', null, InputOption::VALUE_OPTIONAL, 'Nom complet', 'Administrateur Principal');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getOption('email');
        $password = $input->getOption('password');
        $nom = $input->getOption('nom');

        $existingAdmin = $this->employeRepo->findOneBy(['email' => $email]);

        if ($existingAdmin) {
            $io->error("Un utilisateur avec l'email {$email} existe déjà.");
            return Command::FAILURE;
        }

        $departements = $this->deptRepo->findAll();

        if (empty($departements)) {
            $io->error('Aucun département trouvé. Créez d\'abord des départements.');
            return Command::FAILURE;
        }

        $premierDepartement = $departements[0];

        $admin = new Employe();
        $admin->setNumero('ADMIN' . time());
        $admin->setNomComplet($nom);
        $admin->setTelephone('771111111');
        $admin->setCreateAt(new \DateTimeImmutable());
        $admin->setIsArchived(false);
        $admin->setIsDeleted(false);
        $admin->setDepartement($premierDepartement);
        $admin->setEmail($email);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, $password));

        $this->em->persist($admin);
        $this->em->flush();

        $io->success('Administrateur créé avec succès !');
        $io->table(
            ['Propriété', 'Valeur'],
            [
                ['Numéro', $admin->getNumero()],
                ['Nom', $admin->getNomComplet()],
                ['Email', $admin->getEmail()],
                ['Mot de passe', $password],
                ['Rôle', 'ROLE_ADMIN'],
                ['Département', $premierDepartement->getNom()],
            ]
        );

        return Command::SUCCESS;
    }
}