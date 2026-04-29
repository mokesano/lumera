<?php /* Smarty version 2.6.26, created on 2026-04-04 06:08:33
         compiled from common/featured/article_Hero.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'common/featured/article_Hero.tpl', 37, false),array('modifier', 'date_format', 'common/featured/article_Hero.tpl', 71, false),array('modifier', 'strip_tags', 'common/featured/article_Hero.tpl', 93, false),array('modifier', 'capitalize', 'common/featured/article_Hero.tpl', 144, false),array('modifier', 'replace', 'common/featured/article_Hero.tpl', 144, false),array('function', 'translate', 'common/featured/article_Hero.tpl', 38, false),array('function', 'url', 'common/featured/article_Hero.tpl', 93, false),)), $this); ?>
<?php 
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($articleHeroFile = 'plugins/themes/' . $matches[1] . '/php/hero_futured/article_hero.php')) {
        include_once($articleHeroFile);
        break;
    }
}
 ?>

<?php if ($this->_tpl_vars['heroArticle'] && count ( $this->_tpl_vars['heroArticle'] ) > 0): ?>
<?php $_from = $this->_tpl_vars['heroArticle']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['article']):
?>
<article>
    <div data-track-component="hero" class="u-container content-loaded">
        <div class="c-hero c-hero--flush-md-max">
                        <?php $this->assign('displayHomepageImage', $this->_tpl_vars['currentJournal']->getLocalizedSetting('homepageImage')); ?>
            <?php if ($this->_tpl_vars['article']['cover_image']['file_exists']): ?>
            <div class="c-hero__image">
	    	<picture><source type="image/webp" srcset="<?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
?as=webp 450w, <?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img src="<?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
" alt="" itemprop="image" loading="lazy">
	    	</picture>
	    	</div>
            <?php elseif ($this->_tpl_vars['issue']->getLocalizedFileName() && $this->_tpl_vars['issue']->getShowCoverPage($this->_tpl_vars['locale']) && ! $this->_tpl_vars['issue']->getHideCoverPageArchives($this->_tpl_vars['locale'])): ?>
  			<div class="c-hero__image">
  			<picture>
  				<source srcset="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getFileName($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 450w, <?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getFileName($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px" type="image/webp">
  				<img class="lazyload" loading="lazy" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getFileName($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if ($this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']) != ''): ?> title="Cover issue <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?> title="Cover issue"<?php endif; ?><?php if ($this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']) != ''): ?> alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?> alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.coverPage.altText"), $this);?>
"<?php endif; ?> itemprop="image">
  			</picture>
  			</div>
  			<?php elseif ($this->_tpl_vars['displayHomepageImage'] && is_array ( $this->_tpl_vars['displayHomepageImage'] )): ?>
  			<div class="c-hero__image">
  			<picture>
  				<source srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
?as=webp 450w, <?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px" type="image/webp">
  				<img class="lazyload" loading="lazy" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" title="" alt="" itemprop="image">
  			</picture>
  			</div>
  			<?php else: ?>
  			<div class="c-hero__image">
  			<picture>
  				<source srcset="//assets.sangia.org/img/img-default_v2.png?as=webp 450w, //assets.sangia.org/img/img-default_v2.png?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px" type="image/webp">
  				<img class="lazyload" loading="lazy" src="//assets.sangia.org/img/img-default_v2.png" title="" alt="" itemprop="image">
  			</picture>
  			</div>
	    	<?php endif; ?>
	    	<div class="c-hero__copy">
	    	    	    	    <h2 class="c-hero__title" itemprop="name headline">
	    	        <a class="c-hero__link u-link-faux-block" href="<?php echo $this->_tpl_vars['article']['article_url']; ?>
" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo $this->_tpl_vars['article']['title']; ?>
</a>
	            </h2>
	            	            <?php if ($this->_tpl_vars['article']['abstract']): ?>
	            <p class="c-hero__summary ellipsis u-mb-8" itemprop="description"><?php echo $this->_tpl_vars['article']['abstract']; ?>
</p>
	            <?php endif; ?>
	            	            <?php if ($this->_tpl_vars['article']['authors'] && count ( $this->_tpl_vars['article']['authors'] ) > 0): ?>
	            <ul class="c-author-list c-author-list--compact c-author u-hide-sm-max u-mb-4 u-mt-auto" data-test="author-list"><?php $_from = $this->_tpl_vars['article']['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authorLoop']['iteration']++;
?><li itemprop="creator" itemscope="name" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="name"><?php echo $this->_tpl_vars['author']['full_name']; ?>
</span><?php if ($this->_tpl_vars['author']['first_name'] !== $this->_tpl_vars['author']['last_name']): ?><span itemprop="given-name"><?php echo $this->_tpl_vars['author']['first_name']; ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['author']['middle_name']): ?><span itemprop="middle-name"><?php echo $this->_tpl_vars['author']['middle_name']; ?>
</span><?php endif; ?><span itemprop="surname"><?php echo $this->_tpl_vars['author']['last_name']; ?>
</span></li><?php endforeach; endif; unset($_from); ?>
	            </ul>
	            <?php endif; ?>
	            				<div class="c-card__section c-meta u-hide-sm-max"><span class="c-meta__item" data-test="article.type"><span class="c-meta__type"><?php echo $this->_tpl_vars['article']['article_type']; ?>
</span></span><?php if ($this->_tpl_vars['article']['is_open_access']): ?><span class="c-meta__item" itemprop="openAccess" data-test="open-access">Open Access</span><?php endif; ?><time class="c-meta__item" datetime="<?php echo $this->_tpl_vars['article']['date_published_formatted']; ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']['date_published_formatted'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %B %Y") : smarty_modifier_date_format($_tmp, "%d %B %Y")); ?>
</time>
				</div>
			</div>
		</div>
	</div>
</article>
<?php endforeach; endif; unset($_from); ?>
<?php else: ?>
<article>
    <div data-track-component="hero" class="u-container">
        <div class="c-hero c-hero--flush-md-max u-mb-0 u-position-relative">
		    <div class="c-hero__image">
		        <?php if ($this->_tpl_vars['displayPageHeaderTitle'] && is_array ( $this->_tpl_vars['displayPageHeaderTitle'] )): ?>
		        <picture><source type="image/webp" srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderTitle']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
?as=webp 450w, <?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderTitle']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
?as=webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img class="lazyload" loading="lazy" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderTitle']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" alt="">
		        </picture>
		        <?php else: ?>
		        <picture><source type="image/webp" srcset="//assets.sangia.org/static/images/not-available.webp 450w, //assets.sangia.org/static/images/not-available.webp 735w" sizes="(max-width: 1024px) 450px,(max-width: 100vw) 735px 735px"><img class="lazyload" loading="lazy" src="//assets.sangia.org/static/images/not-available.webp" alt="">
		        </picture>
		        <?php endif; ?>
		    </div>
		    <div class="c-hero__copy">
		        <h2 class="c-hero__title">
		            <a class="c-hero__link u-link-faux-block" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'current'), $this);?>
" data-track="click" data-track-action="view announcement" data-track-label="link">Publish with <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
		        </h2>
		        <?php if ($this->_tpl_vars['metaSearchDescription']): ?>
		        <p class="c-hero__summary ellipsis"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['metaSearchDescription'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
		        <?php else: ?>
		        <p class="c-hero__summary ellipsis"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journalDescription'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
		        <?php endif; ?>
		    </div>
		</div>
	</div>
</article>
<?php endif; ?>

<?php if ($this->_tpl_vars['latestArticles'] && count ( $this->_tpl_vars['latestArticles'] ) > 0): ?>
<section id="featured-content" class="area-wrapper featured u-mb-32" data-track-component="featured content">
	<h2 class="u-visually-hidden">Featured Content</h2>
	<div class="u-container content-loaded">

<ul class="app-featured-row">
    <?php $_from = $this->_tpl_vars['latestArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['article']):
?>
	<li class="app-featured-row__item last">
	    <div class="u-full-height" data-native-ad-placement="false">
	        <article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
	            <div class="c-card__layout u-full-height">
	                	                <?php if ($this->_tpl_vars['article']['cover_image']['file_exists']): ?>
	                <div class="c-card__image">
					<picture>
						<source srcset="<?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
?as=webp 290w" type="image/webp" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
						<img class="lazyload" loading="lazy" src="<?php echo $this->_tpl_vars['article']['cover_image']['file_url']; ?>
" alt="" itemprop="image">
					</picture>
					</div>
					<?php endif; ?>
					<div class="c-card__body u-display-flex u-flex-direction-column">
					    					    <h3 class="c-card__title" itemprop="name headline"><a class="c-card__link u-link-inherit" href="<?php echo $this->_tpl_vars['article']['article_url']; ?>
" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo $this->_tpl_vars['article']['title']; ?>
</a>
					    </h3>
					    					    <?php if ($this->_tpl_vars['article']['abstract']): ?>
						<div class="c-card__summary ellips u-mb-16 u-hide-sm-max" itemprop="description"><p><?php echo $this->_tpl_vars['article']['abstract']; ?>
</p>
						</div>
						<?php endif; ?>
					</div>
				</div>
				<div class="c-card__section c-meta">
				    				    <?php if ($this->_tpl_vars['article']['authors'] && count ( $this->_tpl_vars['article']['authors'] ) > 0): ?>
					<ul class="c-author-list c-author-list--compact c-author-list--separated u-mb-4 u-mt-auto" data-test="author-list"><?php $_from = $this->_tpl_vars['article']['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authorLoop']['iteration']++;
?><li itemprop="creator" itemscope="name" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="name"><?php echo $this->_tpl_vars['author']['full_name']; ?>
</span><?php if ($this->_tpl_vars['author']['first_name'] !== $this->_tpl_vars['author']['last_name']): ?><span itemprop="given-name"><?php echo $this->_tpl_vars['author']['first_name']; ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['author']['middle_name']): ?><span itemprop="middle-name"><?php echo $this->_tpl_vars['author']['middle_name']; ?>
</span><?php endif; ?><span itemprop="surname"><?php echo $this->_tpl_vars['author']['last_name']; ?>
</span></li><?php endforeach; endif; unset($_from); ?>
					</ul>
					<?php endif; ?>
					<span class="c-meta__item" data-test="article.type"><span class="c-meta__type"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']['article_type'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('capitalize', true, $_tmp) : smarty_modifier_capitalize($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, 'Research', '') : smarty_modifier_replace($_tmp, 'Research', '')); ?>
</span></span><?php if ($this->_tpl_vars['article']['is_open_access']): ?><span class="c-meta__item" itemprop="openAccess" data-test="open-access"><span class="u-color-open-access">Open Access</span></span><?php endif; ?><time class="c-meta__item" datetime="<?php echo $this->_tpl_vars['article']['date_published_formatted']; ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']['date_published_formatted'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>
</time>
				</div>
			</article>
		</div>
	</li>
	<?php endforeach; endif; unset($_from); ?>
	<li class="app-featured-row__item app-featured-row__item--current-issue">
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
"<?php if ($this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']) != ''): ?> title="Cover issue <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?> title="Cover issue"<?php endif; ?><?php if ($this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']) != ''): ?> alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?> alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.coverPage.altText"), $this);?>
"<?php endif; ?> itemprop="image">
	  			</picture>
	  			<?php elseif ($this->_tpl_vars['displayHomepageImage'] && is_array ( $this->_tpl_vars['displayHomepageImage'] )): ?>
	  			<picture>
	  				<source srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
?as=webp" type="image/webp">
	  				<img class="lazyload" loading="lazy" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" title="" alt="Cover issue" itemprop="image">
	  			</picture>
	  			<?php else: ?>
	  			<picture>
	  				<source srcset="//assets.sangia.org/img/img-default_v2.png?as=webp" type="image/webp">
	  				<img class="lazyload" loading="lazy" src="//assets.sangia.org/img/img-default_v2.png" title="" alt="Cover issue" itemprop="image">
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
    	  	<div class="c-card__section c-meta"><span class="c-meta__item"><span class="c-meta__type"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "journal.currentIssue"), $this);?>
</span></span><time class="c-meta__item" datetime="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, ($this->_tpl_vars['dateFormatShort'])) : smarty_modifier_date_format($_tmp, ($this->_tpl_vars['dateFormatShort']))); ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
</time>
    	  	</div>
        </div>
    </li>
</ul>

	</div>
</section>
<?php endif; ?>