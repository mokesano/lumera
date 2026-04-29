{**
 * templates/about/editorialPolicies.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Journal / Editorial Policies.
 * 
 *}
{strip}
{assign var="pageTitle" value="about.aboutTheJournal"}
{include file="common/header.tpl"}
{/strip}

{if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}
<div id="focusAndScope" class="block">
    <header class="c-anchored-heading"><h3>{translate key="about.focusAndScope"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
    <p>{$currentJournal->getLocalizedSetting('focusScopeDesc')|nl2br}</p>

    <div class="separator">&nbsp;</div>
</div>
{/if}

<div id="sectionPolicies" class="block">
    <header class="c-anchored-heading"><h3>{translate key="about.sectionPolicies"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
{foreach from=$sections item=section}{if !$section->getHideAbout()}
<section class="clearfix">
	<h4>{$section->getLocalizedTitle()}</h4>
	{if strlen($section->getLocalizedPolicy()) > 0}
		<p>{$section->getLocalizedPolicy()|nl2br}</p>
	{/if}

	{assign var="hasEditors" value=0}
	{foreach from=$sectionEditorEntriesBySection item=sectionEditorEntries key=key}
	{if $key == $section->getId()}
		{foreach from=$sectionEditorEntries item=sectionEditorEntry}
		{assign var=sectionEditor value=$sectionEditorEntry.user}
		{assign var="profileImage" value=$sectionEditor->getSetting('profileImage')}
		{if 0 == $hasEditors++}
		<h5 class="u-mb-16 u-mt-16"><b>{translate key="user.role.editors"}</b>:</h5>
		<ul class="app-editor-row">
		{/if}
		<li editor-id="00" class="app-editor-row__item">
		    <div class="editor">
    		    <div class="editor-name cms-person">
			        {if $profileImage}
			        <picture>
			            <source srcset="{$sitePublicFilesDir}/{$profileImage.uploadName}?as=webp" type="image/webp">
    			        <img loading="lazy" title="{$sectionEditor->getFullName()|escape}" class="avatar editor sangia-author" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" width="150" height="auto">
	    			</picture>
	    			{else}
			        <picture>
			            <source srcset="//loop.frontiersin.org/images/profile/479531/203?as=webp" type="image/webp">
    			        <img loading="lazy" title="{$sectionEditor->getFullName()|escape}" class="avatar editor sangia-author" src="//loop.frontiersin.org/images/profile/479531/203" width="150" height="auto">
	    			</picture>	    			
			        {/if}
			        <h5 class="u-h4 u-mt-0">{$sectionEditor->getFullName()|escape}</h5>
    		        {assign var=affiliation value=$sectionEditor->getLocalizedAffiliation()}
    		        {assign var=countryCode value=$sectionEditor->getCountry()}
    		        {assign var=country value=$countries.$countryCode}
    		        <p class="u-font-sans">{if $sectionEditor->getLocalizedAffiliation()} {$sectionEditor->getLocalizedAffiliation()|escape}{else}Affiliation and country of the editor is under construction{/if} {$country|escape}</p>
    			    </div>
			    </div>
		</li>
		{/foreach}
	{/if}
	{/foreach}
	{if $hasEditors}</ul>{/if}

	<table width="60%">
		<tr>
			<td width="33%">{if !$section->getEditorRestricted()}{icon name="checked"}{else}{icon name="unchecked"}{/if} {translate key="manager.sections.open"}</td>
			<td width="33%">{if $section->getMetaIndexed()}{icon name="checked"}{else}{icon name="unchecked"}{/if} {translate key="manager.sections.indexed"}</td>
			<td width="34%">{if $section->getMetaReviewed()}{icon name="checked"}{else}{icon name="unchecked"}{/if} {translate key="manager.sections.reviewed"}</td>
		</tr>
	</table>
</section>
{/if}{/foreach}
</div>

<div class="separator">&nbsp;</div>

{if $currentJournal->getLocalizedSetting('reviewPolicy') != ''}
<div id="peerReviewProcess" class="block">
    <header class="c-anchored-heading"><h3>{translate key="about.peerReviewProcess"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
    <p>{$currentJournal->getLocalizedSetting('reviewPolicy')|nl2br}</p>

    <div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}
<div id="publicationFrequency" class="block">
    <header class="c-anchored-heading"><h3>{translate key="about.publicationFrequency"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
    <p>{$currentJournal->getLocalizedSetting('pubFreqPolicy')|nl2br}</p>

    <div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''} 
<div id="openAccessPolicy" class="block">
    <header class="c-anchored-heading"><h3>{translate key="about.openAccessPolicy"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
    <p>{$currentJournal->getLocalizedSetting('openAccessPolicy')|nl2br}</p>

    <div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableAuthorSelfArchive')} 
<div id="authorSelfArchivePolicy" class="block">
    <header class="c-anchored-heading"><h3>{translate key="about.authorSelfArchive"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header> 
    <p>{$currentJournal->getLocalizedSetting('authorSelfArchivePolicy')|nl2br}</p>

    <div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableDelayedOpenAccess')}
<div id="delayedOpenAccessPolicy" class="block">
    <header class="c-anchored-heading"><h3>{translate key="about.delayedOpenAccess"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header> 
    <p>{translate key="about.delayedOpenAccessDescription1"} {$currentJournal->getSetting('delayedOpenAccessDuration')} {translate key="about.delayedOpenAccessDescription2"}</p>
    {if $currentJournal->getLocalizedSetting('delayedOpenAccessPolicy') != ''}
    	<p>{$currentJournal->getLocalizedSetting('delayedOpenAccessPolicy')|nl2br}</p>
    {/if}

    <div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentJournal->getSetting('enableLockss') && $currentJournal->getLocalizedSetting('lockssLicense') != ''}
<div id="archiving" class="block">
    <header class="c-anchored-heading"><h3>{translate key="about.archiving"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
    <p>{$currentJournal->getLocalizedSetting('lockssLicense')|nl2br}</p>

    <div class="separator">&nbsp;</div>
</div>
{/if}

{foreach key=key from=$currentJournal->getLocalizedSetting('customAboutItems') item=customAboutItem name=customAboutItems}
	{if !empty($customAboutItem.title)}
		<div id="custom-{$key|escape}" class="block">
		    <header class="c-anchored-heading"><h3>{$customAboutItem.title|escape}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
		    </header>
		<p>{$customAboutItem.content|nl2br}</p>
		{if !$smarty.foreach.customAboutItems.last}<div class="separator">&nbsp;</div>{/if}
		</div>
	{/if}
{/foreach}

{include file="common/footer.tpl"}
