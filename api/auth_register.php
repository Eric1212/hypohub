<?php
/**
 * Hypohub — API : création de compte.
 *
 * POST JSON {nom_complet, email, password, acces_proprietaire,
 *            acces_creancier, csrf} → 200 {ok:true} | {ok:false, error}
 * Un seul accès au minimum ; le courtier coche les deux.
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

if (auth_user()) {
    json_response(array('ok' => true)); // déjà connecté
}

json_response(auth_register(
    isset($in['nom_complet']) ? $in['nom_complet'] : '',
    isset($in['email']) ? $in['email'] : '',
    isset($in['password']) ? $in['password'] : '',
    array(
        'proprietaire' => !empty($in['acces_proprietaire']),
        'creancier'    => !empty($in['acces_creancier']),
    )
));
