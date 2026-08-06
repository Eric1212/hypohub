/**
 * Hypohub — JavaScript du site.
 *
 * 1. Navigation AJAX (Turbolinks-like) : un clic sur un lien interne charge la
 *    page via fetch et remplace uniquement le <main>. Le header et le footer
 *    ne sont jamais rechargés → ils ne changent jamais. Fallback : si le fetch
 *    échoue ou si le JS est désactivé, les liens fonctionnent normalement.
 * 2. Accordéon FAQ (un seul élément ouvert).
 * 3. Formulaire de contact (envoi via mailto, sans backend au MVP).
 */

document.addEventListener('DOMContentLoaded', function () {
    applyZoom();
    initPage();
    initAuth();
    initNav();
    window.addEventListener('popstate', function () {
        navigate(location.pathname + location.search);
    });
    window.addEventListener('resize', applyZoom);
});

/**
 * Grossissement adaptatif (règle d'Éric).
 * La page est calibrée pour une hauteur de 720 px. Sur un écran plus grand,
 * on grossit TOUT en agrandissant la taille de police racine (le layout est
 * en rem → tout suit, sans toucher aux 100vh → jamais de scroll).
 *   scale = (hauteur ÷ 720) × 0.75
 *   720p  → 0.75     1080p → 1.5 × 0.75 = 1.125 (+12,5 %)
 *   1440p → 2.0 × 0.75 = 1.5 (plafond)    plancher : 0.75
 */
var ZOOM_BASE_HEIGHT = 720;
var ZOOM_FACTOR = 0.9;
var ZOOM_MIN = 0.9;
var ZOOM_MAX = 1.5;

function applyZoom() {
    var h = window.innerHeight;
    if (!h) return;
    var scale = (h / ZOOM_BASE_HEIGHT) * ZOOM_FACTOR;
    scale = Math.max(ZOOM_MIN, Math.min(ZOOM_MAX, scale));
    document.documentElement.style.fontSize = (16 * scale).toFixed(4) + 'px';
}

/** Réattache les comportements de page (appelé au chargement et après chaque navigation AJAX). */
function initPage() {
    // Accordéon FAQ : ouvrir un élément ferme les autres.
    var details = document.querySelectorAll('.faq-item');
    details.forEach(function (d) {
        d.addEventListener('toggle', function () {
            if (d.open) {
                details.forEach(function (other) {
                    if (other !== d) other.open = false;
                });
            }
        });
    });

    // Accordéons de l'espace membre (règle d'Éric : H3 ET H4) :
    // clic sur .toggle-title → replie/déplie le .toggle-content suivant.
    // Un seul panneau ouvert à la fois dans une série (un ul.accords donné).
    document.querySelectorAll('.toggle-title').forEach(function (h) {
        var content = h.nextElementSibling;
        if (!content || !content.classList.contains('toggle-content')) return;
        h.addEventListener('click', function () {
            var list = h.closest('ul.accords');
            var wasClosed = content.style.display === 'none';

            // Fermer les autres panneaux de la même série.
            if (list) {
                list.querySelectorAll(':scope > li > .toggle-content').forEach(function (other) {
                    if (other === content) return;
                    other.style.display = 'none';
                    var t = other.previousElementSibling;
                    if (t) {
                        t.classList.remove('active');
                        if (t.tagName === 'H3') t.parentElement.classList.remove('active');
                    }
                });
            }

            content.style.display = wasClosed ? '' : 'none';
            h.classList.toggle('active', wasClosed);
            if (h.tagName === 'H3') {
                // La barre du H3 est masquée seulement s'il contient des H4
                // (qui portent leurs propres barres). Un H3 sans H4 garde
                // sa barre de fin même ouvert.
                var hasH4 = !!content.querySelector('.toggle-title');
                h.parentElement.classList.toggle('active', wasClosed && hasH4);
            }
        });
    });

    // Modales de création (espace membre) : ouverture, fermeture, soumission.
    initCreate();

    // Zone compte : réglages, certificat AMF, demande d'accès créancier.
    initAccount();

    // Modale de visualisation de documents.
    initViewer();

    // Défilement fluide vers les ancres internes.
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var href = a.getAttribute('href');
            if (!href || href === '#') {
                return; // ancre vide : ne pas lancer querySelector('#')
            }
            var target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Formulaire de contact : construit un mailto: et ouvre le client courriel.
    var form = document.getElementById('contact_form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var name = document.getElementById('ct_name').value.trim();
            var email = document.getElementById('ct_email').value.trim();
            var subject = document.getElementById('ct_subject').value.trim();
            var message = document.getElementById('ct_message').value.trim();
            var body = 'Nom : ' + name + '\nCourriel : ' + email + '\n\n' + message;
            var href = 'mailto:info@bafinanciere.ca'
                + '?subject=' + encodeURIComponent(subject)
                + '&body=' + encodeURIComponent(body);
            window.location.href = href;
        });
    }
}

