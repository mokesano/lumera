{**
 * templates/about/reviewer.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display group membership information.
 *
 *}
{strip}
{assign var="pageTitle" value="about.editorialTeam"}
{include file="common/header-ABOUT.tpl"}
{/strip}

<div class="publication-editors">
    <div class="publication-editor-type">{$group->getLocalizedTitle()}</div>
        {assign var=groupId value=$group->getId()}
        
        {foreach from=$memberships item=member}
        {assign var=user value=$member->getUser()}
        <div class="publication-editor" itemscope="editor" itemtype="https://schema.org/Person">
            {assign var="profileImage" value=$user->getSetting('profileImage')}
            {if $profileImage}<img id="editor-{$user->getId()}" title="{$user->getFullName()|escape}" alt="{$user->getFullName()|escape}" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" loading="lazy" />{/if}
            
            <div id="editorID{$user->getId()}" class="publication-editor-name">
                {if $user->getLocalizedBiography() || $user->getUrl()}
                <h3><a href="{url op="editorialTeamBio" path=$user->getId()}" target="_blank">{$user->getFullName()|escape}</a></h3>
                {else}
                <h3>{$user->getFullName()|escape}</h3>
                {/if}
            </div>    
            
            {if $user->getLocalizedAffiliation()}
            <span class="publication-editor-affiliation" itemprop="affiliation">{$user->getLocalizedAffiliation()|escape}{/if}{if $user->getCountry()}{assign var=countryCode value=$user->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}
            </span>
            {if $user->getLocalizedGossip() || $user->getInterestString()}
            <div class="sc-1mur6on-9 bLswwL publication-editor-interest">
				<div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg width="1em" height="1em" viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg" class="sc-1mur6on-14 bPLcdE"><path id="Shape" fill="currentColor" fill-rule="nonzero" d="M4.98913043,4.69565217 L6.75,4.69565217 L6.75,8.09706522 L5.86956522,7.45728261 L4.98913043,8.09706522 L4.98913043,4.69565217 Z M9.39130435,4.10869565 L9.39130435,5.57608696 L12.0326087,5.57608696 L12.0326087,16.1413043 L2.70586957,16.1413043 C2.02206522,16.1413043 1.4673913,15.5866304 1.4673913,14.9028261 L1.4673913,5.2826087 C1.8225,5.4675 2.21869565,5.57608696 2.64130435,5.57608696 L3.52173913,5.57608696 L3.52173913,10.9790217 L5.86956522,9.27097826 L8.2173913,10.9790217 L8.2173913,3.22826087 L3.52173913,3.22826087 L3.52173913,4.10869565 L2.64130435,4.10869565 C1.99271739,4.10869565 1.4673913,3.51586957 1.4673913,2.78804348 C1.4673913,2.06021739 1.99271739,1.4673913 2.64130435,1.4673913 L12.6195652,1.4673913 L12.6195652,0 L2.64130435,0 C1.18565217,0 0,1.25021739 0,2.78804348 L0,14.9028261 C0,16.3936957 1.215,17.6086957 2.70586957,17.6086957 L13.5,17.6086957 L13.5,4.10869565 L9.39130435,4.10869565 Z"></path></svg>
				</div>
				<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl">{$user->getInterestString()} {$user->getLocalizedGossip()}</span>
			</div>
			{/if}
            <div class="clearfix"></div>
        </div>
		{/foreach}
	<div class="clearfix"></div>
</div>

<div class="statement u-font-serif">All members of the {$group->getLocalizedTitle()} have identified their affiliated institutions or organizations, along with the corresponding country or geographic region. {if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Production & hosted of Sangia Publishing{else}{$currentJournal->getSetting('publisherInstitution')}{/if} remains neutral with regard to any jurisdictional claims.
</div>

{include file="common/footer.tpl"}

