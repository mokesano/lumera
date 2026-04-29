<?php /* Smarty version 2.6.26, created on 2026-04-05 23:49:20
         compiled from admin/aboutSite.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'admin/aboutSite.tpl', 12, false),array('function', 'url', 'admin/aboutSite.tpl', 14, false),array('function', 'fieldLabel', 'admin/aboutSite.tpl', 26, false),array('modifier', 'escape', 'admin/aboutSite.tpl', 18, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "admin.aboutSiteSettings"); ?><?php echo ''; ?><?php $this->assign('pageDisplayed', 'aboutSite'); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.aboutSiteSettings.description"), $this);?>
</p>

<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'saveAboutSite'), $this);?>
">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/formErrors.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
    
<div id="settings">
<?php $_from = $this->_tpl_vars['formLocales']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['localeKey'] => $this->_tpl_vars['localeName']):
?>
<fieldset class="locale <?php if ($this->_tpl_vars['formLocale'] != $this->_tpl_vars['localeKey']): ?>hidden<?php endif; ?>">
    <legend class="hidden"><?php echo $this->_tpl_vars['localeName']; ?>
</legend>
    <table class="data" width="100%">
        <tr valign="top">
            <td width="20%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publisherMission','key' => $this->_tpl_vars['publisherMissionKey']), $this);?>
</td>
            <td width="80%" class="value">
                                <textarea name="publisherMission[<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="publisherMission" cols="60" rows="10" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['publisherMission'][$this->_tpl_vars['localeKey']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
            </td>
        </tr>
        <tr valign="top">
            <td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publisherHistory','key' => $this->_tpl_vars['publisherHistoryKey']), $this);?>
</td>
            <td class="value">
                                <textarea name="publisherHistory[<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="publisherHistory" cols="60" rows="10" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['publisherHistory'][$this->_tpl_vars['localeKey']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
            </td>
        </tr>
        <tr valign="top">
            <td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publisherLeaderships','key' => $this->_tpl_vars['publisherLeadershipsKey']), $this);?>
</td>
            <td class="value">
                                <textarea name="publisherLeaderships[<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="publisherLeaderships" cols="60" rows="10" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['publisherLeaderships'][$this->_tpl_vars['localeKey']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
            </td>
        </tr>
        <tr valign="top">
            <td class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'publisherAwards','key' => $this->_tpl_vars['publisherAwardsKey']), $this);?>
</td>
            <td class="value">
                                <textarea name="publisherAwards[<?php echo ((is_array($_tmp=$this->_tpl_vars['localeKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
]" id="publisherAwards" cols="60" rows="10" class="textArea"><?php echo ((is_array($_tmp=$this->_tpl_vars['publisherAwards'][$this->_tpl_vars['localeKey']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</textarea>
            </td>
        </tr>
    </table>
</fieldset>
<?php endforeach; endif; unset($_from); ?>
</div>

<p>
    <input type="submit" name="save" class="button defaultButton" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" />
    <input type="button" class="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'index','escape' => false), $this);?>
'" />
</p>
</form>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>