/**
 * Modale de connexion / création de compte.
 * La modale vit dans le DOM global (jamais rechargée par la nav AJAX) :
 * ses écouteurs ne sont attachés qu'une fois, au chargement initial.
 */
function openAuthModal(acces) {
    var modal = document.getElementById('auth_modal');
    if (!modal) return;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');

    if (acces) {
        // Présélection : onglet création + carte du profil choisi (ex. CTA des pages profils).
        var loginForm = document.getElementById('auth_login_form');
        var registerForm = document.getElementById('auth_register_form');
        loginForm.hidden = true;
        registerForm.hidden = false;
        modal.querySelectorAll('.auth-tab').forEach(function (o) {
            o.classList.toggle('active', o.getAttribute('data-auth-tab') === 'register');
        });
        var card = registerForm.querySelector('.auth-card[data-acces="' + acces + '"]');
        if (card) card.click();
    }

    var email = modal.querySelector('#auth_login_form input[name=email]');
    if (email) email.focus();
}

function closeAuthModal() {
    var modal = document.getElementById('auth_modal');
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
}

function initAuth() {
    var modal = document.getElementById('auth_modal');
    if (!modal) return;

    // Ouverture (délégation : fonctionne même pour du contenu chargé en AJAX).
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-auth-open]');
        if (el) {
            e.preventDefault();
            openAuthModal(el.getAttribute('data-auth-acces') || null);
        }
    });

    // Fermeture : croix + clic sur le voile + touche Échap.
    modal.querySelectorAll('[data-auth-close]').forEach(function (el) {
        el.addEventListener('click', closeAuthModal);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAuthModal();
    });

    var loginForm = document.getElementById('auth_login_form');
    var registerForm = document.getElementById('auth_register_form');

    // Onglets Connexion / Créer un compte.
    modal.querySelectorAll('.auth-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            modal.querySelectorAll('.auth-tab').forEach(function (o) { o.classList.remove('active'); });
            tab.classList.add('active');
            var target = tab.getAttribute('data-auth-tab');
            loginForm.hidden = target !== 'login';
            registerForm.hidden = target !== 'register';
        });
    });

    // Cartes de profil : « Je suis courtier » coche les deux accès.
    var accesProp = registerForm.querySelector('input[name=acces_proprietaire]');
    var accesCre = registerForm.querySelector('input[name=acces_creancier]');
    registerForm.querySelectorAll('.auth-card').forEach(function (card) {
        card.addEventListener('click', function () {
            registerForm.querySelectorAll('.auth-card').forEach(function (c) { c.classList.remove('active'); });
            card.classList.add('active');
            var acces = card.getAttribute('data-acces');
            accesProp.value = (acces === 'proprietaire' || acces === 'courtier') ? '1' : '0';
            accesCre.value = (acces === 'creancier' || acces === 'courtier') ? '1' : '0';
        });
    });

    attachAuthForm(registerForm, 'api/auth_register.php');

    // Connexion : si le serveur répond {changer_mdp:true} (mot de passe
    // temporaire), on bascule sur le formulaire de vrai mot de passe au lieu de
    // recharger. Les identifiants (email + temporaire) restent en mémoire.
    var changerMdpForm = document.getElementById('auth_changer_mdp_form');
    var loginForm2 = document.getElementById('auth_login_form');
    attachJsonForm(loginForm2, 'api/auth_login.php', function (res) {
        if (res && res.changer_mdp && changerMdpForm) {
            changerMdpForm.hidden = false;
            loginForm2.hidden = true;
            modal.querySelectorAll('.auth-tab').forEach(function (o) {
                o.classList.toggle('active', o.getAttribute('data-auth-tab') === 'login');
            });
            var mailIn = changerMdpForm.querySelector('input[name="email"]');
            var pwdIn = changerMdpForm.querySelector('input[name="password"]');
            var tmpMail = loginForm2.querySelector('input[name="email"]');
            var tmpPwd = loginForm2.querySelector('input[name="password"]');
            if (mailIn) mailIn.value = tmpMail ? tmpMail.value : '';
            if (pwdIn) pwdIn.value = tmpPwd ? tmpPwd.value : '';
            var focus = changerMdpForm.querySelector('input[name="nouveau"]');
            if (focus) focus.focus();
            return;
        }
        window.location.href = 'index.php?page=espace';
    });

    // Soumission : valide la confirmation, puis définit le vrai mot de passe.
    if (changerMdpForm) {
        changerMdpForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var errEl = changerMdpForm.querySelector('[data-chgmdp-error]');
            if (errEl) errEl.hidden = true;

            var nouveau = changerMdpForm.querySelector('input[name="nouveau"]').value;
            var confirm = changerMdpForm.querySelector('input[name="confirmation"]').value;
            if (nouveau !== confirm) {
                if (errEl) {
                    errEl.textContent = changerMdpForm.getAttribute('data-err-confirm') || '…';
                    errEl.hidden = false;
                }
                return;
            }

            var data = {};
            new FormData(changerMdpForm).forEach(function (v, k) { data[k] = v; });

            var btn = changerMdpForm.querySelector('button[type=submit]');
            var original = btn.textContent;
            btn.disabled = true;
            btn.textContent = '…';

            fetch('api/changer_mdp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                btn.disabled = false;
                btn.textContent = original;
                if (res.ok) {
                    window.location.href = 'index.php?page=espace';
                } else {
                    if (errEl) {
                        errEl.textContent = res.error || changerMdpForm.getAttribute('data-err-network');
                        errEl.hidden = false;
                    }
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.textContent = original;
                if (errEl) {
                    errEl.textContent = changerMdpForm.getAttribute('data-err-network');
                    errEl.hidden = false;
                }
            });
        });
    }
}

