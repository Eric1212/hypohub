<?php
/**
 * Hypohub — modale de création d'items (partial, inclus dans l'espace membre).
 * Même pattern que auth_modal.php : un formulaire par type, ouvert selon le
 * bouton cliqué ([data-create-open="type"]). Soumission JSON vers
 * api/create_<type>.php puis rechargement de la page.
 * Variables attendues avant l'include :
 *   $create_profils     — liste des profils propriétaire de l'utilisateur
 *   $create_proprietes  — liste des propriétés de l'utilisateur
 */
?>
<div class="auth-modal" id="create_modal" aria-hidden="true">
    <div class="auth-modal-overlay" data-create-close></div>
    <div class="auth-modal-box" role="dialog" aria-modal="true" aria-labelledby="create_title">
        <button type="button" class="auth-modal-close" data-create-close aria-label="<?php echo htmlspecialchars(t('auth.close'), ENT_QUOTES, 'UTF-8'); ?>">&times;</button>

        <?php
        /* ---------- Profil propriétaire ---------- */
        ?>
        <form id="create_form_profil" class="auth-form" data-create-type="profil" data-err-network="<?php echo htmlspecialchars(t('auth.error.network'), ENT_QUOTES, 'UTF-8'); ?>" hidden novalidate>
            <h2 class="create-title"><?php echo htmlspecialchars(t('create.profil.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">

            <details class="create-details">
                <summary><?php echo htmlspecialchars(t('create.profil.identification'), ENT_QUOTES, 'UTF-8'); ?></summary>
                <div class="create-grid-2">
                    <label>
                        <span><?php echo htmlspecialchars(t('field.prenom'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <input type="text" name="prenom" required>
                    </label>
                    <label>
                        <span><?php echo htmlspecialchars(t('field.nom'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <input type="text" name="nom" required>
                    </label>
                </div>
                <label>
                    <span><?php echo htmlspecialchars(t('field.naissance'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <input type="date" name="date_naissance" required>
                </label>
            </details>

            <details class="create-details">
                <summary><?php echo htmlspecialchars(t('create.profil.coordonnees'), ENT_QUOTES, 'UTF-8'); ?></summary>
                <div class="create-grid-2">
                    <label>
                        <span><?php echo htmlspecialchars(t('field.email'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <input type="email" name="courriel" required>
                    </label>
                    <label>
                        <span><?php echo htmlspecialchars(t('field.phone'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <input type="tel" name="telephone" required>
                    </label>
                </div>
                <div class="create-grid-2">
                    <label>
                        <span><?php echo htmlspecialchars(t('field.app'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <input type="text" name="app">
                    </label>
                    <label>
                        <span><?php echo htmlspecialchars(t('field.province'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <input type="text" name="province" value="QC" maxlength="2" required>
                    </label>
                </div>
                <label>
                    <span><?php echo htmlspecialchars(t('field.adresse'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <input type="text" name="adresse" placeholder="<?php echo htmlspecialchars(t('field.adresse.placeholder'), ENT_QUOTES, 'UTF-8'); ?>" required>
                </label>
                <div class="create-grid-2">
                    <label>
                        <span><?php echo htmlspecialchars(t('field.ville'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <input type="text" name="ville" required>
                    </label>
                    <label>
                        <span><?php echo htmlspecialchars(t('field.code'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <input type="text" name="code_postal" placeholder="A1A 1A1" required>
                    </label>
                </div>
            </details>

            <details class="create-details">
                <summary><?php echo htmlspecialchars(t('create.profil.societe'), ENT_QUOTES, 'UTF-8'); ?></summary>
                <label>
                    <span><?php echo htmlspecialchars(t('field.compagnie'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <input type="text" name="nom_compagnie">
                </label>
                <label>
                    <span><?php echo htmlspecialchars(t('field.neq'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <input type="text" name="neq" placeholder="XXXXXXXXXX">
                </label>
            </details>

            <details class="create-details">
                <summary><?php echo htmlspecialchars(t('create.profil.documents'), ENT_QUOTES, 'UTF-8'); ?></summary>
                <div class="create-grid-2">
                    <label>
                        <span><?php echo htmlspecialchars(t('create.select.type'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <select name="doc_type_id">
                            <?php foreach ($create_doc_types as $dt): ?>
                            <option value="<?php echo (int) $dt['id']; ?>"><?php echo htmlspecialchars($dt['nom_fr'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span><?php echo htmlspecialchars(t('doc.select'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <input type="file" name="doc_fichiers[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                    </label>
                </div>
                <p class="note"><?php echo htmlspecialchars(t('doc.max'), ENT_QUOTES, 'UTF-8'); ?></p>
            </details>

            <p class="auth-error" data-create-error hidden></p>
            <button type="submit" class="btn btn-primary btn-block"><?php echo htmlspecialchars(t('create.submit'), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>

        <?php /* ---------- Propriété ---------- */ ?>
        <form id="create_form_propriete" class="auth-form" data-create-type="propriete" data-err-network="<?php echo htmlspecialchars(t('auth.error.network'), ENT_QUOTES, 'UTF-8'); ?>" hidden novalidate>
            <h2 class="create-title"><?php echo htmlspecialchars(t('create.propriete.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <label>
                <span><?php echo htmlspecialchars(t('field.adresse'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="text" name="adresse" required>
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('field.ville'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="text" name="ville" required>
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('field.code'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="text" name="code_postal" placeholder="G0X 2P0">
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('field.valeur'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="number" name="valeur_estimee" step="0.01" min="0" placeholder="0,00">
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('field.nette'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="number" name="valeur_nette" step="0.01" min="0" placeholder="0,00">
            </label>
            <p class="auth-error" data-create-error hidden></p>
            <button type="submit" class="btn btn-primary btn-block"><?php echo htmlspecialchars(t('create.submit'), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>

        <?php /* ---------- Dossier d'emprunt ---------- */ ?>
        <form id="create_form_dossier" class="auth-form" data-create-type="dossier" data-err-network="<?php echo htmlspecialchars(t('auth.error.network'), ENT_QUOTES, 'UTF-8'); ?>" hidden novalidate>
            <h2 class="create-title"><?php echo htmlspecialchars(t('create.dossier.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <?php if ($create_profils): ?>
            <label>
                <span><?php echo htmlspecialchars(t('create.select.profil'), ENT_QUOTES, 'UTF-8'); ?></span>
                <select name="profil_proprietaire_id" required>
                    <?php foreach ($create_profils as $cp): ?>
                    <option value="<?php echo (int) $cp['id']; ?>"><?php echo htmlspecialchars($cp['prenom'] . ' ' . $cp['nom'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php else: ?>
            <p class="note"><?php echo htmlspecialchars(t('create.dossier.noprofils'), ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <?php if ($create_proprietes): ?>
            <label>
                <span><?php echo htmlspecialchars(t('create.select.propriete'), ENT_QUOTES, 'UTF-8'); ?></span>
                <select name="profil_propriete_id" required>
                    <?php foreach ($create_proprietes as $cp): ?>
                    <option value="<?php echo (int) $cp['id']; ?>"><?php echo htmlspecialchars($cp['adresse'] . ', ' . $cp['ville'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php else: ?>
            <p class="note"><?php echo htmlspecialchars(t('create.dossier.noproprietes'), ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <label>
                <span><?php echo htmlspecialchars(t('field.montant'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="number" name="montant_demande" step="0.01" min="1" required placeholder="0,00">
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('field.rang'), ENT_QUOTES, 'UTF-8'); ?></span>
                <select name="rang">
                    <option value="premier"><?php echo htmlspecialchars(t('type.rang.premier'), ENT_QUOTES, 'UTF-8'); ?></option>
                    <option value="deuxieme"><?php echo htmlspecialchars(t('type.rang.deuxieme'), ENT_QUOTES, 'UTF-8'); ?></option>
                </select>
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('field.type'), ENT_QUOTES, 'UTF-8'); ?></span>
                <select name="type_financement">
                    <option value="travailleur_autonome"><?php echo htmlspecialchars(t('type.fin.travailleur_autonome'), ENT_QUOTES, 'UTF-8'); ?></option>
                    <option value="consolidation"><?php echo htmlspecialchars(t('type.fin.consolidation'), ENT_QUOTES, 'UTF-8'); ?></option>
                    <option value="deuxieme_rang"><?php echo htmlspecialchars(t('type.fin.deuxieme_rang'), ENT_QUOTES, 'UTF-8'); ?></option>
                    <option value="delai_serre"><?php echo htmlspecialchars(t('type.fin.delai_serre'), ENT_QUOTES, 'UTF-8'); ?></option>
                </select>
            </label>
            <p class="auth-error" data-create-error hidden></p>
            <button type="submit" class="btn btn-primary btn-block"<?php echo ($create_profils && $create_proprietes) ? '' : ' disabled'; ?>><?php echo htmlspecialchars(t('create.submit'), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>

        <?php /* ---------- Profil créancier ---------- */ ?>
        <form id="create_form_creancier" class="auth-form" data-create-type="creancier" data-err-network="<?php echo htmlspecialchars(t('auth.error.network'), ENT_QUOTES, 'UTF-8'); ?>" hidden novalidate>
            <h2 class="create-title"><?php echo htmlspecialchars(t('create.creancier.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <label>
                <span><?php echo htmlspecialchars(t('create.creancier.nom'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="text" name="nom" required>
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('field.type'), ENT_QUOTES, 'UTF-8'); ?></span>
                <select name="type">
                    <option value="individu"><?php echo htmlspecialchars(t('space.cre.type.individu'), ENT_QUOTES, 'UTF-8'); ?></option>
                    <option value="societe"><?php echo htmlspecialchars(t('space.cre.type.societe'), ENT_QUOTES, 'UTF-8'); ?></option>
                </select>
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('space.cre.capital'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="number" name="capital_disponible" step="0.01" min="0" placeholder="0,00">
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('field.permis'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="text" name="permis_opc">
            </label>
            <label>
                <span><?php echo htmlspecialchars(t('field.criteres'), ENT_QUOTES, 'UTF-8'); ?></span>
                <textarea name="criteres" rows="3"></textarea>
            </label>
            <p class="auth-error" data-create-error hidden></p>
            <button type="submit" class="btn btn-primary btn-block"><?php echo htmlspecialchars(t('create.submit'), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>
    </div>
</div>
