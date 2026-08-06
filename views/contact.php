<?php
/** Hypohub — page Contact (formulaire mailto + coordonnées). */
?>
<section class="page-banner">
    <div class="page-banner-inner">
        <h1><?php echo htmlspecialchars(t('ct.title'), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="page-banner-sub"><?php echo htmlspecialchars(t('ct.body'), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
</section>

<section class="section">
    <div class="section-inner wide">
        <div class="contact-grid">
            <div class="card">
                <h2><?php echo htmlspecialchars(t('ct.form.t'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <form id="contact_form" class="contact-form" action="#" method="get">
                    <label>
                        <span><?php echo htmlspecialchars(t('ct.form.name'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="field-wrap" data-autosave>
                            <input type="text" id="ct_name" name="name" required>
                            <span class="save-dot" data-save-surface data-save-state="saved"></span>
                        </span>
                    </label>
                    <label>
                        <span><?php echo htmlspecialchars(t('ct.form.email'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="field-wrap" data-autosave>
                            <input type="email" id="ct_email" name="from" required>
                            <span class="save-dot" data-save-surface data-save-state="saved"></span>
                        </span>
                    </label>
                    <label>
                        <span><?php echo htmlspecialchars(t('ct.form.subject'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <select id="ct_subject" name="subject">
                            <option value="<?php echo htmlspecialchars(t('ct.subject.borrow'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(t('ct.subject.borrow'), ENT_QUOTES, 'UTF-8'); ?></option>
                            <option value="<?php echo htmlspecialchars(t('ct.subject.broker'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(t('ct.subject.broker'), ENT_QUOTES, 'UTF-8'); ?></option>
                            <option value="<?php echo htmlspecialchars(t('ct.subject.lender'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(t('ct.subject.lender'), ENT_QUOTES, 'UTF-8'); ?></option>
                            <option value="<?php echo htmlspecialchars(t('ct.subject.other'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(t('ct.subject.other'), ENT_QUOTES, 'UTF-8'); ?></option>
                        </select>
                    </label>
                    <label>
                        <span><?php echo htmlspecialchars(t('ct.form.message'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="field-wrap" data-autosave>
                            <textarea id="ct_message" name="message" rows="3" required></textarea>
                            <span class="save-dot" data-save-surface data-save-state="saved"></span>
                        </span>
                    </label>
                    <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars(t('ct.form.send'), ENT_QUOTES, 'UTF-8'); ?></button>
                </form>
            </div>

            <div class="card coords">
                <div class="line2"></div>
                <h3><?php echo htmlspecialchars(t('ct.coords.founder'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <div class="line2"></div>
                <p><?php echo nl2br(htmlspecialchars(t('ct.coords.address'), ENT_QUOTES, 'UTF-8')); ?><br>
                    <a href="mailto:info@bafinanciere.ca">info@bafinanciere.ca</a><br>
                    +1 (819) 342-6072</p>
                <div class="line2"></div>
                <h3><?php echo htmlspecialchars(t('ct.coords.company'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <div class="line2"></div>
                <p><?php echo nl2br(htmlspecialchars(t('ct.coords.address'), ENT_QUOTES, 'UTF-8')); ?><br>
                    <a href="mailto:info@bafinanciere.ca">info@bafinanciere.ca</a><br>
                    +1 (819) 535-3114</p>
            </div>
        </div>
    </div>
</section>
