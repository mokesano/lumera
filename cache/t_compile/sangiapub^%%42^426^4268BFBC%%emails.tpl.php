<?php /* Smarty version 2.6.26, created on 2026-04-05 05:52:04
         compiled from manager/emails/emails.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'manager/emails/emails.tpl', 19, false),array('function', 'url', 'manager/emails/emails.tpl', 29, false),array('function', 'icon', 'manager/emails/emails.tpl', 30, false),array('function', 'page_info', 'manager/emails/emails.tpl', 61, false),array('function', 'page_links', 'manager/emails/emails.tpl', 62, false),array('block', 'iterate', 'manager/emails/emails.tpl', 26, false),array('modifier', 'assign', 'manager/emails/emails.tpl', 29, false),array('modifier', 'escape', 'manager/emails/emails.tpl', 30, false),array('modifier', 'replace', 'manager/emails/emails.tpl', 30, false),array('modifier', 'truncate', 'manager/emails/emails.tpl', 34, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "manager.emails"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-USER027.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div id="emails" class="block">
    <table class="listing" width="100%">
    	<tr valign="bottom">
    		<th width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.emails.emailTemplates"), $this);?>
</th>
    		<th width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.sender"), $this);?>
</th>
    		<th width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.recipient"), $this);?>
</th>
    		<th width="35%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.subject"), $this);?>
</th>
    		<th width="25%" align="right"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</th>
    	</tr>
    	<tr><td colspan="5" class="headseparator">&nbsp;</td></tr>
        <?php $this->_tag_stack[] = array('iterate', array('from' => 'emailTemplates','item' => 'emailTemplate')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
        	<tr valign="top">
        		<td>
        			<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'email','template' => $this->_tpl_vars['emailTemplate']->getEmailKey()), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'emailUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'emailUrl'));?>

        			<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['emailTemplate']->getEmailKey())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '_', ' ') : smarty_modifier_replace($_tmp, '_', ' ')); ?>
&nbsp;<?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'mail','url' => $this->_tpl_vars['emailUrl']), $this);?>

        		</td>
        		<td><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['emailTemplate']->getFromRoleName()), $this);?>
</td>
        		<td><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['emailTemplate']->getToRoleName()), $this);?>
</td>
        		<td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['emailTemplate']->getSubject())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 50, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 50, "...")); ?>
</td>
        		<td align="right" class="nowrap">
        			<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editEmail','path' => $this->_tpl_vars['emailTemplate']->getEmailKey()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.edit"), $this);?>
</a>
        			<?php if ($this->_tpl_vars['emailTemplate']->getCanDisable() && ! $this->_tpl_vars['emailTemplate']->isCustomTemplate()): ?>
        				<?php if ($this->_tpl_vars['emailTemplate']->getEnabled() == 1): ?>
        					<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'disableEmail','path' => $this->_tpl_vars['emailTemplate']->getEmailKey()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.emails.disable"), $this);?>
</a>
        				<?php else: ?>
        					<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'enableEmail','path' => $this->_tpl_vars['emailTemplate']->getEmailKey()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.emails.enable"), $this);?>
</a>
        				<?php endif; ?>
        			<?php endif; ?>
        			<?php if ($this->_tpl_vars['emailTemplate']->isCustomTemplate()): ?>
        				<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'deleteCustomEmail','path' => $this->_tpl_vars['emailTemplate']->getEmailKey()), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.emails.confirmDelete"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
</a>
        			<?php else: ?>
        				<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'resetEmail','path' => $this->_tpl_vars['emailTemplate']->getEmailKey()), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.emails.confirmReset"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.emails.reset"), $this);?>
</a>
        			<?php endif; ?>
        		</td>
        	</tr>
        <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        <?php if ($this->_tpl_vars['emailTemplates']->wasEmpty()): ?>
        	<tr>
        		<td colspan="5" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.none"), $this);?>
</td>
        	</tr>
        	<tr>
        		<td colspan="5" class="endseparator">&nbsp;</td>
        	</tr>
        <?php else: ?>
        	<tr class="u-hide">
        		<td colspan="3" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['emailTemplates']), $this);?>
</td>
        		<td align="right" colspan="2"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'emails','name' => 'emails','iterator' => $this->_tpl_vars['emailTemplates']), $this);?>
</td>
        	</tr>
        <?php endif; ?>
    </table>
    <div class="button-link">
        <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createEmail'), $this);?>
" class="link-button"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.emails.createEmail"), $this);?>
</a>
        <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'resetAllEmails'), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.emails.confirmResetAll"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="link-button"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.emails.resetAll"), $this);?>
</a>
    </div>
</div>

<?php if (! $this->_tpl_vars['emailTemplates']->wasEmpty()): ?>
<div class="colspan u-mb-0" id="colspan">	    
	<section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	    <div class="c-pagination"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['emailTemplates']), $this);?>
</div>
    </section>
    <?php if ($this->_tpl_vars['emailTemplates']->getPageCount() > 1): ?>
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'emails','name' => 'emails','iterator' => $this->_tpl_vars['emailTemplates']), $this);?>

       </div>
    </section>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>