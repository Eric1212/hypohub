<?php
/** Hypohub — page Courtiers (profil courtier hypothécaire, pleine hauteur). */
?>
<section class="page-banner">
    <div class="page-banner-inner">
        <h1><?php echo htmlspecialchars(t('bro.banner'), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="page-banner-sub"><?php echo htmlspecialchars(t('bro.intro'), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
</section>

<section class="section page-content">
    <div class="section-inner wide">
        <div class="two-col">
            <div class="card">
                <h2><?php echo htmlspecialchars(t('bro.adv.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('bro.adv1.t'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('bro.adv1.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('bro.adv2.t'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('bro.adv2.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('bro.adv3.t'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('bro.adv3.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <p class="note"><?php echo htmlspecialchars(t('bro.note'), ENT_QUOTES, 'UTF-8'); ?></p>
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
            <p><?php echo htmlspecialchars(t('bro.cta.body'), ENT_QUOTES, 'UTF-8'); ?></p>
            <a class="btn btn-alt btn-xl" href="index.php?page=contact"><?php echo htmlspecialchars(t('bro.cta.btn'), ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </div>
</section>
