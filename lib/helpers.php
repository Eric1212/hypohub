<?php
/**
 * Hypohub — petites fonctions utilitaires partagées.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * La configuration est-elle en place et la connexion MySQL fonctionne-t-elle ?
 */
function config_ok() {
    if (!hypohub_config()) {
        return false;
    }
    try {
        $pdo = db();
        $pdo->query('SELECT 1');
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Répond en JSON et arrête le script.
 */
function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data);
    exit;
}

/**
 * Redirige vers une URL et arrête le script.
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}
