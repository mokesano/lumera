{**
 * templates/manager/people/userProfile.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display user profile.
 *
 *}
{strip}
{assign var="pageTitle" value="manager.people"}
{include file="common/header-USER027.tpl"}
{/strip}

<h3 id="userFullName">{$user->getFullName()|escape}</h3>

<h4>{translate key="user.profile"}</h4>

<p><a href="{url op="editUser" path=$user->getId()}" class="action">{translate key="manager.people.editProfile"}</a></p>

<div id="profile" class="page">
	<div class="profile-header">
		<div class="profile-avatar">
			{* Get profile image data *}
			{assign var="profileImage" value=$user->getSetting('profileImage')}
			
			{* Display profile image if available *}
			{if $profileImage && $profileImage.uploadName}
				<img src="{$sitePublicFilesDir}/{$profileImage.uploadName}" alt="{$user->getFullName()|escape}" class="profile-photo" />
			{else}
				<span class="profile-initials">{$user->getFirstName()|escape|substr:0:1}{$user->getLastName()|escape|substr:0:1}</span>
			{/if}
		</div>
		<div class="profile-main-info">
			<h2>{$user->getFullName()|escape}</h2>
			{if $user->getLocalizedAffiliation()}
				<p class="affiliation">{$user->getLocalizedAffiliation()|escape}</p>
			{/if}
		</div>
	</div>

	<div class="profile-sections">
		
		<div class="section u-mb-48">
			<h3 class="section-title">Personal Information</h3>
			<div class="field-list">
				{if $user->getSalutation()}
				<div class="field-item">
					<span class="field-label">{translate key="user.salutation"}:</span>
					<span class="field-value">{$user->getSalutation()|escape}</span>
				</div>
				{/if}
				
				<div class="field-item">
					<span class="field-label">{translate key="user.username"}:</span>
					<span class="field-value">{$user->getUsername()|escape}</span>
				</div>
				
				<div class="field-item">
					<span class="field-label">{translate key="user.firstName"}:</span>
					<span class="field-value">{$user->getFirstName()|escape}</span>
				</div>
				
				{if $user->getMiddleName()}
				<div class="field-item">
					<span class="field-label">{translate key="user.middleName"}:</span>
					<span class="field-value">{$user->getMiddleName()|escape}</span>
				</div>
				{/if}
				
				<div class="field-item">
					<span class="field-label">{translate key="user.lastName"}:</span>
					<span class="field-value">{$user->getLastName()|escape}</span>
				</div>
				
				{if $user->getGender()}
				<div class="field-item">
					<span class="field-label">{translate key="user.gender"}:</span>
					<span class="field-value">
						{if $user->getGender() == "M"}{translate key="user.masculine"}
						{elseif $user->getGender() == "F"}{translate key="user.feminine"}
						{elseif $user->getGender() == "O"}{translate key="user.other"}
						{/if}
					</span>
				</div>
				{/if}
			</div>
		</div>

		<div class="section u-mb-48">
			<h3 class="section-title">Contact Information</h3>
			<div class="field-list">
				<div class="field-item">
					<span class="field-label">{translate key="user.email"}:</span>
					<span class="field-value">
						{$user->getEmail()|escape}
						{assign var=emailString value=$user->getFullName()|concat:" <":$user->getEmail():">"}
						{url|assign:"url" page="user" op="email" to=$emailString|to_array redirectUrl=$currentUrl}
						<a href="{$url}" class="email-action">{icon name="mail"}</a>
					</span>
				</div>
				
				{if $user->getUrl()}
				<div class="field-item">
					<span class="field-label">{translate key="user.url"}:</span>
					<span class="field-value">
						<a href="{$user->getUrl()|escape:"quotes"}" target="_blank">{$user->getUrl()|escape}</a>
					</span>
				</div>
				{/if}
				
				{if $user->getPhone()}
				<div class="field-item">
					<span class="field-label">{translate key="user.phone"}:</span>
					<span class="field-value">{$user->getPhone()|escape}</span>
				</div>
				{/if}
				
				{if $user->getFax()}
				<div class="field-item">
					<span class="field-label">{translate key="user.fax"}:</span>
					<span class="field-value">{$user->getFax()|escape}</span>
				</div>
				{/if}
				
				{if $user->getMailingAddress()}
				<div class="field-item">
					<span class="field-label">{translate key="common.mailingAddress"}:</span>
					<span class="field-value">{$user->getMailingAddress()|strip_unsafe_html|nl2br}</span>
				</div>
				{/if}
			</div>
		</div>

		{if $user->getLocalizedAffiliation()}
		<div class="section u-mb-48">
			<h3 class="section-title">Affiliation</h3>
			<div class="field-list">
				{assign var="affiliations" value=$user->getLocalizedAffiliation()|explode:"\n"}
				
				{if count($affiliations) > 1}
					{foreach from=$affiliations item=affiliation name=affiliationLoop}
						{if $affiliation|trim}
							<div class="field-item">
								<span class="field-label">Affiliation {$smarty.foreach.affiliationLoop.iteration}:</span>
								<span class="field-value">
									{$affiliation|trim|escape}{if $smarty.foreach.affiliationLoop.last && $country}, {$country|escape}{/if}
								</span>
							</div>
						{/if}
					{/foreach}
				{else}
					<div class="field-item">
						<span class="field-label">{translate key="user.affiliation"}:</span>
						<span class="field-value">
							{$user->getLocalizedAffiliation()|escape}{if $country}, {$country|escape}{/if}
						</span>
					</div>
				{/if}
			</div>
		</div>
		{/if}
		{if $user->getLocalizedSignature() || $userInterests}
		<div class="section u-mb-48">
			<h3 class="section-title">Academic Information</h3>
			<div class="field-list">
				{if $user->getLocalizedSignature()}
				<div class="field-item">
					<span class="field-label">{translate key="user.signature"}:</span>
					<span class="field-value">{$user->getLocalizedSignature()|escape|nl2br}</span>
				</div>
				{/if}
				
				{if $userInterests}
				<div class="field-item">
					<span class="field-label">{translate key="user.interests"}:</span>
					<span class="field-value">{$userInterests|escape}</span>
				</div>
				{/if}
			</div>
		</div>
		{/if}

		{if $user->getLocales()}
		<div class="section u-mb-48">
			<h3 class="section-title">{translate key="user.workingLanguages"}</h3>
			<div class="languages-container">
				{foreach name=workingLanguages from=$user->getLocales() item=localeKey}
					<span class="language-tag">{$localeNames.$localeKey|escape}</span>
				{/foreach}
				{foreach name=workingLanguages from=$user->getLocales() item=localeKey}{$localeNames.$localeKey|escape}{if !$smarty.foreach.workingLanguages.last}; {/if}{foreachelse}&mdash;{/foreach}
			</div>
		</div>
		{/if}

		{if $user->getLocalizedBiography()}
		<div class="section u-mb-48 biography-section">
			<h3 class="section-title">{translate key="user.biography"}</h3>
			<div class="biography-content">
				{$user->getLocalizedBiography()|strip_unsafe_html|nl2br}
			</div>
		</div>
		{/if}

		{if $user->getLocalizedGossip()}
		<div class="section u-mb-48">
			<h3 class="section-title">Additional Information</h3>
			<div class="field-list">
				<div class="field-item">
					<span class="field-label">{translate key="user.gossip"}:</span>
					<span class="field-value">{$user->getLocalizedGossip()|escape}</span>
				</div>
			</div>
		</div>
		{/if}

		<div class="section u-mb-48 system-section">
			<h3 class="section-title">Account Information</h3>
			<div class="system-info">
				<div class="system-item">
					<span class="system-label">{translate key="user.dateRegistered"}:</span>
					<span class="system-value">{$user->getDateRegistered()|date_format:$datetimeFormatLong}</span>
				</div>
				<div class="system-item">
					<span class="system-label">{translate key="user.dateLastLogin"}:</span>
					<span class="system-value">
						{if $user->getDateLastLogin()}
							{$user->getDateLastLogin()|date_format:$datetimeFormatLong}
						{else}
							<em>Never</em>
						{/if}
					</span>
				</div>
			</div>
		</div>
	</div>
</div>

{include file="common/footer-parts/footer-user.tpl"}

