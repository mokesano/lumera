<?php /* Smarty version 2.6.26, created on 2026-04-04 05:37:59
         compiled from common/navbar.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'common/navbar.tpl', 17, false),array('function', 'translate', 'common/navbar.tpl', 19, false),array('function', 'call_hook', 'common/navbar.tpl', 148, false),array('modifier', 'escape', 'common/navbar.tpl', 19, false),array('modifier', 'strip_tags', 'common/navbar.tpl', 29, false),array('modifier', 'lower', 'common/navbar.tpl', 34, false),array('modifier', 'substr', 'common/navbar.tpl', 68, false),array('modifier', 'string_format', 'common/navbar.tpl', 116, false),array('modifier', 'date_format', 'common/navbar.tpl', 140, false),)), $this); ?>

<div class="c-header__row c-header__row--flush">
    <div class="c-header__container">
        <div class="c-header__split">
            <h1 class="c-header__logo-container u-mb-0">
            <?php if ($this->_tpl_vars['displayPageHeaderLogo'] && is_array ( $this->_tpl_vars['displayPageHeaderLogo'] )): ?>
                <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['currentJournal']->getPath()), $this);?>
" data-track="click" data-track-action="home" data-track-label="image">
                    <picture class="c-header__logo" loading="lazy">
                        <source loading="lazy" srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderLogo']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" <?php if ($this->_tpl_vars['displayPageHeaderLogoAltText'] != ''): ?>alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderLogoAltText'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?>alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.pageHeaderLogo.altText"), $this);?>
"<?php endif; ?> width="auto">
                        <img loading="lazy" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderLogo']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" <?php if ($this->_tpl_vars['displayPageHeaderLogoAltText'] != ''): ?>alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderLogoAltText'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?>alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.pageHeaderLogo.altText"), $this);?>
"<?php endif; ?> width="auto">
                    </picture>
                </a>                    
            <?php else: ?>
                <a <?php if ($this->_tpl_vars['currentJournal']): ?>href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['currentJournal']->getPath()), $this);?>
"<?php else: ?>href="<?php echo $this->_tpl_vars['baseUrl']; ?>
"<?php endif; ?> data-track="click" data-track-action="home" data-track-label="image">
                    <picture class="c-header__logo" loading="lazy">
                        <?php if ($this->_tpl_vars['currentJournal']): ?>
                            <?php if ($this->_tpl_vars['currentJournal']->getSetting('initials') == 'Sangia'): ?>
                            <source loading="lazy" srcset="//assets.sangia.org/img/sangia-black-branded-v1.svg" alt="sangia" width="auto">
                            <img loading="lazy" src="//assets.sangia.org/img/sangia-black-branded-v1.svg" alt="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedInitials())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" width="auto">
                            <?php elseif (! $this->_tpl_vars['currentJournal']->getSetting('initials')): ?>
                            <source loading="lazy" srcset="//assets.sangia.org/img/sangia-black-branded-v3.svg" alt="<?php echo $this->_tpl_vars['siteTitle']; ?>
" width="auto">
                            <img loading="lazy" src="//assets.sangia.org/img/sangia-black-branded-v3.svg" alt="<?php echo $this->_tpl_vars['siteTitle']; ?>
" width="auto">
                            <?php else: ?>
                                <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedInitials())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>

                            <?php endif; ?>
                        <?php else: ?>
                            <source loading="lazy" srcset="//assets.sangia.org/img/sangia-black-branded-v3.svg" alt="<?php echo $this->_tpl_vars['siteTitle']; ?>
" width="auto">
                            <img loading="lazy" src="//assets.sangia.org/img/sangia-black-branded-v3.svg" alt="<?php echo $this->_tpl_vars['siteTitle']; ?>
" width="auto">
                        <?php endif; ?>
                    </picture>
                </a>
            <?php endif; ?>
            </h1>
            <ul class="c-header__menu c-header__menu--global">
                <li class="c-header__item c-header__item--padding c-header__item--sangia-research">
                    <?php if ($this->_tpl_vars['siteCategoriesEnabled']): ?>
                    <a class="c-header__link" href="/" data-test="siteindex-link" data-track="click" data-track-action="open sangia research index" data-track-label="link">
                        <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.otherJournals"), $this);?>
</span>
                    </a>
                    <?php endif; ?>                </li>
                <?php if (! $this->_tpl_vars['currentJournal'] || $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>
                <li class="c-header__item c-header__item--padding c-header__item--pipe">
                    <a class="c-header__link c-header__link--search" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search'), $this);?>
" data-header-expander="" data-test="search-link" data-track="click" data-track-action="open search tray" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                        <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.search"), $this);?>
</span>
                        <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
                    </a>
                    <div id="search-menu" class="c-header__dropdown c-header__dropdown--full-width has-tethered u-js-hide" role="banner" data-track-component="sangia-split-header">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/navsearch.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                    </div>
                </li>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['isUserLoggedIn']): ?>
                <li class="c-header__item c-header__item--padding c-header__item--snid-account-widget">
                    <nav class="c-account-nav" aria-labelledby="account-nav-title">
                        <a id="my-account" class="c-header__link eds-c-header__link c-account-nav__anchor" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user'), $this);?>
" data-test="login-link" data-track="click" data-track-action="my account" data-track-category="sangia-split-header" data-track-label="link" aria-expanded="true">
                            <?php if ($this->_tpl_vars['userData']): ?>
                            <span><?php if (((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)) !== ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 1) : substr($_tmp, 0, 1)); ?>
.<?php endif; ?><?php if ($this->_tpl_vars['userData']['middleName']): ?> <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['userData']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 1) : substr($_tmp, 0, 1)); ?>
.<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                            <div class="Ibar__userLogged u-ml-8">
                                <figure class="Avatar Avatar--size-32">
                                    <?php if ($this->_tpl_vars['userData']['profileImage'] && $this->_tpl_vars['userData']['profileImage']['uploadName']): ?><img src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['userData']['profileImage']['uploadName']; ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['userData']['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="Avatar__img is-inside-mask"><?php else: ?><img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/default_203.jpg?as=webp" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php endif; ?>
                                </figure>
                            </div>
                            <?php else: ?>
                            <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.myAccount"), $this);?>
</span>
                            <svg id="account-icon" role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                            <?php endif; ?>
                            <?php if ($this->_tpl_vars['unreadNotifications'] > 0): ?>
                            <span class="notification-icon" id="notification-count"><?php echo $this->_tpl_vars['unreadNotifications']; ?>
</span>
                            <?php endif; ?>
                            <svg class="chevron" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                        </a>
                        <div id="account-nav-menu" class="c-account-nav__menu c-account-nav__menu--right c-account-nav__menu--chevron-right u-js-hide">
                            <?php if ($this->_tpl_vars['userData']): ?>
                            <div class="Sangia__user__dropdown c-account-nav__menu-header">
                                <div class="Sangia__user__avatar">
                                    <figure class="Avatar Avatar--size-96">
                                    <?php if ($this->_tpl_vars['userData']['profileImage'] && $this->_tpl_vars['userData']['profileImage']['uploadName']): ?>
                                        <img class="Avatar__img is-inside-mask" src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['userData']['profileImage']['uploadName']; ?>
?as=webp" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
                                    <?php elseif ($this->_tpl_vars['userData']['gender'] == 'F'): ?>
                                        <img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/contactPersonF.png?as=webp" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php elseif ($this->_tpl_vars['userData']['gender'] == 'M'): ?><img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/contactPersonM.png?as=webp" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php else: ?><img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/default_203.jpg?as=webp" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
                                    <?php endif; ?>
                                    </figure>
                                    
                                    <?php if ($this->_tpl_vars['userData']['is_verified']): ?>
                                    <span class="verified badge" title="Your account is valid"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><circle cx="50" cy="50" fill="#1DA1F2" r="40"></circle><path d="M30 55 L45 70 L70 35" stroke="#ffffff" stroke-width="12" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><?php else: ?><span class="unverified badge" title="Your account needs to be validated"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><path d="M35 35 L65 65" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path><path d="M35 65 L65 35" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><?php endif; ?>
                                </div>

                                <?php if ($this->_tpl_vars['userData']['salutation'] || $this->_tpl_vars['userData']['suffix']): ?>
                                <div class="Sangia__user__salutation u-font-sangia-sans"><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['salutation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['userData']['suffix']): ?>— <?php echo $this->_tpl_vars['userData']['suffix']; ?>
<?php endif; ?></div>
                                <?php endif; ?>
                                <div class="Sangia__user__name"><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['userData']['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
                                <div class="Sangia__user__email"><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
                                <div id="account-nav-title" class="Sangia__user__account u-js-hide">
                                    <span class="u-mt-16 u-js-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.user.loggedInAs"), $this);?>
<br></span>
                                    <span id="logged-in-username" data-username="<?php echo ((is_array($_tmp=$this->_tpl_vars['loggedInUsername'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['loggedInUsername'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                                </div>
                            </div>
                            <?php endif; ?>
                            <ul class="c-account-nav__menu-list dashoboard user-home">
                               <li class="c-account-nav__menu-item"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user'), $this);?>
">Dashoboard</a>
                               </li>
                            </ul>
                            <ul class="c-account-nav__menu-list">
                                <?php if ($this->_tpl_vars['userSession']): ?>
                                <li class="c-account-nav__menu-item"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => "my-profile",'path' => ((is_array($_tmp=$this->_tpl_vars['userSession']->getUserId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d"))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.showMyProfile"), $this);?>
</a></li>
                                <?php endif; ?>
                                <li class="c-account-nav__menu-item u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => "update-profile"), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.editMyProfile"), $this);?>
</a></li>
                                <?php if ($this->_tpl_vars['hasOtherJournals']): ?>
                                    <?php if (! $this->_tpl_vars['showAllJournals']): ?>
                                    <li class="c-account-nav__menu-item"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => 'index','page' => 'user'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.showAllJournals"), $this);?>
</a></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($this->_tpl_vars['currentJournal']): ?>
                                    <?php if ($this->_tpl_vars['subscriptionsEnabled']): ?>
                                    <li class="c-account-nav__menu-item u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'subscriptions'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.manageMySubscriptions"), $this);?>
</a></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($this->_tpl_vars['currentJournal']): ?>
                                    <?php if ($this->_tpl_vars['acceptGiftPayments']): ?>
                                    <li class="c-account-nav__menu-item u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'gifts'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "gifts.manageMyGifts"), $this);?>
</a></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (! $this->_tpl_vars['implicitAuth']): ?>
                                <li class="c-account-nav__menu-item"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'changePassword'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.changeMyPassword"), $this);?>
</a></li>
                                <?php endif; ?>
                                <?php if ($this->_tpl_vars['currentJournal']): ?>
                                    <?php if ($this->_tpl_vars['journalPaymentsEnabled'] && $this->_tpl_vars['membershipEnabled']): ?>
                                    <?php if ($this->_tpl_vars['dateEndMembership']): ?>
                                    <li class="c-account-nav__menu-item u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'payMembership'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.membership.renewMembership"), $this);?>
</a> (<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.membership.ends"), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['dateEndMembership'])) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['dateFormatShort']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['dateFormatShort'])); ?>
)</li>
                                    <?php else: ?>
                                    <li class="c-account-nav__menu-item u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'payMembership'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "payment.membership.buyMembership"), $this);?>
</a></li>
                                    <?php endif; ?>
                                    <?php endif; ?>                                <?php endif; ?>                                <li class="c-account-nav__menu-item"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => 'signOut'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.logOut"), $this);?>
</a></li>
                                    
                                <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::User::Index::MyAccount"), $this);?>

                                <?php if ($this->_tpl_vars['userSession'] && $this->_tpl_vars['userSession']->getSessionVar('signedInAs')): ?>
                                <li class="c-account-nav__menu-item Login_user_as">
                                    <a id="logout-button" class="c-header__link placeholder" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => 'signOutAsUser'), $this);?>
" style="" data-test="logout-link" data-track="click" data-track-action="logout" data-track-category="nature-150-split-header" data-track-label="link">
                                        <span>Logout as <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 1) : substr($_tmp, 0, 1)); ?>
.<?php if (((is_array($_tmp=$this->_tpl_vars['userData']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['userData']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 1) : substr($_tmp, 0, 1)); ?>
.<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                                        <svg aria-hidden="true" focusable="false" role="img" width="22" height="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="m8.72592184 2.54588137c-.48811714-.34391207-1.08343326-.54588137-1.72592184-.54588137-1.65685425 0-3 1.34314575-3 3 0 1.02947485.5215457 1.96853646 1.3698342 2.51900785l.6301658.40892721v1.02400182l-.79002171.32905522c-1.93395773.8055207-3.20997829 2.7024791-3.20997829 4.8180274v.9009805h-1v-.9009805c0-2.5479714 1.54557359-4.79153984 3.82548288-5.7411543-1.09870406-.71297106-1.82548288-1.95054399-1.82548288-3.3578652 0-2.209139 1.790861-4 4-4 1.09079823 0 2.07961816.43662103 2.80122451 1.1446278-.37707584.09278571-.7373238.22835063-1.07530267.40125357zm-2.72592184 14.45411863h-1v-.9009805c0-2.5479714 1.54557359-4.7915398 3.82548288-5.7411543-1.09870406-.71297106-1.82548288-1.95054399-1.82548288-3.3578652 0-2.209139 1.790861-4 4-4s4 1.790861 4 4c0 1.40732121-.7267788 2.64489414-1.8254829 3.3578652 2.2799093.9496145 3.8254829 3.1931829 3.8254829 5.7411543v.9009805h-1v-.9009805c0-2.1155483-1.2760206-4.0125067-3.2099783-4.8180274l-.7900217-.3290552v-1.02400184l.6301658-.40892721c.8482885-.55047139 1.3698342-1.489533 1.3698342-2.51900785 0-1.65685425-1.3431458-3-3-3-1.65685425 0-3 1.34314575-3 3 0 1.02947485.5215457 1.96853646 1.3698342 2.51900785l.6301658.40892721v1.02400184l-.79002171.3290552c-1.93395773.8055207-3.20997829 2.7024791-3.20997829 4.8180274z" fill-rule="evenodd" fill="#ffffff"></path></svg>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </nav>
                </li>
                <?php else: ?>
                <li class="c-header__item c-header__item--padding">
                    <a id="login-button" class="c-header__link placeholder" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login'), $this);?>
" style="login" data-test="login-link" data-track="click" data-track-action="login" data-track-category="sangia-split-header" data-track-label="link">
                        <span>Login</span>
                        <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                    </a>
                </li>
                <?php if (! $this->_tpl_vars['hideRegisterLink']): ?>
                <li class="u-hide c-header__item c-header__item--padding c-header__item--pipe">
            		<a id="register-button" class="c-header__link placeholder" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'register'), $this);?>
" style="register" data-test="register-link" data-track="click" data-track-action="register" data-track-category="sangia-split-header" data-track-label="link">
            		    <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.register"), $this);?>
</span>
                        <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
            		</a>
                </li><?php endif; ?>                <?php endif; ?>            </ul>
        </div>
    </div>
</div>
    