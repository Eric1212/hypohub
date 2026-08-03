<?php
/**
 * Hypohub — point d'entrée principal (multi-pages).
 *
 * Routage : ?page=<slug> → vue. Slugs : accueil (défaut), emprunter,
 * courtiers, creanciers, a-propos, contact.
 *
 * 1. Si la configuration MySQL n'est pas en place → redirige vers config.php
 *    (l'assistant d'installation).
 * 2. Sinon, charge la page demandée.
 *
 * Le sélecteur de langue ?page=X&lang=fr|en pose un cookie puis revient
 * sur la même page.
 */
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/i18n.php';

// Sélecteur de langue : ?lang=fr → cookie → retour (préserve la page)
if (isset($_GET['lang'])) {
    $lang = preg_replace('/[^a-z]/', '', (string) $_GET['lang']);
    if (in_array($lang, array('fr', 'en'), true)) {
        setcookie('hypohub_lang', $lang, time() + 60 * 60 * 24 * 365, '/');
    }
    $lang_page = isset($_GET['page']) ? preg_replace('/[^a-z-]/', '', (string) $_GET['page']) : '';
    $back = $lang_page !== '' ? 'index.php?page=' . urlencode($lang_page) : 'index.php';
    redirect($back);
}

// Pas encore configuré → assistant d'installation
if (!config_ok()) {
    redirect('config.php');
}

require_once __DIR__ . '/lib/db.php';

// Table de routage : slug → (vue, clé de titre)
$pages = array(
    'accueil'    => array('view' => 'home.php',        'title' => 'nav.home'),
    'emprunter'  => array('view' => 'emprunter.php',   'title' => 'nav.borrow'),
    'courtiers'  => array('view' => 'courtiers.php',   'title' => 'nav.brokers'),
    'creanciers' => array('view' => 'creanciers.php',  'title' => 'nav.lenders'),
    'a-propos'   => array('view' => 'apropos.php',     'title' => 'nav.about'),
    'contact'    => array('view' => 'contact.php',     'title' => 'nav.contact'),
);

$page = isset($_GET['page']) ? preg_replace('/[^a-z-]/', '', (string) $_GET['page']) : 'accueil';
if (!isset($pages[$page])) {
    $page = 'accueil';
}

$__page = $page;          // slug courant (utilisé par header.php)
$__title_key = $pages[$page]['title']; // clé de titre (utilisée par header.php)

require __DIR__ . '/views/partials/header.php';
require __DIR__ . '/views/' . $pages[$page]['view'];
require __DIR__ . '/views/partials/footer.php';
