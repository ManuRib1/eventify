<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Avis;
use App\Form\AvisType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/evenement')]
class EvenementController extends AbstractController
{
    #[Route('/', name: 'app_evenement_index', methods: ['GET'])]
    public function index(
        Request $request,
        EvenementRepository $evenementRepository
    ): Response {
        // Récupérer les paramètres de recherche
        $search = $request->query->get('search', '');
        $lieu = $request->query->get('lieu', '');
        $dateDebut = $request->query->get('date_debut', '');
        
        // Construire la requête avec les filtres
        $queryBuilder = $evenementRepository->createQueryBuilder('e')
            ->orderBy('e.date', 'ASC');
        
        if ($search) {
            $queryBuilder
                ->andWhere('e.titre LIKE :search OR e.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        if ($lieu) {
            $queryBuilder
                ->andWhere('e.lieu LIKE :lieu')
                ->setParameter('lieu', '%' . $lieu . '%');
        }
        
        if ($dateDebut) {
            $queryBuilder
                ->andWhere('e.date >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime($dateDebut));
        }
        
        $evenements = $queryBuilder->getQuery()->getResult();
        
        // Récupérer tous les lieux pour le filtre
        $lieux = $evenementRepository->createQueryBuilder('e')
            ->select('DISTINCT e.lieu')
            ->where('e.lieu IS NOT NULL')
            ->orderBy('e.lieu', 'ASC')
            ->getQuery()
            ->getResult();
        
        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
            'lieux' => array_column($lieux, 'lieu'),
            'current_search' => $search,
            'current_lieu' => $lieu,
            'current_date' => $dateDebut,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_show', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function show(
        Evenement $evenement,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $avis = new Avis();
        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                $this->addFlash('error', 'Vous devez être connecté pour poster un avis.');
                return $this->redirectToRoute('app_login');
            }

            $avis->setUtilisateur($this->getUser());
            $avis->setEvenement($evenement);
            $avis->setCreerLe(new \DateTime());
            $avis->setAccepter(false);

            $entityManager->persist($avis);
            $entityManager->flush();

            $this->addFlash('success', 'Votre avis a été soumis et est en attente de modération.');

            return $this->redirectToRoute('app_evenement_show', ['id' => $evenement->getId()]);
        }

        // Récupérer les avis modérés
        $avisModeres = [];
        foreach ($evenement->getAvis() as $avis) {
            if ($avis->isAccepter()) {
                $avisModeres[] = $avis;
            }
        }

        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
            'avis_moderes' => $avisModeres,
            'form' => $form,
        ]);
    }
}