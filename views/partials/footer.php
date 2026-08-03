<?php /** Hypohub — pied de page commun (partial). */ ?>
</main>
<footer class="site-footer">
    <div class="site-footer-inner">
        <div>
            <p class="brand"><?php echo htmlspecialchars(t('footer.brand'), ENT_QUOTES, 'UTF-8'); ?>
                <a href="https://bafinanciere.ca" target="_blank" rel="noopener">bafinanciere.ca</a></p>
            <p class="founder"><i><?php echo htmlspecialchars(t('footer.founder'), ENT_QUOTES, 'UTF-8'); ?></i></p>
        </div>
        <p class="footer-auth">
            <?php $__auth = auth_user(); ?>
            <?php if ($__auth): ?>
                <?php echo htmlspecialchars(t('auth.footer.hello', array('name' => $__auth['nom_complet'])), ENT_QUOTES, 'UTF-8'); ?>
                · <a href="index.php?page=espace"><?php echo htmlspecialchars(t('nav.space'), ENT_QUOTES, 'UTF-8'); ?></a>
                · <a href="index.php?page=deconnexion" data-ajax="off"><?php echo htmlspecialchars(t('auth.footer.logout'), ENT_QUOTES, 'UTF-8'); ?></a>
            <?php else: ?>
                <a href="#" data-auth-open><?php echo htmlspecialchars(t('auth.footer.link'), ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endif; ?>
        </p>
    </div>
</footer>
<?php require __DIR__ . '/auth_modal.php'; ?>
<script src="static/js/app.js"></script>
</body>
</html>
