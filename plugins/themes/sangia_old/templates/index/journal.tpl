{**
 * templates/index/journal.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Journal index page.
 *
 *}
{strip}
{assign var="pageTitleTranslated" value=$siteTitle}
{include file="common/header-home.tpl"}
{/strip}

{if $currentJournal && $currentJournal->getSetting('onlineIssn')}
	{assign var=issn value=$currentJournal->getSetting('onlineIssn')}
{elseif $currentJournal && $currentJournal->getSetting('printIssn')}
	{assign var=issn value=$currentJournal->getSetting('printIssn')}
{/if}
{if $printIssn} {else if $onlineIssn}
{if $currentJournal->getSetting('onlineIssn')}
<p id="issnOverview">ISSN: {if $currentJournal->getSetting('printIssn')}{$currentJournal->getSetting('printIssn')} (Print), {/if}{if $currentJournal->getSetting('onlineIssn')}{$currentJournal->getSetting('onlineIssn')} (Online){/if}</p>{/if}
{/if}

{if $currentJournal->getLocalizedSetting('history') != ''}
<div id="journal-history-link">
	<span class="icon-container info">
		This journal was previously published under other titles
        <a href="{url page="about" op="history" anchor=""}">(view Journal {translate key="about.history"})
        </a>
    </span>
</div>
{/if}

{if $journalDescription}
<div id="journalDescription" class="abstract-content formatted"><h2>Description</h2> {$journalDescription} {if $additionalHomeContent}{$additionalHomeContent}{/if}</div>
{call_hook name="Templates::Index::journal"}
{/if}

<div id="additionalHomeContent" class="abstract-content formatted"></div>

<a id="browse-volumes-and-issues" class="all btn btn-primary btn-monster" href="{url page="issue" op="archive"}">Browse Volumes &amp; Issues</a>

