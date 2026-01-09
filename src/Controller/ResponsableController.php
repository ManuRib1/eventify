<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/responsable')]
#[IsGranted('ROLE_RESPONSABLE')]
class ResponsableController extends AbstractController
{
    #[Route('/', name: 'app_responsable_dashboard')]
    public function dashboard(): Response
    {
        $user = $this->getUser();
        $evenements = $user->getEvenementsResponsable();

        return $this->render('responsable/dashboard.html.twig', [
            'evenements' => $evenements,
        ]);
    }

    #[Route('/moderation', name: 'app_responsable_moderation')]
    public function moderation(AvisRepository $avisRepository): Response
    {
        $user = $this->getUser();
        
        // Si admin, voir tous les avis en attente
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $avisEnAttente = $avisRepository->findBy(['accepter' => false]);
        } else {
            // Sinon, uniquement les avis des événements dont on est responsable
            $evenements = $user->getEvenementsResponsable();
            $avisEnAttente = [];
            
            foreach ($evenements as $evenement) {
                foreach ($evenement->getAvis() as $avis) {
                    if (!$avis->isAccepter()) {
                        $avisEnAttente[] = $avis;
                    }
                }
            }
        }

        return $this->render('responsable/moderation.html.twig', [
            'avis_en_attente' => $avisEnAttente,
        ]);
    }
}