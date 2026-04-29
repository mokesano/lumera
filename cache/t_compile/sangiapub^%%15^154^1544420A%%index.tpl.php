<?php /* Smarty version 2.6.26, created on 2026-04-04 14:30:52
         compiled from notification/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'notification/index.tpl', 19, false),array('function', 'url', 'notification/index.tpl', 25, false),array('function', 'page_info', 'notification/index.tpl', 48, false),array('function', 'page_links', 'notification/index.tpl', 49, false),array('modifier', 'escape', 'notification/index.tpl', 25, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "notification.notifications"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-reader.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<table width="100%" class="headings">
	<tr>
		<td><?php if ($this->_tpl_vars['isUserLoggedIn']): ?>
				<p class="alert-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.notificationsDescription",'unreadCount' => $this->_tpl_vars['unread'],'readCount' => $this->_tpl_vars['read'],'settingsUrl' => $this->_tpl_vars['url']), $this);?>
</p>
			<?php else: ?>
				<p class="alert-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.notificationsPublicDescription",'emailUrl' => $this->_tpl_vars['emailUrl']), $this);?>
</p>
			<?php endif; ?>
		</td>
		<td><ul class="plain rss-icons">
			<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'getNotificationFeedUrl','path' => 'rss'), $this);?>
" class="icon"><img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['baseUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
/lib/pkp/templates/images/rss10_logo.gif" alt="RSS 1.0"/></a></li>
			<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'getNotificationFeedUrl','path' => 'rss2'), $this);?>
" class="icon"><img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/lib/pkp/templates/images/rss20_logo.gif" alt="RSS 2.0"/></a></li>
			<li><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'getNotificationFeedUrl','path' => 'atom'), $this);?>
" class="icon"><img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/lib/pkp/templates/images/atom10_logo.gif" alt="Atom 1.0"/></a></li>
		</ul></td>
	</tr>
</table>

<div id="notifications" class="">
    
    <?php echo $this->_tpl_vars['formattedNotifications']; ?>

    
    <?php if ($this->_tpl_vars['notifications']->wasEmpty()): ?>
    	<table class="notifications">
    		<tr>
    			<td colspan="2" class="nodata"><h5><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "notification.noneExist"), $this);?>
</h5></td>
    		</tr>
    		<tr>
    			<td colspan="2" class="endseparator">&nbsp;</td>
    		</tr>
    	</table>
    <?php else: ?>
    	<table class="notifications u-hide">
    		<tr>
    			<td align="left"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['notifications']), $this);?>
</td>
    			<td align="right"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'notifications','name' => 'notifications','iterator' => $this->_tpl_vars['notifications']), $this);?>
</td>
    		</tr>
    	</table>
    <?php endif; ?>

</div>
<?php if (! $this->_tpl_vars['notifications']->wasEmpty()): ?>
<div class="colspan u-mb-0" id="colspan">	    
	<section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	    <div class="c-pagination"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['notifications']), $this);?>
</div>
    </section>
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'notifications','name' => 'notifications','iterator' => $this->_tpl_vars['notifications']), $this);?>

       </div>
    </section>
</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>