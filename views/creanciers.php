<?php
/** Hypohub — page Créanciers (profil prêteur privé, pleine hauteur). */
?>
<section class="page-banner">
    <div class="page-banner-inner">
        <h1><?php echo htmlspecialchars(t('len.banner'), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="page-banner-sub"><?php echo htmlspecialchars(t('len.intro'), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
</section>

<section class="section page-content">
    <div class="section-inner wide">
        <div class="two-col">
            <div class="card">
                <h2><?php echo htmlspecialchars(t('len.adv.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('len.adv1.t'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('len.adv1.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('len.adv2.t'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('len.adv2.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('len.adv3.t'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('len.adv3.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <p class="note"><?php echo htmlspecialchars(t('len.note'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <div class="card">
                <h2><?php echo htmlspecialchars(t('transparency.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <p><?php echo htmlspecialchars(t('transparency.body'), ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="note"><?php echo htmlspecialchars(t('transparency.note'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
    </div>
</section>

<section class="section cta-big">
    <div class="section-inner">
        <div class="card contact cta-row">
            <p><?php echo htmlspecialchars(t('len.cta.body'), ENT_QUOTES, 'UTF-8'); ?></p>
            <?php if (auth_user()): ?>
                <a class="btn btn-primary btn-xl" href="index.php?page=espace"><?php echo htmlspecialchars(t('space.go'), ENT_QUOTES, 'UTF-8'); ?></a>
            <?php else: ?>
                <a class="btn btn-primary btn-xl" href="#" data-auth-open data-auth-acces="creancier"><?php echo htmlspecialchars(t('len.cta.btn'), ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
