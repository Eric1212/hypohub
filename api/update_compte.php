<?php
/**
 * Hypohub — API : mise à jour des réglages du compte (Zone compte).
 *
 * POST JSON {nom_complet, username, courriel, csrf} → 200 {ok:true}
 * Session requise. Les champs sont tous modifiables ; un champ absent de la
 * requête n'est pas modifié (mise à jour partielle).
 * Spécifique : les accès (Propriétaire/Créancier) ne se changent pas ici.
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

$set  = array();
$vals = array();

// Nom complet
if (array_key_exists('nom_complet', $in)) {
    $nom = trim((string) $in['nom_complet']);
    if ($nom === '') {
        json_response(array('ok' => false, 'error' => t('account.error.nom')));
    }
    $set[] = 'nom_complet = ?';
    $params[] = $nom;
}

// Username (identifiant) — optionnel mais unique s'il est fourni
if (array_key_exists('username', $in)) {
    $username = trim((string) $in['username']);
    if ($username === '') {
        $username = null;
    }
    if ($username !== null && !preg_match('/^[a-zA-Z0-9_.-]{3,80}$/', $username)) {
        json_response(array('ok' => false, 'error' => t('account.error.username')));
    }
    if ($username !== null) {
        $dup = db()->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
        $dup->execute(array($username, (int) $u['id']));
        if ($dup->fetchColumn()) {
            json_response(array('ok' => false, 'error' => t('account.error.username_prise')));
        }
    }
    $set[] = 'username = ?';
    $params[] = $username;
}

// Courriel — requis, valide, unique
if (array_key_exists('courriel', $in)) {
    $courriel = trim((string) $in['courriel']);
    if ($courriel === '' || !filter_var($courriel, FILTER_VALIDATE_EMAIL)) {
        json_response(array('ok' => false, 'error' => t('account.error.courriel')));
    }
    $dup = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
    $dup->execute(array($courriel, (int) $u['id']));
    if ($dup->fetchColumn()) {
        json_response(array('ok' => false, 'error' => t('account.error.courriel_prise')));
    }
    $set[] = 'email = ?';
    $params[] = $courriel;
}

// Certificat AMF — autosave (le champ d'un certificat vérifié est verrouillé côté
// UI : l'alerte de révocation passe par api/certificat_amf.php action=retirer).
// Garde-fou serveur : si une mise à jour arrive directement et que la valeur
// change alors que l'ancien certificat était 'verifie', on révoque l'accès
// courtier/créancier (le nouveau numéro n'a pas été validé par un humain).
if (array_key_exists('certificat_amf', $in)) {
    // Champ AMF : vider est un état valide (on efface le n° et, s'il était
    // vérifié, on révoque l'accès — la révocation passe par certificat_amf.php
    // côté UI ; ce garde-fou protège les requêtes directes).
    $amf = trim((string) $in['certificat_amf']);
    $old = db()->prepare('SELECT certificat_amf, certificat_amf_statut FROM users WHERE id = ?');
    $old->execute(array((int) $u['id']));
    $ancient = $old->fetch();

    if (($ancient['certificat_amf'] ?? null) !== ($amf !== '' ? $amf : null)) {
        $set[] = 'certificat_amf = ?';
        $params[] = $amf !== '' ? $amf : null;
        if (($ancient['certificat_amf_statut'] ?? '') === 'verifie') {
            // Récocation : certif non validé, droit à revendiquer à nouveau.
            $set[] = 'certificat_amf_statut = ?';
            $params[] = 'vide';
            $set[] = 'acces_creancier = ?';
            $params[] = 0;
            $set[] = 'demande_creancier_statut = ?';
            $params[] = 'aucune';
        }
    }
}

if (!$set) {
    json_response(array('ok' => false, 'error' => t('account.error.vide')));
}

$params[] = (int) $u['id'];
$sql = 'UPDATE users SET ' . implode(', ', $set) . ' WHERE id = ?';
$st = db()->prepare($sql);
$st->execute($params);

// Invalider la session cache (auth_user est statique dans le process)
json_response(array('ok' => true));