<?php /* Smarty version 2.6.26, created on 2026-04-04 05:39:17
         compiled from file:/home/sangiaor/public_html/journals/plugins/blocks/languageToggle/block.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/blocks/languageToggle/block.tpl', 20, false),array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/blocks/languageToggle/block.tpl', 27, false),array('function', 'html_options', 'file:/home/sangiaor/public_html/journals/plugins/blocks/languageToggle/block.tpl', 30, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/blocks/languageToggle/block.tpl', 20, false),)), $this); ?>

<?php if ($this->_tpl_vars['enableLanguageToggle']): ?>
<div class="block" id="sidebarLanguageToggle">
	<script type="text/javascript">
		<!--
		function changeLanguage() {
			var e = document.getElementById('languageSelect');
			var new_locale = e.options[e.selectedIndex].value;

			var redirect_url = '<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'setLocale','path' => 'NEW_LOCALE','source' => $_SERVER['REQUEST_URI'],'escape' => false), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript'));?>
';
			redirect_url = redirect_url.replace("NEW_LOCALE", new_locale);

			window.location.href = redirect_url;
		}
		//-->
	</script>
	<span class="blockTitle"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.language"), $this);?>
</span>
	<form action="#">
		<label for="languageSelect"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.languageToggle.selectLabel"), $this);?>
</label>
		<select id="languageSelect" <?php if ($this->_tpl_vars['isPostRequest']): ?>disabled="disabled" <?php endif; ?>size="1" name="locale" class="selectMenu"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['languageToggleLocales'],'selected' => $this->_tpl_vars['currentLocale']), $this);?>
</select>
		<input type="submit" class="button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => 'form.submit'), $this);?>
" onclick="changeLanguage(); return false;" />
	</form>
</div>
<?php endif; ?>