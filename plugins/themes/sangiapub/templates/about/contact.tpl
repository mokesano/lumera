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

{if $currentJournal}
	<section id="contact" class="collection section">
		
		{if not ($currentJournal->getLocalizedSetting('contactTitle') == '' && $currentJournal->getLocalizedSetting('contactAffiliation') == '' && $currentJournal->getLocalizedSetting('contactMailingAddress') == '' && empty($journalSettings.contactPhone) && empty($journalSettings.contactFax) && empty($journalSettings.contactEmail))}
			<section id="principalContact" class="collection section">
				<h3 class="sub-title">Publishing Contact</h3>
				<p class="no-class">General questions about the journal, pre-submission queries, editorial policy or procedure, or special issue proposals.</p>
								
				<section class="collection-data">
					{if !empty($journalSettings.contactName)}
						<h4 class="no-class">{$journalSettings.contactName|escape}</h4>
					{/if}

					{assign var=title value=$currentJournal->getLocalizedSetting('contactTitle')}
					{if $title}<h5 class="no-class">{$title|escape}</h5>{/if}

					<div id="contactDetails" class="no-class">
						{if !empty($journalSettings.contactPhone)}
							<p class="no-class u-mb-0">{translate key="about.contact.phone"}: {$journalSettings.contactPhone|escape}</p>
						{/if}
						
						{if !empty($journalSettings.contactFax)}
							<p class="no-class u-mb-0">{translate key="about.contact.fax"}: {$journalSettings.contactFax|escape}</p>
						{/if}
						
						{if !empty($journalSettings.contactEmail)}
							<p class="no-class u-mb-0">{translate key="about.contact.email"}: {mailto address=$journalSettings.contactEmail|escape encode="hex"}</p>
						{/if}
					
						{assign var=contacInstitution value=$currentJournal->getLocalizedSetting('contactAffiliation')}
						{if $contacInstitution}<p class="no-class u-mb-0">{$contacInstitution|escape}</p>{/if}
					
						{assign var=contactAddres value=$currentJournal->getLocalizedSetting('contactMailingAddress')}
						{if $contactAddres}<p class="u-mb-24">{$contactAddres|nl2br}</p>{/if}
					</div>
				</section>
			</section>
		{/if}

		{if not (empty($journalSettings.supportName) && empty($journalSettings.supportPhone) && empty($journalSettings.supportEmail))}
			<section id="supportContact" class="collection section">
				<h3 class="sub-title">{translate key="about.contact.supportContact"}</h3>
				<p class="no-class">Questions about manuscripts already sent to production.</p>
				<section class="support-name">
					{if !empty($journalSettings.supportName)}
						<h4 class="">{$journalSettings.supportName|escape}</h4>
					{/if}
					<section class="article-body section">
						{assign var=s value=$currentJournal->getLocalizedSetting('contactTitle')}
						{if $s}<h5 class="no-class">{$s|escape}</h5>{/if}

						{if !empty($journalSettings.supportPhone)}
							<p class="u-mb-0">{translate key="about.contact.phone"}: {$journalSettings.supportPhone|escape}</p>
						{/if}

						{if !empty($journalSettings.supportEmail)}
							<p class="u-mb-0">{translate key="about.contact.email"}: {mailto address=$journalSettings.supportEmail|escape encode="hex"}
						{/if}
					</section>
				</section>
			</section>
		{/if}
		
		{if !empty($journalSettings.mailingAddress)}
			<section id="mailingAddress" class="collection section">
				<h3 class="sub-title">Editorial Office</h3>
				<p class="no-class">Questions about the suitability of a topic, how to submit, manuscripts under consideration, and the online submission system (if applicable).</p>
				<section class="collection-data section">
					<p class="no-class">{$journalSettings.mailingAddress|nl2br}</p>
				</section>
			</section>
		{/if}
		
		{if $sitePrincipalContactName || $sitePrincipalContactEmail}
			<section id="principalContact" class="collection section">
				{if $sitePrincipalContactName}
					<h3 class="sub-title">{$sitePrincipalContactName|escape}</h3>
					<p class="no-class">Questions about how to submit and the online submission system (if applicable).</p>
				{/if}
				{if $sitePrincipalContactEmail}
					<p class="no-class">
						<a href="mailto:{$sitePrincipalContactEmail|escape}">{$sitePrincipalContactEmail|escape}</a>
					</p>
				{/if}
			</section>
		{/if}
	</section>
{else}
	<section id="principalContact" class="collection section">
		{if $sitePrincipalContactName}
			<h3 class="sub-title">{$sitePrincipalContactName|escape}</h3>
		{/if}
		{if $siteMailingAddress}
			<p class="no-class">{$siteMailingAddress|escape}</p>
		{/if}
		{if $sitePrincipalContactEmail}
			<p class="no-class"><a class="" title="Principal Contact Email" href="mailto:{$sitePrincipalContactEmail|escape}">{$sitePrincipalContactEmail|escape}</a></p>
		{/if}
	</section>
{/if}

        </div>
    </div>
</div>    

<div class="live-area-wrapper">
	<div class="row">
	    <div role="main" class="column">
	        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15919.707735119626!2d122.5556084!3d-4.0353334!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x59d6a213a880ac1a!2sSangia%20News%20%26%20Media!5e0!3m2!1sid!2sid!4v1658598581283!5m2!1sid!2sid" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" width="100%" height="300"></iframe>
        </div>
    
{include file="common/footer.tpl"}
