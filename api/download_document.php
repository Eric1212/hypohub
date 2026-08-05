<?php
/**
 * Hypohub — API : téléchargement d'un document du profil (contrôlé par session).
 *
 * GET ?id=<document_id> → fichier (Content-Disposition: attachment)
 * Seul le propriétaire du profil (ou un compte avec accès créancier, pour le
 * réseau) peut télécharger. Pas de session → 401.
 */
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

$u = auth_user();
if (!$u) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$st = db()->prepare(
    'SELECT d.id, d.profil_id, d.nom_fichier, d.fichier_stocke
       FROM documents d
      WHERE d.id = ?'
);
$st->execute(array($id));
$doc = $st->fetch();
if (!$doc) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

// Droit d'accès : propriétaire du profil, OU créancier (réseau)
$own = db()->prepare('SELECT id FROM profils_proprietaire WHERE id = ? AND cree_par = ?');
$own->execute(array($doc['profil_id'], (int) $u['id']));
$allowed = (bool) $own->fetchColumn();
if (!$allowed && empty($u['acces_creancier'])) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Le fichier disque est référencé par documents.fichier_stocke (jamais le nom d'origine).
// basename() élimine tout chemin : on ne sert que depuis uploads/.
$stored_path = __DIR__ . '/../uploads/' . basename($doc['fichier_stocke']);
if (!is_file($stored_path)) {
    http_response_code(404);
    echo 'Fichier introuvable';
    exit;
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($doc['nom_fichier']) . '"');
header('Content-Length: ' . filesize($stored_path));
readfile($stored_path);
exit;