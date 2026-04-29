<?php /* Smarty version 2.6.26, created on 2026-04-04 05:39:07
         compiled from author/completed.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'sort_heading', 'author/completed.tpl', 15, false),array('function', 'translate', 'author/completed.tpl', 16, false),array('function', 'url', 'author/completed.tpl', 31, false),array('function', 'print_issue_id', 'author/completed.tpl', 49, false),array('function', 'page_info', 'author/completed.tpl', 65, false),array('function', 'page_links', 'author/completed.tpl', 66, false),array('block', 'iterate', 'author/completed.tpl', 24, false),array('modifier', 'escape', 'author/completed.tpl', 27, false),array('modifier', 'date_format', 'author/completed.tpl', 28, false),array('modifier', 'truncate', 'author/completed.tpl', 30, false),array('modifier', 'strip_unsafe_html', 'author/completed.tpl', 31, false),array('modifier', 'nl2br', 'author/completed.tpl', 31, false),)), $this); ?>
<div id="submissions">
    <table class="listing" width="100%">
    	<tr><td class="headseparator" colspan="<?php if ($this->_tpl_vars['statViews']): ?>7<?php else: ?>6<?php endif; ?>">&nbsp;</td></tr>
    	<tr valign="bottom" class="heading">
    		<td width="5%"><?php echo $this->_plugins['function']['sort_heading'][0][0]->smartySortHeading(array('key' => "common.id",'sort' => 'id'), $this);?>
</td>
    		<td width="10%"><span class="disabled"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.date.mmdd"), $this);?>
</span><br /><?php echo $this->_plugins['function']['sort_heading'][0][0]->smartySortHeading(array('key' => "submissions.submit",'sort' => 'submitDate'), $this);?>
</td>
    		<td width="5%"><?php echo $this->_plugins['function']['sort_heading'][0][0]->smartySortHeading(array('key' => "submissions.sec",'sort' => 'section'), $this);?>
</td>
    		<td width="15%"><?php echo $this->_plugins['function']['sort_heading'][0][0]->smartySortHeading(array('key' => "article.authors",'sort' => 'authors'), $this);?>
</td>
    		<td width="45%"><?php echo $this->_plugins['function']['sort_heading'][0][0]->smartySortHeading(array('key' => "article.title",'sort' => 'title'), $this);?>
</td>
    		<?php if ($this->_tpl_vars['statViews']): ?><td width="5%"><?php echo $this->_plugins['function']['sort_heading'][0][0]->smartySortHeading(array('key' => "submission.views",'sort' => 'views'), $this);?>
</td><?php endif; ?>
    		<td width="20%" align="center"><?php echo $this->_plugins['function']['sort_heading'][0][0]->smartySortHeading(array('key' => "common.status",'sort' => 'status'), $this);?>
</td>
    	</tr>
    	
        <?php $this->_tag_stack[] = array('iterate', array('from' => 'submissions','item' => 'submission')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
        	<?php $this->assign('articleId', $this->_tpl_vars['submission']->getId()); ?>
        	<tr valign="top">
        		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
        		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['submission']->getDateSubmitted())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>
</td>
        		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['submission']->getSectionAbbrev())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
        		<td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['submission']->getAuthorString(true))) ? $this->_run_mod_handler('truncate', true, $_tmp, 40, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 40, "...")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
        		<td><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submission','path' => $this->_tpl_vars['articleId']), $this);?>
" class="action"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['submission']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</a></td>
        		<?php $this->assign('status', $this->_tpl_vars['submission']->getSubmissionStatus()); ?>
        		<?php if ($this->_tpl_vars['statViews']): ?>
        			<td>
        				<?php if ($this->_tpl_vars['status'] == STATUS_PUBLISHED): ?>
        					<?php $this->assign('viewCount', 0); ?>
        					<?php $_from = $this->_tpl_vars['submission']->getGalleys(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['galley']):
?>
        						<?php $this->assign('thisCount', $this->_tpl_vars['galley']->getViews()); ?>
        						<?php $this->assign('viewCount', $this->_tpl_vars['viewCount']+$this->_tpl_vars['thisCount']); ?>
        					<?php endforeach; endif; unset($_from); ?>
        					<?php echo ((is_array($_tmp=$this->_tpl_vars['viewCount'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

        				<?php else: ?>
        					&mdash;
        				<?php endif; ?>
        			</td>
        		<?php endif; ?>
        		<td align="right">
        			<?php if ($this->_tpl_vars['status'] == STATUS_ARCHIVED): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submissions.archived"), $this);?>

        			<?php elseif ($this->_tpl_vars['status'] == STATUS_PUBLISHED): ?><?php echo $this->_plugins['function']['print_issue_id'][0][0]->smartyPrintIssueId(array('articleId' => ($this->_tpl_vars['articleId'])), $this);?>

        			<?php elseif ($this->_tpl_vars['status'] == STATUS_DECLINED): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submissions.declined"), $this);?>

        			<?php endif; ?>
        		</td>
        	</tr>
        <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        
        <?php if ($this->_tpl_vars['submissions']->wasEmpty()): ?>
        	<tr class="u-hide">
        		<td colspan="<?php if ($this->_tpl_vars['statViews']): ?>7<?php else: ?>6<?php endif; ?>" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submissions.noSubmissions"), $this);?>
</td>
        	</tr>
        	<tr>
        		<td colspan="<?php if ($this->_tpl_vars['statViews']): ?>7<?php else: ?>6<?php endif; ?>" class="endseparator">&nbsp;</td>
        	</tr>
        <?php else: ?>
        	<tr class="functions-bar functions-bar-bottom u-hide">
        		<td colspan="<?php if ($this->_tpl_vars['statViews']): ?>5<?php else: ?>4<?php endif; ?>" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['submissions']), $this);?>
</td>
        		<td colspan="2" align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'submissions','name' => 'submissions','iterator' => $this->_tpl_vars['submissions'],'sort' => $this->_tpl_vars['sort'],'sortDirection' => $this->_tpl_vars['sortDirection']), $this);?>
</td>
        	</tr>
        <?php endif; ?>
    </table>
</div>

<?php if ($this->_tpl_vars['submissions']->wasEmpty()): ?>
<div class="container cleared container-type-title" data-container-type="title">
    <div class="border-top-1 border-gray-medium"></div>
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
        </div>
        <div class="c-empty-state-card__text">
            <h3 class="c-empty-state-card__text--title headline-5"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submissions.noSubmissions"), $this);?>
</h3>
            <div class="c-empty-state-card__text--description">All current submissions or new submissions will appear here.</div>
        </div>
    </div>
</div>

<?php else: ?>

<p class="archives-fastTracked"> Items indicate is Fast Tracked</p>

<div class="colspan u-mb-0" id="colspan">	    
	<section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	    <div class="c-pagination"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['submissions']), $this);?>
</div>
    </section>
    <?php if ($this->_tpl_vars['submissions']->getPageCount() > 1): ?>
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'submissions','name' => 'submissions','iterator' => $this->_tpl_vars['submissions'],'sort' => $this->_tpl_vars['sort'],'sortDirection' => $this->_tpl_vars['sortDirection']), $this);?>

       </div>
    </section>
    <?php endif; ?>
</div>
<?php endif; ?>