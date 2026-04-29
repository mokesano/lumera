<?php /* Smarty version 2.6.26, created on 2026-04-04 05:50:44
         compiled from common/header-ABOUT.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'substr', 'common/header-ABOUT.tpl', 2, false),array('modifier', 'assign', 'common/header-ABOUT.tpl', 13, false),array('modifier', 'strip_tags', 'common/header-ABOUT.tpl', 21, false),array('modifier', 'escape', 'common/header-ABOUT.tpl', 21, false),array('function', 'translate', 'common/header-ABOUT.tpl', 13, false),array('function', 'call_hook', 'common/header-ABOUT.tpl', 36, false),array('function', 'url', 'common/header-ABOUT.tpl', 78, false),)), $this); ?>
<!DOCTYPE html>
<html lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentLocale'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)); ?>
">
<?php echo ''; ?><?php if (! $this->_tpl_vars['pageTitleTranslated']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageTitleTranslated'));?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['pageCrumbTitle']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageCrumbTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageCrumbTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageCrumbTitleTranslated'));?><?php echo ''; ?><?php elseif (! $this->_tpl_vars['pageCrumbTitleTranslated']): ?><?php echo ''; ?><?php $this->assign('pageCrumbTitleTranslated', $this->_tpl_vars['pageTitleTranslated']); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?>

<head>
    <title><?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
<?php if ($this->_tpl_vars['currentJournal']): ?> - <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 | Sangia<?php else: ?> | <?php echo $this->_tpl_vars['siteTitle']; ?>
<?php endif; ?></title>
        
    <meta name="description" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaSearchDescription'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <meta name="keywords" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaSearchKeywords'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
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
	
	<?php echo ((is_array($_tmp=$this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Common::LeftSidebar"), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'leftSidebarCode') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'leftSidebarCode'));?>

	<?php echo ((is_array($_tmp=$this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Common::RightSidebar"), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'rightSidebarCode') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'rightSidebarCode'));?>


	<!-- Default global locale keys for JavaScript -->
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/jsLocaleKeys.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js" referrerpolicy="strict-origin-when-cross-origin"></script>

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
<a class="buttontop" href="#sangia.org"></a><!-- Back to top button -->

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
$this->_smarty_include(array('smarty_include_tpl_file' => "common/journal-identity.tpl", 'smarty_include_vars' => array()));
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

<div class="journal-content sangia u-mt-48" role="main">

<div class="journal-content live-area-wrapper">
	<div class="row">
	
	<?php echo '<div class="sidebar"><div class="column medium-2">'; ?><?php if ($this->_tpl_vars['leftSidebarCode'] || $this->_tpl_vars['rightSidebarCode']): ?><?php echo ''; ?><?php if (! $this->_tpl_vars['currentJournal']): ?><?php echo '<div class="default-menu">'; ?><?php echo $this->_tpl_vars['leftSidebarCode']; ?><?php echo ''; ?><?php echo $this->_tpl_vars['rightSidebarCode']; ?><?php echo '</div>'; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['currentJournal']): ?><?php echo '<nav class="journal-subnav"><div class="live"><ul class="c-sidemenu c-nav c-nav--stacked c-collapse-at-lt-md"><li class="c-sidemenu"><a href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => "editorial-team"), $this);?><?php echo '" data-track="click" data-track-label="link" data-test="explore-nav-item">'; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.editorialTeam"), $this);?><?php echo '</a></li>'; ?><?php if ($this->_tpl_vars['membershipGroups']): ?><?php echo ''; ?><?php $_from = $this->_tpl_vars['membershipGroups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['peopleGroup']):
?><?php echo '<li class="c-sidemenu"><a href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => "display-membership",'path' => $this->_tpl_vars['peopleGroup']['group_id']), $this);?><?php echo '" data-track="click" data-track-label="link" data-test="explore-nav-item">'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['peopleGroup']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo '</a></li>'; ?><?php endforeach; endif; unset($_from); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::Other"), $this);?><?php echo ''; ?><?php $_from = $this->_tpl_vars['navMenuItems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['navItemKey'] => $this->_tpl_vars['navItem']):
?><?php echo ''; ?><?php if ($this->_tpl_vars['navItem']['url'] != '' && $this->_tpl_vars['navItem']['name'] != ''): ?><?php echo '<li class="c-sidemenu"><a href="'; ?><?php if ($this->_tpl_vars['navItem']['isAbsolute']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php echo $this->_tpl_vars['baseUrl']; ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo ''; ?><?php endif; ?><?php echo '"  data-track="click" data-track-label="link" data-test="explore-nav-item">'; ?><?php if ($this->_tpl_vars['navItem']['isLiteral']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['navItem']['name']), $this);?><?php echo ''; ?><?php endif; ?><?php echo '</a></li>'; ?><?php endif; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo ''; ?><?php if ($this->_tpl_vars['enableAnnouncements']): ?><?php echo '<li class="c-sidemenu"><a href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement'), $this);?><?php echo '">News & Announcement</a></li>'; ?><?php endif; ?><?php echo ''; ?><?php echo ''; ?><?php if ($this->_tpl_vars['donationEnabled']): ?><?php echo '<li id="linkJournalDonations" class="c-sidemenu"><a href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'donations'), $this);?><?php echo '" data-track="click" data-track-label="link" data-test="explore-nav-item">'; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.type.donation"), $this);?><?php echo '</a></li>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['currentJournal']->getSetting('membershipFee')): ?><?php echo '<li class="c-sidemenu u-hide"><a href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'memberships'), $this);?><?php echo '" data-track="click" data-track-label="link" data-test="explore-nav-item">'; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.memberships"), $this);?><?php echo '</a></li>'; ?><?php endif; ?><?php echo ''; ?><?php if (! ( $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('publisherNote') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('contributorNote') == '' && empty ( $this->_tpl_vars['journalSettings']['contributors'] ) && $this->_tpl_vars['currentJournal']->getLocalizedSetting('sponsorNote') == '' && empty ( $this->_tpl_vars['journalSettings']['sponsors'] ) )): ?><?php echo '<li class="c-sidemenu"><a href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'sponsorship'), $this);?><?php echo '" data-track="click" data-track-label="link" data-test="explore-nav-item">'; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.journalSponsorship"), $this);?><?php echo '</a></li>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('history') != ''): ?><?php echo '<li class="c-sidemenu"><a href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'history'), $this);?><?php echo '" data-track="click" data-track-label="link" data-test="explore-nav-item">'; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.history"), $this);?><?php echo '</a></li>'; ?><?php endif; ?><?php echo '<li class="c-sidemenu"><a href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'contact'), $this);?><?php echo '" data-track="click" data-track-label="link" data-test="explore-nav-item">'; ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact"), $this);?><?php echo '</a></li></ul></div></nav>'; ?><?php endif; ?><?php echo '</div><section class="column medium-3 section" role="aside"><section class="box"><section><h4 class="headline-524909129">Want to publish with us? Submit your Manuscript online.</h4></section><a href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','op' => 'submit'), $this);?><?php echo '" target="_blank" data-track="click" class="button-base-2906877647"><span class="button-label-1281676810">Submit paper</span><svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1969128361"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg></a></section>'; ?><?php echo '<section class="editorial-board-by-country"><div class="divider"></div><h3 class="editorial-board-by-country-title u-h4">Editorial board by country/region </h3><div id="country-map" class="country-map"></div><div class="editors-by-country-text u-margin-s-ver text-s">0 editors and editorial board members in 0 countries/regions</div><ol class="editors-by-country-ordered-list text-s"><li class="country-list-item">Negara (0)</li></ol></section><section class="gender-indicator-metrics-section"><div class="divider"></div><h3 class="gender-indicator-title u-h4">Gender diversity of editors and editorial board members</h3><div class="chart-area"><div class="pie-chart"><canvas id="genderChart"></canvas></div><ul class="legend"><li class="legend-item u-margin-xs-bottom" style="--bullet-color: #FF6A19;"><div><span class="legend-percentage">0%</span><span class="atribut">man</span></div></li><li class="legend-item u-margin-xs-bottom" style="--bullet-color: #3F89FF;"><div><span class="legend-percentage">0%</span><span class="atribut">woman</span></div></li><li class="legend-item u-margin-xs-bottom" style="--bullet-color: #56BF70;"><div><span class="legend-percentage">0%</span><span class="atribut">non-binary or gender diverse</span></div></li><li class="legend-item u-margin-xs-bottom" style="--bullet-color: #4D4D4D;"><div><span class="legend-percentage">0%</span><span class="atribut">prefer not to disclose</span></div></li></ul></div><p class="u-padding-s-top text-s u-padding-s-right">Data represents responses from 100.00% of 0 editors and editorial board members</p><div class="divider"></div></section>'; ?><?php echo ''; ?><?php if ($this->_tpl_vars['leftSidebarCode'] || $this->_tpl_vars['rightSidebarCode']): ?><?php echo ''; ?><?php if ($this->_tpl_vars['currentJournal']): ?><?php echo '<div class="default-sidemenu">'; ?><?php if ($this->_tpl_vars['rightSidebarCode']): ?><?php echo ''; ?><?php echo ''; ?><?php echo $this->_tpl_vars['rightSidebarCode']; ?><?php echo ''; ?><?php echo ''; ?><?php endif; ?><?php echo '</div>'; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo '</section></div>'; ?>


<div class="column medium-7" role="main">
<section class="article about">
<h2 class="main-heading"><?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
</h2>

<?php if ($this->_tpl_vars['pageSubtitle'] && ! $this->_tpl_vars['pageSubtitleTranslated']): ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageSubtitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageSubtitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageSubtitleTranslated'));?>
<?php endif; ?>
<?php if ($this->_tpl_vars['pageSubtitleTranslated']): ?>
	<h3 class="sub-heading"><?php echo $this->_tpl_vars['pageSubtitleTranslated']; ?>
</h3>
<?php endif; ?>

<section id="content" class="publication content-body">
