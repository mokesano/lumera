<?php /* Smarty version 2.6.26, created on 2026-04-04 21:38:27
         compiled from file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/index.tpl', 17, false),array('function', 'plugin_url', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/index.tpl', 18, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/importexport/crossref/templates/index.tpl', 24, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "plugins.importexport.crossref.displayName"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-manager.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div id="crossref" class="crossref-article">
    <p>
        <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.registrationIntro"), $this);?>

        <?php ob_start(); ?><?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'settings'), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('settingsUrl', ob_get_contents());ob_end_clean(); ?>
    </p>
</div>

<div id="crossref" class="manage-doi-crossref">
    <h3 class="u-h2"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.export"), $this);?>
</h3>
    <?php if (! empty ( $this->_tpl_vars['configurationErrors'] ) || ! ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?>
    	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.export.unAvailable"), $this);?>
</p>
    <?php else: ?>
    	<ul>
    		<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'articles'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.manageArticleDOIs"), $this);?>
</a></li>
    		<li><a href="<?php echo $this->_plugins['function']['plugin_url'][0][0]->smartyPluginUrl(array('path' => 'issues'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.manageDOIs"), $this);?>
</a></li>
    	</ul>
    <?php endif; ?>
</div>

<div id="crossref" class="settings-doi-crossref">
    <h3 class="u-h2"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.common.settings"), $this);?>
</h3>
    <br />
    <p>
        <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.importexport.crossref.settings.description",'settingsUrl' => $this->_tpl_vars['settingsUrl']), $this);?>

    </p>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>