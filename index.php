<?php
/**
 * Hypohub — point d'entrée principal.
 *
 * 1. Si la configuration MySQL n'est pas en place → redirige vers config.php
 *    (l'assistant d'installation).
 * 2. Sinon, charge la page demandée.
 *
 * Le sélecteur de langue ?lang=fr|en pose un cookie puis revient.
 */
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/i18n.php';

// Sélecteur de langue : ?lang=fr → cookie → retour
if (isset($_GET['lang'])) {
    $lang = preg_replace('/[^a-z]/', '', (string) $_GET['lang']);
    if (in_array($lang, array('fr', 'en'), true)) {
        setcookie('hypohub_lang', $lang, time() + 60 * 60 * 24 * 365, '/');
    }
    $back = isset($_GET['ref']) && $_GET['ref'] !== '' ? (string) $_GET['ref'] : 'index.php';
    redirect($back);
}

// Pas encore configuré → assistant d'installation
if (!config_ok()) {
    redirect('config.php');
}

require_once __DIR__ . '/lib/db.php';

require __DIR__ . '/views/partials/header.php';
require __DIR__ . '/views/home.php';
require __DIR__ . '/views/partials/footer.php';
