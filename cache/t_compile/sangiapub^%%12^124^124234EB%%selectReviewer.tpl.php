<?php /* Smarty version 2.6.26, created on 2026-04-27 13:13:20
         compiled from sectionEditor/selectReviewer.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'sectionEditor/selectReviewer.tpl', 30, false),array('function', 'url', 'sectionEditor/selectReviewer.tpl', 31, false),array('function', 'html_options_translate', 'sectionEditor/selectReviewer.tpl', 35, false),array('function', 'sort_search', 'sectionEditor/selectReviewer.tpl', 60, false),array('function', 'math', 'sectionEditor/selectReviewer.tpl', 95, false),array('function', 'page_info', 'sectionEditor/selectReviewer.tpl', 127, false),array('function', 'page_links', 'sectionEditor/selectReviewer.tpl', 128, false),array('modifier', 'escape', 'sectionEditor/selectReviewer.tpl', 42, false),array('modifier', 'string_format', 'sectionEditor/selectReviewer.tpl', 81, false),array('modifier', 'date_format', 'sectionEditor/selectReviewer.tpl', 100, false),array('modifier', 'default', 'sectionEditor/selectReviewer.tpl', 101, false),array('modifier', 'to_array', 'sectionEditor/selectReviewer.tpl', 105, false),array('block', 'iterate', 'sectionEditor/selectReviewer.tpl', 72, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "user.role.reviewers"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<script type="text/javascript">
<?php echo '
<!--
function sortSearch(heading, direction) {
	var submitForm = document.getElementById(\'submit\');
	submitForm.sort.value = heading;
	submitForm.sortDirection.value = direction;
	submitForm.submit();
}
// -->
'; ?>

</script>

<div id="selectReviewer">
    <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.article.selectReviewer"), $this);?>
</h3>
    <form id="submit" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'selectReviewer','path' => $this->_tpl_vars['articleId']), $this);?>
">
    	<input type="hidden" name="sort" value="name"/>
    	<input type="hidden" name="sortDirection" value="ASC"/>
    	<select name="searchField" size="1" class="selectMenu">
    		<?php echo $this->_plugins['function']['html_options_translate'][0][0]->smartyHtmlOptionsTranslate(array('options' => $this->_tpl_vars['fieldOptions'],'selected' => $this->_tpl_vars['searchField']), $this);?>

    	</select>
    	<select name="searchMatch" size="1" class="selectMenu">
    		<option value="contains"<?php if ($this->_tpl_vars['searchMatch'] == 'contains'): ?> selected="selected"<?php endif; ?>><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.contains"), $this);?>
</option>
    		<option value="is"<?php if ($this->_tpl_vars['searchMatch'] == 'is'): ?> selected="selected"<?php endif; ?>><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.is"), $this);?>
</option>
    		<option value="startsWith"<?php if ($this->_tpl_vars['searchMatch'] == 'startsWith'): ?> selected="selected"<?php endif; ?>><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.startsWith"), $this);?>
</option>
    	</select>
    	<input type="text" size="10" name="search" class="textField" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['search'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />&nbsp;<input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.search"), $this);?>
" class="button" />
    </form>
    
    <div class="c-jump">
        <p class="describe italic u-font-sans">Click the alphabet to chose name of journal(s)</p>
        <span><?php $_from = $this->_tpl_vars['alphaList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['letter']):
?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'selectReviewer','path' => $this->_tpl_vars['articleId'],'searchInitial' => $this->_tpl_vars['letter']), $this);?>
" class="c-jump-navigation__link u-margin-bottom-xxs-at-md u-mr-4"><?php if ($this->_tpl_vars['letter'] == $this->_tpl_vars['searchInitial']): ?><strong><?php echo ((is_array($_tmp=$this->_tpl_vars['letter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['letter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></a><?php endforeach; endif; unset($_from); ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'selectReviewer','path' => $this->_tpl_vars['articleId']), $this);?>
" class="c-jump-navigation__link u-margin-bottom-xxs-at-md u-mr-4"><?php if ($this->_tpl_vars['searchInitial'] == ''): ?><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.all"), $this);?>
</strong><?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.all"), $this);?>
<?php endif; ?></a></span>
    </div>
    
    <p class="u-mb-16"><a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'enrollSearch','path' => $this->_tpl_vars['articleId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "sectionEditor.review.enrollReviewer"), $this);?>
</a>&nbsp;|&nbsp;<a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'createReviewer','path' => $this->_tpl_vars['articleId']), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "sectionEditor.review.createReviewer"), $this);?>
</a><?php $_from = $this->_tpl_vars['reviewerDatabaseLinks']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['link']):
?><?php if (! empty ( $this->_tpl_vars['link']['title'] ) && ! empty ( $this->_tpl_vars['link']['url'] )): ?>&nbsp;|&nbsp;<a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['link']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_new" class="action"><?php echo ((is_array($_tmp=$this->_tpl_vars['link']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a><?php endif; ?><?php endforeach; endif; unset($_from); ?></p>

    <div id="reviewers">
    <table class="listing" width="100%">
        <?php $this->assign('numCols', 7); ?>
        <?php if ($this->_tpl_vars['rateReviewerOnQuality']): ?>
        	<?php $this->assign('numCols', $this->_tpl_vars['numCols']+1); ?>
        <?php endif; ?>
        <tr><td colspan="<?php echo ((is_array($_tmp=$this->_tpl_vars['numCols'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="headseparator">&nbsp;</td></tr>
        <tr class="heading" valign="bottom">
        	<td width="20%"><?php echo $this->_plugins['function']['sort_search'][0][0]->smartySortSearch(array('key' => "user.name",'sort' => 'reviewerName'), $this);?>
</td>
        	<td><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.interests"), $this);?>
</td>
        	<?php if ($this->_tpl_vars['rateReviewerOnQuality']): ?>
        		<td width="7%"><?php echo $this->_plugins['function']['sort_search'][0][0]->smartySortSearch(array('key' => "reviewer.averageQuality",'sort' => 'quality'), $this);?>
</td>
        	<?php endif; ?>
        	<td width="7%"><?php echo $this->_plugins['function']['sort_search'][0][0]->smartySortSearch(array('key' => "reviewer.completedReviews",'sort' => 'done'), $this);?>
</td>
        	<td width="7%"><?php echo $this->_plugins['function']['sort_search'][0][0]->smartySortSearch(array('key' => "editor.submissions.averageTime",'sort' => 'average'), $this);?>
</td>
        	<td width="13%"><?php echo $this->_plugins['function']['sort_search'][0][0]->smartySortSearch(array('key' => "editor.submissions.lastAssigned",'sort' => 'latest'), $this);?>
</td>
        	<td width="5%"><?php echo $this->_plugins['function']['sort_search'][0][0]->smartySortSearch(array('key' => "common.active",'sort' => 'active'), $this);?>
</td>
        	<td width="7%" class="heading"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</td>
        </tr>
        <tr><td colspan="<?php echo ((is_array($_tmp=$this->_tpl_vars['numCols'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="headseparator">&nbsp;</td></tr>
        <?php $this->_tag_stack[] = array('iterate', array('from' => 'reviewers','item' => 'reviewer')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
            <?php $this->assign('userId', $this->_tpl_vars['reviewer']->getId()); ?>
            <?php $this->assign('qualityCount', $this->_tpl_vars['averageQualityRatings'][$this->_tpl_vars['userId']]['count']); ?>
            <?php $this->assign('reviewerStats', $this->_tpl_vars['reviewerStatistics'][$this->_tpl_vars['userId']]); ?>
            
            <tr valign="top"<?php if ($this->_tpl_vars['reviewer']->review_id && ! $this->_tpl_vars['reviewer']->declined): ?> class="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.alreadyAssigned"), $this);?>
"<?php endif; ?>>
            	<td><a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'userProfile','path' => $this->_tpl_vars['userId']), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['reviewer']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></td>
            	<td><?php echo ((is_array($_tmp=$this->_tpl_vars['reviewer']->getInterestString())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
            	<?php if ($this->_tpl_vars['rateReviewerOnQuality']): ?><td>
            		<?php if ($this->_tpl_vars['qualityCount']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['averageQualityRatings'][$this->_tpl_vars['userId']]['average'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%.1f") : smarty_modifier_string_format($_tmp, "%.1f")); ?>

            		<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.notApplicableShort"), $this);?>
<?php endif; ?>
            	</td><?php endif; ?>
            
            	<td>
            		<?php if ($this->_tpl_vars['completedReviewCounts'][$this->_tpl_vars['userId']]): ?>
            			<?php echo $this->_tpl_vars['completedReviewCounts'][$this->_tpl_vars['userId']]; ?>

            		<?php else: ?>
            			0
            		<?php endif; ?>
            	</td>
            
            	<td>
            		<?php if ($this->_tpl_vars['reviewerStats']['average_span']): ?>
            			<?php echo smarty_function_math(array('equation' => "round(theSpan)",'theSpan' => $this->_tpl_vars['reviewerStats']['average_span']), $this);?>

            		<?php else: ?>
            			&mdash;
            		<?php endif; ?>
            	</td>
            	<td><?php if ($this->_tpl_vars['reviewerStats']['last_notified']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['reviewerStats']['last_notified'])) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>
<?php else: ?>&mdash;<?php endif; ?></td>
            	<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['reviewerStats']['incomplete'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0)); ?>
</td>
            	<td>
            		<?php if ($this->_tpl_vars['reviewer']->review_id): ?>
            			<?php if ($this->_tpl_vars['reviewer']->declined): ?>
            				<a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'reassignReviewer','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['reviewer']->getUserId()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['reviewer']->getUserId()))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.reassign"), $this);?>
</a>
            			<?php else: ?>
            				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.alreadyAssigned"), $this);?>

            			<?php endif; ?>
            		<?php else: ?>
            		<a class="action" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'selectReviewer','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['reviewer']->getId()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['reviewer']->getId()))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.assign"), $this);?>
</a>
            		<?php endif; ?>
            	</td>
            </tr>
            <?php if ($this->_tpl_vars['reviewers']->eof()): ?>
                <tr><td colspan="<?php echo ((is_array($_tmp=$this->_tpl_vars['numCols'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="endseparator">&nbsp;</td></tr>
            <?php endif; ?>
        <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        <?php if ($this->_tpl_vars['reviewers']->wasEmpty()): ?>
            <tr>
                <td colspan="<?php echo ((is_array($_tmp=$this->_tpl_vars['numCols'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.people.noneEnrolled"), $this);?>
</td>
            </tr>
            <tr>
                <td colspan="<?php echo ((is_array($_tmp=$this->_tpl_vars['numCols'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="endseparator">&nbsp;</td>
            </tr>
        <?php else: ?>
        	<tr class="u-hide">
        		<td colspan="2" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['reviewers']), $this);?>
</td>
        		<td colspan="<?php echo $this->_tpl_vars['numCols']-2; ?>
" align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'reviewers','name' => 'reviewers','iterator' => $this->_tpl_vars['reviewers'],'searchInitial' => $this->_tpl_vars['searchInitial'],'searchField' => $this->_tpl_vars['searchField'],'searchMatch' => $this->_tpl_vars['searchMatch'],'search' => $this->_tpl_vars['search'],'dateFromDay' => $this->_tpl_vars['dateFromDay'],'dateFromYear' => $this->_tpl_vars['dateFromYear'],'dateFromMonth' => $this->_tpl_vars['dateFromMonth'],'dateToDay' => $this->_tpl_vars['dateToDay'],'dateToYear' => $this->_tpl_vars['dateToYear'],'dateToMonth' => $this->_tpl_vars['dateToMonth'],'sort' => $this->_tpl_vars['sort'],'sortDirection' => $this->_tpl_vars['sortDirection']), $this);?>
</td>
        	</tr>
        <?php endif; ?>
    </table>

    <?php if (! $this->_tpl_vars['reviewers']->wasEmpty()): ?>
    <div class="colspan u-mb-0" id="colspan">	    
    	<section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
    	    <div class="c-pagination"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['reviewers']), $this);?>
</div>
        </section>
        <?php if ($this->_tpl_vars['reviewers']->getPageCount() > 1): ?>
        <section class="u-display-flex u-justify-content-center">
            <div class="c-pagination"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'reviewers','name' => 'reviewers','iterator' => $this->_tpl_vars['reviewers'],'searchInitial' => $this->_tpl_vars['searchInitial'],'searchField' => $this->_tpl_vars['searchField'],'searchMatch' => $this->_tpl_vars['searchMatch'],'search' => $this->_tpl_vars['search'],'dateFromDay' => $this->_tpl_vars['dateFromDay'],'dateFromYear' => $this->_tpl_vars['dateFromYear'],'dateFromMonth' => $this->_tpl_vars['dateFromMonth'],'dateToDay' => $this->_tpl_vars['dateToDay'],'dateToYear' => $this->_tpl_vars['dateToYear'],'dateToMonth' => $this->_tpl_vars['dateToMonth'],'sort' => $this->_tpl_vars['sort'],'sortDirection' => $this->_tpl_vars['sortDirection']), $this);?>

           </div>
        </section>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <h4 class="u-h3"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.notes"), $this);?>
</h4>
    <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.article.selectReviewerNotes"), $this);?>
</p>
    </div>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
