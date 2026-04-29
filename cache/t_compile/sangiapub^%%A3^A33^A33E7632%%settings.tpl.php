<?php /* Smarty version 2.6.26, created on 2026-04-05 05:53:27
         compiled from rtadmin/settings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'rtadmin/settings.tpl', 17, false),array('function', 'translate', 'rtadmin/settings.tpl', 19, false),array('function', 'html_options', 'rtadmin/settings.tpl', 53, false),array('modifier', 'assign', 'rtadmin/settings.tpl', 56, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "rt.admin.settings"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-USER027.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'saveSettings'), $this);?>
">

<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.settings.description"), $this);?>
</p>

<div id="enableRT"><input type="checkbox" <?php if ($this->_tpl_vars['enabled']): ?>checked="checked" <?php endif; ?>name="enabled" value="1" id="enabled"/>&nbsp;&nbsp;<label for="enabled"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.settings.enableReadingTools"), $this);?>
</label></div><br/>

<div class="separator"></div>
<div id="rtAdminOptions">
<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.options"), $this);?>
</h3>
<table width="100%" class="data">
    <tr valign="top">
        <td class="label" width="3%"><input type="checkbox" name="abstract" id="abstract" <?php if ($this->_tpl_vars['abstract']): ?>checked="checked" <?php endif; ?>/></td>
        <td class="value" width="97%"><label for="abstract"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.settings.abstract"), $this);?>
</label></td>
    </tr>
    <tr valign="top">
        <td class="label"><input type="checkbox" name="captureCite" id="captureCite" <?php if ($this->_tpl_vars['captureCite']): ?>checked="checked" <?php endif; ?>/></td>
        <td class="value">
            <label for="captureCite"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.settings.captureCite"), $this);?>
</label><br />
        </td>
    </tr>
    <tr valign="top">
        <td class="label"><input type="checkbox" name="viewMetadata" id="viewMetadata" <?php if ($this->_tpl_vars['viewMetadata']): ?>checked="checked" <?php endif; ?>/></td>
        <td class="value"><label for="viewMetadata"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.settings.viewMetadata"), $this);?>
</label></td>
    </tr>
    <tr valign="top">
        <td class="label"><input type="checkbox" name="supplementaryFiles" id="supplementaryFiles" <?php if ($this->_tpl_vars['supplementaryFiles']): ?>checked="checked" <?php endif; ?>/></td>
        <td class="value"><label for="supplementaryFiles"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.settings.supplementaryFiles"), $this);?>
</label></td>
    </tr>
</table>
</div>
<div class="separator">&nbsp;</div>
<div id="rtAdminRelatedItems">
    <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.relatedItems"), $this);?>
</h3>
    
    <label for="version"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.settings.relatedItems"), $this);?>
</label>&nbsp;&nbsp;<select name="version" id="version" class="selectMenu">
    <option value=""><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.settings.disableRelatedItems"), $this);?>
</option>
    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['versionOptions'],'selected' => $this->_tpl_vars['version']), $this);?>

    </select>
    
    <div class="u-mt-24 u-mb-24"><?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'versions'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'relatedItemsLink') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'relatedItemsLink'));?>

    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.admin.settings.relatedItemsLink",'relatedItemsLink' => $this->_tpl_vars['relatedItemsLink']), $this);?>
</div>
</div>
<p><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.save"), $this);?>
" class="button defaultButton" /> <input type="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" class="button" onclick="document.location.href='<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'rtadmin','escape' => false), $this);?>
'" /></p>

</form>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>