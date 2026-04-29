<?php /* Smarty version 2.6.26, created on 2026-04-04 06:02:55
         compiled from file:/home/sangiaor/public_html/journals/plugins/blocks/readingTools/block.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/blocks/readingTools/block.tpl', 16, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/blocks/readingTools/block.tpl', 19, false),array('modifier', 'to_array', 'file:/home/sangiaor/public_html/journals/plugins/blocks/readingTools/block.tpl', 24, false),)), $this); ?>

<?php if ($this->_tpl_vars['journalRt'] && $this->_tpl_vars['journalRt']->getEnabled()): ?>

<div class="block" id="sidebarRTArticleTools">

	<span class="blockTitle"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.readingTools.articleTools"), $this);?>
</span>
	<?php if ($this->_tpl_vars['journalRt']->getAbstract() && $this->_tpl_vars['galley'] && $this->_tpl_vars['article']->getLocalizedAbstract()): ?>
		<div class="articleToolItem">
			<img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/blocks/readingTools/icons/abstract.png" class="articleToolIcon" alt="" /> <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['articleId']), $this);?>
" target="_parent"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.abstract"), $this);?>
</a><br />
		</div>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['journalRt']->getPrinterFriendly()): ?>
		<div class="articleToolItem">
			<img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/blocks/readingTools/icons/printArticle.png" class="articleToolIcon" alt="" /> <a href="<?php if (! $this->_tpl_vars['galley'] || $this->_tpl_vars['galley']->isHtmlGalley()): ?>javascript:openRTWindow('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'rt','op' => 'printerFriendly','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galleyId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galleyId']))), $this);?>
');<?php else: ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'download','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galleyId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galleyId']))), $this);?>
<?php endif; ?>"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.readingTools.printThisArticle"), $this);?>
</a>
		</div>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['journalRt']->getViewMetadata()): ?>
		<div class="articleToolItem">
			<img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/blocks/readingTools/icons/metadata.png" class="articleToolIcon" alt=""/> <a href="javascript:openRTWindow('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'rt','op' => 'metadata','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galleyId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galleyId']))), $this);?>
');"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.viewMetadata"), $this);?>
</a><br />
		</div>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['journalRt']->getCaptureCite()): ?>
		<div class="articleToolItem">
			<img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/blocks/readingTools/icons/citeArticle.png" class="articleToolIcon" alt=""/> <a href="javascript:openRTWindow('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'rt','op' => 'captureCite','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galleyId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galleyId']))), $this);?>
');"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.captureCite"), $this);?>
</a><br />
		</div>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['journalRt']->getSupplementaryFiles() && is_a ( $this->_tpl_vars['article'] , 'PublishedArticle' ) && $this->_tpl_vars['article']->getSuppFiles()): ?>
		<div class="articleToolItem">
			<img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/blocks/readingTools/icons/suppFiles.png" class="articleToolIcon" alt=""/> <a href="javascript:openRTWindow('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'rt','op' => 'suppFiles','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galleyId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galleyId']))), $this);?>
');"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.suppFiles"), $this);?>
</a><br />
		</div>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['journalRt']->getFindingReferences()): ?>
		<div class="articleToolItem">
			<img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/blocks/readingTools/icons/findingReferences.png" class="articleToolIcon" alt=""/> <a href="javascript:openRTWindow('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'rt','op' => 'findingReferences','path' => ((is_array($_tmp=$this->_tpl_vars['article']->getId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galleyId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galleyId']))), $this);?>
');"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.findingReferences"), $this);?>
</a>
		</div>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['journalRt']->getViewReviewPolicy()): ?>
		<div class="articleToolItem">
			<img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/blocks/readingTools/icons/editorialPolicies.png" class="articleToolIcon" alt=""/> <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'peerReviewProcess'), $this);?>
" target="_parent"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.reviewPolicy"), $this);?>
</a>
		</div>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['journalRt']->getEmailOthers()): ?>
		<div class="articleToolItem">
			<img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/blocks/readingTools/icons/emailArticle.png" class="articleToolIcon" alt=""/>
			<?php if ($this->_tpl_vars['isUserLoggedIn']): ?><a href="javascript:openRTWindow('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'rt','op' => 'emailColleague','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galleyId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galleyId']))), $this);?>
');"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.readingTools.emailThisArticle"), $this);?>
</a>
			<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.readingTools.emailThisArticle"), $this);?>
 <span style="font-size: 0.8em">(<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.readingTools.loginRequired"), $this);?>
)</span><?php endif; ?>
		</div>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['journalRt']->getEmailAuthor()): ?>
		<div class="articleToolItem">
			<img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/blocks/readingTools/icons/emailArticle.png" class="articleToolIcon" alt=""/>
			<?php if ($this->_tpl_vars['isUserLoggedIn']): ?><a href="javascript:openRTWindow('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'rt','op' => 'emailAuthor','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galleyId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galleyId']))), $this);?>
');"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.emailAuthor"), $this);?>
</a>
			<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.emailAuthor"), $this);?>
 <span style="font-size: 0.8em">(<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.readingTools.loginRequired"), $this);?>
)</span><?php endif; ?>
		</div>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['enableComments']): ?>
		<div class="articleToolItem">
			<img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/blocks/readingTools/icons/postComment.png" class="articleToolIcon" alt=""/>
			<?php if ($this->_tpl_vars['postingLoginRequired']): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.readingTools.postComment"), $this);?>
 <span style="font-size: 0.8em">(<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.readingTools.loginRequired"), $this);?>
)</span>
			<?php else: ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'comment','op' => 'add','path' => ((is_array($_tmp=$this->_tpl_vars['article']->getId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galleyId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galleyId']))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.readingTools.postComment"), $this);?>
</a><?php endif; ?>
		</div>
	<?php endif; ?>
</div>
<?php endif; ?>