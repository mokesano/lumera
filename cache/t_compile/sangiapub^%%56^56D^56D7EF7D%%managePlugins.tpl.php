<?php /* Smarty version 2.6.26, created on 2026-04-05 06:00:38
         compiled from manager/plugins/managePlugins.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'manager/plugins/managePlugins.tpl', 19, false),array('function', 'url', 'manager/plugins/managePlugins.tpl', 22, false),array('modifier', 'to_array', 'manager/plugins/managePlugins.tpl', 68, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "manager.plugins.".($this->_tpl_vars['path'])); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-USER027.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>



<?php if ($this->_tpl_vars['path'] == 'install'): ?>
	<div id="install">
		<?php if (! $this->_tpl_vars['uploaded']): ?>
			<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.plugins.installDescription"), $this);?>
</p>
		<?php endif; ?>

		<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'managePlugins','path' => 'installPlugin'), $this);?>
" enctype="multipart/form-data">
			<?php if ($this->_tpl_vars['error']): ?>
				<span class="formError"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.errorsOccurred"), $this);?>
:</span>
				<ul class="formErrorList">
					<li>
					<?php if (is_array ( $this->_tpl_vars['message'] )): ?>
						<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['message'][0]), $this);?>
 <?php echo $this->_tpl_vars['message'][1]; ?>

					<?php else: ?>
						<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['message']), $this);?>

					<?php endif; ?>
					</li>
				</ul>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['uploaded']): ?>
				<ul>
					<li>
					<?php if (is_array ( $this->_tpl_vars['message'] )): ?>
						<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['message'][0]), $this);?>
 <?php echo $this->_tpl_vars['message'][1]; ?>

					<?php else: ?>
						<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['message']), $this);?>

					<?php endif; ?>
					</li>
				</ul>
			<?php endif; ?>

			<br />
			<table class="data" width="100%">
			<tr>
				<td width="25%" class="label">
					<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.plugins.uploadPluginDir"), $this);?>

				</td>
				<td width="75%" class="value">
					<input type="file" class="uploadField" name="newPlugin" id="newPlugin" /> 
					<input name="uploadPlugin" type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.continue"), $this);?>
" class="button defaultButton" />
				</td>
			</tr>
			</table>
			<p>
		</form>
	</div>
<?php elseif ($this->_tpl_vars['path'] == 'upgrade'): ?>
	<div id="upgrade">
		<?php if (! $this->_tpl_vars['uploaded']): ?>
			<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.plugins.upgradeDescription"), $this);?>
</p>
		<?php endif; ?>

		<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => ((is_array($_tmp='upgradePlugin')) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['category'], $this->_tpl_vars['plugin']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['category'], $this->_tpl_vars['plugin']))), $this);?>
" enctype="multipart/form-data">
			<?php if ($this->_tpl_vars['error']): ?>
				<span class="formError"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.errorsOccurred"), $this);?>
:</span>
				<ul class="formErrorList">
					<?php if (is_array ( $this->_tpl_vars['message'] )): ?>
						<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['message'][0]), $this);?>
 <?php echo $this->_tpl_vars['message'][1]; ?>

					<?php else: ?>
						<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['message']), $this);?>

					<?php endif; ?>
				</ul>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['uploaded']): ?>
				<ul>
					<li>
					<?php if (is_array ( $this->_tpl_vars['message'] )): ?>
						<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['message'][0]), $this);?>
 <?php echo $this->_tpl_vars['message'][1]; ?>

					<?php else: ?>
						<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['message']), $this);?>

					<?php endif; ?>
					</li>
				</ul>
			<?php endif; ?>

			<br />
			<table class="data" width="100%">
			<tr>
				<td width="25%" class="label">
						<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.plugins.uploadPluginDir"), $this);?>

				</td>
				<td width="75%" class="value">
					<input type="file" class="uploadField" name="newPlugin" id="newPlugin" />
					<input name="uploadPlugin" type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.continue"), $this);?>
" class="button defaultButton" />
				</td>
			</tr>
			</table>
			<p>
		</form>
	</div>
<?php elseif ($this->_tpl_vars['path'] == 'delete'): ?>
	<div id="delete">
		<?php if (! $this->_tpl_vars['deleted']): ?>
			<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.plugins.deleteDescription"), $this);?>
</p>
		<?php endif; ?>

		<?php if (! $this->_tpl_vars['deleted']): ?>
			<?php if (! $this->_tpl_vars['error']): ?>
				<ul class="formErrorList">
					<li><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.plugins.deleteConfirm"), $this);?>
</li>
				</ul>
			<?php endif; ?>

			<br />
			<form method="post" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('path' => ((is_array($_tmp='deletePlugin')) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['category'], $this->_tpl_vars['plugin']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['category'], $this->_tpl_vars['plugin']))), $this);?>
" enctype="multipart/form-data">
				<?php if ($this->_tpl_vars['error']): ?>
					<span class="formError"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "form.errorsOccurred"), $this);?>
:</span>
					<ul class="formErrorList">
						<li>
						<?php if (is_array ( $this->_tpl_vars['message'] )): ?>
							<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['message'][0]), $this);?>
 <?php echo $this->_tpl_vars['message'][1]; ?>

						<?php else: ?>
							<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['message']), $this);?>

						<?php endif; ?>
						</li>
					</ul>
				<?php endif; ?>
				<input type="submit" name="save" class="button defaultButton" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.delete"), $this);?>
"/> <input type="button" class="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.cancel"), $this);?>
" onclick="history.go(-1)"/>
			</form>
		<?php else: ?>
			<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.plugins.deleteSuccess"), $this);?>
</p>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
