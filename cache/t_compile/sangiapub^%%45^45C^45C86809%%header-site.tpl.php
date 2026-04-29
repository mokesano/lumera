<?php /* Smarty version 2.6.26, created on 2026-04-04 05:37:59
         compiled from common/header-parts/header-site.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'substr', 'common/header-parts/header-site.tpl', 2, false),array('modifier', 'assign', 'common/header-parts/header-site.tpl', 13, false),array('modifier', 'escape', 'common/header-parts/header-site.tpl', 27, false),array('modifier', 'nl2br', 'common/header-parts/header-site.tpl', 32, false),array('modifier', 'metric_number', 'common/header-parts/header-site.tpl', 192, false),array('modifier', 'metric_suffix', 'common/header-parts/header-site.tpl', 193, false),array('function', 'translate', 'common/header-parts/header-site.tpl', 13, false),array('function', 'call_hook', 'common/header-parts/header-site.tpl', 51, false),array('function', 'url', 'common/header-parts/header-site.tpl', 92, false),)), $this); ?>
<!DOCTYPE html>
<html lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentLocale'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)); ?>
" xml:lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentLocale'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)); ?>
">
<?php echo ''; ?><?php if (! $this->_tpl_vars['pageTitleTranslated']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageTitleTranslated'));?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['pageCrumbTitle']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageCrumbTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageCrumbTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageCrumbTitleTranslated'));?><?php echo ''; ?><?php elseif (! $this->_tpl_vars['pageCrumbTitleTranslated']): ?><?php echo ''; ?><?php $this->assign('pageCrumbTitleTranslated', $this->_tpl_vars['pageTitleTranslated']); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.scholarWizdam"), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'applicationName') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'applicationName'));?><?php echo ''; ?><?php $this->assign('applicationName', 'Frontedge'); ?><?php echo ''; ?><?php $this->assign('VersionFork', "1.0.0.0"); ?><?php echo ''; ?>

<head>
	<title><?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
 | Frontedge</title>
	
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
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultCharset'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" />
	<meta name="generator" content="<?php echo $this->_tpl_vars['applicationName']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['currentVersionString'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 fork <?php echo ((is_array($_tmp=$this->_tpl_vars['VersionFork'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
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
	
	<style>
	    <?php echo '
	    .c-header {
	        margin-bottom: 0;
	    }
	    .c-header__container {
	        max-width: 1300px;
	    }
	    '; ?>

	</style>

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

<body id="sangia.org" class="cms cms-sangia">
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

<div id="JOUR" class="page-wrapper">
<div id="homepage" class="content">
<div class="layout-full-grid">
    <div class="col-main" role="main">
<div class="cms-banner-full cms-highlight-100" <?php if ($this->_tpl_vars['displayPageHeaderTitle'] && is_array ( $this->_tpl_vars['displayPageHeaderTitle'] )): ?>style="background-color: #ffffff; background-image: url('<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderTitle']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
');"<?php else: ?>style="background-color:#555;"<?php endif; ?>>
    <div class="u-row row">
        <div class="cms-tile-row columns small-12 ">
            <div class="row">
                <div class="columns small-12 medium-7">
                    <div class="cms-banner-text">
                        <div class="cms-banner-text-inner">
                            <h1>
                                <?php if ($this->_tpl_vars['displayPageHeaderTitle'] && is_array ( $this->_tpl_vars['displayPageHeaderTitle'] )): ?>
                                <?php if ($this->_tpl_vars['displayPageHeaderTitleAltText'] != ''): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderTitleAltText'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.pageHeader.altText"), $this);?>
<?php endif; ?>
                                <?php elseif ($this->_tpl_vars['displayPageHeaderTitle']): ?>
                                	<?php echo $this->_tpl_vars['displayPageHeaderTitle']; ?>

                                <?php elseif ($this->_tpl_vars['alternatePageHeader']): ?>
                                	<?php echo $this->_tpl_vars['alternatePageHeader']; ?>

                                <?php elseif ($this->_tpl_vars['siteTitle']): ?>
                                	<?php echo $this->_tpl_vars['siteTitle']; ?>

                                <?php else: ?>
                                	<?php echo $this->_tpl_vars['applicationName']; ?>

                                <?php endif; ?>
                            </h1>
                            <?php if ($this->_tpl_vars['intro']): ?>
                            <div>
                                <p><?php echo ((is_array($_tmp=$this->_tpl_vars['intro'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="columns medium-4 end">
                    <div class="cms-banner-image"></div>
                </div>
            </div>
        </div>
    </div>
    <!---- Publisher Statistics ---->
    <div class="MainHeader__actions">
        <div class="MainHeader__actionsWrapper">
            <ul class="Metrics">
                <li class="Metrics__numbers__item">
                    <div>
                        <div class="Metrics__numbers__line"></div>
                        <div class="Metrics__numbers__mask">
                            <p class="Metrics__numbers__main"><!---->
                                <span class="Metrics__numbers__number"><?php echo ((is_array($_tmp=$this->_tpl_vars['allTotalAuthors'])) ? $this->_run_mod_handler('metric_number', true, $_tmp) : $this->_plugins['modifier']['metric_number'][0][0]->smartyMetricNumber($_tmp)); ?>
</span>
                                <span class="Metrics__numbers__suffix"><?php echo ((is_array($_tmp=$this->_tpl_vars['allTotalAuthors'])) ? $this->_run_mod_handler('metric_suffix', true, $_tmp) : $this->_plugins['modifier']['metric_suffix'][0][0]->smartyMetricSuffix($_tmp)); ?>
</span>
                            </p>
                            <p class="Metrics__numbers__text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.metrics.researchers"), $this);?>
</p>
                        </div>
                    </div>
                </li>
                <li class="Metrics__numbers__item">
                    <div>
                        <div class="Metrics__numbers__line"></div>
                        <div class="Metrics__numbers__mask">
                            <p class="Metrics__numbers__main"><!-- For Citation <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.metrics.citations"), $this);?>
 -->
                                <span class="Metrics__numbers__number"><?php echo ((is_array($_tmp=$this->_tpl_vars['allTotalViews'])) ? $this->_run_mod_handler('metric_number', true, $_tmp) : $this->_plugins['modifier']['metric_number'][0][0]->smartyMetricNumber($_tmp)); ?>
</span>
                                <span class="Metrics__numbers__suffix"><?php echo ((is_array($_tmp=$this->_tpl_vars['allTotalViews'])) ? $this->_run_mod_handler('metric_suffix', true, $_tmp) : $this->_plugins['modifier']['metric_suffix'][0][0]->smartyMetricSuffix($_tmp)); ?>
</span>
                            </p>
                            <p class="Metrics__numbers__text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.metrics.views"), $this);?>
</p>
                        </div>
                    </div>
                </li>
                <li class="Metrics__numbers__item">
                    <div>
                        <div class="Metrics__numbers__line"></div>
                        <div class="Metrics__numbers__mask">
                            <p class="Metrics__numbers__main"><!-- -->
                                <span class="Metrics__numbers__number"><?php echo ((is_array($_tmp=$this->_tpl_vars['allTotalDownloads'])) ? $this->_run_mod_handler('metric_number', true, $_tmp) : $this->_plugins['modifier']['metric_number'][0][0]->smartyMetricNumber($_tmp)); ?>
</span>
                                <span class="Metrics__numbers__suffix"><?php echo ((is_array($_tmp=$this->_tpl_vars['allTotalDownloads'])) ? $this->_run_mod_handler('metric_suffix', true, $_tmp) : $this->_plugins['modifier']['metric_suffix'][0][0]->smartyMetricSuffix($_tmp)); ?>
</span>
                            </p>
                            <p class="Metrics__numbers__text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.metrics.downloads"), $this);?>
</p>
                        </div>
                    </div>
                </li>
                <li class="Metrics__numbers__item">
                    <div>
                        <div class="Metrics__numbers__line"></div>
                        <div class="Metrics__numbers__mask">
                            <p class="Metrics__numbers__main"><!---->
                                <span class="Metrics__numbers__number"><?php echo ((is_array($_tmp=$this->_tpl_vars['allTotalInteractions'])) ? $this->_run_mod_handler('metric_number', true, $_tmp) : $this->_plugins['modifier']['metric_number'][0][0]->smartyMetricNumber($_tmp)); ?>
</span>
                                <span class="Metrics__numbers__suffix"><?php echo ((is_array($_tmp=$this->_tpl_vars['allTotalInteractions'])) ? $this->_run_mod_handler('metric_suffix', true, $_tmp) : $this->_plugins['modifier']['metric_suffix'][0][0]->smartyMetricSuffix($_tmp)); ?>
</span>
                            </p>
                            <p class="Metrics__numbers__text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.metrics.viewsDownload"), $this);?>
</p>
                        </div>
                    </div>
                </li>
            </ul><!---->
        </div>
    </div>
</div>

<div class="cms-container cms-highlight-0">
<div class="u-row row">
<div class="columns small-12 ">
    <div class="cms-columns-row">
        <div class="row">
        <div class="columns cms-tile-row-medium small-12 medium-8">
            <div class="cms-container cms-highlight-0">
                <div class="cms-common cms-article default-table">
                    <p class="taxonomy"></p>
                    <h1 class="u-hide u-js-hide"><?php echo $this->_tpl_vars['pageTitleTranslated']; ?>
</h1>
                    <?php if ($this->_tpl_vars['pageSubtitle'] && ! $this->_tpl_vars['pageSubtitleTranslated']): ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageSubtitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageSubtitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageSubtitleTranslated'));?>
<?php endif; ?>
                    <?php if ($this->_tpl_vars['pageSubtitleTranslated']): ?>
                    <h2><?php echo $this->_tpl_vars['pageSubtitleTranslated']; ?>
</h2>
                    <?php endif; ?>
                    <div class="cms-richtext">
                    <?php if (! empty ( $this->_tpl_vars['about'] )): ?>
                        <p class="intro--paragraph"><?php echo ((is_array($_tmp=$this->_tpl_vars['about'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
                    <?php elseif ($this->_tpl_vars['intro']): ?>
                        <p class="intro--paragraph"><?php echo ((is_array($_tmp=$this->_tpl_vars['intro'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="columns cms-tile-row-medium small-12 medium-4">
            <div id="id8" class="cms-container cms-highlight-0">
                <div class="cms-multicolumn-links">
                    <h2 id="c10065960">More information</h2>
                    <div class="row">
                        <div class="columns small-12 medium-12">
                            <ul>
                                <?php if ($this->_tpl_vars['sitePrincipalContactEmail']): ?>
                                <li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => 'index','page' => 'about','op' => 'contact'), $this);?>
" target="_self"><span class="link">Contact Us</span><span></span></a></li>
                                <?php endif; ?>
                                <li><a href="#c15046410"><span class="link">Journals A-Z</span><span></span></a></li>
                                <li class="u-hide"><a href="javascript(0)"><span class="link">Subscribe</span><span></span></a></li>
                                <li><a href="javascript(0)"><span class="link">Why publish with us?</span><span></span></a></li>
                            </ul>
                        </div>
                    </div>
                            
                <?php if ($this->_tpl_vars['leftSidebarCode'] || $this->_tpl_vars['rightSidebarCode']): ?>
                	<div class="columns  small-12 medium-4 u-hide" style="float: right;">
                		<?php if ($this->_tpl_vars['leftSidebarCode']): ?>
                			<div class="slide" role="complementary">
                				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/submit.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                				<?php echo $this->_tpl_vars['leftSidebarCode']; ?>

                			</div>
                		<?php endif; ?>
                		<?php if ($this->_tpl_vars['rightSidebarCode']): ?>
                			<div class="slide" role="complementary">
                				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/submit.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                				<?php echo $this->_tpl_vars['rightSidebarCode']; ?>

                			</div>
                		<?php endif; ?>
                	</div>
                <?php endif; ?>                                    
                </div>
            </div>
        </div>        
        </div>
    </div>
</div>
</div>
</div>

<div class="cms-container cms-highlight-0">
    <div class="u-row row">
        <div class="columns small-12">
        