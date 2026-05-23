# Système de Détection de Plagiat

## Description
Application de détection de similarité entre projets étudiants permettant de filtrer les fichiers, générer des empreintes numériques (fingerprints) et calculer un score de plagiat entre les soumissions. Le système persiste les résultats pour une consultation instantanée sans recalcul.

## Contexte
- Projet réalisé dans le cadre de ma formation en développement
- Sujet : Analyse automatique de code source pour la détection de copies
- Travail en individuel

## Ce que j'ai fait
- Conception et création de la base de données PostgreSQL avec le schéma complet
- Développement du moteur de calcul d'empreintes avec algorithme de hachage glissant (Winnowing)
- Implémentation du filtrage configurable par extension et nom de fichier
- Concaténation automatique des fichiers valides avant analyse
- Calcul du score de similarité (0 % à 100 %) et stockage dans `plagiarism_results`
- Suppression et recalcul automatique des empreintes lors d'une nouvelle upload

## Technologies utilisées

| Catégorie | Technologies |
|------------|--------------|
| Back-end | Laravel 11 |
| Front-end| Blade|
| Base de données | PostgreSQL 16 |
| Algorithmique | Winnowing, Rabin-Karp, Hachage MD5 |

## Fonctionnalités

- Filtrage des extensions autorisées (définies par le professeur)
- Exclusion de fichiers par nom spécifique
- Concaténation de tous les fichiers valides d'une soumission en une seule chaîne
- Génération des empreintes avec hash_value, submission_id et position dans le texte
- Suppression et recalcul des anciennes empreintes en cas de ré-upload
- Calcul du score final entre 0 % (aucun point commun) et 100 % (copie conforme)
- Stockage des résultats pour consultation instantanée

## Installation

```bash
# 1. Cloner le projet
git clone https://github.com/maminiainafita123-art/Gestion-de-plagiat-.git
cd plagiarism-detector

# 2. Créer la base de données PostgreSQL
psql -U postgres -c "CREATE DATABASE plagiarism;"

# 3. Importer le schéma et les données
psql -U postgres -d plagiarism -f src/main/resources/sql/01_schema.sql
psql -U postgres -d plagiarism -f src/main/resources/sql/02_data.sql

# 4. Installer les dépendances
pip install -r requirements.txt

# 5. Lancer l'application
python app.py