<?php /* Smarty version 2.6.26, created on 2026-04-12 21:37:34
         compiled from billing/invoicePdf.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'billing/invoicePdf.tpl', 67, false),array('modifier', 'nl2br', 'billing/invoicePdf.tpl', 90, false),array('modifier', 'date_format', 'billing/invoicePdf.tpl', 103, false),array('modifier', 'strip_unsafe_html', 'billing/invoicePdf.tpl', 134, false),array('modifier', 'upper', 'billing/invoicePdf.tpl', 207, false),)), $this); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<?php echo '
<style>
    body { font-family: helvetica, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
    
    .wi-header { display: flex; justify-content: space-between; border-bottom: 2px solid #222; padding-bottom: 12px; margin-bottom: 20px; }
    .wi-journal h2 { margin: 0; font-size: 13px; font-weight: bold; color: #1a4f8b; }
    .wi-journal p  { margin: 2px 0 0; font-size: 10px; color: #666; }
    .wi-title h1   { margin: 0; font-size: 28px; font-weight: bold; letter-spacing: 2px; color: #222; text-align: right; }
    .wi-title .inv-num { font-size: 11px; color: #555; text-align: right; }

    .wi-meta-grid { width: 100%; margin-bottom: 20px; }
    .wi-meta-grid td { vertical-align: top; width: 50%; padding: 0 5px; }
    .wi-meta-box { background: #f9f9f9; padding: 12px; font-size: 11px; }
    .wi-meta-box .label { font-size: 9px; text-transform: uppercase; color: #777; font-weight: bold; margin-bottom: 4px; }

    .wi-section-title { font-size: 13px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 4px; margin: 20px 0 10px; }

    .wi-details-table { width: 100%; font-size: 11px; border-collapse: collapse; margin-bottom: 15px; }
    .wi-details-table td { padding: 6px 0; vertical-align: top; border-bottom: 1px dotted #eee; }
    .wi-details-table td:first-child { width: 130px; color: #666; }

    .wi-fees-table { width: 100%; font-size: 11px; border-collapse: collapse; margin-bottom: 15px; }
    .wi-fees-table td { padding: 8px 5px; border-bottom: 1px dotted #ccc; }
    .wi-fees-table td.amount { text-align: right; }
    .wi-fees-table tr.subtotal td { font-weight: bold; border-top: 1px solid #333; border-bottom: none; padding-top: 10px; }
    .wi-fees-table tr.tax td { color: #28a745; border-bottom: 1px solid #333; padding-bottom: 10px; }

    .wi-total-box { background: #f4fdf8; padding: 12px 15px; border-left: 4px solid #28a745; margin-top: 8px; }
    .wi-total-label { font-size: 13px; font-weight: bold; text-transform: uppercase; }
    .wi-total-amount { font-size: 20px; font-weight: bold; color: #28a745; text-align: right; }

    .item-table { margin-top: 15px; }
    .item-table th { background-color: #d54449; color: #fff; padding: 8px; text-align: center; }
    .item-table td { padding: 8px; }
    .trasaction td { border-bottom: 1px solid #d54449; }
    .balance { background-color: #ffe6e6; }
    
    .wi-notes-box { border: 1px solid #ffeeba; background: #fffaf0; padding: 12px; font-size: 10px; margin-top: 20px; }

    .wi-qr-section { text-align: center; margin-top: 20px; }
    .wi-qr-section img { width: 30px; height: 30px; }
    .wi-qr-section p { font-size: 9px; color: #888; margin: 4px 0 0; }

    .wi-footer { text-align: center; font-size: 9px; color: #aaa; margin-top: 30px; border-top: 1px solid #eee; padding-top: 8px; }

    .status-paid   { color: #28a745; font-weight: bold; }
    .status-unpaid { color: #dc3545; font-weight: bold; }
</style>
'; ?>

</head>
<body>

<header>
        <table class="wi-header" width="100%">
        <tr>
            <td style="vertical-align: top;">
                <div class="wi-journal">
                    <h2><?php if ($this->_tpl_vars['journal']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>Sangia Research Media and Publishing<?php endif; ?></h2>
                    <p>Owner PT. Sangia Research Media and Publishing</p>
                    <p>Legal Dirjen AHU No. AHU-050003.AH.01.30.Tahun 2022.</p>
                </div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div class="wi-title">
                    <h1>INVOICE</h1>
                    <div class="inv-num">Invoice Number: <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['wizdamInvoiceNumber'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong></div>
                </div>
            </td>
        </tr>
    </table>
</header>

<section>
        <table class="wi-meta-grid" width="100%">
        <tr>
            <td>
                <div class="wi-meta-box">
                    <div class="label">Billed To</div>
                    <div style="font-size: 12px; font-weight: bold; margin-bottom: 3px;"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
                    <div><?php echo ((is_array($_tmp=$this->_tpl_vars['authorAffiliation'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</div>
                    <div style="margin-top: 4px;">Email: <?php echo ((is_array($_tmp=$this->_tpl_vars['authorEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
                </div>
            </td>
            <td>
                <div class="wi-meta-box">
                    <table width="100%">
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
                        <?php if ($this->_tpl_vars['isPaid'] && $this->_tpl_vars['datePaid']): ?>
                        <tr>
                            <td style="color:#666;">Date Paid:</td>
                            <td style="text-align:right; font-weight:bold; color:#28a745;"><?php echo ((is_array($_tmp=$this->_tpl_vars['datePaid'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %B %Y") : smarty_modifier_date_format($_tmp, "%d %B %Y")); ?>
</td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td style="color:#666;">Status:</td>
                            <td style="text-align:right;">
                                <?php if ($this->_tpl_vars['isPaid']): ?>
                                    <span class="status-paid">PAID</span>
                                <?php else: ?>
                                    <span class="status-unpaid">UNPAID</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</section>

<section>
        <div class="wi-section-title">Article Details</div>
    <table class="wi-details-table" width="100%">
        <tr>
            <td>Title</td>
            <td><?php if ($this->_tpl_vars['articleTitle']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['articleTitle'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
<?php else: ?>Judul naskah belum terhubung<?php endif; ?></td>
        </tr>
        <tr>
            <td>Fee Type</td>
            <td style="font-style: italic; color: #555;"><?php echo ((is_array($_tmp=$this->_tpl_vars['localizedFeeName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
        </tr>
        <?php if ($this->_tpl_vars['paymentMethod']): ?>
        <tr>
            <td>Payment Via</td>
            <td><?php echo ((is_array($_tmp=$this->_tpl_vars['paymentMethod'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
        </tr>
        <?php endif; ?>
    </table>
</section>

<section>
        <div class="wi-section-title">Fees Breakdown</div>
    <table class="wi-fees-table" width="100%">
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

    <table class="wi-total-box" width="100%">
        <tr>
            <td class="wi-total-label">Amount Due</td>
            <td class="wi-total-amount"><?php echo $this->_tpl_vars['currencyCode']; ?>
 <?php echo $this->_tpl_vars['formattedAmount']; ?>
</td>
        </tr>
    </table>
    <div style="font-size: 9px; color: #888; text-align: center; margin-top: 4px;">
        * Taxable Amount calculated after stated trade discounts. Tax/VAT applied per Indonesian VAT Law and OECD Guidelines.
    </div>
</section>

<section>
<div style="margin-top: 15px; font-weight: bold; font-size: 10pt;">
    Transactions Confirmation
</div>
<table class="item-table" style="margin-top: 5px; font-size: 10pt;">
    <tr>
        <th width="20%">Date</th>
        <th width="50%">Transaction ID</th>
        <th width="10%">Gateway</th>
        <th width="20%">Amount</th>
    </tr>
    <?php if ($this->_tpl_vars['isPaid']): ?>
    <tr class="trasaction">
        <td class="text-center"><?php echo ((is_array($_tmp=$this->_tpl_vars['datePaid'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %B %Y") : smarty_modifier_date_format($_tmp, "%d %B %Y")); ?>
</td>
        <td class="text-center">TXN-<?php echo $this->_tpl_vars['invoice']->getInvoiceId(); ?>
</td>
        <td class="text-center"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['paymentMethod'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)); ?>
</td>
        <td class="text-right"><?php echo $this->_tpl_vars['currencyCode']; ?>
 <?php echo $this->_tpl_vars['formattedAmount']; ?>
</td>
    </tr>
    <tr colspan="2">
        <td width="20%"></td>
        <td width="50%"></td>
        <td width="20%" class="balance text-center font-bold">Balance Due</td>
        <td width="10%" class="balance text-right font-bold">-</td>
    </tr>
    <?php else: ?>
    <tr class="trasaction">
        <td class="text-center">-</td>
        <td class="text-center">-</td>
        <td class="text-center">-</td>
        <td class="text-center">-</td>
    </tr>
    <tr>
        <td width="20%"></td>
        <td width="50%"></td>
        <td width="20%" class="balance text-center font-bold">Balance Due</td>
        <td width="10%" class="balance text-right font-bold"><?php echo $this->_tpl_vars['currencyCode']; ?>
 <?php echo $this->_tpl_vars['formattedAmount']; ?>
</td>
    </tr>
    <?php endif; ?>
</table>
</section>

<section>
        <table width="100%" style="margin-top: 20px;">
        <tr>
            <td style="vertical-align: top; width: 70%;">
                <div class="wi-notes-box">
                    <strong>Manual Payment Instructions</strong><br><br>
                    Pembayaran manual dapat dilakukan melalui Rekening:<br>
                    Rekening Nomor: <strong>3273 0669 7</strong> (Bank BNI) a.n. Rochmady<br>
                    Rekening Nomor: <strong>3419 0101 0494 506</strong> (Bank BRI) a.n. Rochmady<br><br>
                    <em>Konfirmasi dan Bukti transfer dikirim ke: rochmady@sangia.org<br>
                    Subjek email: <?php echo ((is_array($_tmp=$this->_tpl_vars['wizdamInvoiceCode'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</em>
                </div>
            </td>
            <td style="vertical-align: top; text-align: center; padding-left: 15px;">
                <?php if ($this->_tpl_vars['qrCodeBase64']): ?>
                <div class="wi-qr-section">
                                        <img src="<?php echo $this->_tpl_vars['qrCodeBase64']; ?>
" height="120" width="120" alt="QR Verification" >
                    <p>Scan untuk verifikasi<br>keaslian dokumen</p>
                </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</section>

<footer>
        <div class="wi-footer">
        Document generated automatically by Wizdam Frontedge &bull;
        <?php echo ((is_array($_tmp=$this->_tpl_vars['wizdamInvoiceNumber'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 &bull;
        This document is digitally signed and verified.
    </div>
</footer>

</body>
</html>