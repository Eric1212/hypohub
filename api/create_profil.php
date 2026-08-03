<?php
/**
 * Hypohub — API : création d'un profil propriétaire.
 *
 * POST JSON {nom_complet, telephone, courriel, situation_emploi,
 *            revenu_annuel, csrf} → 200 {ok:true} | {ok:false, error}
 * Session requise.
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

if (empty($u['acces_proprietaire'])) {
    json_response(array('ok' => false, 'error' => t('create.error.access')), 403);
}

$nom_complet = trim(isset($in['nom_complet']) ? $in['nom_complet'] : '');
if ($nom_complet === '') {
    json_response(array('ok' => false, 'error' => t('create.error.required')));
}

$emploi = isset($in['situation_emploi']) ? $in['situation_emploi'] : 'salaire';
if (!in_array($emploi, array('salaire', 'travailleur_autonome', 'autre'), true)) {
    $emploi = 'salaire';
}

$revenu = isset($in['revenu_annuel']) && $in['revenu_annuel'] !== '' ? (float) $in['revenu_annuel'] : null;
if ($revenu !== null && $revenu < 0) {
    $revenu = null;
}

$st = db()->prepare(
    'INSERT INTO profils_proprietaire (cree_par, nom_complet, telephone, courriel, situation_emploi, revenu_annuel)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$st->execute(array(
    (int) $u['id'],
    $nom_complet,
    trim(isset($in['telephone']) ? $in['telephone'] : '') ?: null,
    trim(isset($in['courriel']) ? $in['courriel'] : '') ?: null,
    $emploi,
    $revenu,
));

json_response(array('ok' => true));
