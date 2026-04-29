<?php /* Smarty version 2.6.26, created on 2026-04-04 05:37:59
         compiled from common/navmenu.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'common/navmenu.tpl', 29, false),array('function', 'translate', 'common/navmenu.tpl', 29, false),array('function', 'call_hook', 'common/navmenu.tpl', 104, false),array('modifier', 'assign', 'common/navmenu.tpl', 80, false),array('modifier', 'escape', 'common/navmenu.tpl', 101, false),)), $this); ?>
<div class="c-header__row">
    <div class="c-header__container" data-test="navigation-row">
        <div class="c-header__split">
            <div class="c-header__split">
                <ul class="c-header__menu c-header__menu--journal lm-nav-root">
                    <li class="c-header__item c-header__item--dropdown-menu">
                        <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                            <span><span class="c-header__show-text">Explore</span> content</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                        </a>
                        <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                            <div class="c-header-expander__container">
                                <h2 id="Explore-content" class="c-header-expander__heading u-hide">Explore content</h2>
                                <ul class="c-header-expander__list">
                                <?php if ($this->_tpl_vars['currentJournal']): ?>
                                    
                                    <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'current'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "journal.currentIssue"), $this);?>
</a></li>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'volumes'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Archive Issues</a></li>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'titles'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Titles Index</a></li>
                                        
                                                                            
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'authors'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Authors Index</a></li>
                                        
                                    <?php endif; ?>
                                        
                                <?php else: ?>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'titles'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Titles Index</a></li>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'authors'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Authors Index</a></li>
                                    
                                <?php endif; ?>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'sitemap'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.siteMap"), $this);?>
</a></li>
                                        
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only"><a class="c-header-expander__link" href="//www.facebook.com/SangiaNews" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">Follow us on Facebook</a></li>
                                        
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only"><a class="c-header-expander__link" href="https://twitter.com/SangiaNews" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">Follow us on Twitter</a></li>
                                        
                                    <?php if ($this->_tpl_vars['currentJournal']): ?>
                                    <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION || $this->_tpl_vars['donationEnabled'] || $this->_tpl_vars['currentJournal']->getSetting('membershipFee')): ?>
                                        
                                    <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?>
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'subscriptions'), $this);?>
" data-track="click" data-track-action="subscribe" data-track-label="link" data-test="menu-button-subscribe"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.subscribe"), $this);?>
</a></li>
                                    <?php endif; ?>                                        
                                    <?php if ($this->_tpl_vars['donationEnabled']): ?>
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'donations'), $this);?>
" data-track="click" data-track-action="donation" data-track-label="link" data-test="menu-button-donation"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.type.donation"), $this);?>
</a></li><?php endif; ?>
                                        
                                    <?php if ($this->_tpl_vars['currentJournal']->getSetting('membershipFeeEnabled')): ?>
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'memberships'), $this);?>
" data-track="click" data-track-action="membership" data-track-label="link" data-test="menu-button-membership"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.members"), $this);?>
</a></li><?php endif; ?>
                                        
                                    <?php endif; ?>                                        
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification','op' => 'subscribeMailList'), $this);?>
" rel="nofollow" data-track="click" data-track-action="Sign up for alerts" data-track-external="" data-track-label="link (mobile dropdown)">Sign up for alerts<svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m4 10h2.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-3.08578644l-1.12132034 1.1213203c-.18753638.1875364-.29289322.4418903-.29289322.7071068v.1715729h14v-.1715729c0-.2652165-.1053568-.5195704-.2928932-.7071068l-1.7071068-1.7071067v-3.4142136c0-2.76142375-2.2385763-5-5-5-2.76142375 0-5 2.23857625-5 5zm3 4c0 1.1045695.8954305 2 2 2s2-.8954305 2-2zm-5 0c-.55228475 0-1-.4477153-1-1v-.1715729c0-.530433.21071368-1.0391408.58578644-1.4142135l1.41421356-1.4142136v-3c0-3.3137085 2.6862915-6 6-6s6 2.6862915 6 6v3l1.4142136 1.4142136c.3750727.3750727.5857864.8837805.5857864 1.4142135v.1715729c0 .5522847-.4477153 1-1 1h-4c0 1.6568542-1.3431458 3-3 3-1.65685425 0-3-1.3431458-3-3z" fill="#fff"></path></svg></a></li>
                                        
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'gateway','op' => 'plugin'), $this);?>
/WebFeedGatewayPlugin/rss" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank"><span>RSS feed</span></a></li>
                                        
                                    <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'oai'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'oaiUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'oaiUrl'));?>

                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_tpl_vars['oaiUrl']; ?>
" data-track="click" data-track-action="OAI feed" data-track-label="link" target="_blank"><span>OAI</span></a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </nav>
                    </li>
                    <?php if ($this->_tpl_vars['currentJournal']): ?>
                    <li class="c-header__item c-header__item--dropdown-menu">
                        <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                            <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.about"), $this);?>
 <span class="c-header__show-text">the journal</span></span>
                            <svg role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                        </a>
                        <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                            <div class="c-header-expander__container">
                                <h2 id="Explore-content" class="c-header-expander__heading u-hide">About the journal</h2>
                                <ul class="c-header-expander__list">
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => "editorial-team"), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.editorialTeam"), $this);?>
</a></li>

                            <?php if ($this->_tpl_vars['membershipGroups']): ?>
                                <?php $_from = $this->_tpl_vars['membershipGroups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['peopleGroup']):
?>
                                <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'displayMembership','path' => $this->_tpl_vars['peopleGroup']['group_id']), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo ((is_array($_tmp=$this->_tpl_vars['peopleGroup']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li>
                                <?php endforeach; endif; unset($_from); ?>
                            <?php endif; ?>
                            <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::People"), $this);?>

                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.journal"), $this);?>
</a></li>
                                        
                                    <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('focusScopeDesc') != ''): ?>
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'focusAndScope'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.focusAndScope"), $this);?>
</a></li><?php endif; ?>
                                        
                                    <?php $_from = $this->_tpl_vars['navMenuItems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['navItemKey'] => $this->_tpl_vars['navItem']):
?><?php if ($this->_tpl_vars['navItem']['url'] != '' && $this->_tpl_vars['navItem']['name'] != ''): ?>
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php if ($this->_tpl_vars['navItem']['isAbsolute']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_tpl_vars['baseUrl']; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php if ($this->_tpl_vars['navItem']['isLiteral']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['navItem']['name']), $this);?>
<?php endif; ?></a></li><?php endif; ?><?php endforeach; endif; unset($_from); ?>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'sectionPolicies'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.sectionPolicies"), $this);?>
</a></li>
                                        
                                    <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::Policies"), $this);?>

                                        
                                    <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('pubFreqPolicy') != ''): ?>
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'publicationFrequency'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.publicationFrequency"), $this);?>
</a></li><?php endif; ?>
                                        
                                    <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN && $this->_tpl_vars['currentJournal']->getLocalizedSetting('openAccessPolicy') != ''): ?>
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'openAccessPolicy'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.openAccessPolicy"), $this);?>
</a></li><?php endif; ?>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'announcement'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Announcements</a></li>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'archiving'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.archiving"), $this);?>
</a></li>
                                        
                                    <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('history') != ''): ?>
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'history'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.history"), $this);?>
</a></li>
                                    <?php endif; ?>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'statistics'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.statistics"), $this);?>
</a></li>
                                        
                                    <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Common::Header::Navbar::CurrentJournal"), $this);?>

                                    <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::Other"), $this);?>

                                        
                                    <?php if (! ( $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('publisherNote') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('contributorNote') == '' && empty ( $this->_tpl_vars['journalSettings']['contributors'] ) && $this->_tpl_vars['currentJournal']->getLocalizedSetting('sponsorNote') == '' && empty ( $this->_tpl_vars['journalSettings']['sponsors'] ) )): ?>
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'journalSponsorship'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.journalSponsorship"), $this);?>
</a></li><?php endif; ?>
                                        
                                    <?php if ($this->_tpl_vars['siteCategoriesEnabled']): ?>
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="/" data-track="click" data-track-action="OAI feed" data-track-label="link"><span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.otherJournals"), $this);?>
</span></a></li>
                                    <?php endif; ?>                                        
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'contact'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact"), $this);?>
 Information</a></li>
                                </ul>
                            </div>
                        </nav>
                    </li>
                    <li class="c-header__item c-header__item--dropdown-menu u-mr-2">
                        <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                            <span>Publish <span class="c-header__show-text">with us</span></span>
                            <svg role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                        </a>
                        <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                            <div class="c-header-expander__container">
                                <h2 id="Explore-content" class="c-header-expander__heading u-hide">Publish with us</h2>
                                <ul class="c-header-expander__list">
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'authors'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForAuthors"), $this);?>
</a></li>
                                        
                                    <li class="u-hide c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Submission guidelines</a></li>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'onlineSubmissions'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.onlineSubmissions"), $this);?>
</a></li>
                                        
                                    <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('authorGuidelines') != ''): ?>
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'authorGuidelines'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.authorGuidelines"), $this);?>
</a></li><?php endif; ?>
                                        
                                    <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('copyrightNotice') != ''): ?>
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'copyrightNotice'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.copyrightNotice"), $this);?>
</a></li><?php endif; ?>
                                        
                                    <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('privacyStatement') != ''): ?>
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'privacyStatement'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.privacyStatement"), $this);?>
</a></li><?php endif; ?>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'librarians'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForLibrarians"), $this);?>
</a></li>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'readers'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForReaders"), $this);?>
</a></li>
                                        
                                    <?php if ($this->_tpl_vars['currentJournal']->getSetting('journalPaymentsEnabled') && ( $this->_tpl_vars['currentJournal']->getSetting('submissionFeeEnabled') || $this->_tpl_vars['currentJournal']->getSetting('fastTrackFeeEnabled') || $this->_tpl_vars['currentJournal']->getSetting('publicationFeeEnabled') )): ?>
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'authorFees'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.authorFees"), $this);?>
</a></li><?php endif; ?>
                                        
                                    <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::Submissions"), $this);?>

                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'contact'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact"), $this);?>
 us</a></li>
                                        
                                    <li class="c-header-expander__item c-header-expander__item--keyline"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','op' => 'submit'), $this);?>
" target="_blank" data-track="click" data-track-action="Submit manuscript" data-track-label="link" data-track-external="">Submit manuscript<svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m15 0c1.1045695 0 2 .8954305 2 2v5.5c0 .27614237-.2238576.5-.5.5s-.5-.22385763-.5-.5v-5.5c0-.51283584-.3860402-.93550716-.8833789-.99327227l-.1166211-.00672773h-9v3c0 1.1045695-.8954305 2-2 2h-3v10c0 .5128358.38604019.9355072.88337887.9932723l.11662113.0067277h7.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-7.5c-1.1045695 0-2-.8954305-2-2v-10.17157288c0-.53043297.21071368-1.0391408.58578644-1.41421356l3.82842712-3.82842712c.37507276-.37507276.88378059-.58578644 1.41421356-.58578644zm-.5442863 8.18867991 3.3545404 3.35454039c.2508994.2508994.2538696.6596433.0035959.909917-.2429543.2429542-.6561449.2462671-.9065387-.0089489l-2.2609825-2.3045251.0010427 7.2231989c0 .3569916-.2898381.6371378-.6473715.6371378-.3470771 0-.6473715-.2852563-.6473715-.6371378l-.0010428-7.2231995-2.2611222 2.3046654c-.2531661.2580415-.6562868.2592444-.9065605.0089707-.24295423-.2429542-.24865597-.6576651.0036132-.9099343l3.3546673-3.35466731c.2509089-.25090888.6612706-.25227691.9135302-.00001728zm-.9557137-3.18867991c.2761424 0 .5.22385763.5.5s-.2238576.5-.5.5h-6c-.27614237 0-.5-.22385763-.5-.5s.22385763-.5.5-.5zm-8.5-3.587-3.587 3.587h2.587c.55228475 0 1-.44771525 1-1zm8.5 1.587c.2761424 0 .5.22385763.5.5s-.2238576.5-.5.5h-6c-.27614237 0-.5-.22385763-.5-.5s.22385763-.5.5-.5z" fill="#fff"></path></svg></a></li>
                                        
                                    <?php if ($this->_tpl_vars['isUserLoggedIn']): ?>
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user'), $this);?>
" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank"><span>My Account</span></a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </nav>
                    </li>
                    <?php endif; ?>
                </ul>
                    
                <div class="c-header__menu u-ml-16 u-show-lg u-show-at-lg">
                    <div class="c-header__item c-header__item--pipe">
                    <?php if ($this->_tpl_vars['currentJournal']): ?>
                        <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?><a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'subscriptions'), $this);?>
" data-track="click" data-track-action="subscribe" data-track-label="link" data-test="menu-button-subscribe">
                            <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.subscriptions"), $this);?>
</span>
                        </a><?php endif; ?>
                        <?php if ($this->_tpl_vars['donationEnabled']): ?><a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'donations'), $this);?>
" data-track="click" data-track-action="subscribe" data-track-label="link" data-test="menu-button-subscribe">
                            <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.type.donation"), $this);?>
</span>
                        </a><?php endif; ?>

                        <?php if ($this->_tpl_vars['currentJournal']->getSetting('membershipFeeEnabled')): ?><a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'memberships'), $this);?>
" data-track="click" data-track-action="membership" data-track-label="link" data-test="menu-button-membership">
                            <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.memberships"), $this);?>
</span>
                        </a><?php endif; ?>
                    <?php else: ?>
                        <a class="c-header__link" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/index/search/categories" data-track="click" data-track-action="categories" data-track-label="link" data-test="menu-button-categories">
                            <span>Journals Subjects</span>
                        </a>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <ul class="c-header__menu c-header__menu--tools">
                <li class="c-header__item">
                    <a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification','op' => 'subscribeMailList'), $this);?>
" rel="nofollow" data-track="click" data-track-action="Sign up for alerts" data-track-label="link (desktop site header)" data-track-external="">
                        <span>Sign up for alerts</span>
                        <svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m4 10h2.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-3.08578644l-1.12132034 1.1213203c-.18753638.1875364-.29289322.4418903-.29289322.7071068v.1715729h14v-.1715729c0-.2652165-.1053568-.5195704-.2928932-.7071068l-1.7071068-1.7071067v-3.4142136c0-2.76142375-2.2385763-5-5-5-2.76142375 0-5 2.23857625-5 5zm3 4c0 1.1045695.8954305 2 2 2s2-.8954305 2-2zm-5 0c-.55228475 0-1-.4477153-1-1v-.1715729c0-.530433.21071368-1.0391408.58578644-1.4142135l1.41421356-1.4142136v-3c0-3.3137085 2.6862915-6 6-6s6 2.6862915 6 6v3l1.4142136 1.4142136c.3750727.3750727.5857864.8837805.5857864 1.4142135v.1715729c0 .5522847-.4477153 1-1 1h-4c0 1.6568542-1.3431458 3-3 3-1.65685425 0-3-1.3431458-3-3z" fill="#222"></path></svg>
                    </a>
                </li>
                <li class="c-header__item c-header__item--pipe">
                    <a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'gateway','op' => 'plugin'), $this);?>
/WebFeedGatewayPlugin/rss" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank">
                        <span>RSS feed</span>
                    </a>
                </li>
                <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'oai'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'oaiUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'oaiUrl'));?>

                <li class="c-header__item c-header__item--pipe">
                    <a class="c-header__link" href="<?php echo $this->_tpl_vars['oaiUrl']; ?>
" data-track="click" data-track-action="oai feed" data-track-label="link" target="_blank">
                        <span>OAI</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>