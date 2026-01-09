<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use App\Entity\Evenement;
use App\Entity\Avis;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Rediriger vers la liste des événements par défaut
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration - Relais & Châteaux')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        
        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', Utilisateur::class);
        
        yield MenuItem::section('Événements');
        yield MenuItem::linkToCrud('Événements', 'fa fa-calendar', Evenement::class);
        yield MenuItem::linkToRoute('Assigner Responsables', 'fa fa-user-plus', 'admin_assign_index');

        
        yield MenuItem::section('Avis');
        yield MenuItem::linkToCrud('Avis', 'fa fa-star', Avis::class);
        
        yield MenuItem::section('');
        yield MenuItem::linkToRoute('Retour au site', 'fa fa-arrow-left', 'app_home');
    }
}