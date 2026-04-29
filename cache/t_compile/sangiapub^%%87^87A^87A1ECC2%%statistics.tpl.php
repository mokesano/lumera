<?php /* Smarty version 2.6.26, created on 2026-04-04 19:37:21
         compiled from about/statistics.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'about/statistics.tpl', 31, false),array('function', 'url', 'about/statistics.tpl', 36, false),array('function', 'math', 'about/statistics.tpl', 76, false),array('modifier', 'escape', 'about/statistics.tpl', 38, false),array('modifier', 'default', 'about/statistics.tpl', 89, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "about.statistics"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-overview.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/featured/journal-timeline.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    
    </div>
</div>

<div class="column medium-12 u-mt-48">
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/featured/ArticleHeroAnalize.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>

<?php if ($this->_tpl_vars['publicStatisticsEnabled']): ?>
<div id="statistics">
<table width="100%" class="data">
	<tr valign="top">
		<td width="25%" class="label"><h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.year"), $this);?>
</h4></td>
		<td width="75%" colspan="2" class="value">
			<?php echo '<h4>'; ?><?php if ($this->_tpl_vars['statisticsYear'] > $this->_tpl_vars['firstYear']): ?><?php echo '<a class="action" href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('statisticsYear' => $this->_tpl_vars['statisticsYear']-1), $this);?><?php echo '">'; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.previousPage"), $this);?><?php echo '</a>&nbsp;'; ?><?php endif; ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['statisticsYear'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo ''; ?><?php if ($this->_tpl_vars['statisticsYear'] < $this->_tpl_vars['lastYear']): ?><?php echo '&nbsp;<a class="action" href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('statisticsYear' => $this->_tpl_vars['statisticsYear']+1), $this);?><?php echo '">'; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.nextPage"), $this);?><?php echo '</a>'; ?><?php endif; ?><?php echo '</h4>'; ?>

		</td>
	</tr>

	<?php if ($this->_tpl_vars['statNumPublishedIssues']): ?><tr valign="top">
		<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.numIssues"), $this);?>
</td>
		<td colspan="2" class="value"><?php echo $this->_tpl_vars['issueStatistics']['numPublishedIssues']; ?>
</td>
	</tr><?php endif; ?>

	<?php if ($this->_tpl_vars['statItemsPublished']): ?><tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.itemsPublished"), $this);?>
</td>
		<td width="80%" colspan="2" class="value"><?php echo $this->_tpl_vars['articleStatistics']['numPublishedSubmissions']; ?>
</td>
	</tr><?php endif; ?>
	<?php if ($this->_tpl_vars['statNumSubmissions']): ?><tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.numSubmissions"), $this);?>
</td>
		<td width="80%" colspan="2" class="value"><?php echo $this->_tpl_vars['articleStatistics']['numSubmissions']; ?>
</td>
	</tr><?php endif; ?>
	<?php if ($this->_tpl_vars['statPeerReviewed']): ?><tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.peerReviewed"), $this);?>
</td>
		<td width="80%" colspan="2" class="value"><?php echo $this->_tpl_vars['limitedArticleStatistics']['numReviewedSubmissions']; ?>
</td>
	</tr><?php endif; ?>
	<?php if ($this->_tpl_vars['statCountAccept']): ?><tr valign="top">
		<td width="20%" class="label">&nbsp;&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.count.accept"), $this);?>
</td>
		<td width="80%" colspan="2" class="value"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.count.value",'count' => $this->_tpl_vars['limitedArticleStatistics']['submissionsAccept'],'percentage' => $this->_tpl_vars['limitedArticleStatistics']['submissionsAcceptPercent']), $this);?>
</td>
	</tr><?php endif; ?>
	<?php if ($this->_tpl_vars['statCountDecline']): ?><tr valign="top">
		<td width="20%" class="label">&nbsp;&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.count.decline"), $this);?>
</td>
		<td width="80%" colspan="2" class="value"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.count.value",'count' => $this->_tpl_vars['limitedArticleStatistics']['submissionsDecline'],'percentage' => $this->_tpl_vars['limitedArticleStatistics']['submissionsDeclinePercent']), $this);?>
</td>
	</tr><?php endif; ?>
	<?php if ($this->_tpl_vars['statDaysPerReview']): ?><tr valign="top">
		<td width="20%" class="label">&nbsp;&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.daysPerReview"), $this);?>
</td>
		<td colspan="2" class="value">
			<?php $this->assign('daysPerReview', $this->_tpl_vars['reviewerStatistics']['daysPerReview']); ?>
			<?php echo smarty_function_math(array('equation' => "round(".($this->_tpl_vars['daysPerReview']).")"), $this);?>

		</td>
	</tr><?php endif; ?>
	<?php if ($this->_tpl_vars['statDaysToPublication']): ?><tr valign="top">
		<td width="20%" class="label">&nbsp;&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.daysToPublication"), $this);?>
</td>
		<td colspan="2" class="value"><?php echo $this->_tpl_vars['limitedArticleStatistics']['daysToPublication']; ?>
</td>
	</tr><?php endif; ?>
	<?php if ($this->_tpl_vars['statRegisteredUsers']): ?><tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.registeredUsers"), $this);?>
</td>
		<td colspan="2" class="value"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.totalNewValue",'numTotal' => $this->_tpl_vars['allUserStatistics']['totalUsersCount'],'numNew' => $this->_tpl_vars['userStatistics']['totalUsersCount']), $this);?>
</td>
	</tr><?php endif; ?>
	<?php if ($this->_tpl_vars['statRegisteredReaders']): ?><tr valign="top">
		<td width="20%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.registeredReaders"), $this);?>
</td>
		<td colspan="2" class="value"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.totalNewValue",'numTotal' => ((is_array($_tmp=@$this->_tpl_vars['allUserStatistics']['reader'])) ? $this->_run_mod_handler('default', true, $_tmp, '0') : smarty_modifier_default($_tmp, '0')),'numNew' => ((is_array($_tmp=@$this->_tpl_vars['userStatistics']['reader'])) ? $this->_run_mod_handler('default', true, $_tmp, '0') : smarty_modifier_default($_tmp, '0'))), $this);?>
</td>
	</tr><?php endif; ?>

	<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION && $this->_tpl_vars['statSubscriptions']): ?>
		<tr valign="top">
			<td colspan="3" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.subscriptions"), $this);?>
</td>
		</tr>
		<?php $_from = $this->_tpl_vars['allSubscriptionStatistics']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['type_id'] => $this->_tpl_vars['stats']):
?>
		<tr valign="top">
			<td width="20%" class="label">&nbsp;&nbsp;<?php echo $this->_tpl_vars['stats']['name']; ?>
:</td>
			<td colspan="2" class="value"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.totalNewValue",'numTotal' => ((is_array($_tmp=@$this->_tpl_vars['stats']['count'])) ? $this->_run_mod_handler('default', true, $_tmp, '0') : smarty_modifier_default($_tmp, '0')),'numNew' => ((is_array($_tmp=@$this->_tpl_vars['subscriptionStatistics'][$this->_tpl_vars['type_id']]['count'])) ? $this->_run_mod_handler('default', true, $_tmp, '0') : smarty_modifier_default($_tmp, '0'))), $this);?>
</td>
		</tr>
		<?php endforeach; endif; unset($_from); ?>
	<?php endif; ?>
</table>
</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-general.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>