/** Soumet un formulaire de la modale en JSON ; recharge la page en cas de succès. */
function attachAuthForm(form, url) {
    attachJsonForm(form, url, function () {
        window.location.href = 'index.php?page=espace';
    });
}

/** Soumet un formulaire en JSON ; onSuccess est appelée si le serveur répond {ok:true}. */
function attachJsonForm(form, url, onSuccess) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var errEl = form.querySelector('[data-auth-error],[data-create-error]');
        if (errEl) errEl.hidden = true;

        var data = {};
        new FormData(form).forEach(function (v, k) { data[k] = v; });

        var btn = form.querySelector('button[type=submit]');
        var original = btn.textContent;
        btn.disabled = true;
        btn.textContent = '…';

        var target = (typeof url === 'function') ? url() : url;

        fetch(target, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            btn.disabled = false;
            btn.textContent = original;
            if (res.ok) {
                onSuccess(res);
            } else {
                if (errEl) {
                    errEl.textContent = res.error || form.getAttribute('data-err-network');
                    errEl.hidden = false;
                }
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.textContent = original;
            if (errEl) {
                errEl.textContent = form.getAttribute('data-err-network');
                errEl.hidden = false;
            }
        });
    });
}

/**
 * Modales de création d'items (espace membre).
 * Une modale unique (#create_modal) contient un formulaire par type
 * (profil / propriete / dossier / creancier) ; le bouton cliqué
 * ([data-create-open]) montre le formulaire correspondant.
 */
function initCreate() {
    var modal = document.getElementById('create_modal');
    if (!modal) return;

    document.querySelectorAll('[data-create-open]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openCreateModal(btn.getAttribute('data-create-open'));
        });
    });

    // Passage en mode édition d'un profil : pré-remplit la modale + charge les docs
    document.querySelectorAll('[data-edit-profil]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var data = {
                profil_id: btn.getAttribute('data-edit-profil'),
                prenom: btn.getAttribute('data-prenom') || '',
                nom: btn.getAttribute('data-nom') || '',
                date_naissance: btn.getAttribute('data-naissance') || '',
                courriel: btn.getAttribute('data-courriel') || '',
                telephone: btn.getAttribute('data-telephone') || '',
                app: btn.getAttribute('data-app') || '',
                adresse: btn.getAttribute('data-adresse') || '',
                ville: btn.getAttribute('data-ville') || '',
                code_postal: btn.getAttribute('data-code') || '',
                province: btn.getAttribute('data-province') || '',
                nom_compagnie: btn.getAttribute('data-compagnie') || '',
                neq: btn.getAttribute('data-neq') || '',
            };
            openCreateModal('profil', data);
        });
    });

    modal.querySelectorAll('[data-create-close]').forEach(function (el) {
        el.addEventListener('click', closeCreateModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeCreateModal();
    });

    modal.querySelectorAll('form[data-create-type]').forEach(function (form) {
        var type = form.getAttribute('data-create-type');
        if (type === 'profil') {
            // Flux profil séquencé : uploads en staging PUIS création — le
            // serveur rattache les staged au profil créé (contrat validé Éric).
            bindProfilSubmit(form);
            return;
        }
        attachJsonForm(form, 'api/create_' + type + '.php', function () {
            window.location.href = 'index.php?page=espace';
        });
    });
}

