<?php /* Smarty version 2.6.26, created on 2026-04-09 20:02:43
         compiled from checkout/invoice.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'checkout/invoice.tpl', 57, false),array('modifier', 'nl2br', 'checkout/invoice.tpl', 71, false),array('modifier', 'date_format', 'checkout/invoice.tpl', 82, false),array('modifier', 'strip_unsafe_html', 'checkout/invoice.tpl', 98, false),array('function', 'url', 'checkout/invoice.tpl', 178, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "checkout.invoiceDetail"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php echo '
<style>
    .wizdam-invoice-wrapper { background: #fff; padding: 40px; border-radius: 4px; border: 1px solid #e0e0e0; color: #333; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; }
    .wi-header { display: flex; justify-content: space-between; border-bottom: 2px solid #222; padding-bottom: 15px; margin-bottom: 25px; }
    .wi-journal h2 { margin: 0; font-size: 1.2rem; font-weight: bold; color: #1a4f8b; }
    .wi-journal p { margin: 2px 0 0 0; font-size: 1em; color: #666; line-height: 1;}
    .wi-title h1 { margin: 0; font-size: 36px; font-weight: 800; letter-spacing: 2px; color: #222; text-align: right; }
    .wi-title .inv-num { font-size: 14px; color: #555; text-align: right; }
    
    .wi-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; font-size: 13px; }
    .wi-meta-box { background: #f9f9f9; padding: 15px; border-radius: 4px; }
    .wi-meta-box strong { display: block; margin-bottom: 5px; font-size: 11px; text-transform: uppercase; color: #777; }
    .wi-meta-box.right-align table { width: 100%; }
    .wi-meta-box.right-align td { padding: 4px 0; border-bottom: 1px solid #eee; }
    .wi-meta-box.right-align tr:last-child td { border-bottom: none; }
    
    .wi-section-title { font-size: 16px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 15px; margin-top: 30px;}
    
    .wi-details-table { width: 100%; font-size: 13px; margin-bottom: 20px; border-collapse: collapse; }
    .wi-details-table td { padding: 8px 0; vertical-align: top; }
    .wi-details-table td:first-child { width: 150px; color: #666; }
    .wi-details-table td:last-child { font-weight: 500; }
    
    .wi-fees-table { width: 100%; font-size: 13px; border-collapse: collapse; margin-bottom: 20px; }
    .wi-fees-table td { padding: 10px 5px; border-bottom: 1px dotted #ccc; }
    .wi-fees-table td.amount { text-align: right; }
    .wi-fees-table tr.subtotal td { font-weight: bold; border-top: 1px solid #333; border-bottom: none; padding-top: 15px;}
    .wi-fees-table tr.tax td { color: #28a745; border-bottom: 1px solid #333; padding-bottom: 15px;}
    
    .wi-total-box { display: flex; justify-content: space-between; align-items: center; background: #f4fdf8; padding: 15px 20px; border-left: 5px solid #28a745; margin-top: 10px; }
    .wi-total-box .label { font-size: 16px; font-weight: bold; text-transform: uppercase; }
    .wi-total-box .amount { font-size: 24px; font-weight: bold; color: #28a745; }
    
    .wi-notes-box { border: 1px solid #ffeeba; background: #fffaf0; padding: 15px; border-radius: 4px; font-size: 12px; margin-top: 30px; }
    
    .wi-actions { margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
    .wi-pay-btn { background: #fff; border: 1px solid #ccc; padding: 15px; border-radius: 4px; cursor: pointer; text-align: center; transition: 0.2s; }
    .wi-pay-btn:hover { border-color: #1a4f8b; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    
    .action-button a:hover { border-color: #1a4f8b; box-shadow: 0 4px 10px rgba(0,0,0,0.1); color: #fff; }
    .action-button .download { text-decoration: none; display: inline-block; padding: 10px 20px; border: 1px solid #1a4f8b; color: #fff; border-radius: 4px;background-color: #1a4f8b;" }
    .action-button .cancel-action { text-decoration: none; display: inline-block; padding: 10px 20px; border: 1px solid #999; border-radius: 4px; background-color: #999; color: beige; font-weight: 700; }
    .action-button .cancel-action:hover { text-decoration: none; border: 1px solid #dc3545; color: beige; background-color: #e60000; }
    .action-button a.download:hover { background-color: #297ad6; color: #fff; }
    .action-button a.cancel-action:hover { color: #fff; }
</style>
'; ?>


<div class="wizdam-invoice-wrapper">
    <div class="wi-header">
        <div class="wi-journal">
            <h2><?php if ($this->_tpl_vars['journal']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>Sangia Research Media and Publishing<?php endif; ?></h2>
            <p>Owner PT. Sangia Research Media and Publishing</p>
            <p>Legal Dirjen AHU No. AHU-050003.AH.01.30.Tahun 2022.</p>
        </div>
        <div class="wi-title">
            <h1>INVOICE</h1>
            <div class="inv-num">Invoice Number: <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['wizdamInvoiceNumber'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong></div>
        </div>
    </div>

    <div class="wi-meta-grid">
        <div class="wi-meta-box">
            <strong>Billed To:</strong>
            <div style="font-size: 15px; font-weight: bold; margin-bottom: 4px;"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
            <div><?php echo ((is_array($_tmp=$this->_tpl_vars['authorAffiliation'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</div>
            <div style="margin-top: 5px;">Email: <span style="color: #0056b3;"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></div>
        </div>
        <div class="wi-meta-box right-align">
            <table>
                <tr>
                    <td style="color:#666;">Invoice Code:</td>
                    <td style="text-align:right; font-weight:bold;"><?php echo ((is_array($_tmp=$this->_tpl_vars['wizdamInvoiceCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
                </tr>
                <tr>
                    <td style="color:#666;">Date Billed:</td>
                    <td style="text-align:right; font-weight:bold;"><?php echo ((is_array($_tmp=$this->_tpl_vars['dateBilled'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %B %Y") : smarty_modifier_date_format($_tmp, "%d %B %Y")); ?>
</td>
                </tr>
                <tr>
                    <td style="color:#666;">Status:</td>
                    <td style="text-align:right; font-weight:bold; color:<?php if ($this->_tpl_vars['isPaid']): ?>#28a745<?php else: ?>#dc3545<?php endif; ?>;">
                        <?php if ($this->_tpl_vars['isPaid']): ?>PAID<?php else: ?>UNPAID<?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="wi-section-title">Article Details</div>
    <table class="wi-details-table">
        <tr>
            <td>Title</td>
            <td><?php if ($this->_tpl_vars['articleTitle']): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['articleTitle'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
<?php else: ?><em>Judul naskah belum terhubung</em><?php endif; ?></td>
        </tr>
        <tr>
            <td>Description</td>
            <td style="font-style: italic; color: #555;"><?php echo ((is_array($_tmp=$this->_tpl_vars['localizedFeeName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
        </tr>
    </table>

    <div class="wi-section-title">Fees Breakdown</div>
    <table class="wi-fees-table">
        <?php if ($this->_tpl_vars['formattedBaseFee'] != "0.00"): ?>
        <tr>
            <td>Base Fee (Submission / Publication)</td>
            <td class="amount"><?php echo $this->_tpl_vars['currencyCode']; ?>
 <?php echo $this->_tpl_vars['formattedBaseFee']; ?>
</td>
        </tr>
        <?php endif; ?>
        <?php if ($this->_tpl_vars['formattedFastTrackFee'] != "0.00"): ?>
        <tr>
            <td>Fast-Track Review Fee</td>
            <td class="amount"><?php echo $this->_tpl_vars['currencyCode']; ?>
 <?php echo $this->_tpl_vars['formattedFastTrackFee']; ?>
</td>
        </tr>
        <?php endif; ?>
        <?php if ($this->_tpl_vars['formattedDiscount'] != "0.00"): ?>
        <tr>
            <td style="color: #28a745;">Discount (Trade / Promo)</td>
            <td class="amount" style="color: #28a745;">- <?php echo $this->_tpl_vars['currencyCode']; ?>
 <?php echo $this->_tpl_vars['formattedDiscount']; ?>
</td>
        </tr>
        <?php endif; ?>
        <tr class="subtotal">
            <td>Subtotal (Taxable Amount)</td>
            <td class="amount"><?php echo $this->_tpl_vars['currencyCode']; ?>
 <?php echo $this->_tpl_vars['formattedSubtotal']; ?>
</td>
        </tr>
        <tr class="tax">
            <td>Tax/VAT (<?php echo $this->_tpl_vars['taxPercentage']; ?>
%) <?php if ($this->_tpl_vars['isTaxInclusive']): ?>Inclusive<?php else: ?>Exclusive<?php endif; ?></td>
            <td class="amount"><?php echo $this->_tpl_vars['currencyCode']; ?>
 <?php echo $this->_tpl_vars['formattedTax']; ?>
</td>
        </tr>
    </table>

    <div class="wi-total-box">
        <div class="label" style="margin-top: 0;">Amount Due</div>
        <div class="amount"><?php echo $this->_tpl_vars['currencyCode']; ?>
 <?php echo $this->_tpl_vars['formattedAmount']; ?>
</div>
    </div>
    <div style="font-size: 10px; color: #888; text-align: center; margin-top: 5px;">
        * Taxable Amount calculated after stated trade discounts. Tax/VAT applied per Indonesian VAT Law and OECD Guidelines.
    </div>

    <?php if (! $this->_tpl_vars['isPaid']): ?>
    <div class="wi-actions">
        <button type="button" class="wi-pay-btn" onclick="processPayment('qris')">
            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg" alt="QRIS" style="height: 25px; margin-bottom: 5px;"><br>
            <strong>Scan QR Code (QRIS)</strong><br>
            <span style="font-size: 11px; color: #666;">Gopay, OVO, Dana, LinkAja</span>
        </button>
        <button type="button" class="wi-pay-btn" onclick="processPayment('bank_transfer')">
            <div style="font-size: 20px; color: #0056b3; margin-bottom: 5px;"><i class="icon-building"></i></div>
            <strong>Virtual Account (VA)</strong><br>
            <span style="font-size: 11px; color: #666;">BCA, Mandiri, BNI, BRI, Permata</span>
        </button>
        <button type="button" class="wi-pay-btn" onclick="processPayment('all')">
            <div style="font-size: 20px; color: #17a2b8; margin-bottom: 5px;"><i class="icon-credit-card"></i></div>
            <strong>Metode Lainnya</strong><br>
            <span style="font-size: 11px; color: #666;">Kartu Kredit, Retail</span>
        </button>
    </div>
    
    <div id="loadingIndicator" style="display: none; text-align: center; padding: 15px; color: #1a4f8b; font-weight: bold;">
        Memproses jalur pembayaran aman... Mohon tunggu.
    </div>
    <?php endif; ?>

    <div class="wi-notes-box">
        <strong>Manual Payment Instructions</strong><br><br>
        Pembayaran manual dapat dilakukan melalui Rekening:<br>
        Rekening Nomor: <strong>3273 0669 7</strong> (Bank BNI) a.n. Rochmady<br>
        Rekening Nomor: <strong>3419 0101 0494 506</strong> (Bank BRI) a.n. Rochmady<br><br>
        <em>Konfirmasi dan Bukti transfer dikirim ke email: rochmady@sangia.org. Subjek email: <?php echo ((is_array($_tmp=$this->_tpl_vars['wizdamInvoiceCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</em>
    </div>

    <div class="action-button" style="margin-top: 20px; text-align: right;">
        <?php if (! $this->_tpl_vars['isPaid']): ?>
        <a class="cancel-action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'checkout','op' => 'cancel','path' => $this->_tpl_vars['invoice']->getInvoiceId()), $this);?>
" 
           onclick="return confirm('Apakah Anda yakin ingin membatalkan tagihan ini?');">
            Cancel Billing
        </a>
        <?php endif; ?>
        <a class="download" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'checkout','op' => "download-pdf",'path' => $this->_tpl_vars['invoice']->getInvoiceId()), $this);?>
" class="wizdam-btn wizdam-btn-outline" target="_blank" >
            <i class="icon-download"></i> Download INVOICE
        </a>
    </div>
</div>

<script>
    const invoiceId = <?php echo $this->_tpl_vars['invoice']->getInvoiceId(); ?>
;
    const csrfToken = "<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
";
    const payUrl = "<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'checkout','op' => 'pay','path' => $this->_tpl_vars['invoice']->getInvoiceId()), $this);?>
";

    <?php echo '
    function processPayment(paymentType) {
        document.getElementById(\'loadingIndicator\').style.display = \'block\';
        const formData = new URLSearchParams();
        formData.append(\'csrfToken\', csrfToken); formData.append(\'ajax\', \'1\'); formData.append(\'payment_type\', paymentType);

        fetch(payUrl, { method: \'POST\', headers: { \'Content-Type\': \'application/x-www-form-urlencoded\' }, body: formData.toString() })
        .then(response => response.json())
        .then(res => {
            document.getElementById(\'loadingIndicator\').style.display = \'none\';
            if (res.status === \'success\' && res.data.gateway === \'midtrans\') {
                window.snap.pay(res.data.token, { onSuccess: function() { window.location.reload(); } });
            } else { alert("Kesalahan atau Gateway tidak dikenali."); }
        }).catch(error => { document.getElementById(\'loadingIndicator\').style.display = \'none\'; });
    }
    '; ?>

</script>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>