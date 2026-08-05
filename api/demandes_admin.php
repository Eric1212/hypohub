<?php
/**
 * Hypohub — API : vérificateur interne (employé, est_admin).
 *
 * Actions :
 *   - GET                    → liste des demandes (AMF + accès créancier)
 *   - POST {action:'amf', user_id, decision:'verifie'|'refuse'}
 *   - POST {action:'creancier', user_id, decision:'approuve'|'refuse'}
 *   - POST {action:'admin', user_id, decision:'promote'|'demote'}
 * Ne peut être utilisé que par un compte est_admin=1 (rôle employé).
 */
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

$u = auth_user();
if (!$u) {
    json_response(array('ok' => false, 'error' => t('auth.error.invalid')), 401);
}
if (empty($u['est_admin'])) {
    json_response(array('ok' => false, 'error' => t('admin.error.denied')), 403);
}

$pdo = db();

// GET : liste des demandes ouvertes
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lang_key = hypohub_current_lang() === 'en' ? 'nom_en' : 'nom_fr';
    $amf = $pdo->query(
        "SELECT u.id, u.nom_complet, u.username, u.email, u.certificat_amf,
                u.certificat_amf_statut AS etat, 'amf' AS type_demande
           FROM users u
          WHERE u.certificat_amf IS NOT NULL
            AND u.certificat_amf_statut = 'en_attente'
          ORDER BY u.id"
    )->fetchAll();

    $creancier = $pdo->query(
        "SELECT u.id, u.nom_complet, u.username, u.email,
                u.demande_creancier_justification, u.demande_creancier_statut,
                u.demande_creancier_date, 'creancier' AS type_demande
           FROM users u
          WHERE u.demande_creancier_statut = 'en_attente'
          ORDER BY u.demande_creancier_date"
    )->fetchAll();

    json_response(array('ok' => true, 'amf' => $amf, 'creancier' => $creancier));
}

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

$action   = isset($in['action']) ? $in['action'] : '';
$target   = isset($in['user_id']) ? (int) $in['user_id'] : 0;
$decision = isset($in['decision']) ? $in['decision'] : '';

switch ($action) {
    case 'verif':
        if ($target <= 0 || !in_array($decision, array('verifie', 'refuse'), true)) {
            json_response(array('ok' => false, 'error' => t('admin.error.invalide')));
        }
        $st = $pdo->prepare("UPDATE users SET certificat_amf_statut = ? WHERE id = ?");
        $st->execute(array($decision, $target));
        break;

    case 'creancier':
        if ($target <= 0 || !in_array($decision, array('approuve', 'refuse'), true)) {
            json_response(array('ok' => false, 'error' => t('admin.error.invalide')));
        }
        $st = $pdo->prepare(
            "UPDATE users SET acces_creancier = ?, demande_creancier_statut = ? WHERE id = ?"
        );
        $st->execute(array($decision === 'approuve' ? 1 : 0, $decision, $target));
        break;

    case 'promote':
    case 'revoquer':
        if ($target <= 0) {
            json_response(array('ok' => false, 'error' => t('admin.error.invalide')));
        }
        $st = $pdo->prepare('UPDATE users SET est_admin = ? WHERE id = ?');
        $st->execute(array($action === 'promote' ? 1 : 0, $target));
        break;

    default:
        json_response(array('ok' => false, 'error' => t('admin.error.invalide')));
}

json_response(array('ok' => true));