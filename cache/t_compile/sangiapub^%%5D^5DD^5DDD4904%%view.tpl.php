<?php /* Smarty version 2.6.26, created on 2026-04-04 06:00:07
         compiled from issue/view.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'strlen', 'issue/view.tpl', 42, false),array('modifier', 'slugify', 'issue/view.tpl', 46, false),array('modifier', 'assign', 'issue/view.tpl', 60, false),array('modifier', 'date_format', 'issue/view.tpl', 68, false),array('modifier', 'escape', 'issue/view.tpl', 86, false),array('modifier', 'to_array', 'issue/view.tpl', 118, false),array('function', 'native_url', 'issue/view.tpl', 60, false),array('function', 'url', 'issue/view.tpl', 63, false),array('function', 'translate', 'issue/view.tpl', 86, false),)), $this); ?>

<?php if (! $this->_tpl_vars['showToc'] && $this->_tpl_vars['issue']): ?>

	
		<?php $this->assign('issueVolume', $this->_tpl_vars['issue']->getVolume()); ?>

		<?php if ($this->_tpl_vars['issueId']): ?>
		<?php $this->assign('issueNum', $this->_tpl_vars['issue']->getNumber()); ?>
		<?php if (((is_array($_tmp=$this->_tpl_vars['issueNum'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
						<?php $this->assign('issueSlug', ""); ?>
		<?php else: ?>
			<?php $this->assign('issueSlug', ((is_array($_tmp=$this->_tpl_vars['issueNum'])) ? $this->_run_mod_handler('slugify', true, $_tmp) : PKPString::slugify($_tmp))); ?>
						<?php if (((is_array($_tmp=$this->_tpl_vars['issueSlug'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
				<?php $this->assign('issueSlug', $this->_tpl_vars['issue']->getId()); ?>
			<?php endif; ?>
		<?php endif; ?>

				<?php if (((is_array($_tmp=$this->_tpl_vars['issueSlug'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0 && ((is_array($_tmp=$this->_tpl_vars['issueVolume'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
						<?php echo ((is_array($_tmp=$this->_plugins['function']['native_url'][0][0]->smartyNativeUrl(array('page' => 'issue','volume' => $this->_tpl_vars['issueVolume'],'slug' => $this->_tpl_vars['issueSlug'],'showToc' => true), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

		<?php elseif (((is_array($_tmp=$this->_tpl_vars['issueVolume'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
						<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'view','path' => $this->_tpl_vars['issueVolume']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

		<?php else: ?>
						<?php $this->assign('issueYear', $this->_tpl_vars['issue']->getYear()); ?>
			<?php if (((is_array($_tmp=$this->_tpl_vars['issueYear'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
				<?php $this->assign('issueYear', ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
			<?php endif; ?>
			<?php if (((is_array($_tmp=$this->_tpl_vars['issueYear'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
				<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'year','path' => $this->_tpl_vars['issueYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

			<?php else: ?>
				<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'displayArchive'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

			<?php endif; ?>
		<?php endif; ?>
	<?php else: ?>
				<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'current','path' => 'showToc'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

	<?php endif; ?>

		<?php if ($this->_tpl_vars['coverPagePath']): ?>
	<div id="issueCoverImage" class="u-hide"><a href="<?php echo $this->_tpl_vars['currentUrl']; ?>
"><img class="lazyload cover__image" <?php if ($this->_tpl_vars['coverPageAltText'] != ''): ?> title="Cover issue <?php echo ((is_array($_tmp=$this->_tpl_vars['coverPageAltText'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?> title="Cover issue <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.coverPage.altText"), $this);?>
"<?php endif; ?> <?php if ($this->_tpl_vars['coverPageAltText'] != ''): ?> alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPageAltText'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php else: ?> alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.coverPage.altText"), $this);?>
"<?php endif; ?><?php if ($this->_tpl_vars['width']): ?> width="<?php echo ((is_array($_tmp=$this->_tpl_vars['width'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php endif; ?><?php if ($this->_tpl_vars['height']): ?> height="<?php echo ((is_array($_tmp=$this->_tpl_vars['height'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php endif; ?> <?php if ($this->_tpl_vars['coverPagePath']): ?> src="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getFileName($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php endif; ?> width="100%" /></a>
	</div>
	<?php else: ?>
	<div id="issueCoverImage" class="u-hide"><a href="<?php echo $this->_tpl_vars['currentUrl']; ?>
"><img class="lazyload journal-cover__image cover-lazy img-default" title="Cover issue default" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['homepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
/homepageImage_en_US.jpg" alt="Cover issue default" style="margin-top: 0" width="100%" />
	</div>
	<?php endif; ?>

<?php elseif ($this->_tpl_vars['issue']): ?>

		
    <?php if ($this->_tpl_vars['issueGalleys']): ?>
    <section id="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.fullIssue"), $this);?>
" class="u-mb-48 u-mt-48" aria-labelledby="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.fullIssue"), $this);?>
" data-container-type="issue-section-list" data-track-component="issue section list">
        <div class="c-section-heading" data-test="title">
            <h2><span class="content-break"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.fullIssue"), $this);?>
</span><span class="text-gray-light altSize u-hide">(<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumArticles())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.articlesCount"), $this);?>
)</span></h2>
        </div>
    <ul class="app-article-list-row">
		<?php if (( ! $this->_tpl_vars['subscriptionRequired'] || $this->_tpl_vars['issue']->getAccessStatus() == @ISSUE_ACCESS_OPEN || $this->_tpl_vars['subscribedUser'] || $this->_tpl_vars['subscribedDomain'] || ( $this->_tpl_vars['subscriptionExpiryPartial'] && $this->_tpl_vars['issueExpiryPartial'] ) )): ?>
			<?php $this->assign('hasAccess', 1); ?>
		<?php else: ?>
			<?php $this->assign('hasAccess', 0); ?>
		<?php endif; ?>
		<div class="issue tocArticle">
			<h3 class="link tocTitle title-issue"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.viewIssueDescription"), $this);?>
</h3>
			<div class="tocArticleGalleysPages">
				<ul id="author-article-InfoList" class="tocMenuArticle ul-list">
				<?php if ($this->_tpl_vars['hasAccess'] || ( $this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] )): ?>
					<?php $_from = $this->_tpl_vars['issueGalleys']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['issueGalley']):
?>
					<?php if ($this->_tpl_vars['issueGalley']->isPdfGalley()): ?>
					<li class="tocMenuArticle pubDOI galley-issue">
						<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'download','path' => ((is_array($_tmp=$this->_tpl_vars['issue']->getBestIssueId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['issueGalley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['issueGalley']->getBestGalleyId($this->_tpl_vars['currentJournal'])))), $this);?>
" class="file" target="_blank">Download <?php echo ((is_array($_tmp=$this->_tpl_vars['issueGalley']->getGalleyLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <span class="fileSize">(<?php echo $this->_tpl_vars['issueGalley']->getNiceFileSize(); ?>
)</span> <span class="fileView"><?php echo $this->_tpl_vars['issueGalley']->getViews(); ?>
 views</span></a>
					</li>
					<li class="tocMenuArticle pubDOI galley-issue">
						<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'viewFile','path' => ((is_array($_tmp=$this->_tpl_vars['issue']->getBestIssueId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['issueGalley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['issueGalley']->getBestGalleyId($this->_tpl_vars['currentJournal'])))), $this);?>
" class="file" target="_blank">View <?php echo ((is_array($_tmp=$this->_tpl_vars['issueGalley']->getGalleyLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 file <span class="fileSize">(<?php echo $this->_tpl_vars['issueGalley']->getNiceFileSize(); ?>
)</span> <span class="fileView"><?php echo $this->_tpl_vars['issueGalley']->getViews(); ?>
 views</span></a>
					</li>					
					<?php else: ?>
					<li class="tocMenuArticle pubDOI galley-issue">
						<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'viewDownloadInterstitial','path' => ((is_array($_tmp=$this->_tpl_vars['issue']->getBestIssueId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['issueGalley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['issueGalley']->getBestGalleyId($this->_tpl_vars['currentJournal'])))), $this);?>
" class="file">Download <?php echo ((is_array($_tmp=$this->_tpl_vars['issueGalley']->getGalleyLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <span class="fileSize">(<?php echo $this->_tpl_vars['issueGalley']->getNiceFileSize(); ?>
)</span> <span class="fileView"><?php echo $this->_tpl_vars['issueGalley']->getViews(); ?>
 views</span></a>
					</li>
					<?php endif; ?>
					<?php endforeach; endif; unset($_from); ?>
				<?php endif; ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>
	
	</section>
    
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "issue/issue.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    
<?php else: ?>

		<div class="container cleared container-type-title">
	    <div class="border-top-1 border-gray-medium"></div>
	    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
	        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
	        </div>
	        <div class="c-empty-state-card__text">
	            <h3 class="c-empty-state-card__text--title headline-5"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "current.noCurrentIssueDesc"), $this);?>
</h3>
	            <div class="c-empty-state-card__text--description">We are currently in the early stages of our publication process. Please visit our <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'focusAndScope'), $this);?>
">About</a> page to learn more about our scope and objectives, or consider <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','op' => 'submit'), $this);?>
">submitting your manuscript</a> to contribute to our upcoming inaugural issue.</div>
	        </div>
	    </div>
	</div>

<?php endif; ?>
            
</main>