{**
 * templates/about/editorialTeamBoard.tpl
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

{call_hook name="Templates::About::EditorialTeam::Information"}

<div class="publication-editors">
{foreach from=$groups item=group}
	<div class="publication-editor-type">{$group->getLocalizedTitle()}</div>
	{assign var=groupId value=$group->getId()}
	{assign var=members value=$teamInfo[$groupId]}
		{foreach from=$members item=member}
		{assign var=user value=$member->getUser()}
		<div class="publication-editor" itemscope="" itemtype="https://schema.org/Person">
			{assign var="profileImage" value=$user->getSetting('profileImage')}
			{if $profileImage}<img title="{$user->getFullName()|escape}" alt="{$user->getFullName()|escape}" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" loading="lazy" />{/if}	
			
			{assign var=user value=$member->getUser()}
			<div class="publication-editor-name cms-person">{if $user->getLocalizedBiography() || $user->getUrl() || $user->getData('fax')}<h3><a title="View {$user->getFullName()|escape} profile" href="{url op="editorialTeamBio" path=$user->getId() anchor=$user->getFullName()}">{$user->getFullName()|escape}</h3></a>{else}<h3>{$user->getFullName()|escape}</h3>{/if}
			</div>
			
    		<input id="{$user->getId()|string_format:"%09d"}" type="hidden" value="{$user->getGender()}" name="gender">
    			
            {if $user->getLocalizedAffiliation()}
                {assign var="affiliations" value=$user->getLocalizedAffiliation()|explode:"\n"}
                {assign var="affiliationCount" value=$affiliations|@count}
                {foreach from=$affiliations item=affiliation key=index}
                    {if $affiliation|trim != ''}
        			<div class="sc-1mur6on-9 bLswwL publication-editor-affiliation">
            			<div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg class="sc-1mur6on-14 bPLcdE" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path></svg>
            			</div>
            			<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="affiliation" itemtype="http://schema.org/Affiliation">{$affiliation|escape}{if $index == $affiliationCount - 1 && $user->getCountry()}{assign var=countryCode value=$user->getCountry()}{assign var=country value=$countries[$countryCode]}, {$country|escape}{/if}
            			</span>
            		</div>
                    {/if}
                {/foreach}
            {/if}
    			
			{if $user->getLocalizedGossip()}
            <div class="sc-1mur6on-9 bLswwL GooSSiP">
                <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj">
                    <svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
                </div>
        		<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="" itemtype="http://schema.org/Affiliation">{$user->getLocalizedGossip()}</span>
            </div>
            {/if}
            
			{if $user->getInterestString()}
            <div class="sc-1mur6on-9 bLswwL publication-editor-interest">
			    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg width="1em" height="1em" viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg" class="sc-1mur6on-14 bPLcdE"><path id="Shape" fill="currentColor" fill-rule="nonzero" d="M4.98913043,4.69565217 L6.75,4.69565217 L6.75,8.09706522 L5.86956522,7.45728261 L4.98913043,8.09706522 L4.98913043,4.69565217 Z M9.39130435,4.10869565 L9.39130435,5.57608696 L12.0326087,5.57608696 L12.0326087,16.1413043 L2.70586957,16.1413043 C2.02206522,16.1413043 1.4673913,15.5866304 1.4673913,14.9028261 L1.4673913,5.2826087 C1.8225,5.4675 2.21869565,5.57608696 2.64130435,5.57608696 L3.52173913,5.57608696 L3.52173913,10.9790217 L5.86956522,9.27097826 L8.2173913,10.9790217 L8.2173913,3.22826087 L3.52173913,3.22826087 L3.52173913,4.10869565 L2.64130435,4.10869565 C1.99271739,4.10869565 1.4673913,3.51586957 1.4673913,2.78804348 C1.4673913,2.06021739 1.99271739,1.4673913 2.64130435,1.4673913 L12.6195652,1.4673913 L12.6195652,0 L2.64130435,0 C1.18565217,0 0,1.25021739 0,2.78804348 L0,14.9028261 C0,16.3936957 1.215,17.6086957 2.70586957,17.6086957 L13.5,17.6086957 L13.5,4.10869565 L9.39130435,4.10869565 Z"></path></svg>
				</div>
				<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl">{$user->getInterestString()|escape}</span>
			</div>
			{/if}
			<div class="clearfix"></div>
		</div>
		{/foreach}{* $members *}
    <div class="clearfix"></div>
{/foreach}{* $groups *}
</div>

<div class="statement u-font-serif">All members of the Editorial Board have identified their affiliated institutions or organizations, along with the corresponding country or geographic region. {if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Production & hosted of Sangia Publishing{else}{$currentJournal->getSetting('publisherInstitution')}{/if} remains neutral with regard to any jurisdictional claims.
</div>

{include file="common/footer.tpl"}
