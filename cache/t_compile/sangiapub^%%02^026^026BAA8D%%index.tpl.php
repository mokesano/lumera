<?php /* Smarty version 2.6.26, created on 2026-04-04 19:31:03
         compiled from policies/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'policies/index.tpl', 17, false),array('function', 'url', 'policies/index.tpl', 19, false),array('modifier', 'escape', 'policies/index.tpl', 30, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "about.journalPolicies"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-gfa.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div class="policies-index">
    <h2 class="u-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.journalPolicies"), $this);?>
</h2>
    <ul class="policies-list">
        <li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => "privacy-statement"), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.privacyStatement"), $this);?>
</a></li>
        <li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => "peer-review"), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.peerReviewProcess"), $this);?>
</a></li>
        <li class="u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'ethics'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.publicationEthics"), $this);?>
</a></li>
        <li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => "open-access"), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.openAccessPolicy"), $this);?>
</a></li>
        <li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'archiving'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.archiving"), $this);?>
</a></li>
        <li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'copyright'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.copyrightNotice"), $this);?>
</a></li>
        <li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => "publication-frequency"), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.publicationFrequency"), $this);?>
</a></li>
        <li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => "section-policies"), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.sectionPolicies"), $this);?>
</a></li>

                <?php $_from = $this->_tpl_vars['customPolicies']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['policy']):
?>
            <li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => $this->_tpl_vars['policy']['slug']), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['policy']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li>
        <?php endforeach; endif; unset($_from); ?>
    </ul>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
