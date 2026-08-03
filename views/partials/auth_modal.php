<?php /** Hypohub — modale de connexion / création de compte (partial global). */ ?>
<div class="auth-modal" id="auth_modal" aria-hidden="true">
    <div class="auth-modal-overlay" data-auth-close></div>
    <div class="auth-modal-box" role="dialog" aria-modal="true" aria-labelledby="auth_title">
        <button type="button" class="auth-modal-close" data-auth-close aria-label="<?php echo htmlspecialchars(t('auth.close'), ENT_QUOTES, 'UTF-8'); ?>">&times;</button>

        <div class="auth-tabs" role="tablist">
            <button type="button" class="auth-tab active" data-auth-tab="login" role="tab"><?php echo htmlspecialchars(t('auth.login.title'), ENT_QUOTES, 'UTF-8'); ?></button>
            <button type="button" class="auth-tab" data-auth-tab="register" role="tab"><?php echo htmlspecialchars(t('auth.register.title'), ENT_QUOTES, 'UTF-8'); ?></button>
        </div>

        <!-- Onglet connexion -->
        <form id="auth_login_form" class="auth-form" data-err-network="<?php echo htmlspecialchars(t('auth.error.network'), ENT_QUOTES, 'UTF-8'); ?>" novalidate>
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <label>
                <span><?php echo htmlspecialchars(t('auth.email'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="email" name="email" autocomplete="email" required>
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('auth.password'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <p class="auth-error" data-auth-error hidden></p>
            <button type="submit" class="btn btn-primary btn-block"><?php echo htmlspecialchars(t('auth.login.submit'), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>

        <!-- Onglet création de compte -->
        <form id="auth_register_form" class="auth-form" data-err-network="<?php echo htmlspecialchars(t('auth.error.network'), ENT_QUOTES, 'UTF-8'); ?>" hidden novalidate>
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="acces_proprietaire" value="0">
            <input type="hidden" name="acces_creancier" value="0">

            <div class="auth-cards">
                <button type="button" class="auth-card" data-acces="proprietaire">
                    <span class="auth-card-title"><?php echo htmlspecialchars(t('auth.register.card.prop'), ENT_QUOTES, 'UTF-8'); ?></span>
                </button>
                <button type="button" class="auth-card" data-acces="creancier">
                    <span class="auth-card-title"><?php echo htmlspecialchars(t('auth.register.card.cre'), ENT_QUOTES, 'UTF-8'); ?></span>
                </button>
                <button type="button" class="auth-card" data-acces="courtier">
                    <span class="auth-card-title"><?php echo htmlspecialchars(t('auth.register.card.both'), ENT_QUOTES, 'UTF-8'); ?></span>
                </button>
            </div>
            <p class="auth-hint"><?php echo htmlspecialchars(t('auth.register.hint'), ENT_QUOTES, 'UTF-8'); ?></p>

            <label>
                <span><?php echo htmlspecialchars(t('auth.name'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="text" name="nom_complet" autocomplete="name" required>
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('auth.email'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="email" name="email" autocomplete="email" required>
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('auth.password.register'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="password" name="password" autocomplete="new-password" minlength="8" required>
            </label>
            <p class="auth-error" data-auth-error hidden></p>
            <button type="submit" class="btn btn-primary btn-block"><?php echo htmlspecialchars(t('auth.register.submit'), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>
    </div>
</div>
