<?php
/**
 * Hypohub — API : suppression définitive d'un document.
 *
 * POST JSON {id, csrf} → 200 {ok:true} | {ok:false}
 * Session requise ; le document doit appartenir à l'utilisateur (user_id).
 * Détruit la ligne ET le fichier disque — sans retour.
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

$st = db()->prepare('SELECT fichier_stocke FROM documents WHERE id = ? AND user_id = ?');
$st->execute(array($id, (int) $u['id']));
$fichier = $st->fetchColumn();
if (!$fichier) {
    json_response(array('ok' => false, 'error' => t('doc.error.type')), 404);
}

// Fichier disque (basename : on ne touche qu'à uploads/)
$path = __DIR__ . '/../uploads/' . basename($fichier);
if (is_file($path)) {
    @unlink($path);
}

$st = db()->prepare('DELETE FROM documents WHERE id = ?');
$st->execute(array($id));

json_response(array('ok' => true));