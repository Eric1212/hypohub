<?php
/**
 * Hypohub — API : certificat AMF (Zone compte).
 *
 * Actions (champ `action`) :
 *   - 'enregistrer' : met à jour certificat_amf (aucune demande de vérif)
 *   - 'demander'    : met à jour certificat_amf ET passe le statut en_attente
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

$action = isset($in['action']) ? $in['action'] : 'enregistrer';
if (!in_array($action, array('enregistrer', 'demander'), true)) {
    json_response(array('ok' => false, 'error' => t('account.error.vide')));
}

$num = trim(isset($in['certificat_amf']) ? $in['certificat_amf'] : '');
// Format flexible : chiffres, lettres (A-F), tirets — 4 à 32 caractères
if ($num === '' || !preg_match('/^[A-Za-z0-9-]{4,32}$/', $num)) {
    json_response(array('ok' => false, 'error' => t('account.error.amf')));
}

$statut = ($action === 'demander') ? 'en_attente' : 'vide';
$st = db()->prepare(
    "UPDATE users SET certificat_amf = ?, certificat_amf_statut = ? WHERE id = ?"
);
$st->execute(array($num, $statut, (int) $u['id']));

json_response(array('ok' => true));