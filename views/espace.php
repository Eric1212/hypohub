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

// Balayage d'expiration : les dossiers inactifs 90 jours passent à 'expire'.
expire_dossiers_inactifs();

$pdo       = db();
$profils   = array();
$proprietes = array();
$dossiers  = array();
$creanciers = array();
$reseau    = array();

if ($__u['acces_proprietaire']) {
    $st = $pdo->prepare('SELECT id, prenom, nom, date_naissance, courriel, telephone, app, adresse, ville, code_postal, province, nom_compagnie, neq, statut FROM profils_proprietaire WHERE cree_par = ? ORDER BY id DESC');
    $st->execute(array($__u['id']));
    $profils = $st->fetchAll();

    $st = $pdo->prepare('SELECT id, adresse, ville, code_postal, valeur_estimee, valeur_nette FROM profils_propriete WHERE cree_par = ? ORDER BY id DESC');
    $st->execute(array($__u['id']));
    $proprietes = $st->fetchAll();

    $st = $pdo->prepare(
        'SELECT d.id, d.montant_demande, d.rang, d.type_financement, d.statut,
                p.prenom, p.nom, p.nom_compagnie, p.neq, p.ville
           FROM dossiers_emprunt d
           JOIN profils_proprietaire p ON p.id = d.profil_proprietaire_id
          WHERE d.cree_par = ?
          ORDER BY d.id DESC'
    );
    $st->execute(array($__u['id']));
    $dossiers = $st->fetchAll();
}

if ($__u['acces_creancier']) {
    $st = $pdo->prepare('SELECT id, nom, type, capital_disponible, criteres FROM profils_creancier WHERE user_id = ? ORDER BY id DESC');
    $st->execute(array($__u['id']));
    $creanciers = $st->fetchAll();

    // Dossiers visibles par le réseau : PAS les profils (masqués tant que pas d'acceptation).
    // Le réseau montre TOUS les dossiers du marché, y compris ceux créés par
    // l'utilisateur connecté (décision Éric, 2026-08-03 : pas d'exclusion).
$st = $pdo->query(
        "SELECT id, montant_demande, rang, type_financement, statut
           FROM dossiers_emprunt
          WHERE statut IN ('nouveau', 'act')
          ORDER BY id DESC"
    );
    $reseau = $st->fetchAll();
}

