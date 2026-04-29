<?php /* Smarty version 2.6.26, created on 2026-04-13 18:03:47
         compiled from editor/issues/backIssues.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'editor/issues/backIssues.tpl', 14, false),array('function', 'translate', 'editor/issues/backIssues.tpl', 25, false),array('function', 'page_info', 'editor/issues/backIssues.tpl', 77, false),array('function', 'page_links', 'editor/issues/backIssues.tpl', 78, false),array('function', 'html_options', 'editor/issues/backIssues.tpl', 87, false),array('modifier', 'assign', 'editor/issues/backIssues.tpl', 14, false),array('modifier', 'strip_unsafe_html', 'editor/issues/backIssues.tpl', 53, false),array('modifier', 'nl2br', 'editor/issues/backIssues.tpl', 53, false),array('modifier', 'date_format', 'editor/issues/backIssues.tpl', 54, false),array('modifier', 'default', 'editor/issues/backIssues.tpl', 54, false),array('modifier', 'escape', 'editor/issues/backIssues.tpl', 55, false),array('modifier', 'truncate', 'editor/issues/backIssues.tpl', 87, false),array('block', 'iterate', 'editor/issues/backIssues.tpl', 51, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "editor.issues.backIssues"); ?><?php echo ''; ?><?php $this->assign('page', $this->_tpl_vars['rangeInfo']->getPage()); ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'editor','op' => 'backIssues'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<script type="text/javascript">
<?php echo '
$(document).ready(function() { setupTableDND("#dragTable", "moveIssue"); });
'; ?>

</script>

<ul class="menu">
        <li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createIssue'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.navigation.createIssue"), $this);?>
</a></li>
        <li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'futureIssues'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.navigation.futureIssues"), $this);?>
</a></li>
        <li class="current"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'backIssues'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.navigation.issueArchive"), $this);?>
</a></li>
</ul>

<?php if ($this->_tpl_vars['usesCustomOrdering']): ?>
	<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'resetIssueOrder'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'resetUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'resetUrl'));?>

	<p class="info-message u-mb-8 u-mt-8"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.resetIssueOrder",'url' => $this->_tpl_vars['resetUrl']), $this);?>
</p>
<?php endif; ?>

<div id="issues">
<table width="100%" class="listing" id="dragTable">
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="60%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
</td>
		<td width="15%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.published"), $this);?>
</td>
		<td width="15%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.numArticles"), $this);?>
</td>
		<td width="5%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.order"), $this);?>
</td>
		<td width="5%" align="right"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</td>
	</tr>
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>

	<?php $this->_tag_stack[] = array('iterate', array('from' => 'issues','item' => 'issue')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<tr valign="top" class="data" id="issue-<?php echo $this->_tpl_vars['issue']->getId(); ?>
">
		<td class="drag"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'issueToc','path' => $this->_tpl_vars['issue']->getId()), $this);?>
" class="action"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getIssueIdentification())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</a></td>
		<td class="drag"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, ($this->_tpl_vars['dateFormatShort'])) : smarty_modifier_date_format($_tmp, ($this->_tpl_vars['dateFormatShort']))))) ? $this->_run_mod_handler('default', true, $_tmp, "&mdash;") : smarty_modifier_default($_tmp, "&mdash;")); ?>
</td>
		<td class="drag"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumArticles())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
		<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'moveIssue','d' => 'u','id' => $this->_tpl_vars['issue']->getId(),'issuesPage' => $this->_tpl_vars['page']), $this);?>
">&uarr;</a>	<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'moveIssue','d' => 'd','id' => $this->_tpl_vars['issue']->getId(),'issuesPage' => $this->_tpl_vars['page']), $this);?>
">&darr;</a></td>
		<td align="right"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'removeIssue','path' => $this->_tpl_vars['issue']->getId(),'issuesPage' => $this->_tpl_vars['page']), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.confirmDelete"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
</a></td>
	</tr>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	<tr>
		<td colspan="5" class="endseparator">&nbsp;</td>
	</tr>
</table>
<?php if ($this->_tpl_vars['issues']->wasEmpty()): ?>
<br />
<table id="colspan-anchor" width="100%">
	<tr>
		<td colspan="5" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.noIssues"), $this);?>
</td>
	</tr>
	<tr>
		<td colspan="5" class="endseparator">&nbsp;</td>
	</tr>
</table>
<?php else: ?>
<table id="colspan-anchor" width="100%" style="margin-top: .7em">
	<tr >
		<td class="anchor" colspan="2" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['issues']), $this);?>
</td>
		<td class="anchor" colspan="3" align="right" style="text-align: right;"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'issues','name' => 'issues','iterator' => $this->_tpl_vars['issues']), $this);?>
</td>
	</tr>
</table>
<?php endif; ?>

<form action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'setCurrentIssue'), $this);?>
" method="post">
	<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "journal.currentIssue"), $this);?>
&nbsp;&nbsp;
	<select name="issueId" class="selectMenu">
		<option value=""><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.none"), $this);?>
</option>
		<?php echo smarty_function_html_options(array('options' => ((is_array($_tmp=$this->_tpl_vars['allIssues'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 40, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 40, "...")),'selected' => $this->_tpl_vars['currentIssueId']), $this);?>

	</select>
	<input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.record"), $this);?>
" class="button defaultButton" />
</form>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
