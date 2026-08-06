<?php
/**
 * Hypohub — API : certificat AMF (Zone compte).
 *
 * Cousin de demande_creancier.php : une « demande d'activation » soumise à un
 * humain. Actions (champ `action`) :
 *   - 'demander' : met à jour certificat_amf ET passe le statut en_attente
 *                 (demande de vérification par un employé)
 *   - 'retirer'  : révocation immédiate d'un certificat validé (le nouveau n°
 *                 repasse à vide, l'accès courtier/créancier tombe, la demande
 *                 doit être refaite à zéro)
 *
 * NB : la simple sauvegarde du n° (autosave) passe par api/update_compte.php.
 * Session requise. Accessible depuis Zone Propriétaire ET Zone Créancier.
 */
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(array('ok' => false, 'error' => 'Méthode non autorisée'), 405);
}

$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) {
    $in = array();
}

if (!csrf_check(isset($in['csrf']) ? $in['csrf'] : '')) {
    json_response(array('ok' => false, 'error' => t('auth.error.csrf')), 403);
}

$u = auth_user();
if (!$u) {
    json_response(array('ok' => false, 'error' => t('auth.error.invalid')), 401);
}

$action = isset($in['action']) ? $in['action'] : '';

switch ($action) {
    case 'demander':
        $num = trim(isset($in['certificat_amf']) ? $in['certificat_amf'] : '');
        if ($num === '') {
            json_response(array('ok' => false, 'error' => t('account.error.amf_vide')));
        }
        $st = db()->prepare(
            "UPDATE users SET certificat_amf = ?, certificat_amf_statut = 'en_attente' WHERE id = ?"
        );
        $st->execute(array($num, (int) $u['id']));
        break;

    case 'retirer':
        // Révocation d'un certificat validé : l'accès tombe, demande à refaire.
        $st = db()->prepare(
            "UPDATE users
                SET certificat_amf_statut = 'vide',
                    acces_creancier = 0,
                    demande_creancier_statut = 'aucune'
              WHERE id = ?"
        );
        $st->execute(array((int) $u['id']));
        break;

    default:
        json_response(array('ok' => false, 'error' => t('account.error.vide')));
}

json_response(array('ok' => true));
