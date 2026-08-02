<?php
/**
 * Hypohub — internationalisation (i18n).
 *
 * Langues disponibles : fr (défaut), en.
 * Détection : cookie « hypohub_lang » (posé par le sélecteur ?lang=),
 * sinon langue du navigateur, sinon français.
 */

/**
 * Langue courante : 'fr' ou 'en'.
 */
function hypohub_current_lang() {
    static $lang = null;
    if ($lang !== null) {
        return $lang;
    }

    $available = array('fr', 'en');
    $lang = 'fr';

    if (isset($_COOKIE['hypohub_lang']) && in_array($_COOKIE['hypohub_lang'], $available, true)) {
        $lang = $_COOKIE['hypohub_lang'];
        return $lang;
    }

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $accept = strtolower((string) $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($available as $l) {
            if (strpos($accept, $l) !== false) {
                $lang = $l;
                break;
            }
        }
    }

    return $lang;
}

/**
 * Traduit une clé. Placeholders : t('app.name') ou t('hello', array('name' => 'X')).
 */
function t($key, $params = array()) {
    static $strings = null;
    if ($strings === null) {
        $strings = require __DIR__ . '/../lang/' . hypohub_current_lang() . '.php';
    }

    $msg = isset($strings[$key]) ? $strings[$key] : $key;

    foreach ($params as $k => $v) {
        $msg = str_replace('{' . $k . '}', (string) $v, $msg);
    }

    return $msg;
}