/**
 * Zone compte (espace membre) : réglages (nom/username/courriel), certificat
 * AMF (enregistrer / demander vérification) et modale demande accès créancier
 * (justification, 2000 mots max).
 */
function initAccount() {
    // --- Modale demande d'accès créancier ---
    var dcModal = document.getElementById('demande_creancier_modal');
    if (dcModal) {
        function openDc() {
            dcModal.classList.add('open');
            dcModal.setAttribute('aria-hidden', 'false');
        }
        function closeDc() {
            dcModal.classList.remove('open');
            dcModal.setAttribute('aria-hidden', 'true');
            var err = dcModal.querySelector('[data-demande-creancier-error]');
            if (err) err.hidden = true;
        }
        document.querySelectorAll('[data-demande-creancier-open]').forEach(function (b) {
            b.addEventListener('click', openDc);
        });
        dcModal.querySelectorAll('[data-demande-creancier-close]').forEach(function (el) {
            el.addEventListener('click', closeDc);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDc();
        });

        // Compteur de mots (limite : 2000).
        var ta = dcModal.querySelector('textarea[name="justification"]');
        var compteur = dcModal.querySelector('[data-mots-compteur]');
        if (ta && compteur) {
            function majCompteur() {
                var mots = ta.value.trim() ? ta.value.trim().split(/\s+/).length : 0;
                compteur.textContent = mots + ' / 2000 ' + accountI18n('mots');
                if (mots > 2000) {
                    compteur.classList.add('note-error');
                } else {
                    compteur.classList.remove('note-error');
                }
            }
            ta.addEventListener('input', majCompteur);
            majCompteur();
        }

        var dcForm = dcModal.querySelector('form');
        if (dcForm) {
            dcForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var errEl = dcModal.querySelector('[data-demande-creancier-error]');
                errEl.hidden = true;
                var btn = dcForm.querySelector('button[type="submit"]');
                var original = btn.textContent;
                btn.disabled = true;
                btn.textContent = '…';

                var data = {};
                new FormData(dcForm).forEach(function (v, k) { data[k] = v; });

                fetch('api/demande_creancier.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    btn.disabled = false;
                    btn.textContent = original;
                    if (res.ok) {
                        window.location.href = 'index.php?page=espace';
                    } else {
                        errEl.textContent = res.error || dcForm.getAttribute('data-err-network');
                        errEl.hidden = false;
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.textContent = original;
                    errEl.textContent = dcForm.getAttribute('data-err-network');
                    errEl.hidden = false;
                });
            });
        }
    }

    // --- Réglages du compte : autosave par champ (3 s de pause au clavier) ---
    var compteForm = document.getElementById('compte_form');
    if (compteForm) {
        var errEl = compteForm.querySelector('[data-compte-error]');
        var csrfInput = compteForm.querySelector('input[name="csrf"]');

        compteForm.querySelectorAll('.field-wrap').forEach(function (wrap) {
            var input = wrap.querySelector('input');
            var dot = wrap.querySelector('[data-save-surface]');
            if (!input || !dot) { return; }

            var timer = null;

            var doSave = function () {
                var payload = {};
                payload[input.name] = input.value;
                payload.csrf = csrfInput.value;

                errEl.hidden = true;

                fetch('api/update_compte.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.ok) {
                        dot.setAttribute('data-save-state', 'saved');
                    } else {
                        errEl.textContent = res.error || compteForm.getAttribute('data-err-network');
                        errEl.hidden = false;
                        dot.setAttribute('data-save-state', 'error');
                    }
                })
                .catch(function () {
                    errEl.textContent = compteForm.getAttribute('data-err-network');
                    errEl.hidden = false;
                    dot.setAttribute('data-save-state', 'error');
                });
            };

            // Debounce propre à ce champ : 3 s de pause au clavier (event "input"),
            // aucune dépendance au focus/blur.
            var surSaisie = function () {
                dot.setAttribute('data-save-state', 'idle');
                if (timer) { clearTimeout(timer); }
                timer = setTimeout(function () { timer = null; doSave(); }, 3000);
            };

            input.addEventListener('input', surSaisie);
        });
    }

    // --- Certificat AMF ---
    // Autosave du n° via update_compte.php (même pattern que les champs de la
    // carte réglages : pastille idle → 3 s → sauvegarde → vert/rouge).
    // Bouton « Demander à être courtier » : soumet la demande de vérification
    // (certificat_amf.php action=demander, cousin de demande_creancier.php).
    // Champ verrouillé si certificat vérifié : alerte avant toute édition, et
    // révocation immédiate côté serveur (action=retirer) si l'utilisateur
    // confirme — l'accès courtier/créancier tombe, la demande repart à zéro.
    var amfForm = document.getElementById('certificat_amf_form');
    if (amfForm) {
        var amfInput = amfForm.querySelector('input[name="certificat_amf"]');
        var amfBtn = amfForm.querySelector('button[data-amf-action="demander"]');
        var amfDot = amfForm.querySelector('[data-save-surface]');
        var amfErr = amfForm.querySelector('[data-amf-error]');
        var csrfAmf = amfForm.querySelector('input[name="csrf"]');

        // --- Autosave du n° (comme les 3 champs réglages) ---
        var amfTimer = null;
        var amfSave = function () {
            if (!csrfAmf) { return; }
            var payload = { certificat_amf: amfInput.value, csrf: csrfAmf.value };
            amfErr.hidden = true;
            fetch('api/update_compte.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.ok) {
                    amfDot.setAttribute('data-save-state', 'saved');
                } else {
                    amfErr.textContent = res.error || amfForm.getAttribute('data-err-network');
                    amfErr.hidden = false;
                    amfDot.setAttribute('data-save-state', 'error');
                }
            })
            .catch(function () {
                amfErr.textContent = amfForm.getAttribute('data-err-network');
                amfErr.hidden = false;
                amfDot.setAttribute('data-save-state', 'error');
            });
        };

        // --- Dégrisement du bouton + état de la pastille à la frappe ---
        var majAmf = function () {
            if (amfBtn && !amfBtn.hidden) {
                amfBtn.disabled = (amfInput.value.trim() === '');
            }
            if (amfDot) { amfDot.setAttribute('data-save-state', 'idle'); }
            if (amfTimer) { clearTimeout(amfTimer); }
            amfTimer = setTimeout(function () { amfTimer = null; amfSave(); }, 3000);
        };
        amfInput.addEventListener('input', majAmf);

        // --- Champ verrouillé : alerte avant édition, révocation immédiate ---
        if (amfInput.hasAttribute('readonly')) {
            var demanderDeverrouillage = function (e) {
                if (!amfInput.hasAttribute('readonly')) { return; }
                e.preventDefault();
                var message = amfForm.getAttribute('data-lock-warn') || '…';
                if (!window.confirm(message)) { return; }

                // Révocation immédiate (Option A) : l'accès tombe maintenant.
                var data = { action: 'retirer', csrf: csrfAmf.value };
                fetch('api/certificat_amf.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.ok) {
                        amfInput.removeAttribute('readonly');
                        amfDot.setAttribute('data-save-state', 'idle');
                        var ok = amfForm.getAttribute('data-lock-ok') || '';
                        if (ok) {
                            amfErr.textContent = ok;
                            amfErr.hidden = false;
                        }
                        amfInput.focus();
                    } else {
                        amfErr.textContent = res.error || amfForm.getAttribute('data-err-network');
                        amfErr.hidden = false;
                    }
                })
                .catch(function () {
                    amfErr.textContent = amfForm.getAttribute('data-err-network');
                    amfErr.hidden = false;
                });
            };
            amfInput.addEventListener('mousedown', demanderDeverrouillage);
            amfInput.addEventListener('focus', demanderDeverrouillage);
        }

        // --- Bouton « Demander à être courtier » : vérification par un humain ---
        amfForm.querySelectorAll('button[data-amf-action]').forEach(function (b) {
            b.addEventListener('click', function (e) {
                e.preventDefault();
                var errEl = amfForm.querySelector('[data-amf-error]');
                errEl.hidden = true;
                var btn = b;
                var original = btn.textContent;
                btn.disabled = true;
                btn.textContent = '…';

                var data = {};
                new FormData(amfForm).forEach(function (v, k) { data[k] = v; });
                data.action = btn.getAttribute('data-amf-action');

                fetch('api/certificat_amf.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    btn.disabled = false;
                    btn.textContent = original;
                    if (res.ok) {
                        window.location.href = 'index.php?page=espace';
                    } else {
                        errEl.textContent = res.error || amfForm.getAttribute('data-err-network');
                        errEl.hidden = false;
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.textContent = original;
                    errEl.textContent = amfForm.getAttribute('data-err-network');
                    errEl.hidden = false;
                });
            });
        });
    }
}

