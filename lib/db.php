<?php
/**
 * Hypohub — connexion à la base de données MySQL + création du schéma.
 *
 * Le schéma est créé automatiquement au premier passage : chaque table
 * utilise « CREATE TABLE IF NOT EXISTS », donc relancer est sans danger.
 */
require_once __DIR__ . '/config.php';

/**
 * Retourne l'objet PDO connecté (une seule connexion par requête).
 */
function db() {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = hypohub_config();
    if (!$cfg) {
        throw new RuntimeException('Hypohub n\'est pas configuré. Ouvre config.php à la racine.');
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $cfg['db_host'],
        $cfg['db_name']
    );

    $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ));

    return $pdo;
}

/**
 * Crée les tables si elles n'existent pas.
 * Table témoin pour l'instant : app_meta (version du schéma, réglages...).
 */
function db_schema() {
    $pdo = db();

    $pdo->exec("CREATE TABLE IF NOT EXISTS app_meta (
        meta_key   VARCHAR(64)  NOT NULL PRIMARY KEY,
        meta_value TEXT         NOT NULL
    )");

    $st = $pdo->prepare(
        "INSERT IGNORE INTO app_meta (meta_key, meta_value) VALUES ('schema_version', '1')"
    );
    $st->execute();
}
