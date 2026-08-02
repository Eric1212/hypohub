<?php
/**
 * Hypohub — lecture de la configuration.
 *
 * Charge le fichier lib/config.local.php s'il existe (généré par l'assistant
 * d'installation config.php, ou copié depuis config.example.php).
 * Retourne null tant que la configuration n'existe pas.
 */
define('HYPOHUB_CFG_FILE', __DIR__ . '/config.local.php');

/**
 * Retourne la configuration sous forme de tableau, ou null si absente.
 */
function hypohub_config() {
    if (!is_file(HYPOHUB_CFG_FILE)) {
        return null;
    }
    $cfg = require HYPOHUB_CFG_FILE;
    return is_array($cfg) ? $cfg : null;
}
