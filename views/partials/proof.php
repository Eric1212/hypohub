<?php
/**
 * Hypohub — bandeau de preuve (stats dynamiques), partial de l'accueil.
 * Les valeurs viennent de home_stats() (lib/helpers.php) :
 *   demandes (avec période) · créanciers · propriétaires aidés · total K$.
 */
$__st = home_stats();
$__demandes = $__st['demandes'] == 1 ? 'one' : 'many';
?>
<section class="section home-proof">
    <div class="section-inner wide">
        <div class="card">
            <h2><?php echo htmlspecialchars(t('proof.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <div class="stats">
                <div class="stat">
                    <span class="stat-value"><?php echo htmlspecialchars(t('stats.demandes.' . $__demandes, array('n' => $__st['demandes'])), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="stat-label"><?php echo htmlspecialchars(t('stats.label.' . $__st['periode']), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?php echo htmlspecialchars(t('stats.creanciers.' . ($__st['creanciers'] == 1 ? 'one' : 'many'), array('n' => $__st['creanciers'])), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="stat-label"><?php echo htmlspecialchars(t('stats.creanciers.label'), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?php echo htmlspecialchars(t('stats.proprietaires.' . ($__st['proprietaires'] == 1 ? 'one' : 'many'), array('n' => $__st['proprietaires'])), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="stat-label"><?php echo htmlspecialchars(t('stats.proprietaires.label'), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?php echo htmlspecialchars(number_format(round($__st['total'] / 1000), 0, ',', ' ') . ' K$', ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="stat-label"><?php echo htmlspecialchars(t('stats.total.label'), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>
