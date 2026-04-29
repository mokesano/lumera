<?php /* Smarty version 2.6.26, created on 2026-04-05 00:48:55
         compiled from section/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'section/index.tpl', 17, false),array('modifier', 'explode', 'section/index.tpl', 56, false),array('modifier', 'trim', 'section/index.tpl', 58, false),array('function', 'url', 'section/index.tpl', 25, false),array('function', 'translate', 'section/index.tpl', 26, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitleTranslated', $this->_tpl_vars['section']->getLocalizedTitle()); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-index.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div class="section-index">

        <div class="section-header">
        <?php if ($this->_tpl_vars['section']->getLocalizedAbbrev()): ?>
        <span class="section-abbrev u-js-hide"><?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedAbbrev())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
        <?php endif; ?>
        <div class="section-meta u-js-hide">
            <span><?php echo ((is_array($_tmp=$this->_tpl_vars['journalTitle'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
            <?php if ($this->_tpl_vars['printIssn']): ?><span>ISSN: <?php echo ((is_array($_tmp=$this->_tpl_vars['printIssn'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
            <?php if ($this->_tpl_vars['onlineIssn']): ?><span>E-ISSN: <?php echo ((is_array($_tmp=$this->_tpl_vars['onlineIssn'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
        </div>
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

        <?php if ($this->_tpl_vars['sectionEditors']): ?>
    <div class="section-editors">
        <h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.sectionEditor"), $this);?>
</h2>
        <div class="editors-list">
        <?php $_from = $this->_tpl_vars['sectionEditors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['editor']):
?>
            <div class="editor-item">
                                <?php if ($this->_tpl_vars['editor']['user']->getProfileImageUrl()): ?>
                <img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']['user']->getProfileImageUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                     alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                     class="editor-photo" />
                <?php endif; ?>

                                <div class="editor-identity">
                    <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong>

                    <?php if ($this->_tpl_vars['editor']['user']->getLocalizedAffiliation()): ?>
                    <?php $this->assign('affiliations', ((is_array($_tmp=$this->_tpl_vars['editor']['user']->getLocalizedAffiliation())) ? $this->_run_mod_handler('explode', true, $_tmp, "\n") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "\n"))); ?>
                    <?php $_from = $this->_tpl_vars['affiliations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['affiliation']):
?>
                        <?php if (((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp))): ?>
                        <p class="affiliation"><?php echo ((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
                        <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?>
                    <?php endif; ?>

                    <?php if ($this->_tpl_vars['editor']['user']->getCountry()): ?>
                    <p class="country"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']['user']->getCountryName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
                    <?php endif; ?>

                                        <?php if ($this->_tpl_vars['editor']['user']->getData('orcid')): ?>
                    <a href="https://orcid.org/<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']['user']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                       target="_blank" class="orcid-link">
                        ORCID
                    </a>
                    <?php endif; ?>

                    <?php if ($this->_tpl_vars['editor']['user']->getSintaId()): ?>
                    <a href="https://sinta.kemdikbud.go.id/authors/profile/<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']['user']->getSintaId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                       target="_blank" class="sinta-link">
                        Sinta
                    </a>
                    <?php endif; ?>

                    <?php if ($this->_tpl_vars['editor']['user']->getScopusId()): ?>
                    <a href="https://www.scopus.com/authid/detail.uri?authorId=<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']['user']->getScopusId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                       target="_blank" class="scopus-link">
                        Scopus
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; endif; unset($_from); ?>
        </div>
    </div>
    <?php endif; ?>

        <?php if ($this->_tpl_vars['publishedArticles']): ?>
    <div class="section-recent-articles">
        <h2>Recent Articles</h2>
        <?php $_from = $this->_tpl_vars['publishedArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['article']):
?>
        <div class="article-item">
            <h3>
                <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getId()), $this);?>
">
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                </a>
            </h3>
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
        <?php endforeach; endif; unset($_from); ?>
    
                <?php if ($this->_tpl_vars['totalArticleCount'] > 4): ?>
        <div class="view-all-articles">
            <a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['allArticlesUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
                View all <?php echo $this->_tpl_vars['totalArticleCount']; ?>
 articles →
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="section-no-articles">
        <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "section.noArticles"), $this);?>
</p>
    </div>
    <?php endif; ?>

</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>