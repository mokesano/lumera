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
    <div class="publication-editor-type">{$group->getLocalizedTitle()|escape}</div>
        {assign var=groupId value=$group->getId()}
        
        {foreach from=$memberships item=member}
        {assign var=user value=$member->getUser()}
        <div class="publication-editor" itemscope="reviewer" itemtype="https://schema.org/Person">
            {assign var="profileImage" value=$user->getSetting('profileImage')}
            {if $profileImage}
            <div sangiaID="{$user->getId()|string_format:"%07d"}" title="{$user->getFullName()|escape}" alt="{$user->getFullName()|escape}" itemprop="reviewer" itemscope="reviewer">
                <picture loading="lazy">
                    <source srcset="{$sitePublicFilesDir}/{$profileImage.uploadName}?as=webp" type="image/webp" loading="lazy" />
                    <img src="{$sitePublicFilesDir}/{$profileImage.uploadName}?as=webp" loading="lazy" type="image/webp" />
                </picture>
            </div>
            {/if}
            
            <div class="publication-editor-name" itemprop="reviewer" itemscope="reviewerName">
                {if $user->getLocalizedBiography() || $user->getUrl() || $user->getGoogleScholar() || $user->getSintaId() || $user->getScopusId()}
                <h3 sangiaID="{$user->getId()|string_format:"%011d"|escape}" title="{$user->getFullName()|escape}" itemprop="name" itemtype="http://schema.org/Person"><a href="{url op="editorialTeamBio" path=$user->getId()|string_format:"%011d" from="membership" anchor=$user->getFullName()|escape}" target="_blank"><span class="fullname u-hide">{$user->getFullName()|escape}</span>{if $user->getSalutation()}<span class="text degree">{$user->getSalutation()|escape}</span>{/if}{if $user->getFirstName() !== $user->getLastName()}<span class="text given-name">{$user->getFirstName()|escape}</span>{/if}{if $user->getMiddleName()|escape}<span class="text middle-name">{$user->getMiddleName()|escape}</span>{/if}<span class="text surname">{$user->getLastName()|escape}</span>{if $user->getData('suffix')}<span class="text last-degree">{$user->getData('suffix')|escape}</span>{/if}</a>{if $user->getData('orcid')}<span class="orcid"><a title="Go to Orcid profile of {$user->getFullName()|escape}" href="{$user->getData('orcid')|escape}" target="_blank"><img src="//assets.sangia.org/img/orcid_16x16.svg" style="height:16px" alt="orcid" /></a></span>{/if}{if $user->getSintaId()}<span class="sintaid"><a title="Go to Sinta profile of {$user->getFullName()|escape}" href="//sinta.kemdiktisaintek.go.id/authors/profile/{$user->getSintaId()|escape}" target="_blank" class="sintaid"><img src="//sinta.kemdiktisaintek.go.id/public/assets/img/brand_sinta.png" style="height:18px" alt="sinta" referrerpolicy="strict-origin-when-cross-origin"/></a></span>{/if}{if $user->getScopusId()}<span class="scopus"><a title="Go to Scopus profile of {$user->getFullName()|escape}" href="https://www.scopus.com/authid/detail.uri?authorId={$user->getScopusId()|escape}" target="_blank" class="scopusid"><svg xmlns="http://www.w3.org/2000/svg" role="img" version="1.1" class="gh-wordmark" focusable="false" aria-hidden="true" height="20" viewBox="0 0 70 27" width="55"><g fill="#f36d21"><path class="a" d="M4.23,21a9.79,9.79,0,0,1-4.06-.83l.29-2.08a7.17,7.17,0,0,0,3.72,1.09c2.13,0,3-1.22,3-2.39C7.22,13.85.3,13.43.3,9c0-2.37,1.56-4.29,5.2-4.29a9.12,9.12,0,0,1,3.77.75l-.1,2.08a7.58,7.58,0,0,0-3.67-1c-2.24,0-2.91,1.22-2.91,2.39,0,3,6.92,3.61,6.92,7.8C9.5,19.1,7.58,21,4.23,21Z"></path><path class="a" d="M20.66,20A6.83,6.83,0,0,1,16.76,21c-3,0-5.23-2.18-5.23-6.29,0-4.29,2.91-6.11,5.28-6.11,2.16,0,3.67.54,3.85,2.11,0,.23,0,.57,0,.86H18.81c0-1-.55-1.25-1.9-1.25a2.85,2.85,0,0,0-1.35.21c-.21.13-1.85.94-1.85,4.11s1.9,4.65,3.59,4.65a5.91,5.91,0,0,0,3.2-1.2Z"></path><path class="a" d="M27.29,21c-3.28,0-5.46-2.44-5.46-6.19,0-3.46,2-6.21,5.75-6.21,3.3,0,5.49,2.37,5.49,6.21C33.06,18.5,30.85,21,27.29,21Zm0-10.69a3.3,3.3,0,0,0-2,.65A5.83,5.83,0,0,0,24,14.73c0,3.74,2,4.6,3.56,4.6a3.45,3.45,0,0,0,2-.65A5.53,5.53,0,0,0,30.9,15C30.9,12.86,30.2,10.36,27.31,10.36Z"></path><path class="a" d="M40.37,21a5.63,5.63,0,0,1-2.6-.57v5.46h-2V12.23c0-.91-.05-1.72-.1-2.31l-.1-1H37.4l.31,1.74a4.86,4.86,0,0,1,4-2.05c2.39,0,4.26,1.56,4.26,5.72S43.69,21,40.37,21Zm.91-10.61a4.49,4.49,0,0,0-1.56.31,11.57,11.57,0,0,0-2,2.11v5.8a4.35,4.35,0,0,0,.7.34,4.12,4.12,0,0,0,1.61.34c2.57,0,3.74-1.9,3.74-4.73C43.82,12.94,43.51,10.44,41.27,10.44Z"></path><path class="a" d="M58.36,20.74H56.54L56.22,19a4.06,4.06,0,0,1-3.85,2.05c-2.08,0-3.77-.86-3.77-3.87V9h2V15.8c0,1.92.16,3.54,2,3.54a4.47,4.47,0,0,0,2-.47,6.77,6.77,0,0,0,1.64-2.08V9h2v8.53a19,19,0,0,0,.1,2.31Z"></path><path class="a" d="M64.86,21.07a6.87,6.87,0,0,1-3.67-1l.23-1.87a5.54,5.54,0,0,0,3.28,1.2c1.66,0,2.44-.75,2.44-1.66,0-2.39-5.88-2.26-5.88-5.9,0-1.77,1.38-3.22,4.21-3.22a6.59,6.59,0,0,1,3.38.88l-.21,1.87a4.67,4.67,0,0,0-3.15-1.14c-1.33,0-2.24.52-2.24,1.46,0,2.37,5.88,2.16,5.88,5.9C69.15,19.36,67.85,21.07,64.86,21.07Z"></path></g></svg></a></span>{/if}</h3>
                {else}
                <h3 sangiaID="{$user->getId()|string_format:"%09d"|escape}"><span class="fullname u-hide">{$user->getFullName()|escape}</span>{if $user->getSalutation()}<span class="text degree">{$user->getSalutation()|escape}</span>{/if}{if $user->getFirstName() !== $user->getLastName()}<span class="text given-name">{$user->getFirstName()|escape}</span>{/if}{if $user->getMiddleName()}<span class="text middle-name">{$user->getMiddleName()|escape}</span>{/if}<span class="text surname">{$user->getLastName()|escape}</span>{if $user->getSuffix()}<span class="text last-degree">{$user->getSuffix()|escape}</span>{/if}</h3>
                {/if}
            </div>
            <input id="{$user->getId()|string_format:"%09d"|escape}" type="hidden" value="{$user->getGender()|escape}" name="gender">
			{if $user->getLocalizedAffiliation()}
                {assign var="affiliations" value=$user->getLocalizedAffiliation()|explode:"\n"}
                {assign var="affiliationCount" value=$affiliations|@count}
                {foreach from=$affiliations item=affiliation key=index}
                    {if $affiliation|trim != ''}
			<div class="sc-1mur6on-9 bLswwL publication-editor-affiliation">
    			<div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg class="sc-1mur6on-14 bPLcdE" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path></svg>
    			</div>
    			<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="affiliation" itemtype="http://schema.org/Affiliation">{$affiliation|escape}{if $index == $affiliationCount - 1 && $user->getCountry()}{assign var=countryCode value=$user->getCountry()}{assign var=country value=$countries[$countryCode]}, {$country|escape}{/if}</span>
    		</div>
			    {/if}
                {/foreach}
            {/if}
			
			{if $user->getLocalizedGossip()}
            <div class="sc-1mur6on-9 GooSSiP bLswwL">
                <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj">
                    <svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
                </div>
        		<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="" itemtype="http://schema.org/Affiliation">{$user->getLocalizedGossip()|escape}</span>
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
		{/foreach}
	<div class="clearfix"></div>
</div>

</section>
</section>

<div class="statement u-font-serif">All members of the {$group->getLocalizedTitle()|escape} have identified their affiliated institutions or organizations, along with the corresponding country or geographic region. {if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Production & hosted of Sangia Publishing{else}{$currentJournal->getSetting('publisherInstitution')|escape}{/if} remains neutral with regard to any jurisdictional claims.
</div>

{include file="common/footer.tpl"}
