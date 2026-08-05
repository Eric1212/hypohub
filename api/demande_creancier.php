<?php
/**
 * Hypohub — API : demande pour devenir créancier.
 *
 * POST JSON {justification, csrf} → 200 {ok:true}
 * Session requise. L'utilisateur décrit pourquoi il a besoin de l'accès
 * créancier (modale « Demande d'accès Zone Créancier »). Un employé le
 * recontacte si une information manque. La demande 2000 mots max.
 * Une demande existante en_attente ne peut pas être redéposée.
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

if (!empty($u['acces_creancier'])) {
    json_response(array('ok' => false, 'error' => t('account.error.deja_creancier')), 403);
}

// Une demande déjà en attente bloque un redépôt
if ($u['demande_creancier_statut'] === 'en_attente') {
    json_response(array('ok' => false, 'error' => t('account.error.deja_demande')), 403);
}

$justif = trim(isset($in['justification']) ? $in['justification'] : '');
if ($justif === '') {
    json_response(array('ok' => false, 'error' => t('account.error.justif_vide')));
}

// Limite : 2000 mots (séparés par espacements)
$mots = preg_split('/\s+/u', $justif, -1, PREG_SPLIT_NO_EMPTY);
if (count($mots) > 2000) {
    json_response(array('ok' => false, 'error' => t('account.error.justif_mots')));
}

$st = db()->prepare(
    "UPDATE users
        SET demande_creancier_justification = ?,
            demande_creancier_statut = 'en_attente',
            demande_creancier_date = NOW()
      WHERE id = ?"
);
$st->execute(array($justif, (int) $u['id']));

json_response(array('ok' => true));