<?php
/**
 * Hypohub — API : création d'une propriété.
 *
 * POST JSON {adresse, ville, code_postal, valeur_estimee, valeur_nette, csrf}
 * → 200 {ok:true} | {ok:false, error}
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

$adresse = trim(isset($in['adresse']) ? $in['adresse'] : '');
$ville   = trim(isset($in['ville']) ? $in['ville'] : '');
if ($adresse === '' || $ville === '') {
    json_response(array('ok' => false, 'error' => t('create.error.required')));
}

$val_estimee = isset($in['valeur_estimee']) && $in['valeur_estimee'] !== '' ? (float) $in['valeur_estimee'] : null;
$val_nette   = isset($in['valeur_nette']) && $in['valeur_nette'] !== '' ? (float) $in['valeur_nette'] : null;
if ($val_estimee !== null && $val_estimee < 0) {
    $val_estimee = null;
}
if ($val_nette !== null && $val_nette < 0) {
    $val_nette = null;
}

$st = db()->prepare(
    'INSERT INTO profils_propriete (cree_par, adresse, ville, code_postal, valeur_estimee, valeur_nette)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$st->execute(array(
    (int) $u['id'],
    $adresse,
    $ville,
    trim(isset($in['code_postal']) ? $in['code_postal'] : '') ?: null,
    $val_estimee,
    $val_nette,
));

json_response(array('ok' => true));
