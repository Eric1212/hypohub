<?php
/** Hypohub — page d'accueil (bandeau bienvenue + 3 profils, pleine hauteur). */
?>
<section class="page-banner">
    <div class="page-banner-inner">
        <h1><?php echo htmlspecialchars(t('hero.title'), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="page-banner-sub"><?php echo htmlspecialchars(t('hero.subtitle'), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
</section>

<section class="section home-profiles" id="profils">
    <div class="section-inner wide">
        <div class="who-grid">
            <div class="card who-card">
                <h3><?php echo htmlspecialchars(t('who.borrower.title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?php echo htmlspecialchars(t('who.borrower.body'), ENT_QUOTES, 'UTF-8'); ?></p>
                <a class="btn btn-primary" href="index.php?page=proprietaire"><?php echo htmlspecialchars(t('who.borrower.cta'), ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
            <div class="card who-card">
                <h3><?php echo htmlspecialchars(t('who.broker.title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?php echo htmlspecialchars(t('who.broker.body'), ENT_QUOTES, 'UTF-8'); ?></p>
                <a class="btn btn-alt" href="index.php?page=courtier"><?php echo htmlspecialchars(t('who.broker.cta'), ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
            <div class="card who-card">
                <h3><?php echo htmlspecialchars(t('who.lender.title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?php echo htmlspecialchars(t('who.lender.body'), ENT_QUOTES, 'UTF-8'); ?></p>
                <a class="btn btn-outline" href="index.php?page=creancier"><?php echo htmlspecialchars(t('who.lender.cta'), ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/partials/proof.php'; ?>
