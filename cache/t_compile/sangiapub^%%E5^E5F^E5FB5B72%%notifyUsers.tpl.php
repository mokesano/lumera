<?php /* Smarty version 2.6.26, created on 2026-04-27 12:59:41
         compiled from editor/notifyUsers.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'editor/notifyUsers.tpl', 19, false),array('modifier', 'assign', 'editor/notifyUsers.tpl', 36, false),array('modifier', 'default', 'editor/notifyUsers.tpl', 52, false),array('modifier', 'strip_tags', 'editor/notifyUsers.tpl', 111, false),array('modifier', 'truncate', 'editor/notifyUsers.tpl', 111, false),array('function', 'translate', 'editor/notifyUsers.tpl', 32, false),array('function', 'fieldLabel', 'editor/notifyUsers.tpl', 128, false),array('block', 'iterate', 'editor/notifyUsers.tpl', 110, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "email.compose"); ?><?php echo ''; ?><?php $this->assign('pageCrumbTitle', "email.email"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div id="notifyUsers">
<form method="post" action="<?php echo $this->_tpl_vars['formActionUrl']; ?>
">
    <input type="hidden" name="csrfToken" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <input type="hidden" name="continued" value="1"/>
    <?php if ($this->_tpl_vars['hiddenFormParams']): ?>
    	<?php $_from = $this->_tpl_vars['hiddenFormParams']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['hiddenFormParam']):
?>
    		<input type="hidden" name="<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['hiddenFormParam'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    	<?php endforeach; endif; unset($_from); ?>
    <?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php $_from = $this->_tpl_vars['errorMessages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['message']):
?>
	<?php if (! $this->_tpl_vars['notFirstMessage']): ?>
		<?php $this->assign('notFirstMessage', 1); ?>
		<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.errorsOccurred"), $this);?>
</h4>
		<ul class="plain">
	<?php endif; ?>
	<?php if ($this->_tpl_vars['message']['type'] == MAIL_ERROR_INVALID_EMAIL): ?>
		<?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.invalid",'email' => $this->_tpl_vars['message']['address']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'message') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'message'));?>

		<li><?php echo ((is_array($_tmp=$this->_tpl_vars['message'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
	<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>

<?php if ($this->_tpl_vars['notFirstMessage']): ?>
	</ul>
	<br/>
<?php endif; ?>

<div id="recipients">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.recipients"), $this);?>
</h3>
<table id="recipients" class="listing checklist" width="100%">
<tr valign="top">
	<td><input type="radio" id="allUsers" name="whichUsers" value="allUsers"/></td>
	<td class="label">
		<label for="allUsers"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.notifyUsers.allUsers",'count' => ((is_array($_tmp=@$this->_tpl_vars['allUsersCount'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0))), $this);?>
</label>
	</td>
</tr>
<tr valign="top">
	<td><input type="radio" id="allReaders" name="whichUsers" value="allReaders"/></td>
	<td class="label">
		<label for="allReaders"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.notifyUsers.allReaders",'count' => ((is_array($_tmp=@$this->_tpl_vars['allReadersCount'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0))), $this);?>
</label>
	</td>
</tr>
<tr valign="top">
	<td><input type="radio" id="allAuthors" name="whichUsers" value="allAuthors"/></td>
	<td class="label">
		<label for="allAuthors"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.notifyUsers.allAuthors",'count' => ((is_array($_tmp=@$this->_tpl_vars['allAuthorsCount'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0))), $this);?>
</label>
	</td>
</tr>
<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?>
<tr valign="top">
	<td><input type="radio" id="allIndividualSubscribers" name="whichUsers" value="allIndividualSubscribers"/></td>
	<td class="label">
		<label for="allIndividualSubscribers"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.notifyUsers.allIndividualSubscribers",'count' => ((is_array($_tmp=@$this->_tpl_vars['allIndividualSubscribersCount'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0))), $this);?>
</label>
	</td>
</tr>
<tr valign="top">
	<td><input type="radio" id="allInstitutionalSubscribers" name="whichUsers" value="allInstitutionalSubscribers"/></td>
	<td class="label">
		<label for="allInstitutionalSubscribers"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.notifyUsers.allInstitutionalSubscribers",'count' => ((is_array($_tmp=@$this->_tpl_vars['allInstitutionalSubscribersCount'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0))), $this);?>
</label>
	</td>
</tr>
<?php endif; ?><tr valign="top">
	<td><input type="checkbox" name="sendToMailList" /></td>
	<td class="label">
		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.notifyUsers.allMailingList",'count' => ((is_array($_tmp=@$this->_tpl_vars['allMailListCount'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0))), $this);?>

	</td>
</tr>
<?php if ($this->_tpl_vars['senderEmail']): ?>
	<tr valign="top">
		<td><input type="checkbox" name="ccSelf" /></td>
		<td class="label">
			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.bccSender",'address' => ((is_array($_tmp=$this->_tpl_vars['senderEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>

		</td>
	</tr>
<?php endif; ?>
</table>
</div>
<br/>

<div id="issue">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
</h3>
<table class="data checklist" width="100%">
<tr valign="top">
	<td width="5%">
		<input type="checkbox" name="includeToc" id="includeToc" value="1"/>
	</td>
	<td width="75%" class="label">
		<label for="includeToc"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "editor.notifyUsers.includeToc"), $this);?>
</label>&nbsp;
		<select name="issue" id="issue" class="selectMenu">
			<?php $this->_tag_stack[] = array('iterate', array('from' => 'issues','item' => 'issue')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
				<option <?php if ($this->_tpl_vars['issue']->getCurrent()): ?>checked <?php endif; ?>value="<?php echo $this->_tpl_vars['issue']->getId(); ?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getIssueIdentification())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 40, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 40, "...")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</option>
			<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
		</select>
	</td>
</tr>
</table>
</div>

<br/>


<table id="email" class="data" width="100%">
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.from"), $this);?>
</td>
	<td class="value"><?php echo ((is_array($_tmp=$this->_tpl_vars['from'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
</tr>
<tr valign="top">
	<td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'subject','key' => "email.subject"), $this);?>
</td>
	<td width="80%" class="value"><input type="text" id="subject" name="subject" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['subject'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="60" maxlength="120" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'body','key' => "email.body"), $this);?>
</td>
	<td class="value"><textarea name="body" cols="60" rows="15" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['body'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea></td>
</tr>
</table>

<p><input name="send" type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "email.send"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="history.go(-1)" /></p>
</form>
</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
