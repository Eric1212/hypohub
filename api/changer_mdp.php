<?php
/**
 * Hypohub — API : définition du vrai mot de passe après un mot de passe
 * temporaire (généré par un employé).
 *
 * POST JSON {email, password, nouveau, csrf} → 200 {ok:true}
 * Le flag mdp_temporaire est levé, la session est ouverte proprement.
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

json_response(auth_changer_mdp(
    isset($in['email']) ? $in['email'] : '',
    isset($in['password']) ? $in['password'] : '',
    isset($in['nouveau']) ? $in['nouveau'] : ''
));
