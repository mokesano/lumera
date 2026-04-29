{**
 * templates/index/site.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site index.
 *
 *}
{strip}
{if $siteTitle}
	{assign var="pageTitleTranslated" value=$siteTitle}
{/if}
{include file="common/header-auth.tpl"}
{/strip}

{if $intro}<div id="intro">{$intro|nl2br}</div>{/if}

<a name="journals"></a>

{if $useAlphalist}
	<p class="index--item">{foreach from=$alphaList item=letter}<a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="{url searchInitial=$letter sort="title"}">{if $letter == $searchInitial}<strong>{$letter|escape}</strong>{else}{$letter|escape}{/if}</a> {/foreach}<a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="{url}">{if $searchInitial==''}<strong>{translate key="common.all"}</strong>{else}{translate key="common.all"}{/if}</a></p>
{/if}

<table id="journals" width="100%" class="listing page_links">
	<tr class="page-listing paging">
		<td {if !$currentJournal}colspan="2" {/if}align="left">{page_info iterator=$journals}</td>
		<td colspan="2" align="right">{page_links anchor="journals" name="journals" iterator=$journals}</td>
	</tr>
</table>

<div id="journals" class="journals-index">
    <ol id="results-list" class="content-item-list">
    {iterate from=journals item=journal}
        <li id="articleInfoList" class="has-cover">
            <p class="content-type">{translate key="common.journal"}</p>
            <div class="lozenges"></div>
        
        {assign var="displayHomePageImage" value=$journal->getLocalizedSetting('homepageImage')}
        {assign var="displayHomePageLogo" value=$journal->getLocalizedPageHeaderLogo(true)}
        {assign var="displayPageHeaderLogo" value=$journal->getLocalizedPageHeaderLogo()}
        
        <noscript>
        {if $displayJournalThumbnail && is_array($displayJournalThumbnail)}
		{assign var="altText" value=$journal->getLocalizedSetting('journalThumbnailAltText')}<img class="cover" alt="" src="{$journalFilesPath}{$journal->getId()}/{$displayJournalThumbnail.uploadName|escape:"url"}" {if $altText != ''}alt="{$altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} style="display: inline;" width="95" />
		{elseif $displayHomePageImage && is_array($displayHomePageImage)}
		    {assign var="altText" value=$journal->getLocalizedSetting('homepageImageAltText')}<img class="cover lazy" src="{$journalFilesPath}{$journal->getId()}/{$displayHomePageImage.uploadName|escape:"url"}" {if $altText != ''}alt="{$altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} style="display: inline;" width="95" />
		{elseif $displayHomePageLogo && is_array($displayHomePageLogo)}
		{assign var="altText" value=$journal->getLocalizedSetting('homeHeaderLogoImageAltText')}
		    <img class="cover lazy" src="{$journalFilesPath}{$journal->getId()}/{$displayHomePageLogo.uploadName|escape:"url"}" {if $altText != ''}alt="{$altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} style="display: inline;" width="95" />
		{elseif $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
		{assign var="altText" value=$journal->getLocalizedSetting('pageHeaderLogoImageAltText')}
		    <img class="cover lazy" src="{$journalFilesPath}{$journal->getId()}/{$displayPageHeaderLogo.uploadName|escape:"url"}" {if $altText != ''}alt="{$altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} style="display: inline;" width="95" />
		{else} <img class="cover lazy" src="//media.stipwunaraha.ac.id/img/img-default.jpg" alt="Stipwunaraha Media & Publishing Group" style="display: inline;" width="95" /> {/if}
		</noscript>
		
		{if $displayJournalThumbnail && is_array($displayJournalThumbnail)}
			{assign var="altText" value=$journal->getLocalizedSetting('journalThumbnailAltText')}<img class="cover" alt="" src="{$journalFilesPath}{$journal->getId()}/{$displayJournalThumbnail.uploadName|escape:"url"}" style="display: inline;" width="95" />
			
		{elseif $displayHomePageImage && is_array($displayHomePageImage)}
		    {assign var="altText" value=$journal->getLocalizedSetting('homepageImageAltText')}
		    <img class="cover lazy" src="{$journalFilesPath}{$journal->getId()}/{$displayHomePageImage.uploadName|escape:"url"}" {if $altText != ''}alt="{$altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} style="display: inline;" width="95" />
		
		{elseif $displayHomePageLogo && is_array($displayHomePageLogo)}
		{assign var="altText" value=$journal->getLocalizedSetting('homeHeaderLogoImageAltText')}
		    <img class="cover lazy" src="{$journalFilesPath}{$journal->getId()}/{$displayHomePageLogo.uploadName|escape:"url"}" {if $altText != ''}alt="{$altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} style="display: inline;" width="95" />
		
		{elseif $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
		{assign var="altText" value=$journal->getLocalizedSetting('pageHeaderLogoImageAltText')}
		    <img class="cover lazy" src="{$journalFilesPath}{$journal->getId()}/{$displayPageHeaderLogo.uploadName|escape:"url"}" {if $altText != ''}alt="{$altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} style="display: inline;" width="95" />
		{else}
		    <img class="cover lazy" src="//media.stipwunaraha.ac.id/img/img-default.jpg" alt="Stipwunaraha Media & Publishing Group" style="display: inline;" width="95" />
		{/if}
		
		{if $site->getSetting('showTitle')}
		<div class="text">
		    <h3><a href="{url journal=$journal->getPath()}">{$journal->getLocalizedTitle()|escape}</a></h3>
		    <p class="coverage">Volume Number / Year - Volume Number / Year (Data not available)</p>
		    {if $site->getSetting('showDescription')}
		    {if $journal->getLocalizedDescription()}
			<div class="journalDescription" id="journalDescription-{$journal->getId()|escape}">{$journal->getLocalizedDescription()|nl2br}
			</div>
			{/if}{/if}
		</div>
		{/if}
		</li>
    {/iterate}
    </ol>
</div>
{if $journals->wasEmpty()}
	{translate key="site.noJournals"}
{/if}

<table id="journals" width="100%" class="listing page_links">
	<tr class="page-listing paging">
		<td {if !$currentJournal}colspan="2" {/if}align="left">{page_info iterator=$journals}</td>
		<td colspan="2" align="right">{page_links anchor="journals" name="journals" iterator=$journals}</td>
	</tr>
</table>

{include file="common/footer.tpl"}

