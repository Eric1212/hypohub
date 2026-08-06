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
    //    IDV/INC : prénom+nom = le représentant (INC) ou la personne (IDV) ;
    //    le classement est dérivé (NEQ / nom_compagnie présents → société).
    $pdo->exec("CREATE TABLE IF NOT EXISTS profils_proprietaire (
        id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
        cree_par         INT UNSIGNED NOT NULL,
        prenom           VARCHAR(100) NOT NULL DEFAULT '',
        nom              VARCHAR(100) NOT NULL DEFAULT '',
        date_naissance   DATE        DEFAULT NULL,
        courriel         VARCHAR(190) DEFAULT NULL,
        telephone        VARCHAR(40)  DEFAULT NULL,
        app              VARCHAR(20)  DEFAULT NULL,
        adresse          VARCHAR(255) NOT NULL DEFAULT '',
        ville            VARCHAR(100) NOT NULL DEFAULT '',
        code_postal      VARCHAR(7)   DEFAULT NULL,
        province         VARCHAR(2)   NOT NULL DEFAULT 'QC',
        nom_compagnie    VARCHAR(150) DEFAULT NULL,
        neq              VARCHAR(10)  DEFAULT NULL,
        statut           ENUM('citoyen','residant_permanent') DEFAULT NULL,
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
        statut                  ENUM('nouveau','actif','finance','expire','retire') NOT NULL DEFAULT 'nouveau',
        date_financement        DATETIME DEFAULT NULL,
        derniere_activite       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
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

    // 8. Types de documents — dynamiques (backend admin à venir), seed initial
    $pdo->exec("CREATE TABLE IF NOT EXISTS documents_types (
        id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        nom_fr     VARCHAR(100) NOT NULL,
        nom_en     VARCHAR(100) NOT NULL,
        actif      TINYINT(1)   NOT NULL DEFAULT 1,
        date_creation DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_dt_nom_fr (nom_fr)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Seed des types de documents (uniquement si la table vient d'être créée)
    $has_dt = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.TABLES
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents_types'"
    )->fetchColumn();
    if ($has_dt) {
        $st = $pdo->query('SELECT COUNT(*) FROM documents_types');
        if ((int) $st->fetchColumn() === 0) {
            $seed = $pdo->prepare('INSERT INTO documents_types (nom_fr, nom_en) VALUES (?, ?)');
            foreach (array(
                array('Permis de conduire', 'Driver\'s licence'),
                array('Passeport', 'Passport'),
                array('Avis de cotisation', 'Notice of assessment'),
                array('Talons de paie', 'Pay stubs'),
                array('Relevés bancaires', 'Bank statements'),
                array('États financiers', 'Financial statements'),
            ) as $t) {
                $seed->execute($t);
            }
        }
    }

    // 9. Documents — scans/PDF attachés à un profil propriétaire (uploads/).
    //    Staging : profil_id NULL = en attente sur le compte (rattaché à la
    //    création du profil) ; purgés chaque jour à 3h33 (voir purge ci-bas).
    $pdo->exec("CREATE TABLE IF NOT EXISTS documents (
        id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id      INT UNSIGNED NOT NULL,
        profil_id    INT UNSIGNED DEFAULT NULL,
        type_id      INT UNSIGNED NOT NULL,
        nom_fichier  VARCHAR(255) NOT NULL,
        fichier_stocke VARCHAR(255) NOT NULL DEFAULT '',
        taille_octets BIGINT UNSIGNED NOT NULL,
        mime         VARCHAR(50)  NOT NULL DEFAULT 'application/octet-stream',
        date_upload  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_doc_user (user_id),
        KEY idx_doc_profil (profil_id),
        CONSTRAINT fk_doc_user FOREIGN KEY (user_id) REFERENCES users(id),
        CONSTRAINT fk_doc_profil FOREIGN KEY (profil_id) REFERENCES profils_proprietaire(id),
        CONSTRAINT fk_doc_type FOREIGN KEY (type_id) REFERENCES documents_types(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Migration v8 : documents passe du profil-déterminé au staging compte
    $h_user = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND COLUMN_NAME = 'user_id'"
    )->fetchColumn();
    if (!$h_user) {
        $pdo->exec('ALTER TABLE documents
            ADD COLUMN user_id INT UNSIGNED NOT NULL DEFAULT 0 AFTER id,
            ADD INDEX idx_documents_user (user_id)');
        // injecter le créateur du profil dans user_id (v7 : tout doc avait un profil)
        $pdo->exec('UPDATE documents d JOIN profils_proprietaire p ON p.id = d.profil_id
            SET d.user_id = p.cree_par');
    }
    $h_mime = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND COLUMN_NAME = 'mime'"
    )->fetchColumn();
    if (!$h_mime) {
        $pdo->exec('ALTER TABLE documents ADD COLUMN mime VARCHAR(50) NOT NULL DEFAULT "application/octet-stream" AFTER taille_octets');
    }
    // profil_id nullable = staging (document uploadé, pas encore rattaché)
    $pdo->exec('ALTER TABLE documents MODIFY profil_id INT UNSIGNED DEFAULT NULL');

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

    // Migration profils_proprietaire v6 (2026-08-05) : profil unique IDV/INC.
    // remplace nom_complet par prenom+nom, ajoute adresse/naissance/INC,
    // retire situation_emploi et revenu_annuel (l'argent vit au dossier).
    // Vérifie si une colonne existe dans la table indiquée (par défaut
    // profils_proprietaire, pour la migration v6). Les migrations v9/v10
    // (users) passent la table en 2e argument — sans quoi le garde-fou
    // interrogerait toujours la mauvaise table et relancerait les ALTER.
    $col = function ($name, $table = 'profils_proprietaire') use ($pdo) {
        $table = preg_replace('/[^a-z_]/i', '', $table);
        return (int) $pdo->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_NAME = '$name'"
        )->fetchColumn();
    };

    // 2a. nom_complet → prenom + nom (split au premier espace)
    if ($col('nom_complet')) {
        $pdo->exec("ALTER TABLE profils_proprietaire
            ADD COLUMN prenom VARCHAR(100) NOT NULL DEFAULT '' AFTER cree_par,
            ADD COLUMN nom    VARCHAR(100) NOT NULL DEFAULT '' AFTER prenom");
        $pdo->exec("UPDATE profils_proprietaire SET
            prenom = SUBSTRING_INDEX(nom_complet, ' ', 1),
            nom    = SUBSTRING_INDEX(nom_complet, ' ', -1)");
        $pdo->exec('ALTER TABLE profils_proprietaire DROP COLUMN nom_complet');
    }

    // 2b. colonnes IDV/INC (si table neuve, déjà présentes dans le CREATE)
    if (!$col('date_naissance')) {
        $pdo->exec("ALTER TABLE profils_proprietaire
            ADD COLUMN date_naissance DATE DEFAULT NULL AFTER nom,
            ADD COLUMN app VARCHAR(20) DEFAULT NULL AFTER telephone,
            ADD COLUMN adresse VARCHAR(255) NOT NULL DEFAULT '' AFTER app,
            ADD COLUMN ville VARCHAR(100) NOT NULL DEFAULT '' AFTER adresse,
            ADD COLUMN code_postal VARCHAR(7) DEFAULT NULL AFTER ville,
            ADD COLUMN province VARCHAR(2) NOT NULL DEFAULT 'QC' AFTER code_postal,
            ADD COLUMN nom_compagnie VARCHAR(150) DEFAULT NULL AFTER province,
            ADD COLUMN neq VARCHAR(10) DEFAULT NULL AFTER nom_compagnie,
            ADD COLUMN statut ENUM('citoyen','residant_permanent') DEFAULT NULL AFTER neq");
    }

    // 2c. suppression des colonnes obsolètes (finance → dossier)
    if ($col('situation_emploi')) {
        $pdo->exec('ALTER TABLE profils_proprietaire DROP COLUMN situation_emploi, DROP COLUMN revenu_annuel');
    }

    // v9 — Zone compte : username, certificat AMF + statut de vérification,
    // demande d'accès créancier (justification + statut), rôle vérificateur.
    if (!$col('username', 'users')) {
        $pdo->exec("ALTER TABLE users
            ADD COLUMN username VARCHAR(80) DEFAULT NULL AFTER nom_complet,
            ADD UNIQUE KEY uq_users_username (username)");
    }
    if (!$col('certificat_amf', 'users')) {
        $pdo->exec("ALTER TABLE users
            ADD COLUMN certificat_amf VARCHAR(32) DEFAULT NULL AFTER telephone");
    }
    if (!$col('certificat_amf_statut', 'users')) {
        $pdo->exec("ALTER TABLE users
            ADD COLUMN certificat_amf_statut ENUM('vide','en_attente','verifie') NOT NULL DEFAULT 'vide' AFTER certificat_amf");
    }
    if (!$col('demande_creancier_justification', 'users')) {
        $pdo->exec("ALTER TABLE users
            ADD COLUMN demande_creancier_justification TEXT NULL AFTER acces_creancier");
    }
    if (!$col('demande_creancier_statut', 'users')) {
        $pdo->exec("ALTER TABLE users
            ADD COLUMN demande_creancier_statut ENUM('aucune','en_attente','approuve','refuse') NOT NULL DEFAULT 'aucune' AFTER demande_creancier_justification");
    }
    if (!$col('demande_creancier_date', 'users')) {
        $pdo->exec('ALTER TABLE users
            ADD COLUMN demande_creancier_date DATETIME NULL AFTER demande_creancier_statut');
    }
    if (!$col('est_admin', 'users')) {
        $pdo->exec('ALTER TABLE users
            ADD COLUMN est_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER actif');
    }

    // v10 — Mot de passe temporaire : quand un employé en génère un
    // (admin « reset_mdp »), le compte est marqué et DOIT changer son mot de
    // passe au premier login (modale dédiée avant toute session).
    if (!$col('mdp_temporaire', 'users')) {
        $pdo->exec('ALTER TABLE users
            ADD COLUMN mdp_temporaire TINYINT(1) NOT NULL DEFAULT 0 AFTER mot_de_passe');
    }

    // v11 — Cycle de vie du dossier d'emprunt (décision Éric, 2026-08-06) :
    //   nouveau = créé, aucun professionnel ne l'a encore ouvert ;
    //   act     = au moins un courtier/créancier travaille dessus (ex-accepte) ;
    //   finance = résultat post-acceptation d'une offre (date_financement) ;
    //   expire  = fermé par le système après 90 jours sans activité ;
    //   retire  = retiré volontairement par l'utilisateur.
    // 'refuse' n'existe plus (un dossier n'est pas « refusé », c'est une offre
    // qui l'est). Colonne derniere_activite : pilote l'expiration automatique.
    $statut_type = (string) $pdo->query(
        "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dossiers_emprunt' AND COLUMN_NAME = 'statut'"
    )->fetchColumn();
if (strpos($statut_type, "'accepte'") !== false) {
        // Élargir d'abord (ajouter les nouvelles valeurs), convertir, resserrer.
        // MySQL ne permet pas d'affecter une valeur qui n'est pas (encore)
        // dans l'ENUM — il faut d'abord que 'act' et 'expire' existent.
        $pdo->exec("ALTER TABLE dossiers_emprunt
            MODIFY statut ENUM('nouveau','act','finance','expire','retire','accepte','refuse') NOT NULL DEFAULT 'nouveau'");
        $pdo->exec("UPDATE dossiers_emprunt SET statut = 'act' WHERE statut = 'accepte'");
        $pdo->exec("UPDATE dossiers_emprunt SET statut = 'expire' WHERE statut = 'refuse'");
        $pdo->exec("ALTER TABLE dossiers_emprunt
            MODIFY statut ENUM('nouveau','act','finance','expire','retire') NOT NULL DEFAULT 'nouveau'");
    }
    if (!$col('derniere_activite', 'dossiers_emprunt')) {
        $pdo->exec('ALTER TABLE dossiers_emprunt
            ADD COLUMN derniere_activite DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER date_financement');
    }

    // Version du schéma (bump à chaque évolution de la structure)
    $st = $pdo->prepare(
        "INSERT INTO app_meta (meta_key, meta_value) VALUES ('schema_version', '11')
         ON DUPLICATE KEY UPDATE meta_value = '11'"
    );
    $st->execute();

    // Purge quotidienne des documents en staging (profil_id NULL) — 3h33 locale.
    // Paresseuse (aucun cron sur hébergement partagé) : exécutée au premier
    // accès après 03:33 du jour, une seule fois par jour.
    $purge_key = 'derniere_purge_stage';
    $st = $pdo->query("SELECT meta_value FROM app_meta WHERE meta_key = '$purge_key'");
    $last_purge = $st->fetchColumn();
    $today = date('Y-m-d');
    if ($last_purge !== $today && date('Hi') >= '0333') {
        // fichiers disque des staged expirés
        $st = $pdo->query('SELECT fichier_stocke FROM documents WHERE profil_id IS NULL');
        foreach ($st->fetchAll() as $r) {
            $f = __DIR__ . '/../uploads/' . basename($r['fichier_stocke']);
            if (is_file($f)) {
                @unlink($f);
            }
        }
        $pdo->exec('DELETE FROM documents WHERE profil_id IS NULL');
        $st = $pdo->prepare(
            "INSERT INTO app_meta (meta_key, meta_value) VALUES ('$purge_key', ?)
             ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)"
        );
        $st->execute(array($today));
    }
}
