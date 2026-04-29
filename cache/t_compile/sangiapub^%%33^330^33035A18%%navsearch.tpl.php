<?php /* Smarty version 2.6.26, created on 2026-04-06 00:24:05
         compiled from common/navsearch.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'call_hook', 'common/navsearch.tpl', 11, false),array('function', 'url', 'common/navsearch.tpl', 15, false),array('modifier', 'assign', 'common/navsearch.tpl', 15, false),array('modifier', 'parse_url', 'common/navsearch.tpl', 24, false),array('modifier', 'parse_str', 'common/navsearch.tpl', 24, false),array('modifier', 'strtok', 'common/navsearch.tpl', 26, false),array('modifier', 'escape', 'common/navsearch.tpl', 26, false),array('modifier', 'strip_tags', 'common/navsearch.tpl', 63, false),array('modifier', 'lower', 'common/navsearch.tpl', 63, false),)), $this); ?>

<div class="c-header__container">
    <h2 class="c-header__visually-hidden">Search</h2>
<?php ob_start(); ?><?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Search::SearchResults::FilterInput",'filterName' => $this->_tpl_vars['filterName'],'filterValue' => $this->_tpl_vars['filterValue']), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('filterInput', ob_get_contents());ob_end_clean(); ?>
<?php if ($_GET['journal'] == '' || $_GET['journal'] == 'all' || ( ! $_GET['journal'] && ! $this->_tpl_vars['currentJournal'] )): ?>
        <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => 'index','page' => 'search','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'searchFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'searchFormUrl'));?>

    <?php $this->assign('isAllJournals', true); ?>
<?php else: ?>
        <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'searchFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'searchFormUrl'));?>

    <?php $this->assign('isAllJournals', false); ?>
<?php endif; ?>

<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['searchFormUrl'])) ? $this->_run_mod_handler('parse_url', true, $_tmp, @PHP_URL_QUERY) : parse_url($_tmp, @PHP_URL_QUERY)))) ? $this->_run_mod_handler('parse_str', true, $_tmp, $this->_tpl_vars['formUrlParameters']) : parse_str($_tmp, $this->_tpl_vars['formUrlParameters'])); ?>


<form class="c-header__search-form" action="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['searchFormUrl'])) ? $this->_run_mod_handler('strtok', true, $_tmp, "?") : strtok($_tmp, "?")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" method="GET" role="search" autocomplete="off" data-track="submit" data-track-action="search" data-track-label="form" data-track-category="inline search" onsubmit="return validateSearch(event)">
    
        <input value="<?php echo ((is_array($_tmp=$this->_tpl_vars['csrfToken'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="csrfToken" type="hidden">
    
    <label class="c-header__heading" for="keywords">Search articles by subject, keyword or author</label>
    
    <div class="c-header__search-layout c-header__search-layout--max-width">
        <?php ob_start(); ?><?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Search::SearchResults::FilterInput",'filterName' => 'query','filterValue' => $this->_tpl_vars['query']), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('queryFilter', ob_get_contents());ob_end_clean(); ?>
        <?php if (empty ( $this->_tpl_vars['queryFilter'] )): ?>
            <div>
                                <?php $_from = $this->_tpl_vars['formUrlParameters']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['paramKey'] => $this->_tpl_vars['paramValue']):
?>
                    <?php if ($this->_tpl_vars['paramKey'] != 'journal' && $this->_tpl_vars['paramKey'] != 'query'): ?>
                        <input type="hidden" name="<?php echo ((is_array($_tmp=$this->_tpl_vars['paramKey'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['paramValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"/>
                    <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?>
                
                <input class="c-search__input" type="text" id="query" name="query" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['query'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" placeholder="Search" required>
            </div>
        <?php else: ?>
            <?php echo $this->_tpl_vars['queryFilter']; ?>

        <?php endif; ?>
        
        <div class="c-header__search-layout">
            <div>
                <label for="journal" class="u-visually-hidden">Journal</label>
                <select class="c-search__select" 
                        data-track="change" 
                        data-track-action="search" 
                        data-track-label="journal" 
                        data-track-category="inline search" 
                        name="journal" 
                        id="journal"
                        onchange="handleJournalChange(this)">
                    <option value="all" <?php if ($this->_tpl_vars['isAllJournals']): ?>selected="selected"<?php endif; ?>>All journals</option>
                    <?php if ($this->_tpl_vars['currentJournal']): ?>
                        <option value="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getPath())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if (! $this->_tpl_vars['isAllJournals']): ?>selected="selected"<?php endif; ?>>This journal</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        
        <div>
            <button type="submit" value="Search" class="c-search__button" data-action="clear-adv">
                <span class="c-search__button-text">Search</span>
                <svg class="u-flex-static" role="img" aria-hidden="true" focusable="false" height="16" width="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path>
                </svg>
            </button>
        </div>
    </div>
</form>

<script type="text/javascript">
<?php echo '
<!--
// Simple and GUARANTEED working validation
function validateSearch(event) {
    var input = document.getElementById(\'query\');
    var value = input.value.trim();
    
    if (value === \'\') {
        alert(\'Please fill out this field.\');
        input.focus();
        return false;
    }
    return true;
}

function handleJournalChange(selectElement) {
    const form = selectElement.closest(\'form\');
    const journalValue = selectElement.value;
    const baseUrl = window.location.origin;
    
    if (journalValue === \'all\') {
        form.action = baseUrl + \'/index/search\';
    } else {
        form.action = baseUrl + \'/\' + journalPath + \'/search\';
    }
}

// Initialize on page load
document.addEventListener(\'DOMContentLoaded\', function() {
    const journalSelect = document.getElementById(\'journal\');
    
    if (journalSelect) {
        handleJournalChange(journalSelect);
    }
});

// HANYA attach ke form search tertentu - BUKAN semua form
document.addEventListener(\'DOMContentLoaded\', function() {
    // Target HANYA form search dengan selector spesifik
    var searchForms = document.querySelectorAll(
        \'form[role="search"], \' +
        \'form.c-header__search-form, \' +
        \'form[data-track-category*="inline search"], \' +
        \'.s-search form\'
    );
    
    for (var i = 0; i < searchForms.length; i++) {
        var form = searchForms[i];
        // Double check: pastikan ada input query sebelum attach
        if (form.querySelector(\'input[name="query"]\')) {
            form.onsubmit = function() { return checkEmpty(this); };
        }
    }
});
// -->
'; ?>

</script>

    <div class="c-header__flush"><a class="c-header__link" href="<?php echo $this->_tpl_vars['url']; ?>
" data-track="click" data-track-action="advanced search" data-track-label="link">Advanced search</a>
    </div>
    <h3 class="c-header__heading c-header__heading--keyline">Quick links</h3>
    <ul class="c-header__list">
        <li><a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'browseSearch','op' => 'sections'), $this);?>
" data-track="click" data-track-action="explore articles by section" data-track-label="link">Explore articles by section</a></li>
        <li><a class="c-header__link" href="//www.sangia.org" data-track="click" data-track-action="find a news daily" data-track-label="link">Sangia Daily</a></li>
        <li><a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions'), $this);?>
" data-track="click" data-track-action="guide for authors" data-track-label="link">Guide for authors</a></li>
        <li><a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies'), $this);?>
" data-track="click" data-track-action="editorial policies" data-track-label="link">Editorial policies</a></li>
    </ul>
</div>