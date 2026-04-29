<?php /* Smarty version 2.6.26, created on 2026-04-09 23:44:54
         compiled from common/header.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'substr', 'common/header.tpl', 2, false),array('modifier', 'assign', 'common/header.tpl', 13, false),array('modifier', 'escape', 'common/header.tpl', 24, false),array('modifier', 'strip_tags', 'common/header.tpl', 25, false),array('modifier', 'nl2br', 'common/header.tpl', 33, false),array('function', 'translate', 'common/header.tpl', 13, false),array('function', 'call_hook', 'common/header.tpl', 61, false),array('function', 'url', 'common/header.tpl', 102, false),)), $this); ?>
<!DOCTYPE html>
<html lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentLocale'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)); ?>
">
<?php echo ''; ?><?php if (! $this->_tpl_vars['pageTitleTranslated']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageTitleTranslated'));?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['pageCrumbTitle']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageCrumbTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageCrumbTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageCrumbTitleTranslated'));?><?php echo ''; ?><?php elseif (! $this->_tpl_vars['pageCrumbTitleTranslated']): ?><?php echo ''; ?><?php $this->assign('pageCrumbTitleTranslated', $this->_tpl_vars['pageTitleTranslated']); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.openJournalSystems"), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'applicationName') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'applicationName'));?><?php echo ''; ?><?php $this->assign('applicationName', 'ScholarWizdam'); ?><?php echo ''; ?><?php $this->assign('VersionFork', "1.0.0.0"); ?><?php echo ''; ?>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<title><?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
<?php if ($this->_tpl_vars['currentJournal']): ?> - <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> | Sangia</title>

	<?php if (! empty ( $this->_tpl_vars['about'] )): ?>
    	<meta name="description" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['about'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php else: ?>
    	<meta name="description" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaSearchDescription'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <?php endif; ?>
    <?php if ($this->_tpl_vars['intro']): ?>
        <meta name="keywords" content="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['intro'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
    <?php else: ?>
        <meta name="keywords" content="<?php echo ((is_array($_tmp=$this->_tpl_vars['metaSearchKeywords'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<?php endif; ?>
	<meta name="generator" content="<?php echo $this->_tpl_vars['applicationName']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['VersionFork'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 - <?php echo ((is_array($_tmp=$this->_tpl_vars['currentVersionString'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
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
	
	<script>
    	<?php echo '
    	$(document).ready(function(){
    	  $(".nav-tabs a").click(function(){
    	    $(this).tab(\'show\');
    	  });
    	});
    	'; ?>

	</script>	

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

<div class="journal-content sangia u-mt-48" role="main">

<div class="live-area-wrapper">
	<div class="row">
	<?php if ($this->_tpl_vars['leftSidebarCode'] || $this->_tpl_vars['rightSidebarCode']): ?> 
	<div class="sidebar">
	    <section class="column u-js-hide"></section>
		<div class="column medium-3">
		    
		    <?php if (! $this->_tpl_vars['currentJournal']): ?>
    		    <?php if ($this->_tpl_vars['leftSidebarCode']): ?>
        		    <div class="default-menu-left u-hide">
        		        <?php echo $this->_tpl_vars['leftSidebarCode']; ?>

                    </div>
    		    <?php endif; ?>
		    <?php endif; ?>
            
            <?php if ($this->_tpl_vars['currentJournal']): ?>
			<nav class="journal-subnav">
    			<div class="live">					
    			    <ul class="c-sidemenu c-nav c-nav--stacked c-collapse-at-lt-md">
    			        
    			        <li class="c-sidemenu"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => "editorial-team",'anchor' => ""), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.editorialTeam"), $this);?>
</a></li>
    
                        <?php if ($this->_tpl_vars['membershipGroups']): ?>
                            <?php $_from = $this->_tpl_vars['membershipGroups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['peopleGroup']):
?>
                            <li class="c-sidemenu"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => "display-membership",'path' => $this->_tpl_vars['peopleGroup']['group_id']), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo ((is_array($_tmp=$this->_tpl_vars['peopleGroup']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li>
                            <?php endforeach; endif; unset($_from); ?>
                        <?php endif; ?>
    			        
    			        <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::Other"), $this);?>

    			        
    			        <?php $_from = $this->_tpl_vars['navMenuItems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['navItemKey'] => $this->_tpl_vars['navItem']):
?><?php if ($this->_tpl_vars['navItem']['url'] != '' && $this->_tpl_vars['navItem']['name'] != ''): ?>
    			        <li class="c-sidemenu"><a href="<?php if ($this->_tpl_vars['navItem']['isAbsolute']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_tpl_vars['baseUrl']; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" data-track="click" data-track-label="link" data-test="explore-nav-item" ><?php if ($this->_tpl_vars['navItem']['isLiteral']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['navItem']['name']), $this);?>
<?php endif; ?></a></li><?php endif; ?><?php endforeach; endif; unset($_from); ?>
                        
    			        <?php if ($this->_tpl_vars['enableAnnouncements']): ?><li class="c-sidemenu"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">News & Announcement</a></li><?php endif; ?>    			        
    			        <?php if ($this->_tpl_vars['donationEnabled']): ?><li id="linkJournalContact" class="c-sidemenu"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'donations'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.type.donation"), $this);?>
</a></li><?php endif; ?>
    
    			        <?php if ($this->_tpl_vars['currentJournal']->getSetting('membershipFee')): ?><li class="c-sidemenu u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'memberships'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.memberships"), $this);?>
</a></li><?php endif; ?>
    
    			        <?php if (! ( $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('publisherNote') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('contributorNote') == '' && empty ( $this->_tpl_vars['journalSettings']['contributors'] ) && $this->_tpl_vars['currentJournal']->getLocalizedSetting('sponsorNote') == '' && empty ( $this->_tpl_vars['journalSettings']['sponsors'] ) )): ?><li class="c-sidemenu"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'sponsorship'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.journalSponsorship"), $this);?>
</a></li><?php endif; ?>
    
    			        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('history') != ''): ?><li class="c-sidemenu"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'history'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.history"), $this);?>
</a></li><?php endif; ?>
    			        
    			        <li class="c-sidemenu"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'contact'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact"), $this);?>
</a></li>
    			     </ul>
    			</div>
			</nav>
			<?php endif; ?>
					    
            <?php if ($this->_tpl_vars['rightSidebarCode']): ?>
                <div class="default-menu-right">
                    <?php echo $this->_tpl_vars['rightSidebarCode']; ?>

                </div>
            <?php endif; ?>

		</div>
	</div>
	<?php endif; ?>

    <div class="column medium-9" role="main">
        <section class="article about">
            <h2 class="main-heading"><?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
</h2>
            
            <?php if ($this->_tpl_vars['pageSubtitle'] && ! $this->_tpl_vars['pageSubtitleTranslated']): ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageSubtitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageSubtitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageSubtitleTranslated'));?>
<?php endif; ?>
            <?php if ($this->_tpl_vars['pageSubtitleTranslated']): ?>
            	<h3 class="sub-heading"><?php echo $this->_tpl_vars['pageSubtitleTranslated']; ?>
</h3>
            <?php endif; ?>
            
            <div id="content" class="article publication content-body body">
