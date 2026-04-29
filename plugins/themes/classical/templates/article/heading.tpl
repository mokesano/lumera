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
                            <source loading="lazy" srcset="//www.assets.sangia.org/img/sangia-black-branded-v3.svg" alt="sangia" width="auto">
                            <img loading="lazy" src="//www.assets.sangia.org/img/sangia-black-branded-v3.svg" alt="sangia" width="auto">
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
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" anchor="authorGuidelines"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.authorGuidelines"}</a></li>{/if}
                                        
                                        {if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" anchor="copyrightNotice"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.copyrightNotice"}</a></li>{/if}
                                        
                                        {if $currentJournal->getLocalizedSetting('privacyStatement') != ''}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" anchor="privacyStatement"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.privacyStatement"}</a></li>{/if}
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="information" op="librarians"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="navigation.infoForLibrarians"}</a></li>
                                        
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="information" op="readers"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="navigation.infoForReaders"}</a></li>
                                        
                                        {if $currentJournal->getSetting('journalPaymentsEnabled') && ($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled'))}
                                        <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" anchor="authorFees"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.authorFees"}</a></li>{/if}
                                        
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
                                <span>{translate key="about.memberships"}</span>
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
        		{if $hasAccess || ($subscriptionRequired)}
        		{foreach from=$article->getGalleys() item=galley name=galleyList}
        		{if $galley}{if $galley->isPdfGalley()}
        		<div id="download-pdf-popover" class="popover PdfDownloadButton download-pdf-popover">
        		    <div id="popover-trigger-download-pdf-popover">
        		        <button id="pdfLink" class="button button-anchor u-padding-0-left" role="button" aria-expanded="false" aria-haspopup="true" aria-label="Download PDF options" type="button">
        		            <svg focusable="false" viewBox="0 0 32 32" width="24" height="24" class="icon icon-pdf-multicolor pdf-icon"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg>
        		            {if $galleys && $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}
        		            <span class="button-text"><a rel="noreferrer noopener" class="pdf-file" href="{url page="article" op="download" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" target="_blank" >
        		                <span id="articleFullText" class="pdf-download-label u-show-inline-from-lg">{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley() && !$galley->isHTMLGalley()}Download fulltext {$galley->getLabel()|escape}{else}Get Access{/if}</span>
        		                <span class="pdf-download-label-short u-hide-from-lg">{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley && !$galley->isHTMLGalley}Download{else}Get Access{/if}</span></a>
        		            </span>
        		            {else}
        		            <span class="button-text"><a rel="noreferrer noopener" class="pdf-file" href="{url page="article" op="download" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" target="_blank">
        		                <span id="articleFullText" class="pdf-download-label u-show-inline-from-lg">{if $galleys && $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}Get Access{else}Download fulltext in {$galley->getLabel()|escape}{/if}</span>
        		                <span class="pdf-download-label-short u-hide-from-lg">{if $galleys && $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}Get Access{else}Download{/if}</span></a>
        		            </span>
        		            {/if}
        		        </button>
        		    </div>
        	   </div>{/if}
        	   {else}
        	   <div id="check-access-popover" class="popover check-access-popover">
        	       <div id="popover-trigger-check-access-popover">
        	           <button class="button button-anchor u-padding-0-left" role="button" aria-expanded="false" aria-haspopup="true" type="submit"><svg focusable="false" viewBox="0 0 32 32" width="24" height="24" class="icon icon-pdf-multicolor pdf-icon"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596-.673-.002-.6-.32-.667-.596-.667h-.542m3.8.036v2.92h.35c.933 0 1.223-.448 1.228-1.462.008-1.06-.316-1.45-1.23-1.45h-.347m-.977-.94h1.03c1.68 0 2.523.586 2.534 2.39.01 1.688-.607 2.4-2.534 2.4h-1.03V3.64m4.305 0h2.63v.934h-1.657v.894H16.6V6.4h-1.56v2.026h-.97V3.638"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg>
        	           <span class="button-text"><span class="pdf-download-label u-show-inline-from-lg">Get Access</span>
        	           <span class="pdf-download-label-short u-hide-from-lg">Get Access</span></span>
        	           </button>
        	       </div>
        	    </div>        		
        	    {/if}{/foreach}
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
			    
                <div id="submitter" class="cms-person">
                    <h3 class="u-h5 article-span u-font-sans-sang">Submitted</h3>
                	{assign var="submitter" value=$article->getUser()}
                	{assign var=submitterAffiliation value=$submitter->getLocalizedAffiliation()}
                	{assign var=submitterCountry value=$submitter->getCountry()}
                	{assign var="profileImage" value=$submitter->getSetting('profileImage')}
                    {if $profileImage}
                        <img height="auto" width="150" title="{$submitter->getFullName()|escape}" class="avatar submitter" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" />
                    {/if}
                	<div id="submitter" class="overview">
                    	 <h3 class="u-h4 u-fonts-sans">{$submitter->getFullName()|escape}</h3>
                    	 <dl>{if $submitterAffiliation|escape}{$submitterAffiliation|escape}{else}<i>Submitter Affiliation Not available</i>{/if}, {$submitter->getCountry()}</dl>
                    	 {if $submitter->getData('orcid')}<svg viewBox="0 0 72 72" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="vertical-align: middle;" width="16" height="14"><!-- Generator: sketchtool 53.1 (72631) - https://sketchapp.com --><title>Orcid logo</title><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="hero" transform="translate(-924.000000, -72.000000)" fill-rule="nonzero"><g id="Group-4"><g id="vector_iD_icon" transform="translate(924.000000, 72.000000)"><path d="M72,36 C72,55.884375 55.884375,72 36,72 C16.115625,72 0,55.884375 0,36 C0,16.115625 16.115625,0 36,0 C55.884375,0 72,16.115625 72,36 Z" id="Path" fill="#000" fill-opacity="0.9"></path><g id="Group" transform="translate(18.868966, 12.910345)" fill="#FFFFFF"><polygon id="Path" points="5.03734929 39.1250878 0.695429861 39.1250878 0.695429861 9.14431787 5.03734929 9.14431787 5.03734929 22.6930505 5.03734929 39.1250878"></polygon><path d="M11.409257,9.14431787 L23.1380784,9.14431787 C34.303014,9.14431787 39.2088191,17.0664074 39.2088191,24.1486995 C39.2088191,31.846843 33.1470485,39.1530811 23.1944669,39.1530811 L11.409257,39.1530811 L11.409257,9.14431787 Z M15.7511765,35.2620194 L22.6587756,35.2620194 C32.49858,35.2620194 34.7541226,27.8438084 34.7541226,24.1486995 C34.7541226,18.1301509 30.8915059,13.0353795 22.4332213,13.0353795 L15.7511765,13.0353795 L15.7511765,35.2620194 Z" id="Shape"></path><path d="M5.71401206,2.90182329 C5.71401206,4.441452 4.44526937,5.72914146 2.86638958,5.72914146 C1.28750978,5.72914146 0.0187670918,4.441452 0.0187670918,2.90182329 C0.0187670918,1.33420133 1.28750978,0.0745051096 2.86638958,0.0745051096 C4.44526937,0.0745051096 5.71401206,1.36219458 5.71401206,2.90182329 Z" id="Path"></path></g></g></g></g></g></svg><a rel="noreferrer noopener" title="Go to view {$fullname|escape} orcid-ID profile" href="{$submitter->getData('orcid')|escape}" target="_blank" class="icon extern anchor"> <span class="anchor-text">{$submitter->getData('orcid')|escape}</span></a>{esle}{/if}
                    	 <p><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width=".9em" height=".7em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg> <a rel="noreferrer noopener" class="icon anchor" title="mailto:{$submitter->getData('email')|escape}" href="mailto:{$submitter->getData('email')|escape}" target="_blank" ><span class="anchor-text">{$submitter->getData('email')|escape}</span></a></p>
                    </div>
            	<div class="PageDivider"></div>                    
            	</div>
            	
			    <section class="externals">
			        <span class="__dimensions_badge_embed__" data-doi="{$articleDOI|escape}" data-legend="always" data-style="small_circle"></span>
			    </section>
			    
			    {if $leftSidebarCode || $rightSidebarCode}
			        <div class="p-separator"></div>
    			        {if $leftSidebarCode}{/if}
    			        {if $rightSidebarCode}
    			            {$leftSidebarCode}
    			        {/if}
			    {/if}
			    
			    <div class="js-ad">
			        <aside class="adsbox c-ad c-ad--300x250 u-mt-16" data-component-mpu="">
                        <div class="c-ad__inner">
                            <p class="c-ad__label">Sangia Advertisement</p>
                            <style>@media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                            </style>
                            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                            <!-- Advertisements -->
                            <ins class="adsbygoogle c-ad--300x250"
                                 data-ad-client="ca-pub-8416265824412721"
                                 data-ad-slot="2738201692"></ins>
                            <script>
                                 (adsbygoogle = window.adsbygoogle || []).push({});
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

                <div class="u-hide Figures" id="toc-figures">
                    <h2 class="u-h4">Figures (12)</h2>
                    <ol>
                        <li><span><div><img alt=" " src="#" style="max-width: 140px; max-height: 163px;"></div></span>
                        </li>
                        <li><span><div><img alt=" " src="#" style="max-width: 219px; max-height: 105px;"></div></span>
                        </li>
                    </ol>
                    <button class="button button-anchor" data-aa-button="sd:product:journal:article:type=menu:name=show-figures" type="button"><span class="button-text">Show all figures</span><svg focusable="false" viewBox="0 0 92 128" height="20" width="17.25" class="icon icon-navigate-down"><path d="m1 51l7-7 38 38 38-38 7 7-45 45z"></path></svg></button>
                    <div class="PageDivider"></div>
                </div>
                
                <div class="u-hide Tables" id="toc-tables">
                    <h2 class="u-h4">Tables (1)</h2>
                    <ol class="u-padding-s-bottom">
                        <li><span title="Model characteristics."><svg focusable="false" viewBox="0 0 98 128" width="18.375" height="24" class="icon icon-table"><path d="m54 68h32v32h-32v-32zm-42 0h32v32h-32v-32zm0-42h32v32h-32v-32zm42 0h32v32h-32v-32zm-52 84h94v-94h-94v94z"></path></svg>Table 1</span>
                        </li>
                    </ol>
                    <div class="PageDivider">
                        <aside class="adsbox c-ad c-ad--300x250 u-mt-16" data-component-mpu="">
                            <div class="c-ad__inner">
                                <p class="c-ad__label">Sangia Advertisement</p>
                                <style>@media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                                </style>
                                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                                <!-- Advertisements -->
                                <ins class="adsbygoogle c-ad--300x250"
                                     data-ad-client="ca-pub-8416265824412721"
                                     data-ad-slot="2738201692"></ins>
                                <script>
                                     (adsbygoogle = window.adsbygoogle || []).push({});
                                </script>
                            </div>
                        </aside>    
                    </div>
                </div>
                
                {if $journalRt->getSupplementaryFiles() && is_a($article, 'PublishedArticle') && $article->getSuppFiles()}
                <div class="SuppFile Tables" id="toc-subfiles"><h2 class="u-h4">Appendix</h2><ol class="u-padding-s-bottom">{foreach from=$article->getSuppFiles() item=suppFile key=key}<li><span title="{$suppFile->getSuppFileTitle()|escape}"><svg focusable="false" viewBox="0 0 94 128" width="17.625" height="24" class="icon icon-text-document"><path d="m35.6 1e1c-5.38 0-10.62 1.92-14.76 5.4-9.1 7.68-18.84 20.14-18.84 32.1v70.5h9e1v-15.99-2.01-4e1 -17.64-32.36h-56.4zm0 1e1h46.4v22.36 17.64 4e1 2.01 5.99h-7e1v-49c0-6.08 4.92-11 11-11h17v-2e1h-6c-2.2 0-4 1.8-4 4v6h-7c-3.32 0-6.44 0.78-9.22 2.16 2.46-5.62 7.28-11.86 13.5-17.1 2.34-1.98 5.3-3.06 8.32-3.06zm-13.6 38v1e1h5e1v-1e1h-5e1zm0 2e1v1e1h5e1v-1e1h-5e1z"></path></svg>{$key+1}. Supplementary file ({$suppFile->getNiceFileSize()})</span></li>{/foreach}</ol>
                <div class="PageDivider"></div></div>
                {/if}
            	
            	{if $article->getLocalizedSubject(null)}
                <div id="submitter" class="cms-person">
                    <h3 class="u-h5 article-span u-font-sans-sang">Article submitted</h3>
                	{assign var="submitter" value=$article->getUser()}
                	{assign var=submitterAffiliation value=$submitter->getLocalizedAffiliation()}
                	{assign var=submitterCountry value=$submitter->getCountry()}
                	{assign var="profileImage" value=$submitter->getSetting('profileImage')}
                    {if $profileImage}
                        <img height="auto" width="150" title="{$submitter->getFullName()|escape}" class="avatar editor" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" />
                    {/if}
                	<div id="submitter" class="overview">
                    	 <h3 class="u-h4 u-fonts-sans">{$submitter->getFullName()|escape}</h3>
                    	 <dl>{if $submitterAffiliation|escape}{$submitterAffiliation|escape}{else}<i>Submitter Affiliation Not available</i>{/if}, {$submitter->getCountry()}</dl>
                    	 {if $submitter->getData('orcid')}<svg viewBox="0 0 72 72" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="vertical-align: middle;" width="16" height="14"><!-- Generator: sketchtool 53.1 (72631) - https://sketchapp.com --><title>Orcid logo</title><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="hero" transform="translate(-924.000000, -72.000000)" fill-rule="nonzero"><g id="Group-4"><g id="vector_iD_icon" transform="translate(924.000000, 72.000000)"><path d="M72,36 C72,55.884375 55.884375,72 36,72 C16.115625,72 0,55.884375 0,36 C0,16.115625 16.115625,0 36,0 C55.884375,0 72,16.115625 72,36 Z" id="Path" fill="#000" fill-opacity="0.9"></path><g id="Group" transform="translate(18.868966, 12.910345)" fill="#FFFFFF"><polygon id="Path" points="5.03734929 39.1250878 0.695429861 39.1250878 0.695429861 9.14431787 5.03734929 9.14431787 5.03734929 22.6930505 5.03734929 39.1250878"></polygon><path d="M11.409257,9.14431787 L23.1380784,9.14431787 C34.303014,9.14431787 39.2088191,17.0664074 39.2088191,24.1486995 C39.2088191,31.846843 33.1470485,39.1530811 23.1944669,39.1530811 L11.409257,39.1530811 L11.409257,9.14431787 Z M15.7511765,35.2620194 L22.6587756,35.2620194 C32.49858,35.2620194 34.7541226,27.8438084 34.7541226,24.1486995 C34.7541226,18.1301509 30.8915059,13.0353795 22.4332213,13.0353795 L15.7511765,13.0353795 L15.7511765,35.2620194 Z" id="Shape"></path><path d="M5.71401206,2.90182329 C5.71401206,4.441452 4.44526937,5.72914146 2.86638958,5.72914146 C1.28750978,5.72914146 0.0187670918,4.441452 0.0187670918,2.90182329 C0.0187670918,1.33420133 1.28750978,0.0745051096 2.86638958,0.0745051096 C4.44526937,0.0745051096 5.71401206,1.36219458 5.71401206,2.90182329 Z" id="Path"></path></g></g></g></g></g></svg><a rel="noreferrer noopener" title="Go to view {$fullname|escape} orcid-ID profile" href="{$submitter->getData('orcid')|escape}" target="_blank" class="icon extern anchor"> <span class="anchor-text">{$submitter->getData('orcid')|escape}</span></a>{esle}{/if}
                    	 <p><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width=".9em" height=".7em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg> <a rel="noreferrer noopener" class="icon anchor" title="mailto:{$submitter->getData('email')|escape}" href="mailto:{$submitter->getData('email')|escape}" target="_blank" ><span class="anchor-text">{$submitter->getData('email')|escape}</span></a></p>
                    </div>
            	</div>
            	
            	<div id="correspondence" class="cms-person">
                    <h3 class="u-h5 article-span u-font-sans-sang">Correspondence</h3>
                    {assign var=authors value=$article->getAuthors()}
                    {foreach from=$authors item=author name=authors key=i}
                    {assign var="contact" value=$author->getData('primaryContact')}
                	{assign var=fullname value=$author->getFullName()}
                	{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}
                    {assign var=authorCountry value=$author->getCountry()}
                    {if $author->getData('primaryContact')|escape}
                	<div id="correspondence" class="overview">
                    	 <h3 class="u-h4 u-fonts-sans">{$fullname|escape}</h3>
                    	 <dl>{if $authorAffiliation|escape}{$authorAffiliation|escape}{else}<i>Author Affiliation Not Available</i>{/if}{if $author->getCountry()}, {$author->getCountryLocalized()|escape}{/if}.</dl>
                    	 {if $author->getData('orcid')}<svg viewBox="0 0 72 72" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="vertical-align: middle;" width="16" height="14"><!-- Generator: sketchtool 53.1 (72631) - https://sketchapp.com --><title>Orcid logo</title><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="hero" transform="translate(-924.000000, -72.000000)" fill-rule="nonzero"><g id="Group-4"><g id="vector_iD_icon" transform="translate(924.000000, 72.000000)"><path d="M72,36 C72,55.884375 55.884375,72 36,72 C16.115625,72 0,55.884375 0,36 C0,16.115625 16.115625,0 36,0 C55.884375,0 72,16.115625 72,36 Z" id="Path" fill="#000" fill-opacity="0.9"></path><g id="Group" transform="translate(18.868966, 12.910345)" fill="#FFFFFF"><polygon id="Path" points="5.03734929 39.1250878 0.695429861 39.1250878 0.695429861 9.14431787 5.03734929 9.14431787 5.03734929 22.6930505 5.03734929 39.1250878"></polygon><path d="M11.409257,9.14431787 L23.1380784,9.14431787 C34.303014,9.14431787 39.2088191,17.0664074 39.2088191,24.1486995 C39.2088191,31.846843 33.1470485,39.1530811 23.1944669,39.1530811 L11.409257,39.1530811 L11.409257,9.14431787 Z M15.7511765,35.2620194 L22.6587756,35.2620194 C32.49858,35.2620194 34.7541226,27.8438084 34.7541226,24.1486995 C34.7541226,18.1301509 30.8915059,13.0353795 22.4332213,13.0353795 L15.7511765,13.0353795 L15.7511765,35.2620194 Z" id="Shape"></path><path d="M5.71401206,2.90182329 C5.71401206,4.441452 4.44526937,5.72914146 2.86638958,5.72914146 C1.28750978,5.72914146 0.0187670918,4.441452 0.0187670918,2.90182329 C0.0187670918,1.33420133 1.28750978,0.0745051096 2.86638958,0.0745051096 C4.44526937,0.0745051096 5.71401206,1.36219458 5.71401206,2.90182329 Z" id="Path"></path></g></g></g></g></g></svg><a rel="noreferrer noopener" title="Go to view {$fullname|escape} orcid-ID profile" href="{$author->getData('orcid')|escape}" target="_blank" class="icon extern anchor"> <span class="anchor-text">{$author->getData('orcid')|escape}</span></a>{esle}{/if}
                    	 <p><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width=".9em" height=".7em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg> <a rel="noreferrer noopener" class="icon anchor" title="mailto:{$author->getData('email')|escape}" href="mailto:{$author->getData('email')|escape}" target="_blank" ><span class="anchor-text">{$author->getData('email')|escape}</span></a></p>
                    </div>
                    {/if}{/foreach}
            	</div>
            	
            	<div class="PageDivider"></div>

			    <section class="p-separator link">
			     	{if $galleys && $galley->isPdfGalley()}
			     	<a rel="noreferrer noopener" target="_blank" title="Download this article in PDF format" href="{url page="article" op="download" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" class="file anchor" {if $galley->getRemoteURL()}target="_blank"{else}target="_blank"{/if}><span class="anchor-text">Download PDF fulltext</span></a>
			        {/if}
			        <a class="u-hide external" rel="noreferrer noopener" style="hover:none" href="{url page="rt" op="captureCite" path=$articleId|to_array:$galleyId}" title="Capture citation with citation styles"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="32" height="32" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z"></path></svg><span class="button-alternative-text">Capture citation</span></button></a>
			        <a class="u-hide external" rel="noreferrer noopener" style="hover:none" href="javascript:document.getElementsByTagName('body')[0].appendChild(document.createElement('script')).setAttribute('src','https://www.mendeley.com/minified/bookmarklet.js');" title="Save Article to Mendeley"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="32" height="32" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z"></path></svg><span class="button-alternative-text">Add to Mendeley</span></button></a>
			        <a class="external" rel="noreferrer noopener" style="hover:none" href="//www.mendeley.com/import/?url={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}" target="_blank" title="Save Article to Mendeley"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="32" height="32" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z"></path></svg><span class="button-alternative-text">Export to Mendeley</span></button></a>
			        <a class="external" rel="noreferrer noopener" style="hover:none" href="javascript:document.getElementsByTagName('body')[0].appendChild(document.createElement('script')).setAttribute('src','https://www.zotero.org/bookmarklet/loader.js');" title="Save Article to Zotero"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="32" height="32" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z"></path></svg><span class="button-alternative-text">Add to Zotero</span></button></a>
			    </section>
			                	
                <div id="editors" class="cms-person">
                    <h3 class="u-h5 article-span u-font-sans">Handling {translate key="user.role.editor"}</h3>
                    {foreach from=$editAssignments item=editAssignment}
                    {assign var=editAssignments value=$submission->getEditAssignments()}                    
                	<div id="edited" class="overview">
                    	<h3 class="u-h4 u-fonts-sans">{$editAssignment->getEditorFullName()|escape}</h3>
                    	<dl></dl>
                    	{if $article->getLastModified()}
                    	<p>Last metadata edited {$article->getLastModified()|date_format:"%e %B %Y"}
                	    </p>
                	    {/if}
                    </div>
                    <div class="PageDivider"></div>
                	{foreachelse}
                	<div id="edited" class="overview">
                    	<h3 class="u-h4 u-fonts-sans">Under developments</h3>
                    	<dl></dl>
                	    {if $article->getLastModified()}
                	    <p>Last metadata edited {$article->getLastModified()|date_format:"%e %B %Y"}
                	    </p>
                	    {/if}                    	
                    </div>
                	{/foreach}                	
            	</div>
            	
            	<div class="PageDivider"></div>
			
                <div id="reviewers" class="cms-person">
                    <h3 class="u-h5 article-span u-font-sans">Reviewed by</h3>
                	{foreach from=$reviewAssignments item=reviewAssignment key=reviewKey}
                	{assign var="reviewId" value=$reviewAssignment->getId()}
                	<div id="reviewers" class="overview">
                    	<h3 class="u-h4 u-fonts-sans">{$reviewAssignment->getReviewerFullName()|escape}</h3>
                    	<dl></dl>
                    	<p></p>
                    </div>
                    <div class="PageDivider"></div>
                	{foreachelse}
                	<div id="reviewers" class="overview">
                    	<h3 class="u-h4 u-fonts-sans">Under developments</h3>
                    	<dl></dl>
                    	<p>temporarly data not available</p>
                    </div>
                	{/foreach}                	
            	</div>
            	<div class="PageDivider"></div>
            	{/if}
            	
			    <section class="u-hide externals">
			        <span class="__dimensions_badge_embed__" data-doi="{$articleDOI|escape}" data-legend="always" data-style="small_circle"></span>
			        <div class="p-separator PageDivider"></div>
			    </section>            	
								
                <div class="js-ad">
                    <aside class="adsbox c-ad c-ad--300x250 u-mt-16" data-component-mpu="">
                        <div class="c-ad__inner">
                            <p class="c-ad__label">Sangia Advertisement</p>
                            <style>@media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                            </style>
                            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                            <!-- Advertisements -->
                            <ins class="adsbygoogle c-ad--300x250"
                                 data-ad-client="ca-pub-8416265824412721"
                                 data-ad-slot="2738201692"></ins>
                            <script>
                                 (adsbygoogle = window.adsbygoogle || []).push({});
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
                <section class="SpecialIssueArticles" id="special-issue-articles">
                  <div class="p-separator part-of-issue u-padding-s-bottom link">
                    <h2 class="part-of-issue-text u-h4 special-issue--value u-font-sans">Part of special issue:</h2>
                    <div class="special-issue u-font-sans">
                        {if $currentJournal}<a rel="noreferrer noopener" class="anchor part-of-issue-title file special-issue" href="{url page="issue" op="view" path=$issue->getBestIssueId($currentJournal)}"><span class="anchor-text u-font-sans-sang title-issue">{$issue->getLocalizedTitle($currentJournal)|escape}</span></a>{/if}
                        {if $issue->getLocalizedDescription()}<span class="part-of-issue-editors">{$issue->getLocalizedDescription()|truncate:200:"..."|strip_unsafe_html|nl2br}</span>{/if}
                        {if $issue->getLocalizedCoverPageDescription()}<div class="part-of-issue-editors"><span>{$issue->getLocalizedCoverPageDescription()|strip_unsafe_html|nl2br}</span></div>{/if}
                    </div>
                    {if $issueGalleys && $issueGalley->isPdfGalley()}
                    <a class="external anchor" rel="noreferrer noopener" href="{url page="issue" op="download" path=$issue->getBestIssueId()|to_array:$issueGalley->getBestGalleyId($currentJournal)}"><button class="button-alternative DownloadFullIssue button-alternative-primary" type="button" id="download-full-issue"><svg focusable="false" viewBox="0 0 54 128" width="32" height="32" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z"></path></svg><span class="button-alternative-text anchor-text u-font-sans">Download full issue</span></button></a>
                    {/if}
                  </div>
                </section>
                {/if}
                
                <div class="side-article-impact">
                    <ul class="nav-sangia u-text-center">
                        <li class="impact-data u-mb-16">
                            <script async type="application/javascript" src="https://cdn.scite.ai/badge/scite-badge-latest.min.js">
                            </script>                                
                            <div class="scite">
                                <span class="scite-badge" data-doi="{$articleDOI|escape}" data-layout="horizontal" data-show-zero="true" data-small="false" data-show-labels="false" data-tally-show="true">
                                </span>
                            </div>
                        </li>                            
                        <li class="impact-data" title="" data-original-title="The total view count is updated once a day, so not to worry if you don't see immediate results.">
                            <span class="subtitle-text" style="font-size: 13px;color: #999;display: block;margin-top: 6px;margin-bottom: 6px;">Since {$article->getDateStatusModified()|date_format:"%e %B %Y"}</span>
                            {if $galley}
                            <span class="title-number">{math equation="x + y" x=$article->getViews() y=$galley->getViews()}</span>
                            {else}
                            <span class="title-number">{$article->getViews($totalViews)}</span>
                            {/if}
                            <span class="title-text">total views</span>
                        </li>
                        <li class="hidden-sm hidden-xs">
                            <div class="altmetric-icon">
                                <div class='altmetric-embed' data-badge-type='1' data-doi='{$articleDOI|escape}' data-link-target="new"></div>
                            </div>
                        </li>
                    </ul>
                    <a type="button" title="Article Impact powered by Scite" class="btn-sangia btn-default hidden-sm hidden-xs btn-impact" data-test-id="view-article-impact" href="https://scite.ai/reports/{$articleDOI|escape}" target="_blank"><span class="icon-impact"><i class="fa fa-line-chart"></i></span> View Article Impact</a>
                </div>

                <div class="js-ad u-mb-16">
                    <aside class="adsbox c-ad c-ad--300x250 u-mt-16" data-component-mpu="">
                        <div class="c-ad__inner">
                            <p class="c-ad__label">Sangia Advertisement</p>
                            <style>@media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                            </style>
                            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                            <!-- Advertisements -->
                            <ins class="adsbygoogle c-ad--300x250"
                                 data-ad-client="ca-pub-8416265824412721"
                                 data-ad-slot="2738201692"></ins>
                            <script>
                                 (adsbygoogle = window.adsbygoogle || []).push({});
                            </script>
                        </div>
                    </aside>
                </div>
                
                <section class="learned">
                    
                </section>
                
                <section class="SidePanel u-margin-s-bottom">
			    	<div class="js-shown u-padding-s-bottom">
                        {if $sharingEnabled}
                        <!-- start AddThis -->
                        <!-- Go to www.addthis.com/dashboard to customize your tools --> <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5b1b8c88d8801350"></script> 
                        <!-- end AddThis -->
                        {else}
			    		<h3 class="section-title u-h4 u-margin-s-bottom u-font-sans-sang">Share this article</h3>
			    		<ul data-test="social-media-share-buttons" class="c-social-links">
			    			<li class="c-social-links__item"><a id="shareOnTwitter" rel="noreferrer noopener" class="js-btnShareOnTwitter" data-track="click" data-track-category="Article Page" data-track-action="Share Twitter" data-track-label="{$articleDOI|escape}" href="https://twitter.com/share?text={$article->getLocalizedTitle()|strip_tags|escape}&amp;url={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}&amp;via=SangiaNews @SRMadhy" target="_blank"><span class="u-visually-hidden">Share on Twitter</span><svg class="c-icon c-social-links__icon" width="24" height="24" aria-hidden="true"><use xlink:href="#icon-twitter"><symbol id="icon-twitter" viewBox="0 0 24 24"><circle fill="#26A7DF" cx="12" cy="12" r="12"></circle><path fill="#FFF" d="M5.903 6.768s2.176 2.883 5.953 3.082c0 0-.51-1.702 1.058-3.004 1.568-1.305 3.508-.2 3.879.292 0 0 1.104-.172 1.875-.691 0 0-.252.995-1.18 1.594 0 0 1.086-.146 1.596-.439 0 0-.533.853-1.447 1.503 0 0 .381 3.595-2.709 6.57-3.09 2.971-8.176 2.352-10.012.797 0 0 2.495.277 4.249-1.219 0 0-2.074-.037-2.671-1.973 0 0 1.036.08 1.215-.119 0 0-2.232-.539-2.254-2.873 0 0 .68.339 1.297.359 0-.001-2.153-1.557-.849-3.879z"></path></symbol></use></svg></a>
			    			</li>
			    			<li class="c-social-links__item"><a id="shareOnFacebook" rel="noreferrer noopener" class="js-btnShareOnFacebook" data-track="click" data-track-category="Article Page" data-track-action="Share Facebook" data-track-label="{$articleDOI|escape}" href="javascript:openRTWindow('//www.facebook.com/sharer.php?u={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}')" ><span class="u-visually-hidden">Share on Facebook</span><svg class="c-icon c-social-links__icon" width="24" height="24" aria-hidden="true"><use xlink:href="#icon-facebook"><symbol id="icon-facebook" viewBox="0 0 24 24"><circle fill="#4D67A4" cx="12" cy="12" r="12"></circle><path fill="#FFF" d="M8.396 10.143h2.137V8.165s-.092-1.292.892-2.274c.979-.979 2.298-.887 4.177-.724v2.21h-1.387s-.586-.013-.861.303c-.271.315-.24.764-.24.875v1.588h2.41l-.311 2.458h-2.116v6.336h-2.56v-6.345H8.396v-2.449z"></path></symbol></use></svg></a>
			    			</li>
			    			<li class="c-social-links__item"><a id="shareOnLinkedIn" rel="noreferrer noopener" class="js-btnShareOnLinkedIn" data-track="click" data-track-category="Article Page" data-track-action="Share LinkedIn" data-track-label="{$articleDOI|escape}" href="https://www.linkedin.com/shareArticle?mini=true&amp;url={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}&amp;title={$article->getLocalizedTitle()|strip_tags|escape}&amp;source=SRM Publishing" target="_blank"><span class="u-visually-hidden">Share on LinkedIn</span><svg class="c-icon c-social-links__icon" width="24" height="24" aria-hidden="true"><use xlink:href="#icon-linkedin"><symbol id="icon-linkedin" viewBox="0 0 24 24"><circle fill="#0178B5" cx="12" cy="12" r="12"></circle><g fill="#FFF"><circle cx="8.022" cy="8.043" r="1.256"></circle><path d="M6.929 10.246h2.17v6.967h-2.17zm3.533 6.967h2.157v-3.594s-.078-1.627 1.219-1.627c1.301 0 1.211 1.231 1.211 1.635v3.586h2.183v-3.775s.129-2.745-1.472-3.191c-1.605-.445-2.661.071-3.219.985v-.985h-2.079v6.966z"></path></g></symbol></use></svg></a>
			    			</li>
			    			<li class="c-social-links__item"><a id="shareOnWeibo" rel="noreferrer noopener" class="js-btnShareOnWeibo" data-track="click" data-track-category="Article Page" data-track-action="Share Weibo" data-track-label="{$articleDOI|escape}" href="#"><span class="u-visually-hidden">Share on Weibo</span><svg class="c-icon c-social-links__icon" width="24" height="24" aria-hidden="true"><use xlink:href="#icon-weibo"><symbol id="icon-weibo" viewBox="4 4 24 24"><circle fill="#C8E8F9" cx="16" cy="16" r="12"></circle><path fill="#FFF" d="M9.098 18.194c0 1.981 2.574 3.593 5.757 3.593 3.178 0 5.756-1.611 5.756-3.593 0-1.989-2.578-3.601-5.756-3.601-3.183.001-5.757 1.612-5.757 3.601"></path><path fill="#DF0A21" d="M14.991 21.496c-2.817.278-5.244-.996-5.428-2.85-.183-1.855 1.95-3.586 4.767-3.862 2.813-.278 5.243.996 5.428 2.849.18 1.855-1.957 3.584-4.767 3.863m5.628-6.155c-.24-.067-.404-.116-.279-.432.27-.688.299-1.278.004-1.699-.551-.791-2.062-.748-3.789-.022 0-.001-.548.241-.406-.191.266-.859.224-1.577-.191-1.993-.937-.942-3.437.037-5.577 2.18-1.602 1.61-2.533 3.317-2.533 4.789 0 2.817 3.606 4.533 7.13 4.533 4.626 0 7.701-2.696 7.701-4.834.001-1.292-1.085-2.027-2.06-2.331"></path><path fill="#F4992C" d="M23.689 10.182a4.492 4.492 0 0 0-4.283-1.39.651.651 0 1 0 .269 1.275 3.2 3.2 0 0 1 3.045.99 3.224 3.224 0 0 1 .672 3.138.647.647 0 0 0 .418.823.647.647 0 0 0 .816-.421v-.003a4.51 4.51 0 0 0-.937-4.412"></path><path fill="#F4992C" d="M21.973 11.736a2.183 2.183 0 0 0-2.086-.679.56.56 0 0 0-.428.667.554.554 0 0 0 .662.43v.001c.361-.075.754.034 1.018.33.266.299.34.698.227 1.054a.563.563 0 0 0 .359.706.563.563 0 0 0 .709-.362 2.2 2.2 0 0 0-.461-2.147"></path><path fill="#13110C" d="M15.143 18.139c-.1.17-.315.251-.483.179-.168-.07-.222-.255-.128-.42.097-.167.311-.248.479-.182.165.062.228.252.132.423m-.896 1.155c-.271.434-.854.625-1.295.425-.433-.195-.561-.707-.285-1.128.269-.426.832-.609 1.265-.427.443.183.58.688.315 1.13m1.024-3.084c-1.34-.351-2.852.32-3.434 1.506-.597 1.206-.021 2.546 1.33 2.984 1.402.453 3.055-.243 3.629-1.543.569-1.275-.142-2.587-1.525-2.947"></path></symbol></use></svg></a>
			    			</li>
			    			<li class="c-social-links__item"><a id="shareOnReddit" rel="noreferrer noopener" class="js-btnShareOnReddit" data-track="click" data-track-category="Article Page" data-track-action="Share Reddit" data-track-label="{$articleDOI|escape}" href="https://reddit.com/submit?url={url page="article" op="view" path=$article->getBestArticleId($currentJournal)}&amp;title={$article->getLocalizedTitle()|strip_tags|escape}" aria-label="Reddit" target="_blank"><span class="u-visually-hidden">Share on Reddit</span><svg class="c-icon c-social-links__icon" width="24" height="24" aria-hidden="true"><use xlink:href="#icon-reddit"><symbol id="icon-reddit" viewBox="0 0 24 24"><circle fill="#BCBCBC" cx="12" cy="12" r="12"></circle><path fill="#FFF" d="M4.661 9.741c.941 0 1.703.761 1.703 1.704 0 .938-.762 1.705-1.703 1.705s-1.704-.767-1.704-1.705c0-.943.763-1.704 1.704-1.704zm13.844 0a1.704 1.704 0 1 1 .001 3.409 1.704 1.704 0 0 1-.001-3.409z"></path><path fill="#FFF" d="M11.736 8.732c4.285 0 7.762 2.283 7.762 5.104 0 2.812-3.477 5.1-7.762 5.1-4.288 0-7.762-2.285-7.762-5.1.001-2.82 3.474-5.104 7.762-5.104z"></path><path fill="#010101" d="M11.736 19.262c-4.461 0-8.088-2.437-8.088-5.426 0-2.994 3.626-5.431 8.088-5.431 4.457 0 8.087 2.437 8.087 5.431 0 2.989-3.63 5.426-8.087 5.426zm0-10.205c-4.104 0-7.438 2.145-7.438 4.779 0 2.633 3.334 4.773 7.438 4.773 4.098 0 7.437-2.142 7.437-4.773 0-2.636-3.339-4.779-7.437-4.779z"></path><circle fill="#FFF" cx="18.014" cy="5.765" r="1.385"></circle><path fill="#010101" d="M18.014 7.456a1.694 1.694 0 0 1-1.695-1.691c0-.934.764-1.694 1.695-1.694s1.693.762 1.693 1.694c0 .934-.762 1.691-1.693 1.691zm0-2.734c-.574 0-1.043.469-1.043 1.043s.469 1.041 1.043 1.041a1.042 1.042 0 0 0 0-2.084zM3.818 13.275a2.068 2.068 0 0 1-1.068-1.811 2.074 2.074 0 0 1 3.538-1.466l-.458.462a1.412 1.412 0 0 0-1.007-.42 1.424 1.424 0 0 0-.688 2.669l-.317.566zm15.866 0l-.316-.566a1.43 1.43 0 0 0 .73-1.245c0-.784-.635-1.424-1.42-1.424-.381 0-.736.149-1.008.42l-.459-.462a2.074 2.074 0 1 1 2.473 3.277z"></path><path fill="#F04B23" d="M9.165 11.604c.704 0 1.272.564 1.272 1.27a1.273 1.273 0 1 1-1.272-1.27zm5.321 0a1.274 1.274 0 1 1-1.275 1.27c0-.704.57-1.27 1.275-1.27z"></path><path fill="#010101" d="M11.79 8.915a.327.327 0 0 1-.307-.44l1.472-4.181 3.616.862c.177.044.283.22.238.395a.32.32 0 0 1-.391.239l-3.045-.729-1.275 3.632a.334.334 0 0 1-.308.222zm-.069 8.376c-2.076 0-2.908-.939-2.943-.982a.332.332 0 0 1 .035-.461.334.334 0 0 1 .459.033c.019.021.713.758 2.449.758 1.766 0 2.539-.763 2.549-.767a.324.324 0 1 1 .469.446c-.04.043-.954.973-3.018.973z"></path></symbol></use></svg></a>
			    			</li>
			    			<li class="c-social-links__item"><a id="shareOnReadCube" rel="noreferrer noopener" class="js-btnShareOnReadCube" data-track="click" data-track-category="Article Page" data-track-action="Share ReadCube" data-track-label="{$articleDOI|escape}" href="https://www.readcube.com/library/import?doi={$articleDOI|escape}" aria-label="ReadCube" target="_blank"><span class="u-visually-hidden">Share on ReadCube</span><svg class="c-icon c-social-links__icon" aria-hidden="true" width="24" height="24"><g id="icon-readcube" transform="translate(-5828 -13885)"><circle id="Ellipse_329" data-name="Ellipse 329" transform="translate(5828 13885)" fill="#f1f3f7" cx="12" cy="12" r="12"></circle><g id="R3-Logo" transform="translate(5827.756 13885.999)"><path id="Shape" d="M7.927,6.82a3.209,3.209,0,0,0,.562-.733L11.5.5q.187-.344.075-.451t-.45.076L5.656,3.2a3.4,3.4,0,0,0-.712.573,3.209,3.209,0,0,0-.562.733L1.368,10.074q-.195.344-.082.458t.45-.084l5.48-3.056a3.4,3.4,0,0,0,.712-.573Z" transform="translate(0 -0.005)" fill="#9823c6" fill-rule="evenodd"></path><path id="Shape-2" data-name="Shape" d="M38.41,13.547a1.241,1.241,0,0,0-.337-.726l-5.39-5.493a.658.658,0,0,0-1.117.16l-3.013,5.577a1.236,1.236,0,0,0-.12.795,1.265,1.265,0,0,0,.33.726l5.39,5.485a.655.655,0,0,0,.6.229.687.687,0,0,0,.517-.39l3.013-5.569a1.282,1.282,0,0,0,.127-.795Z" transform="translate(-17.797 -4.64)" fill="#ffc34e" fill-rule="evenodd"></path><path id="Shape-3" data-name="Shape" d="M14.465,40.882a.682.682,0,0,0-.225-.611L8.858,34.778a1.221,1.221,0,0,0-.712-.336,1.173,1.173,0,0,0-.78.122L1.894,37.635a.7.7,0,0,0-.382.527.691.691,0,0,0,.225.611l5.39,5.493a1.2,1.2,0,0,0,.712.344,1.217,1.217,0,0,0,.78-.13l5.465-3.071a.7.7,0,0,0,.382-.527Z" transform="translate(-0.17 -22.547)" fill="#ff3e3c" fill-rule="evenodd"></path><path id="Shape-4" data-name="Shape" d="M39.01,6.464a.728.728,0,0,0-.225-.619L33.4.36a1.165,1.165,0,0,0-.7-.344,1.206,1.206,0,0,0-.78.13L26.439,3.209a.7.7,0,0,0-.39.527.72.72,0,0,0,.232.619l5.39,5.493a1.3,1.3,0,0,0,1.484.214L38.62,6.991a.7.7,0,0,0,.39-.527Z" transform="translate(-16.243 0)" fill="#26c4ff" fill-rule="evenodd"></path><path id="Shape-5" data-name="Shape" d="M44.2,33.331q-.1-.115-.45.076l-5.472,3.056A3.783,3.783,0,0,0,37,37.77l-3,5.585q-.187.344-.082.458t.45-.084l5.465-3.071a3.476,3.476,0,0,0,.72-.573,3.608,3.608,0,0,0,.562-.733l3.013-5.569q.187-.344.075-.451Z" transform="translate(-21.378 -21.803)" fill="#fe0fa6" fill-rule="evenodd"></path><path id="Shape-6" data-name="Shape" d="M17.7,25.808a1.27,1.27,0,0,0,.127-.795,1.241,1.241,0,0,0-.337-.726L12.1,18.8a.664.664,0,0,0-.6-.229.687.687,0,0,0-.517.39L7.973,24.532a1.247,1.247,0,0,0-.12.795,1.265,1.265,0,0,0,.33.726l5.39,5.485a.674.674,0,0,0,.6.237.686.686,0,0,0,.517-.4L17.7,25.808Z" transform="translate(-4.317 -12.154)" fill="#98d843" fill-rule="evenodd"></path></g></g></svg></a>
			    			</li>
			    		</ul>
			    		{/if}
			    	</div>
			    </section>
			    
			    <section class="u-hide SidePanel u-margin-s-bottom">
			        <header id="recommended-articles-header" class="side-panel-header u-margin-s-bottom"><button class="button-link side-panel-toggle is-up button-link-primary" aria-expanded="true" data-aa-button="sd:product:journal:article:location=recommended-articles:type=close" type="button"><span class="button-link-text">
			            <h2 class="section-title u-h4">Recommended articles</h2></span><svg focusable="false" viewBox="0 0 92 128" width="17.25" height="24" class="icon icon-navigate-down"><path d="m1 51l7-7 38 38 38-38 7 7-45 45z"></path></svg></button></header>
			        <div class=" " aria-hidden="false" aria-describedby="recommended-articles-header">
			            <div id="recommended-articles">
			                            
			            </div>
			        </div>
			    </section>
			    
			    {if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $articleExpiryPartial.$articleId))}
			     	{assign var=hasAccess value=1}
			    {else}
			     	{assign var=hasAccess value=0}
			    {/if}			    			    
                <section class="SidePanel u-margin-s-bottom u-padding-s-bottom articleInfo">
                    <div class="articleInfo">
                        <h3 class="section-title u-h4 u-font-sans-sang">Article Level Metrics <span class="fileSize u-show-inline-from-lg">by Journal System</span></h3>
                        <ul class="p-section-title__item">
                            <li class="p-section-title__item readers" type="button">
                                <span class="p-section-item--name">Readers <span class="fileSize u-show-inline-from-lg">{translate key="article.abstract" from="$metaLocale"}{if $galley && $galley->isHTMLGalley()} with fulltext {$galley->getLabel()|escape}{/if}</span></span>
                                {if $galley && $galley->isHTMLGalley()}
                                <span class="p-section-item--value">{math equation="x + y" x=$article->getViews() y=$galley->getViews()}</span>
                                {else}
                                <span class="p-section-item--value">{$article->getViews()}</span>{/if}</li>
                            {if $galley && $galley->isPdfGalley()}
                            <li class="p-section-title__item download" type="button">
                                <span class="p-section-item--name">Download <span class="fileSize u-show-inline-from-lg">fulltext {$galley->getLabel()|escape}</span></span>
                                <span class="p-section-item--value">{$galley->getViews()}</span></li>
                            {/if}
                            
                            <li class="p-section-title__item reviews" type="button"><a class="anchor" rel="noreferrer noopener" title="View article citation in Google Scholar" href="//scholar.google.co.id/scholar_lookup?title={$article->getLocalizedTitle()|strip_tags|escape}" target="_blank">
                                <span class="p-section-item--name anchor-text">Reviews</span><span class="fileSize u-show-inline-from-lg"> by Google Scholar</span>
                                <span class="p-section-item--value google" title="View article citation in Google Scholar" >N/A</span></a></li>                                
                            <li class="p-section-title__item dimension citations" type="button">
                                <span class="p-section-item--name">Citations <span class="fileSize u-show-inline-from-lg">by Dimension</span></span>
                                <span class="p-section-item--value __dimensions_badge_embed__" data-doi="{$articleDOI|escape}" data-style="small_rectangle" ></span></li>                            
                            <li class="p-section-title__item mentions" type="button">
                                <link rel="preload" href="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js" as="script"><script type="text/javascript" src="https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js"></script>
                                <span class="p-section-item--name">Mentions <span class="fileSize u-show-inline-from-lg">by Altmetric</span></span>{if $pubId}{$articleDOI|escape}{else}{/if}
                                {if $pubId}
                                <span data-badge-popover="left" data-badge-type="medium-bar" data-doi="{$articleDOI|escape}" class="altmetric-embed p-section-item--value" data-link-target="new">{$articleDOI|escape}</span>
                                {else}
                                <span data-badge-popover="left" data-badge-type="medium-bar" data-doi="" class="p-section-item--value altmetric-embed" data-link-target="new">Altmetric badge</span>
                                {/if}
                                </li>
                        </ul>
                    </div>
                </section>

			    <section class="SidePanel u-margin-s-bottom details-44861495">
			        <details class="details-summary-2566262091 u-margin-s-bottom">
			            <summary class=" ">    
        			        <header id="citing-articles-header" class="details-summary-label-617948308 side-panel-header">
        			            <div class="u-font-sans" type="button">
        			                <span class="button-link-text">
        			                    <h2 class="section-title u-h4 u-font-sans-sang">Citing articles <span class="fileSize u-show-inline-from-lg">Powered by <span class="scopus">Scopus</span></span> <svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415 icon"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
        			                    </h2>
        			                </span>
        			            </div>    			            
        			        </header>
			            </summary>	
			            
			            <!-- Scopus citation -->
    			        <object class="details-summary-2566262091 metrics-details" height="50" data="https://api.elsevier.com/content/abstract/citation-count?doi={$articleDOI|escape}&apiKey=73e21cba2e777a3093e24a781e0ee1a9&httpAccept=text/html" title="Go to view citing articles powered by Scopus"></object>
    			        <!-- Scopus citation -->
			        
    			        <div class="u-hide metrics-details" >
    			            <div id="citing-articles">
    			                <ul><li class="SidePanelItem">
    			                    <div class="sub-heading"><a href="/" target="_self"><h3 class="article-title ellipsis text-s" id="citing-articles-article0-title">Article title</h3></a><div class="article-source ellipsis">Year, Journal title, Vol.</div>
    			                    </div>
    			                </li></ul>
    			                <a class="anchor" href="//www.scopus.com/scopus/inward/citedby.url?partnerID=000&amp;eid=000&amp;md5=keyAPI" target="_blank"><span class="anchor-text">View more articles</span><svg focusable="false" viewBox="0 0 54 128" width="10.125" height="24" class="u-hide icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z"></path></svg></a>
    		                </div>
    			        </div>
			        </details>
			    </section>
			    
			    {assign var="doi" value=$article->getStoredPubId('doi')}
			    {if $article->getPubId('doi')}
			    <section class="p-separator link u-font-sans-sang">
			        <a class="external" rel="noreferrer noopener" href="http://www.readcube.com/articles/{$article->getPubId('doi')}" target="_blank" title="Go to view fulltext epdf format in ReadCube (Dimension)"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="32" height="32" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z"></path></svg><span class="button-alternative-text">View article in ReadCube</span></button></a>
			        <a class="external" rel="noreferrer noopener" style="hover:none" href="https://publons.com/follow/publon/create/{$article->getPubId('doi')}" title="Go to view article in Publons (Web of Science)" target="_blank"><button type="button" class="button-alternative DownloadFullIssue button-alternative-primary" id=""><svg focusable="false" viewBox="0 0 54 128" width="32" height="32" class="icon icon-navigate-right"><path d="m1 99l38-38-38-38 7-7 45 45-45 45z"></path></svg><span class="button-alternative-text">View article in Publons</span></button></a>
			    </section>
			    {/if}

				<section class="SidePanel u-margin-s-bottom details-44861495">
			        <details class="details-summary-2566262091 u-margin-s-bottom" open="">
			            <summary class="details-summary-label-617948308">
        					{if $blockTitle}
        					<header id="metrics-header" class="side-panel-header">
        					    <div class="u-font-sans" type="button"><span class="button-link-text">
        					        <h2 class="section-title u-h4 u-font-sans-sang">{$blockTitle} <span class="fileSize u-show-inline-from-lg">Powered by <span class="plumx">PlumX</span></span></h2></span>
        					        <svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415 icon"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
        					    </div>
        					</header>
        					{else}
        					<header id="metrics-header" class="side-panel-header">
        					    <div class="u-font-sans" type="button"><span class="button-link-text">
        					        <h2 class="section-title u-h4 u-font-sans-sang">Article Metrics <span class="fileSize u-show-inline-from-lg">Powered by <span class="plumx">PlumX</span></span></h2></span>
        					        <svg width="32" height="32" viewBox="0 0 32 32" class="details-marker-1174223415 icon"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
        					    </div>
        					</header>
        					{/if}
        				</summary>
        					{if $htmlPrefix}{$htmlPrefix}{/if}
        					<!-- Plum Analytics -->
        					<div class="u-margin-s-top metrics-details" aria-hidden="false" aria-describedby="metrics-header">
            					<link rel="preload" href="//cdn.plu.mx/widget-summary.js" as="script">
            					<script type="text/javascript" src="//cdn.plu.mx/widget-summary.js"></script>
            					<a rel="noreferrer noopener" href="https://plu.mx/plum/a/?doi={$articleDOI|escape}" class="plum-sciencedirect-theme plumx-summary" data-site="plum" data-lang="id" loading="lazy" {if $hideWhenEmpty}data-hide-when-empty="{$hideWhenEmpty|escape}" {/if}{if $hidePrint}data-hide-print="{$hidePrint|escape}" {/if}{if $orientation}data-orientation="{$orientation|escape}" {/if}{if $popup}data-popup="{$popup|escape}" {/if}{if $border}data-border="{$border|escape}"{/if}{if $width}data-width="{$width|escape}"{/if}></a>
        					</div>
        					<!-- /Plum Analytics -->
        					{if $htmlSuffix}{$htmlSuffix}{/if}
        			</details>
				</section>
				
			    <section class="externals">
			        <span class="__dimensions_badge_embed__" data-doi="{$articleDOI|escape}" data-legend="always" data-style="small_circle"></span>
			        <div class="p-separator PageDivider"></div>
			    </section>            					
				
				{if $article->getLocalizedSubject(null) == (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
			    {else}		
				<div class="js-ad">
                    <aside class="adsbox c-ad c-ad--300x250 u-mt-16" data-component-mpu="">
                        <div class="c-ad__inner">
                            <p class="c-ad__label">Sangia Advertisement</p>
                            <style>@media(max-width:500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width: 500px){.c-ad--300x250{width:300px;height:250px;}}@media(min-width:800px){.c-ad--300x250{width:300px;height:250px;}}
                            </style>
                            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                            <!-- Advertisements -->
                            <ins class="adsbygoogle c-ad--300x250"
                                 data-ad-client="ca-pub-8416265824412721"
                                 data-ad-slot="2738201692"></ins>
                            <script>
                                 (adsbygoogle = window.adsbygoogle || []).push({});
                            </script>
                        </div>
                    </aside>
                </div>
                {/if}
                
			</aside>
		</div>	
	</div>

<article class="col-lg-12 col-md-16 pad-left pad-right c-side" role="main" lang="{$article->getLanguage()|escape}">

