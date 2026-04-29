<?php /* Smarty version 2.6.26, created on 2026-04-14 11:11:59
         compiled from sectionEditor/submission/scheduling.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'sectionEditor/submission/scheduling.tpl', 12, false),array('function', 'url', 'sectionEditor/submission/scheduling.tpl', 16, false),array('function', 'html_options', 'sectionEditor/submission/scheduling.tpl', 29, false),array('function', 'math', 'sectionEditor/submission/scheduling.tpl', 55, false),array('function', 'html_select_date', 'sectionEditor/submission/scheduling.tpl', 62, false),array('modifier', 'truncate', 'sectionEditor/submission/scheduling.tpl', 29, false),array('modifier', 'date_format', 'sectionEditor/submission/scheduling.tpl', 52, false),array('modifier', 'assign', 'sectionEditor/submission/scheduling.tpl', 55, false),array('modifier', 'default', 'sectionEditor/submission/scheduling.tpl', 62, false),)), $this); ?>
<div id="scheduling" class="block">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.scheduling"), $this);?>
</h3>

<table class="data listing" width="100%">
<?php if (! $this->_tpl_vars['publicationFeeEnabled'] || $this->_tpl_vars['publicationPayment']): ?>
	<form action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'scheduleForPublication','path' => $this->_tpl_vars['submission']->getId()), $this);?>
" method="post">
		<tr valign="top">
			<td width="20%" class="label">
				<label for="issueId"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.article.scheduleForPublication"), $this);?>
</label>
			</td>
			<td width="40%" class="value">
				<?php if ($this->_tpl_vars['publishedArticle']): ?>
					<?php $this->assign('issueId', $this->_tpl_vars['publishedArticle']->getIssueId()); ?>
				<?php else: ?>
					<?php $this->assign('issueId', 0); ?>
				<?php endif; ?>
				<select name="issueId" id="issueId" class="selectMenu">
					<option value=""><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.article.scheduleForPublication.toBeAssigned"), $this);?>
</option>
					<?php echo smarty_function_html_options(array('options' => ((is_array($_tmp=$this->_tpl_vars['issueOptions'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 40, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 40, "...")),'selected' => $this->_tpl_vars['issueId']), $this);?>

				</select>
			</td>
			<td width="40%" class="value">
				<input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.record"), $this);?>
" class="button defaultButton" />&nbsp;
				<?php if ($this->_tpl_vars['issueId']): ?>
					<?php if ($this->_tpl_vars['isEditor']): ?>
						<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'editor','op' => 'issueToc','path' => $this->_tpl_vars['issueId']), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.toc"), $this);?>
</a>
					<?php else: ?>
						<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issueId']), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.toc"), $this);?>
</a>
					<?php endif; ?>
				<?php endif; ?>
			</td>
		</tr>
	</form>
	<?php if ($this->_tpl_vars['publishedArticle']): ?>
		<form action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'setDatePublished','path' => $this->_tpl_vars['submission']->getId()), $this);?>
" method="post">
			<tr valign="top">
				<td width="20%" class="label">
					<label for="issueId"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.issues.published"), $this);?>
</label>
				</td>
				<td class="value">
										<?php $this->assign('currentYear', ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
					<?php if ($this->_tpl_vars['publishedArticle']->getDatePublished()): ?>
						<?php $this->assign('publishedYear', ((is_array($_tmp=$this->_tpl_vars['publishedArticle']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
						<?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "min(x,y)-10",'x' => $this->_tpl_vars['publishedYear'],'y' => $this->_tpl_vars['currentYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'minYear') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'minYear'));?>

						<?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "max(x,y)+2",'x' => $this->_tpl_vars['publishedYear'],'y' => $this->_tpl_vars['currentYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'maxYear') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'maxYear'));?>

					<?php else: ?>
												<?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x-10",'x' => $this->_tpl_vars['currentYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'minYear') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'minYear'));?>

						<?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x+2",'x' => $this->_tpl_vars['currentYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'maxYear') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'maxYear'));?>

					<?php endif; ?>
					<?php echo smarty_function_html_select_date(array('prefix' => 'datePublished','time' => ((is_array($_tmp=@$this->_tpl_vars['publishedArticle']->getDatePublished())) ? $this->_run_mod_handler('default', true, $_tmp, "---") : smarty_modifier_default($_tmp, "---")),'all_extra' => "class=\"selectMenu\"",'start_year' => $this->_tpl_vars['minYear'],'end_year' => $this->_tpl_vars['maxYear'],'year_empty' => "-",'month_empty' => "-",'day_empty' => "-"), $this);?>

				</td>
				<td class="value">
					<input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.record"), $this);?>
" class="button defaultButton" />&nbsp;
				</td>
			</tr>
		</form>
	<?php endif; ?><?php else: ?>
	<tr>
		<td><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.article.payment.publicationFeeNotPaid"), $this);?>
</td>
		<td align="right">
			<form action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'waivePublicationFee','path' => $this->_tpl_vars['submission']->getId()), $this);?>
" method="post">
			<input type="hidden" name="markAsPaid" value=1 />
			<input type="hidden" name="sendToScheduling" value=1 />
			<input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.paymentReceived"), $this);?>
" class="button defaultButton" />&nbsp;
			</form>
		</td>
		<?php if ($this->_tpl_vars['isEditor']): ?>
			<td align="left">
				<form action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'editor','op' => 'waivePublicationFee','path' => $this->_tpl_vars['submission']->getId()), $this);?>
" method="post">
					<input type="hidden" name="sendToScheduling" value=1 />
					<input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.waive"), $this);?>
" class="button defaultButton" />&nbsp;
				</form>
			</td>	
		<?php endif; ?>
	</tr>
<?php endif; ?>
</table>
</div>