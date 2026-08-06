<?php
/**
 * Hypohub — authentification.
 *
 * Session PHP native + table `users`. Un compte = email + mot de passe
 * (hash bcrypt) et deux accès : acces_proprietaire / acces_creancier.
 * Le courtier a les deux accès sur le même compte.
 *
 * Sécurité : cookie de session HttpOnly + SameSite=Lax, CSRF sur les
 * formulaires, verrouillage brute-force (5 échecs → 15 min par email+IP).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(array(
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ));
    session_name('hypohub_session');
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/i18n.php';

const AUTH_MAX_FAILURES = 5;   // tentatives avant verrouillage
const AUTH_BLOCK_MINUTES = 15; // durée du verrouillage

/**
 * Retourne la ligne `users` du compte connecté, ou null.
 */
function auth_user() {
    static $user = null, $checked = false;
    if ($checked) {
        return $user;
    }
    $checked = true;
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    try {
        $st = db()->prepare('SELECT * FROM users WHERE id = ? AND actif = 1');
        $st->execute(array($_SESSION['user_id']));
        $user = $st->fetch() ?: null;
    } catch (Exception $e) {
        $user = null; // base indisponible (ex. installeur pas encore configuré)
    }
    return $user;
}

/**
 * Token CSRF de la session (créé au besoin).
 */
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Vérifie un token CSRF (comparaison en temps constant).
 */
function csrf_check($token) {
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/**
 * Tente une connexion.
 * @return array ['ok' => true] ou ['ok' => false, 'error' => message]
 */
function auth_login($email, $password) {
    $email    = strtolower(trim((string) $email));
    $password = (string) $password;
    $ip       = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';

    if ($email === '' || $password === '') {
        return array('ok' => false, 'error' => t('auth.error.required'));
    }

    // Verrouillage brute-force.
    $st = db()->prepare(
        'SELECT compteur, bloque_jusqua FROM login_tentatives WHERE email = ? AND ip = ?'
    );
    $st->execute(array($email, $ip));
    $row = $st->fetch();

    if ($row && $row['bloque_jusqua'] !== null) {
        $minutes = (int) ceil((strtotime($row['bloque_jusqua']) - time()) / 60);
        if ($minutes > 0) {
            return array('ok' => false, 'error' => t('auth.error.blocked', array('minutes' => $minutes)));
        }
    }

    $st = db()->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute(array($email));
    $user = $st->fetch();

    if (!$user || !password_verify($password, $user['mot_de_passe'])) {
        auth_note_failure($email, $ip);
        return array('ok' => false, 'error' => t('auth.error.invalid'));
    }

    if (!$user['actif']) {
        return array('ok' => false, 'error' => t('auth.error.inactive'));
    }

    // Mot de passe temporaire (généré par un employé) : aucune session n'est
    // ouverte — le compte doit d'abord définir son vrai mot de passe (modale
    // dédiée → api/changer_mdp.php).
    if (!empty($user['mdp_temporaire'])) {
        auth_reset_failures($email, $ip);
        return array('ok' => true, 'changer_mdp' => true);
    }

    auth_reset_failures($email, $ip);
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];

    return array('ok' => true);
}

/**
 * Définit un vrai mot de passe après une connexion avec mot de passe
 * temporaire. Vérifie l'ancien, exige 8 caractères, lève le flag, ouvre la
 * session proprement.
 * @return array ['ok' => true] ou ['ok' => false, 'error' => message]
 */
function auth_changer_mdp($email, $ancien, $nouveau) {
    $email  = strtolower(trim((string) $email));
    $ancien = (string) $ancien;
    $nouveau = (string) $nouveau;

    if ($email === '' || $ancien === '' || $nouveau === '') {
        return array('ok' => false, 'error' => t('auth.error.required'));
    }
    if (strlen($nouveau) < 8) {
        return array('ok' => false, 'error' => t('auth.error.password'));
    }

    $st = db()->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute(array($email));
    $user = $st->fetch();

    if (!$user || !password_verify($ancien, $user['mot_de_passe'])) {
        return array('ok' => false, 'error' => t('auth.error.invalid'));
    }
    if (!$user['actif']) {
        return array('ok' => false, 'error' => t('auth.error.inactive'));
    }
    if (empty($user['mdp_temporaire'])) {
        return array('ok' => false, 'error' => t('auth.error.changer_mdp_deja'));
    }

    $st = db()->prepare(
        'UPDATE users SET mot_de_passe = ?, mdp_temporaire = 0 WHERE id = ?'
    );
    $st->execute(array(password_hash($nouveau, PASSWORD_DEFAULT), (int) $user['id']));

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];

    return array('ok' => true);
}

/**
 * Crée un compte et connecte.
 * @param array $acces ['proprietaire' => bool, 'creancier' => bool]
 * @return array ['ok' => true] ou ['ok' => false, 'error' => message]
 */
function auth_register($nom_complet, $email, $password, $acces) {
    $nom_complet = trim((string) $nom_complet);
    $email       = strtolower(trim((string) $email));
    $password    = (string) $password;
    $acc_prop    = !empty($acces['proprietaire']) ? 1 : 0;
    $acc_cre     = !empty($acces['creancier']) ? 1 : 0;

    if ($nom_complet === '') {
        return array('ok' => false, 'error' => t('auth.error.name'));
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return array('ok' => false, 'error' => t('auth.error.email'));
    }
    if (strlen($password) < 8) {
        return array('ok' => false, 'error' => t('auth.error.password'));
    }
    if (!$acc_prop && !$acc_cre) {
        return array('ok' => false, 'error' => t('auth.error.access'));
    }

    try {
        $st = db()->prepare(
            'INSERT INTO users (email, mot_de_passe, nom_complet, acces_proprietaire, acces_creancier)
             VALUES (?, ?, ?, ?, ?)'
        );
        $st->execute(array(
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $nom_complet,
            $acc_prop,
            $acc_cre,
        ));
    } catch (PDOException $e) {
        if ((int) $e->getCode() === 23000) { // doublon (email unique)
            return array('ok' => false, 'error' => t('auth.error.taken'));
        }
        throw $e;
    }

    $id = (int) db()->lastInsertId();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;

    return array('ok' => true);
}

/**
 * Déconnecte le compte courant (détruit la session et le cookie).
 */
function auth_logout() {
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Note un échec de connexion ; au-delà du seuil, verrouille l'adresse.
 */
function auth_note_failure($email, $ip) {
    $pdo  = db();
    $st   = $pdo->prepare('SELECT compteur FROM login_tentatives WHERE email = ? AND ip = ?');
    $st->execute(array($email, $ip));
    $row = $st->fetch();

    if ($row) {
        $compteur = $row['compteur'] + 1;
        $bloque   = $compteur >= AUTH_MAX_FAILURES ? date('Y-m-d H:i:s', time() + AUTH_BLOCK_MINUTES * 60) : null;
        $st = $pdo->prepare('UPDATE login_tentatives SET compteur = ?, bloque_jusqua = ? WHERE email = ? AND ip = ?');
        $st->execute(array($compteur, $bloque, $email, $ip));
    } else {
        $st = $pdo->prepare('INSERT INTO login_tentatives (email, ip, compteur, bloque_jusqua) VALUES (?, ?, 1, NULL)');
        $st->execute(array($email, $ip));
    }
}

/**
 * Efface les tentatives enregistrées (connexion réussie).
 */
function auth_reset_failures($email, $ip) {
    $st = db()->prepare('DELETE FROM login_tentatives WHERE email = ? AND ip = ?');
    $st->execute(array($email, $ip));
}
