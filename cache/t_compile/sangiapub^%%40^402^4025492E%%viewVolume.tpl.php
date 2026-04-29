<?php /* Smarty version 2.6.26, created on 2026-04-04 15:08:01
         compiled from issue/viewVolume.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'iterate', 'issue/viewVolume.tpl', 20, false),array('modifier', 'string_format', 'issue/viewVolume.tpl', 22, false),array('modifier', 'slugify', 'issue/viewVolume.tpl', 24, false),array('modifier', 'escape', 'issue/viewVolume.tpl', 26, false),array('modifier', 'regex_replace', 'issue/viewVolume.tpl', 27, false),array('modifier', 'strip_tags', 'issue/viewVolume.tpl', 27, false),array('modifier', 'nl2br', 'issue/viewVolume.tpl', 27, false),array('modifier', 'truncate', 'issue/viewVolume.tpl', 27, false),array('modifier', 'date_format', 'issue/viewVolume.tpl', 28, false),array('modifier', 'default', 'issue/viewVolume.tpl', 43, false),array('function', 'native_url', 'issue/viewVolume.tpl', 25, false),array('function', 'translate', 'issue/viewVolume.tpl', 26, false),array('function', 'url', 'issue/viewVolume.tpl', 106, false),)), $this); ?>

<?php echo ''; ?><?php $this->assign('pageTitle', "archive.archives"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "issue/header-volumes.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


	<div class="content mb30 mq1200-padded issue-grid">
		<section>
			<ul id="issue-list" class="ma0 clean-list grid-auto-fill grid-auto-fill-w220 very-small-column medium-row-gap">
        	<?php $this->_tag_stack[] = array('iterate', array('from' => 'issues','item' => 'issue')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
			<?php if ($this->_tpl_vars['issue']->getLocalizedFileName() && $this->_tpl_vars['issue']->getShowCoverPage($this->_tpl_vars['locale']) && ! $this->_tpl_vars['issue']->getHideCoverPageArchives($this->_tpl_vars['locale'])): ?>
			<li id="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
" class="flex-box flex-box-column background-white">
				<div data-component="equal-height-item">
				    <?php $this->assign('issueSlug', ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('slugify', true, $_tmp) : PKPString::slugify($_tmp))); ?>
					<a class="kill-hover flex-box-item" href="<?php echo $this->_plugins['function']['native_url'][0][0]->smartyNativeUrl(array('page' => 'issue','volume' => $this->_tpl_vars['volumeId'],'slug' => $this->_tpl_vars['issueSlug']), $this);?>
" data-track="click" data-track-action="view issue" data-track-label="link">
						<img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getFileName($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" data-src="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getFileName($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if ($this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']) != ''): ?> alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?> alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.coverPage.altText"), $this);?>
"<?php endif; ?> class="lazyload image-constraint pt10" alt="" style="margin: 0 auto;max-width: 200px;">
						<h3 class="h2 pa20 equalize-line-height text13"><?php if (((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/[a-zA-Z]/", "") : smarty_modifier_regex_replace($_tmp, "/[a-zA-Z]/", "")) == $this->_tpl_vars['issue']->getNumber()): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.no"), $this);?>
. <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 5, ".") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 5, ".")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
							<span class="pin-right sans-serif text-gray"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%B %Y") : smarty_modifier_date_format($_tmp, "%B %Y")); ?>
</span>
						</h3>
					</a>
					<div class="pa20 text13 suppress-bottom-margin mq480-hide text-gray-light pt10" data-show-more="" data-show-more-length="200" data-hellip-disable-expand="true">
						<?php if ($this->_tpl_vars['issue']->getLocalizedTitle($this->_tpl_vars['currentJournal'])): ?><h3 class="h3 strong text13"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedTitle($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3><?php endif; ?>
						<?php if ($this->_tpl_vars['issue']->getLocalizedDescription()): ?><p><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedDescription())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 270, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 270, "...")); ?>
</p><?php endif; ?>
					</div>
				</div>
			</li>
			<?php else: ?>
			<li id="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
" class="flex-box flex-box-column background-white">
				<div data-component="equal-height-item">
					<?php $this->assign('issueSlug', ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('slugify', true, $_tmp) : PKPString::slugify($_tmp))); ?>
					<a class="kill-hover flex-box-item" href="<?php echo $this->_plugins['function']['native_url'][0][0]->smartyNativeUrl(array('page' => 'issue','volume' => $this->_tpl_vars['volumeId'],'slug' => $this->_tpl_vars['issueSlug']), $this);?>
" data-track="click" data-track-action="view issue" data-track-label="link">
					    <?php if ($this->_tpl_vars['homepageImage'] && $this->_tpl_vars['homepageImage']['uploadName']): ?>
						<img class="lazyload u-hide image-constraint pt10" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['homepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" data-src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['homepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" alt="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['homepageImage']['altText'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
" style="margin: 0 auto;max-width: 200px">
						<?php endif; ?>
						<h3 class="h2 pa20 equalize-line-height text13"><?php if (((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/[a-zA-Z]/", "") : smarty_modifier_regex_replace($_tmp, "/[a-zA-Z]/", "")) == $this->_tpl_vars['issue']->getNumber()): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.no"), $this);?>
. <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 5, ".") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 5, ".")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
							<span class="pin-right sans-serif text-gray"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%B %Y") : smarty_modifier_date_format($_tmp, "%B %Y")); ?>
</span>
						</h3>
					</a>
					<div class="pa20 text13 suppress-bottom-margin mq480-hide text-gray-light pt10" data-show-more="" data-show-more-length="200" data-hellip-disable-expand="true">
						<?php if ($this->_tpl_vars['issue']->getLocalizedTitle($this->_tpl_vars['currentJournal'])): ?><h3 class="h3 strong text13"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedTitle($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3><?php endif; ?>
						<?php if ($this->_tpl_vars['issue']->getLocalizedDescription()): ?><p><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedDescription())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 270, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 270, "...")); ?>
</p><?php endif; ?>
					</div>
				</div>
			</li>
			<?php endif; ?>
            <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
		    </ul>
		</section>
    </div>
</div>

<div class="column medium-12 container cleared" data-track-component="volume navigation">    
    <div class="content hide-print mq1200-padded" data-track-component="volume navigation">
        <ul class="cleared pb10 pt10 pl0 pr0 double-border-inner text14 mb0 inline-list">
            <?php if ($this->_tpl_vars['prevVolumeId']): ?>
            <li>
                <a href="<?php echo $this->_plugins['function']['native_url'][0][0]->smartyNativeUrl(array('page' => 'volume','volume' => $this->_tpl_vars['prevVolumeId']), $this);?>
" class="icon icon-left icon-double-chevron-left-10x10-blue pl20" data-track="click" data-track-action="previous link" data-track-label="link">
                    <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.prevVolume"), $this);?>
</span>
                </a>
                <?php if ($this->_tpl_vars['prevVolumeId']): ?>
                <span class="pl10 pr10"> | </span>
                <?php else: ?>
                <span class="pl10 pr10"> </span>
                <?php endif; ?>
            </li>
            <?php endif; ?>
            <li>
                <a href="<?php echo $this->_plugins['function']['native_url'][0][0]->smartyNativeUrl(array('page' => 'volume'), $this);?>
" data-track="click" data-track-action="view all" data-track-label="link">
                    <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.allVolume"), $this);?>
</span>
                </a>
                <?php if ($this->_tpl_vars['nextVolumeId']): ?>
                <span class="pl10 pr10"> | </span>
                <?php else: ?>
                <span class="pl10 pr10"> </span>
                <?php endif; ?>
            </li>
            <?php if ($this->_tpl_vars['nextVolumeId']): ?>
            <li>
                <a href="<?php echo $this->_plugins['function']['native_url'][0][0]->smartyNativeUrl(array('page' => 'volume','volume' => $this->_tpl_vars['nextVolumeId']), $this);?>
" class="icon icon-right icon-double-chevron-right-10x10-blue pr20" data-track="click" data-track-action="next link" data-track-label="link">
                    <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.nextVolume"), $this);?>
</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php if ($this->_tpl_vars['issues']->wasEmpty()): ?>
<div class="container cleared container-type-title" data-container-type="title">
    <div class="border-top-1 border-gray-medium"></div>
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
        </div>
        <div class="c-empty-state-card__text">
            <h3 class="c-empty-state-card__text--title headline-5"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "current.noCurrentIssueDesc"), $this);?>
</h3>
            <div class="c-empty-state-card__text--description">We are currently preparing our inaugural content. Please check back soon for our upcoming publications, or consider <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','op' => 'submit'), $this);?>
">submitting your manuscript</a> to be part of our first issue. Visit our <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions'), $this);?>
">Submission Guidelines</a> for more information.</div>
        </div>
    </div>
</div>
    
</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>