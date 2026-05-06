# RoomBooking

Application web de réservation de salles pour établissement scolaire (MediaSchool), développée avec **Symfony 7.3**.

---

## Fonctionnalités

- Réservation de salles avec détection des conflits (chevauchements)
- Annulation douce des réservations (soft-delete via `cancelledAt`)
- Gestion des équipements associés aux salles (vidéoprojecteur, wifi, etc.)
- Gestion des promotions (BTS SIO 1ère et 2ème année)
- Contrôle d'accès par rôles : Admin, Coordinateur, Étudiant
- Envoi d'email de confirmation à la réservation
- Interface d'administration complète

---

## Rôles utilisateurs

| Rôle | Accès |
|------|-------|
| `ROLE_ADMIN` | Gestion complète : utilisateurs, salles, équipements, réservations |
| `ROLE_COORDINATOR` | Gestion des promotions et des étudiants |
| `ROLE_STUDENT` | Création et consultation de ses propres réservations |

---

## Stack technique

- **PHP** >= 8.2
- **Symfony** 7.3
- **Doctrine ORM** 3.x
- **MariaDB** 10.11
- **Twig** (templating)
- **Apache** (serveur web)
- **Docker** (déploiement)

---

## Installation

### Prérequis

- PHP >= 8.2
- Composer
- MySQL / MariaDB
- Docker (optionnel)

### Installation locale

```bash
# Cloner le projet
git clone https://github.com/YnsHlf212/RoomBooking.git
cd RoomBooking

# Installer les dépendances
composer install

# Configurer l'environnement
cp .env .env.local
# Éditer .env.local avec vos paramètres de base de données et mailer
```

Configurer la variable `DATABASE_URL` dans `.env.local` :

```
DATABASE_URL="mysql://user:password@127.0.0.1:3306/roombooking"
```

```bash
# Créer la base de données et exécuter les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# (Optionnel) Charger les données de test
php bin/console doctrine:fixtures:load

# Démarrer le serveur de développement
symfony server:start
```

### Avec Docker

```bash
# Copier et configurer les variables d'environnement Docker
cp .env.docker .env.local

# Lancer les conteneurs
docker-compose up -d
```

L'application sera accessible via le reverse proxy Traefik configuré dans `docker-compose.yml`.

---

## Données de test (fixtures)

Les fixtures créent automatiquement :

| Type | Données |
|------|---------|
| Promotions | BTS SIO 1ère année, BTS SIO 2ème année |
| Équipements | Vidéoprojecteur, Tableau blanc, Climatisation, PC fixe, Wifi |
| Salles | 4 salles préconfigurées avec équipements |
| Utilisateurs | 1 admin, 1 coordinateur, 1 étudiant |

---

## Structure du projet

```
src/
├── Controller/       # Contrôleurs (Admin, Reservation, Room, User, Coordinator…)
├── Entity/           # Entités Doctrine (User, Room, Reservation, Promotion, Equipment)
├── Form/             # Formulaires Symfony
├── Repository/       # Repositories Doctrine
├── Service/          # Services métier (MailService)
└── DataFixtures/     # Données de test
templates/            # Templates Twig
migrations/           # Migrations Doctrine
docs/                 # Documentation projet (cahier des charges, bilan…)
```

---

## Documentation

Les documents projet sont disponibles dans le dossier [`docs/`](docs/) :

- Cahier des charges
- Cahier de recette
- Documentation utilisateur
- MCD (Modèle Conceptuel de Données)
- Bilan de projet
