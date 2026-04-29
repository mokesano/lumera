<?php /* Smarty version 2.6.26, created on 2026-04-04 16:04:16
         compiled from file:/home/sangiaor/public_html/journals/plugins/citationFormats/endNote//citation.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/citationFormats/endNote//citation.tpl', 12, false),array('function', 'math', 'file:/home/sangiaor/public_html/journals/plugins/citationFormats/endNote//citation.tpl', 39, false),array('modifier', 'to_array', 'file:/home/sangiaor/public_html/journals/plugins/citationFormats/endNote//citation.tpl', 12, false),array('modifier', 'assign', 'file:/home/sangiaor/public_html/journals/plugins/citationFormats/endNote//citation.tpl', 12, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/citationFormats/endNote//citation.tpl', 17, false),array('modifier', 'date_format', 'file:/home/sangiaor/public_html/journals/plugins/citationFormats/endNote//citation.tpl', 20, false),array('modifier', 'strip_tags', 'file:/home/sangiaor/public_html/journals/plugins/citationFormats/endNote//citation.tpl', 26, false),array('modifier', 'replace', 'file:/home/sangiaor/public_html/journals/plugins/citationFormats/endNote//citation.tpl', 31, false),)), $this); ?>
<?php if ($this->_tpl_vars['galleyId']): ?>
	<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galleyId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galleyId']))), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'articleUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'articleUrl'));?>

<?php else: ?>
	<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['articleId']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'articleUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'articleUrl'));?>

<?php endif; ?>
<?php $_from = $this->_tpl_vars['article']->getAuthors(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['author']):
?>
%A <?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getFullName(true))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

<?php endforeach; endif; unset($_from); ?>
<?php if ($this->_tpl_vars['article']->getDatePublished()): ?>
%D <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>

<?php elseif ($this->_tpl_vars['issue']->getDatePublished()): ?>
%D <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>

<?php else: ?>
%D <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getYear())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

<?php endif; ?>
%T <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>

%B <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>

%9 <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedSubject())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

%! <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>

%K <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedSubject())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

%X <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, "\n", ' ') : smarty_modifier_replace($_tmp, "\n", ' ')))) ? $this->_run_mod_handler('replace', true, $_tmp, "\r", ' ') : smarty_modifier_replace($_tmp, "\r", ' ')); ?>

%U <?php echo $this->_tpl_vars['articleUrl']; ?>

%0 Journal Article
<?php if ($this->_tpl_vars['article']->getPubId('doi')): ?>%R <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

<?php endif; ?>
<?php if ($this->_tpl_vars['article']->getPages()): ?>
<?php if ($this->_tpl_vars['article']->getStartingPage()): ?>%& <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getStartingPage())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
<?php if ($this->_tpl_vars['article']->getEndingPage()): ?>
<?php echo smarty_function_math(array('equation' => "end - start + 1",'end' => $this->_tpl_vars['article']->getEndingPage(),'start' => $this->_tpl_vars['article']->getStartingPage(),'assign' => 'pages'), $this);?>

%P <?php echo $this->_tpl_vars['pages']; ?>

<?php else: ?>
%P 1
<?php endif; ?>
<?php endif; ?>
%J <?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

<?php if ($this->_tpl_vars['issue']->getShowVolume()): ?>%V <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

<?php endif; ?>
<?php if ($this->_tpl_vars['issue']->getShowNumber()): ?>%N <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

<?php endif; ?>
<?php if ($this->_tpl_vars['currentJournal']->getSetting('onlineIssn')): ?>%@ <?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('onlineIssn'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('printIssn')): ?>%@ <?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('printIssn'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

<?php endif; ?>
<?php if ($this->_tpl_vars['article']->getDatePublished()): ?>
%8 <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>

<?php endif; ?>
<?php if ($this->_tpl_vars['issue']->getDatePublished()): ?>
%7 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y-%m-%d") : smarty_modifier_date_format($_tmp, "%Y-%m-%d")); ?>

<?php endif; ?>