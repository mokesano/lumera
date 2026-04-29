{**
 * templates/about/submissions.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Journal / Submissions.
 *
 *}
{strip}
{assign var="pageTitle" value="about.submissions"}
{include file="common/header.tpl"}
{/strip}

{if $currentJournal->getSetting('journalPaymentsEnabled') && ($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled')) }
	{assign var="authorFees" value=1}
{/if}

{if $currentJournal->getLocalizedSetting('authorGuidelines') != ''}
<div id="authorGuidelines" class="block">
    <header class="c-anchored-heading"><h3>{translate key="about.authorGuidelines"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
    <p>{$currentJournal->getLocalizedSetting('authorGuidelines')|nl2br}</p>

    {if !$currentJournal->getSetting('disableUserReg')}
		<p class="callout">{translate key="about.onlineSubmissions.registrationRequired"}</p>
		<div >
			<p>{translate key="about.onlineSubmissions.haveAccount" journalTitle=$siteTitle|escape}</p>
			<a href="{url page="login"}" class="action">{translate key="about.onlineSubmissions.login"}</a>
			<a href="{url page="author" op="submit"}" class="action">Start New Submission</a>
		</div>
		
		<div >			
			<p>{translate key="about.onlineSubmissions.needAccount"}</p>
			<a href="{url page="user" op="register"}" class="action">{translate key="about.onlineSubmissions.registration"}</a>
		</div>
    {/if}

    <div class="separator">&nbsp;</div>
</div>
{/if}

{if $submissionChecklist}
<div id="submissionPreparationChecklist" class="block">
	<header class="c-anchored-heading"><h3>{translate key="about.submissionPreparationChecklist"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
	<p>{translate key="about.submissionPreparationChecklist.description"}</p>
	<ol>
		{foreach from=$submissionChecklist item=checklistItem}
			<li>{$checklistItem.content|nl2br}</li>
		{/foreach}
	</ol>
	<div class="separator">&nbsp;</div>
</div>
{/if}{* $submissionChecklist *}

{if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}
<div id="copyrightNotice" class="block">
    <header class="c-anchored-heading"><h3>{translate key="about.copyrightNotice"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
    <p>{$currentJournal->getLocalizedSetting('copyrightNotice')|nl2br}</p>

    <div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentJournal->getLocalizedSetting('privacyStatement') != ''}
<div id="privacyStatement" class="block">
    <header class="c-anchored-heading"><h3>{translate key="about.privacyStatement"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
    <p>{$currentJournal->getLocalizedSetting('privacyStatement')|nl2br}</p>

    <div class="separator">&nbsp;</div>
</div>
{/if}

{if $authorFees}
<div id="authorFees" class="block">
    <header class="c-anchored-heading"><h3>{translate key="manager.payment.authorFees"}</h3><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
	<p>{translate key="about.authorFeesMessage"}</p>
	{if $currentJournal->getSetting('submissionFeeEnabled')}
	<section title="{$currentJournal->getLocalizedSetting('submissionFeeName')|escape}">
		<h5 class="u-mb-0 u-mt-16">{$currentJournal->getLocalizedSetting('submissionFeeName')|escape}: <span class="bold">{$currentJournal->getSetting('currency')} {$currentJournal->getSetting('submissionFee')|string_format:"%.2f"|number_format:2:'.':','}</span></h5>
		<p>{$currentJournal->getLocalizedSetting('submissionFeeDescription')|nl2br}<p>
	</section>
	{/if}
	{if $currentJournal->getSetting('fastTrackFeeEnabled')}
	<section title="{$currentJournal->getLocalizedSetting('fastTrackFeeName')|escape}">
		<h5 class="u-mb-0 u-mt-16">{$currentJournal->getLocalizedSetting('fastTrackFeeName')|escape}: <span class="bold">{$currentJournal->getSetting('currency')} {$currentJournal->getSetting('fastTrackFee')|string_format:"%.2f"|number_format:2:'.':','}</span></h5>
		<p>{$currentJournal->getLocalizedSetting('fastTrackFeeDescription')|nl2br}<p>
	</section>
	{/if}
	{if $currentJournal->getSetting('publicationFeeEnabled')}
	<section title="{$currentJournal->getLocalizedSetting('publicationFeeName')|escape}">
		<h5 class="u-mb-0 u-mt-16">{$currentJournal->getLocalizedSetting('publicationFeeName')|escape}: <span class="bold">{$currentJournal->getSetting('currency')} {$currentJournal->getSetting('publicationFee')|string_format:"%.2f"|number_format:2:'.':','}</span></h5>
		<p>{$currentJournal->getLocalizedSetting('publicationFeeDescription')|nl2br}<p>
	</section>
	{/if}
	{if $currentJournal->getLocalizedSetting('waiverPolicy') != ''}
		<p>{$currentJournal->getLocalizedSetting('waiverPolicy')|nl2br}</p>
	{/if}
</div>
{/if}

{include file="common/footer.tpl"}