{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
	{* Display the table of contents or cover page of the current issue. *}
<div class="ranked">
	<h2 class="ContentHeading">Latest Issue <span class="current">Vol {$issue->getVolume()}, {if $issue->getNumber()}No {$issue->getNumber()}, {else}{/if}{$issue->getDatePublished()|date_format:'%B %Y'}</span> <span class="fileCount">({$issue->getNumArticles()|escape} {translate key="article.articlesCount"})</span></h2>	
	{include file="common/current.tpl"}
    <div class="show-all">
        <a href="{url page="search" op="titles"}" target="_blank">See all articles</a>
    </div>
</div>	
{/if}

</div>
</div>

{if $enableAnnouncementsHomepage}{* Display announcements *}                
<div id="kb-nav--aside" class="document-aside" role="complementary">    
    <div id="abstract-about" class="expander expander-open">
        <div class="expander-title" aria-expanded="true"><div class="heading"><button><a title="Click for more announcements" class="announcementsMore" href="{url page="announcement"}"><h2>{translate key="announcement.announcementsHome"}</h2></a></button></div></div>
        <div class="expander-content">
            <div class="expander-content-inner">
                <div class="summary">
                    {counter start=1 skip=1 assign="count"}
                    {iterate from=announcements item=announcement}
                    {if !$numAnnouncementsHomepage || $count <= $numAnnouncementsHomepage}
                    <section class="announcement">
                        {if $announcement->getTypeId()}
                        <h2 class="headline-2545795530">{$announcement->getAnnouncementTypeName()|escape}: {$announcement->getLocalizedTitle()|escape}</h2>
                        {else}
                        <h2 class="headline-2545795530">{$announcement->getLocalizedTitle()|escape}</h2>
                        {/if}

                        <div class="description">{$announcement->getLocalizedDescriptionShort()|nl2br}</div>
                        <tbody>
                            <tr class="details">
                                <td class="posted">{translate key="announcement.posted"}: {$announcement->getDatePosted()|date_format:"%e %B %Y"}</td>
                                <td class="more">&nbsp;</td>
                                {if $announcement->getLocalizedDescription() != null}
                                <td class="more"><a href="{url page="announcement" op="view" path=$announcement->getId()}" style="float: right;">{translate key="announcement.viewLink"}</a></td>
                                {/if}
                            </tr>
                        </tbody>
                    </section>
                    {/if}
                    {/iterate}
                    {counter}
                </div>
            </div>
        </div>
    </div>
</div>
{/if}

<div id="kb-nav--aside" class="document-aside" role="complementary">
    <div id="abstract-about" class="expander expander-open">
    	<div class="expander-title" aria-expanded="true"><div class="heading"><button><h2>About this Journal</h2></button></div></div>
    	<div class="expander-content">
    		<div class="expander-content-inner">
    			<div class="summary">
    				<dl>
    					<dt>Journal Title</dt>
    					<dd id="abstract-about-title">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</dd>

    					{if $currentJournal->getSetting('initials')}
    					<dt>Journal Initials</dt>
    					<dd>{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}</dd>{/if}

    					{if $currentJournal->getSetting('abbreviation')}
    					<dt>Journal Abbreviation</dt>
    					<dd><i>{$currentJournal->getSetting('abbreviation', $currentJournal->getPrimaryLocale())}</i></dd>{/if}    					

    					{if $issue}
                        {assign var=firstYear value=$currentJournal->getSetting('initialYear')}
                        {assign var=firstVolume value=$currentJournal->getSetting('initialVolume')}
                        {assign var=lastYear value=$issue->getYear()}
                        {assign var=lastVolume value=$issue->getVolume()}
                        <dt>Coverage</dt>
    					<dd id="abstract-about-journal-coverage">Volume {$firstVolume|escape} / {$firstYear|escape} - Volume {$lastVolume|escape} / {$lastYear|escape}</dd>
                        {/if}

    					{if $currentJournal->getSetting('printIssn')}
    					<dt id="dt-abstract-about-journal-print-issn">Print ISSN</dt>
    					<dd id="abstract-about-journal-print-issn">{$currentJournal->getSetting('printIssn')}</dd>{/if}

    					<dt id="dt-abstract-about-journal-online-issn">Online ISSN</dt>
    					<dd id="abstract-about-journal-online-issn">{if $currentJournal->getSetting('onlineIssn')}{$currentJournal->getSetting('onlineIssn')}{else}On Process{/if}</dd>

    					{if $currentJournal->getSetting('publisherInstitution')}
    					<dt id="dt-abstract-about-publisher">Publisher</dt>
    					<dd id="abstract-about-publisher">{$currentJournal->getSetting('publisherInstitution')}{else}Stipwunaraha Research Media + Bisnis</dd>
    					{/if}
    				</dl>

    				<dl>

    					<dt>Additional Links</dt>
    					<dd id="abstract-about-additional-links">
    						<ul>
    							<li>
    								<a href="{url page="user" op="register"}" target="_blank" title="For register an opens in new window">Register for Journal Updates</a>
    							</li>
    							<li>
    								<a class="external" href="{url page="about" op="editorialTeam"}" target="_blank" title="It opens in new window">Editorial Board</a>
    							</li>
    							<li>
    								<a class="external" href="{url page="about" op="editorialPolicies"}" target="_blank" title="It opens in new window">About This Journal</a>
    							</li>
    							<li>
    								<a class="external" href="{url page="author" op="submit"}" target="_blank" title="It opens in new window">Manuscript Submission</a>
    							</li>
    						</ul>

                        <dt>Information for Users</dt>
                        <dd></dd>
                            <ul>
                                <li><a href="#">Guidelines for Registration</a></li>
                                <li><a href="#">Guidelines for Submission Manuscript</a></li>
                            </ul>
                            
    					<dt>Topics</dt>
    					<dd itemprop="genre">
    						<ul class="abstract-about-subject">
                                {foreach from=$sections item=section}
    							<li><a href="{url page="search" op="search"}">{$section->getLocalizedTitle()}</a></li>
    							{if strlen($section->getLocalizedPolicy()) > 0}
    								<p>{$section->getLocalizedPolicy()|nl2br}</p>
                                {/if}{/foreach}
                                <li><a href="{url page="search" op="search?subject"}">Ekosistem Laut, Pesisir dan Pulau-Pulau Kecil</a></li>
                                <li><a href="{url page="search" op="search"}">Pengelolaan Sumber Daya Perairan</a></li>
                                <li><a href="{url page="search" op="search"}">Teknologi Budidaya Perairan</a></li>
                                <li><a href="{url page="search" op="search"}">Pemanfaatan Sumber Daya Perikanan</a></li>
                                <li><a href="{url page="search" op="search"}">Kemaritiman dan Industri Kelautan</a></li>
                                <li><a href="{url page="search" op="search"}">Sosial Ekonomi Perikanan</a></li>
    						</ul>
    					</dd>
                    </dl>

    				<dl>
    					<dt>For Authors and Editors</dt>
    					<dd id="abstract-about-additional-links">
    						<ul>
    							{if $currentJournal->getLocalizedSetting('authorGuidelines') != ''}
    							<li>
    								<a href="{url page="about" op="submissions" anchor="authorGuidelines"}" >Instructions for Authors</a>
    							</li>{/if}

    							{if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}
    							<li>
    								<a class="external" href="{url page="about" op="editorialPolicies" anchor="focusAndScope"}" target="_blank" title="It opens in new window">Aims and Scope</a>
    							</li>{/if}

    							{if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}
    							<li>
    								<a class="external" href="{url page="about" op="submissions" anchor="copyrightNotice"}" target="_blank" title="It opens in new window">Copyright Information</a>
    							</li>{/if}

    							{foreach from=$navMenuItems item=navItem key=navItemKey}
    							{if $navItem.url != '' && $navItem.name != ''}
    								<li class="navItem" id="navItem-{$navItemKey|escape}"><a href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{$baseUrl}{$navItem.url|escape}{/if}" target="_blank" title="It opens in new window">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</a></li>
    							{/if}
    							{/foreach}

    							{if $currentJournal->getSetting('journalPaymentsEnabled') && ($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled'))}
    							<li>
    								<a class="external" href="{url page="about" op="submissions" anchor="authorFees"}" target="_blank" title="It opens in new window">Authors Fees</a>
    							</li>{/if}

    							{if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.membershipFeeEnabled && $journalSettings.membershipFee > 0}
    							<li>
    								<a class="external" href="{url page="about" op="memberships"}" target="_blank" title="It opens in new window">Memberships</a>
    							</li>{/if}

    							{call_hook name="Templates::Common::Header::Navbar::CurrentJournal"}

    							<li>
    								<a class="external" href="{url page="about" op="contact"}" target="_blank" title="It opens in new window">Contact this Journal</a>
    							</li>

    						</ul>    	
    					</dl>
    				</div>
    			</div>
    		</div>
    	</div>

{include file="common/footer.tpl"}

