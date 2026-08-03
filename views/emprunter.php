<?php
/** Hypohub — page Emprunter (profil emprunteur, pleine hauteur). */
?>
<section class="page-banner">
    <div class="page-banner-inner">
        <h1><?php echo htmlspecialchars(t('emp.banner'), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="page-banner-sub"><?php echo htmlspecialchars(t('emp.intro'), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
</section>

<section class="section page-content">
    <div class="section-inner wide">
        <div class="two-col">
            <div class="card">
                <h2><?php echo htmlspecialchars(t('how.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <div class="steps-compact">
                    <div class="step">
                        <span class="step-num">1</span>
                        <h3><?php echo htmlspecialchars(t('how.step1.title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('how.step1.body'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="step">
                        <span class="step-num">2</span>
                        <h3><?php echo htmlspecialchars(t('how.step2.title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('how.step2.body'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="step">
                        <span class="step-num">3</span>
                        <h3><?php echo htmlspecialchars(t('how.step3.title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('how.step3.body'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="step">
                        <span class="step-num">4</span>
                        <h3><?php echo htmlspecialchars(t('how.step4.title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('how.step4.body'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2><?php echo htmlspecialchars(t('emp.cases.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('emp.case1.t'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('emp.case1.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('emp.case2.t'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('emp.case2.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('emp.case3.t'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('emp.case3.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <details class="faq-item">
                    <summary><?php echo htmlspecialchars(t('emp.case4.t'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <p><?php echo htmlspecialchars(t('emp.case4.b'), ENT_QUOTES, 'UTF-8'); ?></p>
                </details>
                <p class="note"><?php echo htmlspecialchars(t('problem.note'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
    </div>
</section>

<section class="section cta-big">
    <div class="section-inner">
        <div class="card contact cta-row">
            <p><?php echo htmlspecialchars(t('emp.cta.body'), ENT_QUOTES, 'UTF-8'); ?></p>
            <a class="btn btn-primary btn-xl" href="index.php?page=contact"><?php echo htmlspecialchars(t('emp.cta.btn'), ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </div>
</section>
