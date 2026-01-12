# Système de Notation d'Événements - Eventify


## 📋 Description du projet

Application web de gestion de notation d'événements avec système de rôles et modération.

## 🛠️ Technologies utilisées

- **Framework**: Symfony 7.0
- **Base de données**: MySQL/MariaDB
- **Architecture**: MVC
- **Authentification**: Symfony Security Bundle
- **ORM**: Doctrine
- **Interface Admin**: EasyAdmin 

## 👥 Rôles utilisateurs

### 1. Utilisateur standard (ROLE_USER)
- Consulter les événements
- Publier des avis
- Modifier ses propres avis

### 2. Responsable d'événement (ROLE_RESPONSABLE)
- Toutes les fonctionnalités utilisateur
- Gérer ses événements
- Modérer les avis de ses événements

### 3. Administrateur (ROLE_ADMIN)
- Accès complet
- Gérer tous les utilisateurs
- Gérer tous les événements
- Modérer tous les avis

## 📦 Installation

### Prérequis
- PHP 8.2 ou supérieur
- Composer
- MySQL/MariaDB
- Serveur web (Apache/Nginx)

### Étapes d'installation

1. **Cloner le projet**
```bash
git clone https://github.com/CDWFS26-010/Relais-Ch-teaux
cd relais_chateaux
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer la base de données**
```bash
# Éditer le fichier .env
DATABASE_URL="mysql://root:@127.0.0.1:3306/relais_chateaux"
```

4. **Créer la base de données**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Charger les données de test (fixtures)**
```bash
php bin/console doctrine:fixtures:load
```

6. **Lancer le serveur**
```bash
symfony server:start
# ou
php -S localhost:8000 -t public/
```

## 🔐 Identifiants de test

### Administrateur
- **Email**: admin@test.com
- **Mot de passe**: admin123

### Responsable d'événement
- **Email**: responsable1@test.com
- **Mot de passe**: resp123

### Utilisateur standard
- **Email**: user1@test.com
- **Mot de passe**: user123

## 📁 Structure du projet

```
relais_chateaux/
├── config/              # Configuration Symfony
├── src/
│   ├── Controller/      # Contrôleurs
│   ├── DataFixtures/    # DataFixtures
│   ├── Entity/          # Entités Doctrine
│   ├── Form/            # Formulaires
│   ├── Repository/      # Repositories
│   └── Security/        # Authentification
├── templates/           # Vues Twig
├── public/              # Fichiers publics
└── var/                 # Cache et logs
```






 N° matricule: CDWFS-010
