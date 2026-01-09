<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
    #[Route('/promote/{id}', name: 'admin_user_promote')]
    public function promote(
        Utilisateur $utilisateur,
        EntityManagerInterface $entityManager
    ): Response {
        $roles = $utilisateur->getRoles();
        
        // Ajouter ROLE_RESPONSABLE s'il ne l'a pas déjà
        if (!in_array('ROLE_RESPONSABLE', $roles)) {
            $roles[] = 'ROLE_RESPONSABLE';
            $utilisateur->setRoles($roles);
            $entityManager->flush();
            
            $this->addFlash('success', 
                "{$utilisateur->getPrenom()} {$utilisateur->getNom()} est maintenant Responsable !"
            );
        } else {
            $this->addFlash('info', 'Cet utilisateur est déjà Responsable.');
        }
        
        return $this->redirectToRoute('admin');
    }
    
    #[Route('/demote/{id}', name: 'admin_user_demote')]
    public function demote(
        Utilisateur $utilisateur,
        EntityManagerInterface $entityManager
    ): Response {
        $roles = $utilisateur->getRoles();
        
        // Retirer ROLE_RESPONSABLE et ROLE_ADMIN
        $roles = array_filter($roles, function($role) {
            return !in_array($role, ['ROLE_RESPONSABLE', 'ROLE_ADMIN']);
        });
        
        $utilisateur->setRoles(array_values($roles));
        $entityManager->flush();
        
        $this->addFlash('success', 
            "{$utilisateur->getPrenom()} {$utilisateur->getNom()} est redevenu Utilisateur standard."
        );
        
        return $this->redirectToRoute('admin');
    }
}