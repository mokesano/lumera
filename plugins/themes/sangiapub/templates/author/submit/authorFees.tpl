{**
 * templates/author/submit/authorFees.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display of author fees and payment information
 *
 *}
<div id="authorFees" class="pay_Submission_Fee">
    
    <h3 class="u-h4">{translate key="payment.authorFees"}</h3>
    
    <div class="alert-text">{translate key="about.authorFeesMessage"}</div>
    
    {if $currentJournal->getSetting('submissionFeeEnabled')}
    <div class="u-mb-16">
    	<p class="u-mb-8">{$currentJournal->getLocalizedSetting('submissionFeeName')|escape}:
    	{if $submissionPayment}
    		{translate key="payment.paid"} {$submissionPayment->getTimestamp()|date_format:$datetimeFormatLong}
    	{else}
    		<span class="bold">{$currentJournal->getSetting('currency')} {$currentJournal->getSetting('submissionFee')|string_format:"%.2f"}</span>
    		{if $showPayLinks}<a class="action" href="{url op="paySubmissionFee" path=$articleId}">{translate key="payment.payNow"}</a>{/if}
    	{/if}
    	</p>
    	<p>{$currentJournal->getLocalizedSetting('submissionFeeDescription')|nl2br}</p>
    </div>
    {/if}
    
    {if $currentJournal->getSetting('fastTrackFeeEnabled')}
    <div class="u-mb-16">
    	<p class="u-mb-8">{$currentJournal->getLocalizedSetting('fastTrackFeeName')|escape}: 
    	{if $fastTrackPayment}
    		{translate key="payment.paid"} {$fastTrackPayment->getTimestamp()|date_format:$datetimeFormatLong}
    	{else}
    		<span class="bold">{$currentJournal->getSetting('currency')} {$currentJournal->getSetting('fastTrackFee')|number_format:2:'.':','}</span>
    		{if $showPayLinks}<a class="action" href="{url op="payFastTrackFee" path=$articleId}">{translate key="payment.payNow"}</a>{/if}
    	{/if}
    	</p>
    	<p>{$currentJournal->getLocalizedSetting('fastTrackFeeDescription')|nl2br}</p>
    </div>
    {/if}
    
    {if $currentJournal->getSetting('publicationFeeEnabled')}
    <div class="u-mb-16">
    	<p class="u-mb-8">{$currentJournal->getLocalizedSetting('publicationFeeName')|escape}: <span class="bold">{$currentJournal->getSetting('currency')} {$currentJournal->getSetting('publicationFee')|number_format:2:'.':','}</span></p>
    	<p>{$currentJournal->getLocalizedSetting('publicationFeeDescription')|nl2br}</p>
    </div>
    {/if}
    
    {if $currentJournal->getLocalizedSetting('waiverPolicy') != ''}
    	<div class="alert-text">{$currentJournal->getLocalizedSetting('waiverPolicy')|nl2br}</div>
    {/if}
    
</div>
