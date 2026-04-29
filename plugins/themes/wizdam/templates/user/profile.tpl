{**
 * templates/user/profile.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User profile form.
 *
 *}
{strip}
{assign var="pageTitle" value="user.profile.editProfile"}
{url|assign:"url" op="profile"}
{include file="common/header-USER027.tpl"}
{/strip}

<form id="profile" method="post" action="{url op="saveProfile"}" enctype="multipart/form-data">

{include file="common/formErrors.tpl"}

{if count($formLocales) > 1}
    
    <div class="c-form-field">
        {fieldLabel name="formLocale" key="form.formLanguage" class="c-form-field__label"}
            
        {url|assign:"userProfileUrl" page="user" op="profile" escape=false} {form_language_chooser form="profile" url=$userProfileUrl}
		
		<div id="instruct" class="statement">
		        <p class="statement">{translate key="form.formLanguage.description"}</p></div>
		{/if}
		</div>
	
	<div class="p-grid">
	    <div class="c-form-field p-grid__col p-grid__col--12-at-xs p-grid__col--6-at-md">
        {fieldLabel name="email" required="true" key="user.email" class="c-form-field__label"}
        <input class="c-form-field__input" type="text" id="email" name="email" value="{$email|escape}" size="30" maxlength="90" /></div>
        
        <div class="c-form-field p-grid__col p-grid__col--12-at-xs p-grid__col--6-at-md">
        {fieldLabel suppressId="true" name="username" key="user.username" class="c-form-field__label"}
        <div class="c-form-field__input value" >{$username|escape}</div></div>
        </div>
	    
	<div class="p-grid">
	    <div class="c-form-field p-grid__col p-grid__col--12-at-xs p-grid__col--6-at-cmr">
	   
	   {fieldLabel name="firstName" required="true" key="user.firstName" class="c-form-field__label"}
	   <input type="text" name="firstName" id="firstName" value="{$firstName|escape}" size="20" maxlength="40" class="c-form-field__input" /></div>
	   
	   <div class="c-form-field p-grid__col p-grid__col--12-at-xs p-grid__col--6-at-cmr">
	   
	   {fieldLabel name="middleName" key="user.middleName" class="c-form-field__label"}
	   <input type="text" name="middleName" id="middleName" value="{$middleName|escape}" size="20" maxlength="40" class="c-form-field__input" /></div>
	   
	   <div class="c-form-field p-grid__col p-grid__col--12-at-xs p-grid__col--6-at-cmr">
	   
	   {fieldLabel name="lastName" required="true" key="user.lastName" class="c-form-field__label"}
	   <input type="text" name="lastName" id="lastName" value="{$lastName|escape}" size="20" maxlength="90" class="c-form-field__input" /></div>
	   </div>

    <div class="p-grid">
	    
	    <div class="c-form-field p-grid__col p-grid__col--12-at-xs p-grid__col--6-at-cmr">
			{fieldLabel name="salutation" key="user.salutation" class="c-form-field__label"}
			<input type="text" name="salutation" id="salutation" value="{$salutation|escape}" size="20" maxlength="40" class="c-form-field__input" /></div>
			
	    <div class="c-form-field p-grid__col p-grid__col--12-at-xs p-grid__col--6-at-cmr">
		    
			{fieldLabel name="initials" key="user.initials" class="c-form-field__label"}
			<input type="text" id="initials" name="initials" value="{$initials|escape}" size="5" maxlength="5" class="c-form-field__input" /></div>

		<div class="c-form-field p-grid__col p-grid__col--12-at-xs p-grid__col--6-at-cmr">
			
			{fieldLabel name="gender-m" key="user.gender" class="c-form-field__label"}
			<select name="gender" id="gender" size="1" class="selectMenu">
					{html_options_translate options=$genderOptions selected=$gender}</select></div>
		</div>
		
	<div class="c-form-field">
		<div id="instruct" class="statement">
	    <p class="statement">{translate key="user.salutationDescription"}</p>
	    <p class="statement">{translate key="user.initialsExample"}</p></div></div>

    
    <div class="c-form-field">
		{fieldLabel name="affiliation" required="true" key="user.affiliation" class="c-form-field__label"}
			<textarea id="affiliation" name="affiliation[{$formLocale|escape}]" rows="3" cols="40" class="textArea">{$affiliation[$formLocale]|escape}</textarea>
		<div id="instruct" class="statement">
	    <p class="statement">{translate key="user.affiliation.description"}</p>
            </div>
		</div>
	
	<div class="c-form-field">
        {fieldLabel name="country" required="true" key="common.country" class="c-form-field__label"}
        <select name="country" id="country" class="selectMenu">
            <option value=""></option>
            {html_options options=$countries selected=$country}
            </select>
		</div>
	
	<div class="p-grid">
	    <div class="c-form-field p-grid__col p-grid__col--12-at-xs p-grid__col--6-at-md">
		{fieldLabel name="phone" key="user.phone" required="true" class="c-form-field__label"}
		<input type="text" name="phone" id="phone" value="{$phone|escape}" size="15" maxlength="24" class="c-form-field__input" /></div>
		
		<div class="c-form-field p-grid__col p-grid__col--12-at-xs p-grid__col--6-at-md">
		{fieldLabel name="fax" key="user.fax" class="c-form-field__label"}
		<input type="text" name="fax" id="fax" value="{$fax|escape}" size="15" maxlength="24" class="c-form-field__input" /></div>
		</div>
		
	<div class="c-form-field">
		{fieldLabel name="signature" key="user.signature" class="c-form-field__label"}
		<textarea name="signature[{$formLocale|escape}]" id="signature" rows="3" cols="40" class="textArea" style="font-size: 15.1167px;line-height: 1.2em">{$signature[$formLocale]|escape}</textarea>
		</div>
	
	<div class="c-form-field">
		{fieldLabel name="gossip" key="user.gossip" class="c-form-field__label"}
		<textarea name="gossip[{$formLocale|escape}]" id="gossip" rows="4" cols="40" class="textArea" style="font-size: 15.1167px;line-height: 1.2em">{$gossip[$formLocale]|escape}</textarea>
		</div>	

    <div class="c-form-field">
		{fieldLabel name="mailingAddress" key="common.mailingAddress" class="c-form-field__label"}
		<textarea name="mailingAddress" id="mailingAddress" rows="3" cols="40" class="textArea">{$mailingAddress|escape}</textarea>
		
		<div id="instruct" class="statement">
	    <p class="statement">{translate key="user.mailingAddressdescription"}</p></div>
        </div>
    
    <div class="c-form-field">
		{fieldLabel name="orcid" key="user.orcid" class="c-form-field__label"}
		<input type="text" id="orcid" name="orcid" value="{$orcid|escape}" size="40" maxlength="255" class="c-form-field__input" />
	<div id="instruct" class="statement">
	    <p class="statement">{translate key="user.orcid.description"}</p>
            </div>
        </div>

	<div class="c-form-field">
		{fieldLabel name="userUrl" key="user.url" class="c-form-field__label"}
		<input type="text" id="userUrl" name="userUrl" value="{$userUrl|escape}" size="40" maxlength="255" class="c-form-field__input" />
		<div id="instruct" class="statement">
	    <p class="statement">{translate key="user.url.description"}</p>
            </div>
        </div>
        

    <div class="p-grid">
	    <div class="c-form-field p-grid__col p-grid__col--12-at-xs p-grid__col--6-at-md">
		{fieldLabel name="scopus" key="user.scopus" class="c-form-field__label"}
		<input type="text" name="scopus" id="scopus" value="{$scopus|escape}" size="15" maxlength="12" class="c-form-field__input" /></div>

		<div class="c-form-field p-grid__col p-grid__col--12-at-xs p-grid__col--6-at-md">
		{fieldLabel name="sinta" key="user.sinta" class="c-form-field__label"}
		<input type="text" name="sinta" id="sinta" value="{$sinta|escape}" size="15" maxlength="10" class="c-form-field__input" /></div>
		</div>
	<div class="c-form-field">
	    <div id="instruct" class="statement">
	    <p class="statement">{translate key="user.scopusSintaiD.description"}</p>
            </div>
        </div>
        
    <div class="c-form-field">
		{fieldLabel name="biography" key="user.biography" class="c-form-field__label"}
		<textarea name="biography[{$formLocale|escape}]" id="biography" rows="15" cols="40" class="textArea">{$biography[$formLocale]|escape}</textarea>
		</div>
	<div class="c-form-field">
		<div id="instruct" class="statement">
	    <p class="statement">{translate key="user.biography.descriptions"}</p>
            </div>
        </div>


