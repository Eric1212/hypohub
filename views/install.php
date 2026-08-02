<?php
/** Hypohub — vue de l'assistant d'installation (chargée par config.php). */
?>
<section class="card">
    <h2><?php echo htmlspecialchars(t('install.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
    <p><?php echo htmlspecialchars(t('install.intro'), ENT_QUOTES, 'UTF-8'); ?></p>

    <?php if ($error !== '') : ?>
        <p class="error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="post" action="config.php" class="install-form">
        <label>
            <span><?php echo htmlspecialchars(t('install.db_host'), ENT_QUOTES, 'UTF-8'); ?></span>
            <input type="text" name="db_host" value="<?php echo htmlspecialchars($values['db_host'], ENT_QUOTES, 'UTF-8'); ?>" required autofocus>
        </label>
        <label>
            <span><?php echo htmlspecialchars(t('install.db_name'), ENT_QUOTES, 'UTF-8'); ?></span>
            <input type="text" name="db_name" value="<?php echo htmlspecialchars($values['db_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </label>
        <label>
            <span><?php echo htmlspecialchars(t('install.db_user'), ENT_QUOTES, 'UTF-8'); ?></span>
            <input type="text" name="db_user" value="<?php echo htmlspecialchars($values['db_user'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </label>
        <label>
            <span><?php echo htmlspecialchars(t('install.db_pass'), ENT_QUOTES, 'UTF-8'); ?></span>
            <input type="password" name="db_pass" value="">
        </label>
        <button type="submit" class="btn"><?php echo htmlspecialchars(t('install.submit'), ENT_QUOTES, 'UTF-8'); ?></button>
    </form>
</section>
