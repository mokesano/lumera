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
<section id="contact" class="collection block">
    
    {if not ($currentJournal->getLocalizedSetting('contactTitle') == '' && $currentJournal->getLocalizedSetting('contactAffiliation') == '' && $currentJournal->getLocalizedSetting('contactMailingAddress') == '' && empty($journalSettings.contactPhone) && empty($journalSettings.contactFax) && empty($journalSettings.contactEmail))}
    <section id="principalContact" class="collection">
        <h3>Publishing Contact</h3>
        <p>General questions about the journal, pre-submission queries, editorial policy or procedure, or special issue proposals.</p>
        
        {if !empty($journalSettings.contactName)}
        <section class="collection-data">
        	<h4>{$journalSettings.contactName|escape}</h4>
        </section>
        {/if}
        
    	<section class="collection-data">
        	<p>
        	{assign var=title value=$currentJournal->getLocalizedSetting('contactTitle')}
        	{if $title}{$title|escape}<br />{/if}
        
        	{if !empty($journalSettings.contactPhone)}
        		{translate key="about.contact.phone"}: {$journalSettings.contactPhone|escape}<br />
        	{/if}
        	{if !empty($journalSettings.contactFax)}
        		{translate key="about.contact.fax"}: {$journalSettings.contactFax|escape}<br />
        	{/if}
        	{if !empty($journalSettings.contactEmail)}
        		{translate key="about.contact.email"}: {mailto address=$journalSettings.contactEmail|escape encode="hex"}
        	{/if}</p>
        
        	{assign var=contacInstitution value=$currentJournal->getLocalizedSetting('contactAffiliation')}
        	{if $contacInstitution}<p>{$contacInstitution|escape}</p>{/if}
        
        	{assign var=contactAddres value=$currentJournal->getLocalizedSetting('contactMailingAddress')}
        	{if $contactAddres}<p>{$contactAddres|nl2br}</p>{/if}
        	<br />
    	</section>
    </section>
    {/if}

    {if not (empty($journalSettings.supportName) && empty($journalSettings.supportPhone) && empty($journalSettings.supportEmail))}
    <section id="supportContact" class="collection block">
        <h3>{translate key="about.contact.supportContact"}</h3>
        <p>Questions about manuscripts already sent to production.</p>
        <section class="support-name">
        	{if !empty($journalSettings.supportName)}
        		<h4>{$journalSettings.supportName|escape}</h4>
        	{/if}
        	<section class="article-body block">
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
    <section id="mailingAddress" class="collection">
        <h3>Editorial Office</h3>
        <p>Questions about the suitability of a topic, how to submit, manuscripts under consideration, and the online submission system (if applicable).</p>
    	<section class="collection-data block"><p>{$journalSettings.mailingAddress|nl2br}</p></section>
    </section>
    <br />
    {/if}
    
    {if $sitePrincipalContactName || $sitePrincipalContactEmail}
    <section class="collection block">
    	{if $sitePrincipalContactName}
    	    <h3>{$sitePrincipalContactName|escape} (Customer Service)</h3>
    	{/if}
    	{if $sitePrincipalContactEmail}
    	    <p><a href="mailto:{$sitePrincipalContactEmail|escape}">{$sitePrincipalContactEmail|escape}</a></p>
    	{/if}
    </section>
    {/if}

</section>
{else}
<section class="collection block">
	{if $sitePrincipalContactName}
	    <h3>{$sitePrincipalContactName|escape}</h3>
	{/if}
	{if $siteMailingAddress}
	    <p>{$siteMailingAddress|escape}</p>
	{/if}
	{if $sitePrincipalContactEmail}
	    <p><a href="mailto:{$sitePrincipalContactEmail|escape}">{$sitePrincipalContactEmail|escape}</a></p>
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

