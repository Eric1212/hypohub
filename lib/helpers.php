<?php
/**
 * Hypohub — petites fonctions utilitaires partagées.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * La configuration est-elle en place et la connexion MySQL fonctionne-t-elle ?
 */
function config_ok() {
    if (!hypohub_config()) {
        return false;
    }
    try {
        $pdo = db();
        $pdo->query('SELECT 1');
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Répond en JSON et arrête le script.
 */
function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data);
    exit;
}

/**
 * Redirige vers une URL et arrête le script.
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Expiration automatique des dossiers (décision Éric, 2026-08-06) : tout
 * dossier resté 'nouveau' ou 'actif' pendant 90 jours sans activité passe au
 * statut 'expire' (fermé par le système). Appelée à chaque chargement de
 * l'espace membre — pas besoin de cron sur l'hébergement partagé.
 */
function expire_dossiers_inactifs() {
    try {
        db()->exec(
            "UPDATE dossiers_emprunt
                SET statut = 'expire'
              WHERE statut IN ('nouveau', 'actif')
                AND derniere_activite < (NOW() - INTERVAL 90 DAY)"
        );
    } catch (Exception $e) {
        // Silencieux : l'espace fonctionne même si le balayage échoue.
    }
}

/**
 * Statistiques du bandeau de preuve de l'accueil, calculées depuis la base.
 *
 * - demandes     : nombre de dossiers d'emprunt ACTIFS — statut 'nouveau' ou
 *                  'actif' (un dossier 'finance' est un résultat finalisé, plus
 *                  un dossier actif ; décision Éric 2026-08-06) — affiché par
 *                  la logique de palier d'Éric :
 *                    1. base = fenêtre au ratio/j le plus élevé ;
 *                    2. ratio < 1 partout → repli « 1 aujourd'hui » ;
 *                    3. cascade : chaque fenêtre à droite de la base a un
 *                       seuil ×1,98 (doublage toléré à 1 %, absorbe les
 *                       arrondis de calendrier) ;
 *                    4. la première fenêtre qui dépasse son seuil devient la
 *                       nouvelle base, on recalcule ses seuils, etc. ;
 *                    5. plus rien ne dépasse → la base est le résultat.
 * - periode      : 'jour' | 'semaine' | 'mois' | 'trimestre' | 'semestre'
 *                  | 'annee' retenue pour le libellé.
 * - creanciers   : créanciers seuls + courtiers (= comptes à accès créancier).
 * - proprietaires: propriétaires seuls + courtiers (= comptes à accès
 *                  propriétaire). Le courtier figure dans les DEUX stats
 *                  (accès créancier ET propriétaire), sans compter double.
 * - total        : somme des montants demandés (3 catégories) de la fenêtre
 *                  retenue par la stat 1 — le volume montré avec les demandes.
 * - total_global : même somme mais toutes dates confondues (cumul) ; l'accueil
 *                  affiche « total / total_global » en K$.
 *
 * Base indisponible → repli minimal (jamais de 0 affiché, page jamais cassée).
 */
function home_stats() {
    $stats = array(
        'demandes'      => 1,
        'periode'       => 'jour',
        'creanciers'    => 1,
        'proprietaires' => 1,
        'total'         => 0,
        'total_global'  => 0,
    );

    try {
        $pdo = db();

        // Stat 1 — demandes ACTIVES (nouveau/accepte/finalise), logique de palier.
        $periods = array(
            'jour'      => array(1,   'date_creation >= CURDATE()'),
            'semaine'   => array(7,   'date_creation >= (CURDATE() - INTERVAL 7 DAY)'),
            'mois'      => array(30,  'date_creation >= (CURDATE() - INTERVAL 30 DAY)'),
            'trimestre' => array(90,  'date_creation >= (CURDATE() - INTERVAL 90 DAY)'),
            'semestre'  => array(182, 'date_creation >= (CURDATE() - INTERVAL 182 DAY)'),
            'annee'     => array(365, 'date_creation >= (CURDATE() - INTERVAL 365 DAY)'),
        );
        $counts = array();
        $ratios = array();
        foreach ($periods as $p => $info) {
            $counts[$p] = (int) $pdo->query("SELECT COUNT(*) FROM dossiers_emprunt WHERE statut IN ('nouveau', 'actif') AND " . $info[1])->fetchColumn();
            $ratios[$p] = $counts[$p] / $info[0];
        }

        $base = 'jour';
        $repli = false;
        foreach ($periods as $p => $info) {
            if ($ratios[$p] > $ratios[$base]) {
                $base = $p;
            }
        }

        if ($ratios[$base] < 1) {
            // Ratio < 1 partout (activité trop faible ou absente) : repli.
            $stats['demandes'] = 1;
            $stats['periode']  = 'jour';
            $repli = true;
        } else {
            // Cascade : seuils ×1,98 vers la droite ; recalcul après montée.
            $keys = array_keys($periods);
            while (true) {
                $idx    = array_search($base, $keys);
                $seuil  = $counts[$base];
                $found  = false;
                for ($i = $idx + 1; $i < count($keys); $i++) {
                    $seuil *= 1.98;
                    if ($counts[$keys[$i]] >= $seuil) {
                        $base   = $keys[$i];
                        $found  = true;
                        break;
                    }
                }
                if (!$found) {
                    break;
                }
            }
            $stats['demandes'] = $counts[$base];
            $stats['periode']  = $base;
        }

        // Stat 2 — créanciers : créanciers seuls + courtiers.
        $stats['creanciers'] = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE acces_creancier = 1')->fetchColumn();

        // Stat 3 — propriétaires aidés : tous les comptes à accès propriétaire
        // (propriétaires seuls + courtiers ; le courtier figure aussi à la
        // stat 2 — il apparaît dans les deux stats, sans compter double).
        $stats['proprietaires'] = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE acces_proprietaire = 1')->fetchColumn();

        // Stat 4 — total en demandes de financement (3 catégories) :
        //   - total        : somme des montants de la fenêtre retenue par la
        //                    stat 1 (le volume que montre la stat 1) ;
        //   - total_global : somme cumulée toutes dates.
        // Affichage « X K$ / Y K$ » (option d'Éric). Au repli de la stat 1,
        // la fenêtre jour est vide → on montre le cumul des deux côtés.
        $stats['total_global'] = (float) $pdo->query("SELECT COALESCE(SUM(montant_demande), 0) FROM dossiers_emprunt WHERE statut IN ('nouveau', 'actif')")->fetchColumn();
        if ($repli) {
            $stats['total'] = $stats['total_global'];
        } else {
            $stats['total'] = (float) $pdo->query("SELECT COALESCE(SUM(montant_demande), 0) FROM dossiers_emprunt WHERE statut IN ('nouveau', 'actif') AND " . $periods[$base][1])->fetchColumn();
        }

        // Plancher « jamais 0 » sur les compteurs (base vide ou toute neuve).
        if ($stats['creanciers'] < 1) {
            $stats['creanciers'] = 1;
        }
        if ($stats['proprietaires'] < 1) {
            $stats['proprietaires'] = 1;
        }
    } catch (Exception $e) {
        // Base indisponible : on garde le repli minimal.
    }

    return $stats;
}
