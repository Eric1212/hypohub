<?php
/**
 * Hypohub — API : mise à jour d'un document (renommage, changement de type).
 *
 * POST JSON {id, csrf, nom_fichier?, type_id?} → 200 {ok:true} | {ok:false}
 * Session requise ; le document doit appartenir à l'utilisateur (user_id).
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

$id = isset($in['id']) ? (int) $in['id'] : 0;

// Propriété : le doc appartient à l'utilisateur
$st = db()->prepare('SELECT id FROM documents WHERE id = ? AND user_id = ?');
$st->execute(array($id, (int) $u['id']));
if (!$st->fetchColumn()) {
    json_response(array('ok' => false, 'error' => t('doc.error.type')), 404);
}

// --- Renommage (optionnel) ---
if (array_key_exists('nom_fichier', $in)) {
    $nom = trim((string) $in['nom_fichier']);
    $nom = mb_substr($nom, 0, 255);
    if ($nom === '') {
        json_response(array('ok' => false, 'error' => t('doc.error.name')));
    }
    $st = db()->prepare('UPDATE documents SET nom_fichier = ? WHERE id = ?');
    $st->execute(array($nom, $id));
}

// --- Changement de type (optionnel) ---
if (array_key_exists('type_id', $in) && $in['type_id'] !== '') {
    $type_id = (int) $in['type_id'];
    $st = db()->prepare('SELECT id FROM documents_types WHERE id = ? AND actif = 1');
    $st->execute(array($type_id));
    if (!$st->fetchColumn()) {
        json_response(array('ok' => false, 'error' => t('doc.error.type')));
    }
    $st = db()->prepare('UPDATE documents SET type_id = ? WHERE id = ?');
    $st->execute(array($type_id, $id));
}

json_response(array('ok' => true));