// Documents par profil propriétaire (pour l'affichage dans l'espace)
$profils_docs = array();
if ($__u['acces_proprietaire']) {
    $lang_key = hypohub_current_lang() === 'en' ? 'nom_en' : 'nom_fr';
    $st = $pdo->prepare(
        "SELECT d.profil_id AS pid, d.id AS doc_id, d.nom_fichier,
                t.$lang_key AS doc_type
           FROM documents d
        LEFT JOIN documents_types t ON t.id = d.type_id
          WHERE d.profil_id IN (SELECT p2.id FROM profils_proprietaire p2 WHERE p2.cree_par = ?)
          ORDER BY d.date_upload DESC"
    );
    $st->execute(array((int) $__u['id']));
    foreach ($st->fetchAll() as $row) {
        $profils_docs[$row['pid']][] = $row;
    }
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

<section class="section page-content space-layout">
    <div class="section-inner wide">

        <?php /* ---------- Zone compte (réglages + demandes) ---------- */ ?>
        <div class="card account-card">
            <h2><?php echo htmlspecialchars(t('space.account.title'), ENT_QUOTES, 'UTF-8'); ?></h2>

            <!-- Réglages : nom, utilisateur, courriel, certificat AMF (autosave : pastille dans chaque champ) -->
            <form id="compte_form" class="auth-form" data-err-network="<?php echo htmlspecialchars(t('auth.error.network'), ENT_QUOTES, 'UTF-8'); ?>" novalidate>
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">

                <div class="account-grid">
                <label>
                    <span><?php echo htmlspecialchars(t('account.nom_complet'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="field-wrap">
                        <input type="text" name="nom_complet" value="<?php echo htmlspecialchars($__u['nom_complet'], ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="save-dot" data-save-surface data-save-state="saved"></span>
                    </span>
                </label>
                <label>
                    <span><?php echo htmlspecialchars(t('account.username'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="field-wrap">
                        <input type="text" name="username" value="<?php echo htmlspecialchars($__u['username'] ?: '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="3-80 caractères, accueil a-z0-9_.-">
                        <span class="save-dot" data-save-surface data-save-state="saved"></span>
                    </span>
                </label>
                <label>
                    <span><?php echo htmlspecialchars(t('account.courriel'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="field-wrap">
                        <input type="email" name="courriel" value="<?php echo htmlspecialchars($__u['email'], ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="save-dot" data-save-surface data-save-state="saved"></span>
                    </span>
                </label>
                </div>
                <p class="auth-error" data-compte-error hidden></p>
            </form>

            <!-- Certificat AMF (une rangée : champ + bouton) -->
            <form id="certificat_amf_form" class="auth-form account-inline" data-err-network="<?php echo htmlspecialchars(t('auth.error.network'), ENT_QUOTES, 'UTF-8'); ?>" data-lock-warn="<?php echo htmlspecialchars(t('account.amf.lock_warn'), ENT_QUOTES, 'UTF-8'); ?>" data-lock-ok="<?php echo htmlspecialchars(t('account.amf.lock_ok'), ENT_QUOTES, 'UTF-8'); ?>" novalidate>
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="amf-row">
                    <label class="amf-label">
                        <span class="amf-label-text"><?php echo htmlspecialchars(t('account.amf'), ENT_QUOTES, 'UTF-8'); ?>
                            <?php
                            $amf_statut = $__u['certificat_amf_statut'];
                            $amf_result = $__u['certificat_amf'];
                            if ($amf_statut === 'verifie'):
                                ?>
                            <strong class="badge badge-ok"><?php echo htmlspecialchars(t('account.amf.verifie'), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php elseif ($amf_statut === 'en_attente'): ?>
                            <strong class="badge badge-wait"><?php echo htmlspecialchars(t('account.amf.attente'), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php endif; ?>
                        </span>
                        <span class="field-wrap">
                            <input type="text" name="certificat_amf" value="<?php echo htmlspecialchars($amf_result ?: '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="ex. 123456" <?php echo $amf_statut === 'verifie' ? 'readonly' : ''; ?>>
                            <span class="save-dot" data-save-surface data-save-state="saved"></span>
                        </span>
                    </label>
                    <button type="submit" data-amf-action="demander" class="btn btn-outline" <?php echo $amf_statut === 'verifie' ? 'hidden' : ''; ?> <?php echo ($amf_result === null || $amf_result === '') ? 'disabled' : ''; ?>><?php echo htmlspecialchars(t('account.amf.demander'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <?php if (empty($__u['acces_creancier'])): ?>
                        <?php if ($__u['demande_creancier_statut'] === 'en_attente'): ?>
                            <button type="button" class="btn btn-outline" data-amp-spacer data-cre-attente disabled><?php echo htmlspecialchars(t('account.cre.demander'), ENT_QUOTES, 'UTF-8'); ?></button>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary" data-amp-spacer data-demande-creancier-open><?php echo htmlspecialchars(t('account.cre.demander'), ENT_QUOTES, 'UTF-8'); ?></button>
                        <?php endif; ?>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline" data-amp-spacer disabled><?php echo htmlspecialchars(t('account.cre.deja'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <?php endif; ?>
                </div>
                <p class="account-error" data-amf-error hidden></p>
            </form>
        </div>

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
                                            <h4 class="toggle-title"><?php
                                                if ($p['nom_compagnie'] !== null || $p['neq'] !== null) {
                                                    echo htmlspecialchars(t('space.prop.societe_label', array('nom' => ($p['nom_compagnie'] ?: $p['prenom'] . ' ' . $p['nom']))), ENT_QUOTES, 'UTF-8');
                                                } else {
                                                    echo htmlspecialchars($p['prenom'] . ' ' . $p['nom'], ENT_QUOTES, 'UTF-8');
                                                }
                                            ?>
                                            <button type="button" class="btn-mini" data-edit-profil="<?php echo (int) $p['id']; ?>"
                                                data-prenom="<?php echo htmlspecialchars($p['prenom'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-nom="<?php echo htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-naissance="<?php echo htmlspecialchars($p['date_naissance'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-courriel="<?php echo htmlspecialchars($p['courriel'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-telephone="<?php echo htmlspecialchars($p['telephone'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-app="<?php echo htmlspecialchars($p['app'] ?: '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-adresse="<?php echo htmlspecialchars($p['adresse'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-ville="<?php echo htmlspecialchars($p['ville'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-code="<?php echo htmlspecialchars($p['code_postal'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-province="<?php echo htmlspecialchars($p['province'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-compagnie="<?php echo htmlspecialchars($p['nom_compagnie'] ?: '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-neq="<?php echo htmlspecialchars($p['neq'] ?: '', ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(t('btn.edit'), ENT_QUOTES, 'UTF-8'); ?></button>
                                            </h4>
                                            <div class="toggle-content" style="display: none;">
                                                <div class="block">
                                                    <?php espace_detail(t('field.naissance'), $p['date_naissance']); ?>
                                                    <?php espace_detail(t('field.email'), $p['courriel']); ?>
                                                    <?php espace_detail(t('field.phone'), $p['telephone']); ?>
                                                    <?php
                                                        $adresse_complete = trim(($p['app'] !== null ? $p['app'] . ' — ' : '') . $p['adresse'] . ', ' . $p['ville'] . ($p['code_postal'] ? ', ' . $p['code_postal'] : ''));
                                                        espace_detail(t('field.adresse'), $adresse_complete);
                                                    ?>
                                                    <?php espace_detail(t('field.compagnie'), $p['nom_compagnie']); ?>
                                                    <?php espace_detail(t('field.neq'), $p['neq']); ?>
                                                    <?php if (isset($profils_docs[$p['id']])): ?>
                                                    <p><strong><?php echo htmlspecialchars(t('create.profil.documents'), ENT_QUOTES, 'UTF-8'); ?> :</strong></p>
                                                    <ul class="doc-list">
                                                        <?php foreach ($profils_docs[$p['id']] as $doc): ?>
                                                        <li>
                                                            <a href="api/download_document.php?id=<?php echo (int) $doc['doc_id']; ?>"><?php echo htmlspecialchars($doc['doc_type'] . ' · ' . $doc['nom_fichier'], ENT_QUOTES, 'UTF-8'); ?></a>
                                                        </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="note"><?php echo htmlspecialchars(t('space.empty'), ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                                <button type="button" class="btn btn-outline btn-add" data-create-open="profil"><?php echo htmlspecialchars(t('create.btn.profil'), ENT_QUOTES, 'UTF-8'); ?></button>
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
                                <button type="button" class="btn btn-outline btn-add" data-create-open="propriete"><?php echo htmlspecialchars(t('create.btn.propriete'), ENT_QUOTES, 'UTF-8'); ?></button>
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
                                            <h4 class="toggle-title"><?php
                                                $dossier_nom = ($d['nom_compagnie'] !== null || $d['neq'] !== null)
                                                    ? ($d['nom_compagnie'] ?: ($d['prenom'] . ' ' . $d['nom']))
                                                    : ($d['prenom'] . ' ' . $d['nom']);
                                                echo htmlspecialchars($dossier_nom . ' — ' . number_format((float) $d['montant_demande']) . ' $', ENT_QUOTES, 'UTF-8');
                                            ?></h4>
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
                                <button type="button" class="btn btn-outline btn-add" data-create-open="dossier"><?php echo htmlspecialchars(t('create.btn.dossier'), ENT_QUOTES, 'UTF-8'); ?></button>
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
                                                    <?php espace_detail(t('field.criteres'), $c['criteres']); ?>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="note"><?php echo htmlspecialchars(t('space.empty'), ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                                <button type="button" class="btn btn-outline btn-add" data-create-open="creancier"><?php echo htmlspecialchars(t('create.btn.creancier'), ENT_QUOTES, 'UTF-8'); ?></button>
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

<?php
// Types de documents (dynamiques — table documents_types)
$create_doc_types = array();
try {
    $create_doc_types = $pdo->query('SELECT id, nom_fr, nom_en FROM documents_types WHERE actif = 1 ORDER BY id')->fetchAll();
} catch (Exception $e) {
    // Table pas encore créée par db_schema() — vue install en cours
}

$create_profils    = isset($profils) ? $profils : array();
$create_proprietes = isset($proprietes) ? $proprietes : array();
include __DIR__ . '/partials/create_modal.php';
?>