/** Libellés i18n locaux de la zone compte (selon <html lang>). */
function accountI18n(key) {
    var lang = (document.documentElement.getAttribute('lang') || 'fr').slice(0, 2);
    var dict = {
        'mots': { fr: 'mots', en: 'words' },
    };
    var map = dict[key];
    return map ? (map[lang] || map.fr) : key;
}

/** Soumission du formulaire profil : stage des fichiers, puis POST profil. */
function bindProfilSubmit(form) {
    // Staging immédiat dès la sélection des fichiers : ils apparaissent dans la
    // liste de la modale (actions disponibles), et survivent au F5.
    var filesInput = form.querySelector('input[type=file][name="doc_fichiers[]"]');
    if (filesInput) {
        filesInput.addEventListener('change', function () {
            var files = filesInput.files ? Array.prototype.slice.call(filesInput.files) : [];
            if (files.length === 0) return;
            var typeId = form.querySelector('[name="doc_type_id"]').value;
            var csrf = form.querySelector('[name="csrf"]').value;
            var i = 0;
            function next() {
                if (i >= files.length) {
                    var pidEl = form.querySelector('[name="profil_id"]');
                    if (pidEl && pidEl.value) { loadDocuments(pidEl.value, form); }
                    else { loadStaged(form); }
                    filesInput.value = ''; // permet de re-sélectionner le même fichier
                    return;
                }
                var fd = new FormData();
                fd.append('csrf', csrf);
                fd.append('type_id', typeId);
                fd.append('fichier', files[i]);
                i++;
                fetch('api/stage_document.php', { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function () { next(); })
                    .catch(function () { next(); });
            }
            next();
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var errEl = form.querySelector('[data-create-error]');
        if (errEl) errEl.hidden = true;

        var filesInput = form.querySelector('input[type=file][name="doc_fichiers[]"]');
        var filesInput = form.querySelector('input[type=file][name="doc_fichiers[]"]');
        var files = filesInput && filesInput.files ? Array.prototype.slice.call(filesInput.files) : [];

        var btn = form.querySelector('button[type=submit]');
        if (btn) { btn.disabled = true; btn.textContent = '…'; }

        // Les fichiers choisis sont déjà staged à la sélection (listener change).
        // Si des fichiers restent non diffusés (ex. re-chargement rapide), stage
        // au dernier moment avant l'envoi — mais si aucun staged récent, direct.
        var typeId = form.querySelector('[name="doc_type_id"]').value;
        var csrf = form.querySelector('[name="csrf"]').value;

        // Création/édition du profil — le serveur rattache les staged du compte
        function postProfil() {
            var data = {};
            new FormData(form).forEach(function (v, k) {
                // exclure les champs fichiers (déjà staged) et le csrf déjà envoyé
                if (k === 'doc_fichiers[]' || k === 'doc_type_id') return;
                data[k] = v;
            });
            var endpoint = data.profil_id ? 'api/update_profil.php' : 'api/create_profil.php';
            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.ok) {
                    window.location.href = 'index.php?page=espace';
                } else {
                    if (btn) { btn.disabled = false; btn.textContent = (btn.dataset.label || 'Créer le profil'); }
                    if (errEl) {
                        errEl.textContent = res.error || 'Erreur inconnue';
                        errEl.hidden = false;
                    }
                }
            })
            .catch(function () {
                if (btn) { btn.disabled = false; btn.textContent = (btn.dataset.label || 'Créer le profil'); }
                if (errEl) {
                    errEl.textContent = form.getAttribute('data-err-network') || 'Erreur réseau';
                    errEl.hidden = false;
                }
            });
        }

        postProfil();
    });
}

