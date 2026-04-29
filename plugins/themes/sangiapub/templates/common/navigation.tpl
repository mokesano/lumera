{**
 * navigation.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Journal site header.
 *}
 
<div role="navigation" class="u-hide lm-primary-navigation">
    <div class="row">
        <div class="lm-column">
            <ul class="lm-nav-root">
                <li><a href="{url page="$currentJournal"}">Explore content <svg class="lm-icon-arrow-down" viewBox="0 0 32 32"><path fill="inherit" d="M28 11.5c0-0.4-0.1-0.8-0.4-1.1-0.6-0.6-1.5-0.6-2.1 0l-9.5 10.2-9.4-10.2c-0.6-0.6-1.5-0.6-2.1 0-0.6 0.6-0.6 1.5 0 2.1l10.1 10.9c0.8 0.8 2 0.8 2.8 0l10.2-10.9c0.3-0.3 0.4-0.7 0.4-1.1z"></path></svg></a>
                    <ul class="lm-nav-sub">
                        {if $currentJournal && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
                        <li><a href="{url page="issue" op="current"}" title="Browse Current Issue">{translate key="journal.currentIssue"}</a></li>
                        <li><a href="{url page="issue" op="archive"}" title="Browse Issues">Browse Issues</a></li>                        
                        <li><a href="{url page="search" op="titles"}" title="Browse Title">Title Index</a></li>
                        <li><a href="{url page="browseSearch" op="sections"}" title="Browse Section">Browse Section</a></li>
                        <li><a href="{url page="browseSearch" op="identifyTypes"}" title="Browse Article Type">Browse Article Type</a></li>
                        <li><a href="{url page="search" op="authors"}" title="Browse Authors">Authors Index</a></li>
                        {/if}
                    </ul>
                </li>
                <li><a href="#">{translate key="navigation.about"} the journal <svg class="lm-icon-arrow-down" viewBox="0 0 32 32"><path fill="inherit" d="M28 11.5c0-0.4-0.1-0.8-0.4-1.1-0.6-0.6-1.5-0.6-2.1 0l-9.5 10.2-9.4-10.2c-0.6-0.6-1.5-0.6-2.1 0-0.6 0.6-0.6 1.5 0 2.1l10.1 10.9c0.8 0.8 2 0.8 2.8 0l10.2-10.9c0.3-0.3 0.4-0.7 0.4-1.1z"></path></svg></a>
                    <ul class="lm-nav-sub">

                        {if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}<li><a href="{url page="about" op="editorialPolicies" anchor="focusAndScope"}">{translate key="about.focusAndScope"}</a></li>{/if}

                        <li><a href="{url page="about" op="editorialPolicies" anchor="sectionPolicies"}">{translate key="about.sectionPolicies"}</a></li>

                        {if $currentJournal->getLocalizedSetting('reviewPolicy') != ''}<li><a href="{url page="about" op="editorialPolicies" anchor="peerReviewProcess"}">{translate key="about.peerReviewProcess"}</a></li>{/if}

                        {if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}<li><a href="{url page="about" op="editorialPolicies" anchor="publicationFrequency"}">{translate key="about.publicationFrequency"}</a></li>{/if}

                        <li><a href="{url page="about" op="editorialPolicies" anchor="archiving"}">{translate key="about.archiving"}</a></li>

                        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}<li><a href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}">{translate key="about.openAccessPolicy"}</a></li>{/if}
                        
                        {call_hook name="Templates::About::Index::Policies"}

                        {foreach key=key from=$currentJournal->getLocalizedSetting('customAboutItems') item=customAboutItem}
                        {if !empty($customAboutItem.title)}
                            <li><a href="{url page="about" op="editorialPolicies" anchor="{custom-$key}"}">{$customAboutItem.title|escape}</a></li>
                        {/if}
                        {/foreach}                        

                        {call_hook name="Templates::Common::Header::Navbar::CurrentJournal"}
                        
                        {foreach from=$navMenuItems item=navItem key=navItemKey}
                            {if $navItem.url != '' && $navItem.name != ''}
                                <li class="navItem" id="navItem-{$navItemKey|escape}"><a href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{$baseUrl}{$navItem.url|escape}{/if}">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</a></li>
                            {/if}
                        {/foreach}   

                        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
                            <li><a href="{url page="about" op="subscriptions"}">{translate key="about.subscriptions"}</a></li>
                            {if !empty($journalSettings.enableAuthorSelfArchive)}<li><a href="{url page="about" op="editorialPolicies" anchor="authorSelfArchivePolicy"}">{translate key="about.authorSelfArchive"}</a></li>{/if}
                            {if !empty($journalSettings.enableDelayedOpenAccess)}<li><a href="{url page="about" op="editorialPolicies" anchor="delayedOpenAccessPolicy"}">{translate key="about.delayedOpenAccess"}</a></li>{/if}
                            {if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.acceptSubscriptionPayments && $journalSettings.purchaseIssueFeeEnabled && $journalSettings.purchaseIssueFee > 0}<li><a href="{url page="about" op="editorialPolicies" anchor="purchaseIssue"}">{translate key="about.purchaseIssue"}</a></li>{/if}
                            {if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.acceptSubscriptionPayments && $journalSettings.purchaseArticleFeeEnabled && $journalSettings.purchaseArticleFee > 0}<li><a href="{url page="about" op="editorialPolicies" anchor="purchaseArticle"}">{translate key="about.purchaseArticle"}</a></li>{/if}
                        {/if}{* $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION *}

                        {if $currentJournal->getLocalizedSetting('history') != ''}<li><a href="{url page="about" op="history"}">{translate key="about.history"}</a></li>{/if}

                        {if $publicStatisticsEnabled}<li><a href="{url page="about" op="statistics"}">{translate key="about.statistics"}</a></li>{/if}
                        {call_hook name="Templates::About::Index::Other"}

                    </ul>
                </li>
                
                <li><a href="#">Editorial Team <svg class="lm-icon-arrow-down" viewBox="0 0 32 32"><path fill="inherit" d="M28 11.5c0-0.4-0.1-0.8-0.4-1.1-0.6-0.6-1.5-0.6-2.1 0l-9.5 10.2-9.4-10.2c-0.6-0.6-1.5-0.6-2.1 0-0.6 0.6-0.6 1.5 0 2.1l10.1 10.9c0.8 0.8 2 0.8 2.8 0l10.2-10.9c0.3-0.3 0.4-0.7 0.4-1.1z"></path></svg></a>
                    <ul class="lm-nav-sub">
                        <li id="editorialTeamLink"><a href="{url page="about" op="editorialTeam"}">{translate key="about.editorialTeam"}</a></li>
                        {if $peopleGroups}
                            {iterate from=peopleGroups item=peopleGroup}
                                <li><a href="{url page="about" op="displayMembership" path=$peopleGroup->getId()}">{$peopleGroup->getLocalizedTitle()|escape}</a></li>
                            {/iterate}
                        {/if}
                        {call_hook name="Templates::About::Index::People"}

                        {if not ($currentJournal->getSetting('publisherInstitution') == '' && $currentJournal->getLocalizedSetting('publisherNote') == '' && $currentJournal->getLocalizedSetting('contributorNote') == '' && empty($journalSettings.contributors) && $currentJournal->getLocalizedSetting('sponsorNote') == '' && empty($journalSettings.sponsors))}<li><a href="{url page="about" op="journalSponsorship"}">{translate key="about.journalSponsorship"}</a></li>{/if}
                        
                        <li><a href="{url page="about" op="contact"}">{translate key="about.contact"}</a></li>
                    </ul>
                </li>      
                                      
                <li><a href="#">Submissions <svg class="lm-icon-arrow-down" viewBox="0 0 32 32"><path fill="inherit" d="M28 11.5c0-0.4-0.1-0.8-0.4-1.1-0.6-0.6-1.5-0.6-2.1 0l-9.5 10.2-9.4-10.2c-0.6-0.6-1.5-0.6-2.1 0-0.6 0.6-0.6 1.5 0 2.1l10.1 10.9c0.8 0.8 2 0.8 2.8 0l10.2-10.9c0.3-0.3 0.4-0.7 0.4-1.1z"></path></svg></a>
                    <ul class="lm-nav-sub">
                        <li><a href="{url page="about" op="submissions"}">Submission guidelines</a></li>
                        <li><a href="{url page="about" op="submissions" anchor="onlineSubmissions"}">{translate key="about.onlineSubmissions"}</a></li>
                        {if $currentJournal->getLocalizedSetting('authorGuidelines') != ''}<li><a href="{url page="about" op="submissions" anchor="authorGuidelines"}">{translate key="about.authorGuidelines"}</a></li>{/if}
                        {if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}<li><a href="{url page="about" op="submissions" anchor="copyrightNotice"}">{translate key="about.copyrightNotice"}</a></li>{/if}
                        {if $currentJournal->getLocalizedSetting('privacyStatement') != ''}<li><a href="{url page="about" op="submissions" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></li>{/if}
                        {if $currentJournal->getSetting('journalPaymentsEnabled') && ($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled'))}<li><a href="{url page="about" op="submissions" anchor="authorFees"}">{translate key="about.authorFees"}</a></li>{/if}
                        {call_hook name="Templates::About::Index::Submissions"}

                    </ul>
                </li>
                <li><a href="#">Publish with us <svg class="lm-icon-arrow-down" viewBox="0 0 32 32"><path fill="inherit" d="M28 11.5c0-0.4-0.1-0.8-0.4-1.1-0.6-0.6-1.5-0.6-2.1 0l-9.5 10.2-9.4-10.2c-0.6-0.6-1.5-0.6-2.1 0-0.6 0.6-0.6 1.5 0 2.1l10.1 10.9c0.8 0.8 2 0.8 2.8 0l10.2-10.9c0.3-0.3 0.4-0.7 0.4-1.1z"></path></svg></a>
                    <ul class="lm-nav-sub">
                        <li><a href="{url page="information" op="authors"}">{translate key="navigation.infoForAuthors"}</a></li>                        
                        <li><a href="{url page="information" op="readers"}">{translate key="navigation.infoForReaders"}</a></li>                    
                        <li><a href="{url page="information" op="librarians"}">{translate key="navigation.infoForLibrarians"}</a></li>
                        {if $donationEnabled}<li><a href="{url page="donations"}">{translate key="payment.type.donation"}</a></li>{/if}
                        {if $currentJournal->getSetting('membershipFee')}<li><a href="{url page="about" op="memberships"}">{translate key="about.memberships"}</a></li>{/if}
                        {if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.membershipFeeEnabled && $journalSettings.membershipFee > 0}<li><a href="{url op="memberships"}">{translate key="about.memberships"}</a></li>{/if}                        
                        <li><a class="button-base-2906877647" href="{url page="author" op="submit"}" target="_blank" data-track="click"><span class="button-label-1281676810">Submit manuscript </span>
                        <svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1969128361"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg></a></li>                        
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
