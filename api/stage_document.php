<?php
/**
 * Hypohub — API : stage d'un document (scan/PDF) sur le compte utilisateur.
 *
 * POST multipart : {csrf, type_id, fichier: <file>}
 * → 200 {ok:true, id} | {ok:false, error}
 * Session requise. Le document est stocké en staging (profil_id = NULL),
 * rattaché au profil lors de la création/modification de profil.
 * Purge quotidienne des staged (3h33).
 *
 * Limites : 100 Mo max, extensions .pdf/.jpg/.jpeg/.png (magic bytes vérifiés),
 * stockage sur disque dans uploads/ (nom = hash, jamais le nom original).
 */
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(array('ok' => false, 'error' => 'Méthode non autorisée'), 405);
}

$in = array_merge($_POST, $_FILES);

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

// --- Type de document existant ---
$type_id = isset($in['type_id']) ? (int) $in['type_id'] : 0;
$st = db()->prepare('SELECT id FROM documents_types WHERE id = ? AND actif = 1');
$st->execute(array($type_id));
if (!$st->fetchColumn()) {
    json_response(array('ok' => false, 'error' => t('doc.error.type')), 400);
}

// --- Fichier ---
if (!isset($_FILES['fichier']) || !is_array($_FILES['fichier'])) {
    json_response(array('ok' => false, 'error' => t('doc.error.file')));
}
$f = $_FILES['fichier'];
if ($f['error'] !== UPLOAD_ERR_OK) {
    $msg = $f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE
        ? t('doc.error.size') : t('doc.error.upload');
    json_response(array('ok' => false, 'error' => $msg));
}

const MAX_STAGE_BYTES = 100 * 1024 * 1024; // 100 Mo
if ($f['size'] <= 0 || $f['size'] > MAX_STAGE_BYTES) {
    json_response(array('ok' => false, 'error' => t('doc.error.size')));
}

$tmp_path = $f['tmp_name'];
if (!is_uploaded_file($tmp_path)) {
    json_response(array('ok' => false, 'error' => t('doc.error.upload')));
}

// Vérification du type réel (magic bytes), pas seulement l'extension
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($tmp_path);
$allowed = array(
    'application/pdf'   => 'pdf',
    'image/jpeg'        => 'jpg',
    'image/png'         => 'png',
    'image/webp'        => 'webp',
);
if (!isset($allowed[$mime])) {
    json_response(array('ok' => false, 'error' => t('doc.error.mime')));
}
$ext = $allowed[$mime];

// Nom de stockage : horodatage + hash — jamais le nom d'origine (sécurité)
$dir = __DIR__ . '/../uploads/';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
$stored_name = date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
$stored = $dir . $stored_name;
if (!move_uploaded_file($tmp_path, $stored)) {
    json_response(array('ok' => false, 'error' => t('doc.error.upload')));
}

// Nom d'origine conservé pour l'affichage
$nom_original = isset($f['name']) ? basename($f['name']) : '';

$st = db()->prepare(
    'INSERT INTO documents (user_id, type_id, nom_fichier, fichier_stocke, taille_octets, mime)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$st->execute(array((int) $u['id'], $type_id, $nom_original, $stored_name, $f['size'], $mime));

json_response(array('ok' => true, 'id' => (int) db()->lastInsertId()));