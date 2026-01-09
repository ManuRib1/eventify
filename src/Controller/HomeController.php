<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
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
        
        // Filtre par recherche (titre ou description)
        if ($search) {
            $queryBuilder
                ->andWhere('e.titre LIKE :search OR e.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        // Filtre par lieu
        if ($lieu) {
            $queryBuilder
                ->andWhere('e.lieu LIKE :lieu')
                ->setParameter('lieu', '%' . $lieu . '%');
        }
        
        // Filtre par date
        if ($dateDebut) {
            $queryBuilder
                ->andWhere('e.date >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime($dateDebut));
        }
        
        $evenements = $queryBuilder->getQuery()->getResult();
        
        // Récupérer tous les lieux uniques pour le filtre
        $lieux = $evenementRepository->createQueryBuilder('e')
            ->select('DISTINCT e.lieu')
            ->where('e.lieu IS NOT NULL')
            ->orderBy('e.lieu', 'ASC')
            ->getQuery()
            ->getResult();
        
        return $this->render('home/index.html.twig', [
            'evenements' => $evenements,
            'lieux' => array_column($lieux, 'lieu'),
            'current_search' => $search,
            'current_lieu' => $lieu,
            'current_date' => $dateDebut,
        ]);
    }
}