{**
 * templates/common/navbar.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * Navigation Bar
 *
 *}
<header class="c-header u-mb-0" style="border-color:#000">
    <div class="c-header__row c-header__row--flush">
        <div class="c-header__container">
            <div class="c-header__split">
                <h1 class="c-header__logo-container u-mb-0">
                    <a href="//www.sangia.org" data-track="click" data-track-action="home" data-track-label="image">
                        {if $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
                        <picture class="c-header__logo" loading="lazy">
                            <source loading="lazy" srcset="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" {if $displayPageHeaderLogoAltText != ''}alt="{$displayPageHeaderLogoAltText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} width="auto">
                            <img loading="lazy" src="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" {if $displayPageHeaderLogoAltText != ''}alt="{$displayPageHeaderLogoAltText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} width="auto">
                        </picture>                        
                        {else}
                        <picture class="c-header__logo" loading="lazy">
                            <source loading="lazy" srcset="//www.assets.sangia.org/img/sangia-black-branded-v3.svg" alt="sangia" width="auto">
                            <img loading="lazy" src="//www.assets.sangia.org/img/sangia-black-branded-v3.svg" alt="sangia" width="auto">
                        </picture>
                        {/if}
                    </a>
                </h1>
                <ul class="c-header__menu c-header__menu--global">
                    {if $siteCategoriesEnabled}
                    <li class="c-header__item c-header__item--sangia-research">
                        <a class="c-header__link" href="/" data-test="siteindex-link" data-track="click" data-track-action="open sangia research index" data-track-label="link">
                            <span>{translate key="navigation.otherJournals"}</span>
                        </a>
                    </li>
                    {/if}{* $categoriesEnabled *}
                    {if !$currentJournal || $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
                    <li class="c-header__item c-header__item--pipe">
                        <a class="c-header__link" href="{url page="search"}" data-header-expander="" data-test="search-link" data-track="click" data-track-action="open search tray" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                            <span>{translate key="navigation.search"}</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M16.48 15.455c.283.282.29.749.007 1.032a.738.738 0 01-1.032-.007l-3.045-3.044a7 7 0 111.026-1.026zM8 14A6 6 0 108 2a6 6 0 000 12z"></path></svg>
                        </a>
                    </li>
                    {/if}
                    <li class="c-header__item">
                        {if $isUserLoggedIn}
                        <a id="my-account" class="c-header__link placeholder" href="{url page="user"}" data-test="login-link" data-track="click" data-track-action="my account" data-track-category="sangia-150-split-header" data-track-label="link">
                            <span>My Account</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                        </a>
                        {if $userSession->getSessionVar('signedInAs')}
                        <a id="logout-button" class="c-header__link placeholder" href="{url page="login" op="signOutAsUser"}" style="" data-test="logout-link" data-track="click" data-track-action="logout" data-track-category="nature-150-split-header" data-track-label="link">
                            <span>Logout as</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                        </a>
                        {/if}
                        <a id="logout-button" class="c-header__link placeholder" href="{url page="login" op="signOut"}" style="" data-test="logout-link" data-track="click" data-track-action="logout" data-track-category="nature-150-split-header" data-track-label="link">
                            <span>Logout</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                        </a>
                        {else}
                        <a id="login-button" class="c-header__link placeholder" href="{url page="login"}" style="" data-test="login-link" data-track="click" data-track-action="login" data-track-category="sangia-150-split-header" data-track-label="link">
                            <span>Login</span>
                            <svg role="img" aria-hidden="true" focusable="false" height="22" width="22" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M10.238 16.905a7.96 7.96 0 003.53-1.48c-.874-2.514-2.065-3.936-3.768-4.319V9.83a3.001 3.001 0 10-2 0v1.277c-1.703.383-2.894 1.805-3.767 4.319A7.96 7.96 0 009 17c.419 0 .832-.032 1.238-.095zm4.342-2.172a8 8 0 10-11.16 0c.757-2.017 1.84-3.608 3.49-4.322a4 4 0 114.182 0c1.649.714 2.731 2.305 3.488 4.322zM9 18A9 9 0 119 0a9 9 0 010 18z" fill="#333" fill-rule="evenodd"></path></svg>
                        </a>
                        {/if}{* $isUserLoggedIn *}
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="c-journal-header__identity c-journal-header__identity--default"></div> 
</header>