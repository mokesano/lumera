<?php /* Smarty version 2.6.26, created on 2026-04-04 10:44:40
         compiled from announcement/view.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'nl2br', 'announcement/view.tpl', 17, false),array('modifier', 'date_format', 'announcement/view.tpl', 20, false),array('function', 'translate', 'announcement/view.tpl', 20, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitleTranslated', $this->_tpl_vars['announcementTitle']); ?><?php echo ''; ?><?php $this->assign('pageId', "announcement.view"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>

<article class="announcement">
    <div id="announcementDescription" itemprop="description" class="c-card__description u-mb-24"><?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedDescription())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

    </div>
    <div class="details">
    	<time class="published posted"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "announcement.posted"), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getDatePosted())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
</time>
    	<span class="more"></span>
    </div>
</article>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
