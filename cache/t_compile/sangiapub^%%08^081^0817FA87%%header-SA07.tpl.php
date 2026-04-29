<?php /* Smarty version 2.6.26, created on 2026-04-04 05:56:01
         compiled from common/header-SA07.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'substr', 'common/header-SA07.tpl', 2, false),array('modifier', 'assign', 'common/header-SA07.tpl', 13, false),array('modifier', 'strip_unsafe_html', 'common/header-SA07.tpl', 21, false),array('modifier', 'escape', 'common/header-SA07.tpl', 21, false),array('modifier', 'strip_tags', 'common/header-SA07.tpl', 21, false),array('modifier', 'parse_url', 'common/header-SA07.tpl', 147, false),array('modifier', 'parse_str', 'common/header-SA07.tpl', 147, false),array('modifier', 'strtok', 'common/header-SA07.tpl', 148, false),array('modifier', 'lower', 'common/header-SA07.tpl', 149, false),array('modifier', 'default', 'common/header-SA07.tpl', 188, false),array('modifier', 'replace', 'common/header-SA07.tpl', 277, false),array('function', 'translate', 'common/header-SA07.tpl', 13, false),array('function', 'call_hook', 'common/header-SA07.tpl', 39, false),array('function', 'url', 'common/header-SA07.tpl', 80, false),array('function', 'html_options', 'common/header-SA07.tpl', 250, false),array('function', 'page_info', 'common/header-SA07.tpl', 414, false),)), $this); ?>
<!DOCTYPE html>
<html lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentLocale'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)); ?>
" xml:lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentLocale'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)); ?>
">
<?php echo ''; ?><?php if (! $this->_tpl_vars['pageTitleTranslated']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageTitleTranslated'));?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['pageCrumbTitle']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageCrumbTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageCrumbTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageCrumbTitleTranslated'));?><?php echo ''; ?><?php elseif (! $this->_tpl_vars['pageCrumbTitleTranslated']): ?><?php echo ''; ?><?php $this->assign('pageCrumbTitleTranslated', $this->_tpl_vars['pageTitleTranslated']); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?>

<head>
	<title><?php if ($this->_tpl_vars['query']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['query'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
<?php elseif ($this->_tpl_vars['hasActiveFilters']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['filterValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['currentJournal']): ?> in <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedInitials())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> | <?php if ($this->_tpl_vars['siteSearch']): ?>Sangia Search Results<?php else: ?><?php echo $this->_tpl_vars['siteTitle']; ?>
<?php endif; ?></title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<meta name="description" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaSearchDescription'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<meta name="keywords" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaSearchKeywords'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />

	<?php echo $this->_tpl_vars['metaCustomHeaders']; ?>


	<?php if ($this->_tpl_vars['displayFavicon']): ?>
	<link rel="icon" href="<?php echo $this->_tpl_vars['faviconDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayFavicon']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" type="<?php echo ((is_array($_tmp=$this->_tpl_vars['displayFavicon']['mimeType'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php endif; ?>
	
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/jqueryScripts.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/head.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/lib/pkp/styles/pkp.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/styles/compiled.css" type="text/css" /> 

	<?php echo ((is_array($_tmp=$this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Common::LeftSidebar"), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'leftSidebarCode') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'leftSidebarCode'));?>

	<?php echo ((is_array($_tmp=$this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Common::RightSidebar"), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'rightSidebarCode') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'rightSidebarCode'));?>


	<!-- Default global locale keys for JavaScript -->
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/jsLocaleKeys.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

	<!-- Compiled scripts -->
	<?php if ($this->_tpl_vars['useMinifiedJavaScript']): ?>
		<script type="text/javascript" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/js/pkp.min.js"></script>
	<?php else: ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/minifiedScripts.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php endif; ?>

	<!-- Form validation -->
	<script type="text/javascript" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/lib/pkp/js/lib/jquery/plugins/validate/jquery.validate.js"></script>
	<script type="text/javascript">
		<!--
		// initialise plugins
		<?php echo '
		$(function(){
			jqueryValidatorI18n("'; ?>
<?php echo $this->_tpl_vars['baseUrl']; ?>
<?php echo '", "'; ?>
<?php echo $this->_tpl_vars['currentLocale']; ?>
<?php echo '"); // include the appropriate validation localization
			'; ?>
<?php if ($this->_tpl_vars['validateId']): ?><?php echo '
				$("form[name='; ?>
<?php echo $this->_tpl_vars['validateId']; ?>
<?php echo ']").validate({
					errorClass: "error",
					highlight: function(element, errorClass) {
						$(element).parent().parent().addClass(errorClass);
					},
					unhighlight: function(element, errorClass) {
						$(element).parent().parent().removeClass(errorClass);
					}
				});
			'; ?>
<?php endif; ?><?php echo '
			$(document).on(\'click\', ".tagit", function() {
                $(this).find(\'input\').focus();
            });
		});
		// -->
		'; ?>

	</script>

	<?php if ($this->_tpl_vars['hasSystemNotifications']): ?>
		<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification','op' => 'fetchNotification','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'fetchNotificationUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'fetchNotificationUrl'));?>

		<script type="text/javascript">
			$(function(){
				$.get('<?php echo $this->_tpl_vars['fetchNotificationUrl']; ?>
', null,
					function(data){
						var notifications = data.content;
						var i, l;
						if (notifications && notifications.general) {
							$.each(notifications.general, function(notificationLevel, notificationList) {
								$.each(notificationList, function(notificationId, notification) {
									$.pnotify(notification);
								});
							});
						}
				}, 'json');
			});
		</script>
	<?php endif; ?>	
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/styles/search.css" type="text/css" />
	
	<!-- CSS style via JS sheet -->
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/styles/wizdam_search.css" type="text/css" />
	<!--
	<style rel="stylesheet" type="text/css">@import url(<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/themes/sangiapub/css/journal-mosaic-v1-branded.css);</style>
	-->
	
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/commonCSS.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	
	<?php $_from = $this->_tpl_vars['stylesheets']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['testUrl'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['testUrl']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['cssUrl']):
        $this->_foreach['testUrl']['iteration']++;
?>
		<?php if ($this->_tpl_vars['cssUrl'] == ($this->_tpl_vars['baseUrl'])."/styles/ojs.css"): ?>
			<link rel="stylesheet" href="<?php echo $this->_tpl_vars['cssUrl']; ?>
" type="text/css" />
		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>

	<?php echo $this->_tpl_vars['additionalHeadData']; ?>

	
</head>

<body id="sangia.org">
<a id="skip-to-content" href="#main">Skip to Main Content</a>
<a class="buttontop" href="#sangia.org"><!-- Back to top button --></a>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/banner.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<header class="c-header" style="border-color:#000">
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/navbar.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/navmenu.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/breadcrumbs.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div class="journal-content sangia">
 <div class="s-container u-mb-16">
   <div class="row">

<div id="article" role="main">
<div class="u-mb-16" data-test="search-results-title">

<script type="text/javascript">
	$(function() {
		// Attach the form handler.
		$('#searchForm').pkpHandler('$.pkp.pages.search.SearchFormHandler');
	});
</script>

<?php ob_start(); ?><?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Search::SearchResults::FilterInput",'filterName' => $this->_tpl_vars['filterName'],'filterValue' => $this->_tpl_vars['filterValue']), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('filterInput', ob_get_contents());ob_end_clean(); ?>
<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'search','escape' => false), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'searchFormUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'searchFormUrl'));?>

<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['searchFormUrl'])) ? $this->_run_mod_handler('parse_url', true, $_tmp, @PHP_URL_QUERY) : parse_url($_tmp, @PHP_URL_QUERY)))) ? $this->_run_mod_handler('parse_str', true, $_tmp, $this->_tpl_vars['formUrlParameters']) : parse_str($_tmp, $this->_tpl_vars['formUrlParameters'])); ?>

<form method="GET" id="sub-search" action="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['searchFormUrl'])) ? $this->_run_mod_handler('strtok', true, $_tmp, "?") : strtok($_tmp, "?")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
    <input type="hidden" value="<?php if ($this->_tpl_vars['currentJournal']): ?><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedInitials())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>all<?php endif; ?>" name="journal">
	<div class="c-search c-search--max-width u-mb-24">
		<h1 class="title">
		    <div class="c-search__input-label u-mb-16 u-h1" for="search-keywords">Search</div>
		</h1>
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
			<?php echo ((is_array($_tmp=$this->_tpl_vars['queryFilter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

		<?php endif; ?>
		<div class="c-search__button-container">
			<button type="submit" value="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.search"), $this);?>
" class="c-search__button" data-action="clear-adv">
				<span class="c-search__button-text">Search</span>
				<svg class="u-flex-static" role="img" aria-hidden="true" focusable="false" height="16" width="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
			</button>
		</div>
		<a href="#" class="c-search__link" data-track="click" data-track-category="search results page n150" data-track-action="click link to advanced search page" data-track-label="(not set)" data-test="advance-search-link" tabindex="0">Advance Search</a>
	</div>
	</div>
	<div class="c-facet c-facet--small u-mb-16" data-facet="" data-test="search-filter-box">
	    <h2 class="u-visually-hidden">Filter By:</h2>
	    <div class="c-facet__container">
	        
<?php $this->assign('journalArticlesCount', "[]"); ?>
<?php $this->assign('sectionArticlesCount', "[]"); ?>
<?php $this->assign('disciplineSubjectIndex', "[]"); ?>
<?php $this->assign('safeResults', $this->_tpl_vars['results']); ?>
<?php if (! is_array ( $this->_tpl_vars['safeResults'] )): ?><?php $this->assign('safeResults', "[]"); ?><?php endif; ?>
<?php $_from = $this->_tpl_vars['safeResults']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['result']):
?>
    <?php if (is_array ( $this->_tpl_vars['result'] ) || is_object ( $this->_tpl_vars['result'] )): ?>
        <?php $this->assign('publishedArticle', ((is_array($_tmp=@$this->_tpl_vars['result']['publishedArticle'])) ? $this->_run_mod_handler('default', true, $_tmp, null) : smarty_modifier_default($_tmp, null))); ?>
        <?php $this->assign('article', ((is_array($_tmp=@$this->_tpl_vars['result']['article'])) ? $this->_run_mod_handler('default', true, $_tmp, null) : smarty_modifier_default($_tmp, null))); ?>
        <?php $this->assign('issue', ((is_array($_tmp=@$this->_tpl_vars['result']['issue'])) ? $this->_run_mod_handler('default', true, $_tmp, null) : smarty_modifier_default($_tmp, null))); ?>
        <?php $this->assign('issueAvailable', ((is_array($_tmp=@$this->_tpl_vars['result']['issueAvailable'])) ? $this->_run_mod_handler('default', true, $_tmp, null) : smarty_modifier_default($_tmp, null))); ?>
        <?php $this->assign('journal', ((is_array($_tmp=@$this->_tpl_vars['result']['journal'])) ? $this->_run_mod_handler('default', true, $_tmp, null) : smarty_modifier_default($_tmp, null))); ?>
        <?php $this->assign('section', ((is_array($_tmp=@$this->_tpl_vars['result']['section'])) ? $this->_run_mod_handler('default', true, $_tmp, null) : smarty_modifier_default($_tmp, null))); ?>

        <?php if ($this->_tpl_vars['journal'] && is_object ( $this->_tpl_vars['journal'] )): ?>
            <?php $this->assign('journalName', $this->_tpl_vars['journal']->getLocalizedTitle()); ?>
            <?php if (! isset ( $this->_tpl_vars['journalArticlesCount'][$this->_tpl_vars['journalName']] )): ?>
                <?php $this->assign("journalArticlesCount[".($this->_tpl_vars['journalName'])."]", 0); ?>
            <?php endif; ?>
            <?php $this->assign("journalArticlesCount[".($this->_tpl_vars['journalName'])."]", $this->_tpl_vars['journalArticlesCount'][$this->_tpl_vars['journalName']]+1); ?>
        <?php endif; ?>

        <?php if ($this->_tpl_vars['section'] && is_object ( $this->_tpl_vars['section'] )): ?>
            <?php $this->assign('sectionTitle', $this->_tpl_vars['section']->getLocalizedTitle()); ?>
            <?php if (! isset ( $this->_tpl_vars['sectionArticlesCount'][$this->_tpl_vars['sectionTitle']] )): ?>
                <?php $this->assign("sectionArticlesCount[".($this->_tpl_vars['sectionTitle'])."]", 0); ?>
            <?php endif; ?>
            <?php $this->assign("sectionArticlesCount[".($this->_tpl_vars['sectionTitle'])."]", $this->_tpl_vars['sectionArticlesCount'][$this->_tpl_vars['sectionTitle']]+1); ?>
        <?php endif; ?>

        <?php if ($this->_tpl_vars['article'] && is_object ( $this->_tpl_vars['article'] )): ?>
            <?php if ($this->_tpl_vars['article']->getLocalizedDiscipline()): ?>
                <?php $this->assign('discipline', ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedDiscipline())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))); ?>
                <?php if (! in_array ( $this->_tpl_vars['discipline'] , $this->_tpl_vars['disciplineSubjectIndex'] )): ?>
                    <?php $this->assign("disciplineSubjectIndex[]", $this->_tpl_vars['discipline']); ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($this->_tpl_vars['article']->getLocalizedSubjectClass()): ?>
                <?php $this->assign('subjectClass', ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedSubjectClass())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))); ?>
                <?php if (! in_array ( $this->_tpl_vars['subjectClass'] , $this->_tpl_vars['disciplineSubjectIndex'] )): ?>
                    <?php $this->assign("disciplineSubjectIndex[]", $this->_tpl_vars['subjectClass']); ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
<?php endforeach; endif; unset($_from); ?>

    	<?php if ($this->_tpl_vars['siteSearch']): ?>
        <fieldset class="c-facet__item" data-test="field-set-journals">
            <legend>
                <span id="journal-legend" class="c-facet__label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "search.withinJournal"), $this);?>
 Journal</span>
                <span class="grade-c-hide u-js-show">
                    <button type="button" class="c-facet__button c-facet__button--border" aria-labelledby="journal-legend" data-facet-expander="" data-facet-target="#journal-target" data-track="click" data-track-action="journal filter" data-track-label="button" aria-expanded="false">
                        <span class="c-facet__ellipsis">All</span>
                        <svg class="c-facet__icon" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                    </button>
                </span>
            </legend>
            <span class="u-visually-hidden">Check one or more journals to show results from those journals only.</span>
            <div id="journal-target" data-test="journal-target" data-facet-analytics="journal" class="c-facet-expander u-js-hide" hidden="">
                <div class="c-facet-expander__button-container">
                    <button class="c-facet__submit">Apply filters</button>
                    <button class="c-facet-expander__clear-selection">Clear selection</button>
                </div>
                <ul class="c-facet-expander__list">    
                    <li class="c-facet-expander__list-item">
                        <input name="journal" id="journal-all" value="all" data-action="submit" type="checkbox" checked="checked">
                        <label class="c-facet-expander__link" for="journal-all">
                            <span><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['journalOptions'],'selected' => $this->_tpl_vars['searchJournal']), $this);?>
</span>
                        </label>
                    </li>
                </ul>
                <p class="u-mb-0 u-mt-16">
                    <a href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/search/search?query=<?php if ($this->_tpl_vars['query']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['query'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
<?php elseif ($this->_tpl_vars['hasActiveFilters']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['filterValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" data-track="click" data-track-category="search results page n150" data-track-action="click choose more link in journal filter" data-track-label="(not set)" class="c-facet-expander__link c-facet-expander__link--underline" data-test="advance-search-link-journals">Choose more<span class="u-visually-hidden"> journals</span>
                    </a>
                </p>
            </div>
        </fieldset>
    	<?php else: ?>
    	<fieldset class="c-facet__item u-pa-0" data-test="field-set-journals">
    	    <legend>
    	        <span class="c-facet__label" id="journal-legend">Journal</span>
    	        <span class="grade-c-hide u-js-show">
    	            <button type="button" class="c-facet__button c-facet__button--border" aria-labelledby="journal-legend" data-facet-expander="" data-facet-target="#journal-target" data-track="click" data-track-action="journal filter" data-track-label="button" aria-expanded="false">
    	                <span class="c-facet__ellipsis">All</span>
    	                <svg class="c-facet__icon" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
    	            </button>
    	            </span>
    	       </span>
    	   </legend>
    	   <span class="u-visually-hidden">Check one or more journals to show results from those journals only.</span>
    	   <div id="journal-target" data-test="journal-target" data-facet-analytics="journal" class="c-facet-expander u-js-hide" hidden=""><div class="c-facet-expander__button-container"><button class="c-facet__submit">Apply filters</button><button class="c-facet-expander__clear-selection">Clear selection</button></div>
                <ul class="c-facet-expander__list">
                    <?php $_from = $this->_tpl_vars['journalArticlesCount']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['journalName'] => $this->_tpl_vars['articleCount']):
?>
                    <li class="u-hide c-facet-expander__list-item">
                        <input name="journal" id="journal-<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentUrl'])) ? $this->_run_mod_handler('replace', true, $_tmp, $this->_tpl_vars['baseUrl'], "") : smarty_modifier_replace($_tmp, $this->_tpl_vars['baseUrl'], "")))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
" value="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentUrl'])) ? $this->_run_mod_handler('replace', true, $_tmp, $this->_tpl_vars['baseUrl'], "") : smarty_modifier_replace($_tmp, $this->_tpl_vars['baseUrl'], "")))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
" data-action="submit" type="checkbox" checked="checked">
                        <label class="c-facet-expander__link" for="journal-<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentUrl'])) ? $this->_run_mod_handler('replace', true, $_tmp, $this->_tpl_vars['baseUrl'], "") : smarty_modifier_replace($_tmp, $this->_tpl_vars['baseUrl'], "")))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
">
                            <span><?php echo $this->_tpl_vars['journalName']; ?>
 (<?php echo $this->_tpl_vars['articleCount']; ?>
)</span>
                        </label>
                    </li>
                    <?php endforeach; endif; unset($_from); ?>
                </ul>
                <p class="u-mb-0 u-mt-16">
                    <a href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['journalPath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
/search/search?query=<?php if ($this->_tpl_vars['query']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['query'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
<?php elseif ($this->_tpl_vars['hasActiveFilters']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['filterValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" data-track="click" data-track-category="search results page" data-track-action="click choose more link in journal filter" data-track-label="(not set)" class="c-facet-expander__link c-facet-expander__link--underline" data-test="advance-search-link-journals">Choose more<span class="u-visually-hidden"> journals</span>
                    </a>
                </p>
            </div>
        </fieldset>
    	<?php endif; ?>
    	<fieldset class="c-facet__item u-pa-0" data-test="field-set-article-type">
    	    <legend>
    	        <span class="c-facet__label" id="article-legend">Article type</span>
    	        <span class="grade-c-hide u-js-show">
    	            <button type="button" class="c-facet__button c-facet__button--border" aria-labelledby="article-legend" data-facet-expander="" data-facet-target="#article-type-target" data-track="click" data-track-action="article type filter" data-track-label="button" aria-expanded="false"><span class="c-facet__ellipsis">All</span><svg class="c-facet__icon" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
    	            </button>
    	       </span>
            </legend>
            <span class="u-visually-hidden">Check one or more article types to show results from those article types only.</span>
            <div id="article-type-target" data-test="article-type-target" data-facet-analytics="article type" class="c-facet-expander u-js-hide" hidden=""><div class="c-facet-expander__button-container"><button class="c-facet__submit">Apply filters</button><button class="c-facet-expander__clear-selection">Clear selection</button></div>
                <ul class="c-facet-expander__list">
                    <?php $_from = $this->_tpl_vars['sectionArticlesCount']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['sectionTitle'] => $this->_tpl_vars['articleCount']):
?>
                    <li class="c-facet-expander__list-item">
                        <input name="article_type" id="article-type-<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionTitle'])) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionTitle'])) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
" type="checkbox">
                        <label class="c-facet-expander__link" for="article-type-<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionTitle'])) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
">
                            <span><?php echo $this->_tpl_vars['sectionTitle']; ?>
 (<?php echo $this->_tpl_vars['articleCount']; ?>
)</span>
                        </label>
                    </li>
                    <?php endforeach; endif; unset($_from); ?>
                </ul>
            </div>
        </fieldset>
        <fieldset class="c-facet__item u-pa-0" data-test="field-set-subjects">
            <legend>
                <span class="c-facet__label" id="subject-legend">Subject</span>
                <span class="grade-c-hide u-js-show">
                    <button type="button" class="c-facet__button c-facet__button--border" aria-labelledby="subject-legend" data-facet-expander="" data-facet-target="#subject-target" data-track="click" data-track-action="subject filter" data-track-label="button" aria-expanded="false"><span class="c-facet__ellipsis">All</span><svg class="c-facet__icon" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                    </button>
                </span>
            </legend>
            <span class="u-visually-hidden">Check one or more subjects to show results from those subjects only.</span>
            <div id="subject-target" data-test="subject-target" data-facet-analytics="subject" class="c-facet-expander u-js-hide" hidden=""><div class="c-facet-expander__button-container"><button class="c-facet__submit">Apply filters</button><button class="c-facet-expander__clear-selection">Clear selection</button></div>
                <ul class="c-facet-expander__list">
                    <?php $_from = $this->_tpl_vars['disciplineSubjectIndex']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['indexEntry']):
?>
                    <li class="c-facet-expander__list-item">
                        <input type="checkbox" name="subject" id="subject-<?php echo ((is_array($_tmp=$this->_tpl_vars['indexEntry'])) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['indexEntry'])) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
" data-action="submit">
                        <label class="c-facet-expander__link" for="subject-<?php echo ((is_array($_tmp=$this->_tpl_vars['indexEntry'])) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
">
                            <span><?php echo $this->_tpl_vars['indexEntry']; ?>
</span>
                        </label>
                    </li>
                    <?php endforeach; endif; unset($_from); ?>
                </ul>
            </div>
        </fieldset>
        <fieldset class="c-facet__item u-pa-0" data-test="field-set-date">
            <legend>
                <span class="c-facet__label" id="date-legend">Date</span>
                <span class="grade-c-hide u-js-show">
                    <button type="button" class="c-facet__button c-facet__button--border" aria-labelledby="date-legend" data-facet-expander="" data-facet-target="#date-target" data-track="click" data-track-action="date filter" data-track-label="button" aria-expanded="false"><span class="c-facet__ellipsis">All</span><svg class="c-facet__icon" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                    </button>
                </span>
            </legend>
            <span class="u-visually-hidden">Choose a date option to show results from those dates only.</span>
            <div id="date-target" data-test="date-target" data-facet-analytics="date" class="c-facet-expander u-js-hide" hidden=""><div class="c-facet-expander__button-container"><button class="c-facet__submit">Apply filters</button><button class="c-facet-expander__clear-selection">Clear selection</button></div>
                <ul class="c-facet-expander__list">
                    <li class="c-facet-expander__list-item">
                        <input name="date_range" id="date-range-today" value="today" type="radio">
                        <label class="c-facet-expander__link" for="date-range-today">
                            <span>Today</span>
                        </label>
                    </li>
                    <li class="c-facet-expander__list-item">
                        <input name="date_range" id="date-range-last_7_days" value="last_7_days" type="radio">
                        <label class="c-facet-expander__link" for="date-range-last_7_days">
                            <span>Last 7 days</span>
                        </label>
                    </li>
                    <li class="c-facet-expander__list-item">
                        <input name="date_range" id="date-range-last_30_days" value="last_30_days" type="radio">
                        <label class="c-facet-expander__link" for="date-range-last_30_days">
                            <span>Last 30 days</span>
                        </label>
                    </li>
                    <li class="c-facet-expander__list-item">
                        <input name="date_range" id="date-range-last_year" value="last_year" type="radio">
                        <label class="c-facet-expander__link" for="date-range-last_year">
                            <span>Last 12 months</span>
                        </label>
                    </li>
                    <li class="c-facet-expander__list-item">
                        <input name="date_range" id="date-range-last_2_years" value="last_2_years" type="radio">
                        <label class="c-facet-expander__link" for="date-range-last_2_years">
                            <span>Last 2 years</span>
                        </label>
                    </li>
                    <li class="c-facet-expander__list-item">
                        <input name="date_range" id="date-range-last_5_years" value="last_5_years" type="radio">
                        <label class="c-facet-expander__link" for="date-range-last_5_years">
                            <span>Last 5 years</span>
                        </label>
                    </li>
                </ul>
                <p class="u-mb-0 u-mt-16"><a class="c-facet-expander__link c-facet-expander__link--underline" data-test="advance-search-link-date" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/search/search?query=<?php if ($this->_tpl_vars['query']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['query'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
<?php elseif ($this->_tpl_vars['hasActiveFilters']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['filterValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>">Custom date range</a></p>
            </div>
        </fieldset>
        <span class="c-facet__clear-all">
            <a href="<?php if ($this->_tpl_vars['currentJournal']): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getUrl())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_tpl_vars['baseUrl']; ?>
<?php endif; ?>/search/search?query=<?php if ($this->_tpl_vars['query']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['query'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
<?php elseif ($this->_tpl_vars['hasActiveFilters']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['filterValue'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" data-track="click" data-track-category="search results page n150" data-track-action="click clear all filters" data-track-label="(not set)">Clear all filters</a>
        </span>
    	</div>
    	<fieldset class="c-sort-by u-pa-0" data-sort-by="">
    	    <legend class="c-sort-by__heading">Sort by:</legend>
    	    <div class="c-sort-by__input-container">
    	        <input id="sort-by-relevance" class="c-sort-by__input" name="order" value="relevance" type="radio" checked="" data-test="order-relevance"><label class="c-sort-by__label" for="sort-by-relevance">Relevance</label>
    	    </div>
    	    <div class="c-sort-by__input-container">
    	        <input id="sort-by-date_desc" class="c-sort-by__input" name="order" value="date_desc" type="radio" checked="" data-test="order-date_desc"><label class="c-sort-by__label" for="sort-by-date_desc">Date Published (newest to oldest)</label>
    	    </div>
    	    <div class="c-sort-by__input-container">
    	        <input id="sort-by-date_asc" class="c-sort-by__input" name="order" value="date_asc" type="radio" checked="" data-test="order-date_asc"><label class="c-sort-by__label" for="sort-by-date_asc">Date Published (oldest to newest)</label>
    	    </div>
    	    <div class="c-sort-by__input-container">
    	        <input id="results-only-access-checkbox" class="c-sort-by__input" type="checkbox" checked="checked" aria-describedby="preview-tooltip"><span class="tooltip" id="preview-tooltip" role="tooltip">
					Content is preview-only when you or your institution have not yet subscribed to it.
					<br>
					<br>
					By making our abstracts and previews universally accessible we help you purchase only the content that is relevant to you.
				</span>
    	        <label class="c-sort-by__label" for="results-only-access-checkbox">Include Preview-Only content <img src="//assets.sangia.org/image/classical/lock.png" alt="restricted content" height="13" width="13"></label>
            </div>
    	</fieldset>
	</div>

	<div class="c-list-header">
	    <div class="u-mb-0"><span data-test="results-data" class="u-display-flex"><span>Showing <?php if ($this->_tpl_vars['results'] && is_object ( $this->_tpl_vars['results'] )): ?><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['results']), $this);?>
<?php endif; ?> results.</span></span>
        </div>
    </div>
