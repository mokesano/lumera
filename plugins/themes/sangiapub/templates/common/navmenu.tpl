{**
 * templates/common/navmenu.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Navigation Menu Bar
 *
 *}
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
                                {if $currentJournal}
                                    
                                    {if $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="issue" op="current"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="journal.currentIssue"}</a></li>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="volumes"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Archive Issues</a></li>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="search" op="titles"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Titles Index</a></li>
                                        
                                    {** Kode Perlu Perbaikan - Article Type
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="browseSearch" op="sections"}" data-track="click" data-track-label="link" data-test="explore-nav-item">View Section</a></li>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="browseSearch" op="identifyTypes"}" data-track="click" data-track-label="link" data-test="explore-nav-item">View Article Type</a></li> 
                                        
                                    Kode Perlu Perbaikan **}
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="search" op="authors"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Authors Index</a></li>
                                        
                                    {/if}
                                        
                                {else}
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="search" op="titles"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Titles Index</a></li>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="search" op="authors"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Authors Index</a></li>
                                    
                                {/if}
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="sitemap"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.siteMap"}</a></li>
                                        
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only"><a class="c-header-expander__link" href="//www.facebook.com/SangiaNews" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">Follow us on Facebook</a></li>
                                        
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only"><a class="c-header-expander__link" href="https://twitter.com/SangiaNews" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">Follow us on Twitter</a></li>
                                        
                                    {if $currentJournal}
                                    {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION || $donationEnabled || $currentJournal->getSetting('membershipFee')}
                                        
                                    {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="about" op="subscriptions"}" data-track="click" data-track-action="subscribe" data-track-label="link" data-test="menu-button-subscribe">{translate key="about.subscribe"}</a></li>
                                    {/if}{* $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION *}
                                        
                                    {if $donationEnabled}
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="donations"}" data-track="click" data-track-action="donation" data-track-label="link" data-test="menu-button-donation">{translate key="payment.type.donation"}</a></li>{/if}
                                        
                                    {if $currentJournal->getSetting('membershipFeeEnabled')}
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="about" op="memberships"}" data-track="click" data-track-action="membership" data-track-label="link" data-test="menu-button-membership">{translate key="about.members"}</a></li>{/if}
                                        
                                    {/if}{* $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION || $donationEnabled || $currentJournal->getSetting('membershipFee') *}
                                        
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="notification" op="subscribeMailList"}" rel="nofollow" data-track="click" data-track-action="Sign up for alerts" data-track-external="" data-track-label="link (mobile dropdown)">Sign up for alerts<svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m4 10h2.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-3.08578644l-1.12132034 1.1213203c-.18753638.1875364-.29289322.4418903-.29289322.7071068v.1715729h14v-.1715729c0-.2652165-.1053568-.5195704-.2928932-.7071068l-1.7071068-1.7071067v-3.4142136c0-2.76142375-2.2385763-5-5-5-2.76142375 0-5 2.23857625-5 5zm3 4c0 1.1045695.8954305 2 2 2s2-.8954305 2-2zm-5 0c-.55228475 0-1-.4477153-1-1v-.1715729c0-.530433.21071368-1.0391408.58578644-1.4142135l1.41421356-1.4142136v-3c0-3.3137085 2.6862915-6 6-6s6 2.6862915 6 6v3l1.4142136 1.4142136c.3750727.3750727.5857864.8837805.5857864 1.4142135v.1715729c0 .5522847-.4477153 1-1 1h-4c0 1.6568542-1.3431458 3-3 3-1.65685425 0-3-1.3431458-3-3z" fill="#fff"></path></svg></a></li>
                                        
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="gateway" op="plugin"}/WebFeedGatewayPlugin/rss" data-track="click" data-track-action="rss feed" data-track-label="link" target="_blank"><span>RSS feed</span></a></li>
                                        
                                    {url|assign:"oaiUrl" page="oai"}
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{$oaiUrl}" data-track="click" data-track-action="OAI feed" data-track-label="link" target="_blank"><span>OAI</span></a></li>
                                    {/if}
                                </ul>
                            </div>
                        </nav>
                    </li>
                    {if $currentJournal}
                    <li class="c-header__item c-header__item--dropdown-menu">
                        <a class="c-header__link c-header__link--chevron" href="javascript:;" data-header-expander="" data-test="menu-button--explore" data-track="click" data-track-action="open explore expander" data-track-label="button" role="button" aria-haspopup="true" aria-expanded="false">
                            <span>{translate key="navigation.about"} <span class="c-header__show-text">the journal</span></span>
                            <svg role="img" aria-hidden="true" focusable="false" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg"><path d="m5.58578644 3-3.29289322-3.29289322c-.39052429-.39052429-.39052429-1.02368927 0-1.41421356s1.02368927-.39052429 1.41421356 0l4 4c.39052429.39052429.39052429 1.02368927 0 1.41421356l-4 4c-.39052429.39052429-1.02368927.39052429-1.41421356 0s-.39052429-1.02368927 0-1.41421356z" transform="matrix(0 1 -1 0 11 3)"></path></svg>
                        </a>
                        <nav id="explore" class="u-hide-print c-header-expander has-tethered lm-nav-sub" aria-labelledby="Explore-content" data-test="Explore-content" data-track-component="sangia-150-split-header" hidden="">
                            <div class="c-header-expander__container">
                                <h2 id="Explore-content" class="c-header-expander__heading u-hide">About the journal</h2>
                                <ul class="c-header-expander__list">
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorial-team"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.editorialTeam"}</a></li>

                            {if $membershipGroups}
                                {foreach from=$membershipGroups item=peopleGroup}
                                <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="displayMembership" path=$peopleGroup.group_id}" data-track="click" data-track-label="link" data-test="explore-nav-item">{$peopleGroup.title|escape}</a></li>
                                {/foreach}
                            {/if}
                            {call_hook name="Templates::About::Index::People"}
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.journal"}</a></li>
                                        
                                    {if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="focusAndScope"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.focusAndScope"}</a></li>{/if}
                                        
                                    {foreach from=$navMenuItems item=navItem key=navItemKey}{if $navItem.url != '' && $navItem.name != ''}
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{$baseUrl}{$navItem.url|escape}{/if}" data-track="click" data-track-label="link" data-test="explore-nav-item">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</a></li>{/if}{/foreach}
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="sectionPolicies"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.sectionPolicies"}</a></li>
                                        
                                    {call_hook name="Templates::About::Index::Policies"}
                                        
                                    {if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="publicationFrequency"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.publicationFrequency"}</a></li>{/if}
                                        
                                    {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.openAccessPolicy"}</a></li>{/if}
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="announcement"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Announcements</a></li>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="editorialPolicies" anchor="archiving"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.archiving"}</a></li>
                                        
                                    {if $currentJournal->getLocalizedSetting('history') != ''}
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="history"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.history"}</a></li>
                                    {/if}
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="statistics"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.statistics"}</a></li>
                                        
                                    {call_hook name="Templates::Common::Header::Navbar::CurrentJournal"}
                                    {call_hook name="Templates::About::Index::Other"}
                                        
                                    {if not ($currentJournal->getSetting('publisherInstitution') == '' && $currentJournal->getLocalizedSetting('publisherNote') == '' && $currentJournal->getLocalizedSetting('contributorNote') == '' && empty($journalSettings.contributors) && $currentJournal->getLocalizedSetting('sponsorNote') == '' && empty($journalSettings.sponsors))}
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="journalSponsorship"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.journalSponsorship"}</a></li>{/if}
                                        
                                    {if $siteCategoriesEnabled}
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="/" data-track="click" data-track-action="OAI feed" data-track-label="link"><span>{translate key="navigation.otherJournals"}</span></a></li>
                                    {/if}{* $categoriesEnabled *}
                                        
                                    <li class="c-header-expander__item c-header-expander__item--keyline c-header-expander__item--keyline-first-item-only u-hide-at-lg"><a class="c-header-expander__link" href="{url page="about" op="contact"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.contact"} Information</a></li>
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
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="information" op="authors"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="navigation.infoForAuthors"}</a></li>
                                        
                                    <li class="u-hide c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="submissions"}" data-track="click" data-track-label="link" data-test="explore-nav-item">Submission guidelines</a></li>
                                        
                                    <li class="c-header-expander__item"><a class="c-header-expander__link" href="{url page="about" op="submissions" anchor="onlineSubmissions"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.onlineSubmissions"}</a></li>
                                        
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
                    {/if}
                </ul>
                    
                <div class="c-header__menu u-ml-16 u-show-lg u-show-at-lg">
                    <div class="c-header__item c-header__item--pipe">
                    {if $currentJournal}
                        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}<a class="c-header__link" href="{url page="about" op="subscriptions"}" data-track="click" data-track-action="subscribe" data-track-label="link" data-test="menu-button-subscribe">
                            <span>{translate key="about.subscriptions"}</span>
                        </a>{/if}{* $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION *}

                        {if $donationEnabled}<a class="c-header__link" href="{url page="donations"}" data-track="click" data-track-action="subscribe" data-track-label="link" data-test="menu-button-subscribe">
                            <span>{translate key="payment.type.donation"}</span>
                        </a>{/if}

                        {if $currentJournal->getSetting('membershipFeeEnabled')}<a class="c-header__link" href="{url page="about" op="memberships"}" data-track="click" data-track-action="membership" data-track-label="link" data-test="menu-button-membership">
                            <span>{translate key="about.memberships"}</span>
                        </a>{/if}
                    {else}
                        <a class="c-header__link" href="{$baseUrl}/index/search/categories" data-track="click" data-track-action="categories" data-track-label="link" data-test="menu-button-categories">
                            <span>Journals Subjects</span>
                        </a>
                    {/if}
                    </div>
                </div>
            </div>
            <ul class="c-header__menu c-header__menu--tools">
                <li class="c-header__item">
                    <a class="c-header__link" href="{url page="notification" op="subscribeMailList"}" rel="nofollow" data-track="click" data-track-action="Sign up for alerts" data-track-label="link (desktop site header)" data-track-external="">
                        <span>Sign up for alerts</span>
                        <svg role="img" aria-hidden="true" focusable="false" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg"><path d="m4 10h2.5c.27614237 0 .5.2238576.5.5s-.22385763.5-.5.5h-3.08578644l-1.12132034 1.1213203c-.18753638.1875364-.29289322.4418903-.29289322.7071068v.1715729h14v-.1715729c0-.2652165-.1053568-.5195704-.2928932-.7071068l-1.7071068-1.7071067v-3.4142136c0-2.76142375-2.2385763-5-5-5-2.76142375 0-5 2.23857625-5 5zm3 4c0 1.1045695.8954305 2 2 2s2-.8954305 2-2zm-5 0c-.55228475 0-1-.4477153-1-1v-.1715729c0-.530433.21071368-1.0391408.58578644-1.4142135l1.41421356-1.4142136v-3c0-3.3137085 2.6862915-6 6-6s6 2.6862915 6 6v3l1.4142136 1.4142136c.3750727.3750727.5857864.8837805.5857864 1.4142135v.1715729c0 .5522847-.4477153 1-1 1h-4c0 1.6568542-1.3431458 3-3 3-1.65685425 0-3-1.3431458-3-3z" fill="#222"></path></svg>
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
