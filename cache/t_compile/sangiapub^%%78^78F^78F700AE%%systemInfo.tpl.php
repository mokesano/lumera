<?php /* Smarty version 2.6.26, created on 2026-04-04 17:39:58
         compiled from admin/systemInfo.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'admin/systemInfo.tpl', 17, false),array('function', 'url', 'admin/systemInfo.tpl', 32, false),array('modifier', 'date_format', 'admin/systemInfo.tpl', 20, false),array('modifier', 'escape', 'admin/systemInfo.tpl', 25, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "admin.systemInformation"); ?><?php echo ''; ?><?php $this->assign('pageDisplayed', 'site'); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>

<div id="systemVersion">
    <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.systemVersion"), $this);?>
</h3>
    <div id="currentVersion">
        <h4 class="u-mb-16"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.currentVersion"), $this);?>
</h4>
        <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.currentVersion"), $this);?>
: <span class="bold"><?php echo $this->_tpl_vars['currentVersion']->getVersionString(); ?>
</span> (<?php echo ((is_array($_tmp=$this->_tpl_vars['currentVersion']->getDateInstalled())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatLong']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatLong'])); ?>
)</p>
    </div>
    <div id="latestVersion">
        <?php if ($this->_tpl_vars['latestVersionInfo']): ?>
            <h4 class="u-mb-16"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.version.latest"), $this);?>
</h4>
        	<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.version.latest"), $this);?>
: <span class="bold"><?php echo ((is_array($_tmp=$this->_tpl_vars['latestVersionInfo']['release'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span> (<?php echo ((is_array($_tmp=$this->_tpl_vars['latestVersionInfo']['date'])) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatLong']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatLong'])); ?>
)</p>
        	<?php if ($this->_tpl_vars['currentVersion']->compare($this->_tpl_vars['latestVersionInfo']['version']) < 0): ?>
        		<p><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.version.updateAvailable"), $this);?>
</strong>: <a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['latestVersionInfo']['package'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.version.downloadPackage"), $this);?>
</a> | <?php if ($this->_tpl_vars['latestVersionInfo']['patch']): ?><a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['latestVersionInfo']['patch'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.version.downloadPatch"), $this);?>
</a><?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.version.downloadPatch"), $this);?>
<?php endif; ?> | <a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['latestVersionInfo']['info'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.version.moreInfo"), $this);?>
</a></p>
        	<?php else: ?>
        		<p><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.version.upToDate"), $this);?>
</strong></p>
        	<?php endif; ?>
        <?php else: ?>
        <p><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('versionCheck' => 1), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.version.checkForUpdates"), $this);?>
</a></p>
        <?php endif; ?>
    </div>
    <div id="versionHistory">
        <h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.versionHistory"), $this);?>
</h4>
        <table class="listing" width="100%">
            <thead>
            	<tr>
            		<td colspan="6" class="headseparator">&nbsp;</td>
            	</tr>
            	<tr valign="center" class="heading">
            		<td width="30%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.version"), $this);?>
</td>
            		<td width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.versionMajor"), $this);?>
</td>
            		<td width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.versionMinor"), $this);?>
</td>
            		<td width="10%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.versionRevision"), $this);?>
</td>
            		<td width="20%"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.versionBuild"), $this);?>
</td>
            		<td width="20%" align="right"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.dateInstalled"), $this);?>
</td>
            	</tr>
            	<tr>
            		<td colspan="6" class="headseparator">&nbsp;</td>
            	</tr>
        	</thead>
        	<tbody>
        	<?php $_from = $this->_tpl_vars['versionHistory']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['versions'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['versions']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['version']):
        $this->_foreach['versions']['iteration']++;
?>
            	<tr valign="top">
            		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['version']->getVersionString())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
            		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['version']->getMajor())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
            		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['version']->getMinor())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
            		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['version']->getRevision())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
            		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['version']->getBuild())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
            		<td align="right"><?php echo ((is_array($_tmp=$this->_tpl_vars['version']->getDateInstalled())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>
</td>
            	</tr>
            	<?php if (($this->_foreach['versions']['iteration'] == $this->_foreach['versions']['total'])): ?>
                	<tr>
                		<td colspan="6" class="endseparator">&nbsp;</td>
                	</tr>
            	<?php endif; ?>
            <?php endforeach; endif; unset($_from); ?>
            </tbody>
        </table>
    </div>
</div>

<div id="systemConfiguration">
    <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.systemConfiguration"), $this);?>
</h3>
    <p class="text-info"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.systemConfigurationDescription"), $this);?>
</p>
    
    <?php $_from = $this->_tpl_vars['configData']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['sectionName'] => $this->_tpl_vars['sectionData']):
?>
    <h4><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h4>
        <?php if (! empty ( $this->_tpl_vars['sectionData'] )): ?>        <table class="data" width="100%">
            <?php $_from = $this->_tpl_vars['sectionData']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['settingName'] => $this->_tpl_vars['settingValue']):
?>
            <tr valign="top">
            	<td width="30%" class="label"><?php echo ((is_array($_tmp=$this->_tpl_vars['settingName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
            	<td width="70%"><?php if ($this->_tpl_vars['settingValue'] === true): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.on"), $this);?>
<?php elseif ($this->_tpl_vars['settingValue'] === false): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.off"), $this);?>
<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['settingValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></td>
            </tr>
            <?php endforeach; endif; unset($_from); ?>
        </table>
        <?php endif; ?>    <?php endforeach; endif; unset($_from); ?>
</div>
<div class="separator"></div>
<div id="serverInformation" class="block">
    <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.serverInformation"), $this);?>
</h3>
    <p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.serverInformationDescription"), $this);?>
</p>
    
    <table class="data" width="100%">
        <?php $_from = $this->_tpl_vars['serverInfo']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['settingName'] => $this->_tpl_vars['settingValue']):
?>
        <tr valign="top">
        	<td width="30%" class="label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => ((is_array($_tmp=$this->_tpl_vars['settingName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
</td>
        	<td width="70%" class="value"><?php echo ((is_array($_tmp=$this->_tpl_vars['settingValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</td>
        </tr>
        <?php endforeach; endif; unset($_from); ?>
    </table>
    
    <a class="button u-mt-32" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'phpinfo'), $this);?>
" target="_blank" type="button"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "admin.phpInfo"), $this);?>
</a>
</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
