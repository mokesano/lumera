<?php /* Smarty version 2.6.26, created on 2026-04-04 14:23:44
         compiled from manager/plugins/plugins.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'manager/plugins/plugins.tpl', 16, false),array('function', 'url', 'manager/plugins/plugins.tpl', 22, false),array('modifier', 'escape', 'manager/plugins/plugins.tpl', 22, false),array('modifier', 'to_array', 'manager/plugins/plugins.tpl', 62, false),array('modifier', 'basename', 'manager/plugins/plugins.tpl', 66, false),)), $this); ?>
<?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-USER027.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php if ($this->_tpl_vars['mainPage']): ?>
<div class="pseudoMenu">
	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.plugins.description"), $this);?>
</p>

	<ul id="plugins" >
		<?php $_from = $this->_tpl_vars['plugins']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['plugin']):
?>
			<?php if ($this->_tpl_vars['plugin']->getCategory() != $this->_tpl_vars['category']): ?>
				<?php $this->assign('category', $this->_tpl_vars['plugin']->getCategory()); ?>
				<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => ((is_array($_tmp=$this->_tpl_vars['category'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.categories.".($this->_tpl_vars['category'])), $this);?>
</a></li>
			<?php endif; ?>
		<?php endforeach; endif; unset($_from); ?>
	</ul>

	<?php if (! $this->_tpl_vars['preventManagerPluginManagement']): ?>
		<ul id="pluginManagement">
			<li><b><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'managePlugins','path' => 'install'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.plugins.install"), $this);?>
</a></b></li>
		</ul>
	<?php endif; ?>
</div>
<?php else: ?>
<div class="pluginList">
	<?php $_from = $this->_tpl_vars['plugins']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['plugin']):
?>
		<?php if ($this->_tpl_vars['plugin']->getCategory() != $this->_tpl_vars['category']): ?>
			<?php $this->assign('category', $this->_tpl_vars['plugin']->getCategory()); ?>
			<div id="<?php echo ((is_array($_tmp=$this->_tpl_vars['category'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
			<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.categories.".($this->_tpl_vars['category']).".description"), $this);?>
</p>
			</div>
		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>

	<ul id="plugins">
		<?php $_from = $this->_tpl_vars['plugins']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['plugin']):
?>
			<?php if (! $this->_tpl_vars['plugin']->getHideManagement()): ?>
			<?php if ($this->_tpl_vars['plugin']->getCategory() != $this->_tpl_vars['category']): ?>
				<?php $this->assign('category', $this->_tpl_vars['plugin']->getCategory()); ?>
				<div id="<?php echo ((is_array($_tmp=$this->_tpl_vars['category'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
				<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.categories.".($this->_tpl_vars['category'])), $this);?>
</h3>
				<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.categories.".($this->_tpl_vars['category']).".description"), $this);?>
</p>
				</div>
			<?php endif; ?>
			<li><h4><?php echo ((is_array($_tmp=$this->_tpl_vars['plugin']->getDisplayName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h4>
			<p>
			<?php echo $this->_tpl_vars['plugin']->getDescription(); ?>
<br/>
			<?php $this->assign('managementVerbs', $this->_tpl_vars['plugin']->getManagementVerbs()); ?>
			<?php if ($this->_tpl_vars['managementVerbs'] && $this->_tpl_vars['plugin']->isSitePlugin() && ! $this->_tpl_vars['isSiteAdmin']): ?>
				<em><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.plugins.sitePlugin"), $this);?>
</em>
			<?php elseif ($this->_tpl_vars['managementVerbs']): ?>
				<?php $_from = $this->_tpl_vars['managementVerbs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['verb']):
?>
					<a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'plugin','path' => ((is_array($_tmp=$this->_tpl_vars['category'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['plugin']->getName(), $this->_tpl_vars['verb'][0]) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['plugin']->getName(), $this->_tpl_vars['verb'][0]))), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['verb'][1])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>&nbsp;
				<?php endforeach; endif; unset($_from); ?>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['plugin']->getCurrentVersion() && ! $this->_tpl_vars['preventManagerPluginManagement']): ?>
				<?php $this->assign('pluginInstallName', ((is_array($_tmp=$this->_tpl_vars['plugin']->getPluginPath())) ? $this->_run_mod_handler('basename', true, $_tmp) : basename($_tmp))); ?>
				<a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'managePlugins','path' => ((is_array($_tmp='upgrade')) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['category'], $this->_tpl_vars['pluginInstallName']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['category'], $this->_tpl_vars['pluginInstallName']))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.plugins.upgrade"), $this);?>
</a>&nbsp;
				<a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'managePlugins','path' => ((is_array($_tmp='delete')) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['category'], $this->_tpl_vars['pluginInstallName']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['category'], $this->_tpl_vars['pluginInstallName']))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.plugins.delete"), $this);?>
</a>&nbsp;
			<?php endif; ?>
			</p></li>
			<?php endif; ?>
		<?php endforeach; endif; unset($_from); ?>
	</ul>
	</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
