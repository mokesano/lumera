{**
 * templates/common/navbar.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Navigation Bar
 *
 *}

<div class="c-header__row c-header__row--flush">
    <div class="c-header__container">
        <div class="c-header__split">
            <h1 class="c-header__logo-container u-mb-0">
                {if $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
                <a href="{url page="$currentJournal"}" data-track="click" data-track-action="home" data-track-label="image">
                    <picture class="c-header__logo" loading="lazy">
                        <source loading="lazy" srcset="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" {if $displayPageHeaderLogoAltText != ''}alt="{$displayPageHeaderLogoAltText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} width="auto">
                        <img loading="lazy" src="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" {if $displayPageHeaderLogoAltText != ''}alt="{$displayPageHeaderLogoAltText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} width="auto">
                    </picture>
                </a>                    
                {else}
                <a href="{url page="$currentJournal"}" data-track="click" data-track-action="home" data-track-label="image">
                    <picture class="c-header__logo" loading="lazy">
                        <source loading="lazy" srcset="//www.assets.sangia.org/img/sangia-black-branded-v3.svg" alt="sangia" width="auto">
                        <img loading="lazy" src="//www.assets.sangia.org/img/sangia-black-branded-v3.svg" alt="{$currentJournal->getLocalizedInitials()|strip_tags|escape}" width="auto">
                    </picture>
                </a>
                {/if}
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
                <li class="c-header__item c-header__item--pipe">
                    <a class="c-header__link" href="{url page="search" op="titles"}" data-header-expander="" data-test="search-link" data-track="click" data-track-action="open search tray" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                        <span>{translate key="navigation.search"}</span>
                        <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
                    </a>
                </li>
                {/if}
                {if $isUserLoggedIn}
                <li class="c-header__item c-header__item--sangia-research">
                    <a id="my-account" class="c-header__link placeholder c-header__item--sangia-research" href="{url page="user"}" data-test="login-link" data-track="click" data-track-action="my account" data-track-category="sangia-150-split-header" data-track-label="link">
                        <span>My Account</span>
                        <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                    </a>
                </li>
                {if $userSession->getSessionVar('signedInAs')}
                <li class="c-header__item c-header__item--sangia-research">
                    <a id="logout-button" class="c-header__link placeholder c-header__menu--tools" href="{url page="login" op="signOutAsUser"}" style="" data-test="logout-link" data-track="click" data-track-action="logout" data-track-category="nature-150-split-header" data-track-label="link">
                        <span>Logout as</span>
                        <svg aria-hidden="true" focusable="false" role="img" width="22" height="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="m8.72592184 2.54588137c-.48811714-.34391207-1.08343326-.54588137-1.72592184-.54588137-1.65685425 0-3 1.34314575-3 3 0 1.02947485.5215457 1.96853646 1.3698342 2.51900785l.6301658.40892721v1.02400182l-.79002171.32905522c-1.93395773.8055207-3.20997829 2.7024791-3.20997829 4.8180274v.9009805h-1v-.9009805c0-2.5479714 1.54557359-4.79153984 3.82548288-5.7411543-1.09870406-.71297106-1.82548288-1.95054399-1.82548288-3.3578652 0-2.209139 1.790861-4 4-4 1.09079823 0 2.07961816.43662103 2.80122451 1.1446278-.37707584.09278571-.7373238.22835063-1.07530267.40125357zm-2.72592184 14.45411863h-1v-.9009805c0-2.5479714 1.54557359-4.7915398 3.82548288-5.7411543-1.09870406-.71297106-1.82548288-1.95054399-1.82548288-3.3578652 0-2.209139 1.790861-4 4-4s4 1.790861 4 4c0 1.40732121-.7267788 2.64489414-1.8254829 3.3578652 2.2799093.9496145 3.8254829 3.1931829 3.8254829 5.7411543v.9009805h-1v-.9009805c0-2.1155483-1.2760206-4.0125067-3.2099783-4.8180274l-.7900217-.3290552v-1.02400184l.6301658-.40892721c.8482885-.55047139 1.3698342-1.489533 1.3698342-2.51900785 0-1.65685425-1.3431458-3-3-3-1.65685425 0-3 1.34314575-3 3 0 1.02947485.5215457 1.96853646 1.3698342 2.51900785l.6301658.40892721v1.02400184l-.79002171.3290552c-1.93395773.8055207-3.20997829 2.7024791-3.20997829 4.8180274z" fill-rule="evenodd"></path></svg>
                    </a>
                </li>
                {/if}
                <li class="c-header__item">
                    <a id="logout-button" class="c-header__link placeholder" href="{url page="login" op="signOut"}" style="" data-test="logout-link" data-track="click" data-track-action="logout" data-track-category="nature-150-split-header" data-track-label="link">
                        <span>Logout</span>
                        <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                    </a>
                </li>    
                {else}
                <li class="c-header__item">
                    <a id="login-button" class="c-header__link placeholder" href="{url page="login"}" style="" data-test="login-link" data-track="click" data-track-action="login" data-track-category="sangia-150-split-header" data-track-label="link">
                        <span>Login</span>
                        <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                    </a>
                </li>
                {/if}{* $isUserLoggedIn *}
            </ul>
        </div>
    </div>
</div>
