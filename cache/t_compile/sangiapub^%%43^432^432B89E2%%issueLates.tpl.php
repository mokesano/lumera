<?php /* Smarty version 2.6.26, created on 2026-04-04 06:08:34
         compiled from issue/issueLates.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'issue/issueLates.tpl', 46, false),array('modifier', 'strip_unsafe_html', 'issue/issueLates.tpl', 55, false),array('modifier', 'nl2br', 'issue/issueLates.tpl', 55, false),array('modifier', 'date_format', 'issue/issueLates.tpl', 80, false),array('modifier', 'to_array', 'issue/issueLates.tpl', 83, false),array('modifier', 'strip_tags', 'issue/issueLates.tpl', 88, false),array('modifier', 'string_format', 'issue/issueLates.tpl', 91, false),array('function', 'translate', 'issue/issueLates.tpl', 47, false),array('function', 'call_hook', 'issue/issueLates.tpl', 51, false),array('function', 'url', 'issue/issueLates.tpl', 55, false),)), $this); ?>

<?php $_from = $this->_tpl_vars['publishedArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sections'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sections']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['sectionId'] => $this->_tpl_vars['section']):
        $this->_foreach['sections']['iteration']++;
?>

<?php $_from = $this->_tpl_vars['section']['articles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['article']):
?>

	<?php $this->assign('articlePath', $this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal'])); ?>
	<?php $this->assign('articleId', $this->_tpl_vars['article']->getId()); ?>

	<?php if ($this->_tpl_vars['article']->getLocalizedFileName() && $this->_tpl_vars['article']->getLocalizedShowCoverPage() && ! $this->_tpl_vars['article']->getHideCoverPageToc($this->_tpl_vars['locale'])): ?>
		<?php $this->assign('showCoverPage', true); ?>
	<?php else: ?>
		<?php $this->assign('showCoverPage', false); ?>
	<?php endif; ?>

	<?php if ($this->_tpl_vars['article']->getLocalizedAbstract() == ""): ?>
		<?php $this->assign('hasAbstract', 0); ?>
	<?php else: ?>
		<?php $this->assign('hasAbstract', 1); ?>
	<?php endif; ?>

	<?php if (( ! $this->_tpl_vars['subscriptionRequired'] || $this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN || $this->_tpl_vars['subscribedUser'] || $this->_tpl_vars['subscribedDomain'] || ( $this->_tpl_vars['subscriptionExpiryPartial'] && $this->_tpl_vars['articleExpiryPartial'][$this->_tpl_vars['articleId']] ) )): ?>
		<?php $this->assign('hasAccess', 1); ?>
	<?php else: ?>
		<?php $this->assign('hasAccess', 0); ?>
	<?php endif; ?>
	
	<?php if ($this->_tpl_vars['hasAccess'] || ( $this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] ) || $this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] && $this->_tpl_vars['restrictOnly']): ?>

<li class="app-article-list-row__item 1">
	<div class="u-full-height" data-native-ad-placement="false">
		<article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
			<div class="c-card__layout u-full-heights">
				<?php if ($this->_tpl_vars['showCoverPage']): ?>
				<div class="c-card__image">
					<picture>
						<source srcset="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 290w" type="image/webp" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px" >
						<img class="lazyload" loading="lazy" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['coverPageAltText'] != ''): ?>alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.coverPage.altText"), $this);?>
"<?php else: ?>alt="Graphical abstract" <?php endif; ?>itemprop="image" />
					</picture>
				</div>
				<?php endif; ?>
				<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Article::Article::ArticleCoverImage"), $this);?>


				<div class="c-card__body u-display-flex u-flex-direction-column">
					<h3 class="c-card__title" itemprop="name headline">
						<a class="c-card__link u-link-inherit" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['articlePath']), $this);?>
" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</a>
					</h3>
					<?php if ($this->_tpl_vars['article']->getLocalizedAbstract()): ?>
					<div class="c-card__summary u-mb-16 u-hide-sm-max" itemprop="description"><p><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p></div>
					<?php endif; ?>
					
				<?php if (( ! $this->_tpl_vars['article']->getHideAuthor() == @AUTHOR_TOC_DEFAULT ) || $this->_tpl_vars['article']->getHideAuthor() == @AUTHOR_TOC_SHOW): ?>
				<?php else: ?>
				<ul class="c-author-list c-author-list--compact u-mt-auto" data-test="author-list"><?php $this->assign('authors', $this->_tpl_vars['article']->getAuthors()); ?><?php $_from = $this->_tpl_vars['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authors']['iteration']++;
?><?php $this->assign('fullname', $this->_tpl_vars['author']->getFullName()); ?><?php $this->assign('authorFirstName', $this->_tpl_vars['author']->getFirstName()); ?><?php $this->assign('authorMiddleName', $this->_tpl_vars['author']->getMiddleName()); ?><?php $this->assign('authorLastName', $this->_tpl_vars['author']->getLastName()); ?><li itemprop="creator" itemscope="name" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['fullname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['authorFirstName'] !== $this->_tpl_vars['authorLastName']): ?><span itemprop="given-name"><?php echo $this->_tpl_vars['authorFirstName']; ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['authorMiddleName']): ?><span itemprop="middle-name"><?php echo $this->_tpl_vars['authorMiddleName']; ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['authorLastName']): ?><span itemprop="surname"><?php echo $this->_tpl_vars['authorLastName']; ?>
</span><?php endif; ?></li><?php endforeach; endif; unset($_from); ?>
				</ul>
				<?php endif; ?>
				
				</div>
			</div>
			<div class="c-card__section c-meta">
				<?php if ($this->_tpl_vars['section']['title']): ?>
				<span class="c-meta__item c-meta__item--block-at-lg" data-test="article.type">
					<span class="c-meta__type"><?php echo ((is_array($_tmp=$this->_tpl_vars['section']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				</span>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN || $this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN): ?>
				<span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
					<span class="u-color-open-access">Open Access</span>
				</span>
				<?php endif; ?>
				<time class="c-meta__item c-meta__item--block-at-lg" datetime="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, ($this->_tpl_vars['dateFormatShort'])) : smarty_modifier_date_format($_tmp, ($this->_tpl_vars['dateFormatShort']))); ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>
</time>
    			<?php $_from = $this->_tpl_vars['article']->getGalleys(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['galleyList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['galleyList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['galley']):
        $this->_foreach['galleyList']['iteration']++;
?><?php if ($this->_tpl_vars['hasAccess'] || ( $this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] ) && $this->_tpl_vars['galley']->isPdfGalley()): ?>
            	<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="abstract-and-fulltext-info">
    				<span itemprop="url" id="toc-pdf-link" class="webtrekk-track pdf-link" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=$this->_tpl_vars['articlePath'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])))), $this);?>
" target="_blank" class="file"><?php if ($this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] && $this->_tpl_vars['restrictOnlyPdf']): ?><?php if ($this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN || ! $this->_tpl_vars['galley']->isPdfGalley() || $this->_tpl_vars['galley']->getRemoteURL()): ?>Download <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php elseif ($this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_SUBSCRIPTION): ?>Get <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 Access<?php endif; ?><?php else: ?>Download <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <span class="fileSize">(<?php echo $this->_tpl_vars['galley']->getNiceFileSize(); ?>
) <span><?php echo $this->_tpl_vars['galley']->getViews(); ?>
 views</span></span>
    			</div>
    			<?php endif; ?><?php endforeach; endif; unset($_from); ?>
    			<?php if ($this->_tpl_vars['hasAccess'] || ( $this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] )): ?>
    			<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="abstract-and-fulltext-info">
    				<span itemprop="url" title="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['articlePath']), $this);?>
"><?php if ($this->_tpl_vars['article']->getLocalizedAbstract()): ?>View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.abstract"), $this);?>
<?php else: ?>View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.details"), $this);?>
<?php endif; ?> <span class="fileView"><?php echo $this->_tpl_vars['article']->getViews(); ?>
 views</span></span>
    			</div>
    			<?php endif; ?>
				<span class="u-hide c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="article.pages"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.vol"), $this);?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if (((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?>, <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.no"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php if ($this->_tpl_vars['article']->getPages()): ?>, P: <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPages())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
<?php endif; ?></span>                
			</div>
		</article>
	</div>
</li>

<?php else: ?>

<li class="app-article-list-row__item 2">
	<div class="u-full-height" data-native-ad-placement="false">
		<article class="u-full-height c-card c-card--flush" itemscope="" itemtype="http://schema.org/ScholarlyArticle">
			<div class="c-card__layout u-full-heights">
				<?php if ($this->_tpl_vars['showCoverPage']): ?>
				<div class="c-card__image">
					<picture>
						<source srcset="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 290w" type="image/webp" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px" >
						<img class="lazyload" loading="lazy" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['coverPageAltText'] != ''): ?>alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.coverPage.altText"), $this);?>
"<?php else: ?>alt="Graphical abstract" <?php endif; ?>itemprop="image" />
					</picture>
				</div>		
				<?php endif; ?>
				<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Article::Article::ArticleCoverImage"), $this);?>

				<div class="c-card__body u-display-flex u-flex-direction-column">
					<h3 class="c-card__title" itemprop="name headline">
						<a class="c-card__link u-link-inherit" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['articlePath']), $this);?>
" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</a>
					</h3>
					<?php if ($this->_tpl_vars['article']->getLocalizedAbstract()): ?>
					<div class="c-card__summary u-mb-16 u-hide-sm-max" itemprop="description"><p><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p></div>
					<?php endif; ?>
    				<?php if (( ! $this->_tpl_vars['article']->getHideAuthor() == @AUTHOR_TOC_DEFAULT ) || $this->_tpl_vars['article']->getHideAuthor() == @AUTHOR_TOC_SHOW): ?>
    				<?php else: ?>
    				<ul class="c-author-list c-author-list--compact u-mt-auto" data-test="author-list"><?php $this->assign('authors', $this->_tpl_vars['article']->getAuthors()); ?><?php $_from = $this->_tpl_vars['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authors']['iteration']++;
?><?php $this->assign('fullname', $this->_tpl_vars['author']->getFullName()); ?><?php $this->assign('authorFirstName', $this->_tpl_vars['author']->getFirstName()); ?><?php $this->assign('authorMiddleName', $this->_tpl_vars['author']->getMiddleName()); ?><?php $this->assign('authorLastName', $this->_tpl_vars['author']->getLastName()); ?><li itemprop="creator" itemscope="name" itemtype="http://schema.org/Person"><span class="u-hide" itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['fullname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['authorFirstName'] !== $this->_tpl_vars['authorLastName']): ?><span itemprop="given-name"><?php echo $this->_tpl_vars['authorFirstName']; ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['authorMiddleName']): ?><span itemprop="middle-name"><?php echo $this->_tpl_vars['authorMiddleName']; ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['authorLastName']): ?><span itemprop="surname"><?php echo $this->_tpl_vars['authorLastName']; ?>
</span><?php endif; ?></li><?php endforeach; endif; unset($_from); ?>
    				<?php endif; ?>
				</div>
			</div>
			<div class="c-card__section c-meta">
				<?php if ($this->_tpl_vars['section']['title']): ?>
				<span class="c-meta__item c-meta__item--block-at-lg" data-test="article.type">
					<span class="c-meta__type"><?php echo ((is_array($_tmp=$this->_tpl_vars['section']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				</span>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN || $this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN): ?>
				<span class="c-meta__item c-meta__item--block-at-lg" itemprop="openAccess" data-test="open-access">
					<span class="u-color-open-access">Open Access</span>
				</span>
				<?php endif; ?>
				<time class="c-meta__item c-meta__item--block-at-lg" datetime="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, ($this->_tpl_vars['dateFormatShort'])) : smarty_modifier_date_format($_tmp, ($this->_tpl_vars['dateFormatShort']))); ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>
</time>
    			<?php $_from = $this->_tpl_vars['article']->getGalleys(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['galleyList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['galleyList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['galley']):
        $this->_foreach['galleyList']['iteration']++;
?>
            	<?php if ($this->_tpl_vars['hasAccess'] || ( $this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] ) && $this->_tpl_vars['galley']->isPdfGalley()): ?>
            	<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="abstract-and-fulltext-info">
    				<span itemprop="url" id="toc-pdf-link" class="webtrekk-track pdf-link" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=$this->_tpl_vars['articlePath'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])))), $this);?>
" target="_blank" class="file"><?php if ($this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] && $this->_tpl_vars['restrictOnlyPdf']): ?><?php if ($this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN || ! $this->_tpl_vars['galley']->isPdfGalley() || $this->_tpl_vars['galley']->getRemoteURL()): ?>Download <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php elseif ($this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_SUBSCRIPTION): ?>Get <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 Access<?php endif; ?><?php else: ?>Download <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <span class="fileSize">(<?php echo $this->_tpl_vars['galley']->getNiceFileSize(); ?>
) <span><?php echo $this->_tpl_vars['galley']->getViews(); ?>
 views</span></span>
    			</div>
    			<?php endif; ?>
    			<?php endforeach; endif; unset($_from); ?>
    			<?php if ($this->_tpl_vars['hasAccess'] || ( $this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] )): ?>
    			<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="abstract-and-fulltext-info">
    				<span itemprop="url" title="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['articlePath']), $this);?>
"><?php if ($this->_tpl_vars['article']->getLocalizedAbstract()): ?>View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.abstract"), $this);?>
<?php else: ?>View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.details"), $this);?>
<?php endif; ?> <span class="fileView"><?php echo $this->_tpl_vars['article']->getViews(); ?>
 views</span></span>
    			</div>
    			<?php endif; ?>
				<span class="u-hide c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="article.pages"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.vol"), $this);?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if (((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?>, <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.no"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php if ($this->_tpl_vars['article']->getPages()): ?>, P: <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPages())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
<?php endif; ?></span>
			</div>
		</article>
	</div>
</li>

	<?php endif; ?>

<?php endforeach; endif; unset($_from); ?>

<?php endforeach; endif; unset($_from); ?>