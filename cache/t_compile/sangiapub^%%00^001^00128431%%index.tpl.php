<?php /* Smarty version 2.6.26, created on 2026-04-04 21:23:37
         compiled from file:/home/sangiaor/public_html/journals/plugins/importexport/medra/templates/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/medra/templates/index.tpl', 15, false),array('function', 'plugin_url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/medra/templates/index.tpl', 26, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "plugins.importexport.medra.displayName"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.settings"), $this);?>
</h3>
<p>
<?php if (! empty ( $this->_tpl_vars['configurationErrors'] )): ?>
	<?php $_from = $this->_tpl_vars['configurationErrors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['configurationError']):
?>
		<?php if ($this->_tpl_vars['configurationError'] == @DOI_EXPORT_CONFIGERROR_DOIPREFIX): ?>
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.error.DOIsNotAvailable"), $this);?>
<br /><br />
		<?php elseif ($this->_tpl_vars['configurationError'] == @DOI_EXPORT_CONFIGERROR_SETTINGS): ?>
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.error.pluginNotConfigured"), $this);?>

		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
<?php endif; ?>
<?php ob_start(); ?><?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'settings'), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('settingsUrl', ob_get_contents());ob_end_clean(); ?>
<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.medra.settings.description",'settingsUrl' => $this->_tpl_vars['settingsUrl']), $this);?>

</p>

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.medra.intro"), $this);?>
</p>

<?php if (empty ( $this->_tpl_vars['configurationErrors'] )): ?>
	<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.export"), $this);?>
</h3>

	<ul>
		<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'all'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.export.unregistered"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'issues'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.export.issues"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'articles'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.export.articles"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'galleys'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.export.galleys"), $this);?>
</a></li>
	</ul>
	<p/>
	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.medra.workOrProduct"), $this);?>
</p>
<?php endif; ?>

<br /><br />
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>