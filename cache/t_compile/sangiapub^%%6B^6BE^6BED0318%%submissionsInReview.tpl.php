<?php /* Smarty version 2.6.26, created on 2026-04-15 07:36:39
         compiled from editor/submissionsInReview.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'sort_search', 'editor/submissionsInReview.tpl', 18, false),array('function', 'translate', 'editor/submissionsInReview.tpl', 19, false),array('function', 'url', 'editor/submissionsInReview.tpl', 53, false),array('function', 'page_info', 'editor/submissionsInReview.tpl', 114, false),array('function', 'page_links', 'editor/submissionsInReview.tpl', 115, false),array('block', 'iterate', 'editor/submissionsInReview.tpl', 42, false),array('modifier', 'escape', 'editor/submissionsInReview.tpl', 45, false),array('modifier', 'date_format', 'editor/submissionsInReview.tpl', 50, false),array('modifier', 'truncate', 'editor/submissionsInReview.tpl', 52, false),array('modifier', 'strip_tags', 'editor/submissionsInReview.tpl', 53, false),array('modifier', 'default', 'editor/submissionsInReview.tpl', 61, false),)), $this); ?>
<?php if (! $this->_tpl_vars['submissions']->wasEmpty()): ?>
<div id="submissions" class="review">
    <table width="100%" class="listing">
    	<tr>
    		<td colspan="8" class="headseparator">&nbsp;</td>
    	</tr>
    	<tr class="heading">
    		<td width="1%"></td>
    		<td width="5%"><?php echo $this->_plugins['function']['sort_search'][0][0]->smartySortSearch(array('key' => "common.id",'sort' => 'id'), $this);?>
</td>
    		<td width="5%"><span class="disabled"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.date.yyyymmdd"), $this);?>
</span><br /><?php echo $this->_plugins['function']['sort_search'][0][0]->smartySortSearch(array('key' => "submissions.submitted",'sort' => 'submitDate'), $this);?>
</td>
    		<td width="5%"><?php echo $this->_plugins['function']['sort_search'][0][0]->smartySortSearch(array('key' => "submissions.sec",'sort' => 'section'), $this);?>
</td>
    		<td width="15%"><?php echo $this->_plugins['function']['sort_search'][0][0]->smartySortSearch(array('key' => "article.authors",'sort' => 'authors'), $this);?>
</td>
    		<td width="35%"><?php echo $this->_plugins['function']['sort_search'][0][0]->smartySortSearch(array('key' => "article.title",'sort' => 'title'), $this);?>
</td>
    		<td width="20%">
    			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.peerReview"), $this);?>

    			<table width="100%" class="nested">
    				<tr valign="center">
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.ask"), $this);?>
</td>
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.due"), $this);?>
</td>
    					<td width="34%" style="padding: 0 4px 0 0; font-size: 1.0em"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.done"), $this);?>
</td>
    				</tr>
    			</table>
    		</td>
    		<td width="5%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submissions.ruling"), $this);?>
</td>
    		<td width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.sectionEditor"), $this);?>
</td>
    	</tr>
    	<?php if ($this->_tpl_vars['submissions']->eof()): ?>
        	<tr valign="bottom">
        		<td colspan="8" class="headseparator">&nbsp;</td>
        	</tr>
    	<?php endif; ?>
    	
    	<?php $this->_tag_stack[] = array('iterate', array('from' => 'submissions','item' => 'submission')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
    	<?php $this->assign('highlightClass', $this->_tpl_vars['submission']->getHighlightClass()); ?>
    	<?php $this->assign('fastTracked', $this->_tpl_vars['submission']->getFastTracked()); ?>
    	<tr valign="review-top"<?php if ($this->_tpl_vars['highlightClass'] || $this->_tpl_vars['fastTracked']): ?> class="<?php echo ((is_array($_tmp=$this->_tpl_vars['highlightClass'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['fastTracked']): ?> fastTracked<?php endif; ?>"<?php endif; ?>>
    		<?php if (! isset ( $this->_tpl_vars['highlightClass'] )): ?>
    		<td></td>
    		<?php endif; ?>
    		<td><?php echo $this->_tpl_vars['submission']->getId(); ?>
</td>
    		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['submission']->getDateSubmitted())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>
</td>
    		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['submission']->getSectionAbbrev())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
    		<td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['submission']->getAuthorString(true))) ? $this->_run_mod_handler('truncate', true, $_tmp, 40, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 40, "...")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
    		<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submissionReview','path' => $this->_tpl_vars['submission']->getId()), $this);?>
" class="action"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['submission']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 70, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 70, "...")); ?>
</a></td>
    		<td>
    			<table width="100%">
    			<?php $_from = $this->_tpl_vars['submission']->getReviewAssignments(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['reviewAssignments']):
?>
    				<?php $_from = $this->_tpl_vars['reviewAssignments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['assignmentList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['assignmentList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['assignment']):
        $this->_foreach['assignmentList']['iteration']++;
?>
    					<?php if (! $this->_tpl_vars['assignment']->getCancelled() && ! $this->_tpl_vars['assignment']->getDeclined()): ?>
    					<tr valign="time-review-top">
    						<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em"><?php if ($this->_tpl_vars['assignment']->getDateNotified()): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['assignment']->getDateNotified())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatTrunc']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatTrunc'])); ?>
<?php else: ?>&mdash;<?php endif; ?></td>
    						<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em"><?php if ($this->_tpl_vars['assignment']->getDateCompleted() || ! $this->_tpl_vars['assignment']->getDateConfirmed()): ?>&mdash;<?php else: ?><?php echo ((is_array($_tmp=@$this->_tpl_vars['assignment']->getWeeksDue())) ? $this->_run_mod_handler('default', true, $_tmp, "&mdash;") : smarty_modifier_default($_tmp, "&mdash;")); ?>
<?php endif; ?></td>
    						<td width="34%" style="padding: 0 4px 0 0; font-size: 1.0em"><?php if ($this->_tpl_vars['assignment']->getDateCompleted()): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['assignment']->getDateCompleted())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatTrunc']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatTrunc'])); ?>
<?php else: ?>&mdash;<?php endif; ?></td>
    					</tr>
    					<?php endif; ?>
    				<?php endforeach; else: ?>
    				<tr valign="time-review-top">
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">&mdash;</td>
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">&mdash;</td>
    					<td width="34%" style="padding: 0 0 0 0; font-size: 1.0em">&mdash;</td>
    				</tr>
    				<?php endif; unset($_from); ?>
    			<?php endforeach; else: ?>
    				<tr valign="time-review-top">
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">&mdash;</td>
    					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">&mdash;</td>
    					<td width="34%" style="padding: 0 0 0 0; font-size: 1.0em">&mdash;</td>
    				</tr>
    			<?php endif; unset($_from); ?>
    			</table>
    		</td>
    		<td>
    			<?php $_from = $this->_tpl_vars['submission']->getDecisions(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['decisions']):
?>
    				<?php $_from = $this->_tpl_vars['decisions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['decisionList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['decisionList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['decision']):
        $this->_foreach['decisionList']['iteration']++;
?>
    					<?php if (($this->_foreach['decisionList']['iteration'] == $this->_foreach['decisionList']['total'])): ?>
    							<?php echo ((is_array($_tmp=$this->_tpl_vars['decision']['dateDecided'])) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatTrunc']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatTrunc'])); ?>
				
    					<?php endif; ?>
    				<?php endforeach; else: ?>
    					&mdash;
    				<?php endif; unset($_from); ?>
    			<?php endforeach; else: ?>
    				&mdash;
    			<?php endif; unset($_from); ?>
    		</td>
    		<td align="center">
    			<?php $this->assign('editAssignments', $this->_tpl_vars['submission']->getEditAssignments()); ?>
    			<?php $_from = $this->_tpl_vars['editAssignments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['editAssignment']):
?><?php echo ((is_array($_tmp=$this->_tpl_vars['editAssignment']->getEditorInitials())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php endforeach; endif; unset($_from); ?>
    		</td>
    	</tr>
    	<?php if ($this->_tpl_vars['submissions']->eof()): ?>
        	<tr valign="bottom">
        		<td colspan="8" class="separator">&nbsp;</td>
        	</tr>
    	<?php endif; ?>
    <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    <?php if ($this->_tpl_vars['submissions']->wasEmpty()): ?>
    	<tr valign="top" class="u-hide">
    		<td colspan="8" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submissions.noSubmissions"), $this);?>
</td>
    	</tr>
    	<tr valign="bottom">
    		<td colspan="8" class="separator">&nbsp;</td>
    	</tr>
    <?php else: ?>
    	<tr class="u-hide" valign="bottom">
    		<td colspan="5" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['submissions']), $this);?>
</td>
    		<td colspan="3" align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'submissions','name' => 'submissions','iterator' => $this->_tpl_vars['submissions'],'searchField' => $this->_tpl_vars['searchField'],'searchMatch' => $this->_tpl_vars['searchMatch'],'search' => $this->_tpl_vars['search'],'dateFromDay' => $this->_tpl_vars['dateFromDay'],'dateFromYear' => $this->_tpl_vars['dateFromYear'],'dateFromMonth' => $this->_tpl_vars['dateFromMonth'],'dateToDay' => $this->_tpl_vars['dateToDay'],'dateToYear' => $this->_tpl_vars['dateToYear'],'dateToMonth' => $this->_tpl_vars['dateToMonth'],'dateSearchField' => $this->_tpl_vars['dateSearchField'],'section' => $this->_tpl_vars['section'],'sort' => $this->_tpl_vars['sort'],'sortDirection' => $this->_tpl_vars['sortDirection']), $this);?>
</td>
    	</tr>
    <?php endif; ?>
    </table>
</div>

<p class="fastTracked">Highlighted items indicate action is Fast Tracked</p>

<div class="colspan u-mb-0" id="colspan">	    
	<section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	    <div class="c-pagination"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['submissions']), $this);?>
</div>
    </section>
    <?php if ($this->_tpl_vars['submissions']->getPageCount() > 1): ?>
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'submissions','name' => 'submissions','iterator' => $this->_tpl_vars['submissions'],'searchField' => $this->_tpl_vars['searchField'],'searchMatch' => $this->_tpl_vars['searchMatch'],'search' => $this->_tpl_vars['search'],'dateFromDay' => $this->_tpl_vars['dateFromDay'],'dateFromYear' => $this->_tpl_vars['dateFromYear'],'dateFromMonth' => $this->_tpl_vars['dateFromMonth'],'dateToDay' => $this->_tpl_vars['dateToDay'],'dateToYear' => $this->_tpl_vars['dateToYear'],'dateToMonth' => $this->_tpl_vars['dateToMonth'],'dateSearchField' => $this->_tpl_vars['dateSearchField'],'section' => $this->_tpl_vars['section'],'sort' => $this->_tpl_vars['sort'],'sortDirection' => $this->_tpl_vars['sortDirection']), $this);?>

       </div>
    </section>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="container cleared container-type-title" data-container-type="title">
    <div class="border-top-1 border-gray-medium"></div>
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
        </div>
        <div class="c-empty-state-card__text">
            <h3 class="c-empty-state-card__text--title headline-5"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submissions.noSubmissions"), $this);?>
 are currently in review</h3>
            <div class="c-empty-state-card__text--description">All submissions have either completed the review process or are in other editorial stages. New submissions will appear here once they enter the peer review phase and are assigned to reviewers.</div>
        </div>
    </div>
</div>
<?php endif; ?>