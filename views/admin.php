<?php
/**
 * Hypohub — Panneau admin (employé vérificateur).
 * Réservé aux comptes est_admin=1. Layout « espace connecté » : une carte Zone
 * Admin avec 3 colonnes — sections (gauche), liste des ID/entités (centre),
 * zone de travail (droite, éditer/accepter). Data via api/demandes_admin.php.
 */

// Garde de sécurité redondante (le routeur vérifie déjà)
if (empty($__admin_u) || empty($__admin_u['est_admin'])) {
    redirect('index.php?page=espace');
}
?>
<section class="section page-content">
    <div class="section-inner wide">
        <div class="card admin-shell">
            <div class="admin-shell-head">
                <h1><?php echo htmlspecialchars(t('admin.zone.title'), ENT_QUOTES, 'UTF-8'); ?></h1>
                <a class="btn btn-outline" href="index.php?page=espace"><?php echo htmlspecialchars(t('admin.back.sp'), ENT_QUOTES, 'UTF-8'); ?></a>
            </div>

            <div class="admin-shell-body">
                <!-- Colonne 1 — sections (badge incrusté sur la 1re lettre) -->
                <nav class="admin-sections" aria-label="Sections admin">
                    <button type="button" class="admin-sec" data-admin-sec="amf">
                        <span class="admin-sec-label"><span class="admin-badge-anchor"><span class="admin-sec-badge" data-badge="amf" hidden></span></span><?php echo htmlspecialchars(t('admin.amf.title'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                    <button type="button" class="admin-sec" data-admin-sec="creancier">
                        <span class="admin-sec-label"><span class="admin-badge-anchor"><span class="admin-sec-badge" data-badge="creancier" hidden></span></span><?php echo htmlspecialchars(t('admin.creancier.title'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                    <button type="button" class="admin-sec" data-admin-sec="utilisateurs">
                        <span class="admin-sec-label"><span class="admin-badge-anchor"><span class="admin-sec-badge" data-badge="utilisateurs" hidden></span></span><?php echo htmlspecialchars(t('admin.users.title'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                </nav>

                <!-- Colonne 2 — liste des entités de la section -->
                <div class="admin-list" id="admin_list">
                    <p class="note"><?php echo htmlspecialchars(t('admin.select'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>

                <!-- Colonne 3 — zone de travail (détails + actions) -->
                <div class="admin-work" id="admin_work">
                    <p class="note"><?php echo htmlspecialchars(t('admin.select'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    'use strict';
    var CSRF = <?php echo json_encode(csrf_token()); ?>;
    var L = {};
    L.EMPTY   = <?php echo json_encode(t('admin.empty')); ?>;
    L.LOAD    = <?php echo json_encode(t('admin.loading')); ?>;
    L.OK      = <?php echo json_encode(t('admin.ok_label')); ?>;
    L.NO      = <?php echo json_encode(t('admin.refuser')); ?>;
    L.SELECT  = <?php echo json_encode(t('admin.select')); ?>;
    L.AMF_NUM = <?php echo json_encode(t('admin.amf.num')); ?>;
    L.JUSTIF  = <?php echo json_encode(t('admin.justif')); ?>;
    L.AMF     = <?php echo json_encode(t('admin.users.amf')); ?>;
    L.ADMIN   = <?php echo json_encode(t('admin.users.admin')); ?>;
    L.CRE     = <?php echo json_encode(t('admin.users.creancier')); ?>;
    L.PROP    = <?php echo json_encode(t('admin.users.proprietaire')); ?>;
    L.TEMP    = <?php echo json_encode(t('admin.users.temp')); ?>;
    L.TEMP_DONE = <?php echo json_encode(t('admin.users.temp_done')); ?>;
    L.PROMOTE = <?php echo json_encode(t('admin.users.promote')); ?>;
    L.DEMOTE  = <?php echo json_encode(t('admin.users.demote')); ?>;

    var data = null;          // réponse GET complète
    var current = null;       // section active ('amf' | 'creancier' | 'utilisateurs')
    var selectedId = null;    // entité sélectionnée

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : s;
        return d.innerHTML;
    }

    function el(tag, cls, html) {
        var e = document.createElement(tag);
        if (cls) e.className = cls;
        if (html !== undefined) e.innerHTML = html;
        return e;
    }

    var listEl = document.getElementById('admin_list');
    var workEl = document.getElementById('admin_work');

    /* ---------- Apps Admin ---------- */
    function post(body, done) {
        return fetch('api/demandes_admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.ok) { if (done) done(res); }
            else if (res.error) { alert(res.error); }
        });
    }

    function reload() {
        fetch('api/demandes_admin.php')
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.ok) return;
                data = res;
                updateBadges();
                if (current) renderList(current);
            })
            .catch(function () {});
    }

    /* --- Badges de comptage sur les sections --- */
    function updateBadges() {
        [['amf', data.amf.length], ['creancier', data.creancier.length], ['utilisateurs', data.utilisateurs.length]].forEach(function (p) {
            var b = document.querySelector('[data-badge="' + p[0] + '"]');
            if (b) {
                b.hidden = (p[1] <= 0);
                b.textContent = p[1];
            }
        });
    }

    /* --- Section active (highlight) --- */
    function setActiveSec(sec) {
        document.querySelectorAll('[data-admin-sec]').forEach(function (b) {
            b.classList.toggle('active', b.getAttribute('data-admin-sec') === sec);
        });
        current = sec;
    }

    /* --- Colonne 2 : liste d'une section --- */
    function renderList(sec) {
        listEl.innerHTML = '';
        var rows = data[sec] || [];
        if (!rows.length) {
            listEl.appendChild(el('p', 'note', esc(L.EMPTY)));
            workEl.innerHTML = '<p class="note">' + esc(L.SELECT) + '</p>';
            selectedId = null;
            return;
        }
        rows.forEach(function (row) {
            var b = el('button', 'admin-list-item',
                '<strong>' + esc(row.nom_complet || row.username || row.email) + '</strong>' +
                '<em>' + esc(row.email || '') + '</em>');
            b.type = 'button';
            b.dataset.id = row.id;
            b.addEventListener('click', function () {
                document.querySelectorAll('.admin-list-item').forEach(function (x) { x.classList.remove('selected'); });
                b.classList.add('selected');
                selectedId = row.id;
                renderWork(sec, row);
            });
            listEl.appendChild(b);
        });
    }

    /* --- Colonne 3 : zone de travail (détails + actions) --- */
    function renderWork(sec, row) {
        workEl.innerHTML = '';

        // En-tête : identité
        workEl.appendChild(el('h2', 'admin-work-title', esc(row.nom_complet || row.username || row.email)));

        var lines = workEl.appendChild(el('div', 'admin-work-detail'));
        if (row.email) lines.appendChild(el('p', null, '<strong>Courriel</strong> · ' + esc(row.email)));

        if (sec === 'amf') {
            lines.appendChild(el('p', null, '<strong>' + esc(L.AMF_NUM) + '</strong> · ' + esc(row.certificat_amf || '')));
        }
        if (sec === 'creancier') {
            lines.appendChild(el('p', null, '<strong>' + esc(L.JUSTIF) + '</strong>'));
            lines.appendChild(el('p', null, esc(row.demande_creancier_justification || '')));
        }
        if (sec === 'utilisateurs') {
            var badges = [];
            if (row.est_admin) badges.push(L.ADMIN);
            if (row.acces_creancier) badges.push(L.CRE);
            if (row.acces_proprietaire) badges.push(L.PROP);
            lines.appendChild(el('p', null, '<strong>' + esc(L.AMF) + '</strong> ' + esc(row.certificat_amf_statut || '') +
                (badges.length ? ' · ' + esc(badges.join(' · ')) : '')));
        }

        // Actions
        var actions = workEl.appendChild(el('div', 'admin-work-actions'));
        if (sec === 'amf' || sec === 'creancier') {
            var ok = el('button', 'btn btn-primary', esc(L.OK));
            ok.type = 'button';
            var no = el('button', 'btn btn-outline', esc(L.NO));
            no.type = 'button';
            ok.addEventListener('click', function () {
                post({ action: sec === 'amf' ? 'verif' : 'creancier', decision: sec === 'amf' ? 'verifie' : 'approuve', user_id: row.id, csrf: CSRF }, function () { reload(); });
            });
            no.addEventListener('click', function () {
                post({ action: sec === 'amf' ? 'verif' : 'creancier', decision: 'refuse', user_id: row.id, csrf: CSRF }, function () { reload(); });
            });
            actions.appendChild(ok);
            actions.appendChild(no);
        }
        if (sec === 'utilisateurs') {
            var bTemp = el('button', 'btn btn-outline', esc(L.TEMP));
            bTemp.type = 'button';
            bTemp.addEventListener('click', function () {
                bTemp.disabled = true;
                post({ action: 'reset_mdp', user_id: row.id, csrf: CSRF }, function (res) {
                    bTemp.disabled = false;
                    if (res.temp) {
                        alert(L.TEMP_DONE.replace('{nom}', res.nom || '') + '\n\n' + res.temp);
                    }
                });
            });
            actions.appendChild(bTemp);

            var bPromo = el('button', 'btn btn-outline', esc(row.est_admin ? L.DEMOTE : L.PROMOTE));
            bPromo.type = 'button';
            bPromo.addEventListener('click', function () {
                post({ action: row.est_admin ? 'revoquer' : 'promote', user_id: row.id, csrf: CSRF }, function () { reload(); });
            });
            actions.appendChild(bPromo);
        }
    }

    /* --- Clic section : charger la liste --- */
    document.querySelectorAll('[data-admin-sec]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var sec = btn.getAttribute('data-admin-sec');
            setActiveSec(sec);
            if (data) { renderList(sec); }
            else {
                listEl.innerHTML = '<p class="note">' + esc(L.LOAD) + '</p>';
            }
        });
    });

    /* --- Démarrage : chargement des données + rafraîchissement des badges --- */
    fetch('api/demandes_admin.php')
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.ok) return;
            data = res;
            updateBadges();
            if (current) renderList(current);
        })
        .catch(function () {});
})();
</script>