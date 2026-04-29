<?php /* Smarty version 2.6.26, created on 2026-04-04 05:39:05
         compiled from author/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'author/index.tpl', 18, false),array('function', 'url', 'author/index.tpl', 19, false),array('function', 'call_hook', 'author/index.tpl', 31, false),array('modifier', 'assign', 'author/index.tpl', 19, false),array('modifier', 'trim', 'author/index.tpl', 32, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "common.queue.long.".($this->_tpl_vars['pageToDisplay'])); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div id="submitStart" class="block submit-box">
    <div class="submit-author c-facet__submit">
        <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.startHereTitle"), $this);?>
</h3>
        <p><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submit'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'submitUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'submitUrl'));?>

        <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('submitUrl' => $this->_tpl_vars['submitUrl'],'key' => "author.submit.startHereLink"), $this);?>
</p>
    </div>
</div>

<ul class="menu">
	<li<?php if (( $this->_tpl_vars['pageToDisplay'] == 'active' )): ?> class="current"<?php endif; ?>><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'index','path' => 'active'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.queue.short.active"), $this);?>
</a></li>
	<li<?php if (( $this->_tpl_vars['pageToDisplay'] == 'completed' )): ?> class="current"<?php endif; ?>><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'index','path' => 'completed'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.queue.short.completed"), $this);?>
</a></li>
</ul>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "author/".($this->_tpl_vars['pageToDisplay']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php ob_start(); ?><?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Author::Index::AdditionalItems"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('additionalItems', ob_get_contents());ob_end_clean(); ?>
<?php if (((is_array($_tmp=$this->_tpl_vars['additionalItems'])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp))): ?>
<div class="block refbacks">
<?php echo $this->_tpl_vars['additionalItems']; ?>

</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