function openCreateModal(type, data) {
    var modal = document.getElementById('create_modal');
    if (!modal) return;
    modal.querySelectorAll('form[data-create-type]').forEach(function (f) {
        f.hidden = f.getAttribute('data-create-type') !== type;
    });
    var form = modal.querySelector('form[data-create-type="' + type + '"]');
    // Pré-remplissage (mode édition profil) : remplir chaque champ d'après data
    if (form) {
        var isEdit = !!(data && data.profil_id);
        // Titre et libellé du bouton (profil uniquement — les autres types
        // gardent leur titre i18n rendu côté serveur)
        if (type === 'profil') {
            var titleEl = form.querySelector('.create-title');
            var submitBtn = form.querySelector('button[type=submit]');
            if (titleEl) titleEl.textContent = isEdit ? docI18n('create.edit.title') : docI18n('create.new.title');
            if (submitBtn) {
                submitBtn.textContent = isEdit ? docI18n('create.edit.btn') : docI18n('create.new.btn');
                submitBtn.dataset.label = submitBtn.textContent;
            }
        }
        Object.keys(data || {}).forEach(function (k) {
            var el = form.querySelector('[name="' + k + '"]');
            if (el && el.type !== 'file') el.value = data[k];
        });
        // Champ caché profil_id : signaler update to l'API
        var pid = form.querySelector('[name="profil_id"]');
        if (pid) {
            pid.value = (data && data.profil_id) || '';
            pid.dataset.mode = pid.value ? 'update' : 'create';
        }
        // Charger la liste des docs existants ou des staged du compte
        var docList = form.querySelector('[data-doc-list]');
        if (docList) {
            if (pid && pid.value) {
                loadDocuments(pid.value, form);
            } else {
                loadStaged(form);
            }
        }
    }
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    var first = modal.querySelector('form[data-create-type="' + type + '"] input, form[data-create-type="' + type + '"] select');
    if (first) first.focus();
}

