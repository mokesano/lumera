{**
 * templates/about/editorialTeam.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Journal index.
 *
 *}
{strip}
{assign var="pageTitle" value="about.editorialTeam"}
{include file="common/header-ABOUT.tpl"}
{/strip}

<div class="publication-editors">
{if count($editors) > 0}

	{if count($editors) == 1}
	<div class="publication-editor-type">{translate key="user.role.editor"}-in-chief</div>
	{else}
	<div class="publication-editor-type">{translate key="user.role.editors"}-in-chief</div>
	{/if}
	
	{foreach from=$editors item=editor}
	<div id="editor-{$editor->getId()}" class="publication-editor" itemscope="" itemtype="https://schema.org/Person">
		{assign var="profileImage" value=$editor->getSetting('profileImage')}
		{if $profileImage}<img title="{$editor->getFullName()|escape}" alt="{$editor->getFullName()|escape}" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" />{else}<img id="ID-{$editors}" class="avatar editor" title="{$user->getFullName()|escape}" src="//media.stipwunaraha.ac.id/static/contactPersonM.png" width="150" height="auto" />
		{/if}
		<div class="publication-editor-name">
    		{if $editor->getLocalizedBiography() || $editor->getUrl() || $editor->getData('fax') || $editor->getData('mailingAddress')}
    			<h3 class="editor-name">
    			    <a href="{url op="editorialTeamBio" path=$editor->getId()|string_format:"%012d" anchor=$editor->getFullName()}"><span class="text u-hide" name="fullname">{$editor->getFullName()|escape}</span>{if $editor->getData('salutation') !== "Ph.D"}<span class="text" name="degree">{$editor->getData('salutation')}</span>{/if}{if $editor->getFirstName() !== $editor->getLastName()}<span class="text" name="first-name">{$editor->getFirstName()|escape}</span>{/if}{if $editor->getMiddleName()|escape}<span class="text" name="midle-name">{$editor->getMiddleName()|escape}</span>{/if}<span class="text" name="given-name">{$editor->getLastName()|escape}</span>{if $editor->getData('salutation') == "Ph.D"}<span class="text last-degree" name="degree">{$editor->getData('salutation')}</span>{/if}
    			    </a>
    			</h3>
    		{else}
    			<h3 class="editor-name"><span class="text u-hide" name="fullname">{$editor->getFullName()|escape}</span>{if $editor->getData('salutation') !== "Ph.D"}<span class="text" name="degree">{$editor->getData('salutation')}</span>{/if}{if $editor->getFirstName() !== $editor->getLastName()}<span class="text" name="first-name">{$editor->getFirstName()|escape}</span>{/if}{if $editor->getMiddleName()|escape}<span class="text" name="midle-name">{$editor->getMiddleName()|escape}</span>{/if}<span class="text" name="given-name">{$editor->getLastName()|escape}</span>{if $editor->getData('salutation') == "Ph.D"}<span class="text last-degree" name="degree">{$editor->getData('salutation')}</span>{/if}
    			</h3>
    		{/if}
		</div>
		
    	<input id="{$editor->getId()|string_format:"%012d"}" type="hidden" value="{$editor->getGender()}" name="gender">
    		
        {if $editor->getLocalizedAffiliation()}
            {assign var="affiliations" value=$editor->getLocalizedAffiliation()|explode:"\n"}
            {assign var="affiliationCount" value=$affiliations|@count}
            {foreach from=$affiliations item=affiliation key=index}
                {if $affiliation|trim != ''}
            		<div class="sc-1mur6on-9 bLswwL publication-editor-affiliation">
                		<div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg class="sc-1mur6on-14 bPLcdE" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path></svg>
                		</div>
                		<span class="publication-editor-affiliation" itemprop="affiliation">{$affiliation|escape}{if $index == $affiliationCount - 1 && $editor->getCountry()}{assign var=countryCode value=$editor->getCountry()}{assign var=country value=$countries[$countryCode]}, {$country|escape}{/if}
                		</span>
                	</div>
                {/if}
            {/foreach}
        {/if}
		
		{if $editor->getLocalizedGossip()}
        <div class="sc-1mur6on-9 bLswwL GooSSiP">
            <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
            </div>
        	<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="" itemtype="http://schema.org/Affiliation">{$editor->getLocalizedGossip()}</span>
        </div>
        {/if}
            
		{if $editor->getInterestString()}
        <div class="sc-1mur6on-9 bLswwL publication-editor-affiliation">
			<div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg width="1em" height="1em" viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg" class="sc-1mur6on-14 bPLcdE"><path id="Shape" fill="currentColor" fill-rule="nonzero" d="M4.98913043,4.69565217 L6.75,4.69565217 L6.75,8.09706522 L5.86956522,7.45728261 L4.98913043,8.09706522 L4.98913043,4.69565217 Z M9.39130435,4.10869565 L9.39130435,5.57608696 L12.0326087,5.57608696 L12.0326087,16.1413043 L2.70586957,16.1413043 C2.02206522,16.1413043 1.4673913,15.5866304 1.4673913,14.9028261 L1.4673913,5.2826087 C1.8225,5.4675 2.21869565,5.57608696 2.64130435,5.57608696 L3.52173913,5.57608696 L3.52173913,10.9790217 L5.86956522,9.27097826 L8.2173913,10.9790217 L8.2173913,3.22826087 L3.52173913,3.22826087 L3.52173913,4.10869565 L2.64130435,4.10869565 C1.99271739,4.10869565 1.4673913,3.51586957 1.4673913,2.78804348 C1.4673913,2.06021739 1.99271739,1.4673913 2.64130435,1.4673913 L12.6195652,1.4673913 L12.6195652,0 L2.64130435,0 C1.18565217,0 0,1.25021739 0,2.78804348 L0,14.9028261 C0,16.3936957 1.215,17.6086957 2.70586957,17.6086957 L13.5,17.6086957 L13.5,4.10869565 L9.39130435,4.10869565 Z"></path></svg>
			</div>
			<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl">{$editor->getInterestString()|escape}</span>
		</div>
		{/if}
		
		{if $editor->getData('mailingAddress')}
		<a class="u-hide editorMail sc-1mur6on-15 fTcNcY u-font-sans u-mt-16" href="mailto:{$editor->getEmail()|escape}" target="_self" itemprop="url" title="Use contact to email {$editor->getFullName()|escape}">Email {$editor->getFullName()|escape}</a>
		{/if}
		
		<div class="clearfix"></div>
	</div>
	{/foreach}
{/if}
    
