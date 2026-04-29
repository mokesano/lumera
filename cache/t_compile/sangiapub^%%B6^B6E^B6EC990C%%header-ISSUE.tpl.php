<?php /* Smarty version 2.6.26, created on 2026-04-04 06:00:07
         compiled from common/header-ISSUE.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'substr', 'common/header-ISSUE.tpl', 2, false),array('modifier', 'assign', 'common/header-ISSUE.tpl', 24, false),array('modifier', 'escape', 'common/header-ISSUE.tpl', 37, false),array('modifier', 'strip_tags', 'common/header-ISSUE.tpl', 38, false),array('modifier', 'strlen', 'common/header-ISSUE.tpl', 167, false),array('modifier', 'slugify', 'common/header-ISSUE.tpl', 172, false),array('modifier', 'date_format', 'common/header-ISSUE.tpl', 191, false),array('modifier', 'nl2br', 'common/header-ISSUE.tpl', 205, false),array('modifier', 'regex_replace', 'common/header-ISSUE.tpl', 311, false),array('modifier', 'truncate', 'common/header-ISSUE.tpl', 311, false),array('modifier', 'replace', 'common/header-ISSUE.tpl', 485, false),array('modifier', 'string_format', 'common/header-ISSUE.tpl', 519, false),array('function', 'translate', 'common/header-ISSUE.tpl', 24, false),array('function', 'call_hook', 'common/header-ISSUE.tpl', 54, false),array('function', 'url', 'common/header-ISSUE.tpl', 95, false),array('function', 'native_url', 'common/header-ISSUE.tpl', 183, false),)), $this); ?>
<!DOCTYPE html>
<html lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentLocale'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)); ?>
">
<?php echo ''; ?><?php if (! $this->_tpl_vars['pageTitleTranslated']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageTitleTranslated'));?><?php echo ''; ?><?php echo ''; ?><?php if ($this->_tpl_vars['pageTitle'] == "common.openJournalSystems"): ?><?php echo ''; ?><?php $this->assign('pageTitleTranslated', 'No Current Issue'); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['pageCrumbTitle']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageCrumbTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageCrumbTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageCrumbTitleTranslated'));?><?php echo ''; ?><?php elseif (! $this->_tpl_vars['pageCrumbTitleTranslated']): ?><?php echo ''; ?><?php $this->assign('pageCrumbTitleTranslated', $this->_tpl_vars['pageTitleTranslated']); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<title><?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
 - <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 | Sangia Publishing</title>
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
/assets/static/styles/wizdam-mosaic-v1-branded.css" type="text/css" />
	
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

<main class="<?php if ($this->_tpl_vars['issue']): ?>journal-content<?php else: ?>volumes-content<?php endif; ?> sangia-volumes" role="main">
<div class="live-area-wrapper">
	<div class="row">

<?php if ($this->_tpl_vars['issue']): ?>
<div class="column medium-12 cleared container-type-title" role="main" data-container-type="title" >
    
<main itemscope="itemscope" itemtype="https://schema.org/PublicationIssue">
	<section class="u-display-flex u-align-items-center u-justify-content-space-between u-flex-wrap u-mb-16">

		<?php if (! $this->_tpl_vars['showToc']): ?>
		<?php if ($this->_tpl_vars['issueId']): ?>
						<?php $this->assign('issueVolume', $this->_tpl_vars['issue']->getVolume()); ?>

						<?php $this->assign('issueNum', $this->_tpl_vars['issue']->getNumber()); ?>
						<?php if (((is_array($_tmp=$this->_tpl_vars['issueNum'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
								<?php $this->assign('issueSlug', ""); ?>
			<?php else: ?>
								<?php $this->assign('issueSlug', ((is_array($_tmp=$this->_tpl_vars['issueNum'])) ? $this->_run_mod_handler('slugify', true, $_tmp) : PKPString::slugify($_tmp))); ?>
								<?php if (((is_array($_tmp=$this->_tpl_vars['issueSlug'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
					<?php $this->assign('issueSlug', $this->_tpl_vars['issue']->getId()); ?>
				<?php endif; ?>
			<?php endif; ?>

									<?php if (((is_array($_tmp=$this->_tpl_vars['issueSlug'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0 && ((is_array($_tmp=$this->_tpl_vars['issueVolume'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
								<?php echo ((is_array($_tmp=$this->_plugins['function']['native_url'][0][0]->smartyNativeUrl(array('page' => 'issue','volume' => $this->_tpl_vars['issueVolume'],'slug' => $this->_tpl_vars['issueSlug'],'showToc' => true), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

			<?php elseif (((is_array($_tmp=$this->_tpl_vars['issueVolume'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
								<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'view','path' => $this->_tpl_vars['issueVolume']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

			<?php else: ?>
								<?php $this->assign('issueYear', $this->_tpl_vars['issue']->getYear()); ?>
				<?php if (((is_array($_tmp=$this->_tpl_vars['issueYear'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
					<?php $this->assign('issueYear', ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
				<?php endif; ?>
				<?php if (((is_array($_tmp=$this->_tpl_vars['issueYear'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
					<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'year','path' => $this->_tpl_vars['issueYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

				<?php else: ?>
					<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'displayArchive'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

				<?php endif; ?>
			<?php endif; ?>

		<?php else: ?>
						<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'current','path' => 'showToc'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

		<?php endif; ?>

		<h2 class="headline-4241089976"><a href="<?php echo $this->_tpl_vars['currentUrl']; ?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['issue']->getNumber()): ?> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>, <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%B %Y") : smarty_modifier_date_format($_tmp, "%B %Y")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></h2>
	<?php else: ?>
		<h2 class="headline-4241089976"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['issue']->getNumber()): ?> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.issue"), $this);?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>, <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%B %Y") : smarty_modifier_date_format($_tmp, "%B %Y")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h2>
	<?php endif; ?>

    	<nav class="u-hide-print" data-track-component="issue navigation" aria-label="issue navigation" role="navigation">
    	    <span class="c-pagination app-pagination-borderless">

			
			<?php if ($this->_tpl_vars['isVolumeAsIssue']): ?>

			
				<?php if ($this->_tpl_vars['prevVolumeId']): ?>
				<span class="c-pagination__item">
				    <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'view','path' => $this->_tpl_vars['prevVolumeId']), $this);?>
" class="c-pagination__link" data-track="click" data-track-action="previous volume" data-track-label="link">
				        <svg width="16" height="16" focusable="false" role="img" aria-hidden="true" class="u-icon" viewBox="0 0 16 16"><path d="M5.278 2.308a1 1 0 0 1 1.414-.03l4.819 4.619a1.491 1.491 0 0 1 .019 2.188l-4.838 4.637a1 1 0 1 1-1.384-1.444L9.771 8 5.308 3.722a1 1 0 0 1-.111-1.318l.081-.096Z" transform="rotate(180 8 8)"></path></svg>
				        <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.prevVolume"), $this);?>

				    </a>
				</span>
				<?php endif; ?>

				<span class="c-pagination__item">
				    <a class="c-pagination__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'view','path' => $this->_tpl_vars['volumeId']), $this);?>
" data-track="click" data-track-action="view volume" data-track-label="link">
				        <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

				        <h2 class="kicker u-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.vol"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 (<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>
)</h2>
				    </a>
				</span>

				<?php if ($this->_tpl_vars['nextVolumeId']): ?>
				<span class="c-pagination__item">
				    <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'view','path' => $this->_tpl_vars['nextVolumeId']), $this);?>
" class="c-pagination__link" data-track="click" data-track-action="next volume" data-track-label="link">
				        <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.nextVolume"), $this);?>

				        <svg width="16" height="16" focusable="false" role="img" aria-hidden="true" class="u-icon" viewBox="0 0 16 16"><path d="M5.278 2.308a1 1 0 0 1 1.414-.03l4.819 4.619a1.491 1.491 0 0 1 .019 2.188l-4.838 4.637a1 1 0 1 1-1.384-1.444L9.771 8 5.308 3.722a1 1 0 0 1-.111-1.318l.081-.096Z"></path></svg>
				    </a>
				</span>
				<?php endif; ?>

			<?php else: ?>

			
								<?php if ($this->_tpl_vars['prevIssue']): ?>
				<span class="c-pagination__item">

										<?php $this->assign('prevIssueVolume', $this->_tpl_vars['prevIssue']->getVolume()); ?>
					<?php $this->assign('prevIssueNum', $this->_tpl_vars['prevIssue']->getNumber()); ?>
					<?php if (((is_array($_tmp=$this->_tpl_vars['prevIssueNum'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
						<?php $this->assign('prevIssueSlug', ""); ?>
					<?php else: ?>
						<?php $this->assign('prevIssueSlug', ((is_array($_tmp=$this->_tpl_vars['prevIssueNum'])) ? $this->_run_mod_handler('slugify', true, $_tmp) : PKPString::slugify($_tmp))); ?>
						<?php if (((is_array($_tmp=$this->_tpl_vars['prevIssueSlug'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
							<?php $this->assign('prevIssueSlug', $this->_tpl_vars['prevIssue']->getId()); ?>
						<?php endif; ?>
					<?php endif; ?>

										<?php if (((is_array($_tmp=$this->_tpl_vars['prevIssueSlug'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0 && ((is_array($_tmp=$this->_tpl_vars['prevIssueVolume'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
						<?php echo ((is_array($_tmp=$this->_plugins['function']['native_url'][0][0]->smartyNativeUrl(array('page' => 'issue','volume' => $this->_tpl_vars['prevIssueVolume'],'slug' => $this->_tpl_vars['prevIssueSlug']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'prevIssueUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'prevIssueUrl'));?>

					<?php elseif (((is_array($_tmp=$this->_tpl_vars['prevIssueVolume'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
						<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'view','path' => $this->_tpl_vars['prevIssueVolume']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'prevIssueUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'prevIssueUrl'));?>

					<?php else: ?>
						<?php $this->assign('prevIssueYear', $this->_tpl_vars['prevIssue']->getYear()); ?>
						<?php if (((is_array($_tmp=$this->_tpl_vars['prevIssueYear'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
							<?php $this->assign('prevIssueYear', ((is_array($_tmp=$this->_tpl_vars['prevIssue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
						<?php endif; ?>
						<?php if (((is_array($_tmp=$this->_tpl_vars['prevIssueYear'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
							<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'year','path' => $this->_tpl_vars['prevIssueYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'prevIssueUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'prevIssueUrl'));?>

						<?php else: ?>
							<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'displayArchive'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'prevIssueUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'prevIssueUrl'));?>

						<?php endif; ?>
					<?php endif; ?>

					<a href="<?php echo $this->_tpl_vars['prevIssueUrl']; ?>
" class="c-pagination__link" data-track="click" data-track-action="previous link" data-track-label="link">
					    <svg width="16" height="16" focusable="false" role="img" aria-hidden="true" class="u-icon" viewBox="0 0 16 16"><path d="M5.278 2.308a1 1 0 0 1 1.414-.03l4.819 4.619a1.491 1.491 0 0 1 .019 2.188l-4.838 4.637a1 1 0 1 1-1.384-1.444L9.771 8 5.308 3.722a1 1 0 0 1-.111-1.318l.081-.096Z" transform="rotate(180 8 8)"></path></svg>
					    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.prevIssue"), $this);?>

					</a>
				</span>
				<?php endif; ?>

								<span class="c-pagination__item">
				    <a class="c-pagination__link" href="<?php echo $this->_plugins['function']['native_url'][0][0]->smartyNativeUrl(array('page' => 'volume','volume' => $this->_tpl_vars['issue']->getVolume()), $this);?>
" data-track="click" data-track-action="view volume" data-track-label="link">
				        <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

				        <h2 class="kicker u-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.vol"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['issue']->getNumber() == '0'): ?> Sup<?php elseif (((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/[^A-Za-z]/", "") : smarty_modifier_regex_replace($_tmp, "/[^A-Za-z]/", "")) == $this->_tpl_vars['issue']->getNumber()): ?> <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 3, ".") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 3, ".")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.no"), $this);?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> (<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>
)</h2>
				    </a>
				</span>

								<?php if ($this->_tpl_vars['nextIssue']): ?>
				<span class="c-pagination__item">

										<?php $this->assign('nextIssueVolume', $this->_tpl_vars['nextIssue']->getVolume()); ?>
					<?php $this->assign('nextIssueNum', $this->_tpl_vars['nextIssue']->getNumber()); ?>
					<?php if (((is_array($_tmp=$this->_tpl_vars['nextIssueNum'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
						<?php $this->assign('nextIssueSlug', ""); ?>
					<?php else: ?>
						<?php $this->assign('nextIssueSlug', ((is_array($_tmp=$this->_tpl_vars['nextIssueNum'])) ? $this->_run_mod_handler('slugify', true, $_tmp) : PKPString::slugify($_tmp))); ?>
						<?php if (((is_array($_tmp=$this->_tpl_vars['nextIssueSlug'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
							<?php $this->assign('nextIssueSlug', $this->_tpl_vars['nextIssue']->getId()); ?>
						<?php endif; ?>
					<?php endif; ?>

										<?php if (((is_array($_tmp=$this->_tpl_vars['nextIssueSlug'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0 && ((is_array($_tmp=$this->_tpl_vars['nextIssueVolume'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
						<?php echo ((is_array($_tmp=$this->_plugins['function']['native_url'][0][0]->smartyNativeUrl(array('page' => 'issue','volume' => $this->_tpl_vars['nextIssueVolume'],'slug' => $this->_tpl_vars['nextIssueSlug']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'nextIssueUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'nextIssueUrl'));?>

					<?php elseif (((is_array($_tmp=$this->_tpl_vars['nextIssueVolume'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
						<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'view','path' => $this->_tpl_vars['nextIssueVolume']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'nextIssueUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'nextIssueUrl'));?>

					<?php else: ?>
						<?php $this->assign('nextIssueYear', $this->_tpl_vars['nextIssue']->getYear()); ?>
						<?php if (((is_array($_tmp=$this->_tpl_vars['nextIssueYear'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
							<?php $this->assign('nextIssueYear', ((is_array($_tmp=$this->_tpl_vars['nextIssue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
						<?php endif; ?>
						<?php if (((is_array($_tmp=$this->_tpl_vars['nextIssueYear'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
							<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'year','path' => $this->_tpl_vars['nextIssueYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'nextIssueUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'nextIssueUrl'));?>

						<?php else: ?>
							<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'displayArchive'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'nextIssueUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'nextIssueUrl'));?>

						<?php endif; ?>
					<?php endif; ?>

					<a href="<?php echo $this->_tpl_vars['nextIssueUrl']; ?>
" class="c-pagination__link" data-track="click" data-track-action="next link" data-track-label="link">
					    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.nextIssue"), $this);?>

					    <svg width="16" height="16" focusable="false" role="img" aria-hidden="true" class="u-icon" viewBox="0 0 16 16"><path d="M5.278 2.308a1 1 0 0 1 1.414-.03l4.819 4.619a1.491 1.491 0 0 1 .019 2.188l-4.838 4.637a1 1 0 1 1-1.384-1.444L9.771 8 5.308 3.722a1 1 0 0 1-.111-1.318l.081-.096Z"></path></svg>
					</a>
				</span>
				<?php endif; ?>

			<?php endif; ?>
            </span>
    	</nav>
	</section>
	
	<section class="l-with-sidebar" style="--with-sidebar--gap:0;--with-sidebar--min:58%">
		<div class="app-volumes-cover" data-test="issue-cover-container">
		    <div class="app-volumes-cover__copy" data-test="issue-description">
		        <div class="app-volumes-description">
		            <?php if ($this->_tpl_vars['issue']->getLocalizedTitle($this->_tpl_vars['currentJournal'])): ?>
		            <h2 class="u-h3-title"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedTitle($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h2><?php endif; ?>
		            <?php if ($this->_tpl_vars['issue']->getLocalizedDescription()): ?>
		            <p data-promo-text="" data-promo-text-threshold="560"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedDescription())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p><?php endif; ?>
		            <?php if ($this->_tpl_vars['issue'] && ! $this->_tpl_vars['showToc']): ?>
		            <p data-promo-text="ShowToc" data-promo-text-threshold="560">This issue may be a special issue, so it was deemed necessary to first display the cover issue before viewing the table of contents. To see the list of articles in this issue, please click <a href="<?php echo $this->_tpl_vars['currentUrl']; ?>
">HERE</a> or on the Table of contents.</p>
		            <?php endif; ?>
		            <?php if ($this->_tpl_vars['issue']->getLocalizedCoverPageDescription()): ?>
		            <p class="app-volumes-cover__image-copy-text" data-test="issue-image-credit" data-credit="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedCoverPageDescription())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedCoverPageDescription())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
		            <?php endif; ?>
		        </div>
		        <div class="app-volumes-contents">
                    <?php if ($this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] && $this->_tpl_vars['showToc']): ?>
                    <section id="accessKey" class="access-key block">
                        <div class="access">
                            <span class="open">
                                <img class="lazyload u-hide" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.accessLogoOpen.altText"), $this);?>
" />
                                <span aria-hidden="false" aria-label="Open Access" data-color="gold" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">OA</span></span>
                                <span class="open-access" data-color="gold"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "reader.openAccess"), $this);?>
</span>
                            </span>&nbsp;|&nbsp;
                            <span class="subscribe">
                                <img class="lazyload u-text-top" src="//www.stipwunaraha.ac.id/static/images/classical/lock.png" alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.accessLogoRestricted.altText"), $this);?>
" height="17" />
                                <span aria-hidden="false" aria-label="Open Access" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig" data-color="silver"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">S</span></span>
                        		<span class="subscribe-access" data-color="silver">
                        		<?php if ($this->_tpl_vars['purchaseArticleEnabled']): ?>
                        			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "reader.subscriptionOrFeeAccess"), $this);?>

                        		<?php else: ?>
                        			<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "reader.subscriptionAccess"), $this);?>

                        		<?php endif; ?>
                        		</span>
                        	</span>
                    	</div>
                    </section>
                    <?php endif; ?>
		            
                    <?php $_from = $this->_tpl_vars['pubIdPlugins']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['pubIdPlugin']):
?>
                    <?php if ($this->_tpl_vars['issue']->getPublished()): ?>
                    	<?php $this->assign('pubId', $this->_tpl_vars['pubIdPlugin']->getPubId($this->_tpl_vars['issue'])); ?>
                    <?php else: ?>
                    	<?php $this->assign('pubId', $this->_tpl_vars['pubIdPlugin']->getPubId($this->_tpl_vars['issue'],true)); ?>                    <?php endif; ?>
                    <?php if ($this->_tpl_vars['pubId']): ?>
                    <p class="app-volumes-doi-link u-mb-0"><?php echo ((is_array($_tmp=$this->_tpl_vars['pubIdPlugin']->getPubIdDisplayType())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
: <?php if (((is_array($_tmp=$this->_tpl_vars['pubIdPlugin']->getResolvingURL($this->_tpl_vars['currentJournal']->getId(),$this->_tpl_vars['pubId']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?><a id="pub-id::<?php echo ((is_array($_tmp=$this->_tpl_vars['pubIdPlugin']->getPubIdType())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['pubIdPlugin']->getResolvingURL($this->_tpl_vars['currentJournal']->getId(),$this->_tpl_vars['pubId']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['pubIdPlugin']->getResolvingURL($this->_tpl_vars['currentJournal']->getId(),$this->_tpl_vars['pubId']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['pubId'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></p>
                    <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?>
		        </div>
		    </div>
			<?php if ($this->_tpl_vars['issue']->getLocalizedFileName() && $this->_tpl_vars['issue']->getShowCoverPage($this->_tpl_vars['locale']) && ! $this->_tpl_vars['issue']->getHideCoverPageArchives($this->_tpl_vars['locale'])): ?>
		    <div class="app-volumes-cover__image">
				<img class="lazyload" loading="lazy" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getFileName($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" data-src="<?php echo ((is_array($_tmp=$this->_tpl_vars['coverPagePath'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getFileName($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']) != ''): ?>title="Cover issue <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?>title="Cover issue <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.coverPage.altText"), $this);?>
"<?php endif; ?> <?php if ($this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']) != ''): ?> alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getCoverPageAltText($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?> alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.coverPage.altText"), $this);?>
"<?php endif; ?> data-test="issue-cover-image" />
				<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Article::Article::ArticleCoverImage"), $this);?>
		        
		        <p class="u-mt-16 u-mb-0"><a class="u-button u-button--primary u-button--full-width" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'subscriptions'), $this);?>
" data-test="issue-page-subscribe-button" data-track="click" data-track-action="subscribe" data-track-category="sangia-issue-page" data-track-label="button">Subscribe</a>
		        </p>
		    </div>
		    <?php endif; ?>
		</div>

		<div class="l-with-sidebar__sidebar" style="--with-sidebar--basis: 385px;" data-test="issue-toc-container">
		    <aside class="u-full-height app-toc u-pa-16" aria-label="Issue navigation"><div data-container-type="issue-reading-companion">
		      <div class="clear cleared" data-component="reading-companion-placeholder">
		          <div data-component="reading-companion-sticky">
		              <nav id="toc">
		                  <div data-component="reading-companion-sections">

		                  		                  <?php if ($this->_tpl_vars['issue'] && ! $this->_tpl_vars['showToc']): ?>

		                  	  		                  	  		                  	  <?php if (! isset ( $this->_tpl_vars['issueVolume'] )): ?>
		                  	  	  <?php $this->assign('issueVolume', $this->_tpl_vars['issue']->getVolume()); ?>
		                  	  <?php endif; ?>
		                  	  <?php if (! isset ( $this->_tpl_vars['issueSlug'] )): ?>
		                  	  	  <?php $this->assign('issueNum', $this->_tpl_vars['issue']->getNumber()); ?>
		                  	  	  <?php if (((is_array($_tmp=$this->_tpl_vars['issueNum'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
		                  	  	  	  <?php $this->assign('issueSlug', ""); ?>
		                  	  	  <?php else: ?>
		                  	  	  	  <?php $this->assign('issueSlug', ((is_array($_tmp=$this->_tpl_vars['issueNum'])) ? $this->_run_mod_handler('slugify', true, $_tmp) : PKPString::slugify($_tmp))); ?>
		                  	  	  	  <?php if (((is_array($_tmp=$this->_tpl_vars['issueSlug'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
		                  	  	  	  	  <?php $this->assign('issueSlug', $this->_tpl_vars['issue']->getId()); ?>
		                  	  	  	  <?php endif; ?>
		                  	  	  <?php endif; ?>
		                  	  <?php endif; ?>

		                  	  		                      <?php if ($this->_tpl_vars['issueId']): ?>
		                          <?php if (((is_array($_tmp=$this->_tpl_vars['issueSlug'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0 && ((is_array($_tmp=$this->_tpl_vars['issueVolume'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
		                              <?php echo ((is_array($_tmp=$this->_plugins['function']['native_url'][0][0]->smartyNativeUrl(array('page' => 'issue','volume' => $this->_tpl_vars['issueVolume'],'slug' => $this->_tpl_vars['issueSlug'],'showToc' => true), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

		                          <?php elseif (((is_array($_tmp=$this->_tpl_vars['issueVolume'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
		                              <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'view','path' => $this->_tpl_vars['issueVolume']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

		                          <?php else: ?>
		                              <?php $this->assign('issueYear', $this->_tpl_vars['issue']->getYear()); ?>
		                              <?php if (((is_array($_tmp=$this->_tpl_vars['issueYear'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) == 0): ?>
		                                  <?php $this->assign('issueYear', ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y"))); ?>
		                              <?php endif; ?>
		                              <?php if (((is_array($_tmp=$this->_tpl_vars['issueYear'])) ? $this->_run_mod_handler('strlen', true, $_tmp) : strlen($_tmp)) > 0): ?>
		                                  <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'year','path' => $this->_tpl_vars['issueYear']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

		                              <?php else: ?>
		                                  <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes','op' => 'displayArchive'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

		                              <?php endif; ?>
		                          <?php endif; ?>
		                      <?php else: ?>
		                          <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'current','path' => 'showToc'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'currentUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'currentUrl'));?>

		                      <?php endif; ?>

		                      <h1 class="app-toc__title u-mb-16"><a href="<?php echo $this->_tpl_vars['currentUrl']; ?>
"><span class="content-break"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.toc"), $this);?>
</span><span class="text-gray-light altSize">(<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumArticles())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.articlesCount"), $this);?>
)</span></a></h1>
		                      <ol class="u-list-reset u-mb-0" data-component="reading-companion-section-items">
		                          <?php if ($this->_tpl_vars['issueGalleys']): ?>
		                          <li class="app-toc__item" data-section="0">
		                              <a class="u-display-block u-pt-8 u-pb-8" href="#<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.fullIssue"), $this);?>
" data-id="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.fullIssue"), $this);?>
" data-track="click"data-track-action="section anchor" data-track-category="reading companion" data-track-label="link:<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.fullIssue"), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.fullIssue"), $this);?>
</a>
		                          </li>
		                          <?php endif; ?>
		                          <?php $_from = $this->_tpl_vars['publishedArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sections'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sections']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['sectionId'] => $this->_tpl_vars['section']):
        $this->_foreach['sections']['iteration']++;
?>
		                          <?php $this->assign('sections', $this->_tpl_vars['publishedArticles']); ?>
		                          <?php if ($this->_tpl_vars['section']['title']): ?>
		                          <li class="app-toc__item" data-section="0">
		                              <a class="u-display-block u-pt-8 u-pb-8" href="#<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['section']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', "") : smarty_modifier_replace($_tmp, ' ', "")); ?>
" data-id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['section']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', "") : smarty_modifier_replace($_tmp, ' ', "")); ?>
" data-track="click"data-track-action="section anchor" data-track-category="reading companion" data-track-label="link:<?php echo ((is_array($_tmp=$this->_tpl_vars['section']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['section']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
		                          </li>
		                          <?php endif; ?>
		                          <?php endforeach; endif; unset($_from); ?>
		                      </ol>
		                      <p data-promo-text="ShowToc" data-promo-text-threshold="560">This issue may be a special issue, so it was deemed necessary to first display the cover issue before viewing the table of contents. To see the list of articles in this issue, please click <a href="<?php echo $this->_tpl_vars['currentUrl']; ?>
">HERE</a> or on the Table of contents.</p>
		                  <?php else: ?>
		                      <h1 class="app-toc__title u-mb-16"><span class="content-break"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.toc"), $this);?>
</span><span class="text-gray-light altSize">(<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumArticles())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.articlesCount"), $this);?>
)</span></h1>
		                      <ol class="u-list-reset u-mb-0" data-component="reading-companion-section-items">
		                          <?php if ($this->_tpl_vars['issueGalleys']): ?>
		                          <li class="app-toc__item" data-section="0">
		                              <a class="u-display-block u-pt-8 u-pb-8" href="#<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.fullIssue"), $this);?>
" data-id="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.fullIssue"), $this);?>
" data-track="click"data-track-action="section anchor" data-track-category="reading companion" data-track-label="link:<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.fullIssue"), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.fullIssue"), $this);?>
</a>
		                          </li>
		                          <?php endif; ?>
		                          <?php $_from = $this->_tpl_vars['publishedArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sections'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sections']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['sectionId'] => $this->_tpl_vars['section']):
        $this->_foreach['sections']['iteration']++;
?>
		                          <?php $this->assign('sections', $this->_tpl_vars['publishedArticles']); ?>
		                          <?php if ($this->_tpl_vars['section']['title']): ?>
		                          <li class="app-toc__item" data-section="0">
		                              <a class="u-display-block u-pt-8 u-pb-8" href="#<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['section']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', "") : smarty_modifier_replace($_tmp, ' ', "")); ?>
" data-id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['section']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', "") : smarty_modifier_replace($_tmp, ' ', "")); ?>
" data-track="click"data-track-action="section anchor" data-track-category="reading companion" data-track-label="link:<?php echo ((is_array($_tmp=$this->_tpl_vars['section']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['section']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
		                          </li>
		                          <?php endif; ?>
		                          <?php endforeach; endif; unset($_from); ?>
		                      </ol>
		                  <?php endif; ?>
		                  </div>
		              </nav>
		          </div>
		      </div>
		    </div>
		    </aside>
		</div>
	</section>
</main>

<div id="volumes-contents" issueid="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
" class="issue-contents" data-component="article-container">
		
<?php else: ?>

<div class="container cleared container-type-title" data-container-type="title" >
    <div class="content mb20 mt20 mq1200-padded">
    	<h1 class="content main-heading"><?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
</h1>
    	<?php if ($this->_tpl_vars['pageSubtitle'] && ! $this->_tpl_vars['pageSubtitleTranslated']): ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageSubtitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageSubtitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageSubtitleTranslated'));?>
<?php endif; ?>
    	<?php if ($this->_tpl_vars['pageSubtitleTranslated']): ?><h3 class="content sub-heading"><?php echo $this->_tpl_vars['pageSubtitleTranslated']; ?>
</h3><?php endif; ?>
    </div>
</div>
<div class="column medium-12 cleared container-type-volume-grid" data-container-type="volume-grid" data-track-component="volume grid">

<?php endif; ?>