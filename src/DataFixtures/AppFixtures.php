<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use App\Entity\Evenement;
use App\Entity\Avis;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ========== CRÉER UN ADMINISTRATEUR ==========
        $admin = new Utilisateur();
        $admin->setEmail('admin@test.com');
        $admin->setNom('Admin');
        $admin->setPrenom('Super');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // ========== CRÉER DES RESPONSABLES ==========
        $responsables = [];
        for ($i = 1; $i <= 3; $i++) {
            $responsable = new Utilisateur();
            $responsable->setEmail("responsable{$i}@test.com");
            $responsable->setNom("Responsable{$i}");
            $responsable->setPrenom("Jean");
            $responsable->setRoles(['ROLE_RESPONSABLE']);
            $responsable->setPassword($this->passwordHasher->hashPassword($responsable, 'resp123'));
            $manager->persist($responsable);
            $responsables[] = $responsable;
        }

        // ========== CRÉER DES UTILISATEURS STANDARDS ==========
        $utilisateurs = [];
        for ($i = 1; $i <= 5; $i++) {
            $utilisateur = new Utilisateur();
            $utilisateur->setEmail("user{$i}@test.com");
            $utilisateur->setNom("Utilisateur{$i}");
            $utilisateur->setPrenom("Marie");
            $utilisateur->setRoles(['ROLE_USER']);
            $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, 'user123'));
            $manager->persist($utilisateur);
            $utilisateurs[] = $utilisateur;
        }

        // ========== CRÉER DES ÉVÉNEMENTS ==========
        $evenements = [];
        $titres = [
            'Gala de Charité 2026',
            'Séminaire d\'Entreprise',
            'Mariage Royal au Château',
            'Lancement de Produit Premium',
            'Concert Privé Jazz & Wine',
            'Conférence Tech Innovation',
            'Dîner Gastronomique',
            'Atelier Œnologie'
        ];

        $descriptions = [
            'Un événement exceptionnel dans un cadre prestigieux avec menu gastronomique.',
            'Réunion professionnelle dans nos salons privés avec équipements audiovisuels.',
            'Célébration inoubliable avec hébergement et prestations haut de gamme.',
            'Présentation exclusive de nos nouvelles collections dans une ambiance raffinée.',
            'Soirée musicale intime avec les meilleurs artistes et dégustation de vins.',
            'Rencontre des leaders de l\'innovation technologique.',
            'Expérience culinaire unique avec notre chef étoilé.',
            'Découverte des grands crus dans nos caves historiques.'
        ];

        $lieux = [
            'Château de Beaumont, Bordeaux',
            'Grand Hôtel de Paris',
            'Villa Méditerranée, Nice',
            'Palais Royal, Versailles',
            'Domaine de la Loire',
            'Hôtel de Crillon, Paris',
            'Château de Chambord',
            'Abbaye de Fontevraud'
        ];

        foreach ($titres as $index => $titre) {
            $evenement = new Evenement();
            $evenement->setTitre($titre);
            $evenement->setDescription($descriptions[$index]);
            
            // Dates futures étalées sur les 6 prochains mois
            $dateFuture = new \DateTime('+' . (($index * 15) + 7) . ' days');
            $evenement->setDate($dateFuture);
            $evenement->setLieu($lieux[$index]);
            
            // Assigner un responsable à chaque événement
            $evenement->addResponsable($responsables[$index % count($responsables)]);
            
            $manager->persist($evenement);
            $evenements[] = $evenement;
        }

        // ========== CRÉER DES AVIS ==========
        $commentaires = [
            "Événement absolument magnifique ! L'organisation était parfaite et le cadre somptueux. Je recommande vivement.",
            "Une expérience inoubliable. Le service était irréprochable et l'ambiance exceptionnelle.",
            "Très belle soirée, mais quelques petits détails à améliorer au niveau du timing.",
            "Parfait de A à Z ! Bravo à toute l'équipe pour ce moment magique.",
            "Cadre magnifique et prestations de qualité. Un moment hors du temps.",
            "Bonne organisation générale. Quelques améliorations possibles sur l'accueil.",
            "Superbe événement ! Tout était réuni pour passer une excellente soirée.",
            "Très satisfait de cette expérience. À refaire sans hésiter !",
            "Ambiance chaleureuse et service attentionné. Une belle réussite.",
            "Événement de grande qualité. Merci pour ces moments exceptionnels."
        ];

        foreach ($evenements as $evenement) {
            // Créer 3-5 avis par événement
            $nbAvis = rand(3, 5);
            
            for ($i = 0; $i < $nbAvis; $i++) {
                $avis = new Avis();
                $avis->setNote(rand(3, 5)); // Notes entre 3 et 5 étoiles
                $avis->setCommentaire($commentaires[$i % count($commentaires)]);
                
                // Dates d'avis dans le passé (après l'événement)
                $dateAvis = (clone $evenement->getDate())->modify('+' . rand(1, 10) . ' days');
                $avis->setCreerLe($dateAvis);
                
                $avis->setUtilisateur($utilisateurs[$i % count($utilisateurs)]);
                $avis->setEvenement($evenement);
                
                // Les 2 premiers avis sont acceptés, les autres en attente
                $avis->setAccepter($i < 2);
                
                $manager->persist($avis);
            }
        }

        $manager->flush();

        echo "\n✅ Données de test chargées avec succès !\n";
        echo "📊 Résumé :\n";
        echo "   - 1 Administrateur\n";
        echo "   - 3 Responsables\n";
        echo "   - 5 Utilisateurs\n";
        echo "   - " . count($evenements) . " Événements\n";
        echo "   - Environ " . (count($evenements) * 4) . " Avis\n\n";
    }
}