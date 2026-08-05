<?php
/**
 * Hypohub — API : liste des documents d'un profil propriétaire.
 *
 * GET ?profil_id=X → 200 {ok:true, documents:[{id, nom_fichier, taille_octets,
 *   type_id, type_nom, mime, date_upload}]}
 * Session requise ; le profil doit appartenir à l'utilisateur.
 */
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

$u = auth_user();
if (!$u) {
    json_response(array('ok' => false, 'error' => t('auth.error.invalid')), 401);
}

$profil_id = isset($_GET['profil_id']) ? (int) $_GET['profil_id'] : 0;

$st = db()->prepare('SELECT id FROM profils_proprietaire WHERE id = ? AND cree_par = ?');
$st->execute(array($profil_id, (int) $u['id']));
if (!$st->fetchColumn()) {
    json_response(array('ok' => false, 'error' => t('create.error.profil')), 404);
}

$lang_key = hypohub_current_lang() === 'en' ? 'nom_en' : 'nom_fr';
$st = db()->prepare(
    "SELECT d.id, d.nom_fichier, d.taille_octets, d.type_id, d.mime, d.date_upload,
            t.$lang_key AS type_nom
       FROM documents d
  LEFT JOIN documents_types t ON t.id = d.type_id
      WHERE d.profil_id = ?
      ORDER BY d.date_upload DESC, d.id DESC"
);
$st->execute(array($profil_id));

json_response(array('ok' => true, 'documents' => $st->fetchAll()));