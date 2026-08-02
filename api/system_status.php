<?php
/**
 * Hypohub — api/system_status.php
 *
 * Premier endpoint AJAX : retourne l'état du système en JSON.
 * Pattern regioncities : un fichier PHP par action, réponse JSON.
 *
 * Exemple :
 *   GET api/system_status.php
 *   → {"ok":true,"app":"hypohub","schema_version":"1","server_time":"..."}
 */
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';

if (!config_ok()) {
    json_response(array('ok' => false, 'error' => 'not_configured'), 503);
}

try {
    $pdo = db();
    $schema = $pdo->query(
        "SELECT meta_value FROM app_meta WHERE meta_key = 'schema_version'"
    )->fetchColumn();

    json_response(array(
        'ok'             => true,
        'app'            => 'hypohub',
        'schema_version' => $schema,
        'server_time'    => gmdate('c'),
    ));
} catch (Exception $e) {
    json_response(array('ok' => false, 'error' => 'server_error'), 500);
}
