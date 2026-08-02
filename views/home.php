<?php
/** Hypohub — page d'accueil. Preuve que la base et les tables fonctionnent. */
$schema = db()->query(
    "SELECT meta_value FROM app_meta WHERE meta_key = 'schema_version'"
)->fetchColumn();
?>
<section class="card">
    <h2><?php echo htmlspecialchars(t('home.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
    <p><?php echo htmlspecialchars(t('app.tagline'), ENT_QUOTES, 'UTF-8'); ?></p>
    <ul class="checks">
        <li class="ok"><?php echo htmlspecialchars(t('home.db_ok'), ENT_QUOTES, 'UTF-8'); ?></li>
        <li class="ok"><?php echo htmlspecialchars(t('home.schema_version'), ENT_QUOTES, 'UTF-8'); ?> : <?php echo htmlspecialchars((string) $schema, ENT_QUOTES, 'UTF-8'); ?></li>
    </ul>
    <p>
        <button id="check-ajax" class="btn"><?php echo htmlspecialchars(t('home.db_check'), ENT_QUOTES, 'UTF-8'); ?></button>
    </p>
    <p id="ajax-result" class="ajax-result" aria-live="polite"></p>
</section>
