{**
 * templates/about/contact.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Journal / Journal Contact.
 *
 *}
{strip}
{assign var="pageTitle" value="about.journalContact"}
{include file="common/header-ABOUT.tpl"}
{/strip}
<section id="contact" class="collection">
{if not ($currentJournal->getLocalizedSetting('contactTitle') == '' && $currentJournal->getLocalizedSetting('contactAffiliation') == '' && $currentJournal->getLocalizedSetting('contactMailingAddress') == '' && empty($journalSettings.contactPhone) && empty($journalSettings.contactFax) && empty($journalSettings.contactEmail))}
<section id="principalContact" class="collection block">
<h3>Publishing Contact</h3>
<p>General questions about the journal, pre-submission queries, editorial policy or procedure, or special issue proposals.</p>
<section class="article">
	{if !empty($journalSettings.contactName)}
		<h4>{$journalSettings.contactName|escape}</h4>
	{/if}
	<section class="article-body">
	<p>
	{assign var=s value=$currentJournal->getLocalizedSetting('contactTitle')}
	{if $s}{$s|escape}<br />{/if}

	{if !empty($journalSettings.contactPhone)}
		{translate key="about.contact.phone"}: {$journalSettings.contactPhone|escape}<br />
	{/if}
	{if !empty($journalSettings.contactFax)}
		{translate key="about.contact.fax"}: {$journalSettings.contactFax|escape}<br />
	{/if}
	{if !empty($journalSettings.contactEmail)}
		{translate key="about.contact.email"}: {mailto address=$journalSettings.contactEmail|escape encode="hex"}<br />
	{/if}

	{assign var=s value=$currentJournal->getLocalizedSetting('contactAffiliation')}
	{if $s}Affiliation: {$s|escape}<br />{/if}

	{assign var=s value=$currentJournal->getLocalizedSetting('contactMailingAddress')}
	{if $s}{$s|nl2br}{/if}
	</p>
	</section>
</section>
</section>
<br />
{/if}

{if not (empty($journalSettings.supportName) && empty($journalSettings.supportPhone) && empty($journalSettings.supportEmail))}
<section id="supportContact" class="collection block">
<h3>{translate key="about.contact.supportContact"}</h3>
<p>Questions about manuscripts already sent to production.</p>
<section class="article">
	{if !empty($journalSettings.supportName)}
		<h4>{$journalSettings.supportName|escape}</h4>
	{/if}
	<section class="article-body">
	<p>
	{assign var=s value=$currentJournal->getLocalizedSetting('contactTitle')}
	{if $s}{$s|escape}<br />{/if}

	{if !empty($journalSettings.supportPhone)}
		{translate key="about.contact.phone"}: {$journalSettings.supportPhone|escape}<br />
	{/if}
	{if !empty($journalSettings.supportEmail)}
		{translate key="about.contact.email"}: {mailto address=$journalSettings.supportEmail|escape encode="hex"}<br />
	{/if}
	</p>
	</section>
</section>
</section>
<br />
{/if}

{if !empty($journalSettings.mailingAddress)}
<section id="mailingAddress" class="collection block">
<h3>Editorial Office</h3>
<p>Questions about the suitability of a topic, how to submit, manuscripts under consideration, and the online submission system (if applicable).</p>
<section class="article">
	<section class="article-body">
		<p>{$journalSettings.mailingAddress|nl2br}</p>
	</section>	
</section>
</section>
<br />
{/if}

<section class="collection">
	<h3>Customer Service</h3>
	<br />
	<p><a href="mailto:admin@stipwunaraha.ac.id">admin@sangia.org</a></p>
</section>

</section>

{include file="common/footer.tpl"}

