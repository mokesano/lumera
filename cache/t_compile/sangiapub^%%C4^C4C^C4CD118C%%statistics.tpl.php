<?php /* Smarty version 2.6.26, created on 2026-04-05 05:54:36
         compiled from manager/statistics/statistics.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'manager/statistics/statistics.tpl', 14, false),array('function', 'url', 'manager/statistics/statistics.tpl', 18, false),array('function', 'fieldLabel', 'manager/statistics/statistics.tpl', 25, false),array('function', 'math', 'manager/statistics/statistics.tpl', 99, false),array('modifier', 'escape', 'manager/statistics/statistics.tpl', 29, false),array('modifier', 'default', 'manager/statistics/statistics.tpl', 112, false),)), $this); ?>
<div id="statistics" class="block">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics"), $this);?>
</h3>
<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.description"), $this);?>
</p>
<div id="selectSections">
<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.selectSections"), $this);?>
</p>
<form action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'saveStatisticsSettings'), $this);?>
" method="post">
	<?php if (count ( $this->_tpl_vars['availableMetricTypes'] ) > 1): ?>
		<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "defaultMetric.title"), $this);?>
</h3>
		<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.defaultMetricDescription"), $this);?>
</p>
		<div id="defaultMetricSelection">
			<table class="data" width="100%">
				<tr valign="top">
					<td width="35%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'defaultMetricType','key' => "defaultMetric.availableMetrics"), $this);?>
</td>
					<td colspan="2" width="65%" class="value">
						<select name="defaultMetricType" class="selectMenu" id="defaultMetricType">
						<?php $_from = $this->_tpl_vars['availableMetricTypes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['metricType'] => $this->_tpl_vars['displayName']):
?>
							<option value="<?php echo ((is_array($_tmp=$this->_tpl_vars['metricType'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if ($this->_tpl_vars['metricType'] == $this->_tpl_vars['defaultMetricType']): ?> selected="selected"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['displayName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</option>
						<?php endforeach; endif; unset($_from); ?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<br />
	<?php endif; ?>
	<select name="sectionIds[]" class="selectMenu" multiple="multiple">
		<?php $_from = $this->_tpl_vars['sections']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['section']):
?>
			<option <?php if (in_array ( $this->_tpl_vars['section']->getId() , $this->_tpl_vars['sectionIds'] )): ?>selected="selected" <?php endif; ?>value="<?php echo $this->_tpl_vars['section']->getId(); ?>
"><?php echo $this->_tpl_vars['section']->getLocalizedTitle(); ?>
</option>
		<?php endforeach; endif; unset($_from); ?>
	</select><br/>&nbsp;<br/>
	<input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.record"), $this);?>
" class="button defaultButton"/>
</form>
</div>

<form action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'savePublicStatisticsList'), $this);?>
" method="post">
<table width="100%" class="data">
	<tr valign="top">
		<td width="35%" class="label"><h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.year"), $this);?>
</h4></td>
		<td width="65%" colspan="2" class="value">
			<?php echo '<h4>'; ?><?php if ($this->_tpl_vars['statisticsYear'] > $this->_tpl_vars['firstYear']): ?><?php echo '<a class="action" href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('statisticsYear' => $this->_tpl_vars['statisticsYear']-1), $this);?><?php echo '">'; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.previousPage"), $this);?><?php echo '</a>&nbsp;'; ?><?php endif; ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['statisticsYear'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo ''; ?><?php if ($this->_tpl_vars['statisticsYear'] < $this->_tpl_vars['lastYear']): ?><?php echo '&nbsp;<a class="action" href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('statisticsYear' => $this->_tpl_vars['statisticsYear']+1), $this);?><?php echo '">'; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.nextPage"), $this);?><?php echo '</a>'; ?><?php endif; ?><?php echo '</h4>'; ?>

		</td>
	</tr>

	<tr valign="top">
		<td class="label"><input type="checkbox" id="statNumPublishedIssues" name="statNumPublishedIssues" <?php if ($this->_tpl_vars['statNumPublishedIssues']): ?>checked="checked" <?php endif; ?>/><label for="statNumPublishedIssues"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.numIssues"), $this);?>
</label></td>
		<td colspan="2" class="value"><?php echo $this->_tpl_vars['issueStatistics']['numPublishedIssues']; ?>
</td>
	</tr>

	<tr valign="top">
		<td width="35%" class="label"><input type="checkbox" id="statItemsPublished" name="statItemsPublished" <?php if ($this->_tpl_vars['statItemsPublished']): ?>checked="checked" <?php endif; ?>/><label for="statItemsPublished"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.itemsPublished"), $this);?>
</label></td>
		<td width="65%" colspan="2" class="value"><?php echo $this->_tpl_vars['articleStatistics']['numPublishedSubmissions']; ?>
</td>
	</tr>
	<tr valign="top">
		<td width="35%" class="label"><input type="checkbox" id="statNumSubmissions" name="statNumSubmissions" <?php if ($this->_tpl_vars['statNumSubmissions']): ?>checked="checked" <?php endif; ?>/><label for="statNumSubmissions"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.numSubmissions"), $this);?>
</label></td>
		<td width="65%" colspan="2" class="value"><?php echo $this->_tpl_vars['articleStatistics']['numSubmissions']; ?>
</td>
	</tr>
	<tr valign="top">
		<td width="35%" class="label"><input type="checkbox" id="statPeerReviewed" name="statPeerReviewed" <?php if ($this->_tpl_vars['statPeerReviewed']): ?>checked="checked" <?php endif; ?>/><label for="statPeerReviewed"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.peerReviewed"), $this);?>
</label></td>
		<td width="65%" colspan="2" class="value"><?php echo $this->_tpl_vars['limitedArticleStatistics']['numReviewedSubmissions']; ?>
</td>
	</tr>
	<tr valign="top">
		<td width="35%" class="label"><input type="checkbox" id="statCountAccept" name="statCountAccept" <?php if ($this->_tpl_vars['statCountAccept']): ?>checked="checked" <?php endif; ?>/><label for="statCountAccept"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.count.accept"), $this);?>
</label></td>
		<td width="65%" colspan="2" class="value"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.count.value",'count' => $this->_tpl_vars['limitedArticleStatistics']['submissionsAccept'],'percentage' => $this->_tpl_vars['limitedArticleStatistics']['submissionsAcceptPercent']), $this);?>
</td>
	</tr>
	<tr valign="top">
		<td width="35%" class="label"><input type="checkbox" id="statCountDecline" name="statCountDecline" <?php if ($this->_tpl_vars['statCountDecline']): ?>checked="checked" <?php endif; ?>/><label for="statCountDecline"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.count.decline"), $this);?>
</label></td>
		<td width="65%" colspan="2" class="value"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.count.value",'count' => $this->_tpl_vars['limitedArticleStatistics']['submissionsDecline'],'percentage' => $this->_tpl_vars['limitedArticleStatistics']['submissionsDeclinePercent']), $this);?>
</td>
	</tr>
	<tr valign="top">
		<td width="35%" class="label"><input type="checkbox" id="statCountRevise" name="statCountRevise" <?php if ($this->_tpl_vars['statCountRevise']): ?>checked="checked" <?php endif; ?>/><label for="statCountRevise"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.count.revise"), $this);?>
</label></td>
		<td width="65%" colspan="2" class="value"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.count.value",'count' => $this->_tpl_vars['limitedArticleStatistics']['submissionsRevise'],'percentage' => $this->_tpl_vars['limitedArticleStatistics']['submissionsRevise']), $this);?>
</td>
	</tr>
	<tr valign="top">
		<td width="35%" class="label"><input type="checkbox" id="statDaysPerReview" name="statDaysPerReview" <?php if ($this->_tpl_vars['statDaysPerReview']): ?>checked="checked" <?php endif; ?>/><label for="statDaysPerReview"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.daysPerReview"), $this);?>
</label></td>
		<td colspan="2" class="value">
			<?php $this->assign('daysPerReview', $this->_tpl_vars['reviewerStatistics']['daysPerReview']); ?>
			<?php echo smarty_function_math(array('equation' => "round(".($this->_tpl_vars['daysPerReview']).")"), $this);?>

		</td>
	</tr>
	<tr valign="top">
		<td width="35%" class="label"><input type="checkbox" id="statDaysToPublication" name="statDaysToPublication" <?php if ($this->_tpl_vars['statDaysToPublication']): ?>checked="checked" <?php endif; ?>/><label for="statDaysToPublication"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.daysToPublication"), $this);?>
</label></td>
		<td colspan="2" class="value"><?php echo $this->_tpl_vars['limitedArticleStatistics']['daysToPublication']; ?>
</td>
	</tr>
	<tr valign="top">
		<td width="35%" class="label"><input type="checkbox" id="statRegisteredUsers" name="statRegisteredUsers" <?php if ($this->_tpl_vars['statRegisteredUsers']): ?>checked="checked" <?php endif; ?>/><label for="statRegisteredUsers"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.registeredUsers"), $this);?>
</label></td>
		<td colspan="2" class="value"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.totalNewValue",'numTotal' => $this->_tpl_vars['allUserStatistics']['totalUsersCount'],'numNew' => $this->_tpl_vars['userStatistics']['totalUsersCount']), $this);?>
</td>
	</tr>
	<tr valign="top">
		<td width="35%" class="label"><input type="checkbox" id="statRegisteredReaders" name="statRegisteredReaders" <?php if ($this->_tpl_vars['statRegisteredReaders']): ?>checked="checked" <?php endif; ?>/><label for="statRegisteredReaders"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.registeredReaders"), $this);?>
</label></td>
		<td colspan="2" class="value"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.totalNewValue",'numTotal' => ((is_array($_tmp=@$this->_tpl_vars['allUserStatistics']['reader'])) ? $this->_run_mod_handler('default', true, $_tmp, '0') : smarty_modifier_default($_tmp, '0')),'numNew' => ((is_array($_tmp=@$this->_tpl_vars['userStatistics']['reader'])) ? $this->_run_mod_handler('default', true, $_tmp, '0') : smarty_modifier_default($_tmp, '0'))), $this);?>
</td>
	</tr>

	<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?>
		<tr valign="top">
			<td colspan="3" class="label"><input type="checkbox" id="statSubscriptions" name="statSubscriptions" <?php if ($this->_tpl_vars['statSubscriptions']): ?>checked="checked" <?php endif; ?>/><label for="statSubscriptions"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.subscriptions"), $this);?>
</label></td>
		</tr>
		<?php $_from = $this->_tpl_vars['allSubscriptionStatistics']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['type_id'] => $this->_tpl_vars['stats']):
?>
		<tr valign="top">
			<td width="35%" class="label"><?php echo $this->_tpl_vars['stats']['name']; ?>
:</td>
			<td colspan="2" class="value"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.totalNewValue",'numTotal' => ((is_array($_tmp=@$this->_tpl_vars['stats']['count'])) ? $this->_run_mod_handler('default', true, $_tmp, '0') : smarty_modifier_default($_tmp, '0')),'numNew' => ((is_array($_tmp=@$this->_tpl_vars['subscriptionStatistics'][$this->_tpl_vars['type_id']]['count'])) ? $this->_run_mod_handler('default', true, $_tmp, '0') : smarty_modifier_default($_tmp, '0'))), $this);?>
</td>
		</tr>
		<?php endforeach; endif; unset($_from); ?>
	<?php endif; ?>

	<tr valign="top">
		<td width="35%" colspan="3" class="label"><input type="checkbox" id="statViews" name="statViews" <?php if ($this->_tpl_vars['statViews']): ?>checked="checked" <?php endif; ?>/><label for="statViews"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.articleViews"), $this);?>
</label></td>
	</tr>
</table>
<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.note"), $this);?>
</p>

<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.statistics.statistics.makePublic"), $this);?>
<br/>
<input type="submit" class="button defaultButton" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.record"), $this);?>
"/>
</form>
</div>