<?php /* Smarty version 2.6.26, created on 2026-04-09 21:08:21
         compiled from index/journal.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'math', 'index/journal.tpl', 29, false),array('function', 'call_hook', 'index/journal.tpl', 160, false),array('function', 'translate', 'index/journal.tpl', 169, false),array('function', 'url', 'index/journal.tpl', 217, false),array('function', 'counter', 'index/journal.tpl', 223, false),array('modifier', 'count', 'index/journal.tpl', 29, false),array('modifier', 'escape', 'index/journal.tpl', 169, false),array('modifier', 'date_format', 'index/journal.tpl', 169, false),array('modifier', 'nl2br', 'index/journal.tpl', 233, false),array('modifier', 'strip_tags', 'index/journal.tpl', 344, false),array('modifier', 'truncate', 'index/journal.tpl', 475, false),array('modifier', 'replace', 'index/journal.tpl', 580, false),array('block', 'iterate', 'index/journal.tpl', 224, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitleTranslated', $this->_tpl_vars['siteTitle']); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-HOME.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>

<?php if ($this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('onlineIssn')): ?>
	<?php $this->assign('issn', $this->_tpl_vars['currentJournal']->getSetting('onlineIssn')); ?>
<?php elseif ($this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('printIssn')): ?>
	<?php $this->assign('issn', $this->_tpl_vars['currentJournal']->getSetting('printIssn')); ?>
<?php endif; ?>

<div id="JOUR" class="journal-page">

<div class="journal-content">
<?php $this->assign('articleCount', 0); ?>
<?php if ($this->_tpl_vars['publishedArticles']): ?>
    <?php $_from = $this->_tpl_vars['publishedArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['section']):
?>
        <?php if ($this->_tpl_vars['section']['articles']): ?>
            <?php echo smarty_function_math(array('equation' => "x + y",'x' => $this->_tpl_vars['articleCount'],'y' => count($this->_tpl_vars['section']['articles']),'assign' => 'articleCount'), $this);?>

        <?php endif; ?>
    <?php endforeach; endif; unset($_from); ?>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/featured/article_Hero.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE && $this->_tpl_vars['articleCount'] >= 4): ?>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/featured/editor-home.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>


<?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>
<section class="area-wrapper u-mt-32">
  <div class="live-area">
  	<section class="row raw">
  	    <div class="column medium-12 editorial-timeline">
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/featured/editorial-timeline.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		</div>
	</section>
  </div>
</section>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/featured/mostDownloads.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>

<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Index::journal"), $this);?>


<?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE && $this->_tpl_vars['articleCount'] >= 1): ?>
<section class="area-wrapper u-mb-24 u-mt-32">
  <div class="live-area">
  	<div class="row raw">
  		<div class="position-relative z-index-1">
  			<div class="u-container c-slice-heading" data-test="title">
                <h2 class="titles"><span class="title">Latest issue</span><span class="sub-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
, <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 (<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>
)</span></h2>
                <?php if ($this->_tpl_vars['issue']->getLocalizedTitle($this->_tpl_vars['currentJournal'])): ?>
				<h3 class="u-hide issue-title headline-2545795530"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedTitle($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3>
				<?php endif; ?>
								<?php $this->assign('currentYear', ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
				<?php $this->assign('currentMonth', ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%m") : smarty_modifier_date_format($_tmp, "%m"))); ?>
									
								<?php $this->assign('issueYear', ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
				<?php $this->assign('issueMonth', ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%m") : smarty_modifier_date_format($_tmp, "%m"))); ?>
				<div class="insight-label u-font-sans">This issue is <?php if ($this->_tpl_vars['currentYear'] == $this->_tpl_vars['issueYear']): ?><?php if ($this->_tpl_vars['currentMonth'] <= $this->_tpl_vars['issueMonth']): ?>in progress but<?php else: ?>completed,<?php endif; ?><?php else: ?>halted but<?php endif; ?> contains articles that are final and fully citable.</div>
            </div>
  		</div>
	  	<div id="contents" class="position-relative z-index-1" role="main">
	  		<section id="latest-content" class="u-mb-0" data-track-component="latest content">
	  			<h2 class="u-visually-hidden">Latest Articles Content</h2>
	  			<div class="u-container">
	  				<ul class="app-article-list-row">
	  					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "issue/issueLates.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	  				</ul>
	  			</div>
	  		</section>
	  	</div>
	  </div>
  </div>
</section>
<?php endif; ?>

<?php if ($this->_tpl_vars['externalHomeContent']): ?>
<section id="<?php echo ((is_array($_tmp=$this->_tpl_vars['externalFeedSectionId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="live-area">
    <div class="row raw">
    <?php echo $this->_tpl_vars['externalHomeContent']; ?>

    </div>
</section>
<?php endif; ?>



<?php if ($this->_tpl_vars['enableAnnouncementsHomepage']): ?>
<section class="live-area-wrapper u-mb-32">
  <div class="live-area">
  	<section class="row raw">
  		<div class="columns medium-12 main-contents c-slice-heading">
      		<h2 class="headline">
      		    <a title="View more News and Announcements" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "announcement.announcementsHome"), $this);?>
<svg class="c-section-heading__icon" aria-hidden="true" focusable="false" height="20" width="20" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="m4.08573416 5.70052374 2.48162731-2.4816273c.39282216-.39282216 1.02197315-.40056173 1.40306523-.01946965.39113012.39113012.3914806 1.02492687-.00014045 1.41654791l-4.17620791 4.17620792c-.39120769.39120768-1.02508144.39160691-1.41671995-.0000316l-4.17639421-4.1763942c-.39122513-.39122514-.39767006-1.01908149-.01657797-1.40017357.39113012-.39113012 1.02337105-.3930364 1.41951348.00310603l2.48183447 2.48183446.99770587 1.01367533z" transform="matrix(0 -1 1 0 2.081146 11.085734)"></path></svg></a>
      		</h2>
  		</div>
  		
  		<section class="column medium-8">
  			<div class="app-announcement-list-row announcements">
			<?php echo smarty_function_counter(array('start' => 1,'skip' => 1,'assign' => 'count'), $this);?>

			<?php $this->_tag_stack[] = array('iterate', array('from' => 'announcements','item' => 'announcement')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
			<?php if (! $this->_tpl_vars['numAnnouncementsHomepage'] || $this->_tpl_vars['count'] <= $this->_tpl_vars['numAnnouncementsHomepage']): ?>
				<section class="app-announcement-list-row__item c-card--flush announcement">
				<?php if ($this->_tpl_vars['announcement']->getTypeId()): ?>
					<h3 class="headline-2545795530 u-mb-8"><?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getAnnouncementTypeName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3>
					<?php else: ?>
					<h3 class="headline-2545795530 u-mb-8"><?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3>
				<?php endif; ?>

				<div class="description"><?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedDescriptionShort())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</div>
				<br />
    				<div class="u-flex-direction-column">
        				<span class="details">
        					<time class="published posted"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "announcement.posted"), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getDatePosted())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
</time>
        					<td class="u-hide more">&nbsp;</td>
        					<?php if ($this->_tpl_vars['announcement']->getLocalizedDescription() != null): ?>
        					<span class="more"><a itemprop="url" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement','op' => 'view','path' => $this->_tpl_vars['announcement']->getId()), $this);?>
">View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "announcement.viewLink"), $this);?>
</a></span>
        					<?php endif; ?>
        				</span>
        			</div>
				</section>
			<?php endif; ?>
			<?php echo smarty_function_counter(array(), $this);?>

			<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    		</div>
    	</section>

		<div class="column medium-4">
			<div class="box">
				<section class="teaser"><h4 class="headline-3789142952">Browse our latest news</h4>
					<div><p>Stay up to date on events, announcements &amp; new releases</p></div>
					<a title="News and Announcements" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement'), $this);?>
" class="button-base-3588540778" class="sangia-button"><span class="button-label-1262423735">Browse now</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-248642068"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
				</section>
			</div>
		</div>
		
	</section>
  </div>
</section>
<?php endif; ?>

<?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>

	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/search.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	
<section class="area-wrapper u-mt-16 u-mb-24 u-hide">
  <div class="live-area">
  	<div class="row raw">
  		<div class="position-relative z-index-1">
  			<div class="u-container c-slice-heading" data-test="title">
                <h2 class="titles"><span class="title">Latest Research articles</span><span class="sub-title u-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
, <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 (<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>
)</span></h2>
                <?php if ($this->_tpl_vars['issue']->getLocalizedTitle($this->_tpl_vars['currentJournal'])): ?>
				<h3 class="u-hide issue-title headline-2545795530"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedTitle($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3>
				<?php endif; ?>
								<?php $this->assign('currentYear', ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
				<?php $this->assign('currentMonth', ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%m") : smarty_modifier_date_format($_tmp, "%m"))); ?>
									
								<?php $this->assign('issueYear', ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
				<?php $this->assign('issueMonth', ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%m") : smarty_modifier_date_format($_tmp, "%m"))); ?>
				<div class="insight-label u-font-sans">This issue is <?php if ($this->_tpl_vars['currentYear'] == $this->_tpl_vars['issueYear']): ?><?php if ($this->_tpl_vars['currentMonth'] <= $this->_tpl_vars['issueMonth']): ?>in progress but<?php else: ?>completed,<?php endif; ?><?php else: ?>halted but<?php endif; ?> contains articles that are final and fully citable.</div>
            </div>
  		</div>
	  	<div id="contents" class="position-relative z-index-1" role="main">
	  		<section id="featured-content" class="u-mb-0" data-track-component="featured content">
	  			<h2 class="u-visually-hidden">Featured Content</h2>
	  			<div class="u-container">
	  				<ul class="app-featured-row">
	  					<li class="u-hide app-featured-row__item app-featured-row__item--current-issue">
	  						<div class="c-card c-card--flush u-full-height">
        	  					<a class="c-card__image" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'current'), $this);?>
" data-track="click" data-track-action="view current issue" data-track-label="image">
        	  						<?php $this->assign('displayHomepageImage', $this->_tpl_vars['currentJournal']->getLocalizedSetting('homepageImage')); ?>
        	  						<?php if ($this->_tpl_vars['issue']->getLocalizedFileName() && $this->_tpl_vars['issue']->getShowCoverPage($this->_tpl_vars['locale']) && ! $this->_tpl_vars['issue']->getHideCoverPageArchives($this->_tpl_vars['locale'])): ?>
        	  						<picture>
        	  							<source srcset="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getFileName($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp" type="image/webp">
        	  							<img class="lazyload" loading="lazy" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getFileName($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']) != ''): ?> title="Cover issue <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php else: ?> title="Cover issue"<?php endif; ?> <?php if ($this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']) != ''): ?> alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?> alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.coverPage.altText"), $this);?>
"<?php endif; ?> itemprop="image">
        	  						</picture>
        	  						<?php elseif ($this->_tpl_vars['displayHomepageImage'] && is_array ( $this->_tpl_vars['displayHomepageImage'] )): ?>
        	  						<picture><source type="image/webp" srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
?as=webp 450w, <?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img class="lazyload" loading="lazy" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" alt="">
        	  						</picture>	  						
        	  						<?php else: ?>
        	  						<picture >
        	  							<source srcset="//assets.sangia.org/img/img-default_v2.png?as=webp 450w, //assets.sangia.org/img/img-default_v2.png?as=webp 735w" type="image/webp" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px">
        	  							<img class="lazyload" loading="lazy" src="//assets.sangia.org/img/img-default_v2.png" <?php if ($this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']) != ''): ?> title="Cover issue <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php else: ?> title="Cover issue"<?php endif; ?> <?php if ($this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']) != ''): ?> alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?> alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.coverPage.altText"), $this);?>
"<?php endif; ?> itemprop="image">
        	  						</picture>
        	  						<?php endif; ?>
        	  					</a>
	  							<div class="c-card__body u-display-flex u-flex-direction-column">
	  								<ul class="u-mb-16 u-list-reset u-display-flex">
	  									<li class="u-mr-4 u-flex-grow">
	  										<a class="u-button u-button--full-width" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'current'), $this);?>
" data-track="click" data-track-action="view current issue" data-track-label="button">Contents</a>
	  									</li>
	  									<li class="u-ml-4 u-flex-grow">
	  										<a class="u-button u-button--primary u-button--full-width" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification','op' => 'subscribeMailList'), $this);?>
" data-track="click" data-track-action="subscribe" data-track-label="button">Subscribe</a>
	  									</li>
	  								</ul>
	  							</div>

	  							<div class="c-card__section c-meta">
	  								<span class="c-meta__item"><span class="c-meta__type"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "journal.currentIssue"), $this);?>
</span></span><time class="c-meta__item" datetime="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, ($this->_tpl_vars['dateFormatShort'])) : smarty_modifier_date_format($_tmp, ($this->_tpl_vars['dateFormatShort']))); ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
</time>
	  							</div>
	  						</div>
	  					</li>
	  					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "issue/issues.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	  				</ul>
	  			</div>
	  		</section>
	  	</div>
	</div>
  </div>
</section>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/journal-identity.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<section class="area-wrapper u-mt-32">
  <div class="row raw">
		    
	<div data-test="title" class="column c-slice-heading u-mt-0 u-hide">
	    <h2 class="headline"><a title="View more about <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.about"), $this);?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedInitials())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<svg class="c-section-heading__icon" aria-hidden="true" focusable="false" height="25" width="25" viewBox="0 0 16 12" xmlns="http://www.w3.org/2000/svg"><path d="m4.08573416 5.70052374 2.48162731-2.4816273c.39282216-.39282216 1.02197315-.40056173 1.40306523-.01946965.39113012.39113012.3914806 1.02492687-.00014045 1.41654791l-4.17620791 4.17620792c-.39120769.39120768-1.02508144.39160691-1.41671995-.0000316l-4.17639421-4.1763942c-.39122513-.39122514-.39767006-1.01908149-.01657797-1.40017357.39113012-.39113012 1.02337105-.3930364 1.41951348.00310603l2.48183447 2.48183446.99770587 1.01367533z" transform="matrix(0 -1 1 0 2.081146 11.085734)"></path></svg></a>
	   </h2>
	</div>
			
	<section class="columns medium-12">
		<div class="main-contents" role="main">	

		<div class="explore-contents">
		    <div class="row">
		        <div class="columns medium-8" role="main">
		            <div class="row">
		                <div class="column <?php if ($this->_tpl_vars['issue']): ?>medium-8<?php else: ?>medium-12<?php endif; ?> insight">
	                <ul class="journalinsights u-mb-16 u-fonts">
		                <?php if ($this->_tpl_vars['printIssn']): ?> <?php else: ?>
						<li class="meta"><span><span class="bold">ISSN:</span> <?php if ($this->_tpl_vars['currentJournal']->getSetting('onlineIssn')): ?><span class="c-meta__item"><a class="anchor" href="//portal.issn.org/resource/ISSN/<?php echo $this->_tpl_vars['currentJournal']->getSetting('onlineIssn'); ?>
" target="_blank"><?php echo $this->_tpl_vars['currentJournal']->getSetting('onlineIssn'); ?>
</a> (Medium online)</span><?php else: ?><span class="c-meta__item">on proccess (Medium online)</span><?php endif; ?><?php if ($this->_tpl_vars['currentJournal']->getSetting('printIssn')): ?> <span class="c-meta__item"><a class="anchor" href="//portal.issn.org/resource/ISSN/<?php echo $this->_tpl_vars['currentJournal']->getSetting('printIssn'); ?>
" target="_blank"><?php echo $this->_tpl_vars['currentJournal']->getSetting('printIssn'); ?>
</a> (Medium Print)</span><?php endif; ?></span></li>
						<?php endif; ?>

						<li class="meta"><span class="c-meta__item"><span class="bold">Journal title:</span> <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></li>												
						<?php if ($this->_tpl_vars['currentJournal']->getSetting('initials')): ?>
						<li class="meta"><span class="c-meta__item"><span class="bold">Journal Initials:</span> <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedInitials())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li></span><?php endif; ?>
												
						<?php if ($this->_tpl_vars['currentJournal']->getSetting('abbreviation')): ?>
						<li class="meta"><span class="c-meta__item"><span class="bold">Abbreviation:</span> <span class="italic"><?php echo $this->_tpl_vars['currentJournal']->getSetting('abbreviation',$this->_tpl_vars['currentJournal']->getPrimaryLocale()); ?>
</span></span></li><?php endif; ?>

            			<?php if ($this->_tpl_vars['issue']): ?>
            			<?php $this->assign('firstYear', $this->_tpl_vars['currentJournal']->getSetting('initialYear')); ?>
            			<?php $this->assign('firstVolume', $this->_tpl_vars['currentJournal']->getSetting('initialVolume')); ?>
            			<?php $this->assign('initialNumber', $this->_tpl_vars['currentJournal']->getSetting('initialNumber')); ?>
            			<?php $this->assign('lastYear', $this->_tpl_vars['issue']->getYear()); ?>
            			<?php $this->assign('lastVolume', $this->_tpl_vars['issue']->getVolume()); ?>
            			<li class="meta"><span class="c-meta__item"><span class="bold">First <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.year"), $this);?>
 published:</span> <?php echo ((is_array($_tmp=$this->_tpl_vars['firstYear'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></li>
            			<li class="meta"><span class="c-meta__item"><span class="bold">First <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 published:</span> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['firstVolume'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['initialNumber'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></li>
            			
            			<?php $this->assign('volumePerYear', $this->_tpl_vars['currentJournal']->getSetting('volumePerYear')); ?>
            			<?php $this->assign('issuePerVolume', $this->_tpl_vars['currentJournal']->getSetting('issuePerVolume')); ?>
            			<li class="meta"><span class="c-meta__item"><span class="bold">Frequency:</span> <?php if (((is_array($_tmp=$this->_tpl_vars['issuePerVolume'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)) == 1): ?>Annually<?php elseif (((is_array($_tmp=$this->_tpl_vars['issuePerVolume'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)) == 2): ?>Semiannually<?php elseif (((is_array($_tmp=$this->_tpl_vars['issuePerVolume'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)) == 3): ?>Quarterly<?php elseif (((is_array($_tmp=$this->_tpl_vars['issuePerVolume'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)) == 4): ?>Quarterly<?php elseif (((is_array($_tmp=$this->_tpl_vars['issuePerVolume'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)) == 6): ?>Bimonthly<?php elseif (((is_array($_tmp=$this->_tpl_vars['issuePerVolume'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)) == 12): ?>Monthly<?php endif; ?> — (<span class="italic"><?php echo ((is_array($_tmp=$this->_tpl_vars['issuePerVolume'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
 in <?php echo ((is_array($_tmp=$this->_tpl_vars['volumePerYear'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 per year)</span></span></li>
            			
            			<li class="meta"><span class="c-meta__item"><span class="bold">Coverage <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.year"), $this);?>
:</span> <?php echo ((is_array($_tmp=$this->_tpl_vars['firstYear'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if (((is_array($_tmp=$this->_tpl_vars['lastYear'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?> <i>to present</i><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['lastYear'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></span></li>
            			<li class="meta"><span class="c-meta__item"><span class="bold">Coverage <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
:</span> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['firstVolume'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 — <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['lastVolume'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 (<span class="italic">present</span>)</span></li>
            			<?php else: ?>
            			<li class="meta"><span class="c-meta__item"><span class="bold">First Published:</span> <span class="italic">not available</span></span></li>
            			<li class="meta"><span class="c-meta__item"><span class="bold">Coverage <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.year"), $this);?>
:</span> <span class="italic">has not published any issues</span></span></li>
            			<li class="meta"><span class="c-meta__item"><span class="bold">Coverage <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
:</span> <span class="italic">has not published any issues</span></span></li>
            			<?php endif; ?>
                        <?php if ($this->_tpl_vars['currentJournal']->getSetting('copyrightYearBasis')): ?>
                        <li class="meta"><span class="c-meta__item"><span class="bold">Publishing Model:</span> Publish-as-you-go</span></li>
                        <?php endif; ?>
						</ul>
										
							<div class="u-journalinsights u-font-sanss publishing-options__Wrapper-sc-5j168a-0 hpQXlC">
							    <span class="ublishing-options__Label-sc-5j168a-1 dWYPSC u-fonts">Publishing options:</span>
							    <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN): ?>
							    <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'openAccessPolicy'), $this);?>
" data-aa-name="Publishing options open access" data-aa-region="header" class="tag__TextWrapper-sc-1fw5i3t-5-a tag__AnchorWrapper-sc-1fw5i3t-6 hFFtfC jHbgct header-publishing-options-open-access"><span aria-hidden="false" aria-label="Open Access" data-color="gold" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig icNxLig"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">OA</span></span><span class="tag__LabelText-sc-1fw5i3t-2 kqUCww">Open Access</span><svg width="1em" height="1em" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="tag__ExternalLinkIcon-sc-1fw5i3t-7 hQLDKg"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg></a>
							    <?php endif; ?>
											
								<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?>
								<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'delayedOpenAccessPolicy'), $this);?>
" data-aa-name="Publishing options subscription" data-aa-region="header" class="tag__TextWrapper-sc-1fw5i3t-5-a tag__AnchorWrapper-sc-1fw5i3t-6 hFFtfC jHbgct header-publishing-options-subscription"><span aria-hidden="false" aria-label="Subscription" data-color="silver" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">S</span></span><span class="tag__LabelText-sc-1fw5i3t-2 kqUCww">Subscription</span><svg width="1em" height="1em" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="tag__ExternalLinkIcon-sc-1fw5i3t-7 hQLDKg"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg></a>

								<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'openAccessPolicy'), $this);?>
" data-aa-name="Publishing options open access" data-aa-region="header" class="tag__TextWrapper-sc-1fw5i3t-5-a tag__AnchorWrapper-sc-1fw5i3t-6 hFFtfC jHbgct header-publishing-options-open-access"><span aria-hidden="false" aria-label="Open Access" data-color="gold" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig icNxLig"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">OA</span></span><span class="tag__LabelText-sc-1fw5i3t-2 kqUCww">Open Access</span><svg width="1em" height="1em" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="tag__ExternalLinkIcon-sc-1fw5i3t-7 hQLDKg"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg></a>
								<?php endif; ?>
							</div>
						</div>		

						<?php if ($this->_tpl_vars['issue']): ?>
						<?php $this->assign('firstYear', $this->_tpl_vars['currentJournal']->getSetting('initialYear')); ?>
					    <?php $this->assign('lastYear', $this->_tpl_vars['issue']->getYear()); ?>
					    <?php $this->assign('lastVolume', $this->_tpl_vars['issue']->getVolume()); ?>
						<?php $this->assign('lastNumber', $this->_tpl_vars['issue']->getNumber()); ?>
						<div class="column medium-4 insight">
						    <div class="insight-box box">
						        <section class="teaser u-jour-insight u-font-sans">
						            <div class="js-title title insight-head">Latest <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
</div>
									<?php if ($this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>
									<div class="contents">
									    <div class="insight-data"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'current'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['lastVolume'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['issue']->getNumber()): ?>, <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['lastNumber'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></a></div>
									<?php endif; ?>
																		<?php $this->assign('currentYear', ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
									<?php $this->assign('currentMonth', ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%m") : smarty_modifier_date_format($_tmp, "%m"))); ?>
									
																		<?php $this->assign('issueYear', ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
									<?php $this->assign('issueMonth', ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%m") : smarty_modifier_date_format($_tmp, "%m"))); ?>
									<?php if ($this->_tpl_vars['currentYear'] == $this->_tpl_vars['issueYear']): ?>
									    <?php if ($this->_tpl_vars['currentMonth'] <= $this->_tpl_vars['issueMonth']): ?>
									    <div class="insight-tip in-progress"><i>in progress</i></div>
									    <?php else: ?>
									    <div class="insight-tip"><i>Completed</i></div>
									    <?php endif; ?>
									<?php else: ?>
									<div class="insight-tip Halted"><i>Halted </i></div>
									<?php endif; ?>
									<p class="insight-date"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%B %Y") : smarty_modifier_date_format($_tmp, "%B %Y")); ?>
</p>
									</div>
								</section>
							</div>
						</div>
                    	<?php endif; ?>
					</div>

					<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('history') != ''): ?>
					<div id="journal-history-link" class="u-mb-32"><span class="icon-container info">This journal was previously published under other titles <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'history','anchor' => ""), $this);?>
">(view Journal <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.history"), $this);?>
)</a></span>
					</div>
					<?php endif; ?>

					<?php if ($this->_tpl_vars['journalDescription']): ?>
					<div class="about-journal"><?php echo $this->_tpl_vars['journalDescription']; ?>
</div>
					<?php endif; ?>
					
					<?php if ($this->_tpl_vars['additionalHomeContent']): ?>
					<div class="u-hide journal-contents"><?php echo $this->_tpl_vars['additionalHomeContent']; ?>
</div>
					<?php endif; ?>

				</div>

				<aside class="sangia-aside column medium-4" role="side">
				    <section class="cta-aside-section">
				        <section class="sangia-button submission"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','op' => 'submit'), $this);?>
" target="_blank" data-track="click" class="button-base-47711979"><span class="button-label-2770091062">Submit your paper</span><svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1494494357"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg></a>
				        </section>

						<div class="sangia-box bbnsMx u-font-sangia-sans">
						    <?php if ($this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE && $this->_tpl_vars['currentJournal']->getLocalizedSetting('pubFreqPolicy') != ''): ?>
						    <ul class="sc-391xmi-0 hlGAVI">
						        <?php if ($this->_tpl_vars['alternatePageHeader']): ?>
						        <li class="sc-391xmi-1 eEgBpA"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 78 128" height="1em" width="1em" aria-hidden="true"><path d="M39 52C18.013 52 1 69.013 1 90s17.013 38 38 38 38-17.013 38-38-17.013-38-38-38zm0 66c-15.464 0-28-12.536-28-28s12.536-28 28-28 28 12.536 28 28-12.536 28-28 28zM1 8.72v9.487l26.689 27.138c4.252-1.067 8.336-1.481 12.613-1.356l-1.12-1.167h-.006L11.622 14.853c16.698-6.248 37.036-6.514 55.037.066L44.782 37.127l6.922 7.243L77 18.737V8.454C53-3.15 21-2.169 1 8.72zM29 82h6v26h10V72H29z" fill="currentColor"></path></svg><span>The Sinta Score of this journal is <span class="SintaScore"><?php echo $this->_tpl_vars['alternatePageHeader']; ?>
</span>. Sinta: Science and Technology Index by <em>Ministry of Education, Research and Technology, Republic of Indonesia</em>.</span>
						        </li>
						        <li class="sc-391xmi-1 eEgBpA"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 104 128" height="1em" width="1em" aria-hidden="true"><path d="M51.832 11.982c-28.168 0-51.002 22.834-51.002 51s22.834 51 51 51c24.624 0 45.17-17.45 49.952-40.656 6.392-33.232-18.762-61.344-49.95-61.344zm-25.08 18.612a40.794 40.794 0 0115.21-7.366c.394 3.9.68 7.83.75 10.76-4.036-.686-9.334.084-15.168 5.188-.76-3.49-.828-6.808-.79-8.58zm43.994 5.368c-1.826 6.384-2.996 15.16 1.478 21.494 6.786 9.61 21.162 6.396 19.77 13.808-3.83 18.674-20.356 32.718-40.162 32.718-32.65 0-52.144-36.4-34.23-63.544 1.44 7.378 4.976 13.42 10.358 19.548-1.682 1.92-4.378 4.266-4.824 6.986-2.37 14.44 9.768 17.2 14.284 18.38.608.16 1.38.36 1.842.5 2.318 4.35 5.108 12.24 11.52 12.24 8.792 0 15.148-17.72 14.392-28.584-.766-11.02-16.8-17.148-28.276-14.544a48.454 48.454 0 01-4.958-6.106c9.734-10.94 11.348-.09 17.5-3.972 2.08-1.31 4.636-2.928 2.468-22.9a40.717 40.717 0 0115.86 3.256l.012-.03a41.496 41.496 0 0120.054 18.16l-.042.024a40.68 40.68 0 014.574 13.73c-8.498-3.374-14.106-2.536-13.152-12.51a33.256 33.256 0 00-8.468-8.654zm-37.742 32.63c1.24-7.534 21.828-3.66 22.194 1.608.422 6.08-2.216 13.11-4.296 16.444-4.09-7.14-2.836-8.848-10.956-10.966-4.694-1.226-7.836-1.64-6.942-7.086z" fill="currentColor"></path></svg><span><i><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</i> is indexed in national and international databases, your published article can be read and cited by researchers worldwide.</span>
						        </li>
						        <?php elseif ($this->_tpl_vars['currentJournal']->getLocalizedSetting('pubFreqPolicy') != ''): ?>
						        <li class="sc-391xmi-1 eEgBpA"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 104 128" height="1em" width="1em" aria-hidden="true"><path d="M51.832 11.982c-28.168 0-51.002 22.834-51.002 51s22.834 51 51 51c24.624 0 45.17-17.45 49.952-40.656 6.392-33.232-18.762-61.344-49.95-61.344zm-25.08 18.612a40.794 40.794 0 0115.21-7.366c.394 3.9.68 7.83.75 10.76-4.036-.686-9.334.084-15.168 5.188-.76-3.49-.828-6.808-.79-8.58zm43.994 5.368c-1.826 6.384-2.996 15.16 1.478 21.494 6.786 9.61 21.162 6.396 19.77 13.808-3.83 18.674-20.356 32.718-40.162 32.718-32.65 0-52.144-36.4-34.23-63.544 1.44 7.378 4.976 13.42 10.358 19.548-1.682 1.92-4.378 4.266-4.824 6.986-2.37 14.44 9.768 17.2 14.284 18.38.608.16 1.38.36 1.842.5 2.318 4.35 5.108 12.24 11.52 12.24 8.792 0 15.148-17.72 14.392-28.584-.766-11.02-16.8-17.148-28.276-14.544a48.454 48.454 0 01-4.958-6.106c9.734-10.94 11.348-.09 17.5-3.972 2.08-1.31 4.636-2.928 2.468-22.9a40.717 40.717 0 0115.86 3.256l.012-.03a41.496 41.496 0 0120.054 18.16l-.042.024a40.68 40.68 0 014.574 13.73c-8.498-3.374-14.106-2.536-13.152-12.51a33.256 33.256 0 00-8.468-8.654zm-37.742 32.63c1.24-7.534 21.828-3.66 22.194 1.608.422 6.08-2.216 13.11-4.296 16.444-4.09-7.14-2.836-8.848-10.956-10.966-4.694-1.226-7.836-1.64-6.942-7.086z" fill="currentColor"></path></svg><span><i><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</i> is a NEW journal an bring to index in national and international databases, your published article can be read and cited by researchers worldwide.</span>
						        </li>
						        <li class="sc-391xmi-1 eEgBpA"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 113 128" height="1em" width="1em" aria-hidden="true"><path d="M26 14v16H2v66h10V40h14v64H2v10h64V49.47l12.39 64.73 33.35-6.44-15.42-80.31L66 33.29V14H26zm10 10h20v26H44v10h12v44H36V24zm52.3 15.15l11.69 60.69-13.75 2.64L74.55 41.8l13.75-2.65z"></path></svg><span><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('pubFreqPolicy'))) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 150, "") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 150, "")); ?>
</span>
						        </li>
						        <?php elseif ($this->_tpl_vars['currentJournal']->getLocalizedSetting('focusScopeDesc') != ''): ?>
						        <li class="sc-391xmi-1 eEgBpA"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 104 128" height="1em" width="1em" aria-hidden="true"><path d="M51.832 11.982c-28.168 0-51.002 22.834-51.002 51s22.834 51 51 51c24.624 0 45.17-17.45 49.952-40.656 6.392-33.232-18.762-61.344-49.95-61.344zm-25.08 18.612a40.794 40.794 0 0115.21-7.366c.394 3.9.68 7.83.75 10.76-4.036-.686-9.334.084-15.168 5.188-.76-3.49-.828-6.808-.79-8.58zm43.994 5.368c-1.826 6.384-2.996 15.16 1.478 21.494 6.786 9.61 21.162 6.396 19.77 13.808-3.83 18.674-20.356 32.718-40.162 32.718-32.65 0-52.144-36.4-34.23-63.544 1.44 7.378 4.976 13.42 10.358 19.548-1.682 1.92-4.378 4.266-4.824 6.986-2.37 14.44 9.768 17.2 14.284 18.38.608.16 1.38.36 1.842.5 2.318 4.35 5.108 12.24 11.52 12.24 8.792 0 15.148-17.72 14.392-28.584-.766-11.02-16.8-17.148-28.276-14.544a48.454 48.454 0 01-4.958-6.106c9.734-10.94 11.348-.09 17.5-3.972 2.08-1.31 4.636-2.928 2.468-22.9a40.717 40.717 0 0115.86 3.256l.012-.03a41.496 41.496 0 0120.054 18.16l-.042.024a40.68 40.68 0 014.574 13.73c-8.498-3.374-14.106-2.536-13.152-12.51a33.256 33.256 0 00-8.468-8.654zm-37.742 32.63c1.24-7.534 21.828-3.66 22.194 1.608.422 6.08-2.216 13.11-4.296 16.444-4.09-7.14-2.836-8.848-10.956-10.966-4.694-1.226-7.836-1.64-6.942-7.086z" fill="currentColor"></path></svg><span><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('focusScopeDesc'))) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
						        </li>
						        <?php endif; ?>
						    </ul>
						    <?php else: ?>
						    <p class="jour-profile u-font-sangia-sans"><span class="italic"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span> is a NEW journal an bring to index in national and international databases, your published article can be read and cited by researchers worldwide.</p>      
						    </p>
						    <?php endif; ?>
						</div>
						<?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>
						<section class="sangia-box cta-aside-section"><a class="button-base-159610158" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'titles'), $this);?>
" target="_blank" data-track="click"><span class="button-label-2658192883">View All Articles</span><svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1925105232"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg></a>
						</section>
						<?php endif; ?>

						<?php if ($this->_tpl_vars['displayPageHeaderLogo'] && is_array ( $this->_tpl_vars['displayPageHeaderLogo'] )): ?>
						<div class="box u-box-sangia">
						    <h4 class="headline-424997076">Societies, partners and affiliations</h4>
						    <?php if (! ( $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('publisherNote') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('contributorNote') == '' && empty ( $this->_tpl_vars['journalSettings']['contributors'] ) && $this->_tpl_vars['currentJournal']->getLocalizedSetting('sponsorNote') == '' && empty ( $this->_tpl_vars['journalSettings']['sponsors'] ) )): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'journalSponsorship'), $this);?>
" class="society-link"><img class="lazyload society-logo" title="Societies, partners and affiliations of this Journal" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderLogo']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" width="auto" height="<?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderLogo']['height'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['displayPageHeaderLogoAltText'] != ''): ?>alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderLogoAltText'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?>alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.pageHeaderLogo.altText"), $this);?>
"<?php endif; ?> loading="lazy" /></a><?php endif; ?>
						    <div>Find out more about Societies, partners and affiliations</div>
						    <a title="Societies, partners and affiliations" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'journalSponsorship'), $this);?>
" class="button-base-3588540778"><span class="button-label-1262423735">Find out more</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-248642068"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
						</div>
						<?php endif; ?>

						<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE && $this->_tpl_vars['currentJournal']->getSetting('currency')): ?>

						<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/payment.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
						
						<?php endif; ?>					</section>	
				</aside>
				<div class="column medium-12 u-mt-32"></div>
			</div>
		</div>
		</div>	
	</section>    
  </div>
</section>


<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN): ?>
<div class="u-container position-relative">
  	<div data-test="transformative-journal-announcement" class="c-status-message c-status-message--info <?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>u-mt-32 u-mb-0<?php else: ?> u-mb-32<?php endif; ?> c-status-message--boxed"><svg class="c-status-message__icon c-status-message__icon--top" width="24" height="24" aria-hidden="true" focusable="false" role="img"><use xlink:href="#icon-info"><symbol id="icon-info" viewBox="0 0 18 18"><path d="m9 0c4.9705627 0 9 4.02943725 9 9 0 4.9705627-4.0294373 9-9 9-4.97056275 0-9-4.0294373-9-9 0-4.97056275 4.02943725-9 9-9zm0 7h-1.5l-.11662113.00672773c-.49733868.05776511-.88337887.48043643-.88337887.99327227 0 .47338693.32893365.86994729.77070917.97358929l.1126697.01968298.11662113.00672773h.5v3h-.5l-.11662113.0067277c-.42082504.0488782-.76196299.3590206-.85696816.7639815l-.01968298.1126697-.00672773.1166211.00672773.1166211c.04887817.4208251.35902055.761963.76398144.8569682l.1126697.019683.11662113.0067277h3l.1166211-.0067277c.4973387-.0577651.8833789-.4804365.8833789-.9932723 0-.4733869-.3289337-.8699473-.7707092-.9735893l-.1126697-.019683-.1166211-.0067277h-.5v-4l-.00672773-.11662113c-.04887817-.42082504-.35902055-.76196299-.76398144-.85696816l-.1126697-.01968298zm0-3.25c-.69035594 0-1.25.55964406-1.25 1.25s.55964406 1.25 1.25 1.25 1.25-.55964406 1.25-1.25-.55964406-1.25-1.25-1.25z" fill-rule="evenodd"></path></symbol></use></svg>
  	    <div class="u-flex-shrink" data-track-component="tj-announcement">
  			<p class="u-mb-0 u-fonts-sans"><span class="italic"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span> is a <a data-track="click" data-track-action="click transformative journals link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'librarians'), $this);?>
">Transformative Journal</a>; authors can publish using gold Open Access.</p>
  			<p class="u-fonts-sans">Our Open Access option complies with <a data-track="click" data-track-action="click funding link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'openAccessPolicy'), $this);?>
">Open Access Policy</a>.</p>
  		</div>
  	</div>
</div>
<?php endif; ?>

<section class="live-area-wrapper">
<div class="live-area">
  	<section class="row raw">
  		<div class="columns medium-12 main-contents c-slice-heading u-hide">
  			<h2 class="headline"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h2>
  		</div>
  		<section class="medium-8 teaser-navigation" role="main">
  		<section class="column medium-3">
  			<h4 class="headline-3332373131"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForReaders"), $this);?>
</h4>
  			<nav class="journal-subnav journal-subnav--1"><div class="live">
  				<ul class="ul">
  				    <li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'titles'), $this);?>
"><span class="anchor-text">View Articles</span></a></li>
  				    <li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification','op' => 'subscribeMailList'), $this);?>
"><span class="anchor-text">Volume/Issue Alert</span></a></li>
	  				<li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'readers'), $this);?>
"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForReaders.long"), $this);?>
</span></a></li>
  				</ul>
  			</div>
  			</nav>
  		</section>
  		<section class="column medium-3">
  			<h4 class="headline-3332373131"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForAuthors"), $this);?>
</h4>
  			<nav class="journal-subnav journal-subnav--1"><div class="live">
  				<ul class="ul">
  					<li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','op' => 'submit'), $this);?>
"><span class="anchor-text">Submit your Paper</span></a></li>
  					<li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'onlineSubmissions'), $this);?>
"><span class="anchor-text">Guide for Submission</span></a></li>
  					<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('focusScopeDesc') != ''): ?><li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'focusAndScope'), $this);?>
"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.focusAndScope"), $this);?>
</span></a></li><?php endif; ?>
  					<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('pubFreqPolicy') != ''): ?><li class="sub_menu"><a class="anchor"  href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'publicationFrequency'), $this);?>
"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.publicationFrequency"), $this);?>
</span></a></li><?php endif; ?>
  					<li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'submissionPreparationChecklist'), $this);?>
"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.submissionPreparationChecklist"), $this);?>
</span></a></li>
  					<li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'authors'), $this);?>
"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForAuthors.long"), $this);?>
</span></a></li>
  					<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION && $this->_tpl_vars['currentJournal']->getSetting('enableAuthorSelfArchive')): ?><li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'authorSelfArchivePolicy'), $this);?>
"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.authorSelfArchive"), $this);?>
</span></a></li><?php endif; ?>
  					<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('copyrightNotice') != ''): ?><li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'copyrightNotice'), $this);?>
"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.copyrightNotice"), $this);?>
</span></a></li><?php endif; ?>
  					<li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'contact'), $this);?>
"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact"), $this);?>
</span></a></li>
  				</ul>
  			</div>
  			</nav>
  		</section>
  		<section class="column medium-3">
  			<h4 class="headline-3332373131"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForLibrarians"), $this);?>
</h4>
  			<nav class="journal-subnav journal-subnav--1"><div class="live">
  				<ul class="ul">
  				    <li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'archiving'), $this);?>
"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.archiving"), $this);?>
</span></a></li>
  					<li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'librarians'), $this);?>
"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForLibrarians.long"), $this);?>
</span></a></li>
  				</ul>
  			</div>
  			</nav>
  		</section>
  		<section class="column medium-3">
  			<h4 class="headline-3332373131">Editors and Reviewers</h4>
  			<nav class="journal-subnav journal-subnav--1"><div class="live">
  				<ul class="ul">
			      <li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'peerReviewProcess'), $this);?>
"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.peerReviewProcess"), $this);?>
</span></a></li>
			      <?php $_from = $this->_tpl_vars['currentJournal']->getLocalizedSetting('customAboutItems'); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['customAboutItems'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['customAboutItems']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['customAboutItem']):
        $this->_foreach['customAboutItems']['iteration']++;
?>
			        <?php if (! empty ( $this->_tpl_vars['customAboutItem']['title'] )): ?><li class="sub_menu"><a class="anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['customAboutItem']['title'])) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', "") : smarty_modifier_replace($_tmp, ' ', "")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
"><span class="anchor-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['customAboutItem']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></a></li><?php endif; ?>
			      <?php endforeach; endif; unset($_from); ?>
			      <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::Policies"), $this);?>

  				</ul>
  			</div>
  			</nav>
  		</section>
		</section>
		<aside class="sangia-aside column medium-4">
		    <section class="box2-321956623 box">
			<h4 class="headline-424997076">Publish with us</h4>
			<div><p>Find out more about how to publish in this journal<br></p></div><a title="Submission" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'authors'), $this);?>
" class="button-base-3479930902"><span class="button-label-256261418">More Information</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-1913844361"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
			</section>
			<?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>
			<section class="sign-up-form box2-321956623 box"><div>
				<div id="alert-widget_body">
					<div class="sign-up"><h4>Get informed about updates</h4>
					<p>Receive updates for all new issues/articles published <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
.</p><a id="signUpSRMPub" class="sangia-button" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification','op' => 'subscribeMailList'), $this);?>
"><button type="submit"><span>Sign Up for Alerts</span><svg width="32" height="32" viewBox="0 0 32 32"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></button></a>
				    </div>
				</div></div>
			</section>
			<?php endif; ?>
		</aside>  
	</section>	
</div>
</section>

</div>
<div class="editors-socialMedia">
	<section class="live-area-wrapper <?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>cms-highlight-2<?php else: ?><?php if ($this->_tpl_vars['enableAnnouncementsHomepage']): ?><?php else: ?>cms-highlight<?php endif; ?><?php endif; ?>">
		<div class="live-area">
		  	<div class="row raw">
		  		<div class="column medium-12">
		  			<h2 class="headline-1283242569">Stay informed</h2>
		  		</div>
		  	</div>	
		    <div class="row raw">
			    <div class="columns small-12 ">
				<div class="cms-collection-list">
					<ul class="small-block-grid-1 medium-block-grid-3">
						<li>
							<div id="id19" class=""><a class="cms-teaser-text" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialTeam'), $this);?>
" target="_blank"><div class="cms-teaser-box cms-teaser-box-with-icon cms-teaser-box-titled-icon" data-mh="mh-id3" ><div class="article-meta"></div><div class="cms-font-icon">N</div><h3>Contact an Editor</h3></div></a></div></li>
						<li>
							<div id="id1a" class=""><a class="cms-teaser-text" href="//twitter.com/SangiaNews" target="_blank"><div class="cms-teaser-box cms-teaser-box-with-icon" data-mh="mh-id3" ><div class="article-meta"></div><div class="cms-font-icon">1</div><h3>Follow @Sangia_Publishing</h3><p>Follow Sangia Publishing Journals</p></div></a></div></li>
						<li>
							<div id="id1b" class=""><a class="cms-teaser-text" href="//www.facebook.com/sangiapublishing" target="_blank"><div class="cms-teaser-box cms-teaser-box-with-icon" data-mh="mh-id3" ><div class="article-meta"></div><div class="cms-font-icon">2</div><h3>Sangia Publishing group</h3><p>Follow Fanpage Sangia Publishing group</p></div></a></div></li>
					</ul>
				</div>
			</div>
		    </div>
        </div>
    </section>
</div>

<section class="u-container u-mb-32">
	<div class="live-area">
		<div class="row">					
    	    <?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution')): ?>
    	    <div class="membership medium-6 u-mb-32">
    	        <section class="membership__item">
    	            <h3 class="headfoot u-mb-16">Copyright</h3>
    	            <div class="c-meta__copyright text-s">Copyright © <?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>
 <?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Research Media and Publishing' || $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Publishing'): ?> <?php echo $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'); ?>
<?php else: ?><?php echo $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'); ?>
. Production & hosting by Sangia Publishing<?php endif; ?>, under <a href="<?php echo $this->_tpl_vars['currentJournal']->getSetting('licenseURL'); ?>
" target="_blank">Creative Commons Attribution Licensed</a>.<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('copyrightNotice') != ''): ?> <span class="popup">Go to <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'copyrightNotice'), $this);?>
" target="_blank"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.copyrightNotice"), $this);?>
</a> for more information about copyrights.</span><?php endif; ?></div>
    	        </section>
    	    </div>
    	    <?php endif; ?>
			
    	    <?php $this->assign('contactMailingAddress', $this->_tpl_vars['currentJournal']->getLocalizedSetting('contactMailingAddress')); ?>
    	    <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('contactMailingAddress')): ?>
    	    <div class="membership medium-6 u-mb-32">
    	        <section class="membership__item">
    	            <h3 class="headfoot u-mb-16">Editorial Office</h3>
    	            <div class="c-meta__editorial text-s"><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('contactMailingAddress'))) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>
</div>
    	        </section>
    	    </div>
    		<?php endif; ?>
			
			<?php if ($this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE && $this->_tpl_vars['alternatePageHeader']): ?>
			<div class="indexedin u-mt-16">
				<ul class="indexedin-link">
					<li class="indexedin-item" data-test="logo-listing"><a href="https://garuda.kemdikbud.go.id/journal/" target="_blank">
					    <picture>
					        <source srcset="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/img/static/garuda.png?as=webp" type="image/webp">
					        <img class="garuda u-mb-0" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/img/static/garuda.png" loading="lazy" alt="Garba Rujukan Digital" />
					    </picture>
					    </a></li>
					<li class="indexedin-item" data-test="logo-listing"><a href="http://www.crossref.org/" target="_blank">
					    <picture>
					        <source srcset="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/img/static/indexedin_06.png?as=webp" type="image/webp">
					        <img class="crossref u-mb-0" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/img/static/indexedin_06.png" loading="lazy" alt="CrossRef" />
					    </picture>
					    </a></li>
					<li class="indexedin-item" data-test="logo-listing"><a href="http://sinta.kemdikbud.go.id/journals" target="_blank">
					    <picture>
					        <source srcset="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/img/static/sinta.png?as=webp" type="image/webp">
					        <img class="sinta u-mb-0" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/img/static/sinta.png" loading="lazy" alt="Sinta" />
					    </picture>
					    </a></li>
					<li class="indexedin-item" data-test="logo-listing"><a href="http://scholar.google.com/" target="_blank">
					    <picture>
					        <source srcset="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/img/static/indexedin_02.gif?as=webp" type="image/webp">
					        <img class="scholar u-mb-0" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/img/static/indexedin_02.gif" loading="lazy" alt="Google Scholar" />
					    </picture>
					    </a></li>
				</ul>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>    

<section id="cope-ithenticate-container" class="u-container u-mb-48">
    <ul class="app-membership-row">
        <li class="app-membership-row__item" data-test="logo-listing">
            <a href="http://publicationethics.org/" data-test="logo-link" target="_blank">
                <picture>
                    <source srcset="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/img/static/logos/cope-8d92c4b829.png?as=webp" type="image/webp">
                    <img class="u-mb-0" alt="Committee on Publication Ethics" data-test="logo-image" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/img/static/logos/cope-8d92c4b829.png" loading="lazy">
                </picture>
            </a>
            <p class="c-meta u-mt-16" data-test="logo-label">This journal is a member of and subscribes to the principles of the Committee on Publication Ethics.</p>
        </li>
        <li class="app-membership-row__item" data-test="logo-listing">
            <a href="http://www.ithenticate.com/" data-test="logo-link" target="_blank">
                <picture>
                    <source srcset="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/img/static/logos/ithenticate-eabe2377a3.png?as=webp" type="image/webp">
                    <img class="u-mb-0" alt="Ithenticate Plagiarism Detection" data-test="logo-image" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/img/static/logos/ithenticate-eabe2377a3.png" loading="lazy">
                </picture>
            </a>
            <p class="c-meta u-mt-16" data-test="logo-label"></p>
        </li>
    </ul>
</section>
    
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>