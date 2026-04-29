<?php /* Smarty version 2.6.26, created on 2026-04-09 09:09:12
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/atom.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/atom.tpl', 11, false),array('modifier', 'strip', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/atom.tpl', 15, false),array('modifier', 'date_format', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/atom.tpl', 16, false),array('modifier', 'regex_replace', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/atom.tpl', 16, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/atom.tpl', 15, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/atom.tpl', 39, false),)), $this); ?>
&lt;?xml version="1.0" encoding="<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"?&gt;
<feed xmlns="http://www.w3.org/2005/Atom">
		<id><?php echo ((is_array($_tmp=$this->_tpl_vars['selfUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</id>
	<title><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
: <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "announcement.announcements"), $this);?>
</title>
	<updated><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['dateUpdated'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%dT%T%z") : smarty_modifier_date_format($_tmp, "%Y-%m-%dT%T%z")))) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/00$/", ":00") : smarty_modifier_regex_replace($_tmp, "/00$/", ":00")); ?>
</updated>

			<link rel="alternate" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<link rel="self" type="application/atom+xml" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['selfUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />

				<generator uri="http://pkp.sfu.ca/ojs/" version="<?php echo ((is_array($_tmp=$this->_tpl_vars['ojsVersion'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">Open Journal Systems</generator>
	<?php if ($this->_tpl_vars['journal']->getLocalizedDescription()): ?>
		<?php $this->assign('description', $this->_tpl_vars['journal']->getLocalizedDescription()); ?>
	<?php elseif ($this->_tpl_vars['journal']->getLocalizedSetting('searchDescription')): ?>
		<?php $this->assign('description', $this->_tpl_vars['journal']->getLocalizedSetting('searchDescription')); ?>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['description']): ?>
		<subtitle type="html"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['description'])) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</subtitle>
	<?php endif; ?>

<?php $_from = $this->_tpl_vars['announcements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['announcement']):
?>
	<entry>
				<id><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement','op' => 'view','path' => $this->_tpl_vars['announcement']->getId()), $this);?>
</id>
		<title><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedTitleFull())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</title>
		<updated><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['announcement']->getDatetimePosted())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%dT%T%z") : smarty_modifier_date_format($_tmp, "%Y-%m-%dT%T%z")))) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/00$/", ":00") : smarty_modifier_regex_replace($_tmp, "/00$/", ":00")); ?>
</updated>
	 	<author>
			<name><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</name>
        </author>
		<link rel="alternate" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement','op' => 'view','path' => $this->_tpl_vars['announcement']->getId()), $this);?>
" />
        <?php if ($this->_tpl_vars['announcement']->getLocalizedDescription()): ?>
		<summary type="html" xml:base="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement','op' => 'view','path' => $this->_tpl_vars['announcement']->getId()), $this);?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedDescription())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</summary>
        <?php endif; ?>

								<published><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['announcement']->getDatetimePosted())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%dT%T%z") : smarty_modifier_date_format($_tmp, "%Y-%m-%dT%T%z")))) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/00$/", ":00") : smarty_modifier_regex_replace($_tmp, "/00$/", ":00")); ?>
</published>
					</entry>
<?php endforeach; endif; unset($_from); ?>
</feed>