<table class="data" width="100%">
<br />
{if $currentJournal}
	<tr valign="top">
		<td class="label">{translate key="user.roles"}</td>
		<td class="value">
			{if $allowRegReader}
				<input type="checkbox" id="readerRole" name="readerRole" {if $isReader || $readerRole}checked="checked" {/if}/>&nbsp;{fieldLabel name="readerRole" key="user.role.reader"}<br/>
			{/if}
		</td>
		<td class="value">
			{if $allowRegAuthor}
				<input type="checkbox" id="authorRole" name="authorRole" {if $isAuthor || $authorRole}checked="checked" {/if}/>&nbsp;{fieldLabel name="authorRole" key="user.role.author"}<br/>
			{/if}
		</td>
		<td class="value">
			{if $allowRegReviewer}
				<input type="checkbox" id="reviewerRole" name="reviewerRole" {if $isReviewer || $reviewerRole}checked="checked" {/if}/>&nbsp;{fieldLabel name="reviewerRole" key="user.role.reviewer"}<br/>
			{/if}
		</td>				
	</tr>
{/if}
</table>


{if $allowRegReviewer || $isReviewer}
    <div class="c-form-field">
		{fieldLabel name="interests" key="user.interests" class="c-form-field__label"}</div>

		{include file="form/interestsInput.tpl" FBV_interestsKeywords=$interestsKeywords FBV_interestsTextOnly=$interestsTextOnly}
		
