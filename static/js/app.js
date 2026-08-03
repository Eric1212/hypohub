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

/** Intercepte les clics sur les liens internes pour naviguer en AJAX. */
function initNav() {
    document.addEventListener('click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;

        var href = a.getAttribute('href');
        if (!href) return;
        if (href.charAt(0) === '#' || href.indexOf('mailto:') === 0) return;
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
