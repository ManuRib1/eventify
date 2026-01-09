<?php

namespace App\Controller\Admin;

use App\Entity\Evenement;
use App\Entity\Utilisateur;
use App\Repository\EvenementRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/assign')]
#[IsGranted('ROLE_ADMIN')]
class AssignResponsableController extends AbstractController
{
    #[Route('/', name: 'admin_assign_index')]
    public function index(
        EvenementRepository $evenementRepository,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        $evenements = $evenementRepository->findAll();
        $responsables = $utilisateurRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role_resp OR u.roles LIKE :role_admin')
            ->setParameter('role_resp', '%ROLE_RESPONSABLE%')
            ->setParameter('role_admin', '%ROLE_ADMIN%')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/assign_responsable.html.twig', [
            'evenements' => $evenements,
            'responsables' => $responsables,
        ]);
    }

    #[Route('/add/{evenementId}/{responsableId}', name: 'admin_assign_add')]
    public function add(
        int $evenementId,
        int $responsableId,
        EvenementRepository $evenementRepository,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $evenement = $evenementRepository->find($evenementId);
        $responsable = $utilisateurRepository->find($responsableId);

        if (!$evenement || !$responsable) {
            $this->addFlash('error', 'Événement ou responsable introuvable.');
            return $this->redirectToRoute('admin_assign_index');
        }

        if ($evenement->getResponsables()->contains($responsable)) {
            $this->addFlash('info', 'Ce responsable est déjà assigné à cet événement.');
        } else {
            $evenement->addResponsable($responsable);
            $entityManager->flush();
            $this->addFlash('success', "{$responsable->getPrenom()} {$responsable->getNom()} a été ajouté comme responsable de \"{$evenement->getTitre()}\".");
        }

        return $this->redirectToRoute('admin_assign_index');
    }

    #[Route('/remove/{evenementId}/{responsableId}', name: 'admin_assign_remove')]
    public function remove(
        int $evenementId,
        int $responsableId,
        EvenementRepository $evenementRepository,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $evenement = $evenementRepository->find($evenementId);
        $responsable = $utilisateurRepository->find($responsableId);

        if (!$evenement || !$responsable) {
            $this->addFlash('error', 'Événement ou responsable introuvable.');
            return $this->redirectToRoute('admin_assign_index');
        }

        $evenement->removeResponsable($responsable);
        $entityManager->flush();
        
        $this->addFlash('success', "{$responsable->getPrenom()} {$responsable->getNom()} a été retiré de \"{$evenement->getTitre()}\".");

        return $this->redirectToRoute('admin_assign_index');
    }
}