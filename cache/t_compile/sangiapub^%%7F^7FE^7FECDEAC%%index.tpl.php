<?php /* Smarty version 2.6.26, created on 2026-04-05 10:05:16
         compiled from file:/home/sangiaor/public_html/journals/plugins/generic/googleViewer/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/generic/googleViewer/index.tpl', 12, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/generic/googleViewer/index.tpl', 12, false),array('modifier', 'to_array', 'file:/home/sangiaor/public_html/journals/plugins/generic/googleViewer/index.tpl', 12, false),array('modifier', 'assign', 'file:/home/sangiaor/public_html/journals/plugins/generic/googleViewer/index.tpl', 15, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/generic/googleViewer/index.tpl', 16, false),)), $this); ?>

<div id="pdfDownloadLinkContainer">
	<a class="action pdf" id="pdfDownloadLink" target="_parent" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'download','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.pdf.download"), $this);?>
</a>
</div>

<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'viewFile','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal']))),'escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pdfUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pdfUrl'));?>

<iframe src="//docs.google.com/viewer?url=<?php echo ((is_array($_tmp=$this->_tpl_vars['pdfUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
&embedded=true" style="width:100%; height:800px;" frameborder="0"></iframe>