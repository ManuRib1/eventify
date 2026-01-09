<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Form\AvisType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/avis')]
#[IsGranted('ROLE_USER')]
class AvisController extends AbstractController
{
    #[Route('/{id}/edit', name: 'app_avis_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Avis $avis,
        EntityManagerInterface $entityManager
    ): Response {
        if ($avis->getUtilisateur() !== $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez modifier que vos propres avis.');
            return $this->redirectToRoute('app_evenement_index');
        }

        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avis->setModifierLe(new \DateTime());
            $avis->setAccepter(false);

            $entityManager->flush();

            $this->addFlash('success', 'Votre avis a été modifié et est en attente de modération.');

            return $this->redirectToRoute('app_evenement_show', [
                'id' => $avis->getEvenement()->getId()
            ]);
        }

        return $this->render('avis/edit.html.twig', [
            'avis' => $avis,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/moderate', name: 'app_avis_moderate', methods: ['POST'])]
    public function moderate(
        Request $request,
        Avis $avis,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $evenement = $avis->getEvenement();

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isResponsable = $evenement->isResponsable($user);

        if (!$isAdmin && !$isResponsable) {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour modérer cet avis.');
            return $this->redirectToRoute('app_evenement_index');
        }

        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('moderate'.$avis->getId(), $request->request->get('_token'))) {
            // Toggle l'acceptation
            $avis->setAccepter(!$avis->isAccepter());
            $entityManager->flush();

            $message = $avis->isAccepter() ? 'Avis accepté avec succès !' : 'Avis refusé';
            $this->addFlash('success', $message);
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_responsable_moderation');
    }

    #[Route('/{id}/delete', name: 'app_avis_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Avis $avis,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $evenement = $avis->getEvenement();

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isResponsable = $evenement->isResponsable($user);
        $isOwner = $avis->getUtilisateur() === $user;

        if (!$isAdmin && !$isResponsable && !$isOwner) {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cet avis.');
            return $this->redirectToRoute('app_evenement_index');
        }

        if ($this->isCsrfTokenValid('delete'.$avis->getId(), $request->request->get('_token'))) {
            $entityManager->remove($avis);
            $entityManager->flush();

            $this->addFlash('success', 'Avis supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        // Rediriger vers la page de modération si on est responsable/admin
        if ($isResponsable || $isAdmin) {
            return $this->redirectToRoute('app_responsable_moderation');
        }

        // Sinon rediriger vers la page de l'événement
        return $this->redirectToRoute('app_evenement_show', [
            'id' => $evenement->getId()
        ]);
    }
}