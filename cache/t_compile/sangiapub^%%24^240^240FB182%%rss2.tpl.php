<?php /* Smarty version 2.6.26, created on 2026-04-09 09:30:23
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss2.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss2.tpl', 11, false),array('modifier', 'strip', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss2.tpl', 15, false),array('modifier', 'replace', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss2.tpl', 26, false),array('modifier', 'date_format', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss2.tpl', 28, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss2.tpl', 15, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/generic/announcementFeed/templates/rss2.tpl', 37, false),)), $this); ?>
&lt;?xml version="1.0" encoding="<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"?&gt;
<rss version="2.0">
	<channel>
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
	    <language><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['journal']->getPrimaryLocale())) ? $this->_run_mod_handler('replace', true, $_tmp, '_', '-') : smarty_modifier_replace($_tmp, '_', '-')))) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</language>
	    <?php endif; ?>
		<pubDate><?php echo ((is_array($_tmp=$this->_tpl_vars['dateUpdated'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%a, %d %b %Y %T %z") : smarty_modifier_date_format($_tmp, "%a, %d %b %Y %T %z")); ?>
</pubDate>
		<generator>OJS <?php echo ((is_array($_tmp=$this->_tpl_vars['ojsVersion'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</generator>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
		<ttl>60</ttl>

		<?php $_from = $this->_tpl_vars['announcements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['announcement']):
?>
			<item>
								<title><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedTitleFull())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</title>
				<link><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement','op' => 'view','path' => $this->_tpl_vars['announcement']->getId()), $this);?>
</link>
				<description><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedDescription())) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'html')); ?>
</description>

								<guid isPermaLink="true"><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement','op' => 'view','path' => $this->_tpl_vars['announcement']->getId()), $this);?>
</guid>
				<pubDate><?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getDatetimePosted())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%a, %d %b %Y %T %z") : smarty_modifier_date_format($_tmp, "%a, %d %b %Y %T %z")); ?>
</pubDate>
			</item>
		<?php endforeach; endif; unset($_from); ?>
	</channel>
</rss>