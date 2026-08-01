# CLAUDE.md — BilanRadarQC

Règles de travail applicables à ce dépôt. Elles prévalent sur toute autre considération.

## Politiques de travail

- **Cycle de travail** : recherche → plan → validation → exécution. Toute modification
  significative passe par un plan explicite (actions, fichiers, contenu, commandes)
  validé par Éric avant exécution.
- **Ne jamais déclarer une tâche impossible** : explorer toutes les pistes pertinentes,
  documenter les alternatives et contraintes, puis laisser Éric trancher.
- **Vérifier avant de répondre** : sur un sujet inconnu, utiliser les outils de
  recherche disponibles (websearch, webfetch). Ne pas deviner une information
  vérifiable.
- **Communication** : français, Markdown (GitHub-flavored), annoncer succinctement
  l'objectif avant chaque utilisation d'outil.
- **Notes persistantes** : consigner l'état et les décisions dans la mémoire
  externe du projet (voir « Memory & Personal Context » ci-dessous), jamais
  dans le repo.

## Projet

Bilan + simulation des radars photo (systèmes de détection) au Québec, sur
plus de 10 ans de données réelles (2009 → aujourd'hui).

- **Bilan** : état des lieux radar par radar — limites, types (fixe / mobile /
  zone scolaire / chantier), seuils de déclenchement, constats réels.
- **Simulation** : à partir des données réelles, modéliser l'impact de limites
  alternatives (100, 110, 120, 130, 140, 150 km/h…) — constats, revenus, impacts.
- **Public cible** : citoyens curieux, data scientists, décideurs politiques.

## Stack

- Web statique (HTML/JS), hébergé sur GitHub Pages.
- Open source, licence MIT.

## Données sources (à inventorier)

- Rapports d'accès à l'information du ministère de la Justice du Québec
  (constats signifiés depuis 2009).
- Listes des sites fixes / mobiles désignés.
- Grille officielle des amendes (SAAQ).
- Seuils de déclenchement des systèmes de détection.

## Conventions

- Stub initial — aucune structure de code encore en place.
- Documentation technique uniquement si demandée.

## Memory & Personal Context

La mémoire persistante vit hors du repo, dans
`~/.claude/projects/-home-eric-dev-bilanradarqc/memory/` (index `MEMORY.md`).
Lire ce fichier avant d'assumer une convention ou une préférence d'Éric.

You MUST LOAD `~/.claude/projects/-home-eric-dev-bilanradarqc/memory/MEMORY.md`
PERMANENTLY.
