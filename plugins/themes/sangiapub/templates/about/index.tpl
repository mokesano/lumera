{**
 * templates/about/index.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Journal index.
 *
 *}
{strip}
{assign var="pageTitle" value="about.aboutTheJournal"}
{include file="common/header-index.tpl"}
{/strip}

<div class="MainGrid-1563860116">

<div data-section-outline-level="1">
    <div class="meta MetaFlex-1552298965">
        <div data-section-outline-level="1" data-section-layout-level="1"> 
            {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
            {assign var="displayJournalThumbnail" value=$currentJournal->getLocalizedSetting('journalThumbnail')}
            {assign var="coverLocale" value=$currentJournal->getLocalizedSetting($coverLocale)}
            {if $showToc && $currentJournal->getLocalizedSetting($coverLocale)}
            <picture>
                <source srcset="{$publicFilesDir}/{$issue->getLocalizedFileName()|escape:"url"}?as=webp" type="image/webp">
                <img loading="lazy" class="lazyload journal-cover" src="{$publicFilesDir}/{$issue->getLocalizedFileName()|escape:"url"}" alt="journal cover {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{elseif $displayPageHeaderTitle}{$displayPageHeaderTitle}{elseif $siteTitle}{$siteTitle}{else}{$applicationName}{/if}" style="width: 100%; max-width: 230px; display: block;" />
            </picture>
            {elseif $displayHomepageImage && is_array($displayHomepageImage)}
            <picture>
                <source srcset="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp" type="image/webp">
                <img loading="lazy" class="lazyload journal-cover" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" alt="journal cover {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{elseif $displayPageHeaderTitle}{$displayPageHeaderTitle}{elseif $siteTitle}{$siteTitle}{else}{$applicationName}{/if}" style="width: 100%; max-width: 230px; display: block;" />
            </picture>
            {elseif $displayJournalThumbnail && is_array($displayJournalThumbnail)}
            <picture>
                <source srcset="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}?as=webp" type="image/webp">
                <img loading="lazy" class="lazyload journal-cover" src="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}" alt="journal cover {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{elseif $displayPageHeaderTitle}{$displayPageHeaderTitle}{elseif $siteTitle}{$siteTitle}{else}{$applicationName}{/if}" style="width: 100%; max-width: 230px; display: block;" />
            </picture>
            {else}
            <picture>
                <source srcset="/plugins/themes/sangiapub/img/img-default.jpg?as=webp" type="image/webp">
                <img loading="lazy" class="lazyload journal-cover" src="/plugins/themes/sangiapub/img/img-default.jpg" alt="journal cover" style="width: 100%; max-width: 230px; display: block;" />
            </picture>            
            {/if}
        </div>
        <div data-section-outline-level="2" data-section-layout-level="1">
            <h2 class="Type-2932877531">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</h2>
            {assign var=issuePerVolume value=$currentJournal->getSetting('issuePerVolume')}
            {if $alternatePageHeader}
            <h4 data-section-outline-level="3" class="Subline-2415724952" data-section-layout-level="1">International {if $issuePerVolume|escape == 1}annually{elseif $issuePerVolume|escape == 2}semiannually{elseif $issuePerVolume|escape == 3}quarterly{elseif $issuePerVolume|escape == 4}quarterly{elseif $issuePerVolume|escape == 6}bimonthly{elseif $issuePerVolume|escape == 12}monthly{elseif $issuePerVolume|escape >= 12}weekly{/if} journal of science</h4>
            {else}
            <h4 data-section-outline-level="3" class="Subline-2415724952" data-section-layout-level="1">National {if $issuePerVolume|escape == 1}annually{elseif $issuePerVolume|escape == 2}semiannually{elseif $issuePerVolume|escape == 3}quarterly{elseif $issuePerVolume|escape == 4}quarterly{elseif $issuePerVolume|escape == 6}bimonthly{elseif $issuePerVolume|escape == 12}monthly{elseif $issuePerVolume|escape >= 12}weekly{/if} journal of science</h4>
            {/if}
        </div>
        <div data-section-outline-level="2" data-section-layout-level="1">
            <dl class="MetaGrid-2932877531">
                <div data-section-outline-level="3"class="Type-270967514" data-section-layout-level="1">
                    <dt data-section-outline-level="3">
                        <b data-section-outline-level="3">ISSN:</b>
                    </dt>
                    <dd data-section-outline-level="3">
		                {if $onlineIssn} {else if $printIssn}
						{if $currentJournal->getSetting('onlineIssn')}<span class="item">{$currentJournal->getSetting('onlineIssn')} (Online)</span>{else}<span class="item"><i>on proccess</i> (Online)</span>{/if}
						{/if}
						{if $currentJournal->getSetting('printIssn')}
						<br data-section-outline-level="3"><span class="item">{$currentJournal->getSetting('printIssn')} (Print)</span>{/if}
                    </dd>
                </div>
                <div data-section-outline-level="3" class="Type-2225587032" data-section-layout-level="1">
                    <dt data-section-outline-level="3"><b data-section-outline-level="3">{translate key="about.publicationFrequency"}:</b>
                    </dt>
                    {assign var=volumePerYear value=$currentJournal->getSetting('volumePerYear')}
            		{assign var=issuePerVolume value=$currentJournal->getSetting('issuePerVolume')}
            		{assign var=firstYear value=$currentJournal->getSetting('initialYear')}
                    <dd data-section-outline-level="3" data-section-layout-level="1">1 Year Publication with {$issuePerVolume|escape} Issues (online to all articles starting {$firstYear|escape})</dd>
                </div>
                <div data-section-outline-level="3" class="Type-1803463199" data-section-layout-level="1">
                    <dt data-section-outline-level="3" data-section-layout-level="1"><b data-section-outline-level="3" data-section-layout-level="1">{translate key="about.publicationFrequency"}:</b>
                    </dt>
                    <dd data-section-outline-level="3" data-section-layout-level="1">{if $onlineIssn} {else if $printIssn}{if $currentJournal->getSetting('onlineIssn')}Online{else}Online (<i>on proccess</i>){/if}{/if}{if $currentJournal->getSetting('printIssn')} &amp; Print{/if}</dd>
                </div>         
            </dl>
        </div>
    </div>
    <article style="clear: left" data-section-outline-level="2" data-section-layout-level="1">
        <h3 data-section-outline-level="3" class="Type-3185356244" data-section-layout-level="1">Description</h3>
        {if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}
        <p data-section-outline-level="2" class="Type-2678154903" data-section-layout-level="1">{$currentJournal->getLocalizedSetting('focusScopeDesc')|strip_tags|escape}</p>
        {else}
        <p data-section-outline-level="2" class="Type-2678154903" data-section-layout-level="1">{$currentJournal->getLocalizedTitle()|strip_tags|escape} is a NEW journal an bring to index in national and international databases, your published article can be read and cited by researchers worldwide.</p>
        {/if}
    </article>
</div>

<aside role="complementary" data-section-outline-level="2" class="AsideBox-1834393749" data-section-layout-level="2">
    <div data-section-outline-level="2" data-section-layout-level="2">
        <form data-reflect-state="true" method="post" data-section-outline-level="2" class="SubscribeForm-3185356244" data-section-layout-level="2">
            {if $currentJournal->getSetting('publicationFeeName')}
            <details data-section-outline-level="2" class="Details-2932877531" data-section-layout-level="2" open="">
                <summary class="subscription-type NoneDetailsMarker-270967514" data-section-outline-level="2" data-section-layout-level="2">
                    <div data-section-outline-level="2" class="SummaryStyle-2218521826" data-section-layout-level="2"><svg width="14px" height="14px" viewBox="0 0 32 32" class="details-marker Marker-1664207171" data-section-outline-level="2" data-section-layout-level="2"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z" data-section-outline-level="2" data-section-layout-level="2"></path></svg>
                        <span data-section-outline-level="2" class="SubscriptionName-411063136" data-section-layout-level="2">{translate key="payment.type.publication"}</span>
                        <span data-section-outline-level="2" class="SummaryPrice-3689769601" data-section-layout-level="2">{$currentJournal->getSetting('publicationFee')|string_format:"%.2f"|number_format:2:",":"."}&nbsp;{if $currentJournal->getSetting('currency')}{$currentJournal->getSetting('currency')}{else}€{/if}</span>
                    </div>
                </summary>
                <article data-section-outline-level="3" data-section-layout-level="2">
                    <div class="input-field PriceInfoBox-2218521826 checked" style="position: relative" data-section-outline-level="3" data-section-layout-level="3"><input type="radio" id="input-PS-Journal-subscription" class="radio-checked RadioCircle-1664207171" name="subscription-type" value="PS" checked="checked" style="display: none" data-original-index="0" data-section-outline-level="3" data-section-layout-level="4">
                        <label class="price-info Flex-411063136" for="input-PS-Journal-subscription" data-section-outline-level="3" data-section-layout-level="3">
                            <div class="prices-total  TotalPriceRow-3898465696" data-section-outline-level="3" data-section-layout-level="3">
                                <div data-section-outline-level="3" class="Type-3344151041" data-section-layout-level="3">Total Fees</div>
                                <div id="effective-price" data-section-outline-level="3" class="BasketEffectivePriceCell-538306978" data-section-layout-level="3">{math|number_format:2:",":"." equation="x + y" x=$currentJournal->getSetting('publicationFee')|string_format:"%.2f" y=$currentJournal->getSetting('submissionFee')|string_format:"%.2f"}&nbsp;{if $currentJournal->getSetting('currency')}{$currentJournal->getSetting('currency')}{else}€{/if}</div>
                                <div class="single-article BasketRegularPriceCell-3978918244" data-section-outline-level="3" data-section-layout-level="3">only {$currentJournal->getSetting('submissionFee')|string_format:"%.2f"|number_format:2:",":"."}&nbsp;{if $currentJournal->getSetting('currency')}{$currentJournal->getSetting('currency')}{else}€{/if} per article</div>
                            </div>
                        </label>
                    </div>
                    {if $currentJournal->getSetting('currency')}
                    <button id="subscribe-submit-Journal-subscription" type="submit" data-track="cart-add" data-track-product="0" data-section-outline-level="3" class="ButtonPrimary-171795905" data-section-layout-level="3">
                        <span data-section-outline-level="3" class="ButtonLabel-1664207171" data-section-layout-level="3">{translate key="payment.payNow"}</span>
                    </button>
                    {/if}
                    <input type="hidden" id="input-coupon" name="coupon" value="" data-section-outline-level="3" data-section-layout-level="2">
                    <ul class="usps-list ListInset-3866894855" id="usps" data-section-outline-level="4" data-section-layout-level="2">
                        {if $alternatePageHeader}
                        <li data-section-outline-level="4" class="ListElement-1664207171" data-section-layout-level="2">Sinta Score <b>{$alternatePageHeader}</b> of this journal</li>
                        {/if}
                        <li data-section-outline-level="4" class="ListElement-411063136" data-section-layout-level="2">National and international indexed databases</li>
                        {if $currentJournal->getSetting('currency')}
                        <li data-section-outline-level="4" class="ListElement-3689769601" data-section-layout-level="2">Free manuscript checking similarity by <b><a title="iThenticate powered by Turnitin" href="https://crosscheck.ithenticate.com">iThenticate</a></b>, the final result will be sent to authors (<i>by request</i>).</li>
                        {/if}
                        <li data-section-outline-level="4" class="ListElement-4106162086" data-section-layout-level="2">Free article DOI registration.</li>
                        <li data-section-outline-level="4" class="ListElement-3689769601" data-section-layout-level="2">Total Fees is acumulate of {$currentJournal->getLocalizedSetting('submissionFeeName')|escape}, {if $currentJournal->getLocalizedSetting('publicationFeeName')}plus {$currentJournal->getLocalizedSetting('publicationFeeName')|escape}{else}Article Publication{/if}</li>
                    </ul>
                </article>
            </details>
            {/if}
        </form>
    </div>
    <div data-reflect-state="true" data-section-outline-level="2" class="Institutional-269378647" data-section-layout-level="2">
        <details data-section-outline-level="2" class="Details-3185356244" data-section-layout-level="2">
            {if $currentJournal->getSetting('fastTrackFeeEnabled')}
            <summary class="subscription-type NoneDetailsMarker-2932877531" data-section-outline-level="2" data-section-layout-level="2"><div data-section-outline-level="2" class="SummaryStyle-270967514" data-section-layout-level="2"><svg width="14px" height="14px" viewBox="0 0 32 32" class="details-marker Marker-2218521826" data-section-outline-level="2" data-section-layout-level="2"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z" data-section-outline-level="2" data-section-layout-level="2"></path></svg><span data-section-outline-level="2" class="SubscriptionName-171795905" data-section-layout-level="2">Fast Track</span><span data-section-outline-level="2" class="SummaryPrice-4244084256" data-section-layout-level="2">{$currentJournal->getSetting('fastTrackFee')|string_format:"%.2f"|number_format:2:',':'.'}&nbsp;{$currentJournal->getSetting('currency')}</span></div>
            </summary>
            <article data-section-outline-level="3" data-section-layout-level="2">
                <div class="input-field PriceInfoBox-2218521826 checked" style="position: relative" data-section-outline-level="3" data-section-layout-level="3"><input type="radio" id="input-PS-Journal-subscription" class="radio-checked RadioCircle-1664207171" name="subscription-type" value="PS" checked="checked" style="display: none" data-original-index="0" data-section-outline-level="3" data-section-layout-level="4">
                    <label class="price-info Flex-411063136" for="input-PS-Journal-subscription" data-section-outline-level="3" data-section-layout-level="3">
                        <div class="prices-total  TotalPriceRow-3898465696" data-section-outline-level="3" data-section-layout-level="3">
                            <div data-section-outline-level="3" class="Type-3344151041" data-section-layout-level="3">Total Fees</div>
                            <div id="effective-price" data-section-outline-level="3" class="BasketEffectivePriceCell-538306978" data-section-layout-level="3">{math|number_format:2:",":"." equation="x + y + z" x=$currentJournal->getSetting('fastTrackFee')|string_format:"%.2f" y=$currentJournal->getSetting('submissionFee')|string_format:"%.2f" z=$currentJournal->getSetting('submissionFee')|string_format:"%.2f"}&nbsp;{$currentJournal->getSetting('currency')}</div>
                            <div class="single-article BasketRegularPriceCell-3978918244" data-section-outline-level="3" data-section-layout-level="3">only {math|number_format:2:",":"." equation="x - y - z" x=$currentJournal->getSetting('fastTrackFee')|string_format:"%.2f" y=$currentJournal->getSetting('publicationFee')|string_format:"%.2f" z=$currentJournal->getSetting('submissionFee')|string_format:"%.2f"}&nbsp;{$currentJournal->getSetting('currency')} per article</div>
                        </div>
                    </label>
                </div>
                <a href="#" data-section-outline-level="3" class="ButtonLink-2686258585" data-section-layout-level="3"><span data-section-outline-level="3" class="ButtonLabel-2218521826" data-section-layout-level="3">Learn more</span></a>
                <ul class="usps-list ListInset-3866894855" id="usps" data-section-outline-level="4" data-section-layout-level="2">
                    <li data-section-outline-level="4" class="ListElement-1282713543" data-section-layout-level="2">{$currentJournal->getLocalizedSetting('fastTrackFeeName')|escape} coverage for all cost article publication</li>
                    <li data-section-outline-level="4" class="ListElement-4106162086" data-section-layout-level="2">{$currentJournal->getLocalizedSetting('fastTrackFeeDescription')|strip_tags|nl2br}</li>
                    <li data-section-outline-level="4" class="ListElement-3689769601" data-section-layout-level="2">Total Fees is acumulate of {$currentJournal->getLocalizedSetting('submissionFeeName')|escape}, {if $currentJournal->getLocalizedSetting('publicationFeeName')}{$currentJournal->getLocalizedSetting('publicationFeeName')|escape}, {else}Article Publication, {/if} plus {$currentJournal->getLocalizedSetting('fastTrackFeeName')|escape}</li>
                </ul>                    
            </article>
            {else}
            <summary class="subscription-type NoneDetailsMarker-2932877531" data-section-outline-level="2" data-section-layout-level="2"><div data-section-outline-level="2" class="SummaryStyle-270967514" data-section-layout-level="2"><svg width="14px" height="14px" viewBox="0 0 32 32" class="details-marker Marker-2218521826" data-section-outline-level="2" data-section-layout-level="2"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z" data-section-outline-level="2" data-section-layout-level="2"></path></svg><span data-section-outline-level="2" class="SubscriptionName-171795905" data-section-layout-level="2">For Institutions</span><span data-section-outline-level="2" class="SummaryPrice-4244084256" data-section-layout-level="2">on demand</span></div>
            </summary>
            <article data-section-outline-level="3" data-section-layout-level="2">
                <div class="input-field InstitutionalInfoBox-270967514" data-section-outline-level="3" data-section-layout-level="3">Are you interested in an institutional license?</div>
                <a href="#" data-section-outline-level="3" class="ButtonLink-2686258585" data-section-layout-level="3"><span data-section-outline-level="3" class="ButtonLabel-2218521826" data-section-layout-level="3">Learn more</span></a>
            </article>
            {/if}
        </details>
    </div>
    <div data-section-outline-level="2" class="PaymentOption-851350800" data-section-layout-level="2">
        <svg id="visa" width="1000" height="324" viewBox="0 0 1000 323.7" data-section-outline-level="2" class="PaymentOptionLogo-3185356244" data-section-layout-level="2"><g transform="matrix(4.4299631 0 0 4.4299631 -81.165783 -105.04783)" data-section-outline-level="2" data-section-layout-level="2"><polygon class="letter" points="116.1 95.7 97.9 95.7 109.3 25 127.6 25 " fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></polygon><path class="letter" d="m182.4 26.7c-3.6-1.4-9.3-3-16.4-3-18.1 0-30.8 9.6-30.9 23.4-0.1 10.2 9.1 15.8 16 19.2 7.1 3.5 9.5 5.7 9.5 8.8-0.1 4.7-5.7 6.9-11 6.9-7.3 0-11.2-1.1-17.2-3.8l-2.4-1.1-2.6 15.9c4.3 2 12.2 3.7 20.4 3.8 19.2 0 31.7-9.5 31.8-24.2 0.1-8.1-4.8-14.2-15.3-19.3-6.4-3.2-10.3-5.4-10.3-8.7 0.1-3 3.3-6.1 10.5-6.1 5.9-0.2 10.3 1.3 13.6 2.7l1.7 0.8 2.5-15.3 0 0z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><path class="letter" d="m206.7 70.7c1.5-4.1 7.3-19.8 7.3-19.8-0.1 0.2 1.5-4.1 2.4-6.8l1.3 6.1c0 0 3.5 16.9 4.2 20.5-2.9 0-11.6 0-15.2 0l0 0zm22.6-45.7-14.1 0c-4.4 0-7.7 1.3-9.6 5.9l-27.2 64.9 19.2 0c0 0 3.2-8.7 3.8-10.6 2.1 0 20.8 0 23.5 0 0.5 2.5 2.2 10.6 2.2 10.6l16.9 0-14.8-70.7 0 0z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><path class="letter" d="M82.6 25 64.7 73.2 62.7 63.4C59.4 52.2 49 39.9 37.4 33.8l16.4 61.8 19.3 0 28.7-70.6-19.3 0 0 0z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><path class="hook" d="m48 25-29.4 0-0.3 1.4c23 5.9 38.2 20 44.4 37L56.3 30.9c-1.1-4.5-4.3-5.8-8.3-5.9l0 0z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path></g></svg>
        <svg id="mastercard" width="146.8" height="120.41" viewBox="0 0 146.8 120.41" data-section-outline-level="2" class="PaymentOptionLogo-2678154903" data-section-layout-level="2"><path class="middleGray" d="M36.35,105.26v-6a3.56,3.56,0,0,0-3.76-3.8,3.7,3.7,0,0,0-3.36,1.7,3.51,3.51,0,0,0-3.16-1.7,3.16,3.16,0,0,0-2.8,1.42V95.7H21.19v9.56h2.1V100a2.24,2.24,0,0,1,2.34-2.54c1.38,0,2.08.9,2.08,2.52v5.32h2.1V100a2.25,2.25,0,0,1,2.34-2.54c1.42,0,2.1.9,2.1,2.52v5.32ZM67.42,95.7H64V92.8h-2.1v2.9H60v1.9h1.94V102c0,2.22.86,3.54,3.32,3.54a4.88,4.88,0,0,0,2.6-.74l-.6-1.78a3.84,3.84,0,0,1-1.84.54c-1,0-1.38-.64-1.38-1.6V97.6h3.4Zm17.74-.24a2.82,2.82,0,0,0-2.52,1.4V95.7H80.58v9.56h2.08V99.9c0-1.58.68-2.46,2-2.46a3.39,3.39,0,0,1,1.3.24l.64-2a4.45,4.45,0,0,0-1.48-.26Zm-26.82,1a7.15,7.15,0,0,0-3.9-1c-2.42,0-4,1.16-4,3.06,0,1.56,1.16,2.52,3.3,2.82l1,.14c1.14.16,1.68.46,1.68,1,0,.74-.76,1.16-2.18,1.16a5.09,5.09,0,0,1-3.18-1l-1,1.62a6.9,6.9,0,0,0,4.14,1.24c2.76,0,4.36-1.3,4.36-3.12s-1.26-2.56-3.34-2.86l-1-.14c-.9-.12-1.62-.3-1.62-.94s.68-1.12,1.82-1.12a6.16,6.16,0,0,1,3,.82Zm55.71-1a2.82,2.82,0,0,0-2.52,1.4V95.7h-2.06v9.56h2.08V99.9c0-1.58.68-2.46,2-2.46a3.39,3.39,0,0,1,1.3.24l.64-2a4.45,4.45,0,0,0-1.48-.26Zm-26.8,5a4.83,4.83,0,0,0,5.1,5,5,5,0,0,0,3.44-1.14l-1-1.68a4.2,4.2,0,0,1-2.5.86,3.07,3.07,0,0,1,0-6.12,4.2,4.2,0,0,1,2.5.86l1-1.68a5,5,0,0,0-3.44-1.14,4.83,4.83,0,0,0-5.1,5Zm19.48,0V95.7h-2.08v1.16a3.63,3.63,0,0,0-3-1.4,5,5,0,0,0,0,10,3.63,3.63,0,0,0,3-1.4v1.16h2.08Zm-7.74,0a2.89,2.89,0,1,1,2.9,3.06,2.87,2.87,0,0,1-2.9-3.06Zm-25.1-5a5,5,0,0,0,.14,10A5.81,5.81,0,0,0,78,104.16l-1-1.54a4.55,4.55,0,0,1-2.78,1,2.65,2.65,0,0,1-2.86-2.34h7.1c0-.26,0-.52,0-.8,0-3-1.86-5-4.54-5Zm0,1.86a2.37,2.37,0,0,1,2.42,2.32h-5a2.46,2.46,0,0,1,2.54-2.32ZM126,100.48V91.86H124v5a3.63,3.63,0,0,0-3-1.4,5,5,0,0,0,0,10,3.63,3.63,0,0,0,3-1.4v1.16H126Zm3.47,3.39a1,1,0,0,1,.38.07,1,1,0,0,1,.31.2,1,1,0,0,1,.21.3.93.93,0,0,1,0,.74,1,1,0,0,1-.21.3,1,1,0,0,1-.31.2.94.94,0,0,1-.38.08,1,1,0,0,1-.9-.58.94.94,0,0,1,0-.74,1,1,0,0,1,.21-.3,1,1,0,0,1,.31-.2A1,1,0,0,1,129.5,103.87Zm0,1.69a.71.71,0,0,0,.29-.06.75.75,0,0,0,.23-.16.74.74,0,0,0,0-1,.74.74,0,0,0-.23-.16.72.72,0,0,0-.29-.06.75.75,0,0,0-.29.06.73.73,0,0,0-.24.16.74.74,0,0,0,0,1,.74.74,0,0,0,.24.16A.74.74,0,0,0,129.5,105.56Zm.06-1.19a.4.4,0,0,1,.26.08.25.25,0,0,1,.09.21.24.24,0,0,1-.07.18.35.35,0,0,1-.21.09l.29.33h-.23l-.27-.33h-.09v.33h-.19v-.88Zm-.22.17v.24h.22a.21.21,0,0,0,.12,0,.1.1,0,0,0,0-.09.1.1,0,0,0,0-.09.21.21,0,0,0-.12,0Zm-11-4.06a2.89,2.89,0,1,1,2.9,3.06,2.87,2.87,0,0,1-2.9-3.06Zm-70.23,0V95.7H46v1.16a3.63,3.63,0,0,0-3-1.4,5,5,0,0,0,0,10,3.63,3.63,0,0,0,3-1.4v1.16h2.08Zm-7.74,0a2.89,2.89,0,1,1,2.9,3.06A2.87,2.87,0,0,1,40.32,100.48Z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><rect class="darkGray" x="57.65" y="22.85" width="31.5" height="56.61" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></rect><path class="middleGray" d="M59.65,51.16A35.94,35.94,0,0,1,73.4,22.85a36,36,0,1,0,0,56.61A35.94,35.94,0,0,1,59.65,51.16Z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><path class="lightGray" d="M131.65,51.16A36,36,0,0,1,73.4,79.46a36,36,0,0,0,0-56.61,36,36,0,0,1,58.25,28.3Z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><path class="lightGray" d="M128.21,73.46V72.3h.47v-.24h-1.19v.24H128v1.16Zm2.31,0v-1.4h-.36l-.42,1-.42-1H129v1.4h.26V72.41l.39.91h.27l.39-.91v1.06Z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path></svg>
        <svg id="atm-bersama" class="atm-bersama u-hide" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" version="1.1" viewBox="0 0 514 240" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xodm="http://www.corel.com/coreldraw/odm/2003" height="100%" width="20%"><g id="Layer_x0020_1"><path class="fil0" d="M0 240l514 0 0 -240 -514 0 0 240zm470.8 -33.6l-14.41 0 -5.99 13.2 -15.21 0 28.01 -58.8 7.6 0 7.6 0 28.8 58.8 -15.6 0 -6.41 -13.2 -14.39 0zm0 -31.6l-0.4 -0.4 -9.6 22 10 0 9.6 0 -9.6 -21.6zm-464 -14l28.8 0c5.6,0 10,0 12.8,0.4 2.8,0.4 5.2,1.2 7.6,2.8 2,1.2 4,2.8 5.6,4.8 1.2,2 2,4.4 2,6.8 0,2.8 -0.8,5.2 -2.8,7.6 -1.6,2.4 -4,4 -7.2,5.2 4.4,1.2 7.6,2.8 10,5.2 2.4,2.8 3.6,5.6 3.6,9.2 0,2.4 -0.8,5.2 -2.4,7.6 -1.6,2.4 -3.6,4.4 -6,6 -2.8,1.6 -6,2.4 -10,2.8 -2,0.4 -6.4,0.4 -13.2,0.4 -1.2,0 -2.8,0 -4,0l-24.8 0 0 -58.8zm28.8 10c-1.6,0 -3.6,0 -5.6,0l-8.4 0 0 13.6 9.6 0c1.6,0 3.2,0 4.4,0 2.8,-0.4 4.8,-0.4 6,-0.4 2.4,-0.4 4.4,-0.8 6,-2 1.2,-1.2 2,-2.8 2,-4.8 0,-1.6 -0.8,-3.2 -2,-4.4 -1.2,-1.2 -2.8,-1.6 -5.2,-2 -1.2,0 -3.2,0 -6.8,0zm0 23.2c-0.8,0 -1.6,0 -2.4,0l-11.6 0 0 15.6 13.2 0c0.4,0 0.4,0 0.8,0 4.8,0 8,0 9.2,0 2.4,-0.4 4,-1.2 5.2,-2.4 1.6,-1.2 2,-3.2 2,-5.2 0,-1.6 -0.4,-3.2 -1.2,-4.4 -1.2,-1.2 -2.8,-2 -4.8,-2.8 -1.6,-0.4 -5.2,-0.8 -10.4,-0.8zm44 25.6l0 -58.8 53.2 0 0 10 -38.8 0 0 13.2 36 0 0 9.6 -36 0 0 16 40 0 0 10 -54.4 0zm96 -48.8c-0.8,0 -2,0 -3.2,0l-11.2 0 0 14.8 10.4 0c1.6,0 2.8,0 4,0 4.8,0 8,-0.4 9.2,-0.8 1.6,-0.4 3.2,-1.2 4,-2.4 1.2,-1.2 1.6,-2.4 1.6,-4.4 0,-2 -0.8,-3.6 -2,-4.8 -1.2,-1.2 -3.2,-2 -5.6,-2.4 -0.8,0 -3.2,0 -7.2,0zm0 27.6c-1.2,-1.2 -2.8,-2 -4.4,-2.8 -1.6,-0.4 -4,-0.4 -7.2,-0.4l-2.8 0 0 24.4 -14.8 0 0 -58.8 29.2 0 1.6 0c7.6,0 13.2,0.4 16.8,1.6 3.2,1.2 6,2.8 8,5.6 2.4,2.8 3.2,6 3.2,9.2 0,4.4 -1.6,8 -4.8,11.2 -3.2,2.8 -7.6,4.4 -14,5.2 3.2,1.6 5.6,3.2 7.6,5.2 2,1.6 4.8,4.8 8.4,9.6l8.8 11.2 -17.6 0 -10.4 -12.8c-3.6,-4.4 -6,-7.2 -7.6,-8.4zm39.6 2l14.4 -0.8c0.8,3.6 2.4,6.4 5.2,8.4 2.4,1.6 6,2.8 10.4,2.8 4.8,0 8,-0.8 10.4,-2.4 2.4,-1.6 3.6,-3.6 3.6,-6 0,-1.2 -0.4,-2.4 -1.6,-3.2 -0.8,-1.2 -2.4,-2 -5.2,-2.8 -1.6,-0.4 -5.6,-1.2 -11.6,-2.4 -7.6,-1.6 -13.2,-3.6 -16.4,-6 -4.4,-3.2 -6.4,-7.2 -6.4,-11.6 0,-3.2 0.8,-6 3.2,-8.4 2,-2.8 4.8,-4.8 8.8,-6 4,-1.6 8.8,-2.4 14,-2.4 9.2,0 16,2 20.4,5.2 4.8,3.2 7.2,7.6 7.2,12.8l-14.4 0.4c-0.8,-2.8 -2,-5.2 -4,-6.4 -2,-1.2 -5.2,-2 -9.2,-2 -4.4,0 -7.6,0.8 -10,2 -1.6,1.2 -2.4,2.4 -2.4,4 0,1.2 0.8,2.4 2.4,3.2 1.6,1.6 6.4,2.8 13.2,4 7.2,1.6 12.4,2.8 15.6,4.4 3.6,1.6 6,3.6 8,6 2,2.4 2.8,5.6 2.8,9.2 0,3.6 -0.8,6.8 -3.2,9.6 -2.4,2.8 -5.6,5.2 -9.6,6.4 -4.4,1.6 -9.6,2.4 -16,2.4 -8.8,0 -16,-1.6 -20.8,-5.2 -4.8,-3.6 -7.6,-8.4 -8.8,-15.2zm99.2 -25.6l-0.4 -0.4 -9.6 22 10 0 9.6 0 -9.6 -21.6zm0 31.6l-14.4 0 -6 13.2 -15.2 0 28 -58.8 7.6 0 7.6 0 28.8 58.8 -15.6 0 -6.4 -13.2 -14.4 0zm44 13.2l0 -58.8 21.6 0 13.2 40 12.8 -40 21.6 0 0 58.8 -13.2 0 0 -46.4 -14.4 46.4 -14 0 -14 -46.4 0 46.4 -13.6 0zm125.2 -74.8l14.4 0 0 -125.6 -14.8 0 0.4 125.6zm-416.44 -34.06l66.52 -0.33 0 39.06 -15.82 0 0 -21.72 -40.52 0 -13.34 21.85 -23.2 0 25.2 -38.8 1.16 0.03 0 -0.09zm329.64 33.66l77.6 -120.4 -0.4 120.8 -16.4 0 0 -67.2 -42.8 69.6 -18 0 -2 0 2 -2.8zm-319.2 -51.6l-5.6 8.8 5.6 0 28.8 0 27.2 0 0 -82 -9.2 0 -18 28.8 -28.8 44.4zm28.8 -4l-8 0.4 16.8 -26.8 3.2 0 0 26.4 -12 0zm-28.8 -12.8l-48.4 73.6 -13.2 0 0 -9.2 61.6 -94.4 17.2 -26.4 11.6 0 8 0 -8 12.4 -28.8 44zm65.6 73.6l18 0 0 -130 -18.8 0 0.8 130zm73.2 -44.4l-29.6 46 24 0 5.6 -9.2 49.2 -82.8 32.8 0 6.8 0 12.4 -16.8 -19.2 0.4 -42 0.4 -40 62zm0 -16.8l-41.2 62.8 -10 0 0 -10.8 51.2 -80.4 0.8 -1.2 -0.8 0 -48.8 0 -1.2 -4.8 6 -10.4 44 -0.4 29.2 0 -29.2 45.2zm0 -54l-38.4 0 8.4 -14.8 30 0 82 0 36 0 -10.8 14.4 -25.2 0 -82 0.4zm82 116l-12 0 12 -18.4 68.4 -106 4 -6.4 2 0 0 90.8 -6 9.2 -12 18.8 0 -61.6 -47.2 73.6 -9.2 0zm0 -63.2l-36 54.4 0.4 8.8 12 0 23.6 -35.6 62 -95.2 -17.6 0 -44.4 67.6zm68.4 50.4l-6.4 9.6 6.4 0 16.8 0 6.8 -10.8 6.4 -9.6 70.4 -107.6 -20.8 0 -47.6 74 0 -5.2 -2 2.8 -6.4 9.6 -23.6 37.2zm23.6 -54.4l-7.6 12 -0.4 -75.6 8 0 6.4 0 2 0 0 51.2 -2 3.2 -6.4 9.2z"></path><path class="fil1" d="M470.8 206.4l-14.41 0 -5.99 13.2 -15.21 0 28.01 -58.8 7.6 0 7.6 0 28.8 58.8 -15.6 0 -6.41 -13.2 -14.39 0zm0 -31.6l-0.4 -0.4 -9.6 22 10 0 9.6 0 -9.6 -21.6z"></path><path class="fil2" d="M483.6 144.8l14.4 0 0 -125.6 -14.8 0 0.4 125.6zm-416.44 -34.06l66.52 -0.33 0 39.06 -15.82 0 0 -21.72 -40.52 0 -13.34 21.85 -23.2 0 25.2 -38.8 1.16 0.03 0 -0.09zm329.64 33.66l77.6 -120.4 -0.4 120.8 -16.4 0 0 -67.2 -42.8 69.6 -18 0 -2 0 2 -2.8zm-319.2 -51.6l-5.6 8.8 5.6 0 28.8 0 27.2 0 0 -82 -9.2 0 -18 28.8 -28.8 44.4zm28.8 -4l-8 0.4 16.8 -26.8 3.2 0 0 26.4 -12 0zm-28.8 -12.8l-48.4 73.6 -13.2 0 0 -9.2 61.6 -94.4 17.2 -26.4 11.6 0 8 0 -8 12.4 -28.8 44zm65.6 73.6l18 0 0 -130 -18.8 0 0.8 130zm73.2 -44.4l-29.6 46 24 0 5.6 -9.2 49.2 -82.8 32.8 0 6.8 0 12.4 -16.8 -19.2 0.4 -42 0.4 -40 62zm0 -16.8l-41.2 62.8 -10 0 0 -10.8 51.2 -80.4 0.8 -1.2 -0.8 0 -48.8 0 -1.2 -4.8 6 -10.4 44 -0.4 29.2 0 -29.2 45.2zm0 -54l-38.4 0 8.4 -14.8 30 0 82 0 36 0 -10.8 14.4 -25.2 0 -82 0.4zm82 116l-12 0 12 -18.4 68.4 -106 4 -6.4 2 0 0 90.8 -6 9.2 -12 18.8 0 -61.6 -47.2 73.6 -9.2 0zm0 -63.2l-36 54.4 0.4 8.8 12 0 23.6 -35.6 62 -95.2 -17.6 0 -44.4 67.6zm68.4 50.4l-6.4 9.6 6.4 0 16.8 0 6.8 -10.8 6.4 -9.6 70.4 -107.6 -20.8 0 -47.6 74 0 -5.2 -2 2.8 -6.4 9.6 -23.6 37.2zm23.6 -54.4l-7.6 12 -0.4 -75.6 8 0 6.4 0 2 0 0 51.2 -2 3.2 -6.4 9.2z"></path><polygon class="fil2" points="358.4,219.6 358.4,160.8 380,160.8 393.2,200.8 406,160.8 427.6,160.8 427.6,219.6 414.4,219.6 414.4,173.2 400,219.6 386,219.6 372,173.2 372,219.6 "></polygon><path class="fil2" d="M314.4 206.4l-14.4 0 -6 13.2 -15.2 0 28 -58.8 7.6 0 7.6 0 28.8 58.8 -15.6 0 -6.4 -13.2 -14.4 0zm0 -31.6l-0.4 -0.4 -9.6 22 10 0 9.6 0 -9.6 -21.6z"></path><path class="fil2" d="M215.2 200.4l14.4 -0.8c0.8,3.6 2.4,6.4 5.2,8.4 2.4,1.6 6,2.8 10.4,2.8 4.8,0 8,-0.8 10.4,-2.4 2.4,-1.6 3.6,-3.6 3.6,-6 0,-1.2 -0.4,-2.4 -1.6,-3.2 -0.8,-1.2 -2.4,-2 -5.2,-2.8 -1.6,-0.4 -5.6,-1.2 -11.6,-2.4 -7.6,-1.6 -13.2,-3.6 -16.4,-6 -4.4,-3.2 -6.4,-7.2 -6.4,-11.6 0,-3.2 0.8,-6 3.2,-8.4 2,-2.8 4.8,-4.8 8.8,-6 4,-1.6 8.8,-2.4 14,-2.4 9.2,0 16,2 20.4,5.2 4.8,3.2 7.2,7.6 7.2,12.8l-14.4 0.4c-0.8,-2.8 -2,-5.2 -4,-6.4 -2,-1.2 -5.2,-2 -9.2,-2 -4.4,0 -7.6,0.8 -10,2 -1.6,1.2 -2.4,2.4 -2.4,4 0,1.2 0.8,2.4 2.4,3.2 1.6,1.6 6.4,2.8 13.2,4 7.2,1.6 12.4,2.8 15.6,4.4 3.6,1.6 6,3.6 8,6 2,2.4 2.8,5.6 2.8,9.2 0,3.6 -0.8,6.8 -3.2,9.6 -2.4,2.8 -5.6,5.2 -9.6,6.4 -4.4,1.6 -9.6,2.4 -16,2.4 -8.8,0 -16,-1.6 -20.8,-5.2 -4.8,-3.6 -7.6,-8.4 -8.8,-15.2z"></path><path class="fil2" d="M175.6 198.4c-1.2,-1.2 -2.8,-2 -4.4,-2.8 -1.6,-0.4 -4,-0.4 -7.2,-0.4l-2.8 0 0 24.4 -14.8 0 0 -58.8 29.2 0 1.6 0c7.6,0 13.2,0.4 16.8,1.6 3.2,1.2 6,2.8 8,5.6 2.4,2.8 3.2,6 3.2,9.2 0,4.4 -1.6,8 -4.8,11.2 -3.2,2.8 -7.6,4.4 -14,5.2 3.2,1.6 5.6,3.2 7.6,5.2 2,1.6 4.8,4.8 8.4,9.6l8.8 11.2 -17.6 0 -10.4 -12.8c-3.6,-4.4 -6,-7.2 -7.6,-8.4zm0 -27.6c-0.8,0 -2,0 -3.2,0l-11.2 0 0 14.8 10.4 0c1.6,0 2.8,0 4,0 4.8,0 8,-0.4 9.2,-0.8 1.6,-0.4 3.2,-1.2 4,-2.4 1.2,-1.2 1.6,-2.4 1.6,-4.4 0,-2 -0.8,-3.6 -2,-4.8 -1.2,-1.2 -3.2,-2 -5.6,-2.4 -0.8,0 -3.2,0 -7.2,0z"></path><polygon class="fil2" points="79.6,219.6 79.6,160.8 132.8,160.8 132.8,170.8 94,170.8 94,184 130,184 130,193.6 94,193.6 94,209.6 134,209.6 134,219.6 "></polygon><path class="fil2" d="M6.8 160.8l28.8 0c5.6,0 10,0 12.8,0.4 2.8,0.4 5.2,1.2 7.6,2.8 2,1.2 4,2.8 5.6,4.8 1.2,2 2,4.4 2,6.8 0,2.8 -0.8,5.2 -2.8,7.6 -1.6,2.4 -4,4 -7.2,5.2 4.4,1.2 7.6,2.8 10,5.2 2.4,2.8 3.6,5.6 3.6,9.2 0,2.4 -0.8,5.2 -2.4,7.6 -1.6,2.4 -3.6,4.4 -6,6 -2.8,1.6 -6,2.4 -10,2.8 -2,0.4 -6.4,0.4 -13.2,0.4 -1.2,0 -2.8,0 -4,0l-24.8 0 0 -58.8zm28.8 10c-1.6,0 -3.6,0 -5.6,0l-8.4 0 0 13.6 9.6 0c1.6,0 3.2,0 4.4,0 2.8,-0.4 4.8,-0.4 6,-0.4 2.4,-0.4 4.4,-0.8 6,-2 1.2,-1.2 2,-2.8 2,-4.8 0,-1.6 -0.8,-3.2 -2,-4.4 -1.2,-1.2 -2.8,-1.6 -5.2,-2 -1.2,0 -3.2,0 -6.8,0zm0 23.2c-0.8,0 -1.6,0 -2.4,0l-11.6 0 0 15.6 13.2 0c0.4,0 0.4,0 0.8,0 4.8,0 8,0 9.2,0 2.4,-0.4 4,-1.2 5.2,-2.4 1.6,-1.2 2,-3.2 2,-5.2 0,-1.6 -0.4,-3.2 -1.2,-4.4 -1.2,-1.2 -2.8,-2 -4.8,-2.8 -1.6,-0.4 -5.2,-0.8 -10.4,-0.8z"></path></g></svg>
        <svg id="qris" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" viewBox="0 0 21000 7750" width="60" height="40%">
            <g id="__x0023_Layer_x0020_1"><path d="M20140 4750l0 -667 0 -1333 -2000 0 -1333 0 0 -667 3333 0 0 -1333 -3333 0 -2000 0 0 1333 0 667 0 1333 2000 0 1333 0 0 667 -3333 0 0 1333 3333 0 2000 0 0 -1333zm527 -417l0 2167c0,44 -18,87 -49,118 -31,31 -74,49 -118,49l-2167 0 0 333 2500 0c44,0 87,-18 118,-49 31,-31 49,-74 49,-118l0 -2500 -333 0zm-18000 -4333l-2500 0c-44,0 -87,18 -118,49 -31,31 -49,74 -49,118l0 2500 333 0 0 -2167c0,-44 18,-87 49,-118 31,-31 74,-49 118,-49l2167 0 0 -333zm2140 7750l1333 0 0 -3000 -1333 0 0 3000zm1167 -7000l-3167 0 0 1333 2000 0 0 2000 1333 0 0 -3167c0,-44 -18,-87 -49,-118 -31,-31 -74,-49 -118,-49zm-3833 0l-1167 0c-44,0 -87,18 -118,49 -31,31 -49,74 -49,118l0 5000c0,44 18,87 49,118 31,31 74,49 118,49l3167 0 0 -1333 -2000 0 0 -4000zm667 3333l1333 0 0 -1333 -1333 0 0 1333zm333 -1000l0 0 667 0 0 667 -667 0 0 -667zm3667 -2333l0 1333 4000 0 0 667 -2667 0 -1333 0 0 1333 0 2000 1333 0 0 -1980 2000 1980 2000 0 -2087 -2000 753 0 1333 0 0 -1333 0 -667 0 -1333 -1333 0 -4000 0zm6000 5333l1333 0 0 -5333 -1333 0 0 5333z" class="middleGray"></path></g>
        </svg>
        <svg id="gpn" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3174.803 4025.197" version="1.1" width="30" height="40">
          <path id="path2" d="m 2202.3587,2954.991 v 929.5491 c 0,136.2299 121.555,129.992 245.409,117.6277 5.5557,-0.3341 6.4178,-1.448 6.5136,-6.1264 0,-2.6734 5.843,-614.6497 5.843,-629.5759 35.4416,34.0853 307.6713,427.6259 364.5694,505.3761 108.3362,147.8145 71.5536,133.5566 296.9429,133.5566 39.3689,0 40.8057,-10.8049 40.8057,-48.7888 v -929.5491 c 0,-34.0853 -7.2799,-59.3708 -20.2112,-77.6388 -42.5299,-60.1505 -154.0272,-46.2268 -213.2243,-41.3256 -7.2798,0.6683 -17.7207,6.9061 -18.2955,14.4807 0,70.287 0.479,550.9346 0,655.0842 -15.9008,-12.0301 -57.9517,-67.2795 -71.9368,-87.3297 -121.7466,-175.4392 -248.9531,-364.3565 -377.5965,-530.1047 -54.4076,-70.0643 -99.8111,-54.0242 -217.9178,-54.0242 -40.1352,0 -40.9015,9.691 -40.9015,48.7888 z" class="middleGray"></path>
          <path id="path4" d="m 1398.9843,3990.2491 v -1.1139 l 5.3641,-321.0258 308.0544,-3.0075 c 287.7473,3.3417 423.3832,-357.6731 282.6705,-598.7209 -52.5876,-90.1145 -143.3947,-47.8977 -307.6712,-12.2529 -81.803,17.8224 -139.0842,14.1465 -138.3179,55.3608 68.2011,23.5032 131.4212,-16.3744 194.0666,43.3307 43.0088,40.8801 63.7948,117.8505 43.0088,195.0437 -40.3268,149.5967 -227.4966,113.8406 -381.7154,113.8406 0,-400.6697 40.5184,-356.4478 -146.2683,-318.6867 -153.1651,30.9664 -110.5394,27.402 -110.5394,410.0264 0,111.2786 -3.64,228.5722 -0.096,339.071 4.1189,131.6629 115.5204,113.2836 229.9871,113.2836 0.3831,0 1.2452,0 1.7242,0 16.3797,0.2227 19.7323,-2.562 19.7323,-15.1491 z" class="middleGray"></path>
          <path id="path6" d="m 305.2763,3272.4524 8.621,-15.5946 c 26.8206,-81.4261 154.0271,-140.1286 230.2744,-186.3554 42.051,-25.5083 235.8302,-135.116 256.9036,-159.7332 -238.0333,-51.6849 -453.748,-18.0452 -608.6373,103.1471 -129.0265,100.9193 -204.5075,330.0484 -172.8974,566.8634 29.2153,219.4382 156.4219,357.4503 343.4001,413.3681 172.8975,51.6849 508.8262,29.8525 627.8907,-73.5174 l 3.3526,-302.6465 c 0,-96.2409 17.9124,-213.8686 -77.1094,-237.3719 -102.3016,-25.2855 -291.8662,75.6338 -411.2181,119.8556 -31.8974,11.8073 -36.8784,7.2403 -34.2921,45.8926 l 301.6366,4.9012 0.1915,160.5129 c 1.2453,77.8615 -1.5326,67.0567 -84.1977,81.3146 -97.3206,16.7085 -210.7337,11.0276 -287.8431,-32.4144 -134.5822,-75.8566 -165.1386,-312.8944 -96.0754,-488.2221 z" class="middleGray"></path>
          <path id="path8" d="m 2620.8567,2159.7782 112.7425,15.3719 c -5.843,5.6808 -0.8621,13.2554 -43.4878,18.4907 -24.1385,3.0075 -29.79,2.005 -52.875,-1.4481 z m -1819.7814,750.991 c -21.0734,24.6172 -214.8526,134.2249 -256.9036,159.7332 -76.2473,46.2268 -203.4538,104.9293 -230.2744,186.3554 l -4.3105,7.7973 8.7167,-2.3392 c 48.2772,-8.2429 188.5109,-72.5149 242.9185,-95.684 81.0367,-34.4195 196.5571,-98.3573 251.4436,-67.5023 -33.6216,47.6749 -278.36,94.4587 -277.4979,201.7272 71.4578,-8.6884 1180.9708,-356.225 1290.9355,-388.4167 110.6352,-32.3031 211.3085,-60.8189 302.5945,-115.2886 61.6875,-36.7587 407.2908,-313.8969 471.373,-364.1337 45.2119,-35.5334 64.8485,-58.1456 140.1379,-68.9504 72.7989,-10.3593 128.2602,-3.6759 143.3947,-73.8515 -54.7908,23.726 -65.7106,31.5233 -113.2215,-4.6784 16.0924,-28.5158 56.036,-39.7662 97.4165,-33.9739 68.0095,9.3567 54.9823,38.0953 87.6461,58.2569 14.7513,-149.5967 -133.6244,-134.1135 -157.4756,-148.1486 -16.5713,-9.8023 -29.5985,-36.5359 -55.0781,-52.7989 -43.9668,-28.1816 -119.3519,-14.8148 -175.388,-13.2554 -72.4157,1.8937 -128.1644,-11.139 -194.9286,-12.1415 9.3872,33.0828 27.2996,39.3207 45.4035,71.6238 -21.4565,13.144 -31.5143,11.4731 -63.9864,16.7085 -22.7976,3.6758 -48.5646,8.7998 -64.5612,15.3718 -6.2262,44.3332 62.3581,80.4235 30.748,145.4752 -23.2765,-14.0351 -688.9077,-868.3959 -736.3228,-929.9946 -43.0088,-55.9177 -81.4198,-100.9193 -124.1413,-156.837 -118.9687,-155.5004 -208.7221,-219.9951 -340.5265,-350.8783 -25.1923,-24.9514 -44.4457,-50.1255 -70.0211,-73.9629 L 621.8558,405.4981 C 571.9503,357.7118 527.313,316.6089 477.9822,268.4885 L 267.7274,60.969 C 239.9489,34.124 207.7641,-8.3155 190.8096,7.6132 c -18.8702,33.5284 -11.7819,219.5496 2.8736,264.1056 25.4797,77.7501 82.3778,117.1822 127.2066,161.1812 97.7996,95.9068 190.4266,184.9073 287.9389,281.0368 135.0611,133.111 289.1841,293.067 427.981,421.9451 32.7595,30.4095 120.1182,99.2485 102.4932,180.8973 -24.5217,-8.1315 -359.8757,-342.5241 -415.5286,-393.7635 L 364.9523,569.0185 c -20.4029,-18.9363 -31.6101,-42.7737 -65.7106,-42.8851 -17.7208,30.2981 -11.0156,201.1702 -0.8621,239.377 19.9239,75.2996 69.159,110.3874 109.1984,149.8194 l 517.447,505.3762 c 45.5951,43.7762 79.8873,76.1907 125.4824,126.6504 36.3036,40.2117 102.2058,75.1882 90.3281,167.6418 -38.6984,-9.2453 -140.0421,-129.5465 -182.2847,-169.3127 L 500.1092,1103.8016 c -101.4395,-99.694 -101.3437,-111.9469 -101.3437,28.0703 0,123.5314 -0.7663,150.1536 93.2975,246.6173 149.3336,153.1612 288.1305,277.9179 458.5374,449.0129 104.8879,105.2634 191.6719,151.9358 192.534,265.8877 -33.43,-10.9162 -408.0571,-386.6344 -465.4341,-439.5447 -55.5571,-51.3507 -121.3635,-140.0171 -162.4566,-142.2449 -20.4028,35.5334 -34.8668,214.3142 65.9022,310.8893 125.9613,120.7467 273.0918,274.5762 400.2025,392.0926 34.8668,32.3031 65.6148,61.71 99.3322,95.2384 32.7595,32.5259 64.4654,69.8415 61.6875,142.0222 -34.7711,-6.9062 -93.3933,-81.0919 -130.0802,-116.8481 -44.4456,-43.2193 -84.1019,-79.978 -129.5054,-122.6403 L 694.3674,2026.3331 c -22.2228,-20.7185 -41.572,-54.8039 -76.822,-49.9027 -18.8703,90.1145 -4.981,205.0689 45.5951,261.2094 25.767,28.6272 52.2045,50.6824 80.1746,78.3071 l 239.087,236.3695 c 72.3199,68.9504 83.0482,95.4612 147.6094,126.3162 24.3301,11.6959 11.2072,-0.7797 24.2343,16.9313 z" class="middleGray"></path>
          <path id="path10" d="m 2286.4606,1407.562 -6.8967,-8.577 c -31.7059,-51.3508 -143.5863,-450.5723 -180.3689,-479.5337 -36.4953,15.4832 -83.2399,139.683 -88.125,176.3302 -21.4566,159.956 227.4966,531.3301 124.1413,620.6648 l -82.7609,-213.5345 c -16.4756,-46.7838 -55.2697,-189.4743 -82.8567,-210.6384 -28.5448,9.0226 -101.2479,146.4778 -88.8913,236.1467 5.7473,41.6598 67.0516,214.7598 87.9334,245.6148 40.3268,59.5936 97.5123,127.3187 142.3411,182.0111 l 107.9531,136.8983 c 16.5714,21.4982 18.8703,34.0853 46.3614,39.0979 l 271.0802,-548.2613 c 36.3995,-87.4411 10.9199,-146.4778 -16.3797,-224.5621 L 2309.1624,748.5791 c -21.0734,-60.3733 -46.1699,-148.26 -70.6916,-200.9474 -37.2616,5.9036 -31.8974,20.6071 -63.6991,83.431 -17.2418,33.9739 -33.2384,63.9378 -35.4415,111.5013 -3.3526,71.1782 106.9952,356.2251 136.3063,443.332 20.3071,60.3734 70.3085,165.1913 10.8241,221.666 z" class="lightGray"></path>
        </svg>
        <svg id="paypal" class="payPal PaymentOptionLogo-1121884433" xmlns:xlink="http://www.w3.org/1999/xlink" width="650" height="595" viewBox="90 0 650 595" data-section-outline-level="2" data-section-layout-level="2"><path class="darkGray" fill="inherit" d="M543.3 48.5c-16.7-19-46.9-27.2-85.5-27.2H345.7c-7.9 0-14.6 5.7-15.9 13.5l-46.7 296c-0.9 5.8 3.6 11.1 9.5 11.1h69.2l17.4-110.2 -0.5 3.5c1.2-7.8 7.9-13.5 15.8-13.5h32.9c64.6 0 115.2-26.2 130-102.2 0.4-2.2 0.8-4.4 1.1-6.6 -1.9-1-1.9-1 0 0C562.9 84.9 558.5 65.8 543.3 48.5" data-section-outline-level="2" data-section-layout-level="2"></path><path class="darkGray" fill="inherit" d="M543.3 48.5c-16.7-19-46.9-27.2-85.5-27.2H345.7c-7.9 0-14.6 5.7-15.9 13.5l-46.7 296c-0.9 5.8 3.6 11.1 9.5 11.1h69.2l17.4-110.2 -0.5 3.5c1.2-7.8 7.9-13.5 15.8-13.5h32.9c64.6 0 115.2-26.2 130-102.2 0.4-2.2 0.8-4.4 1.1-6.6 -1.9-1-1.9-1 0 0C562.9 84.9 558.5 65.8 543.3 48.5" data-section-outline-level="2" data-section-layout-level="2"></path><path class="darkGray" fill="inherit" d="M396.9 113.3c0.7-4.7 3.8-8.5 7.8-10.5 1.8-0.9 3.9-1.4 6.1-1.4h87.9c10.4 0 20.1 0.7 29 2.1 2.5 0.4 5 0.9 7.4 1.4 2.4 0.5 4.7 1.1 7 1.8 1.1 0.3 2.2 0.7 3.3 1 4.4 1.5 8.4 3.2 12.2 5.1 4.4-28.1 0-47.2-15.2-64.4 -16.7-19-46.9-27.2-85.5-27.2H344.7c-7.9 0-14.6 5.7-15.8 13.5l-46.7 296c-0.9 5.8 3.6 11.1 9.5 11.1h69.2l17.4-110.2L396.9 113.3 396.9 113.3z" data-section-outline-level="2" data-section-layout-level="2"></path><path class="lightGray" fill="inherit" d="M557.5 112.9L557.5 112.9c-0.3 2.1-0.7 4.3-1.1 6.6 -14.8 75.9-65.4 102.2-130 102.2h-32.9c-7.9 0-14.6 5.7-15.8 13.5L360.9 342l-4.8 30.3c-0.8 5.1 3.1 9.7 8.3 9.7h58.3c6.9 0 12.8-5 13.9-11.8l0.6-3 11-69.7 0.7-3.9c1.1-6.8 7-11.8 13.9-11.8h8.7c56.5 0 100.8-23 113.7-89.4 5.4-27.7 2.6-50.9-11.7-67.2C569.2 120.3 563.8 116.2 557.5 112.9" data-section-outline-level="2" data-section-layout-level="2"></path><path class="middleGray" fill="inherit" d="M543.1 106.8c-2.3-0.7-4.6-1.3-7-1.8 -2.4-0.5-4.9-1-7.4-1.4 -8.9-1.4-18.6-2.1-29-2.1h-87.9c-2.2 0-4.2 0.5-6.1 1.4 -4.1 1.9-7.1 5.8-7.8 10.5l-18.7 118.4 -0.5 3.5c1.2-7.8 7.9-13.5 15.8-13.5h32.9c64.6 0 115.2-26.2 130-102.2 0.4-2.2 0.8-4.4 1.1-6.6 -3.7-2-7.8-3.7-12.2-5.1C545.3 107.4 544.2 107.1 543.1 106.8" data-section-outline-level="2" data-section-layout-level="2"></path><path class="darkGray" fill="inherit" d="M447.9 464.1h-21c-2 0-3.9 1-5 2.7l-29 42.7 -12.3-41c-0.8-2.6-3.1-4.3-5.8-4.3h-20.6c-2.5 0-4.2 2.5-3.4 4.8l23.1 67.9 -21.8 30.7c-1.7 2.4 0 5.7 3 5.7h21c2 0 3.8-1 5-2.6l69.9-100.8C452.6 467.4 450.9 464.1 447.9 464.1M307.5 504.8c-2 11.9-11.5 20-23.6 20 -6.1 0-10.9-2-14-5.6 -3.1-3.7-4.3-8.9-3.3-14.7 1.9-11.8 11.5-20.1 23.4-20.1 5.9 0 10.8 2 13.9 5.7C307.1 493.8 308.4 499 307.5 504.8M336.6 464.1h-20.9c-1.8 0-3.3 1.3-3.6 3.1l-0.9 5.8 -1.5-2.1c-4.5-6.6-14.6-8.8-24.7-8.8 -23.1 0-42.8 17.5-46.7 42 -2 12.2 0.8 23.9 7.8 32.1 6.4 7.5 15.5 10.6 26.3 10.6 18.6 0 28.9-12 28.9-12l-0.9 5.8c-0.3 2.2 1.4 4.2 3.6 4.2h18.8c3 0 5.5-2.2 6-5.1l11.3-71.5C340.5 466.1 338.8 464.1 336.6 464.1M211.2 464.6c-2.4 15.7-14.4 15.7-25.9 15.7h-6.6l4.6-29.3c0.3-1.8 1.8-3.1 3.6-3.1h3c7.9 0 15.3 0 19.2 4.5C211.3 455.1 212 459.1 211.2 464.6M206.1 423.7h-43.6c-3 0-5.5 2.2-6 5.1l-17.6 111.9c-0.3 2.2 1.4 4.2 3.6 4.2h20.8c3 0 5.5-2.2 6-5.1l4.8-30.2c0.5-2.9 3-5.1 6-5.1h13.8c28.8 0 45.3-13.9 49.7-41.5 2-12.1 0.1-21.5-5.6-28.2C231.7 427.6 220.7 423.7 206.1 423.7" data-section-outline-level="2" data-section-layout-level="2"></path><path class="lightGray" fill="inherit" d="M672.5 426.8l-17.9 114c-0.3 2.2 1.4 4.2 3.6 4.2h18c3 0 5.5-2.2 6-5.1L699.9 428c0.3-2.2-1.4-4.2-3.6-4.2h-20.2C674.3 423.8 672.8 425.1 672.5 426.8M618.8 504.8c-2 11.9-11.5 20-23.6 20 -6.1 0-10.9-2-14-5.6 -3.1-3.7-4.3-8.9-3.3-14.7 1.9-11.8 11.5-20.1 23.4-20.1 5.9 0 10.8 2 13.9 5.7C618.4 493.8 619.7 499 618.8 504.8M647.9 464.1H627c-1.8 0-3.3 1.3-3.6 3.1l-0.9 5.8 -1.5-2.1c-4.5-6.6-14.6-8.8-24.7-8.8 -23.1 0-42.8 17.5-46.7 42 -2 12.2 0.8 23.9 7.8 32.1 6.4 7.5 15.5 10.6 26.3 10.6 18.6 0 28.9-12 28.9-12l-0.9 5.8c-0.3 2.2 1.4 4.2 3.6 4.2h18.8c3 0 5.5-2.2 6-5.1l11.3-71.5C651.9 466.1 650.1 464.1 647.9 464.1M522.5 464.6c-2.4 15.7-14.4 15.7-25.9 15.7H490l4.6-29.3c0.3-1.8 1.8-3.1 3.6-3.1h3c7.9 0 15.3 0 19.2 4.5C522.7 455.1 523.4 459.1 522.5 464.6M517.5 423.7h-43.6c-3 0-5.5 2.2-6 5.1l-17.6 111.9c-0.3 2.2 1.4 4.2 3.6 4.2h22.4c2.1 0 3.9-1.5 4.2-3.6l5-31.7c0.5-2.9 3-5.1 6-5.1h13.8c28.8 0 45.3-13.9 49.7-41.5 2-12.1 0.1-21.5-5.6-28.2C543.1 427.6 532.1 423.7 517.5 423.7" data-section-outline-level="2" data-section-layout-level="2"></path></svg>
    </div>
    <div id="vat-description" data-section-outline-level="2" class="VatDescription-3323122897" data-section-layout-level="2">All prices are NET prices.<br> VAT will be added later in the checkout.<br>Tax calculation will be finalised during checkout.</div>
</aside>

</div>
</div>

{assign var="journalDescription" value=$currentJournal->getLocalizedDescription()}
{assign var="additionalHomeContent" value=$currentJournal->getLocalizedSetting('additionalHomeContent')}
{if $currentJournal->getLocalizedDescription() || $currentJournal->getLocalizedSetting('additionalHomeContent')}
<article style="clear:left" data-section-outline-level="2" data-section-layout-level="1" class="descript u-mt-32">
    <h3 data-section-outline-level="3" class="u-hide Type-3185356244 u-mb-16" data-section-layout-level="1">Description</h3>
    <p data-section-outline-level="2" class="Type-2678154903" data-section-layout-level="1">{$currentJournal->getLocalizedDescription()|nl2br}</p>
    {if $currentJournal->getLocalizedSetting('additionalHomeContent')}
    <p data-section-outline-level="2" class="Type-2678154903" data-section-layout-level="1">{$currentJournal->getLocalizedSetting('additionalHomeContent')|nl2br}</p>
    {/if}
</article>
{/if}

</div>
</div>

<section class="SubscriberFooter-1037694867">
    <div class="GridLiveArea-2081012695">
        <section data-section-outline-level="3" data-section-layout-level="2">
            <h3 class="Type-1552298965">{translate key="navigation.about"} {translate key="about.people"}</h3>
            <ul class="AboutLink">
            	<li id="editorialTeamLink" class="Link-2407621270"><a href="{url op="editorialTeam"}">{translate key="about.editorialTeam"}</a></li>
            	{if $peopleGroups}
            		{iterate from=peopleGroups item=peopleGroup}
            		<li id="editorialTeamLink" class="Link-2407621270"><a href="{url op="displayMembership" path=$peopleGroup->getId()}">{$peopleGroup->getLocalizedTitle()|escape}</a></li>
            		{/iterate}
            	{/if}
            	{call_hook name="Templates::About::Index::People"}
            	{if not (empty($journalSettings.mailingAddress) && empty($journalSettings.contactName) && empty($journalSettings.contactAffiliation) && empty($journalSettings.contactMailingAddress) && empty($journalSettings.contactPhone) && empty($journalSettings.contactFax) && empty($journalSettings.contactEmail) && empty($journalSettings.supportName) && empty($journalSettings.supportPhone) && empty($journalSettings.supportEmail))}
            		<li class="Link-2407621270"><a href="{url op="contact"}">{translate key="about.contact.supportContact"}</a></li>
            	{/if}
        	</ul>
        	<h3 class="Type-1552298965">{translate key="about.contact.supportContact"}</h3>
        	<p class="FooterLink-2407621270">{translate key="user.email"}: <a href="mailto:journals@sangia.org" data-section-outline-level="3" data-section-layout-level="2">journals@sangia.org</a></p>
        	<p class="Type-269378647">{translate key="user.phone"}: +6285343880383</p>
            <p data-section-outline-level="3" class="Type-851350800" data-section-layout-level="2">Hours: 24 hours a day, 7 days a week</p>
        </section>
        <section data-section-outline-level="3" data-section-layout-level="2">
            <h3 data-section-outline-level="3" class="Type-1552298965" data-section-layout-level="2">{translate key="about.editorialPolicies"}</h3>
            <ul class="Link">
            	{if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}<li class="Link-2407621270"><a href="{url op="editorialPolicies" anchor="focusAndScope"}">{translate key="about.focusAndScope"}</a></li>{/if}
            	<li class="Link-2407621270"><a href="{url op="editorialPolicies" anchor="sectionPolicies"}">{translate key="about.sectionPolicies"}</a></li>
            	{if $currentJournal->getLocalizedSetting('reviewPolicy') != ''}<li class="Link-2407621270"><a href="{url op="editorialPolicies" anchor="peerReviewProcess"}">{translate key="about.peerReviewProcess"}</a></li>{/if}
            	{if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}<li class="Link-2407621270"><a href="{url op="editorialPolicies" anchor="publicationFrequency"}">{translate key="about.publicationFrequency"}</a></li>{/if}
            	{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}<li class="Link-2407621270"><a href="{url op="editorialPolicies" anchor="openAccessPolicy"}">{translate key="about.openAccessPolicy"}</a></li>{/if}
            	{if $journalSettings.enableLockss && $currentJournal->getLocalizedSetting('lockssLicense') != ''}<li class="Link-2407621270"><a href="{url op="editorialPolicies" anchor="archiving"}">{translate key="about.archiving"}</a></li>{/if}
            	{if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.membershipFeeEnabled && $journalSettings.membershipFee > 0}<li class="u-hide Link-2407621270"><a href="{url op="memberships"}">{translate key="about.memberships"}</a></li>{/if}
            	{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
            		<li class="u-hide Link-2407621270"><a href="{url op="subscriptions"}">{translate key="about.subscriptions"}</a></li>
            		{if !empty($journalSettings.enableAuthorSelfArchive)}<li class="Link-2407621270"><a href="{url op="editorialPolicies" anchor="authorSelfArchivePolicy"}">{translate key="about.authorSelfArchive"}</a></li>{/if}
            		{if !empty($journalSettings.enableDelayedOpenAccess)}<li class="Link-2407621270"><a href="{url op="editorialPolicies" anchor="delayedOpenAccessPolicy"}">{translate key="about.delayedOpenAccess"}</a></li>{/if}
            		{if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.acceptSubscriptionPayments && $journalSettings.purchaseIssueFeeEnabled && $journalSettings.purchaseIssueFee > 0}<li class="Link-2407621270"><a href="{url op="editorialPolicies" anchor="purchaseIssue"}">{translate key="about.purchaseIssue"}</a></li>{/if}
            		{if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.acceptSubscriptionPayments && $journalSettings.purchaseArticleFeeEnabled && $journalSettings.purchaseArticleFee > 0}<li class="Link-2407621270"><a href="{url op="editorialPolicies" anchor="purchaseArticle"}">{translate key="about.purchaseArticle"}</a></li>{/if}
            	{/if}{* $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION *}
            	{foreach key=key from=$customAboutItems item=customAboutItem}
            		{if $customAboutItem.title!=''}<li class="Link-2407621270"><a href="{url op="editorialPolicies" anchor=custom-$key}">{$customAboutItem.title|escape}</a></li>{/if}
            	{/foreach}
            	{call_hook name="Templates::About::Index::Policies"}
            </ul>
        </section>
        <section data-section-outline-level="3" data-section-layout-level="2">
            <h3 data-section-outline-level="3" class="Type-1552298965" data-section-layout-level="2">{translate key="about.submissions"}</h3>
            <ul class="Link">
            	<li class="Link-2407621270"><a href="{url op="submissions" anchor="onlineSubmissions"}">{translate key="about.onlineSubmissions"}</a></li>
            	{if $currentJournal->getLocalizedSetting('authorGuidelines') != ''}<li class="Link-2407621270"><a href="{url op="submissions" anchor="authorGuidelines"}">{translate key="about.authorGuidelines"}</a></li>{/if}
            	{if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}<li class="Link-2407621270"><a href="{url op="submissions" anchor="copyrightNotice"}">{translate key="about.copyrightNotice"}</a></li>{/if}
            	{if $currentJournal->getLocalizedSetting('privacyStatement') != ''}<li class="Link-2407621270"><a href="{url op="submissions" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></li>{/if}
            	{if $currentJournal->getSetting('journalPaymentsEnabled') && ($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled'))}<li class="Link-2407621270"><a href="{url op="submissions" anchor="authorFees"}">{translate key="about.authorFees"}</a></li>{/if}
            	{call_hook name="Templates::About::Index::Submissions"}
            </ul>
            
            <h3 class="Type-1552298965">{translate key="about.other"}</h3>
            <ul class="Link">
            	{if not ($currentJournal->getSetting('publisherInstitution') == '' && $currentJournal->getLocalizedSetting('publisherNote') == '' && $currentJournal->getLocalizedSetting('contributorNote') == '' && empty($journalSettings.contributors) && $currentJournal->getLocalizedSetting('sponsorNote') == '' && empty($journalSettings.sponsors))}<li class="Link-2407621270"><a href="{url op="journalSponsorship"}">{translate key="about.journalSponsorship"}</a></li>{/if}
            	{if $currentJournal->getLocalizedSetting('history') != ''}<li class="Link-2407621270"><a href="{url op="history"}">{translate key="about.history"}</a></li>{/if}
            	<li class="Link-2407621270"><a href="{url op="sitemap"}">{translate key="about.siteMap"}</a></li>
            	<li class="Link-2407621270"><a href="{url op="insights"}">{translate key="about.journalInsight"}</a></li>
            	{if $publicStatisticsEnabled}<li class="Link-2407621270"><a href="{url op="statistics"}">{translate key="about.statistics"}</a></li>{/if}
            	{call_hook name="Templates::About::Index::Other"}
            </ul>
        </section>
    </div>
</section>

<section class="u-hide content">
    <div id="aboutPeople">
        <h2>{translate key="about.people"}</h2>
        <ul>
        	{if not (empty($journalSettings.mailingAddress) && empty($journalSettings.contactName) && empty($journalSettings.contactAffiliation) && empty($journalSettings.contactMailingAddress) && empty($journalSettings.contactPhone) && empty($journalSettings.contactFax) && empty($journalSettings.contactEmail) && empty($journalSettings.supportName) && empty($journalSettings.supportPhone) && empty($journalSettings.supportEmail))}
        		<li><a href="{url op="contact"}">{translate key="about.contact"}</a></li>
        	{/if}
        	<li id="editorialTeamLink"><a href="{url op="editorialTeam"}">{translate key="about.editorialTeam"}</a></li>
        	{if $peopleGroups}
        		{iterate from=peopleGroups item=peopleGroup}
        		<li><a href="{url op="displayMembership" path=$peopleGroup->getId()}">{$peopleGroup->getLocalizedTitle()|escape}</a></li>
        		{/iterate}
        	{/if}
        	{call_hook name="Templates::About::Index::People"}
        </ul>
    </div>
    <div id="aboutPolicies">
        <h2>{translate key="about.policies"}</h2>
        <ul>
        	{if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}<li><a href="{url op="editorialPolicies" anchor="focusAndScope"}">{translate key="about.focusAndScope"}</a></li>{/if}
        	<li><a href="{url op="editorialPolicies" anchor="sectionPolicies"}">{translate key="about.sectionPolicies"}</a></li>
        	{if $currentJournal->getLocalizedSetting('reviewPolicy') != ''}<li><a href="{url op="editorialPolicies" anchor="peerReviewProcess"}">{translate key="about.peerReviewProcess"}</a></li>{/if}
        	{if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}<li><a href="{url op="editorialPolicies" anchor="publicationFrequency"}">{translate key="about.publicationFrequency"}</a></li>{/if}
        	{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}<li><a href="{url op="editorialPolicies" anchor="openAccessPolicy"}">{translate key="about.openAccessPolicy"}</a></li>{/if}
        	{if $journalSettings.enableLockss && $currentJournal->getLocalizedSetting('lockssLicense') != ''}<li><a href="{url op="editorialPolicies" anchor="archiving"}">{translate key="about.archiving"}</a></li>{/if}
        	{if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.membershipFeeEnabled && $journalSettings.membershipFee > 0}<li><a href="{url op="memberships"}">{translate key="about.memberships"}</a></li>{/if}
        	{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
        		<li><a href="{url op="subscriptions"}">{translate key="about.subscriptions"}</a></li>
        		{if !empty($journalSettings.enableAuthorSelfArchive)}<li><a href="{url op="editorialPolicies" anchor="authorSelfArchivePolicy"}">{translate key="about.authorSelfArchive"}</a></li>{/if}
        		{if !empty($journalSettings.enableDelayedOpenAccess)}<li><a href="{url op="editorialPolicies" anchor="delayedOpenAccessPolicy"}">{translate key="about.delayedOpenAccess"}</a></li>{/if}
        		{if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.acceptSubscriptionPayments && $journalSettings.purchaseIssueFeeEnabled && $journalSettings.purchaseIssueFee > 0}<li><a href="{url op="editorialPolicies" anchor="purchaseIssue"}">{translate key="about.purchaseIssue"}</a></li>{/if}
        		{if $paymentConfigured && $journalSettings.journalPaymentsEnabled && $journalSettings.acceptSubscriptionPayments && $journalSettings.purchaseArticleFeeEnabled && $journalSettings.purchaseArticleFee > 0}<li><a href="{url op="editorialPolicies" anchor="purchaseArticle"}">{translate key="about.purchaseArticle"}</a></li>{/if}
        	{/if}{* $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION *}
        	{foreach key=key from=$customAboutItems item=customAboutItem}
        		{if $customAboutItem.title!=''}<li><a href="{url op="editorialPolicies" anchor=custom-$key}">{$customAboutItem.title|escape}</a></li>{/if}
        	{/foreach}
        	{call_hook name="Templates::About::Index::Policies"}
        </ul>
    </div>
    <div id="aboutSubmissions">
        <h2>{translate key="about.submissions"}</h2>
        <ul>
        	<li><a href="{url op="submissions" anchor="onlineSubmissions"}">{translate key="about.onlineSubmissions"}</a></li>
        	{if $currentJournal->getLocalizedSetting('authorGuidelines') != ''}<li><a href="{url op="submissions" anchor="authorGuidelines"}">{translate key="about.authorGuidelines"}</a></li>{/if}
        	{if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}<li><a href="{url op="submissions" anchor="copyrightNotice"}">{translate key="about.copyrightNotice"}</a></li>{/if}
        	{if $currentJournal->getLocalizedSetting('privacyStatement') != ''}<li><a href="{url op="submissions" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></li>{/if}
        	{if $currentJournal->getSetting('journalPaymentsEnabled') && ($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled'))}<li><a href="{url op="submissions" anchor="authorFees"}">{translate key="about.authorFees"}</a></li>{/if}
        	{call_hook name="Templates::About::Index::Submissions"}
        </ul>
    </div>
    <div id="aboutOther">
        <h2>{translate key="about.other"}</h2>
        <ul>
        	{if not ($currentJournal->getSetting('publisherInstitution') == '' && $currentJournal->getLocalizedSetting('publisherNote') == '' && $currentJournal->getLocalizedSetting('contributorNote') == '' && empty($journalSettings.contributors) && $currentJournal->getLocalizedSetting('sponsorNote') == '' && empty($journalSettings.sponsors))}<li><a href="{url op="journalSponsorship"}">{translate key="about.journalSponsorship"}</a></li>{/if}
        	{if $currentJournal->getLocalizedSetting('history') != ''}<li><a href="{url op="history"}">{translate key="about.history"}</a></li>{/if}
        	<li><a href="{url op="sitemap"}">{translate key="about.siteMap"}</a></li>
        	<li><a href="{url op="insights"}">{translate key="about.journalInsight"}</a></li>
        	{if $publicStatisticsEnabled}<li><a href="{url op="statistics"}">{translate key="about.statistics"}</a></li>{/if}
        	{call_hook name="Templates::About::Index::Other"}
        </ul>
    </div>
</section>

{include file="common/footer.tpl"}

