# CLAUDE.md — Hypohub

Règles de travail applicables à ce dépôt. Elles prévalent sur toute autre
considération. Ce fichier décrit **le projet et ses décisions** ; la mémoire
(`~/.claude/projects/-home-eric-dev-hypohub/memory/MEMORY.md`) décrit
l'outillage et les standards de travail.

## Projet

Hypohub — guichet unique de référencement de financements hypothécaires privés au Québec.

- **Métier** : mise en relation entre emprunteurs (1er/2e rang, cas refusés par
  les banques) et prêteurs privés / courtiers hypothécaires.
- **Monétisation** : commission de 25 pdb (0,25 %) sur la valeur de l'hypothèque
  (pas le solde ni la valeur de la propriété), facturée au créancier/courtier au
  moment de l'enregistrement au Registre foncier.
- **Véhicule** : Financière B&A Inc. (dormante, NEQ 1177249589) — domaine corporate
  `bafinanciere.ca`. Domaine de la plateforme : `hypohub.ca` (à réserver).
- **Marché** : Québec. Prêteurs hypothécaires privés (alternatifs) et courtiers.

## Décisions produit (2026-08-02 et après)

- **Terminologie** : on parle de **propriétaire** (emprunteur), **courtier**,
  **créancier** (prêteur). Pas de « plateforme » : on martèle **« guichet
  unique »** partout. Libellés persona : « Je suis propriétaire / courtier /
  créancier ». Slugs internes : `proprietaire`, `courtier`, `creancier`.
- **Commission** : la facturation 25 pdb est gérée **hors guichet** (outil
  interne séparé) — pas de tables `commissions`/`financements` dans le guichet.
- **Périmètre** : le guichet s'arrête au référencement ; pas d'estimation de
  commission dans le guichet, pas de numéro d'enregistrement au Registre.

## Profil propriétaire (2026-08-05)

- **Un seul profil, table unique, pas de champ `type`.** Le classement IDV/INC
  est dérivé : NEQ présent → société (INC) ; nom compagnie seul → société ;
  sinon → individu (IDV). Pas de personne de liaison : prénom/nom = le
  représentant si INC, la personne si IDV.
- **Obligatoires** : prénom, nom, date de naissance (toujours, même INC —
  le représentant est une personne physique), courriel, téléphone, adresse
  (porte : numéro + rue), ville, code postal (A1A 1A1), province (QC par défaut).
- **Optionnels** : app (logement), nom compagnie, NEQ, statut
  (citoyen / résident permanent).
- **Exclus** : province d'incorporation (retirée), NAS (inutile).

## Stack

- **Hébergement** : partagé 1-2 $/mois (PHP + MySQL) — un adolescent doit pouvoir l'installer.
- **Langage** : PHP 8, sans framework (vanilla), zéro dépendance.
- **Base de données** : MySQL (cPanel/hPanel + phpMyAdmin).
- **Pattern** : AJAX type regioncities — `index.php` front controller,
  `api/<action>.php` (JSON), `lib/` partagé, `views/` + `partials/`, `static/`
  CSS/JS, `lang/` i18n **fr et en dès le départ**.
- **Config** : `lib/config.local.php` (installeur, gitignoré) + `config.example.php`.
- **Installeur** : `config.php` test MySQL → écrit config.local → crée les tables.
- **Schéma** : 7 tables métier (`users` + 2 accès booléens, `profils_proprietaire`,
  `profils_propriete`, `dossiers_emprunt`, `profils_creancier`,
  `offres_financement`, `acceptations`) + `app_meta` pour `schema_version`.
- **Police** : Inter (OFL, self-hostée), mapping de graisses validé en 2026-08-02.
- **Pas de GitHub Pages.**

## Conventions

- Référentiel du modèle d'affaires : courriel Éric (SADC Shawinigan, commission 25 pdb).
- Tout état/décision du projet se retrouve dans ce fichier ; jamais dans la mémoire externe.

## Memory & Personal Context

- La mémoire persistante vit hors du repo, dans
  `~/.claude/projects/-home-eric-dev-hypohub/memory/` (index `MEMORY.md`).
- Lire ce fichier avant d'assumer une convention ou préférence d'Éric.

You MUST LOAD `~/.claude/projects/-home-eric-dev-hypohub/memory/MEMORY.md`
PERMANENTLY.