/** Charge la liste des documents du profil (mode édition) et la rend dans la modale. */
function loadDocuments(profilId, form) {
    fetch('api/list_documents.php?profil_id=' + encodeURIComponent(profilId))
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.ok) return;
            renderDocs(res.documents || [], form);
        })
        .catch(function () { /* silence — liste est bonus */ });
}

/** Charge les documents en staging du compte (mode création) et les rend dans la modale. */
function loadStaged(form) {
    fetch('api/list_staged.php')
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.ok) return;
            renderDocs(res.documents || [], form);
        })
        .catch(function () { });
}

/** Rend la liste des documents (staged ou rattachés) dans la modale. */
function renderDocs(documents, form) {
    var listEl = form.querySelector('[data-doc-list]');
    var items = form.querySelector('[data-doc-items]');
    if (!listEl || !items) return;
    items.innerHTML = '';
    (documents || []).forEach(function (d) {
        items.appendChild(renderDocItem(d, form));
    });
    listEl.hidden = (documents || []).length === 0;
}

/** Construit la ligne d'un document avec ses actions (view/download/rename/type/remove). */
function renderDocItem(d, form) {
    var li = document.createElement('li');
    var fmt;
    try {
        fmt = d.taille_octets >= 1048576 ? (d.taille_octets / 1048576).toFixed(1) + ' Mo'
            : d.taille_octets >= 1024 ? Math.round(d.taille_octets / 1024) + ' Ko'
            : d.taille_octets + ' o';
    } catch (e) { fmt = ''; }

    var name = document.createElement('span');
    name.textContent = d.nom_fichier;
    var meta = document.createElement('span');
    meta.className = 'note';
    meta.textContent = ' (' + fmt + ' · ' + (d.type_nom || '') + ')';

    var actions = document.createElement('div');
    actions.className = 'doc-actions';

    var view = document.createElement('button');
    view.type = 'button';
    view.textContent = docI18n('doc.view');
    view.addEventListener('click', function () {
        openDocViewer(d.id);
    });

    var dl = document.createElement('a');
    dl.href = 'api/download_document.php?id=' + d.id;
    dl.textContent = docI18n('doc.download');

    var rename = document.createElement('button');
    rename.type = 'button';
    rename.className = 'doc-rename';
    rename.textContent = docI18n('doc.rename');
    rename.addEventListener('click', function () {
        var newName = window.prompt(docI18n('doc.rename') + ' :', d.nom_fichier);
        if (newName === null) return;
        newName = newName.trim();
        if (!newName) return;
        updateDocument(d.id, { nom_fichier: newName }, form, d, li);
    });

    var typeSel = document.createElement('select');
    typeSel.className = 'doc-type';
    typeOptions().forEach(function (o) {
        var opt = document.createElement('option');
        opt.value = o.id;
        opt.textContent = o.label;
        opt.selected = String(o.id) === String(d.type_id);
        typeSel.appendChild(opt);
    });
    typeSel.addEventListener('change', function () {
        updateDocument(d.id, { type_id: typeSel.value }, form, d, li);
    });

    var remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'doc-remove';
    remove.textContent = docI18n('doc.remove');
    remove.addEventListener('click', function () {
        if (!confirm(docI18n('doc.confirm') + ' : ' + d.nom_fichier)) return;
        updateDocument(d.id, { _delete: true }, form, d, li);
    });

    actions.appendChild(view);
    actions.appendChild(dl);
    actions.appendChild(rename);
    actions.appendChild(typeSel);
    actions.appendChild(remove);

    li.appendChild(name);
    li.appendChild(meta);
    li.appendChild(actions);
    return li;
}

/** Envoie une mise à jour de document (renommage/type/suppression) et rafraîchit. */
function updateDocument(docId, payload, form) {
    var pidEl = form.querySelector('[name="profil_id"]');
    var pid = pidEl ? pidEl.value : '';
    var csrf = form.querySelector('[name="csrf"]').value;
    var body = { id: docId, csrf: csrf };
    var deleteIt = !!payload._delete;
    for (var k in payload) {
        if (k !== '_delete') body[k] = payload[k];
    }
    fetch(deleteIt ? 'api/delete_document.php' : 'api/update_document.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        if (!res.ok) return;
        if (pid) {
            loadDocuments(pid, form); // rafraîchir la liste après chaque mutation
        } else {
            loadStaged(form);
        }
    })
    .catch(function () {});
}

