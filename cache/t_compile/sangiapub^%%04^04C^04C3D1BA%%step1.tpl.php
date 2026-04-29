<?php /* Smarty version 2.6.26, created on 2026-04-14 13:02:25
         compiled from manager/setup/step1.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'manager/setup/step1.tpl', 14, false),array('function', 'fieldLabel', 'manager/setup/step1.tpl', 22, false),array('function', 'form_language_chooser', 'manager/setup/step1.tpl', 25, false),array('function', 'translate', 'manager/setup/step1.tpl', 26, false),array('function', 'html_options', 'manager/setup/step1.tpl', 71, false),array('modifier', 'escape', 'manager/setup/step1.tpl', 16, false),array('modifier', 'assign', 'manager/setup/step1.tpl', 24, false),)), $this); ?>
<?php $this->assign('pageTitle', "manager.setup.gettingDownTheDetails"); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "manager/setup/setupHeader.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<form id="setupForm" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'setup','path' => '1'), $this);?>
">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<input type="hidden" name="csrfToken" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">

<?php if (count ( $this->_tpl_vars['formLocales'] ) > 1): ?>
<div id="locales">
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'formLocale','key' => "form.formLanguage"), $this);?>
</td>
		<td width="80%" class="value">
			<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'setup','path' => '1','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'setupFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'setupFormUrl'));?>

			<?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'setupForm','url' => $this->_tpl_vars['setupFormUrl']), $this);?>

			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</span>
		</td>
	</tr>
</table>
</div>
<?php endif; ?>
<div id="generalInformation" class="block">
<h3>1.1 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.generalInformation"), $this);?>
</h3>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'title','required' => 'true','key' => "manager.setup.journalTitle"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="title[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="title" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['title'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="120" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'initials','required' => 'true','key' => "manager.setup.journalInitials"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="initials[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="initials" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['initials'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="8" maxlength="16" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'abbreviation','key' => "manager.setup.journalAbbreviation"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="abbreviation[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="abbreviation" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['abbreviation'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="120" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'printIssn','key' => "manager.setup.printIssn"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="printIssn" id="printIssn" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['printIssn'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="9" maxlength="16" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'onlineIssn','key' => "manager.setup.onlineIssn"), $this);?>
</td>
		<td width="80%" class="value">
			<input type="text" name="onlineIssn" id="onlineIssn" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['onlineIssn'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="9" maxlength="16" class="textField" />
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.issnDescription"), $this);?>
</span>
		</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'mailingAddress','key' => "common.mailingAddress"), $this);?>
</td>
		<td width="80%" class="value">
			<textarea name="mailingAddress" id="mailingAddress" rows="3" cols="40" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['mailingAddress'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.mailingAddressDescription"), $this);?>
</span>
		</td>
	</tr>
	<?php if ($this->_tpl_vars['categoriesEnabled']): ?>
		<tr valign="top">
			<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'categories','key' => "manager.setup.categories"), $this);?>
</td>
			<td class="value">
				<select id="categories" name="categories[]" class="selectMenu" multiple="multiple">
					<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['allCategories'],'selected' => $this->_tpl_vars['categories']), $this);?>

				</select>
				<br/>
				<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.categories.description"), $this);?>
</span>
			</td>
		</tr>
	<?php endif; ?></table>
</div>

<div class="separator"></div>

<div id="principalContact" class="block">
<h3>1.2 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.principalContact"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.principalContactDescription"), $this);?>
</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'contactName','key' => "user.name",'required' => 'true'), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="contactName" id="contactName" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['contactName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="30" maxlength="60" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'contactTitle','key' => "user.title"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="contactTitle[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="contactTitle" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['contactTitle'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="30" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'contactAffiliation','key' => "user.affiliation"), $this);?>
</td>
		<td width="80%" class="value"><textarea name="contactAffiliation[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="contactAffiliation" rows="5" cols="40" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['contactAffiliation'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'contactEmail','key' => "user.email",'required' => 'true'), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="contactEmail" id="contactEmail" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['contactEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="30" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'contactPhone','key' => "user.phone"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="contactPhone" id="contactPhone" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['contactPhone'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="15" maxlength="24" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'contactFax','key' => "user.fax"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="contactFax" id="contactFax" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['contactFax'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="15" maxlength="24" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'contactMailingAddress','key' => "common.mailingAddress"), $this);?>
</td>
		<td width="80%" class="value"><textarea name="contactMailingAddress[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="contactMailingAddress" rows="3" cols="40" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['contactMailingAddress'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
	</tr>
</table>
</div>

<div class="separator"></div>

<div id="technicalSupportContact" class="block">
<h3>1.3 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.technicalSupportContact"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.technicalSupportContactDescription"), $this);?>
</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'supportName','key' => "user.name",'required' => 'true'), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="supportName" id="supportName" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['supportName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="30" maxlength="60" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'supportEmail','key' => "user.email",'required' => 'true'), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="supportEmail" id="supportEmail" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['supportEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="30" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'supportPhone','key' => "user.phone"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="supportPhone" id="supportPhone" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['supportPhone'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="15" maxlength="24" class="textField" /></td>
	</tr>
</table>
</div>
<div class="separator"></div>
<div id="setupEmails" class="block">
<h3>1.4 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.emails"), $this);?>
</h3>
<table width="100%" class="data">
	<tr valign="top"><td colspan="2"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.emailHeaderDescription"), $this);?>
<br />&nbsp;</td></tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'emailHeader','key' => "manager.setup.emailHeader"), $this);?>
</td>
		<td class="value">
			<textarea name="emailHeader" id="emailHeader" rows="3" cols="60" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['emailHeader'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
		</td>
	</tr>
	<tr valign="top"><td colspan="2"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.emailSignatureDescription"), $this);?>
<br />&nbsp;</td></tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'emailSignature','key' => "manager.setup.emailSignature"), $this);?>
</td>
		<td class="value">
			<textarea name="emailSignature" id="emailSignature" rows="3" cols="60" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['emailSignature'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
		</td>
	</tr>
	<tr valign="top"><td colspan="2">&nbsp;</td></tr>
	<tr valign="top"><td colspan="2"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.emailBounceAddressDescription"), $this);?>
<br />&nbsp;</td></tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'envelopeSender','key' => "manager.setup.emailBounceAddress"), $this);?>
</td>
		<td width="80%" class="value">
			<input type="text" name="envelopeSender" id="envelopeSender" size="40" maxlength="255" class="textField" <?php if (! $this->_tpl_vars['envelopeSenderEnabled']): ?>disabled="disabled" value=""<?php else: ?>value="<?php echo ((is_array($_tmp=$this->_tpl_vars['envelopeSender'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php endif; ?> />
			<?php if (! $this->_tpl_vars['envelopeSenderEnabled']): ?>
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.emailBounceAddressDisabled"), $this);?>
</span>
			<?php endif; ?>
		</td>
	</tr>
</table>
</div>

<div class="separator"></div>
<div id="setupPublisher" class="block">
<h3>1.5 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.publisher"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.publisherDescription"), $this);?>
</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publisherNote','key' => "manager.setup.note"), $this);?>
</td>
		<td width="80%" class="value">
			<textarea name="publisherNote[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="publisherNote" rows="5" cols="40" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['publisherNote'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.publisherNoteDescription"), $this);?>
</span>
		</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publisherInstitution','key' => "manager.setup.institution"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="publisherInstitution" id="publisherInstitution" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['publisherInstitution'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publisherUrl','key' => "common.url"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="publisherUrl" id="publisherUrl" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['publisherUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" class="textField" /></td>
	</tr>
</table>
</div>
<div class="separator"></div>
<div id="sponsors" class="block">
<h3>1.6 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.sponsors"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.sponsorsDescription"), $this);?>
</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'sponsorNote','key' => "manager.setup.note"), $this);?>
</td>
		<td width="80%" class="value">
			<textarea name="sponsorNote[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="sponsorNote" rows="5" cols="40" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['sponsorNote'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.sponsorNoteDescription"), $this);?>
</span>
		</td>
	</tr>
<?php $_from = $this->_tpl_vars['sponsors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sponsors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sponsors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['sponsorId'] => $this->_tpl_vars['sponsor']):
        $this->_foreach['sponsors']['iteration']++;
?>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "sponsors-".($this->_tpl_vars['sponsorId'])."-institution",'key' => "manager.setup.institution"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="sponsors[<?php echo ((is_array($_tmp=$this->_tpl_vars['sponsorId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][institution]" id="sponsors-<?php echo ((is_array($_tmp=$this->_tpl_vars['sponsorId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-institution" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['sponsor']['institution'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="90" class="textField" /><?php if ($this->_foreach['sponsors']['total'] > 1): ?> <input type="submit" name="delSponsor[<?php echo ((is_array($_tmp=$this->_tpl_vars['sponsorId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
" class="button" /><?php endif; ?></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "sponsors-".($this->_tpl_vars['sponsorId'])."-url",'key' => "common.url"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="sponsors[<?php echo ((is_array($_tmp=$this->_tpl_vars['sponsorId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][url]" id="sponsors-<?php echo ((is_array($_tmp=$this->_tpl_vars['sponsorId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-url" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['sponsor']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<?php if (! ($this->_foreach['sponsors']['iteration'] == $this->_foreach['sponsors']['total'])): ?>
	<tr valign="top">
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<?php endif; ?>
<?php endforeach; else: ?>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "sponsors-0-institution",'key' => "manager.setup.institution"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="sponsors[0][institution]" id="sponsors-0-institution" size="40" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "sponsors-0-url",'key' => "common.url"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="sponsors[0][url]" id="sponsors-0-url" size="40" maxlength="255" class="textField" /></td>
	</tr>
<?php endif; unset($_from); ?>
</table>

<p><input type="submit" name="addSponsor" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.addSponsor"), $this);?>
" class="button" /></p>
</div>

<div class="separator"></div>

<div id="contributors" class="block">
<h3>1.7 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.contributors"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.contributorsDescription"), $this);?>
</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'contributorNote','key' => "manager.setup.note"), $this);?>
</td>
		<td width="80%" class="value">
			<textarea name="contributorNote[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="contributorNote" rows="5" cols="40" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['contributorNote'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.contributorNoteDescription"), $this);?>
</span>
		</td>
	</tr>
<?php $_from = $this->_tpl_vars['contributors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['contributors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['contributors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['contributorId'] => $this->_tpl_vars['contributor']):
        $this->_foreach['contributors']['iteration']++;
?>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "contributors-".($this->_tpl_vars['contributorId'])."-name",'key' => "manager.setup.contributor"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="contributors[<?php echo ((is_array($_tmp=$this->_tpl_vars['contributorId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][name]" id="contributors-<?php echo ((is_array($_tmp=$this->_tpl_vars['contributorId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-name" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['contributor']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="90" class="textField" /><?php if ($this->_foreach['contributors']['total'] > 1): ?> <input type="submit" name="delContributor[<?php echo ((is_array($_tmp=$this->_tpl_vars['contributorId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
" class="button" /><?php endif; ?></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "contributors-".($this->_tpl_vars['contributorId'])."-url",'key' => "common.url"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="contributors[<?php echo ((is_array($_tmp=$this->_tpl_vars['contributorId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
][url]" id="contributors-<?php echo ((is_array($_tmp=$this->_tpl_vars['contributorId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
-url" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['contributor']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<?php if (! ($this->_foreach['contributors']['iteration'] == $this->_foreach['contributors']['total'])): ?>
	<tr valign="top">
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<?php endif; ?>
<?php endforeach; else: ?>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "contributors-0-name",'key' => "manager.setup.contributor"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="contributors[0][name]" id="contributors-0-name" size="40" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "contributors-0-url",'key' => "common.url"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="contributors[0][url]" id="contributors-0-url" size="40" maxlength="255" class="textField" /></td>
	</tr>
<?php endif; unset($_from); ?>
</table>

<p><input type="submit" name="addContributor" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.addContributor"), $this);?>
" class="button" /></p>
</div>

<div class="separator"></div>

<div id="searchEngineIndexing" class="block">
<h3>1.8 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.searchEngineIndexing"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.searchEngineIndexingDescription"), $this);?>
</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'searchDescription','key' => "common.description"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="searchDescription[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="searchDescription" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['searchDescription'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'searchKeywords','key' => "common.keywords"), $this);?>
</td>
		<td width="80%" class="value"><input type="text" name="searchKeywords[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="searchKeywords" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['searchKeywords'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'customHeaders','key' => "manager.setup.customTags"), $this);?>
</td>
		<td width="80%" class="value">
			<textarea name="customHeaders[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="customHeaders" rows="3" cols="40" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['customHeaders'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.customTagsDescription"), $this);?>
</span>
		</td>
	</tr>
</table>
</div>

<div class="separator" ></div>

<div class="block">
<h3>1.9 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.history"), $this);?>
</h3>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.historyDescription"), $this);?>
</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'history','key' => "manager.setup.history"), $this);?>
</td>
		<td width="80%" class="value">
			<textarea name="history[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="history" rows="5" cols="40" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['history'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
		</td>
	</tr>
</table>
</div>

<div class="separator"></div>


<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.saveAndContinue"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'setup','escape' => false), $this);?>
'" /></p>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>

</form>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
