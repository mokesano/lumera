<?php /* Smarty version 2.6.26, created on 2026-04-04 21:22:50
         compiled from form/interestsInput.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'form/interestsInput.tpl', 18, false),array('function', 'url', 'form/interestsInput.tpl', 26, false),)), $this); ?>

<?php if (! $this->_tpl_vars['FBV_id']): ?><?php $this->assign('FBV_id', 'interests'); ?><?php endif; ?>

<script type="text/javascript">
	$(document).ready(function(){
		$("#<?php echo ((is_array($_tmp=$this->_tpl_vars['FBV_id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
").find(".interestsTextOnly").html(null).hide();
		$("#<?php echo ((is_array($_tmp=$this->_tpl_vars['FBV_id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
").find(".interestDescription").show();
		$("#<?php echo ((is_array($_tmp=$this->_tpl_vars['FBV_id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
").find(".interests").tagit({
			itemName: "keywords",
			fieldName: "interests",
			allowSpaces: true,
			tagSource: function(search, showChoices) {
				$.ajax({
					url: "<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('router' => @ROUTE_PAGE,'page' => 'user','op' => 'getInterests','escape' => false), $this))) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript'));?>
",
					data: search,
					dataType: 'json',
					success: function(jsonData) {
						if (jsonData.status == true) {
							// Must explicitly escape
							// WARNING: jquery-UI > 1.8.3 supposedly auto-escapes these values.  Reinvestigate when we upgrade.
							var results = $.map(jsonData.content, function(item) {
								return escapeHTML(item);
							});
							showChoices(results);
						}
					}
				});
			}
		});
	});
</script>

<div id="<?php echo ((is_array($_tmp=$this->_tpl_vars['FBV_id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
	<!-- The container which will be processed by tag-it.js as the interests widget -->
	<ul class="interests">
		<?php if ($this->_tpl_vars['FBV_interestsKeywords']): ?><?php $_from = $this->_tpl_vars['FBV_interestsKeywords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['interest']):
?><li class="hidden"><?php echo ((is_array($_tmp=$this->_tpl_vars['interest'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li><?php endforeach; endif; unset($_from); ?><?php endif; ?>
	</ul>
	<?php if ($this->_tpl_vars['FBV_label_content']): ?><span><?php echo $this->_tpl_vars['FBV_label_content']; ?>
</span><?php endif; ?>
	<!-- If Javascript is disabled, this field will be visible -->
	<textarea name="interestsTextOnly" rows="5" cols="40" class="interestsTextOnly textArea"><?php if ($this->_tpl_vars['FBV_interestsTextOnly']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['FBV_interestsTextOnly'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></textarea>
</div>