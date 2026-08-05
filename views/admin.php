<?php
/**
 * Hypohub — Vue vérificateur (admin interne).
 * Réservée aux comptes est_admin=1 (employés). Affiche les demandes en
 * attente : vérifications de certificats AMF et demandes d'accès créancier.
 * Les actions passent par l'API api/demandes_admin.php (JSON) puis rechargent.
 */

// Garde de sécurité redondante (le routeur vérifie déjà)
if (empty($__admin_u) || empty($__admin_u['est_admin'])) {
    redirect('index.php?page=espace');
}
?>
<section class="page-banner">
    <div class="page-banner-inner">
        <h1><?php echo htmlspecialchars(t('admin.title'), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="page-banner-sub"><?php echo htmlspecialchars(t('admin.intro'), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
</section>

<section class="section page-content">
    <div class="section-inner wide">
        <div class="card">
            <h2><?php echo htmlspecialchars(t('admin.amf.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <ul class="accords">
                <li data-admin-panel="amf">
                    <h3 class="toggle-title"><?php echo htmlspecialchars(t('admin.amf.list'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="toggle-content" style="display:none;"><div class="block">
                        <p class="note"><?php echo htmlspecialchars(t('admin.loading'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div></div>
                </li>
            </ul>
        </div>

        <div class="card">
            <h2><?php echo htmlspecialchars(t('admin.creancier.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <ul class="accords">
                <li data-admin-panel="creancier">
                    <h3 class="toggle-title"><?php echo htmlspecialchars(t('admin.creancier.list'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="toggle-content" style="display:none;"><div class="block">
                        <p class="note"><?php echo htmlspecialchars(t('admin.loading'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div></div>
                </li>
            </ul>
        </div>
    </div>
</section>

<script>
(function () {
    'use strict';
    var CSRF = <?php echo json_encode(csrf_token()); ?>;
    var LABEL_OK = <?php echo json_encode(t('admin.ok_label')); ?>;
    var LABEL_NO = <?php echo json_encode(t('admin.refuser')); ?>;
    var LABEL_EMPTY = <?php echo json_encode(t('admin.empty')); ?>;

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : s;
        return d.innerHTML;
    }

    function decis(type, id, decision) {
        var action = (type === 'amf') ? 'verif' : 'creancier';
        return fetch('api/demandes_admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: action, decision: decision, user_id: id, csrf: CSRF })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.ok) window.location.reload();
        });
    }

    document.querySelectorAll('[data-admin-panel]').forEach(function (li) {
        var type = li.getAttribute('data-admin-panel');
        var loaded = false;
        li.querySelector('.toggle-title').addEventListener('click', function () {
            if (loaded) return;
            loaded = true;
            var block = li.querySelector('.toggle-content .block');
            fetch('api/demandes_admin.php')
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.ok) return;
                    var rows = res[type] || [];
                    if (!rows.length) {
                        block.innerHTML = '<p class="note">' + esc(LABEL_EMPTY) + '</p>';
                        return;
                    }
                    var container = block;
                    container.innerHTML = '';
                    rows.forEach(function (row) {
                        var card = document.createElement('div');
                        card.className = 'admin-item';

                        var head = document.createElement('p');
                        head.innerHTML = '<strong>' + esc(row.nom_complet || row.username || row.email) + '</strong> '
                            + '<em>' + esc(row.email || '') + '</em>';

                        var info = document.createElement('p');
                        if (type === 'amf') {
                            info.innerHTML = '<strong><?php echo htmlspecialchars(t('admin.amf.num'), ENT_QUOTES, 'UTF-8'); ?></strong> '
                                + esc(row.certificat_amf || '');
                        } else {
                            info.innerHTML = '<strong><?php echo htmlspecialchars(t('admin.justif'), ENT_QUOTES, 'UTF-8'); ?></strong> '
                                + esc(row.demande_creancier_justification || '');
                        }

                        var btnOk = document.createElement('button');
                        btnOk.type = 'button';
                        btnOk.className = 'btn btn-primary';
                        btnOk.textContent = LABEL_OK;
                        btnOk.onclick = function () {
                            decis(type, row.id, type === 'amf' ? 'verifie' : 'approuve');
                        };

                        var btnNo = document.createElement('button');
                        btnNo.type = 'button';
                        btnNo.className = 'btn btn-outline';
                        btnNo.textContent = LABEL_NO;
                        btnNo.onclick = function () {
                            decis(type, row.id, type === 'amf' ? 'refuse' : 'refuse');
                        };

                        card.appendChild(head);
                        card.appendChild(info);
                        card.appendChild(btnOk);
                        card.appendChild(btnNo);
                        container.appendChild(card);
                    });
                })
                .catch(function () {});
        });
    });
})();
</script>