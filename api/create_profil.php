<?php
/**
 * Hypohub — API : création d'un profil propriétaire.
 *
 * POST JSON {prenom, nom, date_naissance, courriel, telephone, app, adresse,
 *            ville, code_postal, province, nom_compagnie, neq, statut, csrf}
 * → 200 {ok:true} | {ok:false, error}
 * Session requise + accès propriétaire.
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

// --- Champs requis ---
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
// Date de naissance valide (AAAA-MM-JJ) et dans le passé
$naissance_dt = DateTime::createFromFormat('Y-m-d', $naissance);
if (!$naissance_dt || $naissance_dt->format('Y-m-d') !== $naissance || $naissance_dt > new DateTime()) {
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
// Code postal : A1A 1A1 (tolère minuscules/absence d'espace, normalise)
$code_postal = strtoupper(str_replace(' ', '', $code_postal));
if ($code_postal === '' || !preg_match('/^[ABCEGHJ-NPRSTVXY]\d[ABCEGHJ-NPRSTVXY]\d[ABCEGHJ-NPRSTVXY]\d$/', $code_postal)) {
    json_response(array('ok' => false, 'error' => t('create.error.codepostal')));
}
$province = strtoupper($province);
if ($province === '') {
    $province = 'QC';
}

// --- Champs optionnels ---
$app = trim(isset($in['app']) ? $in['app'] : '') ?: null;
$nom_compagnie = trim(isset($in['nom_compagnie']) ? $in['nom_compagnie'] : '') ?: null;
$neq = trim(isset($in['neq']) ? $in['neq'] : '') ?: null;
$statut = isset($in['statut']) && $in['statut'] !== '' ? $in['statut'] : null;
if ($statut !== null && !in_array($statut, array('citoyen', 'residant_permanent'), true)) {
    $statut = null;
}
// NEQ : 9-10 chiffres si présent
if ($neq !== null && !preg_match('/^\d{9,10}$/', $neq)) {
    json_response(array('ok' => false, 'error' => t('create.error.neq')));
}

$st = db()->prepare(
    'INSERT INTO profils_proprietaire
        (cree_par, prenom, nom, date_naissance, courriel, telephone, app,
         adresse, ville, code_postal, province, nom_compagnie, neq, statut)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$st->execute(array(
    (int) $u['id'],
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
));

json_response(array('ok' => true, 'id' => (int) db()->lastInsertId()));