{**
 * templates/about/submissions.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Journal / Submissions.
 *
 *}
{strip}
{assign var="pageTitle" value="about.submissions"}
{include file="common/header-gfa.tpl"}
{/strip}

{if $currentJournal->getSetting('journalPaymentsEnabled') && 
		($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled')) }
	{assign var="authorFees" value=1}
{/if}

<ul class="c-nav-menu">
	<li id="linkDisableUserReg" class="c-nav__link"><a href="{url page="about" op="submissions" anchor="onlineSubmissions"}">{translate key="about.onlineSubmissions"}</a></li>
	{if $currentJournal->getLocalizedSetting('authorGuidelines') != ''}<li id="linkAuthorGuidelines" class="c-nav__link"><a href="{url page="about" op="submissions" anchor="authorGuidelines"}">{translate key="about.authorGuidelines"}</a></li>{/if}
    {if $submissionChecklist}<li id="linkSubmissionPreparationChecklist" class="c-nav__link"><a href="{url page="about" op="submissions" anchor="submissionPreparationChecklist"}">{translate key="about.submissionPreparationChecklist"}</a></li>
    {/if}{* $submissionChecklist *}
	{if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}<li id="linkCopyrightNotice" class="c-nav__link"><a href="{url page="about" op="submissions" anchor="copyrightNotice"}">{translate key="about.copyrightNotice"}</a></li>{/if}
	{if $currentJournal->getLocalizedSetting('privacyStatement') != ''}<li id="linkPrivacyStatement" class="c-nav__link"><a href="{url page="about" op="submissions" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></li>{/if}
	{if $authorFees}<li id="linkAuthorFees" class="c-nav__link"><a href="{url page="about" op="submissions" anchor="authorFees"}">{translate key="about.authorFees"}</a></li>{/if}
</ul>

<section class="content u-mt-48">

<div id="onlineSubmissions" class="block">
    <header class="c-anchored-heading">
	<h2>{translate key="about.onlineSubmissions"}</h2>
        <a class="c-anchored-heading__helper" href="#menu">Back to top</a>	
	</header>
	<p><iframe class="lazyload" src="https://drive.google.com/file/d/1nVm8jfIldLH7xbu7EhgA0nPwLASfYivA/preview" width="100%" height="560"></iframe></p>
	<p>
		{translate key="about.onlineSubmissions.haveAccount" journalTitle=$siteTitle|escape}<br />
		<a href="{url page="login"}" class="action">{translate key="about.onlineSubmissions.login"}</a>
	</p>
	{if !$currentJournal->getSetting('disableUserReg')}
		<p>
			{translate key="about.onlineSubmissions.needAccount"}<br />
			<a href="{url page="user" op="register"}" class="action">{translate key="about.onlineSubmissions.registration"}</a>
		</p>
		<p>{translate key="about.onlineSubmissions.registrationRequired"}</p>
	{/if}
	
</div>

<div class="separator">&nbsp;</div>

{if $currentJournal->getLocalizedSetting('authorGuidelines') != ''}
<div id="authorGuidelines" class="article-body block">
    <header class="c-anchored-heading">
        <h2>{translate key="about.authorGuidelines"}</h2>
        <a class="c-anchored-heading__helper" href="#menu">Back to top</a>
	</header>        
<p>{$currentJournal->getLocalizedSetting('authorGuidelines')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

{if $submissionChecklist}
<div id="submissionPreparationChecklist" class="block">
	<header class="c-anchored-heading">
	    <h2>{translate key="about.submissionPreparationChecklist"}</h2>
	    <a class="c-anchored-heading__helper" href="#menu">Back to top</a>
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
    <header class="c-anchored-heading">
        <h2>{translate key="about.copyrightNotice"}</h2>
        <a class="c-anchored-heading__helper" href="#menu">Back to top</a>
	</header>
	<p>{$currentJournal->getLocalizedSetting('copyrightNotice')|nl2br}</p>

    <div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentJournal->getLocalizedSetting('privacyStatement') != ''}
<div id="privacyStatement" class="block">
    <header class="c-anchored-heading">
        <h2>{translate key="about.privacyStatement"}</h2>
        <a class="c-anchored-heading__helper" href="#menu">Back to top</a>
	</header>        
    <p>{$currentJournal->getLocalizedSetting('privacyStatement')|nl2br}</p>

    <div class="separator">&nbsp;</div>
</div>
{/if}

{if $authorFees}
<div id="authorFees" class="block">
    <header class="c-anchored-heading">
        <h2>{translate key="manager.payment.authorFees"}</h2>
        <a class="c-anchored-heading__helper" href="#menu">Back to top</a>
	</header>        
	<p>{translate key="about.authorFeesMessage"}</p>
	{if $currentJournal->getSetting('submissionFeeEnabled')}
	<section title="{$currentJournal->getLocalizedSetting('submissionFeeName')|escape}">
		<h3 class="u-mb-0 u-mt-16">{$currentJournal->getLocalizedSetting('submissionFeeName')|escape}: <span class="bold">{$currentJournal->getSetting('currency')} {$currentJournal->getSetting('submissionFee')|string_format:"%.2f"|number_format:2:".":","}</span></h3>
		<p>{$currentJournal->getLocalizedSetting('submissionFeeDescription')|nl2br}<p>
	</section>
	{/if}
	{if $currentJournal->getSetting('fastTrackFeeEnabled')}
	<section title="{$currentJournal->getLocalizedSetting('fastTrackFeeName')|escape}">
		<h3 class="u-mb-0 u-mt-16">{$currentJournal->getLocalizedSetting('fastTrackFeeName')|escape}: <span class="bold">{$currentJournal->getSetting('currency')} {$currentJournal->getSetting('fastTrackFee')|string_format:"%.2f"|number_format:2:".":","}</span></h3>
		<p>{$currentJournal->getLocalizedSetting('fastTrackFeeDescription')|nl2br}<p>
	</section>
	{/if}
	{if $currentJournal->getSetting('publicationFeeEnabled')}
	<section title="{$currentJournal->getLocalizedSetting('publicationFeeName')|escape}">
		<h3 class="u-mb-0 u-mt-16">{$currentJournal->getLocalizedSetting('publicationFeeName')|escape}: <span class="bold">{$currentJournal->getSetting('currency')} {$currentJournal->getSetting('publicationFee')|string_format:"%.2f"|number_format:2:".":","}</span></h3>
		<p>{$currentJournal->getLocalizedSetting('publicationFeeDescription')|nl2br}<p>
	</section>
	{/if}
	{if $currentJournal->getLocalizedSetting('waiverPolicy') != ''}
		<p>{$currentJournal->getLocalizedSetting('waiverPolicy')|nl2br}</p>
	{/if}
</div>
{/if}

</section>

{include file="common/footer.tpl"}

