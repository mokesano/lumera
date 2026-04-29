<?php /* Smarty version 2.6.26, created on 2026-04-05 23:48:13
         compiled from admin/settings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'admin/settings.tpl', 17, false),array('function', 'fieldLabel', 'admin/settings.tpl', 25, false),array('function', 'form_language_chooser', 'admin/settings.tpl', 30, false),array('function', 'translate', 'admin/settings.tpl', 31, false),array('function', 'html_options', 'admin/settings.tpl', 92, false),array('modifier', 'escape', 'admin/settings.tpl', 21, false),array('modifier', 'assign', 'admin/settings.tpl', 29, false),array('modifier', 'date_format', 'admin/settings.tpl', 56, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "admin.siteSettings"); ?><?php echo ''; ?><?php $this->assign('pageDisplayed', 'site'); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<form id="settings" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'saveSettings'), $this);?>
" enctype="multipart/form-data">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
            
<?php if (count ( $this->_tpl_vars['formLocales'] ) > 1): ?>
<div class="card-info">
    <h4><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'formLocale','key' => "form.formLanguage"), $this);?>
</h4>
    <table class="data" width="100%">
    	<tr valign="top">
    		<td colspan="2" width="80%" class="value">
    			<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'settings','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'settingsUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'settingsUrl'));?>

    			<?php echo $this->_plugins['function']['form_language_chooser'][0][0]->smartyFormLanguageChooser(array('form' => 'settings','url' => $this->_tpl_vars['settingsUrl']), $this);?>

    			<span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.formLanguage.description"), $this);?>
</span>
    		</td>
    	</tr>
    </table>
</div>
<?php endif; ?>

<div class="card-info">
    <h4 <?php if ($this->_tpl_vars['pageHeaderTitleType'][$this->_tpl_vars['formLocale']] && $this->_tpl_vars['pageHeaderTitleImage'][$this->_tpl_vars['formLocale']]): ?>rowspan="4"<?php else: ?>rowspan="3"<?php endif; ?>  class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'title','key' => "admin.settings.siteTitle",'required' => 'true'), $this);?>
</h4>
    <table class="data" width="100%">
    	<tr valign="top">
    		<td width="15%" class="value">
    			<input type="radio" name="pageHeaderTitleType[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="pageHeaderTitleType-0" value="0"<?php if (! $this->_tpl_vars['pageHeaderTitleType'][$this->_tpl_vars['formLocale']]): ?> checked="checked"<?php endif; ?> /> <?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "pageHeaderTitleType-0",'key' => "manager.setup.useTextTitle"), $this);?>

    		</td>
    		<td width="65%" class="value">
    			<input type="text" id="title" name="title[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['title'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="120" class="textField" />
    		</td>
    	</tr>
    	<tr valign="top">
    		<td class="label" width="20%"><input type="radio" name="pageHeaderTitleType[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="pageHeaderTitleType-1" value="1"<?php if ($this->_tpl_vars['pageHeaderTitleType'][$this->_tpl_vars['formLocale']]): ?> checked="checked"<?php endif; ?> /> <?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => "pageHeaderTitleType-1",'key' => "manager.setup.useImageTitle"), $this);?>
</td>
    		<td colspan="2" width="80%" class="value"><input type="file" name="pageHeaderTitleImage" class="uploadField" /> <input type="submit" name="uploadPageHeaderTitleImage" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.upload"), $this);?>
" class="button" /></td>
    	</tr>
    	<tr valign="top">
    		<td colspan="2">
    			<?php if ($this->_tpl_vars['pageHeaderTitleType'][$this->_tpl_vars['formLocale']] && $this->_tpl_vars['pageHeaderTitleImage'][$this->_tpl_vars['formLocale']]): ?>
    				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.fileName"), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['pageHeaderTitleImage'][$this->_tpl_vars['formLocale']]['originalFilename'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['pageHeaderTitleImage'][$this->_tpl_vars['formLocale']]['dateUploaded'])) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatShort'])); ?>
 <input type="submit" name="deletePageHeaderTitleImage" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
" class="button" />
    				<br />
    				<img src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['pageHeaderTitleImage'][$this->_tpl_vars['formLocale']]['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" width="<?php echo ((is_array($_tmp=$this->_tpl_vars['pageHeaderTitleImage'][$this->_tpl_vars['formLocale']]['width'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" height="<?php echo ((is_array($_tmp=$this->_tpl_vars['pageHeaderTitleImage'][$this->_tpl_vars['formLocale']]['height'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" style="border: 0; width: 100%; height: auto;" alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.homeHeaderImage.altText"), $this);?>
" />
    			<?php endif; ?>
    		</td>
    	</tr>
    	<?php if ($this->_tpl_vars['pageHeaderTitleType'][$this->_tpl_vars['formLocale']] && $this->_tpl_vars['pageHeaderTitleImage'][$this->_tpl_vars['formLocale']]): ?>
    		<tr valign="top">
    			<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'pageHeaderTitleImageAltText','key' => "common.altText"), $this);?>
</td>
    			<td colspan="2" width="80%" class="value">
    				<input type="text" id="pageHeaderTitleImageAltText" name="pageHeaderTitleImageAltText[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['pageHeaderTitleImage'][$this->_tpl_vars['formLocale']]['altText'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="255" class="textField" />
    			</td>
    		</tr>
    		<tr valign="top">
    			<td colspan="2" class="value"><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.altTextInstructions"), $this);?>
</span></td>
    		</tr>
    	<?php endif; ?>
	</table>
</div>

<div class="card-info">
    <h4 class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'intro','key' => "admin.settings.introduction"), $this);?>
</h4>
    <table class="data" width="100%">
    	<tr valign="top">
    		<td colspan="2" class="value"><textarea name="intro[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="intro" cols="40" rows="10" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['intro'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
    	</tr>
	</table>
</div>

<div class="card-info">
    <h4 class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'redirect','key' => "admin.settings.journalRedirect"), $this);?>
</h4>
    <table class="data" width="100%">
    	<tr valign="top">
    		<td colspan="2" class="value">
    			<select name="redirect" id="redirect" size="1" class="selectMenu">
    				<option value=""><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.noJournalRedirect"), $this);?>
</option>
    				<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['redirectOptions'],'selected' => $this->_tpl_vars['redirect']), $this);?>

    			</select>
    		</td>
    	</tr>
    	<tr valign="top">
    		<td colspan="2" class="value"><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.journalRedirectInstructions"), $this);?>
</span></td>
    	</tr>
	</table>
</div>

<div class="card-info">
    <h4 class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'aboutField','key' => "admin.settings.aboutDescription"), $this);?>
</h4>
    <table class="data" width="100%">
    	<tr valign="top">
    		<td colspan="2" class="value"><textarea name="about[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="aboutField" cols="40" rows="10" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['about'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
    	</tr>
	</table>
</div>

<table class="data" width="100%">
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'contactName','key' => "admin.settings.contactName",'required' => 'true'), $this);?>
</td>
		<td colspan="2" class="value"><input type="text" id="contactName" name="contactName[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['contactName'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'contactEmail','key' => "admin.settings.contactEmail",'required' => 'true'), $this);?>
</td>
		<td colspan="2" class="value"><input type="text" id="contactEmail" name="contactEmail[<?php echo ((is_array($_tmp=$this->_tpl_vars['formLocale'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['contactEmail'][$this->_tpl_vars['formLocale']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="40" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'minPasswordLength','key' => "admin.settings.minPasswordLength",'required' => 'true'), $this);?>
</td>
		<td colspan="2" class="value"><input type="text" id="minPasswordLength" name="minPasswordLength" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['minPasswordLength'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="4" maxlength="2" class="textField" /> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.passwordCharacters"), $this);?>
</td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'oneStepReset','key' => "admin.settings.oneStepReset"), $this);?>
</td>
		<td class="value" colspan="2"><input type="checkbox" id="oneStepReset" name="oneStepReset" <?php if ($this->_tpl_vars['oneStepReset']): ?>checked="checked" <?php endif; ?>/></td>
	</tr>
	<tr>
		<td class="label"><label for="journalTheme"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.siteTheme"), $this);?>
</label></td>
		<td colspan="2" class="value">
			<select name="siteTheme" class="selectMenu" id="theme"<?php if (empty ( $this->_tpl_vars['themes'] )): ?> disabled="disabled"<?php endif; ?>>
				<option value=""><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.none"), $this);?>
</option>
				<?php $_from = $this->_tpl_vars['themes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['path'] => $this->_tpl_vars['themePlugin']):
?>
					<option value="<?php echo ((is_array($_tmp=$this->_tpl_vars['path'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if ($this->_tpl_vars['path'] == $this->_tpl_vars['siteTheme']): ?> selected="selected"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['themePlugin']->getDisplayName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</option>
				<?php endforeach; endif; unset($_from); ?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.siteStyleSheet"), $this);?>
</td>
		<td colspan="2" width="80%" class="value">
			<input type="file" name="siteStyleSheet" class="uploadField" /> <input type="submit" name="uploadSiteStyleSheet" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.upload"), $this);?>
" class="button" />
			<?php if ($this->_tpl_vars['siteStyleFileExists']): ?>
				<br />
				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.fileName"), $this);?>
: <a href="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo $this->_tpl_vars['styleFilename']; ?>
" class="file"><?php echo ((is_array($_tmp=$this->_tpl_vars['originalStyleFilename'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a> <?php echo ((is_array($_tmp=$this->_tpl_vars['dateStyleFileUploaded'])) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatShort'])); ?>
 <input type="submit" name="deleteSiteStyleSheet" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
" class="button" />
			<?php endif; ?>
		</td>
	</tr>
	<tr valign="top">
		<td rowspan="2" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.options"), $this);?>
</td>
		<td class="value" colspan="2">
			<input type="checkbox" id="useAlphalist" name="useAlphalist" <?php if ($this->_tpl_vars['useAlphalist']): ?>checked="checked" <?php endif; ?>/>
			<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'useAlphalist','key' => "admin.settings.useAlphalist"), $this);?>

	</tr>
	<tr valign="top">
		<td class="value" colspan="2">
			<input type="checkbox" id="usePaging" name="usePaging" <?php if ($this->_tpl_vars['usePaging']): ?>checked="checked" <?php endif; ?>/>
			<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'usePaging','key' => "admin.settings.usePaging"), $this);?>

		</td>
	</tr>
	<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.journalsList"), $this);?>
</td>
		<td colspan="2" class="value">
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.journalsList.description"), $this);?>
<br />
			<input type="checkbox" name="showThumbnail" id="showThumbnail" value="1"<?php if ($this->_tpl_vars['showThumbnail']): ?> checked="checked"<?php endif; ?> />
			<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'showThumbnail','key' => "admin.settings.journalsList.showThumbnail"), $this);?>
<br />
			<input type="checkbox" name="showTitle" id="showTitle" value="1"<?php if ($this->_tpl_vars['showTitle']): ?> checked="checked"<?php endif; ?> />
			<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'showTitle','key' => "admin.settings.journalsList.showTitle"), $this);?>
<br />
			<input type="checkbox" name="showDescription" id="showDescription" value="1"<?php if ($this->_tpl_vars['showDescription']): ?> checked="checked"<?php endif; ?> />
			<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'showDescription','key' => "admin.settings.journalsList.showDescription"), $this);?>
<br />
		</td>
	</tr>
	<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.security"), $this);?>
</td>
		<td colspan="2" class="value">
			<input type="checkbox" name="preventManagerPluginManagement" id="preventManagerPluginManagement" value="1"<?php if ($this->_tpl_vars['preventManagerPluginManagement']): ?> checked="checked"<?php endif; ?> />
			<?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'preventManagerPluginManagement','key' => "admin.settings.security.plugins"), $this);?>
<br />
		</td>
	</tr>
</table>

<br />
<div id="oaiRegistration">
	<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.oaiRegistration"), $this);?>
</h4>

	<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'oai'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'oaiUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'oaiUrl'));?>

	<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'index'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'siteUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'siteUrl'));?>

	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.oaiRegistrationDescription",'siteUrl' => $this->_tpl_vars['siteUrl'],'oaiUrl' => $this->_tpl_vars['oaiUrl']), $this);?>
</p>
</div>

<?php if (count ( $this->_tpl_vars['availableMetricTypes'] ) > 2): ?>
	<br />
	<div id="defaultMetricSelection">
		<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "defaultMetric.title"), $this);?>
</h4>
		<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.settings.defaultMetricDescription"), $this);?>
</p>
		<table class="data" width="100%">
			<tr valign="top">
				<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'defaultMetricType','key' => "defaultMetric.availableMetrics"), $this);?>
</td>
				<td colspan="2" width="80%" class="value">
					<select name="defaultMetricType" class="selectMenu" id="defaultMetricType">
						<?php $_from = $this->_tpl_vars['availableMetricTypes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['metricType'] => $this->_tpl_vars['displayName']):
?>
							<option value="<?php echo ((is_array($_tmp=$this->_tpl_vars['metricType'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if ($this->_tpl_vars['metricType'] == $this->_tpl_vars['defaultMetricType']): ?> selected="selected"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['displayName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</option>
						<?php endforeach; endif; unset($_from); ?>
					</select>
				</td>
			</tr>
		</table>
	</div>
<?php endif; ?>

<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'admin','escape' => false), $this);?>
'" /></p>

</form>

<p><span class="formRequired"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.requiredField"), $this);?>
</span></p>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
