<?php
/**
 * Hypohub — API : création d'un dossier d'emprunt.
 *
 * POST JSON {profil_proprietaire_id, profil_propriete_id, montant_demande,
 *            rang, type_financement, csrf} → 200 {ok:true} | {ok:false, error}
 * Session requise. Le profil et la propriété référencés doivent appartenir
 * à l'utilisateur connecté (vérification de propriété côté serveur).
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

$profil_id   = isset($in['profil_proprietaire_id']) ? (int) $in['profil_proprietaire_id'] : 0;
$propriete_id = isset($in['profil_propriete_id']) ? (int) $in['profil_propriete_id'] : 0;
$montant      = isset($in['montant_demande']) && $in['montant_demande'] !== '' ? (float) $in['montant_demande'] : 0;

if ($profil_id <= 0 || $propriete_id <= 0 || $montant <= 0) {
    json_response(array('ok' => false, 'error' => t('create.error.required')));
}

// Le profil propriétaire doit appartenir à l'utilisateur.
$st = db()->prepare('SELECT id FROM profils_proprietaire WHERE id = ? AND cree_par = ?');
$st->execute(array($profil_id, (int) $u['id']));
if (!$st->fetch()) {
    json_response(array('ok' => false, 'error' => t('create.error.profil')));
}

// La propriété doit appartenir à l'utilisateur.
$st = db()->prepare('SELECT id FROM profils_propriete WHERE id = ? AND cree_par = ?');
$st->execute(array($propriete_id, (int) $u['id']));
if (!$st->fetch()) {
    json_response(array('ok' => false, 'error' => t('create.error.propriete')));
}

$rang = isset($in['rang']) ? $in['rang'] : 'premier';
if (!in_array($rang, array('premier', 'deuxieme'), true)) {
    $rang = 'premier';
}

$type = isset($in['type_financement']) ? $in['type_financement'] : 'travailleur_autonome';
if (!in_array($type, array('travailleur_autonome', 'consolidation', 'deuxieme_rang', 'delai_serre'), true)) {
    $type = 'travailleur_autonome';
}

$st = db()->prepare(
    'INSERT INTO dossiers_emprunt (cree_par, profil_proprietaire_id, profil_propriete_id, montant_demande, rang, type_financement, statut)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$st->execute(array(
    (int) $u['id'],
    $profil_id,
    $propriete_id,
    $montant,
    $rang,
    $type,
    'nouveau',
));

json_response(array('ok' => true));
