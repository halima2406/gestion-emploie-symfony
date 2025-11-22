<?php

namespace App\Controller;

use App\Form\EmployeType;
use App\Repository\DepartementsRepository;
use App\Repository\EmployeRepository;
use App\Entity\Employe;
use App\Service\GenerateNumeroService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\DTO\EmployeSearchFormDto;
use App\Service\Impl\FileUploaderService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\EmployeSearchType;


#[Route('/employe')]
final class EmployeController extends AbstractController
{
    private const LIMIT = 10;

   
    #[Route('/list', name: 'app_employe_list')]
    #[IsGranted('ROLE_EMPLOYE')] 
   
    public function list(
        EmployeRepository $empRepo, 
        DepartementsRepository $depRepo, 
        Request $request
    ): Response {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = self::LIMIT;

        // ========================================
        // LOGIQUE SELON LE RÔLE
        // ========================================
        
        if ($this->isGranted('ROLE_ADMIN')) {
            // ============ ADMIN : Voit tout avec filtres et pagination ============
            
            //$search = new \App\Form\EmployeSearchType();
            $search = new EmployeSearchFormDto();
            $form = $this->createForm(EmployeSearchType::class, $search);
            $form->handleRequest($request);
            
            [$employes, $count] = $empRepo->searchPaginated($search, $page, $limit);
            $departements = $depRepo->findAll();
            
        } else {
            // ============ EMPLOYÉ : Voit uniquement son département ============
            
            /** @var Employe $currentUser */
            $currentUser = $this->getUser();
            $departement = $currentUser->getDepartement();
            
            if (!$departement) {
                $this->addFlash('error', 'Vous n\'êtes assigné à aucun département.');
                return $this->redirectToRoute('app_login');
            }
            
            // Récupérer uniquement les employés de SON département (sans pagination ni filtres)
            $employes = $empRepo->findBy([
                'departement' => $departement,
                'isDeleted' => false,
            ], ['nomComplet' => 'ASC']);
            
            $count = count($employes);
            $departements = [];
            $form = null; // Pas de formulaire de recherche pour les employés
        }

        return $this->render('employe/list.html.twig', [
            'employes' => $employes,
            'departements' => $departements,
            'pageEnCours' => $page,
            'nbrepage' => (int) max(1, ceil($count / $limit)),
            'formSearchEmp' => $form ? $form->createView() : null,
        ]);
    }

    
    
    #[Route('/employe/add', name: 'app_employe_add', methods: ['GET','POST'])]
    
    public function save(
        Request $request,
        EmployeRepository $repo,
        GenerateNumeroService $numService,
        FileUploaderService $uploader,
        UserPasswordHasherInterface $hasher              // 👈 on injecte le hasher
    ): Response {
        $employe = new Employe();
        $form    = $this->createForm(EmployeType::class, $employe);

        // numéro provisoire affiché (champ non mappé)
        $form->get('codeAffiche')?->setData($numService->generateCodeEmploye());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1) Générer et poser la PK (numero) avant persist
            do {
                $numero = $numService->generateCodeEmploye();
            } while ($repo->find($numero));
            $employe->setNumero($numero);

            // 2) Valeurs par défaut
            $employe->getCreateAt() ?? $employe->setCreateAt(new \DateTimeImmutable());

            // 3) Champs non mappés → adresse
            $pays  = $form->has('pays')  ? $form->get('pays')->getData()  : null;
            $ville = $form->has('ville') ? $form->get('ville')->getData() : null;
            $rue   = $form->has('rue')   ? $form->get('rue')->getData()   : null;

            if ($rue || $ville || $pays) {
                $employe->setAdresse(sprintf('Rue: %s - Ville: %s - Pays: %s', $rue ?? '', $ville ?? '', $pays ?? ''));
            }

            // 4) Upload photo (champ non mappé photoFile)
            if ($form->has('photoFile') && ($file = $form->get('photoFile')->getData())) {
                $filename = $uploader->upload($file);
                $employe->setPhoto($filename);
            }

            // 5) OBLIGATOIRE pour héritage JOINED (User ← Employe) : email / roles / password
            //    - si tu as des champs 'email' et 'plainPassword' dans le form, on les utilise
            //    - sinon, on met un fallback propre pour éviter le NOT NULL
            $email = $form->has('email') ? $form->get('email')->getData() : null;
            if (!$email) {
                $email = strtolower($numero).'@example.test'; // fallback
            }
            $employe->setEmail($email);

            $plain = $form->has('plainPassword') ? $form->get('plainPassword')->getData() : 'Passw0rd!';
            $employe->setPassword($hasher->hashPassword($employe, $plain));

            $roles = $form->has('roles') ? (array) $form->get('roles')->getData() : ['ROLE_EMPLOYE'];
            $employe->setRoles($roles);

            // 6) Sauvegarde (une seule fois)
            $repo->save($employe, true);

            $this->addFlash('success', 'Employé enregistré avec succès. Numéro : '.$employe->getNumero());
            return $this->redirectToRoute('app_employe_list');
        }

        return $this->render('employe/form.html.twig', [
            'formEmp' => $form->createView(),
        ]);
    }

    #[Route('/employe/departement/{id}', name: 'employe_par_departement', methods: ['GET'])]
    public function employeParDepartement(
        int $id,
        Request $request,
        DepartementsRepository $depRepo,
        EmployeRepository $empRepo
    ): Response {
        $departement = $depRepo->find($id);
        if (!$departement) {
            throw $this->createNotFoundException('Département introuvable');
        }

        $search = new EmployeSearchFormDto();
        $search->departement = $departement; // prérempli

        $form = $this->createForm(\App\Form\EmployeSearchType::class, $search);
        $form->handleRequest($request);

        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = 15;

        [$employes, $count] = $empRepo->searchPaginated($search, $page, $limit);

        return $this->render('employe/list.html.twig', [
            'departement'   => $departement,
            'departements'  => $depRepo->findAll(),
            'employes'      => $employes,
            'pageEnCours'   => $page,
            'nbrepage'      => (int) max(1, ceil($count / $limit)),
            'formSearchEmp' => $form->createView(),
        ]);
    }


  




  
}
