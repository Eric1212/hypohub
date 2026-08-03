<?php
/**
 * Hypohub — traductions françaises.
 * Modifie les phrases ici pour personnaliser le texte visible du site.
 */
return array(
    'app.name'    => 'Hypohub',
    'app.tagline' => 'Le guichet unique du financement hypothécaire privé au Québec.',

    // Installation
    'install.title'   => 'Installation de Hypohub',
    'install.intro'   => 'Bienvenue ! Remplis les informations de ta base de données MySQL pour installer Hypohub.',
    'install.db_host' => 'Adresse du serveur (hôte)',
    'install.db_name' => 'Nom de la base de données',
    'install.db_user' => 'Usager MySQL',
    'install.db_pass' => 'Mot de passe MySQL',
    'install.submit'  => 'Installer Hypohub',
    'install.error'   => 'Impossible de se connecter à la base de données. Vérifie tes informations et réessaie.',
    'install.already' => 'Hypohub est déjà configuré.',

    // Navigation — logique persona : un profil = « Je suis … » partout
    'nav.home'    => 'Accueil',
    'nav.borrow'  => 'Je suis propriétaire',
    'nav.brokers' => 'Je suis courtier',
    'nav.lenders' => 'Je suis créancier',
    'nav.about'   => 'À propos',
    'nav.contact' => 'Contact',

    // Bandeau d'accueil (bienvenue, sans appel à l'action)
    'hero.title'    => 'Bienvenue chez Hypohub',
    'hero.subtitle' => 'Le guichet unique du financement hypothécaire privé au Québec.',

    // Problème
    'problem.title' => 'Votre banque a dit non ?',
    'problem.body'  => 'Travailleur autonome, dossier consolidé, délai serré ou second rang : les institutions refusent souvent, mais le financement privé sait répondre. Hypohub référence votre demande auprès de prêteurs privés et de courtiers hypothécaires du Québec.',
    'problem.note'  => 'Aucun frais pour l\'emprunteur.',

    // Comment ça marche (page Emprunter)
    'how.title'       => 'Comment ça marche',
    'how.step1.title' => 'Déposez votre demande',
    'how.step1.body'  => 'Montant, rang, propriété et situation. Quelques minutes suffisent.',
    'how.step2.title' => 'Nous référençons',
    'how.step2.body'  => 'Votre dossier est proposé aux prêteurs privés et courtiers du réseau.',
    'how.step3.title' => 'Un prêteur prend le dossier',
    'how.step3.body'  => 'Il l\'évalue, vous contacte et négocie avec vous.',
    'how.step4.title' => 'Financement',
    'how.step4.body'  => 'L\'hypothèque est conclue et enregistrée au Registre foncier.',

    // Pour qui — 3 profils distincts (accueil), logique « le guichet »
    'who.borrower.title' => 'Je suis propriétaire',
    'who.borrower.body'  => 'Le guichet transmet ma demande aux prêteurs privés et aux courtiers du Québec.',
    'who.borrower.cta'   => 'En savoir plus',
    'who.broker.title'   => 'Je suis courtier',
    'who.broker.body'    => 'Le guichet m\'amène des propriétaires et me relie aux prêteurs privés du Québec.',
    'who.broker.cta'     => 'En savoir plus',
    'who.lender.title'   => 'Je suis créancier',
    'who.lender.body'    => 'Le guichet me transmet des demandes de financement des propriétaires et de courtiers.',
    'who.lender.cta'     => 'En savoir plus',

    // Transparence (commission)
    'transparency.title' => 'Une commission simple et transparente',
    'transparency.body'  => 'Hypohub est rémunéré par le créancier ou le courtier, au moment de l\'enregistrement de l\'hypothèque : 25 points de base (0,25 %) sur la valeur de l\'hypothèque. Jamais à l\'emprunteur.',
    'transparency.note'  => 'Financière B&A Inc.',

    // Preuve (accueil)
    'proof.title'       => 'Une demande réelle, des résultats',
    'proof.stat1.value' => '6 demandes',
    'proof.stat1.label' => 'en une seule journée',
    'proof.stat2.value' => '22 500 $',
    'proof.stat2.label' => 'de commission potentielle',
    'proof.stat3.value' => '150 K$ → 6,5 M$',
    'proof.stat3.label' => 'valeurs d\'hypothèques visées',

    // Page Je suis propriétaire (emprunteur)
    'emp.banner'     => 'Je suis propriétaire',
    'emp.intro'      => 'Un financement hypothécaire privé, même quand la banque a dit non.',
    'emp.cases.title' => 'Cas que nous référençons',
    'emp.case1.t'    => 'Travailleur autonome',
    'emp.case1.b'    => 'Revenus non conventionnels, déclarations d\'impôts particulières : les institutions hésitent, les prêteurs privés évaluent la capacité de payer.',
    'emp.case2.t'    => 'Consolidation de dettes',
    'emp.case2.b'    => 'Regroupez vos dettes dans une seule hypothèque et libérez votre capacité financière.',
    'emp.case3.t'    => 'Deuxième rang',
    'emp.case3.b'    => 'Votre propriété a de la valeur nette ? Une hypothèque de second rang peut débloquer le capital.',
    'emp.case4.t'    => 'Délai serré',
    'emp.case4.b'    => 'Le circuit bancaire est long. Le financement privé répond plus vite.',
    'emp.cta.body'   => 'Un revenu stable et une capacité de payer suffisent pour discuter.',
    'emp.cta.btn'    => 'Moi, je suis propriétaire !',

    // Page Je suis courtier
    'bro.banner'    => 'Je suis courtier',
    'bro.intro'     => 'Un réseau de prêteurs privés pour placer les dossiers de vos clients.',
    'bro.adv.title' => 'Pourquoi rejoindre le réseau',
    'bro.adv1.t'    => 'Dossiers qualifiés',
    'bro.adv1.b'    => 'Recevez des demandes structurées et prêtes à évaluer, avec une mise en relation rapide.',
    'bro.adv2.t'    => 'Commission de suivi',
    'bro.adv2.b'    => 'Une rémunération de suivi en pourcentage, versée périodiquement, selon les ententes.',
    'bro.adv3.t'    => 'Souplesse',
    'bro.adv3.b'    => 'Une grande souplesse pour vous et vos clients : nous nous adaptons à chaque dossier.',
    'bro.note'      => 'En processus de développement du réseau.',
    'bro.cta.body'  => 'Parlons de votre pratique et de vos besoins.',
    'bro.cta.btn'   => 'Moi, je suis courtier !',

    // Page Je suis créancier
    'len.banner'    => 'Je suis créancier',
    'len.intro'     => 'Prêteur privé : placez votre capital, garanti par une hypothèque.',
    'len.adv.title' => 'Pourquoi prêter par Hypohub',
    'len.adv1.t'    => 'Garantie hypothécaire',
    'len.adv1.b'    => 'Premier ou deuxième rang, votre capital est protégé par une hypothèque sur la propriété.',
    'len.adv2.t'    => 'Enregistrement au Registre foncier',
    'len.adv2.b'    => 'Chaque financement est enregistré officiellement, dans un cadre légal reconnu au Québec.',
    'len.adv3.t'    => 'Dossiers sélectionnés',
    'len.adv3.b'    => 'Des demandes qualifiées et une capacité de payer démontrée par l\'emprunteur.',
    'len.note'      => 'Des taux variant selon le permis de l\'OPC et le profil du dossier.',
    'len.cta.body'  => 'Présentez votre capital et vos critères, nous vous proposons des dossiers.',
    'len.cta.btn'   => 'Moi, je suis créancier !',

    // Page À propos
    'ab.banner'    => 'Boucher & Associés, Financière',
    'ab.intro'     => 'Une vision d\'avenir pour le financement hypothécaire privé au Québec.',
    'ab.accord'    => 'Boucher & Associés, Financière : Une vision d\'avenir',
    'ab.vision.t' => 'Vision',
    'ab.vision.b' => 'Boucher et Associés a pour vision un avenir économique solide pour le Québec et être le cœur de ce mouvement.',
    'ab.mission.t' => 'Mission',
    'ab.mission.b' => 'Constituer un portefeuille d\'actifs et de passifs pour le groupe, contracter des prêts à des particuliers et/ou entreprises.',
    'ab.orient.t' => 'Orientation stratégique',
    'ab.orient.b' => 'B&A s\'oriente dans une optique de rapatriement des fonds canadiens et internationaux au Québec et favorise les flux monétaires entre les Québécois et elle-même.',
    'ab.entity.t' => 'L\'entité',
    'ab.entity.b'  => 'Hypohub est le guichet unique du financement hypothécaire privé au Québec, porté par Financière B&A Inc., entreprise immatriculée au Québec (NEQ 1177249589).',

    // FAQ
    'faq.title' => 'Questions fréquentes',
    'faq.q1'    => 'Qui paie la commission ?',
    'faq.a1'    => 'Le créancier ou le courtier, à l\'enregistrement de l\'hypothèque au Registre foncier. Jamais l\'emprunteur.',
    'faq.q4'    => 'Qui sont les prêteurs ?',
    'faq.a4'    => 'Notre réseau rassemble des prêteurs privés et des courtiers hypothécaires du Québec.',
    'faq.q5'    => 'Est-ce légal et réglementé ?',
    'faq.a5'    => 'Le prêt hypothécaire privé est une pratique reconnue au Québec. Hypohub met en relation ; il ne prête pas lui-même et ne prend pas de décision de crédit.',

    // Page Contact
    'ct.title'         => 'Vous avez des questions ou voulez simplement prendre contact, allez-y !',
    'ct.body'          => 'Emprunteur, courtier ou créancier : écrivez-nous, nous vous répondons.',
    'ct.form.t'        => 'Formulaire de contact',
    'ct.form.name'     => 'Prénom et nom',
    'ct.form.email'    => 'Courriel',
    'ct.form.subject'  => 'Sujet',
    'ct.subject.borrow' => 'Je suis propriétaire',
    'ct.subject.broker' => 'Je suis courtier',
    'ct.subject.lender' => 'Je suis créancier',
    'ct.subject.other'  => 'Autres...',
    'ct.form.message'  => 'Message',
    'ct.form.send'     => 'Envoyer',
    'ct.coords.founder' => 'Coordonnées du fondateur',
    'ct.coords.company' => 'Coordonnées de l\'entreprise',
    'ct.coords.address' => "1411 Rue Principale\nSaint-Étienne-des-Grès (Québec) Canada\nG0X 2P0",

    // Pied de page
    'footer.brand'   => 'Le guichet unique du financement hypothécaire privé au Québec.',
    'footer.founder' => 'Éric Boucher — Administrateur / Fondateur',
    'footer.mit'     => 'Logiciel libre — licence MIT',
);
