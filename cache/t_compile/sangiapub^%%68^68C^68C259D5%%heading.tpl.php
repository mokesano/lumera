<?php /* Smarty version 2.6.26, created on 2026-04-04 05:48:04
         compiled from article/heading.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'article/heading.tpl', 27, false),array('function', 'url', 'article/heading.tpl', 33, false),array('function', 'call_hook', 'article/heading.tpl', 122, false),array('function', 'math', 'article/heading.tpl', 897, false),array('modifier', 'escape', 'article/heading.tpl', 47, false),array('modifier', 'substr', 'article/heading.tpl', 47, false),array('modifier', 'string_format', 'article/heading.tpl', 91, false),array('modifier', 'date_format', 'article/heading.tpl', 114, false),array('modifier', 'assign', 'article/heading.tpl', 193, false),array('modifier', 'to_array', 'article/heading.tpl', 389, false),array('modifier', 'nl2br', 'article/heading.tpl', 487, false),array('modifier', 'count', 'article/heading.tpl', 587, false),array('modifier', 'regex_replace', 'article/heading.tpl', 594, false),array('modifier', 'lower', 'article/heading.tpl', 594, false),array('modifier', 'strip_unsafe_html', 'article/heading.tpl', 660, false),array('modifier', 'truncate', 'article/heading.tpl', 872, false),array('modifier', 'number_format', 'article/heading.tpl', 897, false),array('modifier', 'strip_tags', 'article/heading.tpl', 984, false),array('block', 'iterate', 'article/heading.tpl', 210, false),)), $this); ?>
<header class="c-header" style="border-color:#000">
    <div class="c-header__row c-header__row--flush">
        <div class="c-header__container">
            <div class="c-header__split">
                <h1 class="c-header__logo-container u-mb-0">
                    <a href="//www.sangia.org" data-track="click" data-track-action="home" data-track-label="image">
                        <picture class="c-header__logo" loading="lazy">
                            <source loading="lazy" srcset="//assets.sangia.org/img/sangia-black-branded-v3.svg" alt="sangia" width="auto">
                            <img class="lazyload" loading="lazy" src="//assets.sangia.org/img/sangia-black-branded-v3.svg" alt="sangia" width="auto">
                        </picture>
                    </a>
                </h1>
                <ul class="c-header__menu c-header__menu--global">
                    <li class="c-header__item c-header__item--sangia-research">
                        <?php if ($this->_tpl_vars['siteCategoriesEnabled']): ?>
                        <a class="c-header__link" href="/" data-test="siteindex-link" data-track="click" data-track-action="open sangia research index" data-track-label="link">
                            <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.otherJournals"), $this);?>
</span>
                        </a>
                        <?php endif; ?>                    </li>
                    <?php if (! $this->_tpl_vars['currentJournal'] || $this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>
                    <li class="c-header__item c-header__item--padding c-header__item--pipe">
                        <a class="c-header__link c-header__link--search" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'titles'), $this);?>
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
"><?php endif; ?>
                                        </figure>
                                        <?php if ($this->_tpl_vars['userData']['is_verified']): ?>
                                        <span class="verified badge icon" title="Your account is valid"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><circle cx="50" cy="50" fill="#1DA1F2" r="40"></circle><path d="M30 55 L45 70 L70 35" stroke="#ffffff" stroke-width="12" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><?php else: ?><span class="unverified badge icon" title="Your account needs to be validated"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><path d="M35 35 L65 65" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path><path d="M35 65 L65 35" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg></span><?php endif; ?>
                                    </div>
                                    <?php if ($this->_tpl_vars['userData']['salutation'] || $this->_tpl_vars['userData']['suffix']): ?>
                                    <div class="Sangia__user__salutation u-font-sangia-sans"><?php if ($this->_tpl_vars['userData']['salutation']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['salutation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php endif; ?><?php if ($this->_tpl_vars['userData']['suffix']): ?>— <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['suffix'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></div>
                                    <?php endif; ?>
                                    <div class="Sangia__user__name"><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['userData']['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php endif; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
                                    <div class="Sangia__user__email"><?php echo ((is_array($_tmp=$this->_tpl_vars['userData']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</div>
                                    <div id="account-nav-title" class="Sangia__user__account u-js-hide">
                                        <span class="u-js-hide"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.user.loggedInAs"), $this);?>
</span>
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
                                    <li class="c-account-nav__menu-item"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'viewPublicProfile','path' => ((is_array($_tmp=$this->_tpl_vars['userSession']->getUserId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d"))), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.showMyProfile"), $this);?>
</a></li>
                                    <li class="c-account-nav__menu-item u-hide"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'profile'), $this);?>
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
                                        <?php endif; ?>                                    <?php endif; ?>                                    <li class="c-account-nav__menu-item"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'login','op' => 'signOut'), $this);?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.logOut"), $this);?>
</a></li>
                                    
                                    <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::User::Index::MyAccount"), $this);?>

                                    <?php if ($this->_tpl_vars['userSession']->getSessionVar('signedInAs')): ?>
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
                    </li><?php endif; ?>
                    <?php endif; ?>                </ul>
            </div>
        </div>
    </div>
    <div class="u-hide c-header__row">
        <div class="c-header__container" data-test="navigation-row">
            <div class="c-header__split">
                <div class="c-header__split">
                    <ul class="c-header__menu c-header__menu--journal lm-nav-root">
                        <li class="c-header__item c-header__item--dropdown-menu">
                            <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                                <span><span class="c-header__show-text">Explore</span> content</span>
                                <svg class="details-marker" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            
                            <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                                <div class="c-header-expander__container">
                                    <h2 id="Explore-content" class="c-header-expander__heading u-hide">Explore content</h2>
                                    <ul class="c-header-expander__list">
                                        <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') != @PUBLISHING_MODE_NONE): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'current'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "journal.currentIssue"), $this);?>
</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'archive'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Archive Issues</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'titles'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Titles Index</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'browseSearch','op' => 'sections'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">View Section</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'browseSearch','op' => 'identifyTypes'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">View Article Type</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'authors'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Authors Index</a></li>
                                        <?php endif; ?>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'siteMap'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.siteMap"), $this);?>
</a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only"><a class="c-header-expander__link" href="//www.facebook.com/SangiaNews" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">Follow us on Facebook</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="https://twitter.com/SangiaNews" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">Follow us on Twitter</a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification','op' => 'subscribeMailList'), $this);?>
" rel="nofollow" data-track="click" data-track-action="Sign up for alerts" data-track-external="" data-track-label="link (mobile dropdown)">Sign up for alerts<svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m4 10h2.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-3.08578644l-1.12132034 1.1213203c-.18753638.1875364-.29289322.4418903-.29289322.7071068v.1715729h14v-.1715729c0-.2652165-.1053568-.5195704-.2928932-.7071068l-1.7071068-1.7071067v-3.4142136c0-2.76142375-2.2385763-5-5-5-2.76142375 0-5 2.23857625-5 5zm3 4c0 1.1045695.8954305 2 2 2s2-.8954305 2-2zm-5 0c-.55228475 0-1-.4477153-1-1v-.1715729c0-.530433.21071368-1.0391408.58578644-1.4142135l1.41421356-1.4142136v-3c0-3.3137085 2.6862915-6 6-6s6 2.6862915 6 6v3l1.4142136 1.4142136c.3750727.3750727.5857864.8837805.5857864 1.4142135v.1715729c0 .5522847-.4477153 1-1 1h-4c0 1.6568542-1.3431458 3-3 3-1.65685425 0-3-1.3431458-3-3z" fill="#fff"></path></svg></a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'gateway','op' => 'plugin'), $this);?>
/WebFeedGatewayPlugin/rss" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank"><span>RSS feed</span></a></li>
                                        
                                        <?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'oai'), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'oaiUrl') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'oaiUrl'));?>

                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="<?php echo $this->_tpl_vars['oaiUrl']; ?>
" data-track="click" data-track-action="OAI feed" data-track-label="link" target="_blank"><span>OAI</span></a></li>
                                    </ul>
                                </div>
                            </nav>
                        </li>
                        <li class="c-header__item c-header__item--dropdown-menu">
                            <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                                <span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.about"), $this);?>
 <span class="c-header__show-text">the journal</span></span>
                                <svg class="details-marker" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                                <div class="c-header-expander__container">
                                    <h2 id="Explore-content" class="c-header-expander__heading u-hide">About the journal</h2>
                                    <ul class="c-header-expander__list">
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialTeam'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.editorialTeam"), $this);?>
</a></li>
                                        
                                        <?php if ($this->_tpl_vars['peopleGroups']): ?><?php $this->_tag_stack[] = array('iterate', array('from' => 'peopleGroups','item' => 'peopleGroup')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'displayMembership','path' => $this->_tpl_vars['peopleGroup']->getId()), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo ((is_array($_tmp=$this->_tpl_vars['peopleGroup']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li>
                                        <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?>
                                        <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::People"), $this);?>

                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('focusScopeDesc') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'focusAndScope'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.focusAndScope"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'sectionPolicies'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.sectionPolicies"), $this);?>
</a></li>
                                        
                                        <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::Policies"), $this);?>

                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('reviewPolicy') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'peerReviewProcess'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.peerReviewProcess"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('pubFreqPolicy') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'publicationFrequency'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.publicationFrequency"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN && $this->_tpl_vars['currentJournal']->getLocalizedSetting('openAccessPolicy') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'openAccessPolicy'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.openAccessPolicy"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <?php $_from = $this->_tpl_vars['currentJournal']->getLocalizedSetting('customAboutItems'); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['customAboutItem']):
?><?php if (! empty ( $this->_tpl_vars['customAboutItem']['title'] )): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => "custom-".($this->_tpl_vars['key'])), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item" style="word-break:break-all"><?php echo ((is_array($_tmp=$this->_tpl_vars['customAboutItem']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li><?php endif; ?><?php endforeach; endif; unset($_from); ?>
                                        
                                        <?php $_from = $this->_tpl_vars['navMenuItems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['navItemKey'] => $this->_tpl_vars['navItem']):
?><?php if ($this->_tpl_vars['navItem']['url'] != '' && $this->_tpl_vars['navItem']['name'] != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php if ($this->_tpl_vars['navItem']['isAbsolute']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_tpl_vars['baseUrl']; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php if ($this->_tpl_vars['navItem']['isLiteral']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['navItem']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['navItem']['name']), $this);?>
<?php endif; ?></a></li><?php endif; ?><?php endforeach; endif; unset($_from); ?>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'archiving'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.archiving"), $this);?>
</a></li>
                                        
                                        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('history') != ''): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'history'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php if ($this->_tpl_vars['currentJournal']->getSetting('initials')): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.history"), $this);?>
 of <?php echo $this->_tpl_vars['currentJournal']->getSetting('initials',$this->_tpl_vars['currentJournal']->getPrimaryLocale()); ?>
<?php else: ?>Journal <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.history"), $this);?>
<?php endif; ?></a></li>
                                        <?php endif; ?>
                                        
                                        <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Common::Header::Navbar::CurrentJournal"), $this);?>

                                        <?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::About::Index::Other"), $this);?>

                                        
                                        <?php if (! ( $this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('publisherNote') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('contributorNote') == '' && empty ( $this->_tpl_vars['journalSettings']['contributors'] ) && $this->_tpl_vars['currentJournal']->getLocalizedSetting('sponsorNote') == '' && empty ( $this->_tpl_vars['journalSettings']['sponsors'] ) )): ?>
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'journalSponsorship'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.journalSponsorship"), $this);?>
</a></li><?php endif; ?>
                                        
                                        <?php if ($this->_tpl_vars['siteCategoriesEnabled']): ?>
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="/" data-track="click" data-track-action="OAI feed" data-track-label="link" target="_blank"><span><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.otherJournals"), $this);?>
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
                                <svg class="details-marker" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                                <div class="c-header-expander__container">
                                    <h2 id="Explore-content" class="c-header-expander__heading u-hide">Publish with us</h2>
                                    <ul class="c-header-expander__list">
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'authors'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.infoForAuthors"), $this);?>
</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item">Submission guidelines</a></li>
                                        
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
                    </ul>
                    
                    <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION || $this->_tpl_vars['donationEnabled'] || $this->_tpl_vars['currentJournal']->getSetting('membershipFee')): ?>
                    <div class="c-header__menu u-ml-16 u-show-lg u-show-at-lg c-header__menu--tools">
                        <div class="c-header__item c-header__item--pipe">
                            <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?>
                            <a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'subscriptions'), $this);?>
" data-track="click" data-track-action="subscribe" data-track-label="link" data-test="menu-button-subscribe">
                                <span>Subscribe</span>
                            </a><?php endif; ?>                            <?php if ($this->_tpl_vars['currentJournal']->getSetting('donationFeeEnabled')): ?>
                            <a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'donations'), $this);?>
" data-track="click" data-track-action="donation" data-track-label="link" data-test="menu-button-donation">
                                <span>Donation</span>
                            </a><?php endif; ?>
                            <?php if ($this->_tpl_vars['currentJournal']->getSetting('membershipFeeEnabled')): ?>
                            <a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'memberships'), $this);?>
" data-track="click" data-track-action="membership" data-track-label="link" data-test="menu-button-membership">
                                <span>Members</span>
                            </a><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                </div>
                <form class="lm-site-search u-show-from-md" method="GET" id="search-bar" action="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'search','op' => 'search'), $this);?>
">
                	<div class="ms-search-field"><input type="text" id="query" name="query" value="" placeholder="Search in this journal" class="lm-search-term"></div>
                	<button type="submit" value="Search" class="uk-button uk-button-primary btn-search">
                	    <svg role="img" aria-hidden="true" focusable="false" height="32" width="32" viewBox="0 0 17 17" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
                	    <svg class="u-hide lm-icon-search" viewBox="0 0 32 32"><path fill="inherit" d="M31.1 26.9l-8.8-8.8c1.1-1.8 1.7-3.9 1.7-6.1 0-6.6-5.4-12-12-12s-12 5.4-12 12 5.4 12 12 12c2.2 0 4.3-0.6 6.1-1.7l8.8 8.8c0.6 0.6 1.4 0.9 2.1 0.9s1.5-0.3 2.1-0.9c1.2-1.2 1.2-3.1 0-4.2zM3 12c0-5 4-9 9-9s9 4 9 9c0 5-4 9-9 9s-9-4-9-9z"></path></svg>
                	</button>
                </form>                    
                <ul class="u-hide c-header__menu c-header__menu--tools">
                    <li class="c-header__item">
                        <a class="c-header__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'notification','op' => 'subscribeMailList'), $this);?>
" rel="nofollow" data-track="click" data-track-action="Sign up for alerts" data-track-label="link (desktop site header)" data-track-external="">
                            <span>Sign up for alerts</span><svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m4 10h2.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-3.08578644l-1.12132034 1.1213203c-.18753638.1875364-.29289322.4418903-.29289322.7071068v.1715729h14v-.1715729c0-.2652165-.1053568-.5195704-.2928932-.7071068l-1.7071068-1.7071067v-3.4142136c0-2.76142375-2.2385763-5-5-5-2.76142375 0-5 2.23857625-5 5zm3 4c0 1.1045695.8954305 2 2 2s2-.8954305 2-2zm-5 0c-.55228475 0-1-.4477153-1-1v-.1715729c0-.530433.21071368-1.0391408.58578644-1.4142135l1.41421356-1.4142136v-3c0-3.3137085 2.6862915-6 6-6s6 2.6862915 6 6v3l1.4142136 1.4142136c.3750727.3750727.5857864.8837805.5857864 1.4142135v.1715729c0 .5522847-.4477153 1-1 1h-4c0 1.6568542-1.3431458 3-3 3-1.65685425 0-3-1.3431458-3-3z" fill="#222"></path></svg>
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
    <div class="c-journal-header__identity c-journal-header__identity--default"></div>
</header>

<div id="container" class="Article">

<!-- Message Warning or Important Information -->
<div class="panel-s info-banner u-hide">
    <div class="alert alert-container">
        <span class="alert-icon-box u-bg-info-blue"><svg focusable="false" viewBox="0 0 16 128" width="24" height="24" class="icon icon-information u-fill-white"><path d="m2.72 42.24h9.83l0.64 59.06h-11.15l0.63-59.06zm-1.97-19.02c0-3.97 3.25-7.22 7.22-7.22 3.98 0 7.23 3.25 7.23 7.22 0 3.98-3.25 7.23-7.23 7.23-2.42 0-4.57-1.19-5.85-3.02-0.87-1.19-1.37-2.65-1.37-4.21z"></path></svg></span>
        <span class="alert-text"><span class="text-s">Selected articles from this journal and other medical research on<b> Novel Coronavirus (SARS-coV-2) </b>and related viruses are now available for free on GoogleScholar – <a rel="noreferrer noopener" class="anchor" href="//scholar.google.com/scholar?q=SARS-Cov-2" target="_blank"><span class="anchor-text">start exploring directly</span></a> or visit the <a rel="noreferrer noopener" class="anchor" href="https://www.elsevier.com/connect/coronavirus-information-center" target="_blank"><span class="anchor-text">Elsevier Novel Coronavirus Information Center</span></a></span></span>
    </div>
</div>

<div id="app" class="App">
<div class="page">
<section>
    <div class="sd-flex-container">
        <div class="sd-flex-content">
    <div id="mathjax-container" class="Article">
<div class="sticky-outer-wrapper">                
<div class="sticky-inner-wrapper" style="position: relative; z-index: 1; transform: translate3d(0px, 0px, 0px);">            
    <div id="screen-reader-main-content" class="Toolbar medium-bar">
        <div class="toolbar-container">
        	<div class="u-show-from-lg col-lg-6 l-side">&nbsp;</div>
        	<div class="buttons text-s">
        		<?php if (( ! $this->_tpl_vars['subscriptionRequired'] || $this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN || $this->_tpl_vars['subscribedUser'] || $this->_tpl_vars['subscribedDomain'] || ( $this->_tpl_vars['subscriptionExpiryPartial'] && $this->_tpl_vars['articleExpiryPartial'][$this->_tpl_vars['articleId']] ) )): ?>
                    <?php $this->assign('hasAccess', 1); ?>
                <?php else: ?>
                    <?php $this->assign('hasAccess', 0); ?>
                <?php endif; ?>
                
                <?php if ($this->_tpl_vars['hasAccess'] || ( $this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] )): ?>
        		<?php $_from = $this->_tpl_vars['article']->getGalleys(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['galleyList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['galleyList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['galley']):
        $this->_foreach['galleyList']['iteration']++;
?>
        		<?php if ($this->_tpl_vars['galley']->isPdfGalley() && ! $this->_tpl_vars['galley']->isHTMLGalley()): ?>
        		<div id="download-pdf-popover" class="popover PdfDownloadButton download-pdf-popover">
        		    <div id="popover-trigger-download-pdf-popover">
        		        <button id="pdfLink" class="button button-anchor u-padding-0-left" role="button" aria-expanded="false" aria-haspopup="true" aria-label="Download PDF options" type="button">
        		            <svg focusable="false" viewBox="0 0 32 32" width="24" height="24" class="icon icon-pdf-multicolor pdf-icon"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg>
        		            <?php if ($this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] && $this->_tpl_vars['restrictOnlyPdf']): ?>
        		            <span class="button-text"><a rel="noreferrer noopener" class="pdf-file" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])))), $this);?>
" target="_blank" >
        		                <span id="articleFullText" class="pdf-download-label u-show-inline-from-lg"><?php if ($this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN || ! $this->_tpl_vars['galley']->isHTMLGalley() || ! $this->_tpl_vars['galley']->isPdfGalley()): ?>Download fulltext in <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php elseif ($this->_tpl_vars['galley']->getRemoteURL()): ?>Download fulltext <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 (Remote)<?php elseif ($this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_SUBSCRIPTION): ?>Get fulltext <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 Access<?php endif; ?></span>
        		                <span class="pdf-download-label-short u-hide-from-lg"><?php if ($this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN || ! $this->_tpl_vars['galley']->isHTMLGalley()): ?>Download <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php elseif ($this->_tpl_vars['galley']->getRemoteURL()): ?>Download <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 (Remote)<?php elseif ($this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_SUBSCRIPTION): ?>Get <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 Access<?php endif; ?></span></a>
        		            </span>
        		            <?php else: ?>
        		            <span class="button-text"><a rel="noreferrer noopener" class="pdf-file" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])))), $this);?>
" target="_blank">
        		                <span id="articleFullText" class="pdf-download-label u-show-inline-from-lg"><?php if ($this->_tpl_vars['galley']->getRemoteURL()): ?>Download fulltext <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 (Remote)<?php else: ?>Download fulltext in <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></span>
        		                <span class="pdf-download-label-short u-hide-from-lg"><?php if ($this->_tpl_vars['galley']->getRemoteURL()): ?>Download <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 (Remote)<?php else: ?>Download fulltext <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></span></a>
        		            </span>
        		            <?php endif; ?>
        		        </button>
                    </div>
                </div>
                <?php elseif (! $this->_tpl_vars['galley']->isPdfGalley() && ! $this->_tpl_vars['galley']->isHTMLGalley()): ?>
                <div id="check-access-popover" class="popover PdfDownloadButton check-access-popover">
                    <div id="popover-trigger-check-access-popover">
                        <button class="button button-anchor u-padding-0-left" role="button" aria-expanded="false" aria-haspopup="true" type="submit">
                            <?php if ($this->_tpl_vars['galley']->getRemoteURL() && $this->_tpl_vars['galley']->getLabel() == 'PDF'): ?>
                            <svg focusable="false" viewBox="0 0 32 32" width="24" height="24" class="icon icon-pdf-multicolor pdf-icon"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg>
                            <?php elseif ($this->_tpl_vars['galley']->getRemoteURL() && $this->_tpl_vars['galley']->getLabel() == 'JPG' || $this->_tpl_vars['galley']->getRemoteURL() && $this->_tpl_vars['galley']->getLabel() == 'JPEG' || $this->_tpl_vars['galley']->getLabel() == 'JPG' || $this->_tpl_vars['galley']->getLabel() == 'JPEG'): ?>
                            <svg id="icon-jpg" class="icon icon-pdf-multicolor pdf-icon" height="800px" width="800px" viewBox="0 0 512 512" ><path fill="#DEE0E1" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"></path><path fill="#505050" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"></path><polygon fill="#CAD1D8" points="480,224 384,128 480,128 "></polygon><path fill="#d54449" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16  V416z"></path><g><path fill="#FFFFFF" d="M141.968,303.152c0-10.752,16.896-10.752,16.896,0v50.528c0,20.096-9.6,32.256-31.728,32.256   c-10.88,0-19.952-2.96-27.888-13.184c-6.528-7.808,5.76-19.056,12.416-10.88c5.376,6.656,11.136,8.192,16.752,7.936   c7.152-0.256,13.44-3.472,13.568-16.128v-50.528H141.968z"></path><path fill="#FFFFFF" d="M181.344,303.152c0-4.224,3.328-8.832,8.704-8.832H219.6c16.64,0,31.616,11.136,31.616,32.48   c0,20.224-14.976,31.488-31.616,31.488h-21.36v16.896c0,5.632-3.584,8.816-8.192,8.816c-4.224,0-8.704-3.184-8.704-8.816   L181.344,303.152L181.344,303.152z M198.24,310.432v31.872h21.36c8.576,0,15.36-7.568,15.36-15.504   c0-8.944-6.784-16.368-15.36-16.368H198.24z"></path><path fill="#FFFFFF" d="M342.576,374.16c-9.088,7.552-20.224,10.752-31.472,10.752c-26.88,0-45.936-15.344-45.936-45.808   c0-25.824,20.096-45.904,47.072-45.904c10.112,0,21.232,3.44,29.168,11.248c7.792,7.664-3.456,19.056-11.12,12.288   c-4.736-4.608-11.392-8.064-18.048-8.064c-15.472,0-30.432,12.4-30.432,30.432c0,18.944,12.528,30.464,29.296,30.464   c7.792,0,14.448-2.32,19.184-5.76V348.08h-19.184c-11.392,0-10.24-15.616,0-15.616h25.584c4.736,0,9.072,3.584,9.072,7.552v27.248   C345.76,369.568,344.752,371.712,342.576,374.16z"></path></g><path fill="#CAD1D8" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"></path></svg>
        		            <?php elseif ($this->_tpl_vars['galley']->getRemoteURL() && $this->_tpl_vars['galley']->getLabel() == 'PNG' || $this->_tpl_vars['galley']->getLabel() == 'PNG'): ?>
        		            <svg id="icon-png" height="25" width="25" viewBox="0 0 512 512" class="icon icon-pdf-multicolor pdf-icon"><path d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z" fill="#E2E5E7"></path><path d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z" fill="#2f2f2f"></path><polygon fill="#CAD1D8" points="480,224 384,128 480,128"></polygon><path d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16  V416z" fill="#d54449"></path><g><path d="M92.816,303.152c0-4.224,3.312-8.848,8.688-8.848h29.568c16.624,0,31.6,11.136,31.6,32.496   c0,20.224-14.976,31.472-31.6,31.472H109.68v16.896c0,5.648-3.552,8.832-8.176,8.832c-4.224,0-8.688-3.184-8.688-8.832   C92.816,375.168,92.816,303.152,92.816,303.152z M109.68,310.432v31.856h21.376c8.56,0,15.344-7.552,15.344-15.488   c0-8.96-6.784-16.368-15.344-16.368L109.68,310.432L109.68,310.432z" fill="#FFFFFF"></path><path d="M178.976,304.432c0-4.624,1.024-9.088,7.68-9.088c4.592,0,5.632,1.152,9.072,4.464l42.336,52.976   v-49.632c0-4.224,3.696-8.848,8.064-8.848c4.608,0,9.072,4.624,9.072,8.848v72.016c0,5.648-3.456,7.792-6.784,8.832   c-4.464,0-6.656-1.024-10.352-4.464l-42.336-53.744v49.392c0,5.648-3.456,8.832-8.064,8.832s-8.704-3.184-8.704-8.832v-70.752   H178.976z" fill="#FFFFFF"></path><path d="M351.44,374.16c-9.088,7.536-20.224,10.752-31.472,10.752c-26.88,0-45.936-15.36-45.936-45.808   c0-25.84,20.096-45.92,47.072-45.92c10.112,0,21.232,3.456,29.168,11.264c7.808,7.664-3.456,19.056-11.12,12.288   c-4.736-4.624-11.392-8.064-18.048-8.064c-15.472,0-30.432,12.4-30.432,30.432c0,18.944,12.528,30.448,29.296,30.448   c7.792,0,14.448-2.304,19.184-5.76V348.08h-19.184c-11.392,0-10.24-15.632,0-15.632h25.584c4.736,0,9.072,3.6,9.072,7.568v27.248   C354.624,369.552,353.616,371.712,351.44,374.16z" fill="#FFFFFF"></path></g><path d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z" fill="#CAD1D8"></path></svg>
        		            <?php elseif ($this->_tpl_vars['galley']->getRemoteURL() && $this->_tpl_vars['galley']->getLabel() == 'GIF' || $this->_tpl_vars['galley']->getLabel() == 'GIF'): ?>
        		            <svg id="icon-gif" class="icon icon-pdf-multicolor pdf-icon" height="800px" width="800px" viewBox="0 0 512 512"><path d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z" fill="#E2E5E7"></path><path d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z" fill="#2f2f2f"></path><polygon points="480,224 384,128 480,128 " fill="#CAD1D8"></polygon><path d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16  V416z" fill="#A066AA"></path><g><path style="fill:#FFFFFF;" d="M199.84,374.16c-9.088,7.536-20.224,10.752-31.472,10.752c-26.88,0-45.936-15.36-45.936-45.808   c0-25.84,20.096-45.92,47.072-45.92c10.112,0,21.232,3.456,29.168,11.264c7.808,7.664-3.456,19.056-11.12,12.288   c-4.736-4.624-11.392-8.064-18.048-8.064c-15.472,0-30.432,12.4-30.432,30.432c0,18.944,12.528,30.448,29.296,30.448   c7.792,0,14.448-2.304,19.184-5.76V348.08h-19.184c-11.392,0-10.24-15.632,0-15.632h25.584c4.736,0,9.072,3.6,9.072,7.568v27.248   C203.024,369.552,202.016,371.712,199.84,374.16z"></path><path style="fill:#FFFFFF;" d="M224.944,303.152c0-10.496,16.896-10.88,16.896,0v73.024c0,10.624-16.896,10.88-16.896,0V303.152z"></path><path style="fill:#FFFFFF;" d="M281.12,312.096v20.336h32.608c4.608,0,9.216,4.608,9.216,9.088c0,4.224-4.608,7.664-9.216,7.664   H281.12v26.864c0,4.48-3.2,7.936-7.68,7.936c-5.632,0-9.072-3.456-9.072-7.936v-72.656c0-4.608,3.456-7.936,9.072-7.936h44.912   c5.632,0,8.96,3.328,8.96,7.936c0,4.096-3.328,8.688-8.96,8.688H281.12V312.096z"></path></g><path d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z" fill="#CAD1D8"></path></svg>
        		            <?php endif; ?>
                            <span class="button-text"><a rel="noreferrer noopener" class="pdf-file" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])))), $this);?>
" target="_blank">
        		                <span id="articleFullText" class="pdf-download-label u-show-inline-from-lg"><?php if ($this->_tpl_vars['galley']->getRemoteURL()): ?>Get fulltext <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>Download <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 file<?php endif; ?></span>
        		                <span class="pdf-download-label-short u-hide-from-lg"><?php if ($this->_tpl_vars['galley']->getRemoteURL()): ?>Get <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>Download <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></span></a>
        		            </span>
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; else: ?>
        	    <div id="check-access-popover" class="popover PdfDownloadButton check-access-popover">
        	       <div id="popover-trigger-check-access-popover">
        	           <button class="button button-anchor u-padding-0-left" role="button" aria-expanded="false" aria-haspopup="true" type="submit"><svg focusable="false" viewBox="0 0 32 32" width="24" height="24" class="icon icon-pdf-multicolor pdf-icon"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg>
        	           <span class="button-text">
        	               <?php if ($this->_tpl_vars['layoutFile'] != ''): ?>
        	               <a rel="noreferrer noopener" class="pdf-file">
        	                   <span class="pdf-download-label u-show-inline-from-lg">PDF Coming soon!</span>
        	                   <span class="pdf-download-label-short u-hide-from-lg">Coming Soon!</span></a>
        	               <?php elseif ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getPublished()): ?>
        	               <a rel="noreferrer noopener" class="pdf-file" style="cursor: default;">
                                <span class="pdf-download-label u-show-inline-from-lg">No PDF Available</span>
                                <span class="pdf-download-label-short u-hide-from-lg">No PDF</span>
                            </a>
        	               <?php else: ?>
        	               <a rel="noreferrer noopener" class="pdf-file">
        	                   <span class="pdf-download-label u-show-inline-from-lg">PDF Unavailable!</span>
        	                   <span class="pdf-download-label-short u-hide-from-lg">Not Access</span></a>
        	               <span><a class="anchor" href="https://service.elsevier.com/app/answers/detail/a_id/22801/supporthub/sciencedirect/" target="_blank" title="What are Corrected Proof articles?"><svg focusable="false" viewBox="0 0 114 128" width="16" height="16" class="icon icon-help" style="margin-right:0"><path d="m57 8c-14.7 0-28.5 5.72-38.9 16.1-10.38 10.4-16.1 24.22-16.1 38.9 0 30.32 24.68 55 55 55 14.68 0 28.5-5.72 38.88-16.1 10.4-10.4 16.12-24.2 16.12-38.9 0-30.32-24.68-55-55-55zm0 1e1c24.82 0 45 20.18 45 45 0 12.02-4.68 23.32-13.18 31.82s-19.8 13.18-31.82 13.18c-24.82 0-45-20.18-45-45 0-12.02 4.68-23.32 13.18-31.82s19.8-13.18 31.82-13.18zm-0.14 14c-11.55 0.26-16.86 8.43-16.86 18v2h1e1v-2c0-4.22 2.22-9.66 8-9.24 5.5 0.4 6.32 5.14 5.78 8.14-1.1 6.16-11.78 9.5-11.78 20.5v6.6h1e1v-5.56c0-8.16 11.22-11.52 12-21.7 0.74-9.86-5.56-16.52-16-16.74-0.39-0.01-0.76-0.01-1.14 0zm-4.86 5e1v1e1h1e1v-1e1h-1e1z"></path></svg></a>
        	                   </span>
        	               <?php endif; ?>
        	           </span>
        	           </button>
        	       </div>
        	    </div>        	    
        	    <?php endif; unset($_from); ?>
        	    <?php endif; ?>
            </div>
            <div class="quick-search-container pull-right u-show-from-md">
                <form id="quick-search" class="QuickSearch u-margin-xs-right" action="//www.sciencedirect.com/search/advanced#submit" method="get" target="_blank" rel="noreferrer noopener">
                    <input type="search" class="query" aria-label="Search ScienceDirect" name="qs" placeholder="Search ScienceDirect">
                    <button class="button button-primary" type="submit" aria-label="Submit search"><span class="button-text"><svg class="icon icon-search" focusable="false" viewBox="0 0 100 128" height="20" width="18.75"><path d="m19.22 76.91c-5.84-5.84-9.05-13.6-9.05-21.85s3.21-16.01 9.05-21.85c5.84-5.83 13.59-9.05 21.85-9.05 8.25 0 16.01 3.22 21.84 9.05 5.84 5.84 9.05 13.6 9.05 21.85s-3.21 16.01-9.05 21.85c-5.83 5.83-13.59 9.05-21.84 9.05-8.26 0-16.01-3.22-21.85-9.05zm80.33 29.6l-26.32-26.32c5.61-7.15 8.68-15.9 8.68-25.13 0-10.91-4.25-21.17-11.96-28.88-7.72-7.71-17.97-11.96-28.88-11.96s-21.17 4.25-28.88 11.96c-7.72 7.71-11.97 17.97-11.97 28.88s4.25 21.17 11.97 28.88c7.71 7.71 17.97 11.96 28.88 11.96 9.23 0 17.98-3.07 25.13-8.68l26.32 26.32 7.03-7.03"></path></svg></span>
                    </button><a rel="noreferrer noopener" class="advanced-search-link" href="//www.sciencedirect.com/search/advanced#submit" target="_blank">Advanced</a>
                    <input type="hidden" name="origin" value="article">
                    <input type="hidden" name="zone" value="qSearch">
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<div class="article-wrapper u-padding-s-top grid row">
	<div class="sidebar">
		<div class="u-show-from-lg col-lg-6 l-side">
		    <?php if (( ! $this->_tpl_vars['article']->getHideAuthor() == @AUTHOR_TOC_DEFAULT ) || $this->_tpl_vars['article']->getHideAuthor() == @AUTHOR_TOC_SHOW): ?>
			<div class="TableOfContents u-margin-l-bottom" lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLanguage())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">            	
			    
                <div id="submitter" class="cms-person author-group">
                    <h3 class="u-h5 article-span u-font-sans-sang">Submitted</h3>
                	<?php $this->assign('submitter', $this->_tpl_vars['article']->getUser()); ?>
                	<?php $this->assign('submitterFirstName', $this->_tpl_vars['submitter']->getFirstName()); ?>
                    <?php $this->assign('submitterMiddleName', $this->_tpl_vars['submitter']->getMiddleName()); ?>
                    <?php $this->assign('submitterLastName', $this->_tpl_vars['submitter']->getLastName()); ?>
                	<?php $this->assign('submitterAffiliation', $this->_tpl_vars['submitter']->getLocalizedAffiliation()); ?>
                	<?php $this->assign('submitterCountry', $this->_tpl_vars['submitter']->getCountry()); ?>
                	<?php $this->assign('profileImage', $this->_tpl_vars['submitter']->getSetting('profileImage')); ?>
                    <?php if ($this->_tpl_vars['profileImage']): ?>
                    <figure class="avatar editor Avatar Avatar--size-80">
                        <img height="auto" width="150" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="lazyload Avatar__img is-inside-mask" src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
" />
                    </figure>
                    <?php endif; ?>
                	<div id="submitter" class="overview">
                    	 <h3 class="u-h4 u-fonts-sans" itemprop="name"><span class="u-hide text" itemprop="full-name"><?php echo $this->_tpl_vars['submitter']->getFullName(); ?>
</span><?php if ($this->_tpl_vars['submitterFirstName'] !== $this->_tpl_vars['submitterLastName']): ?><span class="text" itemprop="given-name"><?php echo $this->_tpl_vars['submitterFirstName']; ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['submitterMiddleName']): ?><span class="text" itemprop="middle-name"><?php echo $this->_tpl_vars['submitterMiddleName']; ?>
</span><?php endif; ?><span class="text" itemprop="surname"><?php echo $this->_tpl_vars['submitterLastName']; ?>
</span></h3>
                    	 <dl><?php if (((is_array($_tmp=$this->_tpl_vars['submitterAffiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['submitterAffiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
<?php else: ?>[<i>Affiliation Not Available</i>]<?php endif; ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getCountryLocalized())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</dl>
                    	 <?php if ($this->_tpl_vars['submitter']->getData('orcid')): ?><svg viewBox="0 0 72 72" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" <title>Orcid logo</title><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="hero" transform="translate(-924.000000, -72.000000)" fill-rule="nonzero"><g id="Group-4"><g id="vector_iD_icon" transform="translate(924.000000, 72.000000)"><path d="M72,36 C72,55.884375 55.884375,72 36,72 C16.115625,72 0,55.884375 0,36 C0,16.115625 16.115625,0 36,0 C55.884375,0 72,16.115625 72,36 Z" id="Path" fill="#000" fill-opacity="0.9"></path><g id="Group" transform="translate(18.868966, 12.910345)" fill="#FFFFFF"><polygon id="Path" points="5.03734929 39.1250878 0.695429861 39.1250878 0.695429861 9.14431787 5.03734929 9.14431787 5.03734929 22.6930505 5.03734929 39.1250878"></polygon><path d="M11.409257,9.14431787 L23.1380784,9.14431787 C34.303014,9.14431787 39.2088191,17.0664074 39.2088191,24.1486995 C39.2088191,31.846843 33.1470485,39.1530811 23.1944669,39.1530811 L11.409257,39.1530811 L11.409257,9.14431787 Z M15.7511765,35.2620194 L22.6587756,35.2620194 C32.49858,35.2620194 34.7541226,27.8438084 34.7541226,24.1486995 C34.7541226,18.1301509 30.8915059,13.0353795 22.4332213,13.0353795 L15.7511765,13.0353795 L15.7511765,35.2620194 Z" id="Shape"></path><path d="M5.71401206,2.90182329 C5.71401206,4.441452 4.44526937,5.72914146 2.86638958,5.72914146 C1.28750978,5.72914146 0.0187670918,4.441452 0.0187670918,2.90182329 C0.0187670918,1.33420133 1.28750978,0.0745051096 2.86638958,0.0745051096 C4.44526937,0.0745051096 5.71401206,1.36219458 5.71401206,2.90182329 Z" id="Path"></path></g></g></g></g></g></svg><a rel="noreferrer noopener" title="Go to view <?php echo ((is_array($_tmp=$this->_tpl_vars['fullname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 orcid-ID profile" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="icon extern anchor"> <span class="anchor-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></a><?php endif; ?>
                    	 <p><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width=".9em" height=".7em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg> <a rel="noreferrer noopener" class="icon anchor" title="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getData('email'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getData('email'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" ><span class="anchor-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getData('email'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></a></p>
                    </div>
            	<div class="PageDivider"></div>                    
            	</div>
            	
			    <section class="u-hide p-separator externals">
			        <span class="__dimensions_badge_embed__" data-doi="<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" data-legend="always" data-style="small_circle"></span>
			    </section>
			    
			    <?php if ($this->_tpl_vars['leftSidebarCode'] || $this->_tpl_vars['rightSidebarCode']): ?>
    			     <?php if ($this->_tpl_vars['leftSidebarCode']): ?><?php endif; ?>
    			     <?php if ($this->_tpl_vars['rightSidebarCode']): ?><?php echo $this->_tpl_vars['rightSidebarCode']; ?>
<?php endif; ?>
			    <?php endif; ?>
			    
			    <div class="js-ad sideAds">
			        <aside class="leftAds adsbox c-ad c-ad--300x250 u-mt-16" data-component-mpu="">
                        <div class="c-ad__inner">
                            <p class="c-ad__label">Sangia Advertisement</p>
                            <style>
                            <?php echo '@media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                            '; ?>

                            </style>
                            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721" crossorigin="anonymous"></script>
                            <!-- Sangia_Publishing_ads -->
                            <ins class="adsbygoogle c-ad--300x250"
                                style="display:inline-block;width:100%;height:auto"
                                data-ad-client="ca-pub-8416265824412721"
                                data-ad-slot="2738201692">
                            </ins>
                            <script>
                            <?php echo '
                                 (adsbygoogle = window.adsbygoogle || []).push({});
                            '; ?>

                            </script>
                        </div>
                    </aside>
                </div>
                
            </div>
            
		    <?php else: ?>
		    
			<div class="TableOfContents u-margin-l-bottom" lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLanguage())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
                
				<div id="toc-outline" class="Outline">
				    <h2 class="u-h4 article-span u-font-sans-sang">Article Outline</h2>
    				<ul class="u-padding-xs-bottom text-s u-font-sans-sang">
    			        <li><a class="anchor" href="#abstracts" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="Abstract" rel="noreferrer noopener"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.abstract"), $this);?>
</span></a></li>
    			        <li><a class="anchor" href="#articleSubject" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="Keywords" rel="noreferrer noopener"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.subject"), $this);?>
</span></a></li>
                    <?php if ($this->_tpl_vars['article']->getLocalizedSubject(null)): ?>	
                    	<?php if ($this->_tpl_vars['galleys']): ?>
                        <?php if ($this->_tpl_vars['galley']->isHTMLGalley()): ?>
    			        <li><a href="#Introduction" title="Introduction" rel="noreferrer noopener">Introduction</a></li>
    			        <li><a href="#Method" title="Materials and Method" rel="noreferrer noopener">Materials and Method</a></li>
    			        <li><a href="#Results" title="Results" rel="noreferrer noopener">Results</a></li>
    			        <li><a href="#Discussion" title="Discussion" rel="noreferrer noopener">Discussion</a></li>
    			        <li><a href="#Conclusion" title="Conclusion" rel="noreferrer noopener">Conclusion</a></li>
    			        <li><a href="#Conclusion" title="Declaration" rel="noreferrer noopener">Declaration</a></li>
    			        <li><a href="#References" title="References" rel="noreferrer noopener"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.references"), $this);?>
</a></li>
    			        <?php endif; ?>
    			        <li><span title="Introduction">Introduction</span></li>
    			        <li><span title="Materials and Method">Materials and Method</span></li>
    			        <li><span title="Results">Results</span></li>
    			        <li><span title="Discussion">Discussion</span></li>
    			        <li><span title="Conclusion">Conclusion</span></li>
    			        <?php if ($this->_tpl_vars['article']->getLocalizedSponsor()): ?>
    			        <li><a class="anchor" href="#Declaration" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="Funding Information" rel="noreferrer noopener"><span class="anchor-text">Funding Information</span></a></li><?php endif; ?>
    			        <?php if ($this->_tpl_vars['journalRt']->getSupplementaryFiles() && is_a ( $this->_tpl_vars['article'] , 'PublishedArticle' ) && $this->_tpl_vars['article']->getSuppFiles()): ?>
    			        <li><a class="anchor" href="#SuppFiles" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.suppFiles"), $this);?>
" rel="noreferrer noopener"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.suppFiles"), $this);?>
</span></a></li><?php endif; ?>
    			        <li><a class="anchor" href="#References" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations"), $this);?>
" rel="noreferrer noopener"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations"), $this);?>
</span></a></li>
    			        <?php else: ?>
    			        <li><span title="Introduction">Introduction</span></li>
    			        <li><span title="Materials and Method">Materials and Method</span></li>
    			        <li><span title="Results">Results</span></li>
    			        <li><span title="Discussion">Discussion</span></li>
    			        <li><span title="Conclusion">Conclusion</span></li>
    			        <?php if ($this->_tpl_vars['article']->getLocalizedSponsor()): ?>
    			        <li><a href="#Declaration" class="anchor" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="Funding Information" rel="noreferrer noopener"><span class="anchor-text">Funding Information</span></a></li><?php endif; ?>
    			        <?php if ($this->_tpl_vars['journalRt']->getSupplementaryFiles() && is_a ( $this->_tpl_vars['article'] , 'PublishedArticle' ) && $this->_tpl_vars['article']->getSuppFiles()): ?>
    			        <li><a href="#SuppFiles" class="anchor" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.suppFiles"), $this);?>
" rel="noreferrer noopener"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "rt.suppFiles"), $this);?>
</span></a></li><?php endif; ?>
    			        <li><a href="#References" class="anchor" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations"), $this);?>
" rel="noreferrer noopener"><span class="anchor-text"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.citations"), $this);?>
</span></a></li>
    			        <?php endif; ?>
    			    <?php endif; ?>   
    			    </ul>
    			    <br />
                </div>
			    <div class="PageDivider"></div>
                
                <?php $this->assign('displayCoverIssue', $this->_tpl_vars['issue']->getShowCoverPage($this->_tpl_vars['locale'])); ?>
                
                                <?php $this->assign('coverCount', 0); ?>
                <?php if ($this->_tpl_vars['showCoverPage']): ?>
                    <?php $this->assign('coverCount', 1); ?>
                <?php endif; ?>
                
                <?php $this->assign('galleyImageCount', 0); ?>
                <?php if ($this->_tpl_vars['article']->getGalleys() && $this->_tpl_vars['galley']->isHTMLGalley() && $this->_tpl_vars['galley']->getImageFiles()): ?>
                    <?php $this->assign('galleyImageCount', count($this->_tpl_vars['galley']->getImageFiles())); ?>
                <?php endif; ?>
                
                                <?php $this->assign('suppImageCount', 0); ?>
                <?php $_from = $this->_tpl_vars['article']->getSuppFiles(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['suppFile']):
?>
                    <?php $this->assign('fileName', ((is_array($_tmp=$this->_tpl_vars['suppFile']->getOriginalFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))); ?>
                    <?php if (((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['fileName'])) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1") : smarty_modifier_regex_replace($_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1")))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)) == 'jpg' || ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['fileName'])) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1") : smarty_modifier_regex_replace($_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1")))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)) == 'jpeg' || ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['fileName'])) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1") : smarty_modifier_regex_replace($_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1")))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)) == 'png' || ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['fileName'])) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1") : smarty_modifier_regex_replace($_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1")))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)) == 'gif'): ?>
                        <?php $this->assign('suppImageCount', $this->_tpl_vars['suppImageCount']+1); ?>
                    <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?>
                
                                <?php $this->assign('totalImageCount', $this->_tpl_vars['coverCount']+$this->_tpl_vars['galleyImageCount']+$this->_tpl_vars['suppImageCount']); ?>
                
                <?php if ($this->_tpl_vars['showCoverPage'] || $this->_tpl_vars['article']->getGalleys() && $this->_tpl_vars['galley']->isHTMLGalley() && $this->_tpl_vars['galley']->getImageFiles() || $this->_tpl_vars['suppImageCount'] > 0): ?>
                <div class="Figures u-mb-16" id="toc-figures">
                    <h2 class="u-h4 u-font-sans-sang">Figures <span class="count">(<?php echo $this->_tpl_vars['totalImageCount']; ?>
)</span></h2>
                    <ol>
                                                <?php if ($this->_tpl_vars['showCoverPage']): ?>
                        <li><span><div><img loading="lazy" itemprop="image" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedCoverPageAltText())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" style="max-width: 219px; max-height: 105px;"></div></span>
                        </li>
                        <?php endif; ?>
                        
                                                <?php if ($this->_tpl_vars['article']->getGalleys() && $this->_tpl_vars['galley']->isHTMLGalley()): ?>
                            <?php $_from = $this->_tpl_vars['galley']->getImageFiles(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['images'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['images']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['imageFile']):
        $this->_foreach['images']['iteration']++;
?>
                            <li<?php if (( $this->_tpl_vars['key'] + $this->_tpl_vars['coverCount'] ) > 6): ?> class="figure-item-hidden"<?php endif; ?>><span><div><img loading="lazy" itemprop="image" alt="Fig. <?php echo $this->_tpl_vars['key']+1; ?>
" src="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'editor','op' => 'viewFile','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['imageFile']->getFileId()) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['imageFile']->getFileId()))), $this);?>
" style="max-width: 140px; max-height: 163px;"></div></span>
                            </li>
                            <?php endforeach; endif; unset($_from); ?>
                        <?php endif; ?>
                        
                                                <?php $this->assign('figureCounter', $this->_tpl_vars['coverCount']+$this->_tpl_vars['galleyImageCount']); ?>
                        <?php $_from = $this->_tpl_vars['article']->getSuppFiles(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['suppFile']):
?>
                            <?php $this->assign('fileName', ((is_array($_tmp=$this->_tpl_vars['suppFile']->getOriginalFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))); ?>
                            <?php if (((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['fileName'])) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1") : smarty_modifier_regex_replace($_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1")))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)) == 'jpg' || ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['fileName'])) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1") : smarty_modifier_regex_replace($_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1")))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)) == 'jpeg' || ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['fileName'])) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1") : smarty_modifier_regex_replace($_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1")))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)) == 'png' || ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['fileName'])) ? $this->_run_mod_handler('regex_replace', true, $_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1") : smarty_modifier_regex_replace($_tmp, "/^.*\.(jpg|jpeg|png|gif)$/i", "\\1")))) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)) == 'gif'): ?>
                                
                                <?php $this->assign('figureCounter', $this->_tpl_vars['figureCounter']+1); ?>
                                <li<?php if ($this->_tpl_vars['figureCounter'] > 6): ?> class="figure-item-hidden"<?php endif; ?>><span><div>
                                    <img loading="lazy" itemprop="image" 
                                         alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['suppFile']->getSuppFileTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" 
                                         src="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'downloadSuppFile','path' => ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['suppFile']->getBestSuppFileId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['suppFile']->getBestSuppFileId($this->_tpl_vars['currentJournal'])))), $this);?>
" 
                                         style="max-width: 140px; max-height: 163px;" />
                                    <div style="font-size: 0.8em;"><?php echo $this->_tpl_vars['fileName']; ?>
</div>
                                </div></span></li>
                            <?php endif; ?>
                        <?php endforeach; endif; unset($_from); ?>
                    </ol>
                    
                    <?php if ($this->_tpl_vars['totalImageCount'] > 6): ?>
                    <button class="button button-anchor" data-aa-button="show-figures" type="button" 
                            onclick="document.getElementById('toc-figures').classList.toggle('show-all-figures'); this.querySelector('.button-text').textContent = this.querySelector('.button-text').textContent === 'Show all Figures' ? 'Show fewer Figures' : 'Show all Figures';">
                        <span class="button-text text-s">Show all Figures</span>
                        <svg focusable="false" viewBox="0 0 92 128" height="20" width="17.25" class="icon icon-navigate-down">
                            <path d="m1 51l7-7 38 38 38-38 7 7-45 45z"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
                <div class="PageDivider"></div>
                <?php endif; ?>
                
                <div class="u-hide Tables" id="toc-tables">
                    <h2 class="u-h4 u-font-sans-sang">Tables <span class="count">(1)</span></h2>
                    <ol class="u-padding-s-bottom">
                        <li><span title="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
"><svg focusable="false" viewBox="0 0 98 128" width="18.375" height="24" class="icon icon-table"><path d="m54 68h32v32h-32v-32zm-42 0h32v32h-32v-32zm0-42h32v32h-32v-32zm42 0h32v32h-32v-32zm-52 84h94v-94h-94v94z"></path></svg>Table 1</span>
                        </li>
                    </ol>
                    <div class="PageDivider sideAds">
                        <aside class="leftAds adsbox c-ad c-ad--300x250 u-mt-16" data-component-mpu="">
                            <div class="c-ad__inner">
                                <p class="c-ad__label">Advertisement</p>
                                <style>
                                <?php echo '
                                @media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                                '; ?>

                                </style>
                                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721" crossorigin="anonymous"></script>
                                <!-- Sangia_Publishing_ads -->
                                <ins class="adsbygoogle c-ad--300x250"
                                    style="display:inline-block;width:100%;height:auto"
                                    data-ad-client="ca-pub-8416265824412721"
                                    data-ad-slot="2738201692">
                                </ins>
                                <script>
                                <?php echo '
                                     (adsbygoogle = window.adsbygoogle || []).push({});
                                '; ?>

                                </script>
                            </div>
                        </aside>    
                    </div>
                </div>
                
                <?php if ($this->_tpl_vars['journalRt']->getSupplementaryFiles() && is_a ( $this->_tpl_vars['article'] , 'PublishedArticle' ) && $this->_tpl_vars['article']->getSuppFiles()): ?>
                <div class="Extras p-separator" id="toc-subfiles">
                    <h2 class="u-h4 article-span u-font-sans-sang">
                        Extras <span class="count">(<?php echo count($this->_tpl_vars['article']->getSuppFiles()); ?>
)</span>
                    </h2>
                    <ol class="u-padding-s-bottom">
                        <?php $_from = $this->_tpl_vars['article']->getSuppFiles(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['suppFile']):
?>
                        <li class="toc-list-entry-outline-padding<?php if ($this->_tpl_vars['key'] >= 4): ?> extras-item-hidden<?php endif; ?>">
                            <span class="anchor u-text-truncate anchor-default anchor-has-colored-icon" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['suppFile']->getSuppFileTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
                                <svg focusable="false" viewBox="0 0 94 128" width="17.625" height="24" class="icon icon-text-document">
                                    <path d="m35.6 1e1c-5.38 0-10.62 1.92-14.76 5.4-9.1 7.68-18.84 20.14-18.84 32.1v70.5h9e1v-15.99-2.01-4e1 -17.64-32.36h-56.4zm0 1e1h46.4v22.36 17.64 4e1 2.01 5.99h-7e1v-49c0-6.08 4.92-11 11-11h17v-2e1h-6c-2.2 0-4 1.8-4 4v6h-7c-3.32 0-6.44 0.78-9.22 2.16 2.46-5.62 7.28-11.86 13.5-17.1 2.34-1.98 5.3-3.06 8.32-3.06zm-13.6 38v1e1h5e1v-1e1h-5e1zm0 2e1v1e1h5e1v-1e1h-5e1z"></path>
                                </svg>
                                <span class="anchor-text"><?php echo $this->_tpl_vars['key']+1; ?>
. Supplementary file (<?php echo $this->_tpl_vars['suppFile']->getNiceFileSize(); ?>
)</span>
                            </span>
                        </li>
                        <?php endforeach; endif; unset($_from); ?>
                    </ol>
                    
                    <?php if (count($this->_tpl_vars['article']->getSuppFiles()) > 4): ?>
                    <button class="button button-anchor" data-aa-button="show-extras" type="button"
                            onclick="document.getElementById('toc-subfiles').classList.toggle('show-all-extras'); this.querySelector('.button-text').textContent = this.querySelector('.button-text').textContent === 'Show all Extras' ? 'Show fewer Extras' : 'Show all Extras';">
                        <span class="button-text text-s">Show all Extras</span>
                        <svg focusable="false" viewBox="0 0 92 128" height="20" width="17.25" class="icon icon-navigate-down">
                            <path d="m1 51l7-7 38 38 38-38 7 7-45 45z"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                    
                    <div class="u-hide PageDivider"></div>
                </div>
                <?php endif; ?>
            	
            	<?php if ($this->_tpl_vars['article']->getLocalizedSubject(null)): ?>
                <div id="submitter" class="p-separator cms-person author-group">
                    <h3 class="u-h4 article-span u-mb-8 u-font-sans-sang">Article submitted</h3>
                	<?php $this->assign('submitter', $this->_tpl_vars['article']->getUser()); ?>
                	<?php $this->assign('submitterFirstName', $this->_tpl_vars['submitter']->getFirstName()); ?>
                    <?php $this->assign('submitterMiddleName', $this->_tpl_vars['submitter']->getMiddleName()); ?>
                    <?php $this->assign('submitterLastName', $this->_tpl_vars['submitter']->getLastName()); ?>
                	<?php $this->assign('submitterAffiliation', $this->_tpl_vars['submitter']->getLocalizedAffiliation()); ?>
                	<?php $this->assign('submitterCountry', $this->_tpl_vars['submitter']->getCountry()); ?>
                	<?php $this->assign('profileImage', $this->_tpl_vars['submitter']->getSetting('profileImage')); ?>
                    <?php if ($this->_tpl_vars['profileImage']): ?>
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img height="auto" width="150" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="lazyload Avatar__img is-inside-mask" src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
" />
                    </figure>
                    <?php else: ?>
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img height="auto" width="150" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="lazyload Avatar__img is-inside-mask" <?php if ($this->_tpl_vars['submitter']->getGender() == 'M'): ?>src="//assets.sangia.org/static/images/contactPersonM.png"<?php elseif ($this->_tpl_vars['submitter']->getGender() == 'F'): ?>src="//assets.sangia.org/static/images/contactPersonF.png"<?php else: ?>src="//scholar.google.co.id/citations/images/avatar_scholar_128.png"<?php endif; ?> />
                    </figure>
                    <?php endif; ?>
                	<div id="submitter" class="overview">
                    	 <h3 class="u-h4 u-fonts-sans u-mb-4" itemprop="name"><span class="u-hide text" itemprop="full-name"><?php echo $this->_tpl_vars['submitter']->getFullName(); ?>
</span><?php if ($this->_tpl_vars['submitterFirstName'] !== $this->_tpl_vars['submitterLastName']): ?><span class="text" itemprop="given-name"><?php echo $this->_tpl_vars['submitterFirstName']; ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['submitterMiddleName']): ?><span class="text" itemprop="middle-name"><?php echo $this->_tpl_vars['submitterMiddleName']; ?>
</span><?php endif; ?><span class="text" itemprop="surname"><?php echo $this->_tpl_vars['submitterLastName']; ?>
</span></h3>
                    	 <dl><?php if ($this->_tpl_vars['submitterAffiliation']): ?><?php echo $this->_tpl_vars['submitterAffiliation']; ?>
<?php else: ?>[<i>Affiliation Not available</i>]<?php endif; ?><?php if ($this->_tpl_vars['submitter']->getCountry()): ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getCountryLocalized())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></dl>
                    	 <?php if ($this->_tpl_vars['submitter']->getData('orcid')): ?><svg viewBox="0 0 72 72" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="vertical-align: middle;" width="16" height="14"><!-- Generator: sketchtool 53.1 (72631) - https://sketchapp.com --><title>Orcid logo</title><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="hero" transform="translate(-924.000000, -72.000000)" fill-rule="nonzero"><g id="Group-4"><g id="vector_iD_icon" transform="translate(924.000000, 72.000000)"><path d="M72,36 C72,55.884375 55.884375,72 36,72 C16.115625,72 0,55.884375 0,36 C0,16.115625 16.115625,0 36,0 C55.884375,0 72,16.115625 72,36 Z" id="Path" fill="#000" fill-opacity="0.9"></path><g id="Group" transform="translate(18.868966, 12.910345)" fill="#FFFFFF"><polygon id="Path" points="5.03734929 39.1250878 0.695429861 39.1250878 0.695429861 9.14431787 5.03734929 9.14431787 5.03734929 22.6930505 5.03734929 39.1250878"></polygon><path d="M11.409257,9.14431787 L23.1380784,9.14431787 C34.303014,9.14431787 39.2088191,17.0664074 39.2088191,24.1486995 C39.2088191,31.846843 33.1470485,39.1530811 23.1944669,39.1530811 L11.409257,39.1530811 L11.409257,9.14431787 Z M15.7511765,35.2620194 L22.6587756,35.2620194 C32.49858,35.2620194 34.7541226,27.8438084 34.7541226,24.1486995 C34.7541226,18.1301509 30.8915059,13.0353795 22.4332213,13.0353795 L15.7511765,13.0353795 L15.7511765,35.2620194 Z" id="Shape"></path><path d="M5.71401206,2.90182329 C5.71401206,4.441452 4.44526937,5.72914146 2.86638958,5.72914146 C1.28750978,5.72914146 0.0187670918,4.441452 0.0187670918,2.90182329 C0.0187670918,1.33420133 1.28750978,0.0745051096 2.86638958,0.0745051096 C4.44526937,0.0745051096 5.71401206,1.36219458 5.71401206,2.90182329 Z" id="Path"></path></g></g></g></g></g></svg><a rel="noreferrer noopener" title="Go to view <?php echo ((is_array($_tmp=$this->_tpl_vars['fullname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 orcid-ID profile" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="icon extern anchor"> <span class="anchor-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></a><?php endif; ?>
                    	 <p class="u-mb-0"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width=".9em" height=".7em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg> <a rel="noreferrer noopener" class="icon anchor" title="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getData('email'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getData('email'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" ><span class="anchor-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getData('email'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></a></p>
                    </div>
            	</div>
            	
            	<div id="correspondence" class="cms-person author-group">
                    <h3 class="u-h4 article-span u-mb-8 u-font-sans-sang">Correspondence</h3>
                    <?php $this->assign('authors', $this->_tpl_vars['article']->getAuthors()); ?>
                    <?php $this->assign('authorProfileImages', $this->_tpl_vars['article']->getAuthorProfileImages()); ?>
                    <?php $this->assign('authorUserDataMap', $this->_tpl_vars['article']->getAuthorUserDataMap()); ?>
                    <?php $_from = $this->_tpl_vars['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['author']):
        $this->_foreach['authors']['iteration']++;
?>
                    <?php $this->assign('contact', $this->_tpl_vars['author']->getData('primaryContact')); ?>
                	<?php $this->assign('fullname', $this->_tpl_vars['author']->getFullName()); ?>
                	<?php $this->assign('authorFirstName', $this->_tpl_vars['author']->getFirstName()); ?>
                	<?php $this->assign('authorMiddleName', $this->_tpl_vars['author']->getMiddleName()); ?>
                	<?php $this->assign('authorLastName', $this->_tpl_vars['author']->getLastName()); ?>
                	<?php $this->assign('authorAffiliation', $this->_tpl_vars['author']->getLocalizedAffiliation()); ?>
                    <?php $this->assign('authorCountry', $this->_tpl_vars['author']->getCountry()); ?>
                    <?php if (((is_array($_tmp=$this->_tpl_vars['author']->getData('primaryContact'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?>
                    
                                        <?php $this->assign('userProfileImage', ""); ?>
                    <?php $this->assign('currentAuthor', $this->_tpl_vars['author']); ?>
                    <?php $this->assign('currentAuthorEmail', $this->_tpl_vars['currentAuthor']->getEmail()); ?>
                    <?php $this->assign('currentAuthorOrcid', $this->_tpl_vars['currentAuthor']->getData('orcid')); ?>
                    
                                        <?php $this->assign('currentAuthorId', $this->_tpl_vars['author']->getId()); ?>
                    
                                        <?php $this->assign('profileImageData', $this->_tpl_vars['authorProfileImages'][$this->_tpl_vars['currentAuthorId']]); ?>
                    <?php $this->assign('userData', $this->_tpl_vars['authorUserDataMap'][$this->_tpl_vars['currentAuthorId']]); ?>                    
                                        <?php $this->assign('hasImage', $this->_tpl_vars['profileImageData']); ?>
                    
                    <?php if ($this->_tpl_vars['hasImage'] && $this->_tpl_vars['profileImageData']['uploadName']): ?>
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img height="auto" width="150" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['fullname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="lazyload Avatar__img is-inside-mask" src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImageData']['uploadName']; ?>
" />
                    </figure>
                    <?php else: ?>
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img height="auto" width="150" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['submitter']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="lazyload Avatar__img is-inside-mask" <?php if ($this->_tpl_vars['userData']['gender'] == 'M'): ?>src="//assets.sangia.org/static/images/contactPersonM.png"<?php elseif ($this->_tpl_vars['userData']['gender'] == 'F'): ?>src="//assets.sangia.org/static/images/contactPersonF.png"<?php else: ?>src="//assets.sangia.org/static/images/default_203.jpg"<?php endif; ?> />
                    </figure>    
                    <?php endif; ?>
                	<div id="correspondence" class="overview">
                    	 <h3 class="u-h4 u-fonts-sans u-mb-4" itemprop="name"><?php if ($this->_tpl_vars['authorFirstName'] !== $this->_tpl_vars['authorLastName']): ?><span class="text" itemprop="given-name"><?php echo $this->_tpl_vars['authorFirstName']; ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['authorMiddleName']): ?><span class="text" itemprop="middle-name"><?php echo $this->_tpl_vars['authorMiddleName']; ?>
</span><?php endif; ?><span class="text" itemprop="surname"><?php echo $this->_tpl_vars['authorLastName']; ?>
</span></h3>
                    	 <dl><?php if (((is_array($_tmp=$this->_tpl_vars['authorAffiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['authorAffiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>[<i>Affiliation Not Available</i>]<?php endif; ?><?php if ($this->_tpl_vars['author']->getCountry()): ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getCountryLocalized())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>.</dl>
                    	 <?php if ($this->_tpl_vars['author']->getData('orcid')): ?><svg viewBox="0 0 72 72" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="vertical-align: middle;" width="16" height="14"><!-- Generator: sketchtool 53.1 (72631) - https://sketchapp.com --><title>Orcid logo</title><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="hero" transform="translate(-924.000000, -72.000000)" fill-rule="nonzero"><g id="Group-4"><g id="vector_iD_icon" transform="translate(924.000000, 72.000000)"><path d="M72,36 C72,55.884375 55.884375,72 36,72 C16.115625,72 0,55.884375 0,36 C0,16.115625 16.115625,0 36,0 C55.884375,0 72,16.115625 72,36 Z" id="Path" fill="#000" fill-opacity="0.9"></path><g id="Group" transform="translate(18.868966, 12.910345)" fill="#FFFFFF"><polygon id="Path" points="5.03734929 39.1250878 0.695429861 39.1250878 0.695429861 9.14431787 5.03734929 9.14431787 5.03734929 22.6930505 5.03734929 39.1250878"></polygon><path d="M11.409257,9.14431787 L23.1380784,9.14431787 C34.303014,9.14431787 39.2088191,17.0664074 39.2088191,24.1486995 C39.2088191,31.846843 33.1470485,39.1530811 23.1944669,39.1530811 L11.409257,39.1530811 L11.409257,9.14431787 Z M15.7511765,35.2620194 L22.6587756,35.2620194 C32.49858,35.2620194 34.7541226,27.8438084 34.7541226,24.1486995 C34.7541226,18.1301509 30.8915059,13.0353795 22.4332213,13.0353795 L15.7511765,13.0353795 L15.7511765,35.2620194 Z" id="Shape"></path><path d="M5.71401206,2.90182329 C5.71401206,4.441452 4.44526937,5.72914146 2.86638958,5.72914146 C1.28750978,5.72914146 0.0187670918,4.441452 0.0187670918,2.90182329 C0.0187670918,1.33420133 1.28750978,0.0745051096 2.86638958,0.0745051096 C4.44526937,0.0745051096 5.71401206,1.36219458 5.71401206,2.90182329 Z" id="Path"></path></g></g></g></g></g></svg><a rel="noreferrer noopener" title="Go to view <?php echo ((is_array($_tmp=$this->_tpl_vars['fullname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 orcid-ID profile" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="icon extern anchor"> <span class="anchor-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></a><?php endif; ?>
                    	 <p class="u-mb"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width=".9em" height=".7em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg> <a rel="noreferrer noopener" class="icon anchor" title="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getData('email'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getData('email'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" ><span class="anchor-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getData('email'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></a></p>
                    </div>
                    <?php endif; ?><?php endforeach; endif; unset($_from); ?>
            	</div>
            	
            	<div class="PageDivider"></div>
			    
                <div id="editors" class="cms-person author-group">
            	    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "article/editorial.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            	</div>
            	<?php endif; ?>
            	
			    <section class="u-hide externals">
			        <span class="__dimensions_badge_embed__" data-doi="<?php echo ((is_array($_tmp=$this->_tpl_vars['articleDOI'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" data-legend="always" data-style="small_circle"></span>
			        <div class="p-separator PageDivider"></div>
			    </section>

			    <section class="SidePanel p-separator link">
			     	<?php if ($this->_tpl_vars['galleys'] && $this->_tpl_vars['galley']->isPdfGalley()): ?>
			     	<a rel="noreferrer noopener" target="_blank" title="Download this article in PDF format" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'download','path' => ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])))), $this);?>
" class="file anchor" <?php if ($this->_tpl_vars['galley']->getRemoteURL()): ?>target="_blank"<?php else: ?>target="_blank"<?php endif; ?>><span class="anchor-text">Download PDF fulltext</span></a>
			        <?php endif; ?>
			        <a class="u-hide external anchor" rel="noreferrer noopener" style="hover:none" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'rt','op' => 'captureCite','path' => ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galleyId']) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galleyId']))), $this);?>
" title="Capture citation with citation styles"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">Capture citation</span></button></a>
			        <a class="u-hide external anchor" rel="noreferrer noopener" style="hover:none" href="javascript:document.getElementsByTagName('body')[0].appendChild(document.createElement('script')).setAttribute('src','https://www.mendeley.com/minified/bookmarklet.js');" title="Save Article to Mendeley"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">Add to Mendeley</span></button></a>
			        <a class="external anchor" rel="noreferrer noopener" style="hover:none" href="//www.mendeley.com/import/?url=<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal'])), $this);?>
" target="_blank" title="Save Article to Mendeley"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">Export to Mendeley</span></button></a>
			        <a class="external anchor" rel="noreferrer noopener" style="hover:none" href="javascript:document.getElementsByTagName('body')[0].appendChild(document.createElement('script')).setAttribute('src','https://www.zotero.org/bookmarklet/loader.js');" title="Save Article to Zotero"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">Add to Zotero</span></button></a>
			    </section>
			    
                <div class="readers-over-time__StyledWrapper-t9k07n-0 dvZQao">
                    <div class="card__Container-dzzdii-1 hxUoxW">
                        <h4 data-name="readers over time-title" id="readers over time-title" class="card__StyledTitle-dzzdii-0 TaRhM u-font-sans-sang">Readers over time</h4>
                        <div class="recharts-responsive-container">
                            <script type='text/javascript' id='clustrmaps' src='//cdn.clustrmaps.com/map_v2.js?cl=cccccc&w=a&t=n&d=zXaAZvoJOoxUn0MY_Zu8Kg7gUFIU3iXEuLA_nr_ajiM&co=ffffff&cmo=ff5353&cmn=2fb62f&ct=333333'></script>
                        </div>
                    </div>
                </div>
                <div class="p-separator PageDivider"></div> 								
                <div class="js-ad sideAds">
                    <aside class="leftAds adsbox c-ad c-ad--300x250 u-mt-16" data-component-mpu="">
                        <div class="c-ad__inner">
                            <p class="c-ad__label">Advertisement</p>
                            <style>
                            <?php echo '@media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                            '; ?>

                            </style>
                            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721" crossorigin="anonymous"></script>
                            <!-- Sangia_Publishing_ads -->
                            <ins class="adsbygoogle c-ad--300x250"
                                style="display:inline-block;width:100%;height:auto"
                                data-ad-client="ca-pub-8416265824412721"
                                data-ad-slot="2738201692">
                            </ins>
                            <script>
                            <?php echo '
                                 (adsbygoogle = window.adsbygoogle || []).push({});
                            '; ?>

                            </script>
                        </div>
                    </aside>
                </div>
			</div>
			<?php endif; ?>
    	</div>

		<div class="u-show-from-md col-lg-6 col-md-8 pad-right side-r">
			<aside class="RelatedContent c-article--view c-article--view-m">
                
               <?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getShowTitle() && $this->_tpl_vars['issue']->getVolume()): ?>
                <section class="SpecialIssueArticles SidePanel" id="special-issue-articles">
			        <details class="details-summary-2566262091 u-margin-s-bottom" open="">
			            <summary class="details-summary-label-617948308">    
        			        <header id="citing-articles-header" class="side-panel-header">
        			            <div class="u-font-sans" type="button">
        			                <div class="part-of-issue link">
        			                    <span class="button-link-text"><h2 class="part-of-issue-text u-h4 special-issue--value u-font-sans">Part of special issue</h2></span>
        			                    <svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415 icon"><path fill="#d54449" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
        			                 </div>
        			             </div>
        			         </header>
        			    </summary>
                        <div class="special-issue u-margin-s-top metrics-details u-font-sans">
                            <?php if ($this->_tpl_vars['currentJournal']): ?><a rel="noreferrer noopener" class="anchor part-of-issue-title file special-issue" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issue']->getBestIssueId($this->_tpl_vars['currentJournal'])), $this);?>
"><span class="anchor-text u-font-sans-sang title-issue"><?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedTitle($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span></a><?php endif; ?>
                            <?php if ($this->_tpl_vars['issue']->getLocalizedDescription()): ?><span class="part-of-issue-editors"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedDescription())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 200, "") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 200, "")); ?>
</span><?php endif; ?>
                            <?php if ($this->_tpl_vars['issue']->getLocalizedCoverPageDescription()): ?><div class="part-of-issue-editors text-gray-light"><span><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getLocalizedCoverPageDescription())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</span></div><?php endif; ?>
                        </div>
                        <a class="external anchor metrics-details" rel="noreferrer noopener" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'view','path' => $this->_tpl_vars['issue']->getBestIssueId($this->_tpl_vars['currentJournal'])), $this);?>
"><button class="button-alternative ViewFullIssue button-alternative-primary" type="button" id="download-full-issue"><svg focusable="false" viewBox="0 0 54 128" width="30" height="30" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">View special issue</span></button></a>
                        <?php if ($this->_tpl_vars['issueGalleys'] && $this->_tpl_vars['issueGalley']->isPdfGalley()): ?>
                        <a class="external anchor metrics-details" rel="noreferrer noopener" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'download','path' => ((is_array($_tmp=$this->_tpl_vars['issue']->getBestIssueId())) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['issueGalley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['issueGalley']->getBestGalleyId($this->_tpl_vars['currentJournal'])))), $this);?>
"><button class="button-alternative DownloadFullIssue button-alternative-primary" type="button" id="download-full-issue"><svg focusable="false" viewBox="0 0 54 128" width="30" height="30" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">Download full issue</span></button></a>
                        <?php endif; ?>
                    </details>
                </section>
                <?php endif; ?>
                
                <div class="tooltip__Wrapper-sc-1lc2ea0-0 side-article-impact<?php if ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getShowTitle()): ?> u-mt-16<?php endif; ?>">
                    <ul class="nav-sangia u-text-center">
                        <li class="impact-data u-mb-16">
                            <script async type="application/javascript" src="https://cdn.scite.ai/badge/scite-badge-latest.min.js">
                            </script>                                
                            <div class="scite">
                                <span class="scite-badge" data-doi="<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" data-layout="horizontal" data-show-zero="true" data-small="false" data-show-labels="false" data-tally-show="true">
                                </span>
                            </div>
                        </li>                            
                        <li class="impact-data" title="" data-original-title="The total view count is updated once a day, so not to worry if you don't see immediate results.">
                            <span class="subtitle-text" style="font-size: 13px;color: #999;display: block;margin-top: 6px;margin-bottom: 6px;">Since <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getDateStatusModified())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
</span>
                            <?php if ($this->_tpl_vars['galley']): ?>
                                <?php if ($this->_tpl_vars['galley']->isPdfGalley() && $this->_tpl_vars['galley']->isHTMLGalley()): ?>
                                <span class="title-number"><?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x + y + z",'x' => $this->_tpl_vars['article']->getViews(),'y' => $this->_tpl_vars['galley']->getViews($this->_tpl_vars['isPdfGalley']),'z' => $this->_tpl_vars['galley']->getViews($this->_tpl_vars['isHTMLGalley'])), $this))) ? $this->_run_mod_handler('number_format', true, $_tmp, 0) : number_format($_tmp, 0));?>
</span>
                                <?php elseif ($this->_tpl_vars['galley']->isPdfGalley() || $this->_tpl_vars['galley']->isHTMLGalley()): ?>
                                <span class="title-number"><?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x + y",'x' => $this->_tpl_vars['article']->getViews(),'y' => $this->_tpl_vars['galley']->getViews()), $this))) ? $this->_run_mod_handler('number_format', true, $_tmp, 0) : number_format($_tmp, 0));?>
</span>
                                <?php else: ?>
                                <span class="title-number"><?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x + y + z",'x' => $this->_tpl_vars['article']->getViews(),'y' => $this->_tpl_vars['galley']->getViews($this->_tpl_vars['isPdfGalley']),'z' => $this->_tpl_vars['galley']->getViews($this->_tpl_vars['isHTMLGalley'])), $this))) ? $this->_run_mod_handler('number_format', true, $_tmp, 0) : number_format($_tmp, 0));?>
</span>
                                <?php endif; ?>
                            <?php else: ?>
                            <span class="title-number"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getViews())) ? $this->_run_mod_handler('number_format', true, $_tmp, 0) : number_format($_tmp, 0)); ?>
</span>
                            <?php endif; ?>
                            <span class="title-text">total views</span>
                        </li>
                        <li class="hidden-sm hidden-xs">
                            <div class="altmetric-icon">
                                <div class='altmetric-embed' data-badge-type='1' data-doi='<?php echo ((is_array($_tmp=$this->_tpl_vars['articleDOI'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
' data-link-target="new" data-track-label="<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
"></div>
                            </div>
                        </li>
                    </ul>
                    <a type="button" title="Article Impact by Altmetrics" class="u-hide btn-sangia btn-default hidden-sm hidden-xs btn-impact" data-test-id="view-article-impact" href="https://www.altmetric.com/details/doi/<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" target="_blank"><span class="icon-impact"><i class="fa fa-line-chart"></i></span>View Article Impact</a>
                    <a type="button" aria-label="Article Impact by PlumX Metrics" class="btn-sangia btn-default hidden-sm hidden-xs btn-impact" data-test-id="view-article-impact" href="https://plu.mx/plum/a/?doi=<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" target="_blank" aria-haspopup="false"><span class="icon-impact"><i class="fa fa-line-chart"></i></span>View Article Impact</a>
                </div>

                <div class="js-ad sideAds">
                    <aside class="rightAds adsbox u-mt-16" data-component-mpu="">
                        <div class="c-ad__inner">
                            <p class="c-ad__label">Advertisement</p>
                            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721" crossorigin="anonymous"></script>
                            <!-- Sangia_Publishing_ads -->
                            <ins class="adsbygoogle c-ad--300x250"
                                style="display:inline-block;width:300px;height:auto"
                                data-ad-client="ca-pub-8416265824412721"
                                data-ad-slot="2738201692">
                            </ins>
                            <script>
                            <?php echo '
                                 (adsbygoogle = window.adsbygoogle || []).push({});
                            '; ?>

                            </script>
                        </div>
                    </aside>
                </div>
                
                <section class="learned"></section>
                
			    <?php if (( ! $this->_tpl_vars['subscriptionRequired'] || $this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN || $this->_tpl_vars['subscribedUser'] || $this->_tpl_vars['subscribedDomain'] || ( $this->_tpl_vars['subscriptionExpiryPartial'] && $this->_tpl_vars['articleExpiryPartial'][$this->_tpl_vars['articleId']] ) )): ?>
			     	<?php $this->assign('hasAccess', 1); ?>
			    <?php else: ?>
			     	<?php $this->assign('hasAccess', 0); ?>
			    <?php endif; ?>			    			    
                <section class="SidePanel u-margin-s-bottom articleInfo">
			        <details class="details-summary-2566262091 u-margin-s-bottom" open="">
			            <summary class="details-summary-label-617948308">    
        			        <header id="citing-articles-header" class="side-panel-header">
        			            <div class="u-font-sans" type="button">
        			                <span class="button-link-text">
        			                    <h2 class="section-title u-h4 u-font-sans-sang">Article Metrics <span class="fileSize u-show-inline-from-lg">designed by Wizdam</span></h2>
        			                </span>
        			                <svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415 icon"><path fill="#d54449" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
        			            </div>    			            
        			        </header>
			            </summary>                    
                        <div class="articleInfo metrics-details u-margin-s-top">
                        <ul class="p-section-title__item">
                            <li class="p-section-title__item readers" type="button">
                                <span class="p-section-item--name">Readers <span class="fileSize u-show-inline-from-lg"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.abstract",'from' => ($this->_tpl_vars['metaLocale'])), $this);?>
</span></span>
                                <?php if ($this->_tpl_vars['galley'] && $this->_tpl_vars['galley']->isHTMLGalley() && $this->_tpl_vars['galley']->isPdfGalley()): ?>
                                <span class="p-section-item--value"><?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x + y",'x' => $this->_tpl_vars['article']->getViews(),'y' => $this->_tpl_vars['galleys']->getViews()), $this))) ? $this->_run_mod_handler('number_format', true, $_tmp, 0) : number_format($_tmp, 0));?>
</span>
                                <?php else: ?>
                                <span class="p-section-item--value"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getViews())) ? $this->_run_mod_handler('number_format', true, $_tmp, 0) : number_format($_tmp, 0)); ?>
</span><?php endif; ?>
                            </li>
                            <?php $_from = $this->_tpl_vars['article']->getGalleys(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['galleyList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['galleyList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['galley']):
        $this->_foreach['galleyList']['iteration']++;
?>
                            <?php if ($this->_tpl_vars['galley'] && $this->_tpl_vars['galley']->isPdfGalley()): ?>
                            <li class="p-section-title__item download" type="button">
                                <span class="p-section-item--name">Download <span class="fileSize u-show-inline-from-lg">fulltext <?php echo $this->_tpl_vars['galley']->getLabel(); ?>
</span></span>
                                <span class="p-section-item--value"><?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getViews())) ? $this->_run_mod_handler('number_format', true, $_tmp, 0) : number_format($_tmp, 0)); ?>
</span>
                            </li>
                            <?php elseif ($this->_tpl_vars['galley'] && $this->_tpl_vars['galley']->isHTMLGalley()): ?>
                            <li class="p-section-title__item readers" type="button">
                                <span class="p-section-item--name">Readers <span class="fileSize u-show-inline-from-lg">fulltext <?php echo $this->_tpl_vars['galley']->getLabel(); ?>
</span></span>
                                <span class="p-section-item--value"><?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getViews())) ? $this->_run_mod_handler('number_format', true, $_tmp, 0) : number_format($_tmp, 0)); ?>
</span>
                            </li>
                            <?php else: ?>    
                            <li class="p-section-title__item download" type="button">
                                <span class="p-section-item--name">Download <span class="fileSize u-show-inline-from-lg">fulltext <?php echo $this->_tpl_vars['galley']->getLabel(); ?>
</span></span>
                                <span class="p-section-item--value"><?php echo $this->_tpl_vars['galley']->getViews(); ?>
</span>
                            </li>
                            <?php endif; ?>
                            <?php endforeach; endif; unset($_from); ?>
                            <li class="p-section-title__item reviews" type="button"><a class="anchor anchor-external-link" rel="noreferrer noopener" title="View article citation in Google Scholar" href="//scholar.google.co.id/scholar_lookup?title=<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank">
                                <span class="p-section-item--name anchor-text">Citations</span><span class="fileSize u-show-inline-from-lg"> in Google Scholar</span>
                                <span class="p-section-item--value google" title="View article citation in Google Scholar" >N/A</span></a></li>
                            <li class="p-section-title__item dimension citations" type="button">
                                <span class="p-section-item--name">Citations <span class="fileSize u-show-inline-from-lg">by Dimension</span></span>
                                <span class="p-section-item--value __dimensions_badge_embed__" data-doi="<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" data-style="small_rectangle"></span></li>
                            <li class="p-section-title__item mentions" type="button">
                                <link rel="preload" href="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js" as="script"><script type="text/javascript" src="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js"></script>
                                <span class="p-section-item--name">Mentions <span class="fileSize u-show-inline-from-lg">by Altmetric</span></span>
                                <?php if ($this->_tpl_vars['pubId']): ?>
                                <span data-badge-popover="left" data-badge-type="medium-bar" data-doi="<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" class="altmetric-embed p-section-item--value" data-link-target="new"><?php echo ((is_array($_tmp=$this->_tpl_vars['articleDOI'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                                <?php else: ?>
                                <span data-badge-popover="left" data-badge-type="medium-bar" data-doi="<?php echo ((is_array($_tmp=$this->_tpl_vars['articleDOI'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="p-section-item--value altmetric-embed" data-link-target="new">Altmetric badge</span>
                                <?php endif; ?>
                            </li>
                        </ul>
                        </div>
                    </details>
                </section>
			    
                <section class="u-hide SidePanel link externals">
			    	<div class="js-shown u-padding-s-bottom">
                        <?php if ($this->_tpl_vars['sharingEnabled']): ?>
                        <!-- start AddThis -->
                        <!-- Go to www.addthis.com to customize your tools --> <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5b1b8c88d8801350"></script> 
                        <!-- end AddThis -->
                        <?php else: ?>
                            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "article/shared.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			    		<?php endif; ?>
			    	</div>
			    </section>
			    
			    <?php $this->assign('doi', $this->_tpl_vars['article']->getStoredPubId('doi')); ?>
			    <?php if ($this->_tpl_vars['article']->getPubId('doi')): ?>
			    <section class="u-hide SidePanel p-separator link u-font-sans-sang">
			        <a class="external anchor" rel="noreferrer noopener" href="https://www.readcube.com/articles/<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" target="_blank" title="Go to view fulltext epdf format in ReadCube (Dimension)"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">View article in ReadCube</span></button></a>
			        <a class="external anchor" rel="noreferrer noopener" style="hover:none" href="https://publons.com/follow/publon/create/<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" title="Go to view article in Publons (Web of Science)" target="_blank"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">View article in Publons</span></button></a>
			    </section>
			    <?php endif; ?>

			    <section class="SidePanel externals u-margin-s-bottom details-44861495">
			        <details class="details-summary-2566262091 u-margin-s-bottom">
			            <summary class="details-summary-label-617948308">    
        			        <header id="citing-articles-header" class="side-panel-header">
        			            <div class="u-font-sans" type="button">
        			                <span class="button-link-text">
        			                    <h3 class="section-title u-h4 u-font-sans-sang">Field Citation Ratio<span class="fileSize u-show-inline-from-lg"> by <span class="Dimension">Dimension</span></span></h3>   
        			                </span>
        			                <svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415 icon"><path fill="#d54449" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
        			            </div>    			            
        			        </header>
			            </summary>
			            <div class="u-margin-s-top metrics-details">
			                <span class="__dimensions_badge_embed__" data-doi="<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" data-legend="always" data-style="small_circle"></span>
			            </div>
			        </details>
			    </section>
			    
			    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "article/recom-article.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			    
			    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "article/citedby_doi.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			    
				<section class="SidePanel u-margin-s-bottom details-44861495">
			        <details class="details-summary-2566262091 u-margin-s-bottom" open="">
			            <summary class="details-summary-label-617948308">
        					<?php if ($this->_tpl_vars['blockTitle']): ?>
        					<header id="metrics-header" class="side-panel-header">
        					    <div class="u-font-sans" type="button"><span class="button-link-text">
        					        <h3 class="section-title u-h4 u-font-sans-sang"><?php echo $this->_tpl_vars['blockTitle']; ?>
 <span class="fileSize u-show-inline-from-lg">Powered by <span class="PlumX">PlumX</span></span></h3></span>
        					        <svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415 icon"><path fill="#d54449" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
        					    </div>
        					</header>
        					<?php else: ?>
        					<header id="metrics-header" class="side-panel-header">
        					    <div class="u-font-sans" type="button"><span class="button-link-text">
        					        <h2 class="section-title u-h4 u-font-sans-sang">Article Metrics <span class="fileSize u-show-inline-from-lg">Powered by <span class="PlumX">PlumX</span></span></h2></span>
        					        <svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415 icon"><path fill="#d54449" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
        					    </div>
        					</header>
        					<?php endif; ?>
        				</summary>
        					<?php if ($this->_tpl_vars['htmlPrefix']): ?><?php echo $this->_tpl_vars['htmlPrefix']; ?>
<?php endif; ?>
        					<!-- Plum Analytics -->
        					<div class="u-margin-s-top metrics-details" aria-hidden="false" aria-describedby="metrics-header">
            					<link rel="preload" href="//cdn.plu.mx/widget-summary.js" as="script">
            					<script type="text/javascript" src="//cdn.plu.mx/widget-summary.js"></script>
            					<a rel="noreferrer noopener" href="https://plu.mx/plum/a/?doi=<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" class="plum-sciencedirect-theme plumx-summary" data-site="plum" data-lang="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLanguage())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" loading="lazy" data-track-label="<?php echo $this->_tpl_vars['article']->getPubId('doi'); ?>
" <?php if ($this->_tpl_vars['hideWhenEmpty']): ?>data-hide-when-empty="<?php echo ((is_array($_tmp=$this->_tpl_vars['hideWhenEmpty'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php endif; ?><?php if ($this->_tpl_vars['hidePrint']): ?>data-hide-print="<?php echo ((is_array($_tmp=$this->_tpl_vars['hidePrint'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php endif; ?><?php if ($this->_tpl_vars['orientation']): ?>data-orientation="<?php echo ((is_array($_tmp=$this->_tpl_vars['orientation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php endif; ?><?php if ($this->_tpl_vars['popup']): ?>data-popup="<?php echo ((is_array($_tmp=$this->_tpl_vars['popup'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php endif; ?><?php if ($this->_tpl_vars['border']): ?>data-border="<?php echo ((is_array($_tmp=$this->_tpl_vars['border'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php endif; ?><?php if ($this->_tpl_vars['width']): ?>data-width="<?php echo ((is_array($_tmp=$this->_tpl_vars['width'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php endif; ?>></a>
        					</div>
        					<!-- /Plum Analytics -->
        					<?php if ($this->_tpl_vars['htmlSuffix']): ?><?php echo $this->_tpl_vars['htmlSuffix']; ?>
<?php endif; ?>
        			</details>
				</section>
				
				<?php if ($this->_tpl_vars['article']->getLocalizedSubject(null) == ( ! $this->_tpl_vars['article']->getHideAuthor() == @AUTHOR_TOC_DEFAULT ) || $this->_tpl_vars['article']->getHideAuthor() == @AUTHOR_TOC_SHOW): ?>
			    <?php else: ?>		
				<div class="js-ad sideAds">
                    <aside class="rightAds adsbox c-ad c-ad--300x250 u-mt-16" data-component-mpu="">
                        <div class="c-ad__inner">
                            <p class="c-ad__label">Advertisement</p>
                            <style>
                            <?php echo '@media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                            '; ?>

                            </style>
                            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721" crossorigin="anonymous"></script>
                            <!-- Sangia_Publishing_ads -->
                            <ins class="adsbygoogle c-ad--300x250"
                                style="display:inline-block;width:300px;height:auto"
                                data-ad-client="ca-pub-8416265824412721"
                                data-ad-slot="2738201692">
                            </ins>
                            <script>
                            <?php echo '
                                 (adsbygoogle = window.adsbygoogle || []).push({});
                            '; ?>

                            </script>
                        </div>
                    </aside>
                </div>
                <?php endif; ?>
                
			</aside>
		</div>	
	</div>

<article class="col-lg-12 col-md-16 pad-left pad-right c-side" role="main" lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLanguage())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
