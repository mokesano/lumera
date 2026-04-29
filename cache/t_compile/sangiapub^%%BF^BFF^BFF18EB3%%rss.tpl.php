<?php /* Smarty version 2.6.26, created on 2026-04-15 22:31:59
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss.tpl', 11, false),array('modifier', 'strip', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss.tpl', 20, false),array('modifier', 'replace', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss.tpl', 31, false),array('modifier', 'date_format', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss.tpl', 54, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss.tpl', 20, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss.tpl', 37, false),)), $this); ?>
&lt;?xml version="1.0" encoding="<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"?&gt;
<rdf:RDF
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns="http://purl.org/rss/1.0/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:prism="http://prismstandard.org/namespaces/1.2/basic/">

	<channel rdf:about="<?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
				<title><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
: <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "announcement.announcements"), $this);?>
</title>
		<link><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</link>
		<?php if ($this->_tpl_vars['journal']->getLocalizedDescription()): ?>
			<?php $this->assign('description', $this->_tpl_vars['journal']->getLocalizedDescription()); ?>
		<?php elseif ($this->_tpl_vars['journal']->getLocalizedSetting('searchDescription')): ?>
			<?php $this->assign('description', $this->_tpl_vars['journal']->getLocalizedSetting('searchDescription')); ?>
		<?php endif; ?>
		<description><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['description'])) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</description>

				<?php if ($this->_tpl_vars['journal']->getPrimaryLocale()): ?>
		<dc:language><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getPrimaryLocale())) ? $this->_run_mod_handler('replace', true, $_tmp, '_', '-') : smarty_modifier_replace($_tmp, '_', '-')))) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</dc:language>
		<?php endif; ?>

		<items>
			<?php $_from = $this->_tpl_vars['announcements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['announcement']):
?>
			<rdf:Seq>
				<rdf:li rdf:resource="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement','op' => 'view','path' => $this->_tpl_vars['announcement']->getId()), $this);?>
"/>
			</rdf:Seq>
			<?php endforeach; endif; unset($_from); ?>
		</items>
	</channel>

<?php $_from = $this->_tpl_vars['announcements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['announcement']):
?>
	<item rdf:about="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement','op' => 'view','path' => $this->_tpl_vars['announcement']->getId()), $this);?>
">
				<title><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedTitleFull())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</title>
		<link><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement','op' => 'view','path' => $this->_tpl_vars['announcement']->getId()), $this);?>
</link>

				<?php if ($this->_tpl_vars['announcement']->getLocalizedDescription()): ?>
		<description><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedDescription())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</description>
		<?php endif; ?>
		<dc:creator><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</dc:creator>
		<dc:date><?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getDatePosted())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>
</dc:date>
	</item>
<?php endforeach; endif; unset($_from); ?>

</rdf:RDF>