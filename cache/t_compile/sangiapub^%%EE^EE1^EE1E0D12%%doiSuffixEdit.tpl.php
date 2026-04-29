<?php /* Smarty version 2.6.26, created on 2026-04-13 18:03:56
         compiled from file:/home/sangiaor/public_html/journals/plugins/pubIds/doi/templates/doiSuffixEdit.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/pubIds/doi/templates/doiSuffixEdit.tpl', 30, false),array('function', 'fieldLabel', 'file:/home/sangiaor/public_html/journals/plugins/pubIds/doi/templates/doiSuffixEdit.tpl', 37, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/pubIds/doi/templates/doiSuffixEdit.tpl', 38, false),array('modifier', 'cat', 'file:/home/sangiaor/public_html/journals/plugins/pubIds/doi/templates/doiSuffixEdit.tpl', 50, false),)), $this); ?>
<br />
<?php if ($this->_tpl_vars['pubObject']): ?>
<?php $this->assign('pubObjectType', $this->_tpl_vars['pubIdPlugin']->getPubObjectType($this->_tpl_vars['pubObject'])); ?>
<?php $this->assign('enableObjectDoi', $this->_tpl_vars['pubIdPlugin']->getSetting($this->_tpl_vars['currentJournal']->getId(),"enable".($this->_tpl_vars['pubObjectType'])."Doi")); ?>
<?php if ($this->_tpl_vars['enableObjectDoi']): ?>
	<script type="text/javascript">
		<?php echo '
		<!--
			function toggleDOIClear() {
				if ($(\'#excludeDoi\').is(\':checked\')) {
					$(\'#clear_doi\').attr(\'checked\', true);
					$(\'#clear_doi\').attr(\'disabled\', true);
				} else {
					$(\'#clear_doi\').attr(\'disabled\', false);
				}
			}
		// -->
		'; ?>

	</script>
	<div id="pub-id::doi">
		<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.pubIds.doi.editor.doi"), $this);?>
</h3>
		<?php $this->assign('storedPubId', $this->_tpl_vars['pubObject']->getStoredPubId($this->_tpl_vars['pubIdPlugin']->getPubIdType())); ?>
		<?php if (! $this->_tpl_vars['excludeDoi']): ?>
			<?php if ($this->_tpl_vars['pubIdPlugin']->getSetting($this->_tpl_vars['currentJournal']->getId(),'doiSuffix') == 'customId' || $this->_tpl_vars['storedPubId']): ?>
				<?php if (empty ( $this->_tpl_vars['storedPubId'] )): ?>
					<table width="100%" class="data">
						<tr valign="top">
							<td rowspan="2" width="10%" class="label"><?php echo $this->_plugins['function']['fieldLabel'][0][0]->smartyFieldLabel(array('name' => 'doiSuffix','key' => "plugins.pubIds.doi.manager.settings.doiSuffix"), $this);?>
</td>
							<td rowspan="2" width="10%" align="right"><?php echo ((is_array($_tmp=$this->_tpl_vars['pubIdPlugin']->getSetting($this->_tpl_vars['currentJournal']->getId(),'doiPrefix'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
/</td>
							<td width="80%" class="value"><input type="text" class="textField" name="doiSuffix" id="doiSuffix" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['doiSuffix'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" size="20" maxlength="255" />
						</tr>
						<tr valign="top">
							<td colspan="3"><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.pubIds.doi.manager.settings.doiSuffixDescription"), $this);?>
</span></td>
						</tr>
					</table>
				<?php else: ?>
					<p class="u-mb-16 bold">
					    <span class="action"><?php echo ((is_array($_tmp=$this->_tpl_vars['storedPubId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
					</p>
					<input type="checkbox" name="clear_doi" id="clear_doi" value="1" />
					<?php ob_start(); ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => ((is_array($_tmp="plugins.pubIds.doi.editor.doiObjectType")) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['pubObjectType']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['pubObjectType']))), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('translatedObjectType', ob_get_contents());ob_end_clean(); ?>
					<p class="u-mb-16 u-mt-16"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.pubIds.doi.editor.doiReassign.description",'pubObjectType' => $this->_tpl_vars['translatedObjectType']), $this);?>
</p>
				<?php endif; ?>
			<?php else: ?>
				<p class="u-mb-16 bold">
				    <span class="action"><?php echo ((is_array($_tmp=$this->_tpl_vars['pubIdPlugin']->getPubId($this->_tpl_vars['pubObject'],true))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				</p>
				<?php ob_start(); ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => ((is_array($_tmp="plugins.pubIds.doi.editor.doiObjectType")) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['pubObjectType']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['pubObjectType']))), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('translatedObjectType', ob_get_contents());ob_end_clean(); ?>
				<p class="u-mb-16 u-mt-16">
				    <span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.pubIds.doi.editor.doiNotYetGenerated",'pubObjectType' => $this->_tpl_vars['translatedObjectType']), $this);?>
</span>
				</p>
			<?php endif; ?>
		<?php endif; ?>

		<p class="u-mb-24">
		    <input type="checkbox" name="excludeDoi" id="excludeDoi" value="1"<?php if ($this->_tpl_vars['excludeDoi']): ?> checked="checked"<?php endif; ?> onClick="toggleDOIClear()" />
    		<?php ob_start(); ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => ((is_array($_tmp="plugins.pubIds.doi.editor.doiObjectType")) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['pubObjectType']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['pubObjectType']))), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('translatedObjectType', ob_get_contents());ob_end_clean(); ?>
    		<span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.pubIds.doi.editor.excludePubObject",'pubObjectType' => $this->_tpl_vars['translatedObjectType']), $this);?>
</span>
		</p>
	</div>
	<?php $this->assign('divSeparator', true); ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['pubObjectType'] == 'Issue'): ?>
	<?php $this->assign('enableArticleDoi', $this->_tpl_vars['pubIdPlugin']->getSetting($this->_tpl_vars['currentJournal']->getId(),'enableArticleDoi')); ?>
	<?php $this->assign('enableGalleyDoi', $this->_tpl_vars['pubIdPlugin']->getSetting($this->_tpl_vars['currentJournal']->getId(),'enableGalleyDoi')); ?>
	<?php $this->assign('enableSuppFileDoi', $this->_tpl_vars['pubIdPlugin']->getSetting($this->_tpl_vars['currentJournal']->getId(),'enableSuppFileDoi')); ?>
	<?php if ($this->_tpl_vars['enableArticleDoi'] || $this->_tpl_vars['enableGalleyDoi'] || $this->_tpl_vars['enableSuppFileDoi']): ?>
		<div id="pub-id::doi::excludeIssueObjects">
			<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.pubIds.doi.editor.doi.issueObjects"), $this);?>
</h3>
			<section class="u-mb-24">
			    <p><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.pubIds.doi.editor.excludeIssueObjectsDoi.description"), $this);?>
</span></p>
			    <input type="submit" name="excludeIssueObjects_<?php echo ((is_array($_tmp=$this->_tpl_vars['pubIdPlugin']->getPubIdType())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.pubIds.doi.editor.excludeIssueObjectsDoi"), $this);?>
" class="action" />
			</section>
			<section class="u-mb-24">
    			<p><span class="instruct"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.pubIds.doi.editor.clearIssueObjectsDoi.description"), $this);?>
</span></p>
    			<input type="submit" name="clearIssueObjects_<?php echo ((is_array($_tmp=$this->_tpl_vars['pubIdPlugin']->getPubIdType())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.pubIds.doi.editor.clearIssueObjectsDoi"), $this);?>
" class="action" />
			</section>
		</div>
		<?php $this->assign('divSeparator', true); ?>
	<?php endif; ?>
<?php endif; ?>
<?php if ($this->_tpl_vars['divSeparator']): ?>
	<div class="separator"> </div>
<?php endif; ?>
<?php endif; ?>