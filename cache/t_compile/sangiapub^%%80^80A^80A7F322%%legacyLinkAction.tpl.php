<?php /* Smarty version 2.6.26, created on 2026-04-16 16:17:00
         compiled from linkAction/legacyLinkAction.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'concat', 'linkAction/legacyLinkAction.tpl', 18, false),array('modifier', 'uniqid', 'linkAction/legacyLinkAction.tpl', 18, false),array('modifier', 'escape', 'linkAction/legacyLinkAction.tpl', 20, false),array('function', 'modal', 'linkAction/legacyLinkAction.tpl', 27, false),array('function', 'confirm', 'linkAction/legacyLinkAction.tpl', 36, false),)), $this); ?>

<?php if (! $this->_tpl_vars['actOnId']): ?>
	<?php $this->assign('actOnId', $this->_tpl_vars['id']); ?>
<?php endif; ?>

<?php if (! $this->_tpl_vars['buttonId']): ?>
	<?php $this->assign('buttonId', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['id'])) ? $this->_run_mod_handler('concat', true, $_tmp, "-", $this->_tpl_vars['action']->getId(), "-button") : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, "-", $this->_tpl_vars['action']->getId(), "-button")))) ? $this->_run_mod_handler('uniqid', true, $_tmp) : uniqid($_tmp))); ?>
	<?php if ($this->_tpl_vars['action']->getImage()): ?>
		<a href="<?php if ($this->_tpl_vars['action']->getMode() == @LINK_ACTION_MODE_LINK): ?><?php echo $this->_tpl_vars['action']->getUrl(); ?>
<?php endif; ?>" id="<?php echo ((is_array($_tmp=$this->_tpl_vars['buttonId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="<?php if ($this->_tpl_vars['actionCss']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['actionCss'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php endif; ?><?php if ($this->_tpl_vars['action']->getImage()): ?>sprite <?php echo ((is_array($_tmp=$this->_tpl_vars['action']->getImage())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" <?php if ($this->_tpl_vars['hoverTitle']): ?>title="<?php echo ((is_array($_tmp=$this->_tpl_vars['action']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">&nbsp;<?php else: ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['action']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></a>
	<?php else: ?>
		<a href="<?php if ($this->_tpl_vars['action']->getMode() == @LINK_ACTION_MODE_LINK): ?><?php echo $this->_tpl_vars['action']->getUrl(); ?>
<?php endif; ?>" id="<?php echo ((is_array($_tmp=$this->_tpl_vars['buttonId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['actionCss']): ?>class="<?php echo ((is_array($_tmp=$this->_tpl_vars['actionCss'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php endif; ?> <?php if ($this->_tpl_vars['hoverTitle']): ?> title="<?php echo $this->_tpl_vars['action']->getLocalizedTitle(); ?>
"><?php else: ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['action']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></a>
	<?php endif; ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['action']->getMode() == @LINK_ACTION_MODE_MODAL): ?>
	<?php echo $this->_plugins['function']['modal'][0][0]->smartyModal(array('url' => $this->_tpl_vars['action']->getUrl(),'actOnType' => $this->_tpl_vars['action']->getType(),'actOnId' => ((is_array($_tmp="#")) ? $this->_run_mod_handler('concat', true, $_tmp, $this->_tpl_vars['actOnId']) : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, $this->_tpl_vars['actOnId'])),'button' => ((is_array($_tmp="#")) ? $this->_run_mod_handler('concat', true, $_tmp, $this->_tpl_vars['buttonId']) : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, $this->_tpl_vars['buttonId']))), $this);?>


<?php elseif ($this->_tpl_vars['action']->getMode() == @LINK_ACTION_MODE_CONFIRM): ?>
	<?php if ($this->_tpl_vars['action']->getLocalizedConfirmMessage()): ?>
		<?php $this->assign('dialogText', $this->_tpl_vars['action']->getLocalizedConfirmMessage()); ?>
	<?php else: ?>
		<?php $this->assign('dialogText', $this->_tpl_vars['action']->getLocalizedTitle()); ?>
	<?php endif; ?>

	<?php echo $this->_plugins['function']['confirm'][0][0]->smartyConfirm(array('url' => $this->_tpl_vars['action']->getUrl(),'dialogText' => $this->_tpl_vars['dialogText'],'actOnType' => $this->_tpl_vars['action']->getType(),'actOnId' => ((is_array($_tmp="#")) ? $this->_run_mod_handler('concat', true, $_tmp, $this->_tpl_vars['actOnId']) : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, $this->_tpl_vars['actOnId'])),'button' => ((is_array($_tmp="#")) ? $this->_run_mod_handler('concat', true, $_tmp, $this->_tpl_vars['buttonId']) : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, $this->_tpl_vars['buttonId'])),'translate' => false), $this);?>


<?php elseif ($this->_tpl_vars['action']->getMode() == @LINK_ACTION_MODE_AJAX): ?>
	<script type="text/javascript">
		<!--
		$(function() {
			ajaxAction(
				'<?php echo ((is_array($_tmp=$this->_tpl_vars['action']->getType())) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript')); ?>
',
				'#<?php echo ((is_array($_tmp=$this->_tpl_vars['actOnId'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript')); ?>
',
				'#<?php echo ((is_array($_tmp=$this->_tpl_vars['buttonId'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript')); ?>
',
				'<?php echo ((is_array($_tmp=$this->_tpl_vars['action']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript')); ?>
'
			);
		});
		// -->
	</script>
<?php endif; ?>
