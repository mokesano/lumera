<?php /* Smarty version 2.6.26, created on 2026-04-13 18:03:52
         compiled from editor/issues/issueToc.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'editor/issues/issueToc.tpl', 12, false),array('modifier', 'truncate', 'editor/issues/issueToc.tpl', 43, false),array('modifier', 'assign', 'editor/issues/issueToc.tpl', 57, false),array('modifier', 'strip_tags', 'editor/issues/issueToc.tpl', 99, false),array('function', 'url', 'editor/issues/issueToc.tpl', 25, false),array('function', 'translate', 'editor/issues/issueToc.tpl', 33, false),array('function', 'html_options', 'editor/issues/issueToc.tpl', 43, false),array('function', 'call_hook', 'editor/issues/issueToc.tpl', 53, false),array('function', 'icon', 'editor/issues/issueToc.tpl', 110, false),)), $this); ?>
<?php echo ''; ?><?php if (! $this->_tpl_vars['noIssue']): ?><?php echo ''; ?><?php $this->assign('pageTitleTranslated', ((is_array($_tmp=$this->_tpl_vars['issue']->getIssueIdentification())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitleTranslated', ((is_array($_tmp=$this->_tpl_vars['issue']->getIssueIdentification(false,true))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))); ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php $this->assign('pageTitle', "editor.issues.noLiveIssues"); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitle', "editor.issues.noLiveIssues"); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<script type="text/javascript">
<?php echo '
$(document).ready(function() {
	'; ?>
<?php $_from = $this->_tpl_vars['sections']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['sectionKey'] => $this->_tpl_vars['section']):
?><?php echo '
	setupTableDND("#issueToc-'; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo '", "'; ?>
<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'moveArticleToc','escape' => false), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
<?php echo '");
	'; ?>
<?php endforeach; endif; unset($_from); ?><?php echo '
});
'; ?>

</script>

<?php if (! $this->_tpl_vars['isLayoutEditor']): ?>	<ul class="menu">
		<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createIssue'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.navigation.createIssue"), $this);?>
</a></li>
		<li<?php if ($this->_tpl_vars['unpublished']): ?> class="current"<?php endif; ?>><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'futureIssues'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.navigation.futureIssues"), $this);?>
</a></li>
		<li<?php if (! $this->_tpl_vars['unpublished']): ?> class="current"<?php endif; ?>><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'backIssues'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.navigation.issueArchive"), $this);?>
</a></li>
	</ul>
<?php endif; ?>

<?php if (! $this->_tpl_vars['noIssue']): ?>
<br />

<form action="#">
<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
: <select name="issue" class="selectMenu" onchange="if(this.options[this.selectedIndex].value > 0) location.href='<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'issueToc','path' => 'ISSUE_ID','escape' => false), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript'));?>
'.replace('ISSUE_ID', this.options[this.selectedIndex].value)" size="1"><?php echo smarty_function_html_options(array('options' => ((is_array($_tmp=$this->_tpl_vars['issueOptions'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 40, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 40, "...")),'selected' => $this->_tpl_vars['issueId']), $this);?>
</select>
</form>

<div class="separator"></div>

<ul class="menu">
	<li class="current"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'issueToc','path' => $this->_tpl_vars['issueId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.toc"), $this);?>
</a></li>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'issueData','path' => $this->_tpl_vars['issueId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.issueData"), $this);?>
</a></li>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'issueGalleys','path' => $this->_tpl_vars['issueId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.galleys"), $this);?>
</a></li>
	<?php if ($this->_tpl_vars['unpublished']): ?><li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issue']->getBestIssueId()), $this);?>
" target="_blank"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.previewIssue"), $this);?>
</a></li><?php endif; ?>
	<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Editor::Issues::IssueToc::IssuePages"), $this);?>

</ul>

<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.toc"), $this);?>
</h3>
<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'resetSectionOrder','path' => $this->_tpl_vars['issueId']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

<?php if ($this->_tpl_vars['customSectionOrderingExists']): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.resetSectionOrder",'url' => $this->_tpl_vars['url']), $this);?>
<br/><?php endif; ?>
<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'updateIssueToc','path' => $this->_tpl_vars['issueId']), $this);?>
" onsubmit="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.saveChanges"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')">

<?php $this->assign('numCols', 5); ?>
<?php if ($this->_tpl_vars['issueAccess'] == @ISSUE_ACCESS_SUBSCRIPTION && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?><?php $this->assign('numCols', $this->_tpl_vars['numCols']+1); ?><?php endif; ?>
<?php if ($this->_tpl_vars['enablePublicArticleId']): ?><?php $this->assign('numCols', $this->_tpl_vars['numCols']+1); ?><?php endif; ?>
<?php if ($this->_tpl_vars['enablePageNumber']): ?><?php $this->assign('numCols', $this->_tpl_vars['numCols']+1); ?><?php endif; ?>

<?php $_from = $this->_tpl_vars['sections']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['sectionKey'] => $this->_tpl_vars['section']):
?>
<h4><?php echo $this->_tpl_vars['section'][1]; ?>
<?php if ($this->_tpl_vars['section'][4]): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'moveSectionToc','path' => $this->_tpl_vars['issueId'],'d' => 'u','newPos' => $this->_tpl_vars['section'][4],'sectionId' => $this->_tpl_vars['section'][0]), $this);?>
" class="plain">&uarr;</a><?php else: ?>&uarr;<?php endif; ?> <?php if ($this->_tpl_vars['section'][5]): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'moveSectionToc','path' => $this->_tpl_vars['issueId'],'d' => 'd','newPos' => $this->_tpl_vars['section'][5],'sectionId' => $this->_tpl_vars['section'][0]), $this);?>
" class="plain">&darr;</a><?php else: ?>&darr;<?php endif; ?></h4>

<table width="100%" class="listing" id="issueToc-<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
	<tr>
		<td colspan="<?php echo ((is_array($_tmp=$this->_tpl_vars['numCols'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="5%">&nbsp;</td>
		<td width="15%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.authors"), $this);?>
</td>
		<td><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.title"), $this);?>
</td>
		<?php if ($this->_tpl_vars['issueAccess'] == @ISSUE_ACCESS_SUBSCRIPTION && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?><td width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.access"), $this);?>
</td><?php endif; ?>
		<?php if ($this->_tpl_vars['enablePublicArticleId']): ?><td width="7%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.publicId"), $this);?>
</td><?php endif; ?>
		<?php if ($this->_tpl_vars['enablePageNumber']): ?><td width="7%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.pages"), $this);?>
</td><?php endif; ?>
		<td width="5%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.remove"), $this);?>
</td>
		<td width="5%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.proofed"), $this);?>
</td>
	</tr>
	<tr>
		<td colspan="<?php echo ((is_array($_tmp=$this->_tpl_vars['numCols'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="headseparator">&nbsp;</td>
	</tr>

	<?php $this->assign('articleSeq', 0); ?>
	<?php $_from = $this->_tpl_vars['section'][2]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['currSection'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['currSection']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['article']):
        $this->_foreach['currSection']['iteration']++;
?>

	<?php $this->assign('articleSeq', $this->_tpl_vars['articleSeq']+1); ?>
	<?php $this->assign('articleId', $this->_tpl_vars['article']->getId()); ?>
	<tr id="article-<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPublishedArticleId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="data">
		<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'moveArticleToc','d' => 'u','id' => $this->_tpl_vars['article']->getPublishedArticleId()), $this);?>
" class="plain">&uarr;</a>&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'moveArticleToc','d' => 'd','id' => $this->_tpl_vars['article']->getPublishedArticleId()), $this);?>
" class="plain">&darr;</a></td>
		<td>
			<?php $_from = $this->_tpl_vars['article']->getAuthors(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authorList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authorList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authorList']['iteration']++;
?>
				<?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if (! ($this->_foreach['authorList']['iteration'] == $this->_foreach['authorList']['total'])): ?>,<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
		</td>
		<td class="drag"><?php if (! $this->_tpl_vars['isLayoutEditor']): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submission','path' => $this->_tpl_vars['articleId']), $this);?>
" class="action"><?php endif; ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 60, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 60, "...")); ?>
<?php if (! $this->_tpl_vars['isLayoutEditor']): ?></a><?php endif; ?></td>
		<?php if ($this->_tpl_vars['issueAccess'] == @ISSUE_ACCESS_SUBSCRIPTION && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?>
		<td><select name="accessStatus[<?php echo $this->_tpl_vars['article']->getPublishedArticleId(); ?>
]" size="1" class="selectMenu"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['accessOptions'],'selected' => $this->_tpl_vars['article']->getAccessStatus()), $this);?>
</select></td>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['enablePublicArticleId']): ?>
		<td><input type="text" name="publishedArticles[<?php echo $this->_tpl_vars['article']->getId(); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPubId('publisher-id'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="7" maxlength="255" class="textField" /></td>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['enablePageNumber']): ?><td><input type="text" name="pages[<?php echo $this->_tpl_vars['article']->getId(); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPages())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="7" maxlength="255" class="textField" /></td><?php endif; ?>
		<td align="center"><input type="checkbox" name="remove[<?php echo $this->_tpl_vars['article']->getId(); ?>
]" value="<?php echo $this->_tpl_vars['article']->getPublishedArticleId(); ?>
" /></td>
		<td align="center">
			<?php if (in_array ( $this->_tpl_vars['article']->getId() , $this->_tpl_vars['proofedArticleIds'] )): ?>
				<?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'checked'), $this);?>

			<?php else: ?>
				<?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'unchecked'), $this);?>

			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; endif; unset($_from); ?>
</table>

<?php endforeach; else: ?>
<article id="articles_issue" itemtype="http://schema.org/ScholarlyArticle">
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
        </div>
        <div class="c-empty-state-card__text">
            <h3 class="c-empty-state-card__text--title headline-5"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.noArticles"), $this);?>
</h3>
            <?php if ($this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>
            <p class="c-empty-state-card__text--description">Browse our <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'archive'), $this);?>
">Issue Archive</a> to explore previously published research and scholarly articles, or visit the <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'current'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "journal.currentIssue"), $this);?>
</a> to access the latest manuscripts. You can also use the <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search'), $this);?>
">Search</a> function to find specific topics or authors, or check our <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'titles'), $this);?>
">Titles Index</a> for a comprehensive list of all published articles within our journal collection.</p>
            <?php endif; ?>
        </div>
    </div>
</article>

<div class="separator"></div>
<?php endif; unset($_from); ?>

<input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" />
<?php if ($this->_tpl_vars['unpublished'] && ! $this->_tpl_vars['isLayoutEditor']): ?>
		<input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.publishIssue"), $this);?>
" onclick="confirmAction('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'publishIssue','path' => $this->_tpl_vars['issueId']), $this);?>
', '<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.confirmPublish"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="button" />
<?php elseif (! $this->_tpl_vars['isLayoutEditor']): ?>
		<input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.unpublishIssue"), $this);?>
" onclick="confirmAction('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'unpublishIssue','path' => $this->_tpl_vars['issueId']), $this);?>
', '<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.confirmUnpublish"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="button" />
<?php endif; ?>

</form>

<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
