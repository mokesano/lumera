{**
 * templates/manager/emails/emails.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of email templates in journal management.
 *
 *}
{strip}
{assign var="pageTitle" value="manager.emails"}
{include file="common/header-USER027.tpl"}
{/strip}

<div id="emails" class="block">
    <table class="listing" width="100%">
    	<tr valign="bottom">
    		<th width="20%">{translate key="manager.emails.emailTemplates"}</th>
    		<th width="10%">{translate key="email.sender"}</th>
    		<th width="10%">{translate key="email.recipient"}</th>
    		<th width="35%">{translate key="email.subject"}</th>
    		<th width="25%" align="right">{translate key="common.action"}</th>
    	</tr>
    	<tr><td colspan="5" class="headseparator">&nbsp;</td></tr>
        {iterate from=emailTemplates item=emailTemplate}
        	<tr valign="top">
        		<td>
        			{url|assign:"emailUrl" op="email" template=$emailTemplate->getEmailKey()}
        			{$emailTemplate->getEmailKey()|escape|replace:"_":" "}&nbsp;{icon name="mail" url=$emailUrl}
        		</td>
        		<td>{translate key=$emailTemplate->getFromRoleName()}</td>
        		<td>{translate key=$emailTemplate->getToRoleName()}</td>
        		<td>{$emailTemplate->getSubject()|escape|truncate:50:"..."}</td>
        		<td align="right" class="nowrap">
        			<a href="{url op="editEmail" path=$emailTemplate->getEmailKey()}" class="action">{translate key="common.edit"}</a>
        			{if $emailTemplate->getCanDisable() && !$emailTemplate->isCustomTemplate()}
        				{if $emailTemplate->getEnabled() == 1}
        					<a href="{url op="disableEmail" path=$emailTemplate->getEmailKey()}" class="action">{translate key="manager.emails.disable"}</a>
        				{else}
        					<a href="{url op="enableEmail" path=$emailTemplate->getEmailKey()}" class="action">{translate key="manager.emails.enable"}</a>
        				{/if}
        			{/if}
        			{if $emailTemplate->isCustomTemplate()}
        				<a href="{url op="deleteCustomEmail" path=$emailTemplate->getEmailKey()}" onclick="return confirm('{translate|escape:"jsparam" key="manager.emails.confirmDelete"}')" class="action">{translate key="common.delete"}</a>
        			{else}
        				<a href="{url op="resetEmail" path=$emailTemplate->getEmailKey()}" onclick="return confirm('{translate|escape:"jsparam" key="manager.emails.confirmReset"}')" class="action">{translate key="manager.emails.reset"}</a>
        			{/if}
        		</td>
        	</tr>
        {/iterate}
        {if $emailTemplates->wasEmpty()}
        	<tr>
        		<td colspan="5" class="nodata">{translate key="common.none"}</td>
        	</tr>
        	<tr>
        		<td colspan="5" class="endseparator">&nbsp;</td>
        	</tr>
        {else}
        	<tr class="u-hide">
        		<td colspan="3" align="left">{page_info iterator=$emailTemplates}</td>
        		<td align="right" colspan="2">{page_links anchor="emails" name="emails" iterator=$emailTemplates}</td>
        	</tr>
        {/if}
    </table>
    <div class="button-link">
        <a href="{url op="createEmail"}" class="link-button">{translate key="manager.emails.createEmail"}</a>
        <a href="{url op="resetAllEmails"}" onclick="return confirm('{translate|escape:"jsparam" key="manager.emails.confirmResetAll"}')" class="link-button">{translate key="manager.emails.resetAll"}</a>
    </div>
</div>

{if !$emailTemplates->wasEmpty()}
<div class="colspan u-mb-0" id="colspan">	    
	<section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	    <div class="c-pagination">{page_info iterator=$emailTemplates}</div>
    </section>
    {if $emailTemplates->getPageCount() > 1}
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination">{page_links anchor="emails" name="emails" iterator=$emailTemplates}
       </div>
    </section>
    {/if}
</div>
{/if}

{include file="common/footer-parts/footer-user.tpl"}
