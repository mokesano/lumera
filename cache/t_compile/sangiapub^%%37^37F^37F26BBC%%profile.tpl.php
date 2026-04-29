<?php /* Smarty version 2.6.26, created on 2026-04-05 23:51:41
         compiled from user/profile.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'user/profile.tpl', 13, false),array('function', 'translate', 'user/profile.tpl', 20, false),array('function', 'form_language_chooser', 'user/profile.tpl', 48, false),array('function', 'html_options_translate', 'user/profile.tpl', 126, false),array('function', 'html_options', 'user/profile.tpl', 151, false),array('modifier', 'assign', 'user/profile.tpl', 13, false),array('modifier', 'escape', 'user/profile.tpl', 30, false),array('modifier', 'date_format', 'user/profile.tpl', 361, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "user.profile.editProfile"); ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'profile'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div class="auth-container">
    <div class="profile-card">
        <div class="auth-header u-mb-24">
            <p class="auth-subtitle"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.profile.description"), $this);?>
</p>
        </div>
        
        <div class="alert alert-info">
            <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.profile.alert"), $this);?>
</p>
        </div>

        <form id="profile" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'saveProfile'), $this);?>
" enctype="multipart/form-data">
            
                        <input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">

            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

            <div class="form-section-note">
                <p>
                    <span class="required-indicator">*</span>
                    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>

                </p>
            </div>
            
                        <?php if (count ( $this->_tpl_vars['formLocales'] ) > 1): ?>
                <div class="form-section">
                    <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.language"), $this);?>
</h3>
                    <div class="form-group">
                        <div class="language-selector-container">
                            <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'profile','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'userProfileUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'userProfileUrl'));?>

                            <?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'profile','url' => $this->_tpl_vars['userProfileUrl']), $this);?>

                        </div>
                        <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</div>
                    </div>
                </div>
            <?php endif; ?>

                        <div class="form-section">
                <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.accountInformation"), $this);?>
</h3>
                
                                <div class="form-group">
                    <input type="text" id="username" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['username'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" readonly>
                    <label for="username" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.username"), $this);?>
<span class="required-indicator">*</span></label>
                </div>
                <div class="form-group">
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="90" required>
                    <label for="email" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.email"), $this);?>
<span class="required-indicator">*</span></label>
                    <div class="error-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.email"), $this);?>
 is required</div>
                    <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.email"), $this);?>
 is available!</div>
                </div>
            </div>

                        <div class="form-section">
                <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.personal"), $this);?>
</h3>
                
                                <div class="form-group">
                    <input type="text" name="salutation" id="salutation" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['salutation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="40">
                    <label for="salutation" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.salutation"), $this);?>
</label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.salutation.description"), $this);?>
</div>
                    <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.salutation"), $this);?>
 looks good!</div>
                </div>
                                <div class="form-group">
                    <input type="text" name="firstName" id="firstName" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="40" required>
                    <label for="firstName" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.firstName"), $this);?>
<span class="required-indicator">*</span></label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.firstName.description"), $this);?>
</div>
                    <div class="error-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.firstName"), $this);?>
 is required</div>
                    <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.firstName"), $this);?>
 looks good!</div>
                </div>
                                <div class="form-group">
                    <input type="text" name="middleName" id="middleName" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="40">
                    <label for="middleName" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.middleName"), $this);?>
</label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.middleName.description"), $this);?>
</div>
                    <div class="error-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.middleName"), $this);?>
 is required</div>
                    <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.middleName"), $this);?>
 looks good!</div>
                </div>
                                <div class="form-group">
                    <input type="text" name="lastName" id="lastName" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="90" required>
                    <label for="lastName" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.lastName"), $this);?>
<span class="required-indicator">*</span></label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.firstName.description"), $this);?>
</div>
                    <div class="error-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.lastName"), $this);?>
 is required</div>
                    <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.lastName"), $this);?>
 looks good!</div>
                </div>
                                <div class="form-group">
                    <input type="text" id="suffix" name="suffix" class="form-control" 
                           value="<?php echo ((is_array($_tmp=$this->_tpl_vars['suffix'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="90">
                    <label for="suffix" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.suffix"), $this);?>
</label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.suffix.description"), $this);?>
</div>
                    <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.suffix"), $this);?>
 Looks good!</div>
                </div>
                        
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="initials" id="initials" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['initials'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="5">
                        <label for="initials" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.initials"), $this);?>
</label>
                        <div class="success-message">Looks good!</div>
                        <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.initialsExample"), $this);?>
</div>
                    </div>
                    <div class="form-group">
                        <select name="gender" id="gender" class="form-control">
                            <option value=""></option>
                            <?php echo $this->_plugins['function']['html_options_translate'][0][0]->smartyHtmlOptionsTranslate(array('options' => $this->_tpl_vars['genderOptions'],'selected' => $this->_tpl_vars['gender']), $this);?>

                        </select>
                        <label for="gender" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.gender"), $this);?>
</label>
                        <div class="success-message">Looks good!</div>
                        <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.gender.description"), $this);?>
</div>
                    </div>
                </div>
            </div>

                        <div class="form-section">
                <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.profile.professionalInformation"), $this);?>
</h3>
                
                <div class="form-group">
                    <textarea name="affiliation[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="affiliation" class="form-control form-textarea" rows="3" required><?php echo ((is_array($_tmp=$this->_tpl_vars['affiliation'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
                    <label for="affiliation" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.affiliation"), $this);?>
<span class="required-indicator">*</span>
                    </label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.affiliation.description"), $this);?>
</div>
                    <div class="error-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.affiliation"), $this);?>
 is required</div>
                    <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.affiliation"), $this);?>
 is good!</div>
                </div>

                <div class="form-group">
                    <select name="country" id="country" class="form-control" required>
                        <option value=""></option>
                        <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['countries'],'selected' => $this->_tpl_vars['country']), $this);?>

                    </select>
                    <label for="country" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.country"), $this);?>
<span class="required-indicator">*</span></label>
                    <div class="error-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.country"), $this);?>
 is required</div>
                    <div class="success-message"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.country"), $this);?>
 looks good!</div>
                </div>
                
                <div class="form-group">
                    <textarea name="biography[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="biography" class="form-control form-textarea" rows="7"><?php echo ((is_array($_tmp=$this->_tpl_vars['biography'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
                    <label for="biography" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.biography"), $this);?>
</label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.biography.description"), $this);?>
</div>
                </div>
                
                <div class="form-group">
                    <textarea name="signature[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="signature" class="form-control form-textarea" rows="7"><?php echo ((is_array($_tmp=$this->_tpl_vars['signature'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
                    <label for="signature" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.signature"), $this);?>
</label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.signature.description"), $this);?>
</div>
                </div>
            </div>

                        <div class="form-section">
                <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.identifiers"), $this);?>
</h3>

                <div class="form-group">
                    <input type="text" name="orcid" id="orcid" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['orcid'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="255">
                    <label for="orcid" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.orcid"), $this);?>
</label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.orcid.description"), $this);?>
</div>
                </div>

                <div class="form-group">
                    <input type="url" name="googleScholar" id="googleScholar" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['googleScholar'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="255">
                    <label for="googleScholar" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.googleScholar"), $this);?>
</label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.googleScholar.description"), $this);?>
</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="sintaId" id="sintaId" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['sintaId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="24">
                        <label for="sintaId" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.sintaId"), $this);?>
</label>
                        <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.sintaId.description"), $this);?>
</div>
                    </div>
                    <div class="form-group">
                        <input type="text" name="scopusId" id="scopusId" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['scopusId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="24">
                        <label for="scopusId" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.scopusId"), $this);?>
</label>
                        <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.scopusId.description"), $this);?>
</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="dimensionId" id="dimensionId" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['dimensionId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="24">
                        <label for="dimensionId" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.dimensionId"), $this);?>
</label>
                        <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.dimensionId.description"), $this);?>
</div>
                    </div>
                    <div class="form-group">
                        <input type="text" name="researcherId" id="researcherId" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['researcherId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="24">
                        <label for="researcherId" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.researcherId"), $this);?>
</label>
                        <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.researcherId.description"), $this);?>
</div>
                    </div>
                </div>

                <div class="form-group">
                    <input type="url" name="userUrl" id="userUrl" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['userUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="255">
                    <label for="userUrl" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.url"), $this);?>
</label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.url.description"), $this);?>
</div>
                </div>
            </div>

                        <div class="form-section">
                <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.profile.contactInformation"), $this);?>
</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <input type="tel" name="phone" id="phone" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['phone'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="24">
                        <label for="phone" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.phones"), $this);?>
</label>
                        <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.phone.description"), $this);?>
</div>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="fax" id="fax" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['fax'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="24">
                        <label for="fax" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.fax"), $this);?>
</label>
                        <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.fax.description"), $this);?>
</div>
                    </div>
                </div>

                <div class="form-group">
                    <textarea name="mailingAddress" id="mailingAddress" class="form-control form-textarea" rows="3"><?php echo ((is_array($_tmp=$this->_tpl_vars['mailingAddress'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
                    <label for="mailingAddress" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.mailingAddress"), $this);?>
</label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.mailingAddress.description"), $this);?>
</div>
                </div>

                <div class="form-group">
                    <textarea name="gossip[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="gossip" class="form-control form-textarea" rows="4"><?php echo ((is_array($_tmp=$this->_tpl_vars['gossip'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
                    <label for="gossip" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.gossip"), $this);?>
</label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.gossip.description"), $this);?>
</div>
                </div>
            </div>

                        <?php if ($this->_tpl_vars['currentJournal']): ?>
                <div class="form-section">
                    <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.roles"), $this);?>
</h3>
                    <div class="modern-checkbox-group">
                        <?php if ($this->_tpl_vars['allowRegReader']): ?>
                            <div class="modern-checkbox-item <?php if ($this->_tpl_vars['isReader'] || $this->_tpl_vars['readerRole']): ?>checked<?php endif; ?>" onclick="toggleModernCheckbox(this, 'readerRole')">
                                <div class="modern-checkbox-content">
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.reader"), $this);?>
</div>
                                        <div class="modern-checkbox-description"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.readerDescription"), $this);?>
</div>
                                    </div>
                                </div>
                                <input type="checkbox" id="readerRole" name="readerRole" class="modern-checkbox-input" <?php if ($this->_tpl_vars['isReader'] || $this->_tpl_vars['readerRole']): ?>checked="checked"<?php endif; ?>>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($this->_tpl_vars['allowRegAuthor']): ?>
                            <div class="modern-checkbox-item <?php if ($this->_tpl_vars['isAuthor'] || $this->_tpl_vars['authorRole']): ?>checked<?php endif; ?>" onclick="toggleModernCheckbox(this, 'authorRole')">
                                <div class="modern-checkbox-content">
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.author"), $this);?>
</div>
                                        <div class="modern-checkbox-description"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.authorDescription"), $this);?>
</div>
                                    </div>
                                </div>
                                <input type="checkbox" id="authorRole" name="authorRole" class="modern-checkbox-input" <?php if ($this->_tpl_vars['isAuthor'] || $this->_tpl_vars['authorRole']): ?>checked="checked"<?php endif; ?>>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($this->_tpl_vars['allowRegReviewer']): ?>
                            <div class="modern-checkbox-item <?php if ($this->_tpl_vars['isReviewer'] || $this->_tpl_vars['reviewerRole']): ?>checked<?php endif; ?>" onclick="toggleModernCheckbox(this, 'reviewerRole')">
                                <div class="modern-checkbox-content">
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.reviewer"), $this);?>
</div>
                                        <div class="modern-checkbox-description"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.register.reviewerDescription"), $this);?>
</div>
                                    </div>
                                </div>
                                <input type="checkbox" id="reviewerRole" name="reviewerRole" class="modern-checkbox-input" <?php if ($this->_tpl_vars['isReviewer'] || $this->_tpl_vars['reviewerRole']): ?>checked="checked"<?php endif; ?>>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

                        <div class="form-section" id="reviewer-interests-section">
                <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.interests"), $this);?>
</h3>
                <div class="form-group u-js-hide">
                    <input type="text" name="interestsTextOnly" id="interestsTextOnly" class="form-control" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['interestsTextOnly'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" maxlength="255">
                    <label for="interestsTextOnly" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.interests"), $this);?>
</label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.interests.description"), $this);?>
</div>
                </div>
                <div class="form-group">
                    <textarea name="interestsTextOnly" id="interestsTextOnly" class="form-control form-textarea" rows="3"><?php echo ((is_array($_tmp=$this->_tpl_vars['interestsTextOnly'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
                    <label for="interestsTextOnly" class="form-control-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.interests"), $this);?>
</label>
                    <div class="form-help-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.interests.description"), $this);?>
</div>
                </div>
            
                                <?php if ($this->_tpl_vars['allowRegReviewer'] || $this->_tpl_vars['isReviewer']): ?>
                <div class="form-group">
                    <div id="interests-container">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "form/interestsInput.tpl", 'smarty_include_vars' => array('FBV_interestsKeywords' => $this->_tpl_vars['interestsKeywords'],'FBV_interestsTextOnly' => $this->_tpl_vars['interestsTextOnly'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

                        <div class="form-section">
                <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.profile.form.profileImage"), $this);?>
</h3>
                
                <div class="auth-header u-mb-8">
                    <p class="auth-subtitle">Recommended size 150×150 pixels (max. 500 KB). <a href="javascript:void(0)" onclick="var url='https://apps.sangia.org/tools/compress/compress_image'; if(url.startsWith('https://apps.sangia.org') && confirm('Open Resize Image Tool?')) window.open(url, '_blank', 'noopener,noreferrer');">Resize your image</a> if needed. Verify before saving.</p>
                </div>
                
                <div class="form-group">
                    <div class="profile-image-upload-container">
                        <div class="file-input-wrapper">
                            <input type="file" id="profileImage" name="profileImage" class="file-input-hidden" accept="image/*">
                            <div class="file-input-display" onclick="document.getElementById('profileImage').click()">
                                <div class="file-input-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="9" cy="9" r="2"/>
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                    </svg>
                                </div>
                                <div class="file-input-content">
                                    <div class="file-input-text">Choose profile image</div>
                                    <div class="file-input-subtext">JPG, PNG, or GIF recommended</div>
                                </div>
                                <div class="file-browse-btn">Browse</div>
                            </div>
                        </div>
                        
                        <div class="profile-image-actions">
                            <input type="submit" name="uploadProfileImage" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.upload"), $this);?>
" class="button" />
                        </div>
                    </div>
                    
                    <?php if ($this->_tpl_vars['profileImage']): ?>
                        <div class="current-profile-image">
                            <div class="current-image-info">
                                <h4 class="image-info">Current Profile Image</h4>
                                <div class="image-details">
                                    <p><strong>File:</strong> <?php echo ((is_array($_tmp=$this->_tpl_vars['profileImage']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
                                    <p><strong>Uploaded:</strong> <?php echo ((is_array($_tmp=$this->_tpl_vars['profileImage']['dateUploaded'])) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatShort'])); ?>
</p>
                                </div>
                                <input type="submit" name="deleteProfileImage" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
" class="button" />
                            </div>
                            <div class="current-image-preview">
                                <img src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['profileImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" 
                                     width="<?php echo ((is_array($_tmp=$this->_tpl_vars['profileImage']['width'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                                     height="<?php echo ((is_array($_tmp=$this->_tpl_vars['profileImage']['height'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                                     alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.profile.form.profileImage"), $this);?>
" 
                                     class="profile-image-thumbnail">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

                        <?php if (count ( $this->_tpl_vars['availableLocales'] ) > 1): ?>
                <div class="form-section">
                    <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.workingLanguages"), $this);?>
</h3>
                    <div class="checkbox-group">
                        <?php $_from = $this->_tpl_vars['availableLocales']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['localeKey'] => $this->_tpl_vars['localeName']):
?>
                            <div class="checkbox-container <?php if (in_array ( $this->_tpl_vars['localeKey'] , $this->_tpl_vars['userLocales'] )): ?>checked<?php endif; ?>" onclick="toggleCheckbox(this, 'userLocales-<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
')">
                                <div class="checkbox-content">
                                    <div class="checkbox-indicator"></div>
                                    <div class="checkbox-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['localeName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
                                </div>
                                <input type="checkbox" name="userLocales[]" id="userLocales-<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                                       value="<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="checkbox-input" 
                                       <?php if (in_array ( $this->_tpl_vars['localeKey'] , $this->_tpl_vars['userLocales'] )): ?>checked="checked"<?php endif; ?>>
                            </div>
                        <?php endforeach; endif; unset($_from); ?>
                    </div>
                </div>
            <?php endif; ?>

                        <?php if ($this->_tpl_vars['displayOpenAccessNotification']): ?>
                <div class="form-section">
                    <h3 class="form-section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.profile.form.openAccessNotifications"), $this);?>
</h3>
                    <div class="checkbox-group">
                        <?php $_from = $this->_tpl_vars['journals']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['journalOpenAccessNotifications'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['journalOpenAccessNotifications']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['thisJournalId'] => $this->_tpl_vars['thisJournal']):
        $this->_foreach['journalOpenAccessNotifications']['iteration']++;
?>
                            <?php $this->assign('thisJournalId', $this->_tpl_vars['thisJournal']->getJournalId()); ?>
                            <?php $this->assign('publishingMode', $this->_tpl_vars['thisJournal']->getSetting('publishingMode')); ?>
                            <?php $this->assign('enableOpenAccessNotification', $this->_tpl_vars['thisJournal']->getSetting('enableOpenAccessNotification')); ?>
                            <?php $this->assign('notificationEnabled', $this->_tpl_vars['user']->getSetting('openAccessNotification',$this->_tpl_vars['thisJournalId'])); ?>
                            
                            <?php if ($this->_tpl_vars['publishingMode'] == @PUBLISHING_MODE_SUBSCRIPTION && $this->_tpl_vars['enableOpenAccessNotification']): ?>
                                <div class="checkbox-container <?php if ($this->_tpl_vars['notificationEnabled']): ?>checked<?php endif; ?>" onclick="toggleCheckbox(this, 'openAccessNotify-<?php echo ((is_array($_tmp=$this->_tpl_vars['thisJournalId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
')">
                                    <div class="checkbox-content">
                                        <div class="checkbox-indicator"></div>
                                        <div class="checkbox-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
                                    </div>
                                    <input type="checkbox" name="openAccessNotify[]" id="openAccessNotify-<?php echo ((is_array($_tmp=$this->_tpl_vars['thisJournalId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                                           value="<?php echo ((is_array($_tmp=$this->_tpl_vars['thisJournalId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="checkbox-input" 
                                           <?php if ($this->_tpl_vars['notificationEnabled']): ?>checked="checked"<?php endif; ?>>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; endif; unset($_from); ?>
                    </div>
                </div>
            <?php endif; ?>

                        <?php if ($this->_tpl_vars['turnstileEnabled'] || $this->_tpl_vars['reCaptchaEnabled']): ?>
            <div class="security-barrier">
                                <?php if ($this->_tpl_vars['turnstileEnabled']): ?>
                <div class="turnstile-group">
                    <div id="turnstile-container" class="cf-turnstile" 
                        data-sitekey="<?php echo ((is_array($_tmp=$this->_tpl_vars['turnstilePublicKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                        data-theme="light"
                        data-size="flexible"
                        data-callback="onTurnstileSuccess">
                    </div>
                    <div id="turnstile-loading"><span class="spinner"></span> Loading security verification...</div>
                    <div id="turnstile-error"></div>
                </div>
                <?php endif; ?>
                    
                                <?php if ($this->_tpl_vars['reCaptchaEnabled']): ?>
                <div class="recaptcha-group">
                    <?php if ($this->_tpl_vars['reCaptchaVersion'] == 3): ?>
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                        <script src="https://www.google.com/recaptcha/api.js?render=<?php echo ((is_array($_tmp=$this->_tpl_vars['reCaptchaPublicKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"></script>
                        <?php echo '
                        <script>document.addEventListener("DOMContentLoaded",function(){var a=document.getElementById(\'g-recaptcha-response\');if(a){var b=a.closest(\'form\');if(b){b.addEventListener(\'submit\',function(c){c.preventDefault();var d=\''; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['reCaptchaPublicKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo '\';grecaptcha.ready(function(){grecaptcha.execute(d,{action:\'register\'}).then(function(e){document.getElementById(\'g-recaptcha-response\').value=e;b.submit()})})})}}});</script>
                        '; ?>

                    <?php elseif ($this->_tpl_vars['reCaptchaVersion'] == 2): ?>
                        <div class="g-recaptcha" 
                            data-sitekey="<?php echo ((is_array($_tmp=$this->_tpl_vars['reCaptchaPublicKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                            data-theme="light"
                            data-size="flexible">
                        </div>
                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>        
                    <?php else: ?>
                                                <div class="value u-mb-24">
                            <?php echo $this->_tpl_vars['reCaptchaHtml']; ?>

                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                    
                                <?php if ($this->_tpl_vars['turnstileEnabled'] || $this->_tpl_vars['reCaptchaEnabled']): ?>
                    <div class="form-help-text u-hide">
                        ScholarWizdam register system protected by 
                        <?php if ($this->_tpl_vars['turnstileEnabled'] && $this->_tpl_vars['reCaptchaEnabled']): ?>Cloudflare Turnstile & Google reCAPTCHA
                        <?php elseif ($this->_tpl_vars['turnstileEnabled']): ?>Cloudflare Turnstile
                        <?php else: ?>Google reCAPTCHA<?php endif; ?>.
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
                            
                        <div class="turnstile-group">
                <div id="turnstile-container" class="cf-turnstile"
                    data-sitekey="<?php echo ((is_array($_tmp=$this->_tpl_vars['turnstilePublicKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"
                    data-theme="light"
                    data-size="flexible"
                    data-callback="onTurnstileSuccess">
                </div>
                <div id="turnstile-loading"><span class="spinner"></span> Loading security verification...</div>
                <div id="turnstile-error"></div>
            </div>
            
                        <div class="actions-button">
                <p>
                    <input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button" />
                    <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="defaultButton" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user'), $this);?>
'" />
                </p>
            </div>

            <div class="form-section-note">
                <p>
                    <span class="required-indicator">*</span>
                    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>

                </p>
            </div>

        </form>
    </div>
</div>

<script>
<?php echo '
document.addEventListener(\'DOMContentLoaded\', function() {
    // Initialize only essential components
    initializeOJSInterestsInput();
    initializeFileInput();
    initializeFormSubmission();
});

function initializeOJSInterestsInput() {
    // Wait for OJS scripts and tagit to be fully loaded
    setTimeout(function() {
        const interestsContainer = document.getElementById(\'interests-container\');
        
        if (interestsContainer) {
            // Only apply styling, don\'t create new elements
            applyInterestsStyling(interestsContainer);
            observeInterestsChanges(interestsContainer);
        }
    }, 300);
}

function applyInterestsStyling(container) {
    // Find the actual OJS interests elements
    const interestsDiv = container.querySelector(\'#interests\');
    const interestsList = container.querySelector(\'.interests\');
    
    if (interestsDiv && interestsList) {
        // Add CSS class instead of inline styles
        interestsDiv.classList.add(\'tagit-modern\');
        interestsList.classList.add(\'interests-modern\');
        
        // Style existing tagit tags
        styleExistingTags(container);
        
        // Style the input field if it exists
        const tagitInput = interestsList.querySelector(\'input[type="text"]\');
        if (tagitInput) {
            tagitInput.classList.add(\'tagit-input-modern\');
        }
    }
    
    // Handle textarea fallback (when JavaScript is disabled)
    const textareaFallback = container.querySelector(\'.interestsTextOnly\');
    if (textareaFallback && window.getComputedStyle(textareaFallback).display !== \'none\') {
        textareaFallback.classList.add(\'form-control\', \'form-textarea\');
    }
}

function styleExistingTags(container) {
    const existingTags = container.querySelectorAll(\'.tagit-tag\');
    
    existingTags.forEach(tag => {
        if (!tag.hasAttribute(\'data-modern-styled\')) {
            tag.classList.add(\'tagit-tag-modern\');
            
            // Style the close button if it exists
            const closeBtn = tag.querySelector(\'.tagit-close\');
            if (closeBtn) {
                closeBtn.classList.add(\'tagit-close-modern\');
            }
            
            tag.setAttribute(\'data-modern-styled\', \'true\');
        }
    });
}

function observeInterestsChanges(container) {
    // Only observe changes to apply styling to new tags
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === \'childList\' && mutation.addedNodes.length > 0) {
                // Style any new tags that are added dynamically
                setTimeout(function() {
                    styleExistingTags(container);
                    
                    // Re-style input if it changes
                    const interestsList = container.querySelector(\'.interests\');
                    if (interestsList) {
                        const tagitInput = interestsList.querySelector(\'input[type="text"]:not(.tagit-input-modern)\');
                        if (tagitInput) {
                            tagitInput.classList.add(\'tagit-input-modern\');
                        }
                    }
                }, 10);
            }
        });
    });
    
    observer.observe(container, {
        childList: true,
        subtree: true
    });
    
    container._observer = observer;
}

function initializeFileInput() {
    const fileInput = document.getElementById(\'profileImage\');
    const fileDisplay = document.querySelector(\'.file-input-display\');
    const textElement = document.querySelector(\'.file-input-text\');
    const subtextElement = document.querySelector(\'.file-input-subtext\');
    
    if (fileInput && fileDisplay) {
        fileInput.addEventListener(\'change\', function(e) {
            const file = e.target.files[0];
            if (file) {
                textElement.textContent = file.name;
                subtextElement.textContent = `${Math.round(file.size / 1024)} KB`;
                fileDisplay.classList.add(\'file-selected\');
                
                // File validation
                const allowedTypes = [\'image/jpeg\', \'image/png\', \'image/gif\'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!allowedTypes.includes(file.type)) {
                    showProfileFeedback(\'Please select a valid image file (JPG, PNG, or GIF)\', \'error\');
                    resetFileInput();
                    return;
                }
                
                if (file.size > maxSize) {
                    showProfileFeedback(\'File size too large. Please use an image under 5MB\', \'error\');
                    resetFileInput();
                    return;
                }
                
                // Enable upload button by removing disabled attribute
                const uploadBtn = document.querySelector(\'input[name="uploadProfileImage"]\');
                if (uploadBtn) {
                    uploadBtn.removeAttribute(\'disabled\');
                }
                
            } else {
                resetFileInput();
            }
        });
    }
}

function resetFileInput() {
    const textElement = document.querySelector(\'.file-input-text\');
    const subtextElement = document.querySelector(\'.file-input-subtext\');
    const fileDisplay = document.querySelector(\'.file-input-display\');
    const uploadBtn = document.querySelector(\'input[name="uploadProfileImage"]\');
    
    if (textElement) textElement.textContent = \'Choose profile image\';
    if (subtextElement) subtextElement.textContent = \'JPG, PNG, or GIF recommended\';
    if (fileDisplay) fileDisplay.classList.remove(\'file-selected\');
    if (uploadBtn) uploadBtn.setAttribute(\'disabled\', \'disabled\');
}

function initializeFormSubmission() {
    const form = document.getElementById(\'profile\');
    if (!form) return;
    
    form.addEventListener(\'submit\', function(e) {
        const submitter = document.activeElement;
        
        const isImageUpload = submitter && submitter.name === \'uploadProfileImage\';
        const isImageDelete = submitter && submitter.name === \'deleteProfileImage\';
        const isLanguageChange = submitter && submitter.name === \'setLocale\';
        
        if (isImageUpload) {
            console.log(\'Image upload initiated\');
            const fileInput = document.getElementById(\'profileImage\');
            if (fileInput && fileInput.files && fileInput.files[0]) {
                const file = fileInput.files[0];
                console.log(\'Uploading file:\', file.name, \'Size:\', file.size, \'Type:\', file.type);
                showProfileFeedback(\'Profile image is being uploaded...\', \'info\');
            }
            return true;
        }
        
        if (isImageDelete) {
            console.log(\'Image delete initiated\');
            showProfileFeedback(\'Profile image is being deleted...\', \'info\');
            return true;
        }
        
        if (isLanguageChange) {
            console.log(\'Language change initiated\');
            return true;
        }
        
        // Regular profile save - validate required fields
        console.log(\'Profile save initiated\');
        const isValid = validateProfileForm(form);
        if (!isValid) {
            e.preventDefault();
            showProfileFeedback(\'Please fill in all required fields\', \'error\');
            return false;
        }
        
        return true;
    });
}

function validateProfileForm(form) {
    const requiredFields = form.querySelectorAll(\'input[required], select[required], textarea[required]\');
    let isValid = true;
    
    requiredFields.forEach(function(field) {
        if (!field.value || !field.value.trim()) {
            field.classList.add(\'form-error\');
            isValid = false;
        } else {
            field.classList.remove(\'form-error\');
        }
    });
    
    return isValid;
}

function showProfileFeedback(message, type) {
    // Remove existing feedback
    const existingFeedback = document.querySelector(\'.profile-feedback\');
    if (existingFeedback) {
        existingFeedback.remove();
    }
    
    // Create new feedback element
    const feedback = document.createElement(\'div\');
    feedback.className = `profile-feedback profile-feedback-${type}`;
    feedback.innerHTML = `<strong>${type === \'error\' ? \'Error: \' : type === \'success\' ? \'Success: \' : \'Info: \'}</strong>${message}`;
    
    // Insert feedback at top of form
    const form = document.getElementById(\'profile\');
    if (form && form.firstChild) {
        form.insertBefore(feedback, form.firstChild);
    }
    
    // Auto remove after delay
    setTimeout(function() {
        if (feedback.parentNode) {
            feedback.remove();
        }
    }, 5000);
}

// Global error handler
window.onerror = function(msg, url, lineNo, columnNo, error) {
    console.log(\'JavaScript error:\', msg);
    return true; // Suppress error display
};

// Ensure console exists
if (typeof console === \'undefined\') {
    window.console = {
        log: function() {},
        error: function() {},
        warn: function() {}
    };
}

console.log(\'Clean profile form script loaded successfully\');
'; ?>

</script>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-welcome.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>