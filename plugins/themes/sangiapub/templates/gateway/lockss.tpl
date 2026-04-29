{**
 * templates/gateway/lockss.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * LOCKSS Publisher Manifest gateway page.
 * NOTE: This page is not localized in order to provide a consistent interface to LOCKSS across all OJS installations. It is not meant to be accessed by humans.
 *
 *}
{strip}
{assign var="pageTitleTranslated" value="LOCKSS Publisher Manifest"}
{include file="common/header.tpl"}
{/strip}

{if $journals}
    <h3 class="bold text-l">Archive of Published Issues</h3>
    
    <ul>
    {iterate from=journals item=journal}
    	{if $journal->getSetting('enableLockss')}<li><a href="{url journal=$journal->getPath() page="gateway" op="lockss"}">{$journal->getLocalizedTitle()|escape}</a></li>{/if}
    {/iterate}
    </ul>

{else}

    <p>{if $prevYear !== null}<a href="{url op="lockss" year=$prevYear}" class="action">&lt;&lt; Previous</a>{else}<span class="disabled heading">&lt;&lt; Previous</span>{/if} | {if $nextYear !== null}<a href="{url op="lockss" year=$nextYear}" class="action">Next &gt;&gt;</a>{else}<span class="disabled heading">Next &gt;&gt;</span>{/if}</p>
    
    <h3 class="bold text-l">Archive of Published Issues: {$year|escape}</h3>
    
    <ul>
    {iterate from=issues item=issue}
    	<li><a href="{url page="issue" op="view" path=$issue->getBestIssueId($journal)}">{$issue->getIssueIdentification()|strip_unsafe_html|nl2br}</a></li>
    {/iterate}
    </ul>
    
    {if $showInfo}
    
    <div class="separator"></div>
    
    <h3 class="bold text-l">Front Matter</h3>
    
    <p>Front Matter associated with this Archival Unit includes:</p>
    
    <ul>
    	<li><a href="{url page="about"}">About the Journal</a></li>
    	<li><a href="{url page="about" op="submissions"}">Submission Guidelines</a></li>
    	<li><a href="{url page="about" op="contact"}">Contact Information</a></li>
    </ul>
    
    <div class="separator"></div>
    
    <h3 class="bold text-l">Metadata</h3>
    
    <p class="separated">Metadata associated with this Archival Unit includes:</p>
    
    <div class="journal-content">
        <div class="info-grid">
                    
            <div class="info-card">
                <h3 class="info-label">Journal URL</h3>
                <p class="info-value">
                    <a href="{$journal->getUrl()|escape}" class="journal-url" target="_blank">
                        {$journal->getUrl()|escape}
                    </a>
                </p>
            </div>
            
            <div class="info-card">
                <h3 class="info-label">Journal Title</h3>
                <p class="info-value">{$journal->getLocalizedTitle()|escape}</p>
            </div>
            
            <div class="info-card">
                <h3 class="info-label">Publisher</h3>
                <p class="info-value">
                    <a href="{$journal->getSetting('publisherUrl')|escape}" target="_blank">{$journal->getSetting('publisherInstitution')|escape}</a>
                </p>
            </div>
    
            <div class="info-card">
                <h3 class="info-label">Publisher Email</h3>
                <p class="info-value">
                    {mailto address=$journal->getSetting('contactEmail')|escape encode="hex"}
                </p>
            </div>
    
            {if $journal->getSetting('issn')}
            <div class="info-card">
                <h3 class="info-label">ISSN</h3>
                <p class="info-value">
                    <span class="issn-badge">{$journal->getSetting('issn')|escape}</span>
                </p>
            </div>
            {/if}
    
            <div class="info-card">
                <h3 class="info-label">Supported Languages</h3>
                <div class="info-value">
                    <div class="language-tags">
                        {foreach from=$locales key=localeKey item=localeName}
                            <span class="language-tag">{$localeName|escape} ({$localeKey|escape})</span>
                        {/foreach}
                    </div>
                </div>
            </div>
    
            {if $journal->getLocalizedSetting('searchKeywords')}
            <div class="info-card">
                <h3 class="info-label">Keywords</h3>
                <p class="info-value">{$journal->getLocalizedSetting('searchKeywords')|escape}</p>
            </div>
            {/if}
    
            {if $journal->getLocalizedSetting('searchDescription')}
            <div class="info-card description-card">
                <h3 class="info-label">Description</h3>
                <p class="info-value">{$journal->getLocalizedSetting('searchDescription')|escape}</p>
            </div>
            {/if}
    
            {if $journal->getLocalizedSetting('copyrightNotice')}
            <div class="copyright-section">
                <h3 class="copyright-title u-mb-16">Copyright Notice</h3>
                <div>{$journal->getLocalizedSetting('copyrightNotice')|nl2br}</div>
            </div>
            {/if}
        </div>
    </div>
    {/if}

{/if}

<div style="text-align: center; width: 250px; margin: 0 auto">
	<a href="http://www.lockss.org/"><img src="{$baseUrl}/templates/images/lockss.gif" style="border: 0;" alt="LOCKSS" /></a>
	<br />
	LOCKSS system has permission to collect, preserve, and serve this Archival Unit.
</div>

{include file="common/footer.tpl"}

