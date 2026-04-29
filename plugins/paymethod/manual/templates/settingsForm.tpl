{**
 * plugins/paymethod/manual/templates/settingsForm.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form for manual payment settings.
 *}
	<tr>
		<td colspan="2"><h4>{translate key="plugins.paymethod.manual.settings"}</h4></td>
	</tr>
	<tr valign="top">
		<td class="label" width="20%">{fieldLabel name="manualInstructions" required="true" key="plugins.paymethod.manual.settings.instructions"}</td>
		<td class="value" width="80%">
			{translate key="plugins.paymethod.manual.settings.manualInstructions"}<br />
			<textarea name="manualInstructions" id="manualInstructions" rows="12" cols="60" class="textArea">{$manualInstructions|escape}</textarea>
		</td>
	</tr>

    <script type="text/javascript">
    {literal}
    $(document).ready(function() {
        $('#paymentSettingsForm').submit(function(e) {
            // Hanya validasi jika radio button "Manual" yang dipilih
            if ($('input[name="paymentMethodPluginName"]:checked').val() === 'Manual') {
                
                // Jika TinyMCE aktif, paksa sinkronisasi teks ke textarea asli
                if (typeof tinyMCE !== 'undefined') {
                    tinyMCE.triggerSave();
                }
                
                // Ambil nilai, bersihkan dari tag HTML dan spasi bayangan
                var rawValue = $('#manualInstructions').val();
                var cleanValue = $.trim(rawValue.replace(/(<([^>]+)>)/ig, "").replace(/&nbsp;/ig, ""));
                
                if (cleanValue === '') {
                    alert('Instruksi pembayaran manual wajib diisi dan tidak boleh kosong!');
                    e.preventDefault(); // Hentikan proses simpan ke peladen
                    return false;
                }
            }
        });
    });
    {/literal}
    </script>
