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
{assign var="pageTitle" value="about.sectionPolicies"}
{include file="common/header-gfa.tpl"}
{/strip}

<section class="content u-mt-48">
	
<div id="sectionPolicies" class="block">
    
{foreach from=$sections item=section}{if !$section->getHideAbout()}
<section id="{$section->getLocalizedTitle()|replace:" ":"-"}" class="section-area clearfix" aria-labelledby="{$section->getLocalizedTitle()|replace:" ":""}">
    <div class="section-describe">
    	<h4 class="section-title">{$section->getLocalizedTitle()}</h4>
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

</section>

{include file="common/footer.tpl"}
