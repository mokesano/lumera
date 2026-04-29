<?php /* Smarty version 2.6.26, created on 2026-04-15 01:14:18
         compiled from sectionEditor/submissionNotes.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'sectionEditor/submissionNotes.tpl', 50, false),array('function', 'translate', 'sectionEditor/submissionNotes.tpl', 50, false),array('function', 'page_info', 'sectionEditor/submissionNotes.tpl', 169, false),array('function', 'page_links', 'sectionEditor/submissionNotes.tpl', 170, false),array('modifier', 'date_format', 'sectionEditor/submissionNotes.tpl', 77, false),array('modifier', 'escape', 'sectionEditor/submissionNotes.tpl', 81, false),array('modifier', 'strip_unsafe_html', 'sectionEditor/submissionNotes.tpl', 85, false),array('modifier', 'to_array', 'sectionEditor/submissionNotes.tpl', 93, false),array('modifier', 'nl2br', 'sectionEditor/submissionNotes.tpl', 150, false),array('block', 'iterate', 'sectionEditor/submissionNotes.tpl', 140, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "submission.notes"); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitle', "submission.notes.breadcrumb"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php echo '
<script type="text/javascript">
<!--
	var toggleAll = 0;
	var noteArray = new Array();
	function toggleNote(divNoteId) {
		var domStyle = getBrowserObject("note" + divNoteId,1);
		domStyle.display = (domStyle.display == "block") ? "none" : "block";
	}

	function toggleNoteAll() {
		for(var i = 0; i < noteArray.length; i++) {
			var domStyle = getBrowserObject("note" + noteArray[i],1);
			domStyle.display = toggleAll ? "none" : "block";
		}
		toggleAll = toggleAll ? 0 : 1;

		var collapse = getBrowserObject("collapseNotes",1);
		var expand = getBrowserObject("expandNotes",1);
		if (collapse.display == "inline") {
			collapse.display = "none";
			expand.display = "inline";
		} else {
			collapse.display = "inline";
			expand.display = "none";
		}
	}
// -->
</script>
'; ?>


<ul class="menu">
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submission','path' => $this->_tpl_vars['submission']->getId()), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.summary"), $this);?>
</a></li>
	<?php if ($this->_tpl_vars['canReview']): ?><li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submissionReview','path' => $this->_tpl_vars['submission']->getId()), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.review"), $this);?>
</a></li><?php endif; ?>
	<?php if ($this->_tpl_vars['canEdit']): ?><li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submissionEditing','path' => $this->_tpl_vars['submission']->getId()), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.editing"), $this);?>
</a></li><?php endif; ?>
	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submissionHistory','path' => $this->_tpl_vars['submission']->getId()), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.history"), $this);?>
</a></li>
</ul>
<div class="pseudoMenu">
    <ul class="menu">
    	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submissionEventLog','path' => $this->_tpl_vars['submission']->getId()), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.history.submissionEventLog"), $this);?>
</a></li>
    	<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submissionEmailLog','path' => $this->_tpl_vars['submission']->getId()), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.history.submissionEmailLog"), $this);?>
</a></li>
    	<li class="current"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submissionNotes','path' => $this->_tpl_vars['submission']->getId()), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.history.submissionNotes"), $this);?>
</a></li>
    </ul>
</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "sectionEditor/submission/summary.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div class="separator"></div>

<div id="submissionNotes">
    <?php if ($this->_tpl_vars['noteViewType'] == 'edit'): ?>
    <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes"), $this);?>
</h3>
    <form name="editNote" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'updateSubmissionNote'), $this);?>
" enctype="multipart/form-data">
    	<input type="hidden" name="articleId" value="<?php echo $this->_tpl_vars['articleNote']->getArticleId(); ?>
" />
    	<input type="hidden" name="noteId" value="<?php echo $this->_tpl_vars['articleNote']->getId(); ?>
" />
    	<input type="hidden" name="fileId" value="<?php echo $this->_tpl_vars['articleNote']->getFileId(); ?>
" />
    
    <table width="100%" class="data">
    	<tr valign="top">
    		<td class="label" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.dateModified"), $this);?>
</td>
    		<td class="value" width="80%"><?php echo ((is_array($_tmp=$this->_tpl_vars['articleNote']->getDateModified())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatShort'])); ?>
</td>
    	</tr>
    	<tr valign="top">
    		<td class="label" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.title"), $this);?>
</td>
    		<td class="value" width="80%"><input type="text" name="title" id="title" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['articleNote']->getTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="50" maxlength="120" class="textField" /></td>
    	</tr>
    	<tr valign="top">
    		<td class="label" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.note"), $this);?>
</td>
    		<td class="value" width="80%"><textarea name="note" id="note" rows="10" cols="50" class="textArea"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['articleNote']->getNote())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
    	</tr>
    	<tr valign="top">
    		<td class="label" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.file"), $this);?>
</td>
    		<td class="value" width="80%"><input type="file" id="upload" name="upload" class="uploadField" /></td>
    	</tr>
    	<tr valign="top">
    		<td class="label" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.uploadedFile"), $this);?>
</td>
    		<td class="value" width="80%"><?php if ($this->_tpl_vars['articleNote']->getFileId()): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'downloadFile','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['articleNote']->getFileId()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['articleNote']->getFileId()))), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['articleNote']->getOriginalFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a><br /><input type="checkbox" name="removeUploadedFile" value="1" />&nbsp;<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.removeUploadedFile"), $this);?>
<?php else: ?>&mdash;<?php endif; ?></td>
    	</tr>
    </table>
    <br />
    <input type="button" class="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.deleteNote"), $this);?>
" onclick="confirmAction('<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'removeSubmissionNote','articleId' => $this->_tpl_vars['articleNote']->getArticleId(),'noteId' => $this->_tpl_vars['articleNote']->getId(),'fileId' => $this->_tpl_vars['articleNote']->getFileId()), $this);?>
', '<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.confirmDelete"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" />&nbsp;<input type="submit" class="button defaultButton" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.updateNote"), $this);?>
" />
    </form>
    
    <?php elseif ($this->_tpl_vars['noteViewType'] == 'add'): ?>
    	<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.addNewNote"), $this);?>
</h3>
    	<form name="addNote" method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'addSubmissionNote'), $this);?>
" enctype="multipart/form-data">
        	<input type="hidden" name="articleId" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
        	<table width="100%" class="data">
        	    <thead>
                	<tr valign="top">
                		<td class="label" width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.title"), $this);?>
</td>
                		<td class="value" width="80%"><input type="text" id="title" name="title" size="50" maxlength="90" class="textField" /></td>
                	</tr>
        	    </thead>
            	<tbody>
                	<tr valign="top">
                		<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.note"), $this);?>
</td>
                		<td class="value"><textarea name="note" id="note" rows="10" cols="50" class="textArea"></textarea></td>
                	</tr>
                	<tr valign="top">
                		<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.file"), $this);?>
</td>
                		<td class="value"><input type="file" name="upload" class="uploadField" /></td>
                	</tr>
            	</tbody>
        	</table>
        	<br/>
        	<input type="submit" class="button defaultButton" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.createNewNote"), $this);?>
" />
    	</form>
    <?php else: ?>
    <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes"), $this);?>
</h3>

    <table width="100%" class="listing">
        <thead>
        	<tr><td colspan="6" class="headseparator">&nbsp;</td></tr>
        	<tr class="heading" valign="bottom">
        		<td width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.date"), $this);?>
</td>
        		<td width="45%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.title"), $this);?>
</td>
        		<td width="35%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.attachedFile"), $this);?>
</td>
        		<td width="10%" align="right"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.action"), $this);?>
</td>
        	</tr>
        	<tr><td colspan="6" class="headseparator">&nbsp;</td></tr>
        </thead>
        <tbody>
        <?php $this->_tag_stack[] = array('iterate', array('from' => 'submissionNotes','item' => 'note')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
        	<tr valign="top">
        		<td>
        			<script type="text/javascript">
        			<!--
        				noteArray.push(<?php echo $this->_tpl_vars['note']->getId(); ?>
);
        			// -->
        			</script>
        			<?php echo ((is_array($_tmp=$this->_tpl_vars['note']->getDateCreated())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatTrunc']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatTrunc'])); ?>

        		</td>
        		<td><a class="action" href="javascript:toggleNote(<?php echo $this->_tpl_vars['note']->getId(); ?>
)"><?php echo ((is_array($_tmp=$this->_tpl_vars['note']->getTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a><div style="display: none" id="note<?php echo $this->_tpl_vars['note']->getId(); ?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['note']->getNote())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</div></td>
        		<td><?php if ($this->_tpl_vars['note']->getFileId()): ?><a class="file" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'downloadFile','path' => ((is_array($_tmp=$this->_tpl_vars['submission']->getId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['note']->getFileId()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['note']->getFileId()))), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['note']->getOriginalFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a><?php else: ?>&mdash;<?php endif; ?></td>
        		<td align="right"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submissionNotes','path' => ((is_array($_tmp=$this->_tpl_vars['submission']->getId())) ? $this->_run_mod_handler('to_array', true, $_tmp, 'edit', $this->_tpl_vars['note']->getId()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, 'edit', $this->_tpl_vars['note']->getId()))), $this);?>
" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.view"), $this);?>
</a>&nbsp;|&nbsp;<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'removeSubmissionNote','articleId' => $this->_tpl_vars['submission']->getId(),'noteId' => $this->_tpl_vars['note']->getId(),'fileId' => $this->_tpl_vars['note']->getFileId()), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.confirmDelete"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')" class="action"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
</a></td>
        	</tr>
        	<?php if ($this->_tpl_vars['submissionNotes']->eof()): ?>
        	<tr valign="top">
        		<td colspan="6" class="endseparator">&nbsp;</td>
        	</tr>
        	<?php endif; ?>
        <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        <?php if ($this->_tpl_vars['submissionNotes']->wasEmpty()): ?>
        	<tr valign="top">
        		<td colspan="6" class="nodata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.noSubmissionNotes"), $this);?>
</td>
        	</tr>
        	<tr valign="top">
        		<td colspan="6" class="endseparator">&nbsp;</td>
        	</tr>
        <?php else: ?>
        	<tr>
        		<td colspan="3" align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['submissionNotes']), $this);?>
</td>
        		<td colspan="3" align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'submissionNotes','name' => 'submissionNotes','iterator' => $this->_tpl_vars['submissionNotes']), $this);?>
</td>
        	</tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="block">
        <a class="link-button" href="javascript:toggleNoteAll()"><div style="display:inline" id="expandNotes" name="expandNotes"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.expandNotes"), $this);?>
</div><div style="display: none" id="collapseNotes" name="collapseNotes"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.collapseNotes"), $this);?>
</div></a> |
        <a class="link-button" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submissionNotes','path' => ((is_array($_tmp=$this->_tpl_vars['submission']->getId())) ? $this->_run_mod_handler('to_array', true, $_tmp, 'add') : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, 'add'))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.addNewNote"), $this);?>
</a> |
        <a class="link-button" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'clearAllSubmissionNotes','articleId' => $this->_tpl_vars['submission']->getId()), $this);?>
" onclick="return confirm('<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.confirmDeleteAll"), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'jsparam') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'jsparam'));?>
')"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.notes.clearAllNotes"), $this);?>
</a>
        <?php endif; ?>
    </div>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
