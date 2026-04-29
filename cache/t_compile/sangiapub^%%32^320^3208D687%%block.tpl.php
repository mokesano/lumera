<?php /* Smarty version 2.6.26, created on 2026-04-04 05:39:17
         compiled from file:/home/sangiaor/public_html/journals/plugins/blocks/navigation/block.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'file:/home/sangiaor/public_html/journals/plugins/blocks/navigation/block.tpl', 13, false),array('function', 'url', 'file:/home/sangiaor/public_html/journals/plugins/blocks/navigation/block.tpl', 15, false),array('function', 'call_hook', 'file:/home/sangiaor/public_html/journals/plugins/blocks/navigation/block.tpl', 24, false),array('function', 'html_options_translate', 'file:/home/sangiaor/public_html/journals/plugins/blocks/navigation/block.tpl', 38, false),array('modifier', 'assign', 'file:/home/sangiaor/public_html/journals/plugins/blocks/navigation/block.tpl', 15, false),array('modifier', 'parse_url', 'file:/home/sangiaor/public_html/journals/plugins/blocks/navigation/block.tpl', 16, false),array('modifier', 'parse_str', 'file:/home/sangiaor/public_html/journals/plugins/blocks/navigation/block.tpl', 16, false),array('modifier', 'strtok', 'file:/home/sangiaor/public_html/journals/plugins/blocks/navigation/block.tpl', 17, false),array('modifier', 'escape', 'file:/home/sangiaor/public_html/journals/plugins/blocks/navigation/block.tpl', 17, false),)), $this); ?>
<?php if (! $this->_tpl_vars['currentJournal'] || $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>
<div class="block" id="sidebarNavigation">
	<span class="blockTitle"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.navigation.journalContent"), $this);?>
</span>

	<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'search','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'searchFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'searchFormUrl'));?>

	<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['searchFormUrl'])) ? $this->_run_mod_handler('parse_url', true, $_tmp, @PHP_URL_QUERY) : parse_url($_tmp, @PHP_URL_QUERY)))) ? $this->_run_mod_handler('parse_str', true, $_tmp, $this->_tpl_vars['formUrlParameters']) : parse_str($_tmp, $this->_tpl_vars['formUrlParameters'])); ?>

	<form id="simpleSearchForm" action="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['searchFormUrl'])) ? $this->_run_mod_handler('strtok', true, $_tmp, "?") : strtok($_tmp, "?")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
		<?php $_from = $this->_tpl_vars['formUrlParameters']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['paramKey'] => $this->_tpl_vars['paramValue']):
?>
			<input type="hidden" name="<?php echo ((is_array($_tmp=$this->_tpl_vars['paramKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['paramValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"/>
		<?php endforeach; endif; unset($_from); ?>
		<table id="simpleSearchInput">
			<tr>
				<td>
				<?php ob_start(); ?><?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Search::SearchResults::FilterInput",'filterName' => 'simpleQuery','filterValue' => "",'size' => 15), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('filterInput', ob_get_contents());ob_end_clean(); ?>
				<?php if (empty ( $this->_tpl_vars['filterInput'] )): ?>
					<label for="simpleQuery"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.search"), $this);?>
 <br />
					<input type="text" id="simpleQuery" name="simpleQuery" size="15" maxlength="255" value="" class="textField" /></label>
				<?php else: ?>
					<?php echo $this->_tpl_vars['filterInput']; ?>

				<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td><label for="searchField">
				<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "plugins.block.navigation.searchScope"), $this);?>

				<br />
				<select id="searchField" name="searchField" size="1" class="selectMenu">
					<?php echo $this->_plugins['function']['html_options_translate'][0][0]->smartyHtmlOptionsTranslate(array('options' => $this->_tpl_vars['articleSearchByOptions']), $this);?>

				</select></label>
				</td>
			</tr>
			<tr>
				<td><input type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.search"), $this);?>
" class="button" /></td>
			</tr>
		</table>
	</form>

	<br />

	<?php if ($this->_tpl_vars['currentJournal']): ?>
	<span class="blockSubtitle"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.browse"), $this);?>
</span>
	<ul>
		<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'archive'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.browseByIssue"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'authors'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.browseByAuthor"), $this);?>
</a></li>
		<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'titles'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.browseByTitle"), $this);?>
</a></li>
		<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Plugins::Blocks::Navigation::BrowseBy"), $this);?>

		<?php if ($this->_tpl_vars['hasOtherJournals']): ?>
			<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => 'index'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.otherJournals"), $this);?>
</a></li>
			<?php if ($this->_tpl_vars['siteCategoriesEnabled']): ?><li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => 'index','page' => 'search','op' => 'categories'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.categories"), $this);?>
</a></li><?php endif; ?>
		<?php endif; ?>
	</ul>
	<?php endif; ?>
</div>
<?php endif; ?>