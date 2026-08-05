<?php
/**
 * Hypohub — API : mise à jour d'un profil propriétaire (même formulaire que la création).
 *
 * POST JSON {profil_id, prenom, nom, date_naissance, courriel, telephone, app,
 *            adresse, ville, code_postal, province, nom_compagnie, neq, statut, csrf}
 * → 200 {ok:true, id} | {ok:false, error}
 * Session requise + accès propriétaire ; le profil doit appartenir (cree_par).
 * Les documents en staging du compte se rattachent au profil comme en création.
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

// --- Profil : existe + appartient à l'utilisateur ---
$profil_id = isset($in['profil_id']) ? (int) $in['profil_id'] : 0;
$st = db()->prepare('SELECT id FROM profils_proprietaire WHERE id = ? AND cree_par = ?');
$st->execute(array($profil_id, (int) $u['id']));
if (!$st->fetchColumn()) {
    json_response(array('ok' => false, 'error' => t('create.error.profil')), 404);
}

// --- Champs requis (mêmes règles que la création) ---
$prenom = trim(isset($in['prenom']) ? $in['prenom'] : '');
$nom    = trim(isset($in['nom']) ? $in['nom'] : '');
$naissance = trim(isset($in['date_naissance']) ? $in['date_naissance'] : '');
$courriel  = trim(isset($in['courriel']) ? $in['courriel'] : '');
$telephone = trim(isset($in['telephone']) ? $in['telephone'] : '');
$adresse   = trim(isset($in['adresse']) ? $in['adresse'] : '');
$ville     = trim(isset($in['ville']) ? $in['ville'] : '');
$code_postal = trim(isset($in['code_postal']) ? $in['code_postal'] : '');
$province    = trim(isset($in['province']) ? $in['province'] : 'QC');

if ($prenom === '' || $nom === '') {
    json_response(array('ok' => false, 'error' => t('create.error.required')));
}
if ($naissance === '') {
    json_response(array('ok' => false, 'error' => t('create.error.required')));
}
$test = DateTime::createFromFormat('Y-m-d', $naissance);
if (!$test || $test->format('Y-m-d') !== $naissance || $test > new DateTime()) {
    json_response(array('ok' => false, 'error' => t('create.error.naissance')));
}
if ($courriel === '' || !filter_var($courriel, FILTER_VALIDATE_EMAIL)) {
    json_response(array('ok' => false, 'error' => t('create.error.required')));
}
if ($telephone === '') {
    json_response(array('ok' => false, 'error' => t('create.error.required')));
}
if ($adresse === '' || $ville === '') {
    json_response(array('ok' => false, 'error' => t('create.error.required')));
}
$code_postal = strtoupper(str_replace(' ', '', $code_postal));
if ($code_postal === '' || !preg_match('/^[ABCEGHJ-NPRSTVXY]\d[ABCEGHJ-NPRSTVXY]\d[ABCEGHJ-NPRSTVXY]\d$/', $code_postal)) {
    json_response(array('ok' => false, 'error' => t('create.error.codepostal')));
}
$province = strtoupper($province);
if ($province === '') {
    $province = 'QC';
}

$app = trim(isset($in['app']) ? $in['app'] : '') ?: null;
$nom_compagnie = trim(isset($in['nom_compagnie']) ? $in['nom_compagnie'] : '') ?: null;
$neq = trim(isset($in['neq']) ? $in['neq'] : '') ?: null;
$statut = isset($in['statut']) && $in['statut'] !== '' ? $in['statut'] : null;
if ($statut !== null && !in_array($statut, array('citoyen', 'residant_permanent'), true)) {
    $statut = null;
}
if ($neq !== null && !preg_match('/^\d{9,10}$/', $neq)) {
    json_response(array('ok' => false, 'error' => t('create.error.neq')));
}

$st = db()->prepare(
    'UPDATE profils_proprietaire SET
        prenom = ?, nom = ?, date_naissance = ?, courriel = ?, telephone = ?, app = ?,
        adresse = ?, ville = ?, code_postal = ?, province = ?, nom_compagnie = ?, neq = ?, statut = ?
     WHERE id = ?'
);
$st->execute(array(
    $prenom,
    $nom,
    $naissance,
    $courriel,
    $telephone,
    $app,
    $adresse,
    $ville,
    $code_postal,
    $province,
    $nom_compagnie,
    $neq,
    $statut,
    $profil_id,
));

// Rattachement automatique des documents en staging (même logique création)
$st = db()->prepare(
    'UPDATE documents SET profil_id = ?
     WHERE user_id = ? AND profil_id IS NULL'
);
$st->execute(array($profil_id, (int) $u['id']));

json_response(array('ok' => true, 'id' => $profil_id));