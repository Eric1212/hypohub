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

            content.style.display = wasClosed ? 'block' : 'none';
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

    // Défilement fluide vers les ancres internes.
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var target = document.querySelector(a.getAttribute('href'));
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

    attachAuthForm(loginForm, 'api/auth_login.php');
    attachAuthForm(registerForm, 'api/auth_register.php');
}

/** Soumet un formulaire de la modale en JSON ; recharge la page en cas de succès. */
function attachAuthForm(form, url) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var errEl = form.querySelector('[data-auth-error]');
        errEl.hidden = true;

        var data = {};
        new FormData(form).forEach(function (v, k) { data[k] = v; });

        var btn = form.querySelector('button[type=submit]');
        var original = btn.textContent;
        btn.disabled = true;
        btn.textContent = '…';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            btn.disabled = false;
            btn.textContent = original;
            if (res.ok) {
                // Redirection vers l'espace membre (le serveur rend le footer connecté).
                window.location.href = 'index.php?page=espace';
            } else {
                errEl.textContent = res.error || form.getAttribute('data-err-network');
                errEl.hidden = false;
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.textContent = original;
            errEl.textContent = form.getAttribute('data-err-network');
            errEl.hidden = false;
        });
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
