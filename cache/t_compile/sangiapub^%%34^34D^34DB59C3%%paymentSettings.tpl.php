<?php /* Smarty version 2.6.26, created on 2026-04-05 04:45:18
         compiled from payments/paymentSettings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'payments/paymentSettings.tpl', 16, false),array('function', 'translate', 'payments/paymentSettings.tpl', 16, false),array('function', 'fieldLabel', 'payments/paymentSettings.tpl', 26, false),array('function', 'form_language_chooser', 'payments/paymentSettings.tpl', 29, false),array('function', 'html_options', 'payments/paymentSettings.tpl', 51, false),array('modifier', 'assign', 'payments/paymentSettings.tpl', 28, false),array('modifier', 'escape', 'payments/paymentSettings.tpl', 72, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "manager.payment.feePaymentOptions"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>

<div id="paymentSettings">
<ul class="menu">
	<li class="current"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'payments'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.options"), $this);?>
</a></li>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'payMethodSettings'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.paymentMethods"), $this);?>
</a></li>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'viewPayments'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.records"), $this);?>
</a></li>
</ul>

<form name="paymentSettingsForm" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'savePaymentSettings'), $this);?>
">
    <?php if (count ( $this->_tpl_vars['formLocales'] ) > 1): ?>
    <div id="locales" class="block">
        <table width="100%" class="settings">
        <tr valign="top">
        	<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'formLocale','key' => "form.formLanguage"), $this);?>
</td>
        	<td width="80%" class="value">
        		<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'payments','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'paymentSettingsFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'paymentSettingsFormUrl'));?>

        		<?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'paymentSettingsForm','url' => $this->_tpl_vars['paymentSettingsFormUrl']), $this);?>

        		<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</span>
        	</td>
        </tr>
        </table>
    </div>
    <?php endif; ?>
    
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <div id="generalOptions" class="block">
        <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.generalOptions"), $this);?>
</h3>
        <table width="100%" class="settings">
    	<tr>
    		<th class="label" width="20%"><input type="checkbox" name="journalPaymentsEnabled" id="journalPaymentsEnabled" value="1"<?php if ($this->_tpl_vars['journalPaymentsEnabled']): ?> checked="checked"<?php endif; ?> /></th>
        		<th>Enable Payments</th>
    	</tr>
    	<tr>
    		<td width="20%"></td>
    		<td class="value" width="80%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'journalPaymentsEnabled','key' => "manager.payment.options.enablePayments"), $this);?>
</td>
    	</tr>
    	<tr>
    		<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'currency','key' => "manager.payment.currency"), $this);?>
</td>
    		<td class="value" width="80%"><select name="currency" id="currency" class="selectMenu"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['validCurrencies'],'selected' => $this->_tpl_vars['currency']), $this);?>
</select></td>
    	</tr>
    	<tr>
    		<td width="20%"></td>
    		<td width="80%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.currencymessage"), $this);?>
</td>
    	</tr>
        </table>
    </div>
    
    <div id="tax-discount" class="block">
        <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.taxDiscount"), $this);?>
</h3>
        <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.taxDiscount.description"), $this);?>
</p>
                <table width="100%" class="settings">
        <thead>
            <th width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.taxDiscount"), $this);?>
</th>
            <th width="80%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.taxDiscount.description"), $this);?>
</th>
        </thead>
        <tr>
            <td class="label" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.taxRate"), $this);?>
</td>
            <td class="value" width="80%">
                <input type="text" class="textField" name="paymentTax" id="paymentTax" size="10" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['paymentTax'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
                <br />
                <input type="checkbox" name="paymentTaxInclusive" id="paymentTaxInclusive" value="1" <?php if ($this->_tpl_vars['paymentTaxInclusive']): ?>checked="checked"<?php endif; ?> />
                <label for="paymentTaxInclusive"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.taxInclusive"), $this);?>
</label>
                <span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.taxRate.instruct"), $this);?>
</span>
            </td>
        </tr>
        <tr>
            <td class="label" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.discountNominal"), $this);?>
</td>
            <td class="value" width="80%">
                <input type="text" class="textField" name="paymentDiscount" id="paymentDiscount" size="20" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['paymentDiscount'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
                <span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.discount.instruct"), $this);?>
</span>
            </td>
        </tr>
        </table>
            </div>
    
    <div id="authorFees" class="block">
        <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.authorFees"), $this);?>
</h3>
        <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.authorFeesDescription"), $this);?>
</p>
        <table width="100%" class="settings">
        <tr>
        	<th width="20%"><input type="checkbox" name="submissionFeeEnabled" id="submissionFeeEnabled" value="1"<?php if ($this->_tpl_vars['submissionFeeEnabled']): ?> checked="checked"<?php endif; ?> /></th>
        	<th width="80%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'submissionFeeEnabled','key' => "manager.payment.options.submissionFee"), $this);?>
</th>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'submissionFee','key' => "manager.payment.options.fee"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="submissionFee" id="submissionFee" size="10" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['submissionFee'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'submissionFeeName','key' => "manager.payment.options.feeName"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="submissionFeeName[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="submissionFeeName" size="50" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['submissionFeeName'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'submissionFeeDescription','key' => "manager.payment.options.feeDescription"), $this);?>
</td>
        	<td class="value" width="80%"><textarea class="textArea" name="submissionFeeDescription[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="submissionFeeDescription" rows="2" cols="50"><?php echo ((is_array($_tmp=$this->_tpl_vars['submissionFeeDescription'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
        </tr>
        </table>
    
        <table width="100%" class="settings">
        <tr>
        	<th width="20%"><input type="checkbox" name="fastTrackFeeEnabled" id="fastTrackFeeEnabled" value="1"<?php if ($this->_tpl_vars['fastTrackFeeEnabled']): ?> checked="checked"<?php endif; ?> /></th>
        	<th width="80%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'fastTrackFeeEnabled','key' => "manager.payment.options.fastTrackFee"), $this);?>
</th>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'fastTrackFee','key' => "manager.payment.options.fee"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="fastTrackFee" id="fastTrackFee" size="10" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['fastTrackFee'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'fastTrackFeeName','key' => "manager.payment.options.feeName"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="fastTrackFeeName[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="fastTrackFeeName" size="50" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['fastTrackFeeName'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'fastTrackFeeDescription','key' => "manager.payment.options.feeDescription"), $this);?>
</td>
        	<td class="value" width="80%"><textarea class="textArea" name="fastTrackFeeDescription[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="fastTrackFeeDescription" rows="2" cols="50"><?php echo ((is_array($_tmp=$this->_tpl_vars['fastTrackFeeDescription'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
        </tr>
        </table>
    
        <table width="100%" class="settings">
        <tr>
        	<th width="20%"><input type="checkbox" name="publicationFeeEnabled" id="publicationFeeEnabled" value="1"<?php if ($this->_tpl_vars['publicationFeeEnabled']): ?> checked="checked"<?php endif; ?> /></th>
        	<th width="80%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publicationFeeEnabled','key' => "manager.payment.options.publicationFee"), $this);?>
</th>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publicationFee','key' => "manager.payment.options.fee"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="publicationFee" id="publicationFee" size="10" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['publicationFee'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publicationFeeName','key' => "manager.payment.options.feeName"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="publicationFeeName[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="publicationFeeName" size="50" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['publicationFeeName'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publicationFeeDescription','key' => "manager.payment.options.feeDescription"), $this);?>
</td>
        	<td class="value" width="80%"><textarea class="textArea" name="publicationFeeDescription[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="publicationFeeDescription" rows="2" cols="50"><?php echo ((is_array($_tmp=$this->_tpl_vars['publicationFeeDescription'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'waiverPolicy','key' => "manager.payment.options.waiverPolicy"), $this);?>
</td>
        	<td class="value" width="80%"><textarea class="textArea" name="waiverPolicy[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="waiverPolicy" rows="2" cols="50"><?php echo ((is_array($_tmp=$this->_tpl_vars['waiverPolicy'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
        </tr>
        </table>
    </div>
    
    <div id="readerFees" class="block">
        <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.readerFees"), $this);?>
</h3>
        <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.readerFeesDescription"), $this);?>
</p>
        
        <table width="100%" class="settings">
        <tr>
        	<th class="value" width="20%"><input type="checkbox" name="acceptSubscriptionPayments" id="acceptSubscriptionPayments" value="1"<?php if ($this->_tpl_vars['acceptSubscriptionPayments']): ?> checked="checked"<?php endif; ?> /></th>
        	<th>Reader Subscriptions</th>
        </tr>
        <tr>
        	<td width="20%"></td>
        	<td width="80%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'acceptSubscriptionPayments','key' => "manager.payment.options.acceptSubscriptionPayments"), $this);?>
</td>
        
        </table>
    
        <table width="100%" class="settings">
        <tr>
        	<th width="20%"><input type="checkbox" name="purchaseIssueFeeEnabled" id="purchaseIssueFeeEnabled" value="1"<?php if ($this->_tpl_vars['purchaseIssueFeeEnabled']): ?> checked="checked"<?php endif; ?> /></th>
        	<th width="80%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'purchaseIssueFeeEnabled','key' => "manager.payment.options.purchaseIssueFee"), $this);?>
</th>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'purchaseIssueFee','key' => "manager.payment.options.fee"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="purchaseIssueFee" id="purchaseIssueFee" size="10" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['purchaseIssueFee'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'purchaseIssueFeeName','key' => "manager.payment.options.feeName"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="purchaseIssueFeeName[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="purchaseIssueFeeName" size="50" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['purchaseIssueFeeName'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'purchaseIssueFeeDescription','key' => "manager.payment.options.feeDescription"), $this);?>
</td>
        	<td class="value" width="80%"><textarea class="textArea" name="purchaseIssueFeeDescription[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="purchaseIssueFeeDescription" rows="2" cols="50"><?php echo ((is_array($_tmp=$this->_tpl_vars['purchaseIssueFeeDescription'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
        </tr>
        </table>
    
        <table width="100%" class="settings">
        <tr>
        	<th width="20%"><input type="checkbox" name="purchaseArticleFeeEnabled" id="purchaseArticleFeeEnabled" value="1"<?php if ($this->_tpl_vars['purchaseArticleFeeEnabled']): ?> checked="checked"<?php endif; ?> /></th>
        	<th width="80%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'purchaseArticleFeeEnabled','key' => "manager.payment.options.purchaseArticleFee"), $this);?>
</th>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'purchaseArticleFee','key' => "manager.payment.options.fee"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="purchaseArticleFee" id="purchaseArticleFee" size="10" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['purchaseArticleFee'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'purchaseArticleFeeName','key' => "manager.payment.options.feeName"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="purchaseArticleFeeName[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="purchaseArticleFeeName" size="50" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['purchaseArticleFeeName'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'purchaseArticleFeeDescription','key' => "manager.payment.options.feeDescription"), $this);?>
</td>
        	<td class="value" width="80%"><textarea class="textArea" name="purchaseArticleFeeDescription[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="purchaseArticleFeeDescription" rows="2" cols="50"><?php echo ((is_array($_tmp=$this->_tpl_vars['purchaseArticleFeeDescription'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
        </tr>
        <tr>
        	<td class="value" width="20%"><input type="checkbox" name="restrictOnlyPdf" id="restrictOnlyPdf" value="1"<?php if ($this->_tpl_vars['restrictOnlyPdf']): ?> checked="checked"<?php endif; ?> /></td>
        	<td width="80%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'restrictOnlyPdf','key' => "manager.payment.options.onlypdf"), $this);?>
</td>
        </tr>
        </table>
    </div>
    
    <div id="generalFees" class="block">
        <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.generalFees"), $this);?>
</h3>
        <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.generalFeesDescription"), $this);?>
</p>
        
        <table width="100%" class="settings">
        <tr>
        	<th width="20%"><input type="checkbox" name="membershipFeeEnabled" id="membershipFeeEnabled" value="1"<?php if ($this->_tpl_vars['membershipFeeEnabled']): ?> checked="checked"<?php endif; ?> /></th>
        	<th width="80%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'membershipFeeEnabled','key' => "manager.payment.options.membershipFee"), $this);?>
</th>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'membershipFee','key' => "manager.payment.options.fee"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="membershipFee" id="membershipFee" size="10" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['membershipFee'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'membershipFeeName','key' => "manager.payment.options.feeName"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="membershipFeeName[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="membershipFeeName" size="50" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['membershipFeeName'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'membershipFeeDescription','key' => "manager.payment.options.feeDescription"), $this);?>
</td>
        	<td class="value" width="80%"><textarea class="textArea" name="membershipFeeDescription[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="membershipFeeDescription" rows="2" cols="50"><?php echo ((is_array($_tmp=$this->_tpl_vars['membershipFeeDescription'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
        </tr>
        </table>
    
        <table width="100%" class="settings">
        <tr>
        	<th width="20%"><input type="checkbox" name="donationFeeEnabled" id="donationFeeEnabled" value="1"<?php if ($this->_tpl_vars['donationFeeEnabled']): ?> checked="checked"<?php endif; ?> /></th>
        	<th width="80%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'donationFeeEnabled','key' => "manager.payment.options.donationFee"), $this);?>
</th>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'donationFeeName','key' => "manager.payment.options.feeName"), $this);?>
</td>
        	<td class="value" width="80%"><input type="text" class="textField" name="donationFeeName[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="donationFeeName" size="50" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['donationFeeName'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" /></td>
        </tr>
        <tr>
        	<td class="label" width="20%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'donationFeeDescription','key' => "manager.payment.options.feeDescription"), $this);?>
</td>
        	<td class="value" width="80%"><textarea class="textArea" name="donationFeeDescription[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="donationFeeDescription" rows="2" cols="50"><?php echo ((is_array($_tmp=$this->_tpl_vars['donationFeeDescription'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
        </tr>
        </table>
    </div>
    
    <div id="gifts" class="block">
        <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.giftFees"), $this);?>
</h3>
        <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.payment.giftFeesDescription"), $this);?>
</p>
        
        <table width="100%" class="settings">
        <tr>
        	<th width="20%"><input type="checkbox" name="acceptGiftSubscriptionPayments" id="acceptGiftSubscriptionPayments" value="1"<?php if ($this->_tpl_vars['acceptGiftSubscriptionPayments']): ?> checked="checked"<?php endif; ?> /></th>
        	<th>Gift Subscriptions</th>
        <tr>
        	<td class="value" width="20%"></td>
        	<td width="80%"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'acceptGiftSubscriptionPayments','key' => "manager.payment.options.acceptGiftSubscriptionPayments"), $this);?>
</td>
        </tr>
        </table>
    </div>
    
    <p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'manager'), $this);?>
'" /></p>
</form>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>