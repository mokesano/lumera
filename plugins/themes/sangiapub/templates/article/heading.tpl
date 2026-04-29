{**
 * templates/article/head.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article View -- Head component.
 *
 *}
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
                        {if $siteCategoriesEnabled}
                        <a class="c-header__link" href="/" data-test="siteindex-link" data-track="click" data-track-action="open sangia research index" data-track-label="link">
                            <span>{translate key="navigation.otherJournals"}</span>
                        </a>
                        {/if}{* $categoriesEnabled *}
                    </li>
                    {if !$currentJournal || $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
                    <li class="c-header__item c-header__item--padding c-header__item--pipe">
                        <a class="c-header__link c-header__link--search" href="{url page="search" op="titles"}" data-header-expander="" data-test="search-link" data-track="click" data-track-action="open search tray" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                            <span>{translate key="navigation.search"}</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
                        </a>
                        <div id="search-menu" class="c-header__dropdown c-header__dropdown--full-width has-tethered u-js-hide" role="banner" data-track-component="sangia-split-header">
                            {include file="common/navsearch.tpl"}
                        </div>
                    </li>
                    {/if}
                    {if $isUserLoggedIn}
                    <li class="c-header__item c-header__item--padding c-header__item--snid-account-widget">
                        <nav class="c-account-nav" aria-labelledby="account-nav-title">
                            <a id="my-account" class="c-header__link eds-c-header__link c-account-nav__anchor" href="{url page="user"}" data-test="login-link" data-track="click" data-track-action="my account" data-track-category="sangia-split-header" data-track-label="link" aria-expanded="true">
                                {if $userData}
                                <span>{if $userData.firstName|escape !== $userData.lastName|escape}{$userData.firstName|escape|substr:0:1}.{/if}{if $userData.middleName} {$userData.middleName|escape|substr:0:1}.{/if} {$userData.lastName|escape}</span>
                                <div class="Ibar__userLogged u-ml-8">
                                    <figure class="Avatar Avatar--size-32">
                                        {if $userData.profileImage && $userData.profileImage.uploadName}<img src="{$sitePublicFilesDir}/{$userData.profileImage.uploadName}" alt="{$userData.firstName|escape}{if $userData.middleName} {$userData.middleName|escape}{/if} {$userData.lastName|escape}" class="Avatar__img is-inside-mask">{else}<img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/default_203.jpg?as=webp" alt="{$userData.firstName|escape}">{/if}
                                    </figure>
                                </div>
                                {else}
                                <span>{translate key="user.myAccount"}</span>
                                <svg id="account-icon" role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                                {/if}
                                {if $unreadNotifications > 0}
                                <span class="notification-icon" id="notification-count">{$unreadNotifications}</span>
                                {/if}
                                <svg class="chevron" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            <div id="account-nav-menu" class="c-account-nav__menu c-account-nav__menu--right c-account-nav__menu--chevron-right u-js-hide">
                                {if $userData}
                                <div class="Sangia__user__dropdown c-account-nav__menu-header">
                                    <div class="Sangia__user__avatar">
                                        <figure class="Avatar Avatar--size-96">
                                            {if $userData.profileImage && $userData.profileImage.uploadName}
                                            <img class="Avatar__img is-inside-mask" src="{$sitePublicFilesDir}/{$userData.profileImage.uploadName}?as=webp" alt="{$userData.firstName|escape} {$userData.lastName|escape}">
                                            {elseif $userData.gender == 'F'}
                                            <img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/contactPersonF.png?as=webp" alt="{$userData.firstName|escape} {$userData.lastName|escape}">{elseif $userData.gender == 'M'}<img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/contactPersonM.png?as=webp" alt="{$userData.firstName|escape} {$userData.lastName|escape}">{else}<img class="Avatar__img is-inside-mask" src="//assets.sangia.org/static/images/default_203.jpg?as=webp" alt="{$userData.firstName|escape} {$userData.lastName|escape}">{/if}
                                        </figure>
                                        {if $userData.is_verified}
                                        <span class="verified badge icon" title="Your account is valid"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><circle cx="50" cy="50" fill="#1DA1F2" r="40"></circle><path d="M30 55 L45 70 L70 35" stroke="#ffffff" stroke-width="12" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg></span>{else}<span class="unverified badge icon" title="Your account needs to be validated"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" height="18" width="18"><circle cx="50" cy="50" r="45" fill="#ffffff" stroke="#cccccc" stroke-width="2"></circle><path d="M35 35 L65 65" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path><path d="M35 65 L65 35" stroke="#FF0000" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"></path></svg></span>{/if}
                                    </div>
                                    {if $userData.salutation || $userData.suffix}
                                    <div class="Sangia__user__salutation u-font-sangia-sans">{if $userData.salutation}{$userData.salutation|escape} {/if}{if $userData.suffix}— {$userData.suffix|escape}{/if}</div>
                                    {/if}
                                    <div class="Sangia__user__name">{$userData.firstName|escape} {if $userData.middleName} {$userData.middleName|escape} {/if}{$userData.lastName|escape}</div>
                                    <div class="Sangia__user__email">{$userData.email|escape}</div>
                                    <div id="account-nav-title" class="Sangia__user__account u-js-hide">
                                        <span class="u-js-hide">{translate key="common.user.loggedInAs"}</span>
                                        <span id="logged-in-username" data-username="{$loggedInUsername|escape}">{$loggedInUsername|escape}</span>
                                    </div>
                                </div>
                                {/if}
                                <ul class="c-account-nav__menu-list dashoboard user-home">
                                   <li class="c-account-nav__menu-item"><a href="{url page="user"}">Dashoboard</a>
                                   </li>
                                </ul>
                                <ul class="c-account-nav__menu-list">
                                    <li class="c-account-nav__menu-item"><a href="{url page="user" op="viewPublicProfile" path=$userSession->getUserId()|string_format:"%011d"}">{translate key="user.showMyProfile"}</a></li>
                                    <li class="c-account-nav__menu-item u-hide"><a href="{url page="user" op="profile"}">{translate key="user.editMyProfile"}</a></li>
                                    {if $hasOtherJournals}
                                        {if !$showAllJournals}
                                        <li class="c-account-nav__menu-item"><a href="{url journal="index" page="user"}">{translate key="user.showAllJournals"}</a></li>
                                        {/if}
                                    {/if}
                                    {if $currentJournal}
                                        {if $subscriptionsEnabled}
                                        <li class="c-account-nav__menu-item u-hide"><a href="{url page="user" op="subscriptions"}">{translate key="user.manageMySubscriptions"}</a></li>
                                        {/if}
                                    {/if}
                                    {if $currentJournal}
                                        {if $acceptGiftPayments}
                                        <li class="c-account-nav__menu-item u-hide"><a href="{url page="user" op="gifts"}">{translate key="gifts.manageMyGifts"}</a></li>
                                        {/if}
                                    {/if}
                                    {if !$implicitAuth}
                                    <li class="c-account-nav__menu-item"><a href="{url page="user" op="changePassword"}">{translate key="user.changeMyPassword"}</a></li>
                                    {/if}
                                    {if $currentJournal}
                                        {if $journalPaymentsEnabled && $membershipEnabled}
                                            {if $dateEndMembership}
                                    <li class="c-account-nav__menu-item u-hide"><a href="{url page="user" op="payMembership"}">{translate key="payment.membership.renewMembership"}</a> ({translate key="payment.membership.ends"}: {$dateEndMembership|date_format:$dateFormatShort})</li>
                                    {else}
                                    <li class="c-account-nav__menu-item u-hide"><a href="{url page="user" op="payMembership"}">{translate key="payment.membership.buyMembership"}</a></li>
                                            {/if}
                                        {/if}{* $journalPaymentsEnabled && $membershipEnabled *}
                                    {/if}{* $userJournal *}
                                    <li class="c-account-nav__menu-item"><a href="{url page="login" op="signOut"}">{translate key="user.logOut"}</a></li>
                                    
                                    {call_hook name="Templates::User::Index::MyAccount"}
                                    {if $userSession->getSessionVar('signedInAs')}
                                    <li class="c-account-nav__menu-item Login_user_as">
                                        <a id="logout-button" class="c-header__link placeholder" href="{url page="login" op="signOutAsUser"}" style="" data-test="logout-link" data-track="click" data-track-action="logout" data-track-category="nature-150-split-header" data-track-label="link">
                                            <span>Logout as {$userData.firstName|escape|substr:0:1}.{if $userData.middleName|escape}{$userData.middleName|escape|substr:0:1}.{/if} {$userData.lastName|escape}</span>
                                            <svg aria-hidden="true" focusable="false" role="img" width="22" height="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="m8.72592184 2.54588137c-.48811714-.34391207-1.08343326-.54588137-1.72592184-.54588137-1.65685425 0-3 1.34314575-3 3 0 1.02947485.5215457 1.96853646 1.3698342 2.51900785l.6301658.40892721v1.02400182l-.79002171.32905522c-1.93395773.8055207-3.20997829 2.7024791-3.20997829 4.8180274v.9009805h-1v-.9009805c0-2.5479714 1.54557359-4.79153984 3.82548288-5.7411543-1.09870406-.71297106-1.82548288-1.95054399-1.82548288-3.3578652 0-2.209139 1.790861-4 4-4 1.09079823 0 2.07961816.43662103 2.80122451 1.1446278-.37707584.09278571-.7373238.22835063-1.07530267.40125357zm-2.72592184 14.45411863h-1v-.9009805c0-2.5479714 1.54557359-4.7915398 3.82548288-5.7411543-1.09870406-.71297106-1.82548288-1.95054399-1.82548288-3.3578652 0-2.209139 1.790861-4 4-4s4 1.790861 4 4c0 1.40732121-.7267788 2.64489414-1.8254829 3.3578652 2.2799093.9496145 3.8254829 3.1931829 3.8254829 5.7411543v.9009805h-1v-.9009805c0-2.1155483-1.2760206-4.0125067-3.2099783-4.8180274l-.7900217-.3290552v-1.02400184l.6301658-.40892721c.8482885-.55047139 1.3698342-1.489533 1.3698342-2.51900785 0-1.65685425-1.3431458-3-3-3-1.65685425 0-3 1.34314575-3 3 0 1.02947485.5215457 1.96853646 1.3698342 2.51900785l.6301658.40892721v1.02400184l-.79002171.3290552c-1.93395773.8055207-3.20997829 2.7024791-3.20997829 4.8180274z" fill-rule="evenodd" fill="#ffffff"></path></svg>
                                        </a>
                                    </li>
                                    {/if}
                                </ul>
                            </div>
                        </nav>
                    </li>
                    {else}
                    <li class="c-header__item c-header__item--padding">
                        <a id="login-button" class="c-header__link placeholder" href="{url page="login"}" style="login" data-test="login-link" data-track="click" data-track-action="login" data-track-category="sangia-split-header" data-track-label="link">
                            <span>Login</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                        </a>
                    </li>
                    {if !$hideRegisterLink}
                    <li class="u-hide c-header__item c-header__item--padding c-header__item--pipe">
                		<a id="register-button" class="c-header__link placeholder" href="{url page="user" op="register"}" style="register" data-test="register-link" data-track="click" data-track-action="register" data-track-category="sangia-split-header" data-track-label="link">
                		    <span>{translate key="navigation.register"}</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                		</a>
                    </li>{/if}
                    {/if}{* $isUserLoggedIn *}
                </ul>
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
                                        {if $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="issue" op="current"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="journal.currentIssue"}</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="issue" op="archive"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Archive Issues</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="search" op="titles"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Titles Index</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="browseSearch" op="sections"}" data-track="click" data-track-label="link" data-test="explore-nav-item">View Section</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="browseSearch" op="identifyTypes"}" data-track="click" data-track-label="link" data-test="explore-nav-item">View Article Type</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="search" op="authors"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Authors Index</a></li>
                                        {/if}
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="siteMap"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.siteMap"}</a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only"><a class="c-header-expander__link" href="//www.facebook.com/SangiaNews" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">Follow us on Facebook</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="https://twitter.com/SangiaNews" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">Follow us on Twitter</a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="notification" op="subscribeMailList"}" rel="nofollow" data-track="click" data-track-action="Sign up for alerts" data-track-external="" data-track-label="link (mobile dropdown)">Sign up for alerts<svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m4 10h2.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-3.08578644l-1.12132034 1.1213203c-.18753638.1875364-.29289322.4418903-.29289322.7071068v.1715729h14v-.1715729c0-.2652165-.1053568-.5195704-.2928932-.7071068l-1.7071068-1.7071067v-3.4142136c0-2.76142375-2.2385763-5-5-5-2.76142375 0-5 2.23857625-5 5zm3 4c0 1.1045695.8954305 2 2 2s2-.8954305 2-2zm-5 0c-.55228475 0-1-.4477153-1-1v-.1715729c0-.530433.21071368-1.0391408.58578644-1.4142135l1.41421356-1.4142136v-3c0-3.3137085 2.6862915-6 6-6s6 2.6862915 6 6v3l1.4142136 1.4142136c.3750727.3750727.5857864.8837805.5857864 1.4142135v.1715729c0 .5522847-.4477153 1-1 1h-4c0 1.6568542-1.3431458 3-3 3-1.65685425 0-3-1.3431458-3-3z" fill="#fff"></path></svg></a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="gateway" op="plugin"}/WebFeedGatewayPlugin/rss" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank"><span>RSS feed</span></a></li>
                                        
                                        {url|assign:"oaiUrl" page="oai"}
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{$oaiUrl}" data-track="click" data-track-action="OAI feed" data-track-label="link" target="_blank"><span>OAI</span></a></li>
                                    </ul>
                                </div>
                            </nav>
                        </li>
                        <li class="c-header__item c-header__item--dropdown-menu">
                            <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                                <span>{translate key="navigation.about"} <span class="c-header__show-text">the journal</span></span>
                                <svg class="details-marker" role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                            </a>
                            <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                                <div class="c-header-expander__container">
                                    <h2 id="Explore-content" class="c-header-expander__heading u-hide">About the journal</h2>
                                    <ul class="c-header-expander__list">
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialTeam"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.editorialTeam"}</a></li>
                                        
                                        {if $peopleGroups}{iterate from=peopleGroups item=peopleGroup}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="displayMembership" path=$peopleGroup->getId()}" data-track="click" data-track-label="link" data-test="explore-nav-item">{$peopleGroup->getLocalizedTitle()|escape}</a></li>
                                        {/iterate}{/if}
                                        {call_hook name="Templates::About::Index::People"}
                                        
                                        {if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="focusAndScope"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.focusAndScope"}</a></li>{/if}
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="sectionPolicies"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.sectionPolicies"}</a></li>
                                        
                                        {call_hook name="Templates::About::Index::Policies"}
                                        
                                        {if $currentJournal->getLocalizedSetting('reviewPolicy') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="peerReviewProcess"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.peerReviewProcess"}</a></li>{/if}
                                        
                                        {if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="publicationFrequency"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.publicationFrequency"}</a></li>{/if}
                                        
                                        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.openAccessPolicy"}</a></li>{/if}
                                        
                                        {foreach key=key from=$currentJournal->getLocalizedSetting('customAboutItems') item=customAboutItem}{if !empty($customAboutItem.title)}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="custom-$key"}" data-track="click" data-track-label="link" data-test="explore-nav-item" style="word-break:break-all">{$customAboutItem.title|escape}</a></li>{/if}{/foreach}
                                        
                                        {foreach from=$navMenuItems item=navItem key=navItemKey}{if $navItem.url != '' && $navItem.name != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{$baseUrl}{$navItem.url|escape}{/if}" data-track="click" data-track-label="link" data-test="explore-nav-item">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</a></li>{/if}{/foreach}
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="archiving"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.archiving"}</a></li>
                                        
                                        {if $currentJournal->getLocalizedSetting('history') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="history"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{if $currentJournal->getSetting('initials')}{translate key="about.history"} of {$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}{else}Journal {translate key="about.history"}{/if}</a></li>
                                        {/if}
                                        
                                        {call_hook name="Templates::Common::Header::Navbar::CurrentJournal"}
                                        {call_hook name="Templates::About::Index::Other"}
                                        
                                        {if not ($currentJournal->getSetting('publisherInstitution') == '' && $currentJournal->getLocalizedSetting('publisherNote') == '' && $currentJournal->getLocalizedSetting('contributorNote') == '' && empty($journalSettings.contributors) && $currentJournal->getLocalizedSetting('sponsorNote') == '' && empty($journalSettings.sponsors))}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="journalSponsorship"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.journalSponsorship"}</a></li>{/if}
                                        
                                        {if $siteCategoriesEnabled}
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="/" data-track="click" data-track-action="OAI feed" data-track-label="link" target="_blank"><span>{translate key="navigation.otherJournals"}</span></a></li>
                                        {/if}{* $categoriesEnabled *}
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="about" op="contact"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.contact"} Information</a></li>
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
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="information" op="authors"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="navigation.infoForAuthors"}</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="submissions"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Submission guidelines</a></li>
                                        
                                        {if $currentJournal->getLocalizedSetting('authorGuidelines') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="submissions" anchor="authorGuidelines"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.authorGuidelines"}</a></li>{/if}
                                        
                                        {if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="submissions" anchor="copyrightNotice"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.copyrightNotice"}</a></li>{/if}
                                        
                                        {if $currentJournal->getLocalizedSetting('privacyStatement') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="submissions" anchor="privacyStatement"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.privacyStatement"}</a></li>{/if}
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="information" op="librarians"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="navigation.infoForLibrarians"}</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="information" op="readers"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="navigation.infoForReaders"}</a></li>
                                        
                                        {if $currentJournal->getSetting('journalPaymentsEnabled') && ($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled'))}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="submissions" anchor="authorFees"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.authorFees"}</a></li>{/if}
                                        
                                        {call_hook name="Templates::About::Index::Submissions"}
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="contact"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.contact"} us</a></li>
                                        
                                        <li class="c-header-expander__item c-header-expander__item--keyline"><a class="c-header-expander__link" href="{url page="author" op="submit"}" target="_blank" data-track="click" data-track-action="Submit manuscript" data-track-label="link" data-track-external="">Submit manuscript<svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m15 0c1.1045695 0 2 .8954305 2 2v5.5c0 .27614237-.2238576.5-.5.5s-.5-.22385763-.5-.5v-5.5c0-.51283584-.3860402-.93550716-.8833789-.99327227l-.1166211-.00672773h-9v3c0 1.1045695-.8954305 2-2 2h-3v10c0 .5128358.38604019.9355072.88337887.9932723l.11662113.0067277h7.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-7.5c-1.1045695 0-2-.8954305-2-2v-10.17157288c0-.53043297.21071368-1.0391408.58578644-1.41421356l3.82842712-3.82842712c.37507276-.37507276.88378059-.58578644 1.41421356-.58578644zm-.5442863 8.18867991 3.3545404 3.35454039c.2508994.2508994.2538696.6596433.0035959.909917-.2429543.2429542-.6561449.2462671-.9065387-.0089489l-2.2609825-2.3045251.0010427 7.2231989c0 .3569916-.2898381.6371378-.6473715.6371378-.3470771 0-.6473715-.2852563-.6473715-.6371378l-.0010428-7.2231995-2.2611222 2.3046654c-.2531661.2580415-.6562868.2592444-.9065605.0089707-.24295423-.2429542-.24865597-.6576651.0036132-.9099343l3.3546673-3.35466731c.2509089-.25090888.6612706-.25227691.9135302-.00001728zm-.9557137-3.18867991c.2761424 0 .5.22385763.5.5s-.2238576.5-.5.5h-6c-.27614237 0-.5-.22385763-.5-.5s.22385763-.5.5-.5zm-8.5-3.587-3.587 3.587h2.587c.55228475 0 1-.44771525 1-1zm8.5 1.587c.2761424 0 .5.22385763.5.5s-.2238576.5-.5.5h-6c-.27614237 0-.5-.22385763-.5-.5s.22385763-.5.5-.5z" fill="#fff"></path></svg></a></li>
                                        
                                        {if $isUserLoggedIn}
                                        <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="user"}" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank"><span>My Account</span></a></li>
                                        {/if}
                                    </ul>
                                </div>
                            </nav>
                        </li>
                    </ul>
                    
                    {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION || $donationEnabled || $currentJournal->getSetting('membershipFee')}
                    <div class="c-header__menu u-ml-16 u-show-lg u-show-at-lg c-header__menu--tools">
                        <div class="c-header__item c-header__item--pipe">
                            {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
                            <a class="c-header__link" href="{url page="about" op="subscriptions"}" data-track="click" data-track-action="subscribe" data-track-label="link" data-test="menu-button-subscribe">
                                <span>Subscribe</span>
                            </a>{/if}{* $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION *}
                            {if $currentJournal->getSetting('donationFeeEnabled')}
                            <a class="c-header__link" href="{url page="donations"}" data-track="click" data-track-action="donation" data-track-label="link" data-test="menu-button-donation">
                                <span>Donation</span>
                            </a>{/if}
                            {if $currentJournal->getSetting('membershipFeeEnabled')}
                            <a class="c-header__link" href="{url page="about" op="memberships"}" data-track="click" data-track-action="membership" data-track-label="link" data-test="menu-button-membership">
                                <span>Members</span>
                            </a>{/if}
                        </div>
                    </div>
                    {/if}
                    
                </div>
                <form class="lm-site-search u-show-from-md" method="GET" id="search-bar" action="{url page="search" op="search"}">
                	<div class="ms-search-field"><input type="text" id="query" name="query" value="" placeholder="Search in this journal" class="lm-search-term"></div>
                	<button type="submit" value="Search" class="uk-button uk-button-primary btn-search">
                	    <svg role="img" aria-hidden="true" focusable="false" height="32" width="32" viewBox="0 0 17 17" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
                	    <svg class="u-hide lm-icon-search" viewBox="0 0 32 32"><path fill="inherit" d="M31.1 26.9l-8.8-8.8c1.1-1.8 1.7-3.9 1.7-6.1 0-6.6-5.4-12-12-12s-12 5.4-12 12 5.4 12 12 12c2.2 0 4.3-0.6 6.1-1.7l8.8 8.8c0.6 0.6 1.4 0.9 2.1 0.9s1.5-0.3 2.1-0.9c1.2-1.2 1.2-3.1 0-4.2zM3 12c0-5 4-9 9-9s9 4 9 9c0 5-4 9-9 9s-9-4-9-9z"></path></svg>
                	</button>
                </form>                    
                <ul class="u-hide c-header__menu c-header__menu--tools">
                    <li class="c-header__item">
                        <a class="c-header__link" href="{url page="notification" op="subscribeMailList"}" rel="nofollow" data-track="click" data-track-action="Sign up for alerts" data-track-label="link (desktop site header)" data-track-external="">
                            <span>Sign up for alerts</span><svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m4 10h2.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-3.08578644l-1.12132034 1.1213203c-.18753638.1875364-.29289322.4418903-.29289322.7071068v.1715729h14v-.1715729c0-.2652165-.1053568-.5195704-.2928932-.7071068l-1.7071068-1.7071067v-3.4142136c0-2.76142375-2.2385763-5-5-5-2.76142375 0-5 2.23857625-5 5zm3 4c0 1.1045695.8954305 2 2 2s2-.8954305 2-2zm-5 0c-.55228475 0-1-.4477153-1-1v-.1715729c0-.530433.21071368-1.0391408.58578644-1.4142135l1.41421356-1.4142136v-3c0-3.3137085 2.6862915-6 6-6s6 2.6862915 6 6v3l1.4142136 1.4142136c.3750727.3750727.5857864.8837805.5857864 1.4142135v.1715729c0 .5522847-.4477153 1-1 1h-4c0 1.6568542-1.3431458 3-3 3-1.65685425 0-3-1.3431458-3-3z" fill="#222"></path></svg>
                        </a>
                    </li>
                    <li class="c-header__item c-header__item--pipe">
                        <a class="c-header__link" href="{url page="gateway" op="plugin"}/WebFeedGatewayPlugin/rss" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank">
                            <span>RSS feed</span>
                        </a>
                    </li>
                    {url|assign:"oaiUrl" page="oai"}
                    <li class="c-header__item c-header__item--pipe">
                        <a class="c-header__link" href="{$oaiUrl}" data-track="click" data-track-action="oai feed" data-track-label="link" target="_blank">
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
        		{if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $articleExpiryPartial.$articleId))}
                    {assign var=hasAccess value=1}
                {else}
                    {assign var=hasAccess value=0}
                {/if}
                
                {if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
        		{foreach from=$article->getGalleys() item=galley name=galleyList}
        		{if $galley->isPdfGalley() && !$galley->isHTMLGalley()}
        		<div id="download-pdf-popover" class="popover PdfDownloadButton download-pdf-popover">
        		    <div id="popover-trigger-download-pdf-popover">
        		        <button id="pdfLink" class="button button-anchor u-padding-0-left" role="button" aria-expanded="false" aria-haspopup="true" aria-label="Download PDF options" type="button">
        		            <svg focusable="false" viewBox="0 0 32 32" width="24" height="24" class="icon icon-pdf-multicolor pdf-icon"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg>
        		            {if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}
        		            <span class="button-text"><a rel="noreferrer noopener" class="pdf-file" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" target="_blank" >
        		                <span id="articleFullText" class="pdf-download-label u-show-inline-from-lg">{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isHTMLGalley() || !$galley->isPdfGalley()}Download fulltext in {$galley->getLabel()|escape}{elseif $galley->getRemoteURL()}Download fulltext {$galley->getLabel()|escape} (Remote){elseif $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_SUBSCRIPTION}Get fulltext {$galley->getLabel()|escape} Access{/if}</span>
        		                <span class="pdf-download-label-short u-hide-from-lg">{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isHTMLGalley()}Download {$galley->getLabel()|escape}{elseif $galley->getRemoteURL()}Download {$galley->getLabel()|escape} (Remote){elseif $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_SUBSCRIPTION}Get {$galley->getLabel()|escape} Access{/if}</span></a>
        		            </span>
        		            {else}
        		            <span class="button-text"><a rel="noreferrer noopener" class="pdf-file" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" target="_blank">
        		                <span id="articleFullText" class="pdf-download-label u-show-inline-from-lg">{if $galley->getRemoteURL()}Download fulltext {$galley->getLabel()|escape} (Remote){else}Download fulltext in {$galley->getLabel()|escape}{/if}</span>
        		                <span class="pdf-download-label-short u-hide-from-lg">{if $galley->getRemoteURL()}Download {$galley->getLabel()|escape} (Remote){else}Download fulltext {$galley->getLabel()|escape}{/if}</span></a>
        		            </span>
        		            {/if}
        		        </button>
                    </div>
                </div>
                {elseif !$galley->isPdfGalley() && !$galley->isHTMLGalley()}
                <div id="check-access-popover" class="popover PdfDownloadButton check-access-popover">
                    <div id="popover-trigger-check-access-popover">
                        <button class="button button-anchor u-padding-0-left" role="button" aria-expanded="false" aria-haspopup="true" type="submit">
                            {if $galley->getRemoteURL() && $galley->getLabel() == "PDF"}
                            <svg focusable="false" viewBox="0 0 32 32" width="24" height="24" class="icon icon-pdf-multicolor pdf-icon"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg>
                            {elseif $galley->getRemoteURL() && $galley->getLabel() == "JPG" || $galley->getRemoteURL() && $galley->getLabel() == "JPEG" || $galley->getLabel() == "JPG" || $galley->getLabel() == "JPEG"}
                            <svg id="icon-jpg" class="icon icon-pdf-multicolor pdf-icon" height="800px" width="800px" viewBox="0 0 512 512" ><path fill="#DEE0E1" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"></path><path fill="#505050" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"></path><polygon fill="#CAD1D8" points="480,224 384,128 480,128 "></polygon><path fill="#d54449" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16  V416z"></path><g><path fill="#FFFFFF" d="M141.968,303.152c0-10.752,16.896-10.752,16.896,0v50.528c0,20.096-9.6,32.256-31.728,32.256   c-10.88,0-19.952-2.96-27.888-13.184c-6.528-7.808,5.76-19.056,12.416-10.88c5.376,6.656,11.136,8.192,16.752,7.936   c7.152-0.256,13.44-3.472,13.568-16.128v-50.528H141.968z"></path><path fill="#FFFFFF" d="M181.344,303.152c0-4.224,3.328-8.832,8.704-8.832H219.6c16.64,0,31.616,11.136,31.616,32.48   c0,20.224-14.976,31.488-31.616,31.488h-21.36v16.896c0,5.632-3.584,8.816-8.192,8.816c-4.224,0-8.704-3.184-8.704-8.816   L181.344,303.152L181.344,303.152z M198.24,310.432v31.872h21.36c8.576,0,15.36-7.568,15.36-15.504   c0-8.944-6.784-16.368-15.36-16.368H198.24z"></path><path fill="#FFFFFF" d="M342.576,374.16c-9.088,7.552-20.224,10.752-31.472,10.752c-26.88,0-45.936-15.344-45.936-45.808   c0-25.824,20.096-45.904,47.072-45.904c10.112,0,21.232,3.44,29.168,11.248c7.792,7.664-3.456,19.056-11.12,12.288   c-4.736-4.608-11.392-8.064-18.048-8.064c-15.472,0-30.432,12.4-30.432,30.432c0,18.944,12.528,30.464,29.296,30.464   c7.792,0,14.448-2.32,19.184-5.76V348.08h-19.184c-11.392,0-10.24-15.616,0-15.616h25.584c4.736,0,9.072,3.584,9.072,7.552v27.248   C345.76,369.568,344.752,371.712,342.576,374.16z"></path></g><path fill="#CAD1D8" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"></path></svg>
        		            {elseif $galley->getRemoteURL() && $galley->getLabel() == "PNG" || $galley->getLabel() == "PNG"}
        		            <svg id="icon-png" height="25" width="25" viewBox="0 0 512 512" class="icon icon-pdf-multicolor pdf-icon"><path d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z" fill="#E2E5E7"></path><path d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z" fill="#2f2f2f"></path><polygon fill="#CAD1D8" points="480,224 384,128 480,128"></polygon><path d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16  V416z" fill="#d54449"></path><g><path d="M92.816,303.152c0-4.224,3.312-8.848,8.688-8.848h29.568c16.624,0,31.6,11.136,31.6,32.496   c0,20.224-14.976,31.472-31.6,31.472H109.68v16.896c0,5.648-3.552,8.832-8.176,8.832c-4.224,0-8.688-3.184-8.688-8.832   C92.816,375.168,92.816,303.152,92.816,303.152z M109.68,310.432v31.856h21.376c8.56,0,15.344-7.552,15.344-15.488   c0-8.96-6.784-16.368-15.344-16.368L109.68,310.432L109.68,310.432z" fill="#FFFFFF"></path><path d="M178.976,304.432c0-4.624,1.024-9.088,7.68-9.088c4.592,0,5.632,1.152,9.072,4.464l42.336,52.976   v-49.632c0-4.224,3.696-8.848,8.064-8.848c4.608,0,9.072,4.624,9.072,8.848v72.016c0,5.648-3.456,7.792-6.784,8.832   c-4.464,0-6.656-1.024-10.352-4.464l-42.336-53.744v49.392c0,5.648-3.456,8.832-8.064,8.832s-8.704-3.184-8.704-8.832v-70.752   H178.976z" fill="#FFFFFF"></path><path d="M351.44,374.16c-9.088,7.536-20.224,10.752-31.472,10.752c-26.88,0-45.936-15.36-45.936-45.808   c0-25.84,20.096-45.92,47.072-45.92c10.112,0,21.232,3.456,29.168,11.264c7.808,7.664-3.456,19.056-11.12,12.288   c-4.736-4.624-11.392-8.064-18.048-8.064c-15.472,0-30.432,12.4-30.432,30.432c0,18.944,12.528,30.448,29.296,30.448   c7.792,0,14.448-2.304,19.184-5.76V348.08h-19.184c-11.392,0-10.24-15.632,0-15.632h25.584c4.736,0,9.072,3.6,9.072,7.568v27.248   C354.624,369.552,353.616,371.712,351.44,374.16z" fill="#FFFFFF"></path></g><path d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z" fill="#CAD1D8"></path></svg>
        		            {elseif $galley->getRemoteURL() && $galley->getLabel() == "GIF" || $galley->getLabel() == "GIF"}
        		            <svg id="icon-gif" class="icon icon-pdf-multicolor pdf-icon" height="800px" width="800px" viewBox="0 0 512 512"><path d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z" fill="#E2E5E7"></path><path d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z" fill="#2f2f2f"></path><polygon points="480,224 384,128 480,128 " fill="#CAD1D8"></polygon><path d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16  V416z" fill="#A066AA"></path><g><path style="fill:#FFFFFF;" d="M199.84,374.16c-9.088,7.536-20.224,10.752-31.472,10.752c-26.88,0-45.936-15.36-45.936-45.808   c0-25.84,20.096-45.92,47.072-45.92c10.112,0,21.232,3.456,29.168,11.264c7.808,7.664-3.456,19.056-11.12,12.288   c-4.736-4.624-11.392-8.064-18.048-8.064c-15.472,0-30.432,12.4-30.432,30.432c0,18.944,12.528,30.448,29.296,30.448   c7.792,0,14.448-2.304,19.184-5.76V348.08h-19.184c-11.392,0-10.24-15.632,0-15.632h25.584c4.736,0,9.072,3.6,9.072,7.568v27.248   C203.024,369.552,202.016,371.712,199.84,374.16z"></path><path style="fill:#FFFFFF;" d="M224.944,303.152c0-10.496,16.896-10.88,16.896,0v73.024c0,10.624-16.896,10.88-16.896,0V303.152z"></path><path style="fill:#FFFFFF;" d="M281.12,312.096v20.336h32.608c4.608,0,9.216,4.608,9.216,9.088c0,4.224-4.608,7.664-9.216,7.664   H281.12v26.864c0,4.48-3.2,7.936-7.68,7.936c-5.632,0-9.072-3.456-9.072-7.936v-72.656c0-4.608,3.456-7.936,9.072-7.936h44.912   c5.632,0,8.96,3.328,8.96,7.936c0,4.096-3.328,8.688-8.96,8.688H281.12V312.096z"></path></g><path d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z" fill="#CAD1D8"></path></svg>
        		            {/if}
                            <span class="button-text"><a rel="noreferrer noopener" class="pdf-file" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" target="_blank">
        		                <span id="articleFullText" class="pdf-download-label u-show-inline-from-lg">{if $galley->getRemoteURL()}Get fulltext {$galley->getLabel()|escape}{else}Download {$galley->getLabel()|escape} file{/if}</span>
        		                <span class="pdf-download-label-short u-hide-from-lg">{if $galley->getRemoteURL()}Get {$galley->getLabel()|escape}{else}Download {$galley->getLabel()|escape}{/if}</span></a>
        		            </span>
                        </button>
                    </div>
                </div>
                {/if}
                {foreachelse}
        	    <div id="check-access-popover" class="popover PdfDownloadButton check-access-popover">
        	       <div id="popover-trigger-check-access-popover">
        	           <button class="button button-anchor u-padding-0-left" role="button" aria-expanded="false" aria-haspopup="true" type="submit"><svg focusable="false" viewBox="0 0 32 32" width="24" height="24" class="icon icon-pdf-multicolor pdf-icon"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg>
        	           <span class="button-text">
        	               {if $layoutFile != ''}
        	               <a rel="noreferrer noopener" class="pdf-file">
        	                   <span class="pdf-download-label u-show-inline-from-lg">PDF Coming soon!</span>
        	                   <span class="pdf-download-label-short u-hide-from-lg">Coming Soon!</span></a>
        	               {elseif $issue && $issue->getPublished()}
        	               <a rel="noreferrer noopener" class="pdf-file" style="cursor: default;">
                                <span class="pdf-download-label u-show-inline-from-lg">No PDF Available</span>
                                <span class="pdf-download-label-short u-hide-from-lg">No PDF</span>
                            </a>
        	               {else}
        	               <a rel="noreferrer noopener" class="pdf-file">
        	                   <span class="pdf-download-label u-show-inline-from-lg">PDF Unavailable!</span>
        	                   <span class="pdf-download-label-short u-hide-from-lg">Not Access</span></a>
        	               <span><a class="anchor" href="https://service.elsevier.com/app/answers/detail/a_id/22801/supporthub/sciencedirect/" target="_blank" title="What are Corrected Proof articles?"><svg focusable="false" viewBox="0 0 114 128" width="16" height="16" class="icon icon-help" style="margin-right:0"><path d="m57 8c-14.7 0-28.5 5.72-38.9 16.1-10.38 10.4-16.1 24.22-16.1 38.9 0 30.32 24.68 55 55 55 14.68 0 28.5-5.72 38.88-16.1 10.4-10.4 16.12-24.2 16.12-38.9 0-30.32-24.68-55-55-55zm0 1e1c24.82 0 45 20.18 45 45 0 12.02-4.68 23.32-13.18 31.82s-19.8 13.18-31.82 13.18c-24.82 0-45-20.18-45-45 0-12.02 4.68-23.32 13.18-31.82s19.8-13.18 31.82-13.18zm-0.14 14c-11.55 0.26-16.86 8.43-16.86 18v2h1e1v-2c0-4.22 2.22-9.66 8-9.24 5.5 0.4 6.32 5.14 5.78 8.14-1.1 6.16-11.78 9.5-11.78 20.5v6.6h1e1v-5.56c0-8.16 11.22-11.52 12-21.7 0.74-9.86-5.56-16.52-16-16.74-0.39-0.01-0.76-0.01-1.14 0zm-4.86 5e1v1e1h1e1v-1e1h-1e1z"></path></svg></a>
        	                   </span>
        	               {/if}
        	           </span>
        	           </button>
        	       </div>
        	    </div>        	    
        	    {/foreach}
        	    {/if}
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
		    {if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
			<div class="TableOfContents u-margin-l-bottom" lang="{$article->getLanguage()|escape}">            	
			    
                <div id="submitter" class="cms-person author-group">
                    <h3 class="u-h5 article-span u-font-sans-sang">Submitted</h3>
                	{assign var="submitter" value=$article->getUser()}
                	{assign var=submitterFirstName value=$submitter->getFirstName()}
                    {assign var=submitterMiddleName value=$submitter->getMiddleName()}
                    {assign var=submitterLastName value=$submitter->getLastName()}
                	{assign var=submitterAffiliation value=$submitter->getLocalizedAffiliation()}
                	{assign var=submitterCountry value=$submitter->getCountry()}
                	{assign var="profileImage" value=$submitter->getSetting('profileImage')}
                    {if $profileImage}
                    <figure class="avatar editor Avatar Avatar--size-80">
                        <img height="auto" width="150" title="{$submitter->getFullName()|escape}" class="lazyload Avatar__img is-inside-mask" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" />
                    </figure>
                    {/if}
                	<div id="submitter" class="overview">
                    	 <h3 class="u-h4 u-fonts-sans" itemprop="name"><span class="u-hide text" itemprop="full-name">{$submitter->getFullName()}</span>{if $submitterFirstName !== $submitterLastName}<span class="text" itemprop="given-name">{$submitterFirstName}</span>{/if}{if $submitterMiddleName}<span class="text" itemprop="middle-name">{$submitterMiddleName}</span>{/if}<span class="text" itemprop="surname">{$submitterLastName}</span></h3>
                    	 <dl>{if $submitterAffiliation|escape}{$submitterAffiliation|escape|nl2br}{else}[<i>Affiliation Not Available</i>]{/if}, {$submitter->getCountryLocalized()|escape}</dl>
                    	 {if $submitter->getData('orcid')}<svg viewBox="0 0 72 72" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" <title>Orcid logo</title><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="hero" transform="translate(-924.000000, -72.000000)" fill-rule="nonzero"><g id="Group-4"><g id="vector_iD_icon" transform="translate(924.000000, 72.000000)"><path d="M72,36 C72,55.884375 55.884375,72 36,72 C16.115625,72 0,55.884375 0,36 C0,16.115625 16.115625,0 36,0 C55.884375,0 72,16.115625 72,36 Z" id="Path" fill="#000" fill-opacity="0.9"></path><g id="Group" transform="translate(18.868966, 12.910345)" fill="#FFFFFF"><polygon id="Path" points="5.03734929 39.1250878 0.695429861 39.1250878 0.695429861 9.14431787 5.03734929 9.14431787 5.03734929 22.6930505 5.03734929 39.1250878"></polygon><path d="M11.409257,9.14431787 L23.1380784,9.14431787 C34.303014,9.14431787 39.2088191,17.0664074 39.2088191,24.1486995 C39.2088191,31.846843 33.1470485,39.1530811 23.1944669,39.1530811 L11.409257,39.1530811 L11.409257,9.14431787 Z M15.7511765,35.2620194 L22.6587756,35.2620194 C32.49858,35.2620194 34.7541226,27.8438084 34.7541226,24.1486995 C34.7541226,18.1301509 30.8915059,13.0353795 22.4332213,13.0353795 L15.7511765,13.0353795 L15.7511765,35.2620194 Z" id="Shape"></path><path d="M5.71401206,2.90182329 C5.71401206,4.441452 4.44526937,5.72914146 2.86638958,5.72914146 C1.28750978,5.72914146 0.0187670918,4.441452 0.0187670918,2.90182329 C0.0187670918,1.33420133 1.28750978,0.0745051096 2.86638958,0.0745051096 C4.44526937,0.0745051096 5.71401206,1.36219458 5.71401206,2.90182329 Z" id="Path"></path></g></g></g></g></g></svg><a rel="noreferrer noopener" title="Go to view {$fullname|escape} orcid-ID profile" href="{$submitter->getData('orcid')|escape}" target="_blank" class="icon extern anchor"> <span class="anchor-text">{$submitter->getData('orcid')|escape}</span></a>{/if}
                    	 <p><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width=".9em" height=".7em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg> <a rel="noreferrer noopener" class="icon anchor" title="mailto:{$submitter->getData('email')|escape}" href="mailto:{$submitter->getData('email')|escape}" target="_blank" ><span class="anchor-text">{$submitter->getData('email')|escape}</span></a></p>
                    </div>
            	<div class="PageDivider"></div>                    
            	</div>
            	
			    <section class="u-hide p-separator externals">
			        <span class="__dimensions_badge_embed__" data-doi="{$article->getPubId('doi')}" data-legend="always" data-style="small_circle"></span>
			    </section>
			    
			    {if $leftSidebarCode || $rightSidebarCode}
    			     {if $leftSidebarCode}{/if}
    			     {if $rightSidebarCode}{$rightSidebarCode}{/if}
			    {/if}
			    
			    <div class="js-ad sideAds">
			        <aside class="leftAds adsbox c-ad c-ad--300x250 u-mt-16" data-component-mpu="">
                        <div class="c-ad__inner">
                            <p class="c-ad__label">Sangia Advertisement</p>
                            <style>
                            {literal}@media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                            {/literal}
                            </style>
                            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721" crossorigin="anonymous"></script>
                            <!-- Sangia_Publishing_ads -->
                            <ins class="adsbygoogle c-ad--300x250"
                                style="display:inline-block;width:100%;height:auto"
                                data-ad-client="ca-pub-8416265824412721"
                                data-ad-slot="2738201692">
                            </ins>
                            <script>
                            {literal}
                                 (adsbygoogle = window.adsbygoogle || []).push({});
                            {/literal}
                            </script>
                        </div>
                    </aside>
                </div>
                
            </div>
            
		    {else}
		    
			<div class="TableOfContents u-margin-l-bottom" lang="{$article->getLanguage()|escape}">
                
				<div id="toc-outline" class="Outline">
				    <h2 class="u-h4 article-span u-font-sans-sang">Article Outline</h2>
    				<ul class="u-padding-xs-bottom text-s u-font-sans-sang">
    			        <li><a class="anchor" href="#abstracts" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="Abstract" rel="noreferrer noopener"><span class="anchor-text">{translate key="article.abstract"}</span></a></li>
    			        <li><a class="anchor" href="#articleSubject" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="Keywords" rel="noreferrer noopener"><span class="anchor-text">{translate key="article.subject"}</span></a></li>
                    {if $article->getLocalizedSubject(null)}	
                    	{if $galleys}
                        {if $galley->isHTMLGalley()}
    			        <li><a href="#Introduction" title="Introduction" rel="noreferrer noopener">Introduction</a></li>
    			        <li><a href="#Method" title="Materials and Method" rel="noreferrer noopener">Materials and Method</a></li>
    			        <li><a href="#Results" title="Results" rel="noreferrer noopener">Results</a></li>
    			        <li><a href="#Discussion" title="Discussion" rel="noreferrer noopener">Discussion</a></li>
    			        <li><a href="#Conclusion" title="Conclusion" rel="noreferrer noopener">Conclusion</a></li>
    			        <li><a href="#Conclusion" title="Declaration" rel="noreferrer noopener">Declaration</a></li>
    			        <li><a href="#References" title="References" rel="noreferrer noopener">{translate key="article.references"}</a></li>
    			        {/if}
    			        <li><span title="Introduction">Introduction</span></li>
    			        <li><span title="Materials and Method">Materials and Method</span></li>
    			        <li><span title="Results">Results</span></li>
    			        <li><span title="Discussion">Discussion</span></li>
    			        <li><span title="Conclusion">Conclusion</span></li>
    			        {if $article->getLocalizedSponsor()}
    			        <li><a class="anchor" href="#Declaration" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="Funding Information" rel="noreferrer noopener"><span class="anchor-text">Funding Information</span></a></li>{/if}
    			        {if $journalRt->getSupplementaryFiles() && is_a($article, 'PublishedArticle') && $article->getSuppFiles()}
    			        <li><a class="anchor" href="#SuppFiles" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="{translate key="rt.suppFiles"}" rel="noreferrer noopener"><span class="anchor-text">{translate key="rt.suppFiles"}</span></a></li>{/if}
    			        <li><a class="anchor" href="#References" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="{translate key="submission.citations"}" rel="noreferrer noopener"><span class="anchor-text">{translate key="submission.citations"}</span></a></li>
    			        {else}
    			        <li><span title="Introduction">Introduction</span></li>
    			        <li><span title="Materials and Method">Materials and Method</span></li>
    			        <li><span title="Results">Results</span></li>
    			        <li><span title="Discussion">Discussion</span></li>
    			        <li><span title="Conclusion">Conclusion</span></li>
    			        {if $article->getLocalizedSponsor()}
    			        <li><a href="#Declaration" class="anchor" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="Funding Information" rel="noreferrer noopener"><span class="anchor-text">Funding Information</span></a></li>{/if}
    			        {if $journalRt->getSupplementaryFiles() && is_a($article, 'PublishedArticle') && $article->getSuppFiles()}
    			        <li><a href="#SuppFiles" class="anchor" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="{translate key="rt.suppFiles"}" rel="noreferrer noopener"><span class="anchor-text">{translate key="rt.suppFiles"}</span></a></li>{/if}
    			        <li><a href="#References" class="anchor" data-aa-button="sd:product:journal:article:type=anchor:name=outlinelink" title="{translate key="submission.citations"}" rel="noreferrer noopener"><span class="anchor-text">{translate key="submission.citations"}</span></a></li>
    			        {/if}
    			    {/if}   
    			    </ul>
    			    <br />
                </div>
			    <div class="PageDivider"></div>
                
                {assign var="displayCoverIssue" value=$issue->getShowCoverPage($locale)}
                
                {* Hitung jumlah gambar dari semua sumber terlebih dahulu *}
                {assign var="coverCount" value=0}
                {if $showCoverPage}
                    {assign var="coverCount" value=1}
                {/if}
                
                {assign var="galleyImageCount" value=0}
                {if $article->getGalleys() && $galley->isHTMLGalley() && $galley->getImageFiles()}
                    {assign var="galleyImageCount" value=$galley->getImageFiles()|@count}
                {/if}
                
                {* Hitung jumlah gambar dari extras *}
                {assign var="suppImageCount" value=0}
                {foreach from=$article->getSuppFiles() item=suppFile}
                    {assign var="fileName" value=$suppFile->getOriginalFileName()|escape}
                    {if $fileName|regex_replace:"/^.*\.(jpg|jpeg|png|gif)$/i":"\\1"|lower == 'jpg' || 
                        $fileName|regex_replace:"/^.*\.(jpg|jpeg|png|gif)$/i":"\\1"|lower == 'jpeg' || 
                        $fileName|regex_replace:"/^.*\.(jpg|jpeg|png|gif)$/i":"\\1"|lower == 'png' || 
                        $fileName|regex_replace:"/^.*\.(jpg|jpeg|png|gif)$/i":"\\1"|lower == 'gif'}
                        {assign var="suppImageCount" value=$suppImageCount+1}
                    {/if}
                {/foreach}
                
                {* Hitung total *}
                {assign var="totalImageCount" value=$coverCount+$galleyImageCount+$suppImageCount}
                
                {if $showCoverPage || $article->getGalleys() && $galley->isHTMLGalley() && $galley->getImageFiles() || $suppImageCount > 0}
                <div class="Figures u-mb-16" id="toc-figures">
                    <h2 class="u-h4 u-font-sans-sang">Figures <span class="count">({$totalImageCount})</span></h2>
                    <ol>
                        {* Tampilkan cover jika ada *}
                        {if $showCoverPage}
                        <li><span><div><img loading="lazy" itemprop="image" alt="{$article->getLocalizedCoverPageAltText()|escape}" src="{$publicFilesDir}/{$article->getLocalizedFileName()|escape}" style="max-width: 219px; max-height: 105px;"></div></span>
                        </li>
                        {/if}
                        
                        {* Tampilkan gambar dari galley *}
                        {if $article->getGalleys() && $galley->isHTMLGalley()}
                            {foreach name=images from=$galley->getImageFiles() item=imageFile key=key}
                            <li{if ($key + $coverCount) > 6} class="figure-item-hidden"{/if}><span><div><img loading="lazy" itemprop="image" alt="Fig. {$key+1}" src="{url page="editor" op="viewFile" path=$articleId|to_array:$imageFile->getFileId()}" style="max-width: 140px; max-height: 163px;"></div></span>
                            </li>
                            {/foreach}
                        {/if}
                        
                        {* Tampilkan gambar dari extras *}
                        {assign var="figureCounter" value=$coverCount+$galleyImageCount}
                        {foreach from=$article->getSuppFiles() item=suppFile key=key}
                            {assign var="fileName" value=$suppFile->getOriginalFileName()|escape}
                            {if $fileName|regex_replace:"/^.*\.(jpg|jpeg|png|gif)$/i":"\\1"|lower == 'jpg' || 
                                $fileName|regex_replace:"/^.*\.(jpg|jpeg|png|gif)$/i":"\\1"|lower == 'jpeg' || 
                                $fileName|regex_replace:"/^.*\.(jpg|jpeg|png|gif)$/i":"\\1"|lower == 'png' || 
                                $fileName|regex_replace:"/^.*\.(jpg|jpeg|png|gif)$/i":"\\1"|lower == 'gif'}
                                
                                {assign var="figureCounter" value=$figureCounter+1}
                                <li{if $figureCounter > 6} class="figure-item-hidden"{/if}><span><div>
                                    <img loading="lazy" itemprop="image" 
                                         alt="{$suppFile->getSuppFileTitle()|escape}" 
                                         src="{url page="article" op="downloadSuppFile" path=$article->getBestArticleId()|to_array:$suppFile->getBestSuppFileId($currentJournal)}" 
                                         style="max-width: 140px; max-height: 163px;" />
                                    <div style="font-size: 0.8em;">{$fileName}</div>
                                </div></span></li>
                            {/if}
                        {/foreach}
                    </ol>
                    
                    {if $totalImageCount > 6}
                    <button class="button button-anchor" data-aa-button="show-figures" type="button" 
                            onclick="document.getElementById('toc-figures').classList.toggle('show-all-figures'); this.querySelector('.button-text').textContent = this.querySelector('.button-text').textContent === 'Show all Figures' ? 'Show fewer Figures' : 'Show all Figures';">
                        <span class="button-text text-s">Show all Figures</span>
                        <svg focusable="false" viewBox="0 0 92 128" height="20" width="17.25" class="icon icon-navigate-down">
                            <path d="m1 51l7-7 38 38 38-38 7 7-45 45z"></path>
                        </svg>
                    </button>
                    {/if}
                </div>
                <div class="PageDivider"></div>
                {/if}
                
                <div class="u-hide Tables" id="toc-tables">
                    <h2 class="u-h4 u-font-sans-sang">Tables <span class="count">(1)</span></h2>
                    <ol class="u-padding-s-bottom">
                        <li><span title="{$article->getLocalizedTitle()|strip_unsafe_html}"><svg focusable="false" viewBox="0 0 98 128" width="18.375" height="24" class="icon icon-table"><path d="m54 68h32v32h-32v-32zm-42 0h32v32h-32v-32zm0-42h32v32h-32v-32zm42 0h32v32h-32v-32zm-52 84h94v-94h-94v94z"></path></svg>Table 1</span>
                        </li>
                    </ol>
                    <div class="PageDivider sideAds">
                        <aside class="leftAds adsbox c-ad c-ad--300x250 u-mt-16" data-component-mpu="">
                            <div class="c-ad__inner">
                                <p class="c-ad__label">Advertisement</p>
                                <style>
                                {literal}
                                @media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                                {/literal}
                                </style>
                                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721" crossorigin="anonymous"></script>
                                <!-- Sangia_Publishing_ads -->
                                <ins class="adsbygoogle c-ad--300x250"
                                    style="display:inline-block;width:100%;height:auto"
                                    data-ad-client="ca-pub-8416265824412721"
                                    data-ad-slot="2738201692">
                                </ins>
                                <script>
                                {literal}
                                     (adsbygoogle = window.adsbygoogle || []).push({});
                                {/literal}
                                </script>
                            </div>
                        </aside>    
                    </div>
                </div>
                
                {if $journalRt->getSupplementaryFiles() && is_a($article, 'PublishedArticle') && $article->getSuppFiles()}
                <div class="Extras p-separator" id="toc-subfiles">
                    <h2 class="u-h4 article-span u-font-sans-sang">
                        Extras <span class="count">({$article->getSuppFiles()|@count})</span>
                    </h2>
                    <ol class="u-padding-s-bottom">
                        {foreach from=$article->getSuppFiles() item=suppFile key=key}
                        <li class="toc-list-entry-outline-padding{if $key >= 4} extras-item-hidden{/if}">
                            <span class="anchor u-text-truncate anchor-default anchor-has-colored-icon" title="{$suppFile->getSuppFileTitle()|escape}">
                                <svg focusable="false" viewBox="0 0 94 128" width="17.625" height="24" class="icon icon-text-document">
                                    <path d="m35.6 1e1c-5.38 0-10.62 1.92-14.76 5.4-9.1 7.68-18.84 20.14-18.84 32.1v70.5h9e1v-15.99-2.01-4e1 -17.64-32.36h-56.4zm0 1e1h46.4v22.36 17.64 4e1 2.01 5.99h-7e1v-49c0-6.08 4.92-11 11-11h17v-2e1h-6c-2.2 0-4 1.8-4 4v6h-7c-3.32 0-6.44 0.78-9.22 2.16 2.46-5.62 7.28-11.86 13.5-17.1 2.34-1.98 5.3-3.06 8.32-3.06zm-13.6 38v1e1h5e1v-1e1h-5e1zm0 2e1v1e1h5e1v-1e1h-5e1z"></path>
                                </svg>
                                <span class="anchor-text">{$key+1}. Supplementary file ({$suppFile->getNiceFileSize()})</span>
                            </span>
                        </li>
                        {/foreach}
                    </ol>
                    
                    {if $article->getSuppFiles()|@count > 4}
                    <button class="button button-anchor" data-aa-button="show-extras" type="button"
                            onclick="document.getElementById('toc-subfiles').classList.toggle('show-all-extras'); this.querySelector('.button-text').textContent = this.querySelector('.button-text').textContent === 'Show all Extras' ? 'Show fewer Extras' : 'Show all Extras';">
                        <span class="button-text text-s">Show all Extras</span>
                        <svg focusable="false" viewBox="0 0 92 128" height="20" width="17.25" class="icon icon-navigate-down">
                            <path d="m1 51l7-7 38 38 38-38 7 7-45 45z"></path>
                        </svg>
                    </button>
                    {/if}
                    
                    <div class="u-hide PageDivider"></div>
                </div>
                {/if}
            	
            	{if $article->getLocalizedSubject(null)}
                <div id="submitter" class="p-separator cms-person author-group">
                    <h3 class="u-h4 article-span u-mb-8 u-font-sans-sang">Article submitted</h3>
                	{assign var="submitter" value=$article->getUser()}
                	{assign var=submitterFirstName value=$submitter->getFirstName()}
                    {assign var=submitterMiddleName value=$submitter->getMiddleName()}
                    {assign var=submitterLastName value=$submitter->getLastName()}
                	{assign var=submitterAffiliation value=$submitter->getLocalizedAffiliation()}
                	{assign var=submitterCountry value=$submitter->getCountry()}
                	{assign var="profileImage" value=$submitter->getSetting('profileImage')}
                    {if $profileImage}
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img height="auto" width="150" title="{$submitter->getFullName()|escape}" class="lazyload Avatar__img is-inside-mask" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" />
                    </figure>
                    {else}
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img height="auto" width="150" title="{$submitter->getFullName()|escape}" class="lazyload Avatar__img is-inside-mask" {if $submitter->getGender() == "M"}src="//assets.sangia.org/static/images/contactPersonM.png"{elseif $submitter->getGender() == "F"}src="//assets.sangia.org/static/images/contactPersonF.png"{else}src="//scholar.google.co.id/citations/images/avatar_scholar_128.png"{/if} />
                    </figure>
                    {/if}
                	<div id="submitter" class="overview">
                    	 <h3 class="u-h4 u-fonts-sans u-mb-4" itemprop="name"><span class="u-hide text" itemprop="full-name">{$submitter->getFullName()}</span>{if $submitterFirstName !== $submitterLastName}<span class="text" itemprop="given-name">{$submitterFirstName}</span>{/if}{if $submitterMiddleName}<span class="text" itemprop="middle-name">{$submitterMiddleName}</span>{/if}<span class="text" itemprop="surname">{$submitterLastName}</span></h3>
                    	 <dl>{if $submitterAffiliation}{$submitterAffiliation}{else}[<i>Affiliation Not available</i>]{/if}{if $submitter->getCountry()}, {$submitter->getCountryLocalized()|escape}{/if}</dl>
                    	 {if $submitter->getData('orcid')}<svg viewBox="0 0 72 72" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="vertical-align: middle;" width="16" height="14"><!-- Generator: sketchtool 53.1 (72631) - https://sketchapp.com --><title>Orcid logo</title><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="hero" transform="translate(-924.000000, -72.000000)" fill-rule="nonzero"><g id="Group-4"><g id="vector_iD_icon" transform="translate(924.000000, 72.000000)"><path d="M72,36 C72,55.884375 55.884375,72 36,72 C16.115625,72 0,55.884375 0,36 C0,16.115625 16.115625,0 36,0 C55.884375,0 72,16.115625 72,36 Z" id="Path" fill="#000" fill-opacity="0.9"></path><g id="Group" transform="translate(18.868966, 12.910345)" fill="#FFFFFF"><polygon id="Path" points="5.03734929 39.1250878 0.695429861 39.1250878 0.695429861 9.14431787 5.03734929 9.14431787 5.03734929 22.6930505 5.03734929 39.1250878"></polygon><path d="M11.409257,9.14431787 L23.1380784,9.14431787 C34.303014,9.14431787 39.2088191,17.0664074 39.2088191,24.1486995 C39.2088191,31.846843 33.1470485,39.1530811 23.1944669,39.1530811 L11.409257,39.1530811 L11.409257,9.14431787 Z M15.7511765,35.2620194 L22.6587756,35.2620194 C32.49858,35.2620194 34.7541226,27.8438084 34.7541226,24.1486995 C34.7541226,18.1301509 30.8915059,13.0353795 22.4332213,13.0353795 L15.7511765,13.0353795 L15.7511765,35.2620194 Z" id="Shape"></path><path d="M5.71401206,2.90182329 C5.71401206,4.441452 4.44526937,5.72914146 2.86638958,5.72914146 C1.28750978,5.72914146 0.0187670918,4.441452 0.0187670918,2.90182329 C0.0187670918,1.33420133 1.28750978,0.0745051096 2.86638958,0.0745051096 C4.44526937,0.0745051096 5.71401206,1.36219458 5.71401206,2.90182329 Z" id="Path"></path></g></g></g></g></g></svg><a rel="noreferrer noopener" title="Go to view {$fullname|escape} orcid-ID profile" href="{$submitter->getData('orcid')|escape}" target="_blank" class="icon extern anchor"> <span class="anchor-text">{$submitter->getData('orcid')|escape}</span></a>{/if}
                    	 <p class="u-mb-0"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width=".9em" height=".7em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg> <a rel="noreferrer noopener" class="icon anchor" title="mailto:{$submitter->getData('email')|escape}" href="mailto:{$submitter->getData('email')|escape}" target="_blank" ><span class="anchor-text">{$submitter->getData('email')|escape}</span></a></p>
                    </div>
            	</div>
            	
            	<div id="correspondence" class="cms-person author-group">
                    <h3 class="u-h4 article-span u-mb-8 u-font-sans-sang">Correspondence</h3>
                    {assign var=authors value=$article->getAuthors()}
                    {assign var=authorProfileImages value=$article->getAuthorProfileImages()}
                    {assign var=authorUserDataMap value=$article->getAuthorUserDataMap()}
                    {foreach from=$authors item=author name=authors key=i}
                    {assign var="contact" value=$author->getData('primaryContact')}
                	{assign var=fullname value=$author->getFullName()}
                	{assign var=authorFirstName value=$author->getFirstName()}
                	{assign var=authorMiddleName value=$author->getMiddleName()}
                	{assign var=authorLastName value=$author->getLastName()}
                	{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}
                    {assign var=authorCountry value=$author->getCountry()}
                    {if $author->getData('primaryContact')|escape}
                    
                    {* Inisialisasi variabel *}
                    {assign var="userProfileImage" value=""}
                    {assign var="currentAuthor" value=$author}
                    {assign var="currentAuthorEmail" value=$currentAuthor->getEmail()}
                    {assign var="currentAuthorOrcid" value=$currentAuthor->getData('orcid')}
                    
                    {* Cari user berdasarkan ORCID atau email *}
                    {assign var="currentAuthorId" value=$author->getId()}
                    
                    {* Tentukan apakah author memiliki gambar *}
                    {assign var="profileImageData" value=$authorProfileImages[$currentAuthorId]}
                    {assign var="userData" value=$authorUserDataMap[$currentAuthorId]}                    
                    {* Tentukan apakah author memiliki gambar *}
                    {assign var="hasImage" value=$profileImageData}
                    
                    {if $hasImage && $profileImageData.uploadName}
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img height="auto" width="150" title="{$fullname|escape}" class="lazyload Avatar__img is-inside-mask" src="{$sitePublicFilesDir}/{$profileImageData.uploadName}" />
                    </figure>
                    {else}
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img height="auto" width="150" title="{$submitter->getFullName()|escape}" class="lazyload Avatar__img is-inside-mask" {if $userData.gender == "M"}src="//assets.sangia.org/static/images/contactPersonM.png"{elseif $userData.gender == "F"}src="//assets.sangia.org/static/images/contactPersonF.png"{else}src="//assets.sangia.org/static/images/default_203.jpg"{/if} />
                    </figure>    
                    {/if}
                	<div id="correspondence" class="overview">
                    	 <h3 class="u-h4 u-fonts-sans u-mb-4" itemprop="name">{if $authorFirstName !== $authorLastName}<span class="text" itemprop="given-name">{$authorFirstName}</span>{/if}{if $authorMiddleName}<span class="text" itemprop="middle-name">{$authorMiddleName}</span>{/if}<span class="text" itemprop="surname">{$authorLastName}</span></h3>
                    	 <dl>{if $authorAffiliation|escape}{$authorAffiliation|escape}{else}[<i>Affiliation Not Available</i>]{/if}{if $author->getCountry()}, {$author->getCountryLocalized()|escape}{/if}.</dl>
                    	 {if $author->getData('orcid')}<svg viewBox="0 0 72 72" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="vertical-align: middle;" width="16" height="14"><!-- Generator: sketchtool 53.1 (72631) - https://sketchapp.com --><title>Orcid logo</title><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="hero" transform="translate(-924.000000, -72.000000)" fill-rule="nonzero"><g id="Group-4"><g id="vector_iD_icon" transform="translate(924.000000, 72.000000)"><path d="M72,36 C72,55.884375 55.884375,72 36,72 C16.115625,72 0,55.884375 0,36 C0,16.115625 16.115625,0 36,0 C55.884375,0 72,16.115625 72,36 Z" id="Path" fill="#000" fill-opacity="0.9"></path><g id="Group" transform="translate(18.868966, 12.910345)" fill="#FFFFFF"><polygon id="Path" points="5.03734929 39.1250878 0.695429861 39.1250878 0.695429861 9.14431787 5.03734929 9.14431787 5.03734929 22.6930505 5.03734929 39.1250878"></polygon><path d="M11.409257,9.14431787 L23.1380784,9.14431787 C34.303014,9.14431787 39.2088191,17.0664074 39.2088191,24.1486995 C39.2088191,31.846843 33.1470485,39.1530811 23.1944669,39.1530811 L11.409257,39.1530811 L11.409257,9.14431787 Z M15.7511765,35.2620194 L22.6587756,35.2620194 C32.49858,35.2620194 34.7541226,27.8438084 34.7541226,24.1486995 C34.7541226,18.1301509 30.8915059,13.0353795 22.4332213,13.0353795 L15.7511765,13.0353795 L15.7511765,35.2620194 Z" id="Shape"></path><path d="M5.71401206,2.90182329 C5.71401206,4.441452 4.44526937,5.72914146 2.86638958,5.72914146 C1.28750978,5.72914146 0.0187670918,4.441452 0.0187670918,2.90182329 C0.0187670918,1.33420133 1.28750978,0.0745051096 2.86638958,0.0745051096 C4.44526937,0.0745051096 5.71401206,1.36219458 5.71401206,2.90182329 Z" id="Path"></path></g></g></g></g></g></svg><a rel="noreferrer noopener" title="Go to view {$fullname|escape} orcid-ID profile" href="{$author->getData('orcid')|escape}" target="_blank" class="icon extern anchor"> <span class="anchor-text">{$author->getData('orcid')|escape}</span></a>{/if}
                    	 <p class="u-mb"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width=".9em" height=".7em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg> <a rel="noreferrer noopener" class="icon anchor" title="mailto:{$author->getData('email')|escape}" href="mailto:{$author->getData('email')|escape}" target="_blank" ><span class="anchor-text">{$author->getData('email')|escape}</span></a></p>
                    </div>
                    {/if}{/foreach}
            	</div>
            	
            	<div class="PageDivider"></div>
			    
                <div id="editors" class="cms-person author-group">
            	    {include file="article/editorial.tpl"}
            	</div>
            	{/if}
            	
			    <section class="u-hide externals">
			        <span class="__dimensions_badge_embed__" data-doi="{$articleDOI|escape}" data-legend="always" data-style="small_circle"></span>
			        <div class="p-separator PageDivider"></div>
			    </section>

			    <section class="SidePanel p-separator link">
			     	{if $galleys && $galley->isPdfGalley()}
			     	<a rel="noreferrer noopener" target="_blank" title="Download this article in PDF format" href="{url page="article" op="download" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" class="file anchor" {if $galley->getRemoteURL()}target="_blank"{else}target="_blank"{/if}><span class="anchor-text">Download PDF fulltext</span></a>
			        {/if}
			        <a class="u-hide external anchor" rel="noreferrer noopener" style="hover:none" href="{url page="rt" op="captureCite" path=$articleId|to_array:$galleyId}" title="Capture citation with citation styles"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">Capture citation</span></button></a>
			        <a class="u-hide external anchor" rel="noreferrer noopener" style="hover:none" href="javascript:document.getElementsByTagName('body')[0].appendChild(document.createElement('script')).setAttribute('src','https://www.mendeley.com/minified/bookmarklet.js');" title="Save Article to Mendeley"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">Add to Mendeley</span></button></a>
			        <a class="external anchor" rel="noreferrer noopener" style="hover:none" href="//www.mendeley.com/import/?url={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" target="_blank" title="Save Article to Mendeley"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">Export to Mendeley</span></button></a>
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
                            {literal}@media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                            {/literal}
                            </style>
                            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721" crossorigin="anonymous"></script>
                            <!-- Sangia_Publishing_ads -->
                            <ins class="adsbygoogle c-ad--300x250"
                                style="display:inline-block;width:100%;height:auto"
                                data-ad-client="ca-pub-8416265824412721"
                                data-ad-slot="2738201692">
                            </ins>
                            <script>
                            {literal}
                                 (adsbygoogle = window.adsbygoogle || []).push({});
                            {/literal}
                            </script>
                        </div>
                    </aside>
                </div>
			</div>
			{/if}
    	</div>

		<div class="u-show-from-md col-lg-6 col-md-8 pad-right side-r">
			<aside class="RelatedContent c-article--view c-article--view-m">
                
               {if $issue && $issue->getShowTitle() && $issue->getVolume()}
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
                            {if $currentJournal}<a rel="noreferrer noopener" class="anchor part-of-issue-title file special-issue" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}"><span class="anchor-text u-font-sans-sang title-issue">{$issue->getLocalizedTitle($currentJournal)|escape}</span></a>{/if}
                            {if $issue->getLocalizedDescription()}<span class="part-of-issue-editors">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br|truncate:200:""}</span>{/if}
                            {if $issue->getLocalizedCoverPageDescription()}<div class="part-of-issue-editors text-gray-light"><span>{$issue->getLocalizedCoverPageDescription()|strip_unsafe_html|nl2br}</span></div>{/if}
                        </div>
                        <a class="external anchor metrics-details" rel="noreferrer noopener" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}"><button class="button-alternative ViewFullIssue button-alternative-primary" type="button" id="download-full-issue"><svg focusable="false" viewBox="0 0 54 128" width="30" height="30" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">View special issue</span></button></a>
                        {if $issueGalleys && $issueGalley->isPdfGalley()}
                        <a class="external anchor metrics-details" rel="noreferrer noopener" href="{url page="issue" op="download" path=$issue->getBestIssueId()|to_array:$issueGalley->getBestGalleyId($currentJournal)}"><button class="button-alternative DownloadFullIssue button-alternative-primary" type="button" id="download-full-issue"><svg focusable="false" viewBox="0 0 54 128" width="30" height="30" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">Download full issue</span></button></a>
                        {/if}
                    </details>
                </section>
                {/if}
                
                <div class="tooltip__Wrapper-sc-1lc2ea0-0 side-article-impact{if $issue && $issue->getShowTitle()} u-mt-16{/if}">
                    <ul class="nav-sangia u-text-center">
                        <li class="impact-data u-mb-16">
                            <script async type="application/javascript" src="https://cdn.scite.ai/badge/scite-badge-latest.min.js">
                            </script>                                
                            <div class="scite">
                                <span class="scite-badge" data-doi="{$article->getPubId('doi')}" data-layout="horizontal" data-show-zero="true" data-small="false" data-show-labels="false" data-tally-show="true">
                                </span>
                            </div>
                        </li>                            
                        <li class="impact-data" title="" data-original-title="The total view count is updated once a day, so not to worry if you don't see immediate results.">
                            <span class="subtitle-text" style="font-size: 13px;color: #999;display: block;margin-top: 6px;margin-bottom: 6px;">Since {$article->getDateStatusModified()|date_format:"%e %B %Y"}</span>
                            {if $galley}
                                {if $galley->isPdfGalley() && $galley->isHTMLGalley()}
                                <span class="title-number">{math|number_format:0 equation="x + y + z" x=$article->getViews() y=$galley->getViews($isPdfGalley) z=$galley->getViews($isHTMLGalley)}</span>
                                {elseif $galley->isPdfGalley() || $galley->isHTMLGalley()}
                                <span class="title-number">{math|number_format:0 equation="x + y" x=$article->getViews() y=$galley->getViews()}</span>
                                {else}
                                <span class="title-number">{math|number_format:0 equation="x + y + z" x=$article->getViews() y=$galley->getViews($isPdfGalley) z=$galley->getViews($isHTMLGalley)}</span>
                                {/if}
                            {else}
                            <span class="title-number">{$article->getViews()|number_format:0}</span>
                            {/if}
                            <span class="title-text">total views</span>
                        </li>
                        <li class="hidden-sm hidden-xs">
                            <div class="altmetric-icon">
                                <div class='altmetric-embed' data-badge-type='1' data-doi='{$articleDOI|escape}' data-link-target="new" data-track-label="{$article->getPubId('doi')}"></div>
                            </div>
                        </li>
                    </ul>
                    <a type="button" title="Article Impact by Altmetrics" class="u-hide btn-sangia btn-default hidden-sm hidden-xs btn-impact" data-test-id="view-article-impact" href="https://www.altmetric.com/details/doi/{$article->getPubId('doi')}" target="_blank"><span class="icon-impact"><i class="fa fa-line-chart"></i></span>View Article Impact</a>
                    <a type="button" aria-label="Article Impact by PlumX Metrics" class="btn-sangia btn-default hidden-sm hidden-xs btn-impact" data-test-id="view-article-impact" href="https://plu.mx/plum/a/?doi={$article->getPubId('doi')}" target="_blank" aria-haspopup="false"><span class="icon-impact"><i class="fa fa-line-chart"></i></span>View Article Impact</a>
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
                            {literal}
                                 (adsbygoogle = window.adsbygoogle || []).push({});
                            {/literal}
                            </script>
                        </div>
                    </aside>
                </div>
                
                <section class="learned"></section>
                
			    {if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $articleExpiryPartial.$articleId))}
			     	{assign var=hasAccess value=1}
			    {else}
			     	{assign var=hasAccess value=0}
			    {/if}			    			    
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
                                <span class="p-section-item--name">Readers <span class="fileSize u-show-inline-from-lg">{translate key="article.abstract" from="$metaLocale"}</span></span>
                                {if $galley && $galley->isHTMLGalley() && $galley->isPdfGalley()}
                                <span class="p-section-item--value">{math|number_format:0 equation="x + y" x=$article->getViews() y=$galleys->getViews()}</span>
                                {else}
                                <span class="p-section-item--value">{$article->getViews()|number_format:0}</span>{/if}
                            </li>
                            {foreach from=$article->getGalleys() item=galley name=galleyList}
                            {if $galley && $galley->isPdfGalley()}
                            <li class="p-section-title__item download" type="button">
                                <span class="p-section-item--name">Download <span class="fileSize u-show-inline-from-lg">fulltext {$galley->getLabel()}</span></span>
                                <span class="p-section-item--value">{$galley->getViews()|number_format:0}</span>
                            </li>
                            {elseif $galley && $galley->isHTMLGalley()}
                            <li class="p-section-title__item readers" type="button">
                                <span class="p-section-item--name">Readers <span class="fileSize u-show-inline-from-lg">fulltext {$galley->getLabel()}</span></span>
                                <span class="p-section-item--value">{$galley->getViews()|number_format:0}</span>
                            </li>
                            {else}    
                            <li class="p-section-title__item download" type="button">
                                <span class="p-section-item--name">Download <span class="fileSize u-show-inline-from-lg">fulltext {$galley->getLabel()}</span></span>
                                <span class="p-section-item--value">{$galley->getViews()}</span>
                            </li>
                            {/if}
                            {/foreach}
                            <li class="p-section-title__item reviews" type="button"><a class="anchor anchor-external-link" rel="noreferrer noopener" title="View article citation in Google Scholar" href="//scholar.google.co.id/scholar_lookup?title={$article->getLocalizedTitle()|strip_tags|escape}" target="_blank">
                                <span class="p-section-item--name anchor-text">Citations</span><span class="fileSize u-show-inline-from-lg"> in Google Scholar</span>
                                <span class="p-section-item--value google" title="View article citation in Google Scholar" >N/A</span></a></li>
                            <li class="p-section-title__item dimension citations" type="button">
                                <span class="p-section-item--name">Citations <span class="fileSize u-show-inline-from-lg">by Dimension</span></span>
                                <span class="p-section-item--value __dimensions_badge_embed__" data-doi="{$article->getPubId('doi')}" data-style="small_rectangle"></span></li>
                            <li class="p-section-title__item mentions" type="button">
                                <link rel="preload" href="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js" as="script"><script type="text/javascript" src="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js"></script>
                                <span class="p-section-item--name">Mentions <span class="fileSize u-show-inline-from-lg">by Altmetric</span></span>
                                {if $pubId}
                                <span data-badge-popover="left" data-badge-type="medium-bar" data-doi="{$article->getPubId('doi')}" class="altmetric-embed p-section-item--value" data-link-target="new">{$articleDOI|escape}</span>
                                {else}
                                <span data-badge-popover="left" data-badge-type="medium-bar" data-doi="{$articleDOI|escape}" class="p-section-item--value altmetric-embed" data-link-target="new">Altmetric badge</span>
                                {/if}
                            </li>
                        </ul>
                        </div>
                    </details>
                </section>
			    
                <section class="u-hide SidePanel link externals">
			    	<div class="js-shown u-padding-s-bottom">
                        {if $sharingEnabled}
                        <!-- start AddThis -->
                        <!-- Go to www.addthis.com to customize your tools --> <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5b1b8c88d8801350"></script> 
                        <!-- end AddThis -->
                        {else}
                            {include file="article/shared.tpl"}
			    		{/if}
			    	</div>
			    </section>
			    
			    {assign var="doi" value=$article->getStoredPubId('doi')}
			    {if $article->getPubId('doi')}
			    <section class="u-hide SidePanel p-separator link u-font-sans-sang">
			        <a class="external anchor" rel="noreferrer noopener" href="https://www.readcube.com/articles/{$article->getPubId('doi')}" target="_blank" title="Go to view fulltext epdf format in ReadCube (Dimension)"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">View article in ReadCube</span></button></a>
			        <a class="external anchor" rel="noreferrer noopener" style="hover:none" href="https://publons.com/follow/publon/create/{$article->getPubId('doi')}" title="Go to view article in Publons (Web of Science)" target="_blank"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="36" height="36" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z" fill="#ffffff"></path></svg><span class="button-alternative-text anchor-text u-font-sans">View article in Publons</span></button></a>
			    </section>
			    {/if}

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
			                <span class="__dimensions_badge_embed__" data-doi="{$article->getPubId('doi')}" data-legend="always" data-style="small_circle"></span>
			            </div>
			        </details>
			    </section>
			    
			    {include file="article/recom-article.tpl"}
			    
			    {include file="article/citedby_doi.tpl"}
			    
				<section class="SidePanel u-margin-s-bottom details-44861495">
			        <details class="details-summary-2566262091 u-margin-s-bottom" open="">
			            <summary class="details-summary-label-617948308">
        					{if $blockTitle}
        					<header id="metrics-header" class="side-panel-header">
        					    <div class="u-font-sans" type="button"><span class="button-link-text">
        					        <h3 class="section-title u-h4 u-font-sans-sang">{$blockTitle} <span class="fileSize u-show-inline-from-lg">Powered by <span class="PlumX">PlumX</span></span></h3></span>
        					        <svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415 icon"><path fill="#d54449" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
        					    </div>
        					</header>
        					{else}
        					<header id="metrics-header" class="side-panel-header">
        					    <div class="u-font-sans" type="button"><span class="button-link-text">
        					        <h2 class="section-title u-h4 u-font-sans-sang">Article Metrics <span class="fileSize u-show-inline-from-lg">Powered by <span class="PlumX">PlumX</span></span></h2></span>
        					        <svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415 icon"><path fill="#d54449" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
        					    </div>
        					</header>
        					{/if}
        				</summary>
        					{if $htmlPrefix}{$htmlPrefix}{/if}
        					<!-- Plum Analytics -->
        					<div class="u-margin-s-top metrics-details" aria-hidden="false" aria-describedby="metrics-header">
            					<link rel="preload" href="//cdn.plu.mx/widget-summary.js" as="script">
            					<script type="text/javascript" src="//cdn.plu.mx/widget-summary.js"></script>
            					<a rel="noreferrer noopener" href="https://plu.mx/plum/a/?doi={$article->getPubId('doi')}" class="plum-sciencedirect-theme plumx-summary" data-site="plum" data-lang="{$article->getLanguage()|strip_tags|escape}" loading="lazy" data-track-label="{$article->getPubId('doi')}" {if $hideWhenEmpty}data-hide-when-empty="{$hideWhenEmpty|escape}" {/if}{if $hidePrint}data-hide-print="{$hidePrint|escape}" {/if}{if $orientation}data-orientation="{$orientation|escape}" {/if}{if $popup}data-popup="{$popup|escape}" {/if}{if $border}data-border="{$border|escape}"{/if}{if $width}data-width="{$width|escape}"{/if}></a>
        					</div>
        					<!-- /Plum Analytics -->
        					{if $htmlSuffix}{$htmlSuffix}{/if}
        			</details>
				</section>
				
				{if $article->getLocalizedSubject(null) == (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
			    {else}		
				<div class="js-ad sideAds">
                    <aside class="rightAds adsbox c-ad c-ad--300x250 u-mt-16" data-component-mpu="">
                        <div class="c-ad__inner">
                            <p class="c-ad__label">Advertisement</p>
                            <style>
                            {literal}@media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                            {/literal}
                            </style>
                            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721" crossorigin="anonymous"></script>
                            <!-- Sangia_Publishing_ads -->
                            <ins class="adsbygoogle c-ad--300x250"
                                style="display:inline-block;width:300px;height:auto"
                                data-ad-client="ca-pub-8416265824412721"
                                data-ad-slot="2738201692">
                            </ins>
                            <script>
                            {literal}
                                 (adsbygoogle = window.adsbygoogle || []).push({});
                            {/literal}
                            </script>
                        </div>
                    </aside>
                </div>
                {/if}
                
			</aside>
		</div>	
	</div>

<article class="col-lg-12 col-md-16 pad-left pad-right c-side" role="main" lang="{$article->getLanguage()|escape}">

