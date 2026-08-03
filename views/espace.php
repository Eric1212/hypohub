<?php
/**
 * Hypohub — Espace membre.
 * Protégé : sans session, on redirige vers l'accueil (où la modale s'ouvre).
 * Structure d'accordéons imbriqués (règle d'Éric) : H3 (sections) et H4 (éléments)
 * sont tous deux pliables ; sous chaque H3 des H4, jamais de listes à puces.
 */
$__u = auth_user();
if (!$__u) {
    redirect('index.php');
}

$pdo       = db();
$profils   = array();
$proprietes = array();
$dossiers  = array();
$creanciers = array();
$reseau    = array();

if ($__u['acces_proprietaire']) {
    $st = $pdo->prepare('SELECT id, nom_complet, telephone, courriel, situation_emploi, revenu_annuel FROM profils_proprietaire WHERE cree_par = ? ORDER BY id DESC');
    $st->execute(array($__u['id']));
    $profils = $st->fetchAll();

    $st = $pdo->prepare('SELECT id, adresse, ville, code_postal, valeur_estimee, valeur_nette FROM profils_propriete WHERE cree_par = ? ORDER BY id DESC');
    $st->execute(array($__u['id']));
    $proprietes = $st->fetchAll();

    $st = $pdo->prepare(
        'SELECT d.id, d.montant_demande, d.rang, d.type_financement, d.statut, p.nom_complet AS emprunteur
           FROM dossiers_emprunt d
           JOIN profils_proprietaire p ON p.id = d.profil_proprietaire_id
          WHERE d.cree_par = ?
          ORDER BY d.id DESC'
    );
    $st->execute(array($__u['id']));
    $dossiers = $st->fetchAll();
}

if ($__u['acces_creancier']) {
    $st = $pdo->prepare('SELECT id, nom, type, capital_disponible, criteres, permis_opc FROM profils_creancier WHERE user_id = ? ORDER BY id DESC');
    $st->execute(array($__u['id']));
    $creanciers = $st->fetchAll();

    // Dossiers visibles par le réseau : PAS les profils (masqués tant que pas d'acceptation).
    $st = $pdo->prepare(
        "SELECT id, montant_demande, rang, type_financement, statut
           FROM dossiers_emprunt
          WHERE statut IN ('nouveau', 'accepte') AND cree_par != ?
          ORDER BY id DESC"
    );
    $st->execute(array($__u['id']));
    $reseau = $st->fetchAll();
}

