<?php /* Smarty version 2.6.26, created on 2026-04-04 05:37:59
         compiled from index/publisher.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'index/publisher.tpl', 35, false),array('function', 'translate', 'index/publisher.tpl', 35, false),array('function', 'page_info', 'index/publisher.tpl', 94, false),array('function', 'page_links', 'index/publisher.tpl', 95, false),array('modifier', 'escape', 'index/publisher.tpl', 35, false),array('modifier', 'nl2br', 'index/publisher.tpl', 110, false),array('block', 'iterate', 'index/publisher.tpl', 52, false),)), $this); ?>
<?php echo ''; ?><?php if ($this->_tpl_vars['siteTitle']): ?><?php echo ''; ?><?php $this->assign('pageTitleTranslated', $this->_tpl_vars['siteTitle']); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php $this->assign('pageDisplayed', 'site'); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-site.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>



<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "trends/trendsArticlesPublisher.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div id="_sangiaJOUR" class="cms-common cms-article default-table">
    <p class="taxonomy"></p>
    <div class="section-header">
        <h2 id="c14965756">Agriculture and Marine Science</h2>
        <div class="cms-richtext">
            <p>You can find more journals on our&nbsp;<a href="#_sangiaJOUR" class="is-external internal">Agriculture and Marine Science</a> pages, and&nbsp;in the <a href="#_sangiaJOUR" class="is-external internal">A-Z index</a> below.</p>
        </div>
    </div>
</div>

<?php if ($this->_tpl_vars['useAlphalist']): ?>
<p class="u-hide column"><?php $_from = $this->_tpl_vars['alphaList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['letter']):
?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('searchInitial' => $this->_tpl_vars['letter'],'sort' => 'title'), $this);?>
"><?php if ($this->_tpl_vars['letter'] == $this->_tpl_vars['searchInitial']): ?><strong><?php echo ((is_array($_tmp=$this->_tpl_vars['letter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['letter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></a> <?php endforeach; endif; unset($_from); ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array(), $this);?>
"><?php if ($this->_tpl_vars['searchInitial'] == ''): ?><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.all"), $this);?>
</strong><?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.all"), $this);?>
<?php endif; ?></a></p>

<div class="c-jump">
    <p class="describe italic u-font-sans">Click the alphabet to chose name of journal(s)</p>
    <span class="c-jump-navigation"><?php $_from = $this->_tpl_vars['alphaList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['letter']):
?><a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('searchInitial' => $this->_tpl_vars['letter'],'sort' => 'title'), $this);?>
"><?php if ($this->_tpl_vars['letter'] == $this->_tpl_vars['searchInitial']): ?><strong><?php echo ((is_array($_tmp=$this->_tpl_vars['letter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['letter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></a> <?php endforeach; endif; unset($_from); ?><a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array(), $this);?>
"><?php if ($this->_tpl_vars['searchInitial'] == ''): ?><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.all"), $this);?>
</strong><?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.all"), $this);?>
<?php endif; ?></a></span>
</div>            
<?php endif; ?>

        </div>
    </div>
</div>

<div class="cms-container cms-container-tiles cms-highlight-0">
    <div class="u-row row">
        <div class="columns small-12">
            <div class="cms-tile-row">
                <div class="row">
                    <?php $this->_tag_stack[] = array('iterate', array('from' => 'journals','item' => 'journal')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
                    <div class="columns small-6 medium-3 large-3 cms-tile-row-medium">
                        <div class="">
                            <?php if ($this->_tpl_vars['site']->getSetting('showThumbnail')): ?>
                            <?php $this->assign('displayJournalThumbnail', $this->_tpl_vars['journal']->getLocalizedSetting('journalThumbnail')); ?>
                            <div class="cms-hp-tile cms-hp-tile-image cms-hp-tile-image" <?php if ($this->_tpl_vars['displayJournalThumbnail'] && is_array ( $this->_tpl_vars['displayJournalThumbnail'] )): ?>style="background-image: url(<?php echo $this->_tpl_vars['journalFilesPath']; ?>
<?php echo $this->_tpl_vars['journal']->getId(); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayJournalThumbnail']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
);"<?php else: ?>style="background-image: url(//assets.sangia.org/img/img-default.jpg);"<?php endif; ?>>
                                <a class="tile-toggle" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath()), $this);?>
">
                                    <?php $this->assign('altText', $this->_tpl_vars['journal']->getLocalizedSetting('journalThumbnailAltText')); ?>
                                    <div class="tile-detail"><?php if ($this->_tpl_vars['altText'] != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['altText'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.pageHeaderLogo.altText"), $this);?>
<?php endif; ?></div>
                                    <div class="tile-bottom">
                                        <?php if ($this->_tpl_vars['site']->getSetting('showTitle')): ?>
                                        <h3 class=""><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                    <?php if ($this->_tpl_vars['journals']->wasEmpty()): ?>
                    <div class="cms-common cms-article cms-tile-row-medium">
                        <p class="taxonomy"></p>
                        <div class="section-header">
                            <h2 id="c14965756">Journal Results</h2>
                            <div class="cms-richtext">
                                <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "site.noJournals"), $this);?>
</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="u-hide cms-container cms-highlight-0">
    <div class="u-row row">
        <div class="columns small-12 ">
            <div class="cms-common cms-article default-table">
                <div class="cms-richtext">
                    <div id="journalListPageInfo" class=" "><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['journals']), $this);?>
</div>
                    <div id="journalListPageLinks"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'journals','name' => 'journals','iterator' => $this->_tpl_vars['journals']), $this);?>
</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="cms-container cms-highlight-2">

<?php $this->_tag_stack[] = array('iterate', array('from' => 'journals','item' => 'journal')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php if ($this->_tpl_vars['site']->getSetting('showTitle')): ?>
		<h3><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['site']->getSetting('showDescription')): ?>
		<?php if ($this->_tpl_vars['journal']->getLocalizedDescription()): ?>
			<p><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedDescription())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
		<?php endif; ?>
	<?php endif; ?>
	<p><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath()), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "site.journalView"), $this);?>
</a> | <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'issue','op' => 'current'), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "site.journalCurrent"), $this);?>
</a> | <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'user','op' => 'register'), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "site.journalRegister"), $this);?>
</a></p>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

    <div class="u-row row">
        <div class="columns small-12 ">
            <div class="cms-common cms-article default-table">
                <p class="taxonomy"></p>
                <div class="section-header">
                    <h2 id="c15046410">Journals A-Z Index</h2>
                </div>
                <div class="cms-richtext"><p>Browse all of our journals by subject area</p></div>
                            </div>
        </div>
    </div>
</div>

<div id="_sangia" class="cms-container cms-container-tiles cms-highlight-0">
    <div class="u-row row">
        <div class="columns small-12 ">
            <div class="cms-tile-row">
                <div class="row">
                    <div class="columns  small-12 medium-4 large-4 cms-tile-row-medium">
                        <div id="id5f" class="">
                            <div class="cms-hp-tile cms-hp-tile-image cms-hp-tile-image" style="background-image: url(//assets.sangia.org/static/img/Journals+Impact+Report.jpg);">
                                <a class="tile-toggle" href="//sinta.kemdikbud.go.id/journals" target="_blank">
                                    <div class="tile-detail"></div>
                                    <div class="tile-bottom"><h3 class="">Journal Impact Report Indonesia</h3></div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="columns  small-12 medium-4 large-4 cms-tile-row-medium">
                        <div id="id60" class="">
                            <div class="cms-hp-tile cms-hp-tile-image cms-hp-tile-image" style="background-image: url(//journals.sangia.org/public/journals/1/cover_issue_48_en_US.jpg?as=webp);">
                                <a class="tile-toggle" href="//journals.sangia.org/ISLE" target="_blank">
                                    <div class="tile-detail">
                                        <div class="cms-richtext"><p>Our open access online journal</p></div>
                                    </div>
                                    <div class="tile-bottom"><h3 class="">Akuatikisle: Jurnal Akuakultur, Pesisir dan Pulau-Pulau Kecil </h3></div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="columns  small-12 medium-4 large-4 cms-tile-row-medium">
                        <div id="id61" class="">
                            <div class="cms-hp-tile cms-hp-tile-image cms-hp-tile-image" style="background-image: url(//assets.sangia.org/static/img/Journals_+Why+Publish+With+Us_.jpg);">
                                <a class="tile-toggle" href="#_sangia">
                                    <div class="tile-detail"><div class="cms-richtext"><p>Here's why you should submit your work to Sangia Journals.</p></div></div>
                                    <div class="tile-bottom"><h3 class="">Want to publish an article?</h3></div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="cms-container cms-container-grid cms-highlight-0">
    <div class="u-row row">
        <div class="columns small-12 ">
            <div class="cms-grid-collection">
                <h2 id="c10046798">Stay informed</h2>
                <div class="cms-collection-list">
                    <ul class="small-block-grid-1  medium-block-grid-3">
                        <li>
                            <div id="id63" class="">
                                <a class="cms-teaser-text" href="https://twitter.com/SangiaNews" target="_blank">
                                    <div class="cms-teaser-box cms-teaser-box-with-icon" data-mh="mh-id1" style="">
                                        <div class="article-meta"></div>
                                        <div class="cms-font-icon">1</div>
                                        <h3>Follow @SangiaNews</h3>
                                        <p>Follow Sangia Research & Comment</p>
                                    </div>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div id="id64" class="">
                                <a class="cms-teaser-text" href="//www.facebook.com/sangiapublishing" target="_blank">
                                    <div class="cms-teaser-box cms-teaser-box-with-icon cms-teaser-box-titled-icon" data-mh="mh-id1" style="">
                                        <div class="article-meta"></div>
                                        <div class="cms-font-icon">2</div>
                                        <h3>Sangia News & Publishing</h3>
                                        <p class=""></p>
                                    </div>
                                </a>
                            </div>
                        </li>                        
                        <li>
                            <div id="id64" class="">
                                <a class="cms-teaser-text" href="//www.linkedin.com/company/sangia-publishing" target="_blank">
                                    <div class="cms-teaser-box cms-teaser-box-with-icon" data-mh="mh-id1" style="">
                                        <div class="article-meta"></div>
                                        <div class="cms-font-icon">3</div>
                                        <h3>Sangia Publishing group</h3>
                                        <p>Follow Sangia Research Media & Publishing</p>
                                    </div>
                                </a>
                            </div>
                        </li>
                        <li class="u-hide">
                            <div id="id10" class="">
                                <a class="cms-teaser-text" href="#" data-track="click" data-track-label="#" data-track-category="content links" data-track-action="click - # - >Sign up for our e-newsletter">
                                    <div class="cms-teaser-box cms-teaser-box-with-icon cms-teaser-box-titled-icon" data-mh="mh-id2" style="height: 127px;">
                                        <div class="article-meta"></div>
                                        <div class="cms-font-icon">&gt;</div>
                                        <h3>Sign up for our e-newsletter</h3>
                                    </div>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
