<?php
/**
 * Hypohub — point d'entrée principal (multi-pages).
 *
 * Routage : ?page=<slug> → vue. Slugs : accueil (défaut), proprietaire,
 * courtier, creancier, a-propos, contact.
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
require_once __DIR__ . '/lib/auth.php'; // session + connexion/inscription

// Table de routage : slug → (vue, clé de titre)
$pages = array(
    'accueil'    => array('view' => 'home.php',        'title' => 'nav.home'),
    'proprietaire' => array('view' => 'proprietaire.php', 'title' => 'nav.borrow'),
    'courtier'     => array('view' => 'courtier.php',     'title' => 'nav.brokers'),
    'creancier'    => array('view' => 'creancier.php',    'title' => 'nav.lenders'),
    'a-propos'   => array('view' => 'apropos.php',     'title' => 'nav.about'),
    'contact'    => array('view' => 'contact.php',     'title' => 'nav.contact'),
    'espace'     => array('view' => 'espace.php',      'title' => 'nav.space'),
    'admin'      => array('view' => 'admin.php',       'title' => 'nav.admin'),
);

$page = isset($_GET['page']) ? preg_replace('/[^a-z-]/', '', (string) $_GET['page']) : 'accueil';

// Action : déconnexion (avant le fallback — ce n'est pas une page, un saut d'état)
if ($page === 'deconnexion') {
    auth_logout();
    redirect('index.php');
}

if (!isset($pages[$page])) {
    $page = 'accueil';
}

// Vue admin réservée aux employés vérificateurs (est_admin=1)
if ($page === 'admin') {
    $__admin_u = auth_user();
    if (!$__admin_u || empty($__admin_u['est_admin'])) {
        redirect('index.php?page=espace');
    }
    // Espace & panneau : jamais en cache — les données changent à chaque
    // action et dépendent de la session (sinon le navigateur ressort des
    // copies périmées et il faut F5 pour voir l'état réel).
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

$__page = $page;          // slug courant (utilisé par header.php)
$__title_key = $pages[$page]['title']; // clé de titre (utilisée par header.php)

require __DIR__ . '/views/partials/header.php';
require __DIR__ . '/views/' . $pages[$page]['view'];
require __DIR__ . '/views/partials/footer.php';
