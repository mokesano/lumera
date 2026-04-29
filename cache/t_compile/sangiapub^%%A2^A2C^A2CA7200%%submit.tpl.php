<?php /* Smarty version 2.6.26, created on 2026-04-04 05:37:59
         compiled from common/submit.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'common/submit.tpl', 3, false),array('function', 'url', 'common/submit.tpl', 7, false),)), $this); ?>
<?php if (( $this->_tpl_vars['pageDisplayed'] == 'site' )): ?>
<div id="customblock-Large-Button"class="_largeButton">
	<a href="mailto:rochmady@stipwunaraha.ac.id"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact.principalContact"), $this);?>
</a>
</div>
<?php else: ?>
<div id="customblock-Large-Button" class="_largeButton">
	<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','op' => 'submit'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.noRoles.submitArticle"), $this);?>
</a>
</div>
<?php endif; ?>