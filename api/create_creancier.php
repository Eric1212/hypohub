<?php
/**
 * Hypohub — API : création d'un profil créancier.
 *
 * POST JSON {nom, type, capital_disponible, criteres, csrf}
 * → 200 {ok:true} | {ok:false, error}
 * Session requise (accès créancier).
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

if (empty($u['acces_creancier'])) {
    json_response(array('ok' => false, 'error' => t('create.error.access')), 403);
}

$nom = trim(isset($in['nom']) ? $in['nom'] : '');
if ($nom === '') {
    json_response(array('ok' => false, 'error' => t('create.error.required')));
}

$type = isset($in['type']) ? $in['type'] : 'individu';
if (!in_array($type, array('individu', 'societe'), true)) {
    $type = 'individu';
}

$capital = isset($in['capital_disponible']) && $in['capital_disponible'] !== '' ? (float) $in['capital_disponible'] : null;
if ($capital !== null && $capital < 0) {
    $capital = null;
}

$st = db()->prepare(
    'INSERT INTO profils_creancier (user_id, nom, type, capital_disponible, criteres)
     VALUES (?, ?, ?, ?, ?)'
);
$st->execute(array(
    (int) $u['id'],
    $nom,
    $type,
    $capital,
    trim(isset($in['criteres']) ? $in['criteres'] : '') ?: null,
));

json_response(array('ok' => true));
