<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Repository\EmployeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe')]
class EmployeViewController extends AbstractController
{
    /**
     * Vue pour les employés : ils ne voient QUE les employés de leur département
     * Sans possibilité d'ajouter, modifier ou filtrer
     */
    #[Route('/mon-departement', name: 'app_employe_my_department')]
    #[IsGranted('ROLE_EMPLOYE')]
    public function myDepartment(EmployeRepository $employeRepo): Response
    {
        /** @var Employe $currentUser */
        $currentUser = $this->getUser();

        // Récupérer le département de l'employé connecté
        $departement = $currentUser->getDepartement();

        if (!$departement) {
            throw $this->createNotFoundException('Vous n\'êtes assigné à aucun département.');
        }

        // Récupérer uniquement les employés actifs (non supprimés) du même département
        $employes = $employeRepo->findBy([
            'departement' => $departement,
            'isDeleted' => false,
        ], ['nomComplet' => 'ASC']);

        return $this->render('employe/my_department.html.twig', [
            'employes' => $employes,
            'departement' => $departement,
        ]);
    }
}