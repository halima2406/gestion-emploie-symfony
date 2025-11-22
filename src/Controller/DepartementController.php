<?php

namespace App\Controller;

use App\Entity\Departements;
use App\Form\DepartementType;
use App\DTO\DepartementListDto;
use App\Repository\DepartementsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DepartementController extends AbstractController
{
    private const LIMIT = 4;

    public function __construct(
        private readonly DepartementsRepository $departementRepository,
        private readonly EntityManagerInterface $manager
    ) {}

    #[Route('/departement/list', name: 'app_departement_list', methods: ['GET','POST'])]
    public function list(Request $request): Response
    {
        // Formulaire d'ajout
        $departement = new Departements();
        $form = $this->createForm(DepartementType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->departementRepository->save($departement, true);
            return $this->redirectToRoute('app_departement_list');
        }

        // Pagination
        $page   = max(1, (int) $request->query->get('page', 1));
        $offset = ($page - 1) * self::LIMIT;

        $departements = $this->departementRepository->findBy(
            [],
            ['id' => 'DESC'],
            self::LIMIT,
            $offset
        );

        // DTO pour l'affichage
        $departementListDto = DepartementListDto::fromEntities($departements);

        // Nombre total de pages
        $count    = $this->departementRepository->count([]);
        $nbrepage = (int) max(1, ceil($count / self::LIMIT));

        // >>> Comptage des employés par département (utilise la méthode du repo)
        $ids    = array_map(fn(Departements $d) => $d->getId(), $departements);
        $counts = $this->departementRepository->countEmployeesFor($ids);

        return $this->render('departement/list.html.twig', [
            'departements' => $departementListDto,
            'pageEnCours'  => $page,
            'nbrepage'     => $nbrepage,
            'counts'       => $counts,            // [depId => nbEmployes]
            'formDept'     => $form->createView(),
        ]);
    }
}