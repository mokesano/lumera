<?php /* Smarty version 2.6.26, created on 2026-04-04 14:21:57
         compiled from author/submission/authorFees.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'author/submission/authorFees.tpl', 12, false),array('function', 'url', 'author/submission/authorFees.tpl', 21, false),array('modifier', 'escape', 'author/submission/authorFees.tpl', 16, false),array('modifier', 'date_format', 'author/submission/authorFees.tpl', 18, false),array('modifier', 'string_format', 'author/submission/authorFees.tpl', 20, false),)), $this); ?>
<div id="authorFees">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.authorFees"), $this);?>
</h3>
<table width="100%" class="data">
<?php if ($this->_tpl_vars['currentJournal']->getSetting('submissionFeeEnabled')): ?>
	<tr>
		<td width="20%"><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('submissionFeeName'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
	<?php if ($this->_tpl_vars['submissionPayment']): ?>
		<td width="80%" colspan="2"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.paid"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['submissionPayment']->getTimestamp())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatLong']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatLong'])); ?>
</td>
	<?php else: ?>
		<td width="30%"><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('submissionFee'))) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")); ?>
 <?php echo $this->_tpl_vars['currentJournal']->getSetting('currency'); ?>
</td> 
		<td width="50%"><a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'paySubmissionFee','path' => $this->_tpl_vars['submission']->getId()), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.payNow"), $this);?>
</a></td>
	<?php endif; ?>
	</tr>
<?php endif; ?>
<?php if ($this->_tpl_vars['currentJournal']->getSetting('fastTrackFeeEnabled')): ?>
	<tr>
		<td width="20%"><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('fastTrackFeeName'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
: 
	<?php if ($this->_tpl_vars['fastTrackPayment']): ?>
		<td width="80%" colspan="2"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.paid"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['fastTrackPayment']->getTimestamp())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatLong']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatLong'])); ?>
</td>
	<?php else: ?>
		<td width="30%"><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('fastTrackFee'))) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")); ?>
 <?php echo $this->_tpl_vars['currentJournal']->getSetting('currency'); ?>
</td>
		<td width="50%"><a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'payFastTrackFee','path' => $this->_tpl_vars['submission']->getId()), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.payNow"), $this);?>
</a></td>
	<?php endif; ?>
	</tr>	
<?php endif; ?>
<?php if ($this->_tpl_vars['currentJournal']->getSetting('publicationFeeEnabled')): ?>
	<tr>
		<td width="20%"><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('publicationFeeName'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
	<?php if ($this->_tpl_vars['publicationPayment']): ?>
		<td width="80%" colspan="2"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.paid"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['publicationPayment']->getTimestamp())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatLong']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatLong'])); ?>
</td>
	<?php else: ?>
		<td width="30%"><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publicationFee'))) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")); ?>
 <?php echo $this->_tpl_vars['currentJournal']->getSetting('currency'); ?>
</td>
		<td width="50%"><a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'payPublicationFee','path' => $this->_tpl_vars['submission']->getId()), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.payNow"), $this);?>
</a></td>
	<?php endif; ?>
	</tr>	
<?php endif; ?>
</table>
</div>