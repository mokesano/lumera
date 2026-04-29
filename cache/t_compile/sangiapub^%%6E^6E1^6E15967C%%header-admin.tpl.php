<?php /* Smarty version 2.6.26, created on 2026-04-04 14:38:18
         compiled from common/header-parts/header-admin.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'substr', 'common/header-parts/header-admin.tpl', 2, false),array('modifier', 'assign', 'common/header-parts/header-admin.tpl', 14, false),array('modifier', 'escape', 'common/header-parts/header-admin.tpl', 28, false),array('modifier', 'time_ago', 'common/header-parts/header-admin.tpl', 159, false),array('modifier', 'date_format', 'common/header-parts/header-admin.tpl', 173, false),array('function', 'translate', 'common/header-parts/header-admin.tpl', 14, false),array('function', 'call_hook', 'common/header-parts/header-admin.tpl', 44, false),array('function', 'url', 'common/header-parts/header-admin.tpl', 85, false),)), $this); ?>
<!DOCTYPE html>
<html lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentLocale'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)); ?>
">
<?php echo ''; ?><?php if (! $this->_tpl_vars['pageTitleTranslated']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageTitleTranslated'));?><?php echo ''; ?><?php echo ''; ?><?php if ($this->_tpl_vars['pageTitle'] == "common.openJournalSystems"): ?><?php echo ''; ?><?php $this->assign('pageTitleTranslated', 'Editorial Management System'); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['pageCrumbTitle']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageCrumbTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageCrumbTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageCrumbTitleTranslated'));?><?php echo ''; ?><?php elseif (! $this->_tpl_vars['pageCrumbTitleTranslated']): ?><?php echo ''; ?><?php $this->assign('pageCrumbTitleTranslated', $this->_tpl_vars['pageTitleTranslated']); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?>

<head>
    <title>Account Overview (<?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
) | ScholarWizdam</title>
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
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/commonCSS.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/styles/modern-forms.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/static/styles/user-home.css"  type="text/css" />

	<?php $_from = $this->_tpl_vars['stylesheets']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['testUrl'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['testUrl']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['cssUrl']):
        $this->_foreach['testUrl']['iteration']++;
?>
		<?php if ($this->_tpl_vars['cssUrl'] != ($this->_tpl_vars['baseUrl'])."/styles/ojs.css"): ?>
			<link rel="stylesheet" href="<?php echo $this->_tpl_vars['cssUrl']; ?>
" type="text/css" />
		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>

	<?php echo $this->_tpl_vars['additionalHeadData']; ?>

	
</head>

<body id="sangia.org" class="u-full-height white">
<a id="skip-to-content" href="#main">Skip to Main Content</a>
<a class="buttontop" href="#sangia.org"></a>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/banner.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div class="idp-layout-container">

<header class="c-header" style="border-color:#000">
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/navbar.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>

<main id="main-account-page" class="u-flex-grow">
    <section class="c-masthead" aria-label="masthead" data-c-masthead="">
        <div class="c-masthead__container">
            <div class="c-masthead__main">
                <div class="c-masthead__breadcrumbs"></div>
                <?php if ($this->_tpl_vars['userData']): ?>
                    <div class="c-masthead__main-image c-masthead__main-image--profile-image c-masthead__main-image-icon">
                        <?php if ($this->_tpl_vars['userData']['profileImage'] && $this->_tpl_vars['userData']['profileImage']['uploadName']): ?>
                        <figure class="Avatar Avatar--size-108"><img src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['userData']['profileImage']['uploadName']; ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 1) : substr($_tmp, 0, 1)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 1) : substr($_tmp, 0, 1)); ?>
" class="Avatar__img is-inside-mask">
                        </figure>
                        <?php else: ?>
                        <svg class="c-masthead__main-image-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1c6.075 0 11 4.925 11 11s-4.925 11-11 11S1 18.075 1 12 5.925 1 12 1Zm0 16c-1.806 0-3.52.994-4.664 2.698A8.947 8.947 0 0 0 12 21a8.958 8.958 0 0 0 4.664-1.301C15.52 17.994 13.806 17 12 17Zm0-14a9 9 0 0 0-6.25 15.476C7.253 16.304 9.54 15 12 15s4.747 1.304 6.25 3.475A9 9 0 0 0 12 3Zm0 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"></path>
                        </svg>
                        <?php endif; ?>
                        <?php if ($this->_tpl_vars['userData']['is_verified']): ?>
                        <span class="verified badge icon" title="Your account is valid"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><circle cx="50" cy="50" fill="#1DA1F2" r="40"></circle><path d="M30 55 L45 70 L70 35" stroke="#ffffff" stroke-width="12" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </span>
                        <?php else: ?>
                        <span class="unverified badge icon" title="Your account needs to be validated"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><path d="M35 35 L65 65" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path><path d="M35 65 L65 35" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </span>
                        <?php endif; ?>
                </div>
                <div class="c-masthead__header">
                    <div class="c-masthead__welcome">
                        <span class="user-welcome"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.user.welcome"), $this);?>
</span>
                        <?php if ($this->_tpl_vars['userData']['current_login']): ?>
                        <span class="date-item"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['userData']['current_login'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('time_ago', true, $_tmp) : $this->_plugins['modifier']['time_ago'][0][0]->smartyTimeAgo($_tmp)); ?>
 🚩</span>
                        <?php endif; ?>
                    </div>
                    <h1 class="c-masthead__heading"><?php if ($this->_tpl_vars['userData']['salutation']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['salutation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php endif; ?><?php if ($this->_tpl_vars['userData']['firstName'] !== $this->_tpl_vars['userData']['lastName']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php endif; ?><?php if ($this->_tpl_vars['userData']['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php endif; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['userData']['suffix']): ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['suffix'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></h1>
                    <p class="c-masthead__subheading" data-test-masthead-subheading=""><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
        
    <div class="account-container u-mt-48" role="main">
        <p class="dated-info">
            <?php if ($this->_tpl_vars['userData']['registered']): ?>
            <span class="date-item registered"><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.user.registered"), $this);?>
:</strong> <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['userData']['registered'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>
 <span class="text-muted date-item">(<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['userData']['registered'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('time_ago', true, $_tmp) : $this->_plugins['modifier']['time_ago'][0][0]->smartyTimeAgo($_tmp)); ?>
)</span>
            </span>
            <?php endif; ?>
            <span class="date-item validated"><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.user.validated"), $this);?>
:</strong> 
                <?php if ($this->_tpl_vars['userData']['validated']): ?>
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['validated'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>

                    <span class="text-muted date-item">(<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['validated'])) ? $this->_run_mod_handler('time_ago', true, $_tmp) : $this->_plugins['modifier']['time_ago'][0][0]->smartyTimeAgo($_tmp)); ?>
)</span>
                <?php else: ?>
                    <span class="text-warning unvalidated highlight"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.user.unvalidated"), $this);?>
</span>
                <?php endif; ?>
            </span>
            <span class="date-item last_login"><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.user.lastLogin"), $this);?>
:</strong> 
                <?php if ($this->_tpl_vars['userData']['last_login']): ?>
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['last_login'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>

                    <span class="text-muted date-item">(<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['last_login'])) ? $this->_run_mod_handler('time_ago', true, $_tmp) : $this->_plugins['modifier']['time_ago'][0][0]->smartyTimeAgo($_tmp)); ?>
)</span>
                <?php else: ?>
                    <span class="text-muted date-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.timeAgo.never"), $this);?>
</span>
                <?php endif; ?>
            </span>
            <span class="date-item current_login"><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.user.currentLogin"), $this);?>
:</strong> 
                <?php if ($this->_tpl_vars['userData']['current_login']): ?>
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['current_login'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>

                    <span class="text-muted date-item">(<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['current_login'])) ? $this->_run_mod_handler('time_ago', true, $_tmp) : $this->_plugins['modifier']['time_ago'][0][0]->smartyTimeAgo($_tmp)); ?>
)</span>
                <?php else: ?>
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['current_login'])) ? $this->_run_mod_handler('time_ago', true, $_tmp) : $this->_plugins['modifier']['time_ago'][0][0]->smartyTimeAgo($_tmp)); ?>

                <?php endif; ?>
            </span>
        </p>
        <h2 class="main-heading u-hide"><?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
</h2>
        <?php if ($this->_tpl_vars['pageSubtitle'] && ! $this->_tpl_vars['pageSubtitleTranslated']): ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageSubtitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageSubtitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageSubtitleTranslated'));?>
<?php endif; ?>
        <?php if ($this->_tpl_vars['pageSubtitleTranslated']): ?>
        	<h3 class="sub-heading"><?php echo $this->_tpl_vars['pageSubtitleTranslated']; ?>
</h3>
        <?php endif; ?>
            
        