<!--h3 class="twoColumnSeparatorLeft--paragraph headline-1159404042"><br></h3>
    <div class="publication-editor-type">Area Editors</div-->
    
    {if count($sectionEditors) > 0}
    	{if count($sectionEditors) == 1}
    	<div class="publication-editor-type">{translate key="user.role.sectionEditor"}</div>
    	{else}
    	<div class="publication-editor-type">{translate key="user.role.sectionEditors"}</div>
    	{/if}
    	{foreach from=$sectionEditors item=sectionEditor}
    	<div id="editor-{$sectionEditor->getId()}" class="publication-editor" itemscope="" itemtype="https://schema.org/Person">
    		{assign var="profileImage" value=$sectionEditor->getSetting('profileImage')}
    		{if $profileImage}<img title="{$sectionEditor->getFullName()|escape}" alt="{$sectionEditor->getFullName()|escape}" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" />{/if}		
    		<div class="publication-editor-name">
    			{if $sectionEditor->getLocalizedBiography() || $sectionEditor->getUrl() || $sectionEditor->getData('fax') || $sectionEditor->getData('mailingAddress')}
    			<h3 class="editor-name"><a href="{url op="editorialTeamBio" path=$sectionEditor->getId()|string_format:"%012d"}"><span class="text u-hide" name="fullname">{$sectionEditor->getFullName()|escape}</span>{if $sectionEditor->getData('salutation') !== "Ph.D"}<span class="text" name="degree">{$sectionEditor->getData('salutation')}</span>{/if}{if $sectionEditor->getFirstName() !== $sectionEditor->getLastName()}<span class="text" name="first-name">{$sectionEditor->getFirstName()|escape}</span>{/if}{if $sectionEditor->getMiddleName()|escape}<span class="text" name="midle-name">{$sectionEditor->getMiddleName()|escape}</span>{/if}<span class="text" name="given-name">{$sectionEditor->getLastName()|escape}</span>{if $sectionEditor->getData('salutation') == "Ph.D"}<span class="text last-degree" name="degree">{$sectionEditor->getData('salutation')}</span>{/if}</a>
    			</h3>
    			{else}
    			<h3 class="editor-name"><span class="text u-hide" name="fullname">{$sectionEditor->getFullName()|escape}</span>{if $sectionEditor->getData('salutation') !== "Ph.D"}<span class="text" name="degree">{$sectionEditor->getData('salutation')}</span>{/if}{if $sectionEditor->getFirstName() !== $sectionEditor->getLastName()}<span class="text" name="first-name">{$sectionEditor->getFirstName()|escape}</span>{/if}{if $sectionEditor->getMiddleName()|escape}<span class="text" name="midle-name">{$sectionEditor->getMiddleName()|escape}</span>{/if}<span class="text" name="given-name">{$sectionEditor->getLastName()|escape}</span>{if $sectionEditor->getData('salutation') == "Ph.D"}<span class="text last-degree" name="degree">{$sectionEditor->getData('salutation')}</span>{/if}
    			</h3>
    			{/if}
    		</div>
    		
    		<input id="{$sectionEditor->getId()|string_format:"%012d"}" type="hidden" value="{$sectionEditor->getGender()}" name="gender">
        		
    		{if $sectionEditor->getLocalizedAffiliation()}
                {assign var="affiliations" value=$sectionEditor->getLocalizedAffiliation()|explode:"\n"}
                {assign var="affiliationCount" value=$affiliations|@count}
                {foreach from=$affiliations item=affiliation key=index}
                    {if $affiliation|trim != ''}
            		<div class="sc-1mur6on-9 bLswwL publication-editor-affiliation">
                		<div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg class="sc-1mur6on-14 bPLcdE" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path></svg>
                		</div>    		
                		<span class="publication-editor-affiliation" itemprop="affiliation">{$affiliation|escape}{if $index == $affiliationCount - 1 && $sectionEditor->getCountry()}{assign var=countryCode value=$sectionEditor->getCountry()}{assign var=country value=$countries[$countryCode]}, {$country|escape}{/if}
                		</span>
                	</div>
                    {/if}
                {/foreach}
            {/if}
        		
    		{if $sectionEditor->getLocalizedGossip()}
            <div class="sc-1mur6on-9 bLswwL GooSSiP">
                <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
                </div>
            	<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="" itemtype="http://schema.org/Affiliation">{$sectionEditor->getLocalizedGossip()}</span>
            </div>
            {/if}
            
    		{if $sectionEditor->getInterestString()}
            <div class="sc-1mur6on-9 bLswwL publication-editor-affiliation">
    		    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg width="1em" height="1em" viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg" class="sc-1mur6on-14 bPLcdE"><path id="Shape" fill="currentColor" fill-rule="nonzero" d="M4.98913043,4.69565217 L6.75,4.69565217 L6.75,8.09706522 L5.86956522,7.45728261 L4.98913043,8.09706522 L4.98913043,4.69565217 Z M9.39130435,4.10869565 L9.39130435,5.57608696 L12.0326087,5.57608696 L12.0326087,16.1413043 L2.70586957,16.1413043 C2.02206522,16.1413043 1.4673913,15.5866304 1.4673913,14.9028261 L1.4673913,5.2826087 C1.8225,5.4675 2.21869565,5.57608696 2.64130435,5.57608696 L3.52173913,5.57608696 L3.52173913,10.9790217 L5.86956522,9.27097826 L8.2173913,10.9790217 L8.2173913,3.22826087 L3.52173913,3.22826087 L3.52173913,4.10869565 L2.64130435,4.10869565 C1.99271739,4.10869565 1.4673913,3.51586957 1.4673913,2.78804348 C1.4673913,2.06021739 1.99271739,1.4673913 2.64130435,1.4673913 L12.6195652,1.4673913 L12.6195652,0 L2.64130435,0 C1.18565217,0 0,1.25021739 0,2.78804348 L0,14.9028261 C0,16.3936957 1.215,17.6086957 2.70586957,17.6086957 L13.5,17.6086957 L13.5,4.10869565 L9.39130435,4.10869565 Z"></path></svg>
    			</div>
    			<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl">{$sectionEditor->getInterestString()|escape}</span>
    		</div>
    		{/if}
    		
        	<div class="clearfix"></div>
    	</div>
    	{/foreach}
    {/if}
    
    {if count($copyEditors) > 0}
    <div class="editors-area">
    	{if count($copyEditors) == 1}
    		<h3>{translate key="user.role.copyeditor"}<br></h3>
    	{else}
    		<h3>{translate key="user.role.copyeditors"}<br></h3>
    	{/if}
    </div>
    	{foreach from=$copyEditors item=copyEditor}
    	<div id="{$copyEditor->getId()|string_format:"%012d"}" class="publication-editor" itemscope="copyEditor" itemtype="https://schema.org/Person">
        	<input id="{$copyEditor->getId()|string_format:"%012d"}" type="hidden" value="{$copyEditor->getGender()}" name="gender">
        	<section class="article-body">{if $copyEditor->getLocalizedBiography()}<a href="{url op="editorialTeamBio" path=$copyEditor->getId()}">{$copyEditor->getFullName()|escape}</a>{else}{$copyEditor->getFullName()|escape}{/if}
        		{if $copyEditor->getLocalizedAffiliation()}
        		<div class="publication-editor-affiliation">{$copyEditor->getLocalizedAffiliation()|escape}{/if}{if $copyEditor->getCountry()}{assign var=countryCode value=$copyEditor->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}
        	</section>
        </div>	
    	{/foreach}
    {/if}

    {if count($proofreaders) > 0}
    <div class="editors-area">
    	{if count($copyEditors) == 1}
    		<h3>{translate key="user.role.proofreader"}<br></h3>
    	{else}
    		<h3>{translate key="user.role.proofreaders"}<br></h3>
    	{/if}
    </div>
    	{foreach from=$proofreaders item=proofreader}
    	<input id="{$proofreader->getId()|string_format:"%012d"}" type="hidden" value="{$proofreader->getGender()}" name="gender">
    	<section class="article-body">{if $proofreader->getLocalizedBiography()}<a href="{url op="editorialTeamBio" path=$proofreader->getId()|string_format:"%012d"}">{$proofreader->getFullName()|escape}</a>{else}{$proofreader->getFullName()|escape}{/if},
    		{if $proofreader->getLocalizedAffiliation()}{$proofreader->getLocalizedAffiliation()|escape}{/if}{if $proofreader->getCountry()}{assign var=countryCode value=$proofreader->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}
    	</section>
    	{/foreach}
    	<br>
    {/if}

    {if count($layoutEditors) > 0}
    <div class="editors-area">
    	{if count($copyEditors) == 1}
    		<h3>{translate key="user.role.layoutEditor"}<br></h3>
    	{else}
    		<h3>{translate key="user.role.layoutEditors"}<br></h3>
    	{/if}
    </div>
    	{foreach from=$layoutEditors item=layoutEditor}
    	<input id="{$layoutEditor->getId()|string_format:"%09d"}" type="hidden" value="{$layoutEditor->getGender()}" name="gender">
    	<section class="article-body">{if $layoutEditor->getLocalizedBiography()}<a href="{url op="editorialTeamBio" path=$layoutEditor->getId()}">{$layoutEditor->getFullName()|escape}</a>{else}{$layoutEditor->getFullName()|escape}{/if},
    		{if $layoutEditor->getLocalizedAffiliation()}{$layoutEditor->getLocalizedAffiliation()|escape}{/if}{if $layoutEditor->getCountry()}{assign var=countryCode value=$layoutEditor->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}
    	</section>
    	{/foreach}
    {/if}
</div>

<div class="statement u-font-serif">All members of the Editorial Board have identified their affiliated institutions or organizations, along with the corresponding country or geographic region. {if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Production & hosted of Sangia Publishing{else}{$currentJournal->getSetting('publisherInstitution')}{/if} remains neutral with regard to any jurisdictional claims.
</div>

{include file="common/footer.tpl"}