function closeCreateModal() {
    var modal = document.getElementById('create_modal');
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
}

/** Ouvre la modale de visualisation d'un document (inline, session requise). */
function openDocViewer(docId) {
    var viewer = document.getElementById('viewer_modal');
    if (!viewer) return;
    var frame = document.getElementById('viewer_frame');
    if (frame) frame.src = 'api/download_document.php?id=' + docId + '&inline=1';
    viewer.classList.add('open');
    viewer.setAttribute('aria-hidden', 'false');
}

/** Ferme la modale de visualisation (et vide le frame). */
function closeDocViewer() {
    var viewer = document.getElementById('viewer_modal');
    if (!viewer) return;
    var frame = document.getElementById('viewer_frame');
    if (frame) frame.src = 'about:blank';
    viewer.classList.remove('open');
    viewer.setAttribute('aria-hidden', 'true');
}

/** Écran viewer : fermeture par overlay, bouton et Échap. */
function initViewer() {
    document.querySelectorAll('[data-viewer-close]').forEach(function (el) {
        el.addEventListener('click', closeDocViewer);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDocViewer();
    });
}

/** Intercepte les clics sur les liens internes pour naviguer en AJAX. */
function initNav() {
    document.addEventListener('click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;

        var href = a.getAttribute('href');
        if (!href) return;
        if (href.charAt(0) === '#' || href.indexOf('mailto:') === 0) return;
        if (a.getAttribute('data-ajax') === 'off') return; // navigation classique (ex. déconnexion)
        if (a.target) return; // liens avec target (ex. nouvelle fenêtre)

        var url;
        try { url = new URL(href, location.href); } catch (err) { return; }
        if (url.origin !== location.origin) return; // lien externe

        // Le sélecteur de langue pose un cookie via redirection : navigation classique.
        if (url.searchParams.get('lang')) return;

        e.preventDefault();
        navigate(url.pathname + url.search);
    });
}

/** Charge la page cible et remplace uniquement le <main>. */
function navigate(url) {
    fetch(url)
        .then(function (r) { return r.text(); })
        .then(function (html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var newMain = doc.querySelector('.site-main');
            if (!newMain) return;
            var title = doc.querySelector('title');

            var oldMain = document.querySelector('.site-main');
            oldMain.outerHTML = newMain.outerHTML;
            if (title) document.title = title.textContent;

            history.pushState({}, '', url);
            window.scrollTo(0, 0);
            updateActiveNav(url);
            initPage();
        })
        .catch(function () {
            window.location.href = url; // filet de sécurité : navigation classique
        });
}

/** Met à jour le lien actif de la navigation selon la page affichée. */
function updateActiveNav(url) {
    document.querySelectorAll('.site-nav a').forEach(function (a) {
        var href = a.getAttribute('href');
        var linkUrl;
        try { linkUrl = new URL(href, location.href); } catch (err) { return; }
        var linkTarget = linkUrl.pathname + linkUrl.search;
        a.classList.toggle('active', linkTarget === url);
    });
}
/**
 * Options <select> des types de documents, depuis le select de la modale.
 */
function typeOptions() {
    var sel = document.querySelector('#create_form_profil select[name="doc_type_id"]');
    if (!sel) return [];
    return Array.prototype.slice.call(sel.options).map(function (o) {
        return { id: o.value, label: o.textContent };
    });
}

/** Libellé i18n des actions documents (selon <html lang>). */
function docI18n(key) {
    var lang = (document.documentElement.getAttribute('lang') || 'fr').slice(0, 2);
    var dict = {
        'doc.view': { fr: 'Visualiser', en: 'View' },
        'doc.download': { fr: 'Télécharger', en: 'Download' },
        'doc.rename': { fr: 'Renommer', en: 'Rename' },
        'doc.remove': { fr: 'Retirer', en: 'Remove' },
        'doc.confirm': { fr: 'Retirer définitivement ce document ?', en: 'Permanently remove this document?' },
        'doc.existing': { fr: 'Documents déjà joints :', en: 'Attached documents:' },
        'create.new.title': { fr: 'Nouveau profil', en: 'New profile' },
        'create.new.btn': { fr: 'Créer le profil', en: 'Create profile' },
        'create.edit.title': { fr: 'Modifier le profil', en: 'Edit profile' },
        'create.edit.btn': { fr: 'Enregistrer', en: 'Save' },
    };
    var map = dict[key];
    return map ? (map[lang] || map.fr) : key;
}

/** Échappe un texte pour insertion HTML (XSS). */
function esc(s) {
    var div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
}
