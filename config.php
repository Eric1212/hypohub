<?php
/**
 * Hypohub — assistant d'installation.
 *
 * Règles :
 * - Si Hypohub est déjà configuré (config OK) → il REFUSE de s'exécuter et
 *   redirige vers index.php.
 * - Sinon, il affiche le formulaire (hôte, base, usager, mot de passe),
 *   teste la connexion MySQL, écrit lib/config.local.php, crée les tables
 *   puis redirige vers index.php.
 */
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/i18n.php';

// Déjà configuré ? On refuse de s'exécuter.
if (config_ok()) {
    redirect('index.php');
}

$error  = '';
$values = array('db_host' => 'localhost', 'db_name' => 'hypohub', 'db_user' => '', 'db_pass' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = array(
        'db_host' => isset($_POST['db_host']) ? trim((string) $_POST['db_host']) : '',
        'db_name' => isset($_POST['db_name']) ? trim((string) $_POST['db_name']) : '',
        'db_user' => isset($_POST['db_user']) ? trim((string) $_POST['db_user']) : '',
        'db_pass' => isset($_POST['db_pass']) ? (string) $_POST['db_pass'] : '',
    );

    if ($values['db_host'] === '' || $values['db_name'] === '' || $values['db_user'] === '') {
        $error = t('install.error');
    } else {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $values['db_host'],
            $values['db_name']
        );

        try {
            // 1. Teste la connexion avant d'écrire quoi que ce soit
            $pdo = new PDO($dsn, $values['db_user'], $values['db_pass'], array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ));

            // 2. Écrit la configuration locale (jamais versionnée)
            $content = "<?php\n// Généré par config.php le " . date('c') . "\nreturn " .
                var_export($values, true) . ";\n";
            file_put_contents(HYPOHUB_CFG_FILE, $content);

            // 3. Crée les tables
            require_once __DIR__ . '/lib/db.php';
            db_schema();

            // 4. En ligne !
            redirect('index.php');
        } catch (Exception $e) {
            $error = t('install.error');
        }
    }
}

require __DIR__ . '/views/partials/header.php';
require __DIR__ . '/views/install.php';
require __DIR__ . '/views/partials/footer.php';
