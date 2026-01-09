<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class UtilisateurCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            EmailField::new('email'),
            TextField::new('nom'),
            TextField::new('prenom'),
        ];

        // Ajouter le champ mot de passe seulement pour la création
        if ($pageName === Crud::PAGE_NEW) {
            $fields[] = TextField::new('password', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->setRequired(true)
                ->setHelp('Le mot de passe sera automatiquement hashé');
        }

        // Pour la modification, proposer de changer le mot de passe (optionnel)
        if ($pageName === Crud::PAGE_EDIT) {
            $fields[] = TextField::new('password', 'Nouveau mot de passe (optionnel)')
                ->setFormType(PasswordType::class)
                ->setRequired(false)
                ->setHelp('Laissez vide pour conserver le mot de passe actuel');
        }

        $fields[] = ChoiceField::new('roles')
            ->setChoices([
                'Utilisateur' => 'ROLE_USER',
                'Responsable' => 'ROLE_RESPONSABLE',
                'Administrateur' => 'ROLE_ADMIN',
            ])
            ->allowMultipleChoices()
            ->renderExpanded()
            ->setHelp('Sélectionnez un ou plusieurs rôles');

        return $fields;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPageTitle('index', 'Gestion des Utilisateurs')
            ->setSearchFields(['email', 'nom', 'prenom']);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Utilisateur $entityInstance */
        if ($entityInstance instanceof Utilisateur) {
            // Hasher le mot de passe avant de persister
            if ($entityInstance->getPassword()) {
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $entityInstance,
                    $entityInstance->getPassword()
                );
                $entityInstance->setPassword($hashedPassword);
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Utilisateur $entityInstance */
        if ($entityInstance instanceof Utilisateur) {
            // Si un nouveau mot de passe a été saisi, le hasher
            if ($entityInstance->getPassword()) {
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $entityInstance,
                    $entityInstance->getPassword()
                );
                $entityInstance->setPassword($hashedPassword);
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}