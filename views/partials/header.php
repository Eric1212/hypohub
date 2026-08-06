<?php
/** Hypohub — en-tête commun (partial). */
$__lang = hypohub_current_lang();
if (!isset($__page)) { $__page = 'accueil'; }
if (!isset($__title_key)) { $__title_key = 'nav.home'; }

// Navigation (ordre : comme sur le site B&A)
$__nav = array(
    'accueil'    => 'nav.home',
    'proprietaire' => 'nav.borrow',
    'courtier'     => 'nav.brokers',
    'creancier'    => 'nav.lenders',
    'a-propos'   => 'nav.about',
    'contact'    => 'nav.contact',
);
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($__lang, ENT_QUOTES, 'UTF-8'); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars(t('app.name'), ENT_QUOTES, 'UTF-8'); ?> :: <?php echo htmlspecialchars(t($__title_key), ENT_QUOTES, 'UTF-8'); ?></title>
<link rel="stylesheet" href="static/css/style.css">
</head>
<body<?php echo $__page === 'espace' ? ' class="page-espace"' : ($__page === 'admin' ? ' class="page-admin"' : ''); ?>>
<header class="site-header">
    <div class="site-header-inner">
        <a class="logo" href="index.php"><?php echo htmlspecialchars(t('app.name'), ENT_QUOTES, 'UTF-8'); ?></a>
        <nav class="site-nav">
            <?php foreach ($__nav as $slug => $key): ?>
                <?php
                $href = $slug === 'accueil' ? 'index.php' : 'index.php?page=' . $slug;
                $active = $__page === $slug ? ' class="active"' : '';
                ?>
                <a href="<?php echo $href; ?>"<?php echo $active; ?>><?php echo htmlspecialchars(t($key), ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endforeach; ?>
        </nav>
        <nav class="lang-switch">
            <a href="?page=<?php echo htmlspecialchars($__page, ENT_QUOTES, 'UTF-8'); ?>&lang=fr" <?php if ($__lang === 'fr') { echo 'class="active"'; } ?>>FR</a>
            <span class="sep">|</span>
            <a href="?page=<?php echo htmlspecialchars($__page, ENT_QUOTES, 'UTF-8'); ?>&lang=en" <?php if ($__lang === 'en') { echo 'class="active"'; } ?>>EN</a>
        </nav>
    </div>
</header>
<main class="site-main<?php echo $__page === 'accueil' ? ' site-main--home' : ' site-main--page'; ?>">
