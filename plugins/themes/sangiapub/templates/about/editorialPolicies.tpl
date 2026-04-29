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
{include file="common/header-gfa.tpl"}
{/strip}

<ul class="c-nav-menu">
	{if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}<li id="linkFocusScopeDesc" class="c-nav__link"><a href="{url op="editorialPolicies" anchor="focusAndScope"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.focusAndScope"}</a></li>{/if}
	<li id="linkEditorialPolicies" class="c-nav__link"><a href="{url op="editorialPolicies" anchor="sectionPolicies"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.sectionPolicies"}</a></li>
	{if $currentJournal->getLocalizedSetting('reviewPolicy') != ''}<li id="linkReviewPolicy" class="c-nav__link"><a href="{url op="editorialPolicies" anchor="peerReviewProcess"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.peerReviewProcess"}</a></li>{/if}
	{if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}<li id="linkPubFreqPolicy" class="c-nav__link"><a href="{url op="editorialPolicies" anchor="publicationFrequency"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.publicationFrequency"}</a></li>{/if}
	{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}<li id="linkOpenAccessPolicy" class="c-nav__link"><a href="{url op="editorialPolicies" anchor="openAccessPolicy"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.openAccessPolicy"}</a></li>{/if}
	{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
		{if $currentJournal->getSetting('enableAuthorSelfArchive')}<li id="linkenabledAuthorSelfArchive" class="c-nav__link"><a href="{url op="editorialPolicies" anchor="authorSelfArchivePolicy"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.authorSelfArchive"}</a></li>{/if}
		{if $currentJournal->getSetting('enableDelayedOpenAccess')}<li id="linkenabledDelayedOpenAccess" class="c-nav__link"><a href="{url op="editorialPolicies" anchor="delayedOpenAccessPolicy"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.delayedOpenAccess"}</a></li>{/if}
		{if $paymentConfigured && $currentJournal->getSetting('journalPaymentsEnabled') && $currentJournal->getSetting('acceptSubscriptionPayments') && $currentJournal->getSetting('purchaseIssueFeeEnabled') && $currentJournal->getSetting('purchaseIssueFee') > 0}<li id="linkpurchaseIssue" class="c-nav__link"><a href="{url op="editorialPolicies" anchor="purchaseIssue"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.purchaseIssue"}</a></li>{/if}
		{if $paymentConfigured && $currentJournal->getSetting('journalPaymentsEnabled') && $currentJournal->getSetting('acceptSubscriptionPayments') && $currentJournal->getSetting('purchaseArticleFeeEnabled') && $currentJournal->getSetting('purchaseArticleFee') > 0}<li id="linkpurchaseArticle" class="c-nav__link"><a href="{url op="editorialPolicies" anchor="purchaseArticle"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.purchaseArticle"}</a></li>{/if}
	{/if}
	{if $currentJournal->getSetting('enableLockss') && $currentJournal->getLocalizedSetting('lockssLicense') != ''}<li id="linkLockssLicense" class="c-nav__link"><a href="{url op="editorialPolicies" anchor="archiving"}" data-track="click" data-track-label="link" data-test="explore-nav-item">{translate key="about.archiving"}</a></li>{/if}
	{foreach key=key from=$currentJournal->getLocalizedSetting('customAboutItems') item=customAboutItem}
		{if !empty($customAboutItem.title)}
			<li id="link{$customAboutItem.title|replace:" ":""|escape}" class="c-nav__link"><a href="{url op="editorialPolicies" anchor=$customAboutItem.title|replace:" ":""|escape}" data-track="click" data-track-label="link" data-test="explore-nav-item">{$customAboutItem.title|escape}</a></li>
		{/if}
	{/foreach}
</ul>

<section class="content u-mt-48">
	
{if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}
<div id="focusAndScope" class="block">
    <header class="c-anchored-heading"><h2>{translate key="about.focusAndScope"}</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
