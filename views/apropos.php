<?php
/** Hypohub — page À propos (inspirée de ba_info.html, pleine hauteur). */
?>
<section class="page-banner">
    <div class="page-banner-inner">
        <h1><?php echo htmlspecialchars(t('ab.banner'), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="page-banner-sub"><?php echo htmlspecialchars(t('ab.intro'), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
</section>

<section class="section page-content">
    <div class="section-inner wide">
        <div class="two-col">
            <div class="card">
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('ab.accord'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <h3><?php echo htmlspecialchars(t('ab.vision.t'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars(t('ab.vision.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <h3><?php echo htmlspecialchars(t('ab.mission.t'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars(t('ab.mission.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <h3><?php echo htmlspecialchars(t('ab.orient.t'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars(t('ab.orient.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <h2><?php echo htmlspecialchars(t('ab.entity.t'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <p><?php echo htmlspecialchars(t('ab.entity.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="signature">
                    <p class="sig-name"><i><?php echo htmlspecialchars(t('footer.founder'), ENT_QUOTES, 'UTF-8'); ?></i></p>
                </div>
            </div>

            <div class="card">
                <h2><?php echo htmlspecialchars(t('proof.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <div class="stats">
                    <div class="stat">
                        <span class="stat-value"><?php echo htmlspecialchars(t('proof.stat1.value'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-label"><?php echo htmlspecialchars(t('proof.stat1.label'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo htmlspecialchars(t('proof.stat2.value'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-label"><?php echo htmlspecialchars(t('proof.stat2.label'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo htmlspecialchars(t('proof.stat3.value'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-label"><?php echo htmlspecialchars(t('proof.stat3.label'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
                <h2><?php echo htmlspecialchars(t('faq.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('faq.q1'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('faq.a1'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('faq.q4'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('faq.a4'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('faq.q5'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('faq.a5'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
            </div>
        </div>
    </div>
</section>
