<?php
/** Hypohub — en-tête commun (partial). */
$__lang = hypohub_current_lang();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($__lang, ENT_QUOTES, 'UTF-8'); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars(t('app.name'), ENT_QUOTES, 'UTF-8'); ?></title>
<link rel="stylesheet" href="static/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="site-header-inner">
        <a class="logo" href="index.php"><?php echo htmlspecialchars(t('app.name'), ENT_QUOTES, 'UTF-8'); ?></a>
        <nav class="lang-switch">
            <a href="?lang=fr" <?php if ($__lang === 'fr') { echo 'class="active"'; } ?>>FR</a>
            <span class="sep">|</span>
            <a href="?lang=en" <?php if ($__lang === 'en') { echo 'class="active"'; } ?>>EN</a>
        </nav>
    </div>
</header>
<main class="site-main">
