<?php /* Smarty version 2.6.26, created on 2026-04-06 03:50:11
         compiled from section/articles.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'section/articles.tpl', 15, false),array('function', 'translate', 'section/articles.tpl', 16, false),array('function', 'page_links', 'section/articles.tpl', 49, false),array('block', 'iterate', 'section/articles.tpl', 30, false),array('modifier', 'escape', 'section/articles.tpl', 34, false),array('modifier', 'date_format', 'section/articles.tpl', 38, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitleTranslated', $this->_tpl_vars['section']->getLocalizedTitle()); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-index.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div class="section-articles-page">

    <div class="section-header">
        <div class="section-nav">
            <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'section','op' => $this->_tpl_vars['section']->getSectionUrlTitle()), $this);?>
">
                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "section.sectionTheSection"), $this);?>

            </a>
            <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'section','op' => $this->_tpl_vars['section']->getSectionUrlTitle(),'path' => 'about'), $this);?>
">
                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "section.aboutTheSection"), $this);?>

            </a>
            <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'section','op' => $this->_tpl_vars['section']->getSectionUrlTitle(),'path' => 'articles'), $this);?>
" class="active">
                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "section.sectionArticle"), $this);?>

            </a>
        </div>
    </div>

    <div class="section-articles-list">
        <p class="total-count"><?php echo $this->_tpl_vars['totalCount']; ?>
 articles</p>

        <?php $this->_tag_stack[] = array('iterate', array('from' => 'articles','item' => 'article')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
        <div class="article-item">
            <h3>
                <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getId()), $this);?>
">
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                </a>
            </h3>
            <div class="article-meta">
                <span class="date-published"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>
</span>
            </div>
            <div class="article-authors">
                <?php $_from = $this->_tpl_vars['article']->getAuthors(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authorLoop']['iteration']++;
?>
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if (! ($this->_foreach['authorLoop']['iteration'] == $this->_foreach['authorLoop']['total'])): ?>, <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?>
            </div>
        </div>
        <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

                <?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'articles','name' => $this->_tpl_vars['rangeInfoName'],'iterator' => $this->_tpl_vars['articles']), $this);?>

    </div>

</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>