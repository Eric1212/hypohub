/**
 * Hypohub — JavaScript du site.
 * Pattern regioncities : le JS appelle api/<action>.php qui répond en JSON.
 */
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('check-ajax');
    var out = document.getElementById('ajax-result');
    if (!btn || !out) return;

    btn.addEventListener('click', function () {
        out.textContent = '…';
        fetch('api/system_status.php')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.ok) {
                    out.textContent = 'AJAX OK — schéma v' + data.schema_version;
                } else {
                    out.textContent = 'Erreur : ' + data.error;
                }
            })
            .catch(function () {
                out.textContent = 'Erreur réseau';
            });
    });
});
