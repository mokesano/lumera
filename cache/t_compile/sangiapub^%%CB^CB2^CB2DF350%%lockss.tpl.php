<?php /* Smarty version 2.6.26, created on 2026-04-04 06:48:51
         compiled from gateway/lockss.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'iterate', 'gateway/lockss.tpl', 21, false),array('function', 'url', 'gateway/lockss.tpl', 22, false),array('function', 'mailto', 'gateway/lockss.tpl', 85, false),array('modifier', 'escape', 'gateway/lockss.tpl', 22, false),array('modifier', 'strip_unsafe_html', 'gateway/lockss.tpl', 34, false),array('modifier', 'nl2br', 'gateway/lockss.tpl', 34, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitleTranslated', 'LOCKSS Publisher Manifest'); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php if ($this->_tpl_vars['journals']): ?>
    <h3 class="bold text-l">Archive of Published Issues</h3>
    
    <ul>
    <?php $this->_tag_stack[] = array('iterate', array('from' => 'journals','item' => 'journal')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
    	<?php if ($this->_tpl_vars['journal']->getSetting('enableLockss')): ?><li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'gateway','op' => 'lockss'), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li><?php endif; ?>
    <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    </ul>

<?php else: ?>

    <p><?php if ($this->_tpl_vars['prevYear'] !== null): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'lockss','year' => $this->_tpl_vars['prevYear']), $this);?>
" class="action">&lt;&lt; Previous</a><?php else: ?><span class="disabled heading">&lt;&lt; Previous</span><?php endif; ?> | <?php if ($this->_tpl_vars['nextYear'] !== null): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'lockss','year' => $this->_tpl_vars['nextYear']), $this);?>
" class="action">Next &gt;&gt;</a><?php else: ?><span class="disabled heading">Next &gt;&gt;</span><?php endif; ?></p>
    
    <h3 class="bold text-l">Archive of Published Issues: <?php echo ((is_array($_tmp=$this->_tpl_vars['year'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3>
    
    <ul>
    <?php $this->_tag_stack[] = array('iterate', array('from' => 'issues','item' => 'issue')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
    	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issue']->getBestIssueId($this->_tpl_vars['journal'])), $this);?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getIssueIdentification())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</a></li>
    <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    </ul>
    
    <?php if ($this->_tpl_vars['showInfo']): ?>
    
    <div class="separator"></div>
    
    <h3 class="bold text-l">Front Matter</h3>
    
    <p>Front Matter associated with this Archival Unit includes:</p>
    
    <ul>
    	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about'), $this);?>
">About the Journal</a></li>
    	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions'), $this);?>
">Submission Guidelines</a></li>
    	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'contact'), $this);?>
">Contact Information</a></li>
    </ul>
    
    <div class="separator"></div>
    
    <h3 class="bold text-l">Metadata</h3>
    
    <p class="separated">Metadata associated with this Archival Unit includes:</p>
    
    <div class="journal-content">
        <div class="info-grid">
                    
            <div class="info-card">
                <h3 class="info-label">Journal URL</h3>
                <p class="info-value">
                    <a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="journal-url" target="_blank">
                        <?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                    </a>
                </p>
            </div>
            
            <div class="info-card">
                <h3 class="info-label">Journal Title</h3>
                <p class="info-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
            </div>
            
            <div class="info-card">
                <h3 class="info-label">Publisher</h3>
                <p class="info-value">
                    <a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getSetting('publisherUrl'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
                </p>
            </div>
    
            <div class="info-card">
                <h3 class="info-label">Publisher Email</h3>
                <p class="info-value">
                    <?php echo smarty_function_mailto(array('address' => ((is_array($_tmp=$this->_tpl_vars['journal']->getSetting('contactEmail'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)),'encode' => 'hex'), $this);?>

                </p>
            </div>
    
            <?php if ($this->_tpl_vars['journal']->getSetting('issn')): ?>
            <div class="info-card">
                <h3 class="info-label">ISSN</h3>
                <p class="info-value">
                    <span class="issn-badge"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getSetting('issn'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                </p>
            </div>
            <?php endif; ?>
    
            <div class="info-card">
                <h3 class="info-label">Supported Languages</h3>
                <div class="info-value">
                    <div class="language-tags">
                        <?php $_from = $this->_tpl_vars['locales']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['localeKey'] => $this->_tpl_vars['localeName']):
?>
                            <span class="language-tag"><?php echo ((is_array($_tmp=$this->_tpl_vars['localeName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 (<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
)</span>
                        <?php endforeach; endif; unset($_from); ?>
                    </div>
                </div>
            </div>
    
            <?php if ($this->_tpl_vars['journal']->getLocalizedSetting('searchKeywords')): ?>
            <div class="info-card">
                <h3 class="info-label">Keywords</h3>
                <p class="info-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedSetting('searchKeywords'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
            </div>
            <?php endif; ?>
    
            <?php if ($this->_tpl_vars['journal']->getLocalizedSetting('searchDescription')): ?>
            <div class="info-card description-card">
                <h3 class="info-label">Description</h3>
                <p class="info-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedSetting('searchDescription'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
            </div>
            <?php endif; ?>
    
            <?php if ($this->_tpl_vars['journal']->getLocalizedSetting('copyrightNotice')): ?>
            <div class="copyright-section">
                <h3 class="copyright-title u-mb-16">Copyright Notice</h3>
                <div><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedSetting('copyrightNotice'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>

<div style="text-align: center; width: 250px; margin: 0 auto">
	<a href="http://www.lockss.org/"><img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/templates/images/lockss.gif" style="border: 0;" alt="LOCKSS" /></a>
	<br />
	LOCKSS system has permission to collect, preserve, and serve this Archival Unit.
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