/** Affiche une ligne détail « Libellé : valeur » si la valeur n'est pas vide. */
function espace_detail($label, $value) {
    if ($value === null || $value === '') {
        return;
    }
    echo '<p><strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ' :</strong> '
        . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</p>';
}
?>
<section class="page-banner">
    <div class="page-banner-inner">
        <h1><?php echo htmlspecialchars(t('space.title', array('name' => $__u['nom_complet'])), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="page-banner-sub"><?php echo htmlspecialchars(t('space.intro'), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
</section>

<section class="section page-content">
    <div class="section-inner wide">
        <div class="two-col">

            <?php if ($__u['acces_proprietaire']): ?>
            <div class="card">
                <h2><?php echo htmlspecialchars(t('space.prop.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <ul class="accords">

                    <li>
                        <h3 class="toggle-title"><?php echo htmlspecialchars(t('space.profiles'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="toggle-content" style="display: none;">
                            <div class="block">
                                <?php if ($profils): ?>
                                    <ul class="accords">
                                        <?php foreach ($profils as $p): ?>
                                        <li>
                                            <h4 class="toggle-title"><?php echo htmlspecialchars($p['nom_complet'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                            <div class="toggle-content" style="display: none;">
                                                <div class="block">
                                                    <?php espace_detail(t('field.phone'), $p['telephone']); ?>
                                                    <?php espace_detail(t('field.email'), $p['courriel']); ?>
                                                    <?php espace_detail(t('field.emploi'), t('type.emploi.' . $p['situation_emploi'])); ?>
                                                    <?php espace_detail(t('field.revenu'), ($p['revenu_annuel'] !== null ? number_format((float) $p['revenu_annuel']) . ' $' : null)); ?>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="note"><?php echo htmlspecialchars(t('space.empty'), ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>

                    <li>
                        <h3 class="toggle-title"><?php echo htmlspecialchars(t('space.properties'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="toggle-content" style="display: none;">
                            <div class="block">
                                <?php if ($proprietes): ?>
                                    <ul class="accords">
                                        <?php foreach ($proprietes as $p): ?>
                                        <li>
                                            <h4 class="toggle-title"><?php echo htmlspecialchars($p['adresse'] . ', ' . $p['ville'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                            <div class="toggle-content" style="display: none;">
                                                <div class="block">
                                                    <?php espace_detail(t('field.code'), $p['code_postal']); ?>
                                                    <?php espace_detail(t('field.valeur'), ($p['valeur_estimee'] !== null ? number_format((float) $p['valeur_estimee']) . ' $' : null)); ?>
                                                    <?php espace_detail(t('field.nette'), ($p['valeur_nette'] !== null ? number_format((float) $p['valeur_nette']) . ' $' : null)); ?>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="note"><?php echo htmlspecialchars(t('space.empty'), ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>

                    <li>
                        <h3 class="toggle-title"><?php echo htmlspecialchars(t('space.files'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="toggle-content" style="display: none;">
                            <div class="block">
                                <?php if ($dossiers): ?>
                                    <ul class="accords">
                                        <?php foreach ($dossiers as $d): ?>
                                        <li>
                                            <h4 class="toggle-title"><?php echo htmlspecialchars($d['emprunteur'] . ' — ' . number_format((float) $d['montant_demande']) . ' $', ENT_QUOTES, 'UTF-8'); ?></h4>
                                            <div class="toggle-content" style="display: none;">
                                                <div class="block">
                                                    <?php espace_detail(t('field.rang'), t('type.rang.' . $d['rang'])); ?>
                                                    <?php espace_detail(t('field.type'), t('type.fin.' . $d['type_financement'])); ?>
                                                    <?php espace_detail(t('field.statut'), t('type.statut.' . $d['statut'])); ?>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="note"><?php echo htmlspecialchars(t('space.empty'), ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>

                </ul>
            </div>
            <?php endif; ?>

            <?php if ($__u['acces_creancier']): ?>
            <div class="card">
                <h2><?php echo htmlspecialchars(t('space.cre.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <ul class="accords">

                    <li>
                        <h3 class="toggle-title"><?php echo htmlspecialchars(t('space.cre.profile'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="toggle-content" style="display: none;">
                            <div class="block">
                                <?php if ($creanciers): ?>
                                    <ul class="accords">
                                        <?php foreach ($creanciers as $c): ?>
                                        <li>
                                            <h4 class="toggle-title"><?php echo htmlspecialchars($c['nom'] . ' (' . t('space.cre.type.' . $c['type']) . ')', ENT_QUOTES, 'UTF-8'); ?></h4>
                                            <div class="toggle-content" style="display: none;">
                                                <div class="block">
                                                    <?php espace_detail(t('space.cre.capital'), ($c['capital_disponible'] !== null ? number_format((float) $c['capital_disponible']) . ' $' : null)); ?>
                                                    <?php espace_detail(t('field.permis'), $c['permis_opc']); ?>
                                                    <?php espace_detail(t('field.criteres'), $c['criteres']); ?>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="note"><?php echo htmlspecialchars(t('space.empty'), ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>

                    <li>
                        <h3 class="toggle-title"><?php echo htmlspecialchars(t('space.network'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="toggle-content" style="display: none;">
                            <div class="block">
                                <?php if ($reseau): ?>
                                    <ul class="accords">
                                        <?php foreach ($reseau as $d): ?>
                                        <li>
                                            <h4 class="toggle-title"><?php echo htmlspecialchars(number_format((float) $d['montant_demande']) . ' $ · ' . t('type.rang.' . $d['rang']) . ' · ' . t('type.statut.' . $d['statut']), ENT_QUOTES, 'UTF-8'); ?></h4>
                                            <div class="toggle-content" style="display: none;">
                                                <div class="block">
                                                    <?php espace_detail(t('field.type'), t('type.fin.' . $d['type_financement'])); ?>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="note"><?php echo htmlspecialchars(t('space.empty'), ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>

                </ul>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>
