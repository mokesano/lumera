<?php /* Smarty version 2.6.26, created on 2026-04-05 04:45:25
         compiled from file:/home/sangiaor/public_html/journals/plugins/paymethod/manual/templates/settingsForm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/paymethod/manual/templates/settingsForm.tpl', 11, false),array('function', 'fieldLabel', 'file:/home/sangiaor/public_html/journals/plugins/paymethod/manual/templates/settingsForm.tpl', 14, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/paymethod/manual/templates/settingsForm.tpl', 17, false),)), $this); ?>
	<tr>
		<td colspan="2"><h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.settings"), $this);?>
</h4></td>
	</tr>
	<tr valign="top">
		<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'manualInstructions','required' => 'true','key' => "plugins.paymethod.manual.settings.instructions"), $this);?>
</td>
		<td class="value" width="80%">
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.paymethod.manual.settings.manualInstructions"), $this);?>
<br />
			<textarea name="manualInstructions" id="manualInstructions" rows="12" cols="60" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['manualInstructions'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
		</td>
	</tr>

    <script type="text/javascript">
    <?php echo '
    $(document).ready(function() {
        $(\'#paymentSettingsForm\').submit(function(e) {
            // Hanya validasi jika radio button "Manual" yang dipilih
            if ($(\'input[name="paymentMethodPluginName"]:checked\').val() === \'Manual\') {
                
                // Jika TinyMCE aktif, paksa sinkronisasi teks ke textarea asli
                if (typeof tinyMCE !== \'undefined\') {
                    tinyMCE.triggerSave();
                }
                
                // Ambil nilai, bersihkan dari tag HTML dan spasi bayangan
                var rawValue = $(\'#manualInstructions\').val();
                var cleanValue = $.trim(rawValue.replace(/(<([^>]+)>)/ig, "").replace(/&nbsp;/ig, ""));
                
                if (cleanValue === \'\') {
                    alert(\'Instruksi pembayaran manual wajib diisi dan tidak boleh kosong!\');
                    e.preventDefault(); // Hentikan proses simpan ke peladen
                    return false;
                }
            }
        });
    });
    '; ?>

    </script>