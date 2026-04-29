<?php /* Smarty version 2.6.26, created on 2026-04-16 16:32:50
         compiled from author/submit/authorFees.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'author/submit/authorFees.tpl', 13, false),array('function', 'url', 'author/submit/authorFees.tpl', 24, false),array('modifier', 'escape', 'author/submit/authorFees.tpl', 19, false),array('modifier', 'date_format', 'author/submit/authorFees.tpl', 21, false),array('modifier', 'string_format', 'author/submit/authorFees.tpl', 23, false),array('modifier', 'nl2br', 'author/submit/authorFees.tpl', 27, false),array('modifier', 'number_format', 'author/submit/authorFees.tpl', 37, false),)), $this); ?>
<div id="authorFees" class="pay_Submission_Fee">
    
    <h3 class="u-h4"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.authorFees"), $this);?>
</h3>
    
    <div class="alert-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.authorFeesMessage"), $this);?>
</div>
    
    <?php if ($this->_tpl_vars['currentJournal']->getSetting('submissionFeeEnabled')): ?>
    <div class="u-mb-16">
    	<p class="u-mb-8"><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('submissionFeeName'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
:
    	<?php if ($this->_tpl_vars['submissionPayment']): ?>
    		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.paid"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['submissionPayment']->getTimestamp())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatLong']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatLong'])); ?>

    	<?php else: ?>
    		<span class="bold"><?php echo $this->_tpl_vars['currentJournal']->getSetting('currency'); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('submissionFee'))) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.2f") : smarty_modifier_string_format($_tmp, "%.2f")); ?>
</span>
    		<?php if ($this->_tpl_vars['showPayLinks']): ?><a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'paySubmissionFee','path' => $this->_tpl_vars['articleId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.payNow"), $this);?>
</a><?php endif; ?>
    	<?php endif; ?>
    	</p>
    	<p><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('submissionFeeDescription'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
    </div>
    <?php endif; ?>
    
    <?php if ($this->_tpl_vars['currentJournal']->getSetting('fastTrackFeeEnabled')): ?>
    <div class="u-mb-16">
    	<p class="u-mb-8"><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('fastTrackFeeName'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
: 
    	<?php if ($this->_tpl_vars['fastTrackPayment']): ?>
    		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.paid"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['fastTrackPayment']->getTimestamp())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatLong']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatLong'])); ?>

    	<?php else: ?>
    		<span class="bold"><?php echo $this->_tpl_vars['currentJournal']->getSetting('currency'); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('fastTrackFee'))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2, '.', ',') : number_format($_tmp, 2, '.', ',')); ?>
</span>
    		<?php if ($this->_tpl_vars['showPayLinks']): ?><a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'payFastTrackFee','path' => $this->_tpl_vars['articleId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.payNow"), $this);?>
</a><?php endif; ?>
    	<?php endif; ?>
    	</p>
    	<p><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('fastTrackFeeDescription'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
    </div>
    <?php endif; ?>
    
    <?php if ($this->_tpl_vars['currentJournal']->getSetting('publicationFeeEnabled')): ?>
    <div class="u-mb-16">
    	<p class="u-mb-8"><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('publicationFeeName'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
: <span class="bold"><?php echo $this->_tpl_vars['currentJournal']->getSetting('currency'); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publicationFee'))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2, '.', ',') : number_format($_tmp, 2, '.', ',')); ?>
</span></p>
    	<p><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('publicationFeeDescription'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
    </div>
    <?php endif; ?>
    
    <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('waiverPolicy') != ''): ?>
    	<div class="alert-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('waiverPolicy'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</div>
    <?php endif; ?>
    
</div>