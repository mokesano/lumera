<?php /* Smarty version 2.6.26, created on 2026-04-06 00:23:32
         compiled from common/search.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'call_hook', 'common/search.tpl', 17, false),array('function', 'url', 'common/search.tpl', 18, false),array('function', 'translate', 'common/search.tpl', 48, false),array('modifier', 'assign', 'common/search.tpl', 18, false),array('modifier', 'parse_url', 'common/search.tpl', 19, false),array('modifier', 'parse_str', 'common/search.tpl', 19, false),array('modifier', 'strtok', 'common/search.tpl', 20, false),array('modifier', 'escape', 'common/search.tpl', 20, false),array('modifier', 'strip_tags', 'common/search.tpl', 25, false),array('modifier', 'lower', 'common/search.tpl', 25, false),)), $this); ?>

<div class="u-container u-search u-mt-0 u-mb-32">
<script type="text/javascript">
	$(function() {
		// Attach the form handler.
		$('#searchForm').pkpHandler('$.pkp.pages.search.SearchFormHandler');
	});
</script>
    <div class="s-search c-search--background">
        <?php ob_start(); ?><?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Search::SearchResults::FilterInput",'filterName' => $this->_tpl_vars['filterName'],'filterValue' => $this->_tpl_vars['filterValue']), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('filterInput', ob_get_contents());ob_end_clean(); ?>
        <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'search','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'searchFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'searchFormUrl'));?>

        <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['searchFormUrl'])) ? $this->_run_mod_handler('parse_url', true, $_tmp, @PHP_URL_QUERY) : parse_url($_tmp, @PHP_URL_QUERY)))) ? $this->_run_mod_handler('parse_str', true, $_tmp, $this->_tpl_vars['formUrlParameters']) : parse_str($_tmp, $this->_tpl_vars['formUrlParameters'])); ?>

        <form action="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['searchFormUrl'])) ? $this->_run_mod_handler('strtok', true, $_tmp, "?") : strtok($_tmp, "?")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" method="GET" role="search" autocomplete="off" data-track="submit" data-track-action="search" data-track-label="form" data-track-category="inline search">
            
                        <input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
            
            <input type="hidden" value="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedInitials())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="journal">
            <label class="c-search__input-label" for="keywords">Search <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</label>
            <div class="c-search__field">
            	<?php ob_start(); ?><?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Search::SearchResults::FilterInput",'filterName' => 'query','filterValue' => $this->_tpl_vars['query']), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('queryFilter', ob_get_contents());ob_end_clean(); ?>
            	<?php if (empty ( $this->_tpl_vars['queryFilter'] )): ?>
                <div class="c-search__input-container c-search__input-container--sm">
        			<?php $_from = $this->_tpl_vars['formUrlParameters']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['paramKey'] => $this->_tpl_vars['paramValue']):
?>
            		<input type="hidden" name="<?php echo ((is_array($_tmp=$this->_tpl_vars['paramKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['paramValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"/>
        			<?php endforeach; endif; unset($_from); ?>
        			<input id="search-keywords" type="text" data-test="search-box" name="query" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['query'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" placeholder="Search" class="c-search__input" />
    			</div>
        		<?php elseif ($this->_tpl_vars['hasActiveFilters']): ?>
        		    <?php echo ((is_array($_tmp=$this->_tpl_vars['filterValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
	
        		<?php else: ?>
        			<?php echo $this->_tpl_vars['queryFilter']; ?>

        		<?php endif; ?>           
                <div class="c-search__select-container">
                    <label for="subject" class="u-visually-hidden">Subject</label>
                    <select class="c-search__select" data-track="change" data-track-action="search" data-track-label="subject" data-track-category="inline search 150" name="subject" id="subject">All Subjects
                        <option value="">All Subjects</option>
                    </select>
                </div>
                <div class="c-search__button-container">
                    <button type="submit" class="c-search__button" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.search"), $this);?>
">
                        <span class="c-search__button-text">Search</span>
                        <svg class="u-flex-static" role="img" aria-hidden="true" focusable="false" height="16" width="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
                    </button>
                </div>
            </div>
        </form> 
    </div>
</div>	