<?php
/**
 * Hypohub — connexion à la base de données MySQL + création du schéma.
 *
 * Le schéma est créé automatiquement au premier passage : chaque table
 * utilise « CREATE TABLE IF NOT EXISTS », donc relancer est sans danger.
 */
require_once __DIR__ . '/config.php';

/**
 * Retourne l'objet PDO connecté (une seule connexion par requête).
 */
function db() {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = hypohub_config();
    if (!$cfg) {
        throw new RuntimeException('Hypohub n\'est pas configuré. Ouvre config.php à la racine.');
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $cfg['db_host'],
        $cfg['db_name']
    );

    $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ));

    return $pdo;
}

/**
 * Crée les tables si elles n'existent pas.
 *
 * Modèle : un compte unique (users) avec deux accès cochés (proprietaire /
 * creancier — le courtier a les deux). L'accès propriétaire crée des profils
 * propriétaire/propriété et des dossiers d'emprunt ; l'accès créancier crée
 * son profil et visualise les dossiers (les profils restent masqués tant
 * qu'il n'y a pas d'acceptation). La facturation 25 pdb est gérée en interne,
 * hors de cette base.
 */
function db_schema() {
    $pdo = db();

    $pdo->exec("CREATE TABLE IF NOT EXISTS app_meta (
        meta_key   VARCHAR(64)  NOT NULL PRIMARY KEY,
        meta_value TEXT         NOT NULL
    )");

    // 1. Compte unique — 2 accès : acces_proprietaire, acces_creancier
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
        email             VARCHAR(190) NOT NULL,
        mot_de_passe      VARCHAR(255) NOT NULL,
        nom_complet       VARCHAR(150) NOT NULL,
        telephone         VARCHAR(40)  DEFAULT NULL,
        adresse           TEXT,
        acces_proprietaire TINYINT(1)  NOT NULL DEFAULT 0,
        acces_creancier   TINYINT(1)   NOT NULL DEFAULT 0,
        actif             TINYINT(1)   NOT NULL DEFAULT 1,
        date_creation     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_users_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 2. Profils propriétaire — plusieurs par compte (soi, père, sœur...)
    $pdo->exec("CREATE TABLE IF NOT EXISTS profils_proprietaire (
        id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
        cree_par         INT UNSIGNED NOT NULL,
        nom_complet      VARCHAR(150) NOT NULL,
        telephone        VARCHAR(40)  DEFAULT NULL,
        courriel         VARCHAR(190) DEFAULT NULL,
        situation_emploi ENUM('salaire','travailleur_autonome','autre') NOT NULL DEFAULT 'salaire',
        revenu_annuel    DECIMAL(12,2) DEFAULT NULL,
        notes            TEXT,
        date_creation    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_pp_cree_par (cree_par),
        CONSTRAINT fk_pp_cree_par FOREIGN KEY (cree_par) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 3. Profils propriété — l'immeuble donné en garantie
    $pdo->exec("CREATE TABLE IF NOT EXISTS profils_propriete (
        id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
        cree_par       INT UNSIGNED NOT NULL,
        adresse        VARCHAR(255) NOT NULL,
        ville          VARCHAR(100) NOT NULL,
        code_postal    VARCHAR(7)   DEFAULT NULL,
        valeur_estimee DECIMAL(12,2) DEFAULT NULL,
        valeur_nette   DECIMAL(12,2) DEFAULT NULL,
        date_creation  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_ppe_cree_par (cree_par),
        CONSTRAINT fk_ppe_cree_par FOREIGN KEY (cree_par) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 4. Dossiers d'emprunt — le lead central (1 dossier = 1 emprunteur + 1 propriété)
    $pdo->exec("CREATE TABLE IF NOT EXISTS dossiers_emprunt (
        id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
        cree_par                INT UNSIGNED NOT NULL,
        profil_proprietaire_id  INT UNSIGNED NOT NULL,
        profil_propriete_id     INT UNSIGNED NOT NULL,
        montant_demande         DECIMAL(12,2) NOT NULL,
        rang                    ENUM('premier','deuxieme') NOT NULL DEFAULT 'premier',
        type_financement        ENUM('travailleur_autonome','consolidation','deuxieme_rang','delai_serre') NOT NULL DEFAULT 'travailleur_autonome',
        statut                  ENUM('nouveau','accepte','finance','refuse','retire') NOT NULL DEFAULT 'nouveau',
        date_financement        DATETIME DEFAULT NULL,
        date_creation           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_de_cree_par (cree_par),
        KEY idx_de_statut (statut),
        CONSTRAINT fk_de_cree_par FOREIGN KEY (cree_par) REFERENCES users(id),
        CONSTRAINT fk_de_proprietaire FOREIGN KEY (profil_proprietaire_id) REFERENCES profils_proprietaire(id),
        CONSTRAINT fk_de_propriete FOREIGN KEY (profil_propriete_id) REFERENCES profils_propriete(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 5. Profils créancier — plusieurs par compte (société, véhicules de capital...)
    $pdo->exec("CREATE TABLE IF NOT EXISTS profils_creancier (
        id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id            INT UNSIGNED NOT NULL,
        nom                VARCHAR(150) NOT NULL DEFAULT '',
        type               ENUM('individu','societe') NOT NULL DEFAULT 'individu',
        capital_disponible DECIMAL(12,2) DEFAULT NULL,
        criteres           TEXT,
        permis_opc         VARCHAR(30)  DEFAULT NULL,
        date_creation      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_pc_user (user_id),
        CONSTRAINT fk_pc_user FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 6. Offres de financement — transmises au sein du dossier (document du notaire)
    $pdo->exec("CREATE TABLE IF NOT EXISTS offres_financement (
        id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
        dossier_id       INT UNSIGNED NOT NULL,
        professionnel_id INT UNSIGNED NOT NULL,
        taux_interet     DECIMAL(6,3) DEFAULT NULL,
        echeances        VARCHAR(100) DEFAULT NULL,
        conditions       TEXT,
        statut           ENUM('proposee','acceptee','declinee') NOT NULL DEFAULT 'proposee',
        date_creation    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_of_dossier (dossier_id),
        CONSTRAINT fk_of_dossier FOREIGN KEY (dossier_id) REFERENCES dossiers_emprunt(id),
        CONSTRAINT fk_of_professionnel FOREIGN KEY (professionnel_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 7. Acceptations — engagement 25 pdb + déblocage d'accès aux profils (fusionnés)
    $pdo->exec("CREATE TABLE IF NOT EXISTS acceptations (
        id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
        dossier_id        INT UNSIGNED NOT NULL,
        professionnel_id  INT UNSIGNED NOT NULL,
        date_acceptation  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_acc_dossier_prof (dossier_id, professionnel_id),
        CONSTRAINT fk_acc_dossier FOREIGN KEY (dossier_id) REFERENCES dossiers_emprunt(id),
        CONSTRAINT fk_acc_professionnel FOREIGN KEY (professionnel_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Compteur anti brute-force : 5 échecs → 15 min (par email + IP)
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_tentatives (
        email        VARCHAR(190) NOT NULL,
        ip           VARCHAR(45)  NOT NULL,
        compteur     INT UNSIGNED NOT NULL DEFAULT 0,
        bloque_jusqua DATETIME    DEFAULT NULL,
        date_maj     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (email, ip)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Migration : plusieurs profils créancier par compte
    // (l'index unique « un profil par compte » devient un index simple)
    $has_unique = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.STATISTICS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_creancier' AND INDEX_NAME = 'uq_pc_user'"
    )->fetchColumn();
    if ($has_unique) {
        $pdo->exec('ALTER TABLE profils_creancier DROP INDEX uq_pc_user, ADD INDEX idx_pc_user (user_id)');
    }

    // Migration : le profil créancier a besoin d'un nom (société ou prêteur)
    $has_nom = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_creancier' AND COLUMN_NAME = 'nom'"
    )->fetchColumn();
    if (!$has_nom) {
        $pdo->exec("ALTER TABLE profils_creancier ADD COLUMN nom VARCHAR(150) NOT NULL DEFAULT '' AFTER user_id");
    }

    // Version du schéma (bump à chaque évolution de la structure)
    $st = $pdo->prepare(
        "INSERT INTO app_meta (meta_key, meta_value) VALUES ('schema_version', '5')
         ON DUPLICATE KEY UPDATE meta_value = '5'"
    );
    $st->execute();
}