<p>{$currentJournal->getLocalizedSetting('focusScopeDesc')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

<div id="sectionPolicies" class="block">
    <header class="c-anchored-heading"><h2>{translate key="about.sectionPolicies"}</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
{foreach from=$sections item=section}{if !$section->getHideAbout()}
<section id="{$section->getLocalizedTitle()|replace:" ":"-"}" class="section-area clearfix" aria-labelledby="{$section->getLocalizedTitle()|replace:" ":""}">
    <div class="section-describe">
    	<h4 class="section-title">
    	    <a href="{url page="section" op=$section->getSectionUrlTitle()}">{$section->getLocalizedTitle()|escape}</a>
    	</h4>
    	{if strlen($section->getLocalizedPolicy()) > 0}
    	<p class="section-describes">{$section->getLocalizedPolicy()|nl2br}</p>
    	{/if}
    	<table width="60%">
    		<tr>
    			<td width="33%">{if !$section->getEditorRestricted()}{icon name="checked"}{else}{icon name="unchecked"}{/if} {translate key="manager.sections.open"}</td>
    			<td width="33%">{if $section->getMetaIndexed()}{icon name="checked"}{else}{icon name="unchecked"}{/if} {translate key="manager.sections.indexed"}</td>
    			<td width="34%">{if $section->getMetaReviewed()}{icon name="checked"}{else}{icon name="unchecked"}{/if} {translate key="manager.sections.reviewed"}</td>
    		</tr>
    	</table>
	</div>
	{assign var="hasEditors" value=0}
	{foreach from=$sectionEditorEntriesBySection item=sectionEditorEntries key=key}
	{if $key == $section->getId()}
	<div class="editors-section">
	{if 0 == $hasEditors++}{if $hasEditors}
	<h2 class="section-title u-h2 u-mt-32">{translate key="user.role.sectionEditor"} on <span class="section-name italic">{$section->getLocalizedTitle()}</span></h2>
	{else}
	<h2 class="section-title u-h2 u-mt-32">{translate key="user.role.sectionEditors"} on <span class="section-name italic">{$section->getLocalizedTitle()}</span></h2>
	{/if}{/if}
	<ul class="app-editor-row">
		{foreach from=$sectionEditorEntries item=sectionEditorEntry}
		{assign var=sectionEditor value=$sectionEditorEntry.user}
    	{assign var="profileImage" value=$sectionEditor->getSetting('profileImage')}
    	{assign var="sectionEditorAffiliation" value=$sectionEditorEntry.affiliationString}
    	{assign var="sectionEditorCountry" value=$sectionEditorEntry.countryString}
		<li id="SE-{$sectionEditor->getId()|string_format:"%07d"}" class="app-editor-row__item editor-member">
		    <div class="sangia-editor--profile member editor">
    		    <div class="section-editor-avatar cms-person">
			        {if $profileImage}
			        <picture>
			            <source srcset="{$sitePublicFilesDir}/{$profileImage.uploadName}?as=webp" type="image/webp">
    			        <img class="lazyload editor sangia-author" loading="lazy" title="{$sectionEditor->getFullName()|escape}" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" width="100" height="auto">
	    			</picture>
	    			{else}
			        <picture>
			            <source {if $sectionEditor->getGender() == "M"}srcset="//assets.sangia.org/static/images/contactPersonM.png?as=webp"{elseif $sectionEditor->getGender() == "F"}srcset="//assets.sangia.org/static/images/contactPersonF.png?as=webp"{elseif $sectionEditor->getGender() == "O"}srcset="//scholar.google.co.id/citations/images/avatar_scholar_128.png?as=webp"{elseif $sectionEditor->getGender() == ""}srcset="//loop.frontiersin.org/images/profile/479531/203?as=webp"{/if} type="image/webp">
    			        <img class="lazyload editor sangia-author" loading="lazy" id="editor-{$sectionEditor->getId()|string_format:"%07d"}" title="{$sectionEditor->getFullName()|escape}" {if $sectionEditor->getGender() == "M"}src="//assets.sangia.org/static/images/contactPersonM.png"{elseif $sectionEditor->getGender() == "F"}src="//assets.sangia.org/static/images/contactPersonF.png"{elseif $sectionEditor->getGender() == "O"}src="//scholar.google.co.id/citations/images/avatar_scholar_128.png"{elseif $sectionEditor->getGender() == ""}src="//loop.frontiersin.org/images/profile/479531/203"{/if} width="100" height="auto">
	    			</picture>	    			
			        {/if}
    	        </div>
			 </div>
			 <div class="sangia-editor--link">
			     <div class="section-editor-name u-font-sans">
			         <span class="sangia-editor--name">
			             <span class="fullname u-hide">{$sectionEditor->getFullName()|escape}</span>
			             {if $sectionEditor->getData('salutation') !== "Ph.D" && $sectionEditor->getData('salutation') !== "DEA"}
			             <span class="text degree">{$sectionEditor->getData('salutation')}</span>{/if}
			             {if $sectionEditor->getFirstName() !== $sectionEditor->getLastName()}<span class="text given-name">{$sectionEditor->getFirstName()}</span>{/if}
			             {if $sectionEditor->getMiddleName()|escape}
			             <span class="text middle-name">{$sectionEditor->getMiddleName()|escape}</span>{/if}
			             <span class="text surname">{$sectionEditor->getLastName()|escape}</span>
			             {if $sectionEditor->getData('salutation') == "Ph.D" && $sectionEditor->getData('salutation') == "DEA"}
			             <span class="text last-degree">{$sectionEditor->getData('salutation')}</span>{/if}
			         </span>
			     </div>

                {if $sectionEditorAffiliation}
                    {assign var="affiliations" value=$sectionEditorAffiliation|explode:"\n"}
                    {foreach from=$affiliations item=affiliation key=index name=affiliationLoop}
                    {if $affiliation|trim != ''}
                    <div class="sangia-editor--affiliation description">
                        <div class="sc-1mur6on-9 bLswwL">
                            <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj">
                                <svg class="sc-1mur6on-14 bPLcdE" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path>
                                </svg>
                            </div>
                            <span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="affiliation" itemtype="http://schema.org/Affiliation">{$affiliation|escape}{if $sectionEditorCountry and $smarty.foreach.affiliationLoop.last}, {$sectionEditorCountry}{/if}
                            </span>
                        </div>
                    </div>
                    {/if}
                    {/foreach}
                {/if}
			     {if $sectionEditor->getLocalizedGossip()}
			     <div class="sc-1mur6on-9 bLswwL description GooSSiP">
			         <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
			         </div>
			         <span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl">{$sectionEditor->getLocalizedGossip()}</span>
			     </div>
			     {/if}
			     {if $sectionEditor->getInterestString()}
			     <div class="sc-1mur6on-9 bLswwL description">
			         <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg width="1em" height="1em" viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg" class="sc-1mur6on-14 bPLcdE"><path id="Shape" fill="currentColor" fill-rule="nonzero" d="M4.98913043,4.69565217 L6.75,4.69565217 L6.75,8.09706522 L5.86956522,7.45728261 L4.98913043,8.09706522 L4.98913043,4.69565217 Z M9.39130435,4.10869565 L9.39130435,5.57608696 L12.0326087,5.57608696 L12.0326087,16.1413043 L2.70586957,16.1413043 C2.02206522,16.1413043 1.4673913,15.5866304 1.4673913,14.9028261 L1.4673913,5.2826087 C1.8225,5.4675 2.21869565,5.57608696 2.64130435,5.57608696 L3.52173913,5.57608696 L3.52173913,10.9790217 L5.86956522,9.27097826 L8.2173913,10.9790217 L8.2173913,3.22826087 L3.52173913,3.22826087 L3.52173913,4.10869565 L2.64130435,4.10869565 C1.99271739,4.10869565 1.4673913,3.51586957 1.4673913,2.78804348 C1.4673913,2.06021739 1.99271739,1.4673913 2.64130435,1.4673913 L12.6195652,1.4673913 L12.6195652,0 L2.64130435,0 C1.18565217,0 0,1.25021739 0,2.78804348 L0,14.9028261 C0,16.3936957 1.215,17.6086957 2.70586957,17.6086957 L13.5,17.6086957 L13.5,4.10869565 L9.39130435,4.10869565 Z"></path></svg>
			         </div>
			         <span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl">{$sectionEditor->getInterestString()|escape}</span>
			     </div>
			     {/if}
			 </div>
		</li>
		{/foreach}
	</ul>
	</div>
	{/if}
	{/foreach}
	
</section>
{/if}{/foreach}

<div class="separator">&nbsp;</div>
</div>

{if $currentJournal->getLocalizedSetting('reviewPolicy') != ''}<div id="peerReviewProcess" class="block">
    <header class="c-anchored-heading"><h2>{translate key="about.peerReviewProcess"}</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
<p>{$currentJournal->getLocalizedSetting('reviewPolicy')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}
<div id="publicationFrequency" class="block">
    <header class="c-anchored-heading"><h2>{translate key="about.publicationFrequency"}</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
<p>{$currentJournal->getLocalizedSetting('pubFreqPolicy')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''} 
<div id="openAccessPolicy" class="block">
    <header class="c-anchored-heading"><h2>{translate key="about.openAccessPolicy"}</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
<p>{$currentJournal->getLocalizedSetting('openAccessPolicy')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableAuthorSelfArchive')} 
<div id="authorSelfArchivePolicy" class="block">
    <header class="c-anchored-heading"><h2>{translate key="about.authorSelfArchive"}</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header> 
<p>{$currentJournal->getLocalizedSetting('authorSelfArchivePolicy')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableDelayedOpenAccess')}
<div id="delayedOpenAccessPolicy" class="block">
    <header class="c-anchored-heading"><h2>{translate key="about.delayedOpenAccess"}</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
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
    <header class="c-anchored-heading"><h2>{translate key="about.archiving"}</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
<p>{$currentJournal->getLocalizedSetting('lockssLicense')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

{foreach key=key from=$currentJournal->getLocalizedSetting('customAboutItems') item=customAboutItem name=customAboutItems}
	{if !empty($customAboutItem.title)}
		<div id="{$customAboutItem.title|replace:" ":""|escape}" class="block">
		    <header class="c-anchored-heading"><h2>{$customAboutItem.title|escape}</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
		    </header>
		<p>{$customAboutItem.content|nl2br}</p>
		{if !$smarty.foreach.customAboutItems.last}<div class="separator">&nbsp;</div>{/if}
		</div>
	{/if}
{/foreach}

</section>

{include file="common/footer.tpl"}