{/if}



<table class="data" width="100%">
<tr valign="top">
	<td class="label">
		{fieldLabel name="profileImage" key="user.profile.form.profileImage"}
	</td>
	<td class="value">
		<input type="file" id="profileImage" name="profileImage" class="uploadField" /> <input type="submit" name="uploadProfileImage" value="{translate key="common.upload"}" class="button" />
		{if $profileImage}
			<br /> {translate key="common.fileName"}: {$profileImage.name|escape} {$profileImage.dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deleteProfileImage" value="{translate key="common.delete"}" class="button" />
			<br />
			<img src="{$sitePublicFilesDir}/{$profileImage.uploadName|escape:"url"}" width="{$profileImage.width|escape}" height="{$profileImage.height|escape}" style="border: 0;" alt="{translate key="user.profile.form.profileImage"}" />
		{/if}
	</td>
</tr>

{if count($availableLocales) > 1}
<tr valign="top">
	<td class="label">{translate key="user.workingLanguages"}</td>
	{foreach from=$availableLocales key=localeKey item=localeName}
	<td class="value"><input type="checkbox" name="userLocales[]" id="userLocales-{$localeKey|escape}" value="{$localeKey|escape}"{if in_array($localeKey, $userLocales)} checked="checked"{/if} /> <label for="userLocales-{$localeKey|escape}">{$localeName|escape}</label><br />
	</td>
	{/foreach}
</tr>
{/if}

{if $displayOpenAccessNotification}
	{assign var=notFirstJournal value=0}
	{foreach from=$journals name=journalOpenAccessNotifications key=thisJournalId item=thisJournal}
		{assign var=thisJournalId value=$thisJournal->getJournalId()}
		{assign var=publishingMode value=$thisJournal->getSetting('publishingMode')}
		{assign var=enableOpenAccessNotification value=$thisJournal->getSetting('enableOpenAccessNotification')}
		{assign var=notificationEnabled value=$user->getSetting('openAccessNotification', $thisJournalId)}
		{if !$notFirstJournal}
			{assign var=notFirstJournal value=1}
			<tr valign="top">
				<td class="label">{translate key="user.profile.form.openAccessNotifications"}</td>
				<td class="value">
		{/if}

		{if $publishingMode == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $enableOpenAccessNotification}
			<input type="checkbox" name="openAccessNotify[]" {if $notificationEnabled}checked="checked" {/if}id="openAccessNotify-{$thisJournalId|escape}" value="{$thisJournalId|escape}" /> <label for="openAccessNotify-{$thisJournalId|escape}">{$thisJournal->getLocalizedTitle()|escape}</label><br/>
		{/if}

		{if $smarty.foreach.journalOpenAccessNotifications.last}
				</td>
			</tr>
		{/if}
	{/foreach}
{/if}

</table>
<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url page="user"}'" /></p>
</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}

