<?php
/**
 * Hypohub — API : liste des documents en staging du compte (profil_id NULL).
 *
 * GET → 200 {ok:true, documents:[{id, nom_fichier, taille_octets, type_id,
 *   type_nom, mime, date_upload}]}
 * Session requise. Utilisé par l'éditeur de profil : les staged du compte
 * apparaissent dans la section Documents (récupération après F5, avant
 * rattachement au profil lors de la soumission).
 */
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

$u = auth_user();
if (!$u) {
    json_response(array('ok' => false, 'error' => t('auth.error.invalid')), 401);
}

$lang_key = hypohub_current_lang() === 'en' ? 'nom_en' : 'nom_fr';
$st = db()->prepare(
    "SELECT d.id, d.nom_fichier, d.taille_octets, d.type_id, d.mime, d.date_upload,
            t.$lang_key AS type_nom
       FROM documents d
  LEFT JOIN documents_types t ON t.id = d.type_id
      WHERE d.user_id = ? AND d.profil_id IS NULL
      ORDER BY d.date_upload DESC, d.id DESC"
);
$st->execute(array((int) $u['id']));

json_response(array('ok' => true, 'documents' => $st->fetchAll()));