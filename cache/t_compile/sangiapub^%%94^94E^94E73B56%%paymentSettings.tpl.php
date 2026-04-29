<?php /* Smarty version 2.6.26, created on 2026-04-05 21:43:32
         compiled from checkout/paymentSettings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'checkout/paymentSettings.tpl', 15, false),array('modifier', 'escape', 'checkout/paymentSettings.tpl', 17, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', 'Payment Gateway Settings'); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php if ($_GET['saved']): ?>
    <div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">
        <strong>Berhasil!</strong> Pengaturan Payment Gateway telah disimpan ke dalam database.
    </div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'checkout','op' => 'saveSettings'), $this);?>
">
        <input type="hidden" name="csrfToken" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">

    <div style="margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; background: #fff;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Pengaturan Lingkungan (Environment)</h3>
        <table class="data" width="100%">
            <tr valign="top">
                <td width="25%" class="label"><label for="active_gateway">Gateway Aktif</label></td>
                <td width="75%" class="value">
                    <select name="active_gateway" id="active_gateway" class="selectMenu" style="padding: 5px;">
                        <option value="midtrans" <?php if ($this->_tpl_vars['active_gateway'] == 'midtrans'): ?>selected="selected"<?php endif; ?>>Midtrans (Snap)</option>
                        <option value="xendit" <?php if ($this->_tpl_vars['active_gateway'] == 'xendit'): ?>selected="selected"<?php endif; ?>>Xendit</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <td class="label"><label for="is_production">Mode Sistem</label></td>
                <td class="value">
                    <select name="is_production" id="is_production" class="selectMenu" style="padding: 5px;">
                        <option value="0" <?php if (! $this->_tpl_vars['is_production']): ?>selected="selected"<?php endif; ?>>Sandbox / Testing</option>
                        <option value="1" <?php if ($this->_tpl_vars['is_production']): ?>selected="selected"<?php endif; ?>>Production / Live</option>
                    </select>
                    <br>
                    <span style="font-size: 11px; color: #666;">Pilih Sandbox saat Anda sedang menguji pembayaran.</span>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 30px; border: 1px solid #0056b3; padding: 20px; border-radius: 5px; background: #f4f9ff;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #cce5ff; padding-bottom: 10px; color: #0056b3;">Konfigurasi Xendit</h3>
        <table class="data" width="100%">
            <tr valign="top">
                <td width="25%" class="label"><label for="xendit_api_key">Secret API Key</label></td>
                <td width="75%" class="value">
                                        <input type="password" name="xendit_api_key" id="xendit_api_key" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['xendit_api_key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" maxlength="255" class="textField" />
                </td>
            </tr>
            <tr valign="top">
                <td class="label"><label for="xendit_webhook_token">Webhook Verification Token</label></td>
                <td class="value">
                    <input type="password" name="xendit_webhook_token" id="xendit_webhook_token" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['xendit_webhook_token'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" maxlength="255" class="textField" />
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 30px; border: 1px solid #17a2b8; padding: 20px; border-radius: 5px; background: #fdfdfe;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #17a2b8;">Konfigurasi Midtrans</h3>
        <table class="data" width="100%">
            <tr valign="top">
                <td width="25%" class="label"><label for="midtrans_server_key">Server Key</label></td>
                <td width="75%" class="value">
                    <input type="password" name="midtrans_server_key" id="midtrans_server_key" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['midtrans_server_key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" maxlength="255" class="textField" />
                </td>
            </tr>
            <tr valign="top">
                <td class="label"><label for="midtrans_client_key">Client Key</label></td>
                <td class="value">
                                        <input type="text" name="midtrans_client_key" id="midtrans_client_key" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['midtrans_client_key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" maxlength="255" class="textField" />
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 20px; padding-bottom: 40px; display: flex; align-items: center;">
        <button type="submit" class="wizdam-btn wizdam-btn-success" style="padding: 10px 25px; font-size: 14px; font-weight: bold; cursor: pointer; border: none; border-radius: 4px;">
            <?php if ($_GET['saved']): ?>Perbarui Lagi<?php else: ?>Simpan Pengaturan<?php endif; ?>
        </button>

        <?php if ($_GET['saved']): ?>
                        <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'checkout','op' => 'index'), $this);?>
" style="margin-left: 15px; padding: 10px 25px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; transition: 0.3s;">
                &larr; Selesai & Kembali
            </a>
        <?php else: ?>
                        <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'checkout','op' => 'index'), $this);?>
" style="margin-left: 15px; text-decoration: none; color: #666; padding: 10px 15px;">
                Batal
            </a>
        <?php endif; ?>
    </div>
</form>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>