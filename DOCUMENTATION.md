# DOCUMENTATION.md — Système de Détection de Plagiat (PlagDetect)

## Table des Matières

1. [Installation](#installation)
2. [Architecture](#architecture)
3. [Algorithmes de Détection](#algorithmes-de-détection)
4. [Services et Fonctions Clés](#services-et-fonctions-clés)
5. [Paramètres Ajustables](#paramètres-ajustables)
6. [Flux de Fonctionnement](#flux-de-fonctionnement)
7. [Base de Données](#base-de-données)
8. [Personnalisation](#personnalisation)

---

## Installation

### Prérequis
- PHP 8.2+
- PostgreSQL 14+
- Composer
- Extension PHP `zip`
- Extension PHP `pgsql`

### Étapes

```bash
# 1. Cloner le projet
git clone <repo-url>
cd plagiarism-detector

# 2. Installer les dépendances
composer install

# 3. Copier et configurer l'environnement
cp .env.example .env
php artisan key:generate

# 4. Configurer la base de données PostgreSQL dans .env
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=plagiarism_detector
# DB_USERNAME=postgres
# DB_PASSWORD=your_password

# 5. Créer la base de données
createdb plagiarism_detector

# 6. Exécuter les migrations et le seeder
php artisan migrate
php artisan db:seed

# 7. Créer le dossier de stockage
php artisan storage:link
mkdir -p storage/app/submissions
mkdir -p storage/app/temp

# 8. Lancer le serveur
php artisan serve

# 9. (Optionnel) Lancer le worker pour les jobs en arrière-plan
php artisan queue:work