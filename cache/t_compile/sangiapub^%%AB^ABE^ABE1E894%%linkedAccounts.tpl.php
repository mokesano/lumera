<?php /* Smarty version 2.6.26, created on 2026-04-16 17:31:28
         compiled from user/linkedAccounts.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'user/linkedAccounts.tpl', 62, false),array('function', 'url', 'user/linkedAccounts.tpl', 70, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "user.linkedAccounts"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-admin.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div id="linkedAccounts" style="max-width: 800px;">
    <p style="margin-bottom: 25px; color: #555; line-height: 1.6;">
        Tautkan akun ScholarWizdam Anda dengan layanan pihak ketiga untuk mempermudah akses masuk (Login) dan melakukan sinkronisasi otomatis terhadap data identitas riset Anda.
    </p>

        <?php if ($_GET['success'] == 'google_linked'): ?>
        <div style="padding: 10px 15px; margin-bottom: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
            &#10004; Akun Google berhasil ditautkan.
        </div>
    <?php elseif ($_GET['success'] == 'google_unlinked'): ?>
        <div style="padding: 10px 15px; margin-bottom: 20px; background-color: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; border-radius: 4px;">
            &#10004; Tautan akun Google berhasil dilepas.
        </div>
    <?php elseif ($_GET['error'] == 'google_in_use'): ?>
        <div style="padding: 10px 15px; margin-bottom: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;">
            &#10008; <strong>Gagal:</strong> Akun Google tersebut sudah tertaut dengan pengguna lain di jurnal ini.
        </div>
    <?php elseif ($_GET['success'] == 'orcid_linked'): ?>
        <div style="padding: 10px 15px; margin-bottom: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
            &#10004; ORCID iD berhasil ditautkan.
        </div>
    <?php elseif ($_GET['success'] == 'orcid_unlinked'): ?>
        <div style="padding: 10px 15px; margin-bottom: 20px; background-color: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; border-radius: 4px;">
            &#10004; Tautan ORCID iD berhasil dilepas.
        </div>
    <?php elseif ($_GET['error'] == 'orcid_in_use'): ?>
        <div style="padding: 10px 15px; margin-bottom: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;">
            &#10008; <strong>Gagal:</strong> ORCID iD tersebut sudah tertaut dengan pengguna lain di jurnal ini.
        </div>
    <?php endif; ?>

    <table class="data" width="100%" style="border-collapse: collapse; border: 1px solid #ddd; background: #fff; border-radius: 5px;">

                                <?php if ($this->_tpl_vars['googleSsoEnabled']): ?>
        <tr valign="middle">
            <td width="25%" class="label" style="padding: 20px 15px; background: #fcfcfc;">
                <strong>Google Account</strong>
            </td>
            <td width="55%" class="value" style="padding: 20px 15px;">
                <?php if ($this->_tpl_vars['isGoogleLinked']): ?>
                    <span style="color: #28a745; font-weight: bold;">&#10004; Tertaut</span><br/>
                    <?php if ($this->_tpl_vars['googleEmail']): ?>
                        <span style="font-size: 0.9em; color: #555;"><?php echo ((is_array($_tmp=$this->_tpl_vars['googleEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color: #6c757d;">Belum tertaut</span>
                <?php endif; ?>
            </td>
            <td width="20%" align="right" style="padding: 20px 15px;">
                <?php if ($this->_tpl_vars['isGoogleLinked']): ?>
                    <button type="button" class="button" onclick="if(confirm('Apakah Anda yakin ingin memutus tautan Akun Google ini dari profil Anda?')) window.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => 'index','page' => 'login','op' => "google-unlink"), $this);?>
';">Unlink</button>
                <?php else: ?>
                    <button type="button" class="button defaultButton" onclick="window.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => 'index','page' => 'login','op' => "google-auth"), $this);?>
';">Link Google</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>

                                <?php if ($this->_tpl_vars['orcidSsoEnabled']): ?>
        <tr valign="middle" style="border-bottom: 1px solid #eee;">
            <td width="25%" class="label" style="padding: 20px 15px; background: #fcfcfc;">
                <strong><span style="color: #A6CE39; font-size: 1.1em;">iD</span> ORCID</strong>
            </td>
            <td width="55%" class="value" style="padding: 20px 15px;">
                <?php if ($this->_tpl_vars['isOrcidLinked']): ?>
                    <span style="color: #28a745; font-weight: bold;">&#10004; Tertaut</span><br/>
                    <a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['orcidUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" style="font-size: 0.9em; color: #0056b3;"><?php echo ((is_array($_tmp=$this->_tpl_vars['orcidUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
                <?php else: ?>
                    <span style="color: #6c757d;">Belum tertaut</span>
                <?php endif; ?>
            </td>
            <td width="20%" align="right" style="padding: 20px 15px;">
                <?php if ($this->_tpl_vars['isOrcidLinked']): ?>
                    <button type="button" class="button" onclick="if(confirm('Apakah Anda yakin ingin memutus tautan ORCID iD ini dari profil Anda?')) window.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => 'index','page' => 'login','op' => "orcid-unlink"), $this);?>
';">Unlink</button>
                <?php else: ?>
                    <button type="button" class="button defaultButton" onclick="window.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => 'index','page' => 'login','op' => "orcid-auth"), $this);?>
';">Link ORCID</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>

    </table>
    
    <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px;">
        <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user'), $this);?>
" class="action" style="text-decoration: none;">&larr; Kembali ke Profil Utama</a>
    </div>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-admin.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
