<?php

namespace App\Controller\Admin;

use App\Entity\Evenement;
use App\Entity\Utilisateur;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class EvenementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Evenement::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            TextField::new('titre')
                ->setLabel('Titre de l\'événement')
                ->setRequired(true),
            
            TextareaField::new('description')
                ->setLabel('Description')
                ->hideOnIndex(),
            
            DateTimeField::new('date')
                ->setLabel('Date et heure')
                ->setRequired(true),
            
            TextField::new('lieu')
                ->setLabel('Lieu'),
            
            AssociationField::new('responsables')
                ->setLabel('Responsables')
                ->setHelp('Sélectionnez un ou plusieurs responsables pour cet événement')
                ->autocomplete()
                ->setQueryBuilder(function ($queryBuilder) {
                    // Ne montrer que les utilisateurs avec le rôle RESPONSABLE ou ADMIN
                    return $queryBuilder
                        ->andWhere('entity.roles LIKE :role_resp OR entity.roles LIKE :role_admin')
                        ->setParameter('role_resp', '%ROLE_RESPONSABLE%')
                        ->setParameter('role_admin', '%ROLE_ADMIN%');
                }),
        ];
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Événement')
            ->setEntityLabelInPlural('Événements')
            ->setPageTitle('index', 'Gestion des Événements')
            ->setDefaultSort(['date' => 'ASC'])
            ->setSearchFields(['titre', 'lieu', 'description']);
    }
}