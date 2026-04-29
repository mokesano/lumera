{**
 * templates/about/editorialTeamBio.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * View the biography of an editorial team member.
 *
 *}
{strip}
{assign var="pageTitle" value="about.editorialTeamBio"}
{include file="common/header-profile.tpl"}
{/strip}

{assign var=pageTitleTranslated value=$user->getFullName()|escape}
{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}

<h1 id="USER-ID{$editors}">{$pageTitleTranslated}</h1>

	<div class="overview">
		{assign var="profileImage" value=$user->getSetting('profileImage')}
		{if $profileImage}
		<img id="ID-{$editors}" height="auto" width="150" title="{$user->getFullName()|escape}" class="avatar editor" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" />
			{else}
			<img id="ID-{$editors}" class="avatar editor" title="{$user->getFullName()|escape}" src="//media.stipwunaraha.ac.id/static/contactPersonM.png" width="150" height="auto" />
		{/if}

		<div class="description">
			<p class="description--paragraph">
				<span>
					{if count($editors) == 1}
					{translate key="user.role.editor"}, {$currentJournal->getLocalizedTitle()|strip_tags|escape}
						{else}
					{translate key="user.role.editors"}, {$currentJournal->getLocalizedTitle()|strip_tags|escape}
					{/if}
				</span>
				<br />
				<span>
					{if count($editors) == 1}
					{translate key="user.role.editor"}
						{else}
					{translate key="user.role.editors"} Bidang keahlian
					{/if}					
				</span>
				<br />
			</p>
			<br />

			<span>Stipwunaraha Research Media</span>
			<br />
			{if $user->getLocalizedAffiliation()}
			<span>{$user->getLocalizedAffiliation()|escape}{assign var=needsComma value=1}{/if}{if $country}{if $needsComma}, {/if}{$country|escape}</span>
			{/if}
			<p class="description--paragraph">
				Phone: <span>{$user->getData('phone')|escape}</span><br />
				email: {$user->getEmail()|escape}
			</p>

			<div class="editor-person">
				<div class="editor-person--value">
					{if $user->getData('orcid')}
					<span>ORCID: <a title="Go to Orcid profile of {$user->getFullName()|escape}" href="{$user->getData('orcid')|escape}" target="_blank" class="fax">{$user->getData('orcid')|escape} <img src="{$baseUrl}/public/site/images/orcid_16x16.svg" style="height:16px" alt="orcid" /></a></span>{/if}

					{if $user->getUrl()}, <span><a title="Go to Google Scholar profile of {$user->getFullName()|escape}" href="{$user->getUrl()|escape}" target="_blank">Google Scholar <img src="{$baseUrl}/public/site/images/scholar.svg" style="height:17px" /></a></span>{/if}

					{if $user->getData('scopus')}
					<span>{translate key="user.scopus"}: <a title="Go to Scopus profile of {$user->getFullName()|escape}" href="https://www.scopus.com/authid/detail.uri?authorId={$user->getData('scopus')|escape}" target="_blank" class="scopus u-font-serif">{$user->getData('scopus')|escape}</a></span>{/if}

					{if $user->getData('sinta')}
					<span>{translate key="user.sinta"}: <a title="Go to Sinta ID profile of {$user->getFullName()|escape}" href="http://sinta2.ristekdikti.go.id/authors/detail?id={$user->getData('sinta')|escape}&view=overview" target="_blank" class="sinta u-font-serif">{$user->getData('sinta')|escape}</a></span>{/if}
				</div>
			</div>
		</div>
	</div>

{if $user->getLocalizedBiography()}	
<div class="editor-bio person-detail">
{$user->getLocalizedBiography()|nl2br|strip_unsafe_html}
</div>
{/if}


{include file="common/footer.tpl"}