# Hypohub

**Le guichet unique du financement hypothécaire privé au Québec.**

Hypohub met en relation les emprunteurs que les banques refusent avec des
prêteurs privés et des courtiers hypothécaires.

- **Monétisation** : 25 pdb (0,25 %) sur la valeur de l'hypothèque, facturée au
  créancier/courtier à l'enregistrement.
- **Licence** : MIT — logiciel libre, à installer et personnaliser partout.

## C'est quoi, en une phrase ?

Un petit site web que tu installes sur ton hébergement, qui gère des demandes
de financement et met en relation emprunteurs et prêteurs.

## Ce qu'il te faut

- Un hébergement web avec **PHP 7.4 ou plus** et **MySQL**.
  (Tous les hébergements à ~1-2 $/mois : Hostinger, Namecheap, o2switch…)
- Le dossier Hypohub — télécharge ce repo en ZIP (bouton vert « Code »
  → « Download ZIP »).

## Installation en 5 étapes

1. **Télécharge** Hypohub : bouton vert « Code » → « Download ZIP ».
2. **Décompresse** le ZIP chez toi.
3. **Crée une base de données MySQL** dans le panneau de ton hébergement
   (cPanel / hPanel : section « Bases de données »). Note le nom, l'usager
   et le mot de passe.
4. **Mets les fichiers en ligne** : dans le gestionnaire de fichiers du
   panneau, va dans `public_html` (ou `www`) et dépose *tout le contenu*
   du dossier Hypohub dedans.
5. **Ouvre ton site** (ex. `http://tondomaine.com`). Tu arrives sur
   l'assistant d'installation : remplis l'hôte (souvent `localhost`), le nom
   de la base, l'usager et le mot de passe → clique **Installer**.

C'est tout. Si les informations sont bonnes, Hypohub crée ses tables tout
seul et affiche la page d'accueil. Si tu reviens sur `config.php` plus tard,
il refusera de s'exécuter tant que la configuration est valide.

## Personnaliser

- **Le texte** : les phrases visibles sont dans `lang/fr.php` et `lang/en.php`.
  Modifie une phrase, sauvegarde, c'est en ligne.
- **L'apparence** : les couleurs et la mise en page sont dans
  `static/css/style.css`.
- **Les pages** : chaque page est un fichier dans `views/`, routé par
  `index.php?page=<slug>` (accueil, proprietaire, courtier, creancier,
  a-propos, contact, espace).
- **Les actions** : chaque action (endpoint AJAX) est un fichier dans `api/`
  qui répond en JSON, appelé par le JavaScript dans `static/js/`.

## Structure

```
hypohub/
├── index.php              → page d'entrée (vérifie la config, sert les pages)
├── config.php             → assistant d'installation (un seul usage)
├── api/                   → endpoints AJAX (réponse JSON)
├── lib/                   → code partagé (base de données, config, i18n…)
│   ├── config.example.php → modèle de configuration
│   └── config.local.php   → ta configuration (créée par l'installateur)
├── lang/                  → traductions (fr.php, en.php)
├── views/                 → pages et fragments (partials/)
└── static/                → fichiers CSS et JS
```

## Sécurité

- `lib/config.local.php` contient ton mot de passe MySQL : il est protégé par
  le `.gitignore` (jamais envoyé sur GitHub) et normalement non visible en
  ligne (les fichiers `config*.php` ne sont pas servis comme texte par PHP).
