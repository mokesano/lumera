{**
 * templates/about/subscriptions.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Journal Subscriptions.
 *
 *}
{strip}
    {assign var="pageTitle" value="about.subscriptions"}
    {include file="common/header-members.tpl"}
{/strip}

<div class="MainGrid-1563860116">

<div data-section-outline-level="1">
    <div class="meta MetaFlex-1552298965">
        <div data-section-outline-level="1" data-section-layout-level="1">
            {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
            {assign var="displayJournalThumbnail" value=$currentJournal->getLocalizedSetting('journalThumbnail')}
            {if $displayHomepageImage && is_array($displayHomepageImage)}
            <picture>
                <source srcset="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp" type="image/webp">
                <img loading="lazy" class="journal-cover" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" alt="journal cover {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{elseif $displayPageHeaderTitle}{$displayPageHeaderTitle}{elseif $siteTitle}{$siteTitle}{else}{$applicationName}{/if}" style="width: 100%; max-width: 230px; display: block;" />
            </picture>
            {elseif $displayJournalThumbnail && is_array($displayJournalThumbnail)}
            <picture>
                <source srcset="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}?as=webp" type="image/webp">
                <img loading="lazy" class="journal-cover" src="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}" alt="journal cover {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{elseif $displayPageHeaderTitle}{$displayPageHeaderTitle}{elseif $siteTitle}{$siteTitle}{else}{$applicationName}{/if}" style="width: 100%; max-width: 230px; display: block;" />
            </picture>
            {else}
            <picture>
                <source srcset="//assets.sangia.org/img/img-default.jpg?as=webp" type="image/webp">
                <img loading="lazy" class="journal-cover" src="//assets.sangia.org/img/img-default.jpg" alt="journal cover" style="width: 100%; max-width: 230px; display: block;" />
            </picture>
            {/if}
        </div>
        <div data-section-outline-level="2" data-section-layout-level="1">
            <h2 class="Type-2932877531">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</h2>
            {assign var=issuePerVolume value=$currentJournal->getSetting('issuePerVolume')}
            {if $alternatePageHeader}
            <h4 data-section-outline-level="3" class="Subline-2415724952" data-section-layout-level="1">International {if $issuePerVolume|escape == 1}annually{elseif $issuePerVolume|escape == 2}semiannually{elseif $issuePerVolume|escape == 3}quarterly{elseif $issuePerVolume|escape == 4}quarterly{elseif $issuePerVolume|escape == 6}bimonthly{elseif $issuePerVolume|escape == 12}monthly{elseif $issuePerVolume|escape != 12}weekly{/if} journal of science</h4>
            {else}
            <h4 data-section-outline-level="3" class="Subline-2415724952" data-section-layout-level="1">National {if $issuePerVolume|escape == 1}annually{elseif $issuePerVolume|escape == 2}semiannually{elseif $issuePerVolume|escape == 3}quarterly{elseif $issuePerVolume|escape == 4}quarterly{elseif $issuePerVolume|escape == 6}bimonthly{elseif $issuePerVolume|escape == 12}monthly{elseif $issuePerVolume|escape != 12}weekly{/if} journal of science</h4>
            {/if}
        </div>
        <div data-section-outline-level="2" data-section-layout-level="1">
            <dl class="MetaGrid-2932877531">
                <div class="Type-270967514">
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
                <div class="Type-2225587032">
                    <dt data-section-outline-level="3"><b data-section-outline-level="3">{translate key="about.subscribeLength"}:</b>
                    </dt>
                    {assign var=volumePerYear value=$currentJournal->getSetting('volumePerYear')}
            		{assign var=issuePerVolume value=$currentJournal->getSetting('issuePerVolume')}
            		{assign var=firstYear value=$currentJournal->getSetting('initialYear')}
                    <dd data-section-outline-level="3" data-section-layout-level="1">1 Year Subscription with {$issuePerVolume|escape} Issues (plus online access to all articles starting {$firstYear|escape})</dd>
                </div>
                <div data-section-outline-level="3" class="Type-1803463199" data-section-layout-level="1">
                    <dt data-section-outline-level="3" data-section-layout-level="1"><b data-section-outline-level="3" data-section-layout-level="1">{translate key="about.accessOptions"}:</b>
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
            <input type="hidden" name="csrfToken" value="{$csrfToken|escape}" />
            {if !$individualSubscriptionTypes->wasEmpty()}
            <details data-section-outline-level="2" class="Details-2932877531" data-section-layout-level="2" open="">
                {iterate from=individualSubscriptionTypes item=subscriptionType}
                <summary class="subscription-type NoneDetailsMarker-270967514" data-section-outline-level="2" data-section-layout-level="2">
                    <div data-section-outline-level="2" class="SummaryStyle-2218521826" data-section-layout-level="2"><svg width="14px" height="14px" viewBox="0 0 32 32" class="details-marker Marker-1664207171" data-section-outline-level="2" data-section-layout-level="2"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z" data-section-outline-level="2" data-section-layout-level="2"></path></svg>
                        {if $smarty.iterate.subcat.index % 1 == 0}
                        <span data-section-outline-level="2" class="SubscriptionName-411063136" data-section-layout-level="2">{$subscriptionType->getSubscriptionTypeName()|escape}</span>
                        <span data-section-outline-level="2" class="SummaryPrice-3689769601" data-section-layout-level="2">{$subscriptionType->getCost()|string_format:"%.2f"|number_format:2:",":"."}&nbsp;{$subscriptionType->getCurrencyStringShort()|escape}</span>
                        {/if}
                    </div>
                </summary>
                <article data-section-outline-level="3" data-section-layout-level="2">
                    <div class="input-field PriceInfoBox-2218521826 checked" style="position: relative" data-section-outline-level="3" data-section-layout-level="3"><input type="radio" id="input-PS-Journal-subscription" class="radio-checked RadioCircle-1664207171" name="subscription-type" value="PS" checked="checked" style="display: none" data-original-index="0" data-section-outline-level="3" data-section-layout-level="4">
                        <label class="price-info Flex-411063136" for="input-PS-Journal-subscription" data-section-outline-level="3" data-section-layout-level="3">
                            <div class="prices-total  TotalPriceRow-3898465696" data-section-outline-level="3" data-section-layout-level="3">
                                <div data-section-outline-level="3" class="Type-3344151041" data-section-layout-level="3">{translate key=$subscriptionType->getFormatString()}</div>
                                <div id="effective-price" data-section-outline-level="3" class="BasketEffectivePriceCell-538306978" data-section-layout-level="3">{$subscriptionType->getCost()|string_format:"%.2f"|number_format:2:",":"."}&nbsp;{$subscriptionType->getCurrencyStringShort()|escape}</div>
                                <div class="single-issue BasketRegularPriceCell-3978918244" data-section-outline-level="3" data-section-layout-level="3">only {math|number_format:2:",":"." equation="x / y" x=$subscriptionType->getCost()|string_format:"%.2f" y=$issuePerVolume|escape} {$subscriptionType->getCurrencyStringShort()|escape} per issue</div>
                            </div>
                        </label>
                    </div>
                    <button id="subscribe-submit-Journal-subscription" type="submit" onclick="this.form.action='{url op="payPurchaseSubscription" path="individual"|to_array:$subscriptionType->getId()}'" data-track="cart-add" data-track-product="0" data-section-outline-level="3" class="ButtonPrimary-171795905" data-section-layout-level="3"><span data-section-outline-level="3" class="ButtonLabel-1664207171" data-section-layout-level="3">{translate key="about.subscribe"}</span>
                    </button>
                    <input type="hidden" id="input-coupon" name="coupon" value="" data-section-outline-level="3" data-section-layout-level="2">
                    <ul class="usps-list ListInset-3866894855" id="usps" data-section-outline-level="4" data-section-layout-level="2">
                        {if $alternatePageHeader}
                        <li data-section-outline-level="4" class="ListElement-1664207171" data-section-layout-level="2">Sinta Score {$alternatePageHeader} of this journal</li>
                        {/if}
                        {if $subscriptionType->getSubscriptionTypeDescription()}
                        <li data-section-outline-level="4" class="ListElement-1664207171" data-section-layout-level="2">{$subscriptionType->getSubscriptionTypeDescription()|nl2br}</li>
                        {/if}
                        <li data-section-outline-level="4" class="ListElement-3689769601" data-section-layout-level="2">Immediate Online Access</li>
                        <li data-section-outline-level="4" class="ListElement-4106162086" data-section-layout-level="2">Exclusive offer for individuals only</li>
                        {assign var=volumePerYear value=$currentJournal->getSetting('volumePerYear')}
            			{assign var=issuePerVolume value=$currentJournal->getSetting('issuePerVolume')}
            			{assign var=firstYear value=$currentJournal->getSetting('initialYear')}
                        <li data-section-outline-level="4" class="ListElement-1282713543" data-section-layout-level="2">1 Year Subscription with {$issuePerVolume|escape} Issues (plus online access to all articles starting {$firstYear|escape})</li>
                    </ul>
                </article>
                {/iterate}
            </details>
            {else if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $paymentConfigured && $currentJournal->getSetting('journalPaymentsEnabled') && $currentJournal->getSetting('acceptSubscriptionPayments') && $currentJournal->getSetting('purchaseIssueFeeEnabled') && $currentJournal->getSetting('purchaseIssueFee') > 0}
            <details data-section-outline-level="2" class="Details-2932877531" data-section-layout-level="2" open="">
                <summary class="subscription-type NoneDetailsMarker-270967514" data-section-outline-level="2" data-section-layout-level="2">
                    <div data-section-outline-level="2" class="SummaryStyle-2218521826" data-section-layout-level="2"><svg width="14px" height="14px" viewBox="0 0 32 32" class="details-marker Marker-1664207171" data-section-outline-level="2" data-section-layout-level="2"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z" data-section-outline-level="2" data-section-layout-level="2"></path></svg>
                        <span data-section-outline-level="2" class="SubscriptionName-411063136" data-section-layout-level="2">{translate key="about.subscriptions"}</span>
                        <span data-section-outline-level="2" class="SummaryPrice-3689769601" data-section-layout-level="2">{math|number_format:2:",":"." equation="x * y" x=$currentJournal->getSetting('purchaseIssueFee') y=$issuePerVolume|escape} {$currentJournal->getSetting('currency')|escape}</span>
                    </div>
                </summary>
                <article data-section-outline-level="3" data-section-layout-level="2">
                    <div class="input-field PriceInfoBox-2218521826 checked" style="position: relative" data-section-outline-level="3" data-section-layout-level="3"><input type="radio" id="input-PS-Journal-subscription" class="radio-checked RadioCircle-1664207171" name="subscription-type" value="PS" checked="checked" style="display: none" data-original-index="0" data-section-outline-level="3" data-section-layout-level="4">
                        <label class="price-info Flex-411063136" for="input-PS-Journal-subscription" data-section-outline-level="3" data-section-layout-level="3">
                            <div class="prices-total  TotalPriceRow-3898465696" data-section-outline-level="3" data-section-layout-level="3">
                                <div data-section-outline-level="3" class="Type-3344151041" data-section-layout-level="3">{if $onlineIssn} {else if $printIssn}{if $currentJournal->getSetting('onlineIssn')}Online{else}Online (<i>on proccess</i>){/if}{/if}{if $currentJournal->getSetting('printIssn')} &amp; Print{/if}</div>
                                <div id="effective-price" data-section-outline-level="3" class="BasketEffectivePriceCell-538306978" data-section-layout-level="3">{math|number_format:2:",":"." equation="x * y" x=$currentJournal->getSetting('purchaseIssueFee') y=$issuePerVolume|escape} {$currentJournal->getSetting('currency')|escape}</div>
                                <div class="single-issue BasketRegularPriceCell-3978918244" data-section-outline-level="3" data-section-layout-level="3">only {$currentJournal->getSetting('purchaseIssueFee')|string_format:"%.2f"|number_format:2:",":"."} {$currentJournal->getSetting('currency')|escape} per issue</div>
                            </div>
                        </label>
                    </div>
                    {if $currentJournal->getSetting('currency')|escape}
                        {assign var="purchaseIssueCartAmount" value=$currentJournal->getSetting('purchaseIssueFee')|default:0}
                        {assign var="purchaseIssueCartCount" value=$issuePerVolume|default:0}
                        {if $purchaseIssueCartCount > 0}
                            {math equation="x * y" x=$purchaseIssueCartAmount y=$purchaseIssueCartCount format="%.2f" assign="purchaseIssueCartAmount"}
                        {/if}
                        <button id="subscribe-submit-Journal-subscription" type="submit" onclick="this.form.action='{url page="checkout" op="cart" feeType="PURCHASE_ISSUE" amount=$purchaseIssueCartAmount}'" data-track="cart-add" data-track-product="0" data-section-outline-level="3" class="ButtonPrimary-171795905" data-section-layout-level="3"><span data-section-outline-level="3" class="ButtonLabel-1664207171" data-section-layout-level="3">{translate key="about.subscribe"}</span>
                        </button>
                    {/if}
                    <input type="hidden" id="input-coupon" name="coupon" value="" data-section-outline-level="3" data-section-layout-level="2">
                    <ul class="usps-list ListInset-3866894855" id="usps" data-section-outline-level="4" data-section-layout-level="2">
                        {if $alternatePageHeader}
                        <li data-section-outline-level="4" class="ListElement-1664207171" data-section-layout-level="2">Sinta Score {$alternatePageHeader} of this journal</li>
                        <li data-section-outline-level="4" class="ListElement-411063136" data-section-layout-level="2">National and international indexed databases</li>
                        {/if}
                        <li data-section-outline-level="4" class="ListElement-3689769601" data-section-layout-level="2">Immediate Online Access</li>
                        <li data-section-outline-level="4" class="ListElement-4106162086" data-section-layout-level="2">Exclusive offer for individuals only</li>
                        {assign var=volumePerYear value=$currentJournal->getSetting('volumePerYear')}
            			{assign var=issuePerVolume value=$currentJournal->getSetting('issuePerVolume')}
            			{assign var=firstYear value=$currentJournal->getSetting('initialYear')}
                        <li data-section-outline-level="4" class="ListElement-1282713543" data-section-layout-level="2">1 Year Subscription with {$issuePerVolume|escape} Issues (plus online access to all articles starting {$firstYear|escape})</li>
                    </ul>
                </article>
            </details>
            {/if}            
        </form>
    </div>
    {if !$institutionalSubscriptionTypes->wasEmpty()}
    <div data-reflect-state="true" data-section-outline-level="2" class="Institutional-269378647" data-section-layout-level="2">
        <details data-section-outline-level="2" class="Details-3185356244" data-section-layout-level="2">
            {iterate name=subs from=institutionalSubscriptionTypes item=subscriptionType}
            <summary class="subscription-type NoneDetailsMarker-2932877531" data-section-outline-level="2" data-section-layout-level="2">
                <div data-section-outline-level="2" class="SummaryStyle-270967514" data-section-layout-level="2"><svg width="14px" height="14px" viewBox="0 0 32 32" class="details-marker Marker-2218521826" data-section-outline-level="2" data-section-layout-level="2"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z" data-section-outline-level="2" data-section-layout-level="2"></path></svg>
                    <span data-section-outline-level="2" class="SubscriptionName-171795905" data-section-layout-level="2">{$subscriptionType->getSubscriptionTypeName()|escape}</span>
                    {if $smarty.iterate.subcat.index % 1 == 0}
                    <span data-section-outline-level="2" class="SummaryPrice-4244084256" data-section-layout-level="2">{$subscriptionType->getCost()|string_format:"%.2f"|number_format:2:",":"."}&nbsp;{$subscriptionType->getCurrencyStringShort()|escape}</span>
                    {/if}
                </div>
            </summary>
            <article data-section-outline-level="3" data-section-layout-level="2">
                <div class="input-field InstitutionalInfoBox-270967514 PriceInfoBox-2218521826 checked" style="position: relative" data-section-outline-level="3" data-section-layout-level="3"><input type="radio" id="input-PS-Journal-subscription" class="radio-checked RadioCircle-1664207171" name="subscription-type" value="PS" checked="checked" style="display: none" data-original-index="0" data-section-outline-level="3" data-section-layout-level="4">
                    <label class="price-info Flex-411063136" for="input-PS-Journal-subscription" data-section-outline-level="3" data-section-layout-level="3">
                        <div class="prices-total  TotalPriceRow-3898465696" data-section-outline-level="3" data-section-layout-level="3">
                            <div data-section-outline-level="3" class="Type-3344151041" data-section-layout-level="3">{translate key=$subscriptionType->getFormatString()}</div>
                            <div id="effective-price" data-section-outline-level="3" class="BasketEffectivePriceCell-538306978" data-section-layout-level="3">{$subscriptionType->getCost()|string_format:"%.2f"|number_format:2:",":"."}&nbsp;{$subscriptionType->getCurrencyStringShort()|escape}</div>
                            <div class="single-issue BasketRegularPriceCell-3978918244" data-section-outline-level="3" data-section-layout-level="3">only {math|number_format:2:",":"." equation="x / y" x=$subscriptionType->getCost()|string_format:"%.2f" y=$issuePerVolume|escape} {$subscriptionType->getCurrencyStringShort()|escape} per issue</div>
                        </div>
                    </label>
                </div>
                <a href="{url op="payPurchaseSubscription" path="institutional"|to_array:$subscriptionType->getId()}" data-section-outline-level="3" class="ButtonLink-2686258585" data-section-layout-level="3"><span data-section-outline-level="3" class="ButtonLabel-2218521826" data-section-layout-level="3">Learn more</span></a>
                <ul class="usps-list ListInset-3866894855" id="usps" data-section-outline-level="4" data-section-layout-level="2">
                    <li data-section-outline-level="4" class="ListElement-411063136" data-section-layout-level="2">{translate key="subscriptions.institutionalDescription"}</li>
                    {if $subscriptionType->getSubscriptionTypeDescription()}
                    <li data-section-outline-level="4" class="ListElement-1664207171" data-section-layout-level="2">{$subscriptionType->getSubscriptionTypeDescription()|nl2br}</li>
                    {/if}
                </ul>
            </article>
            {/iterate}
        </details>
    </div>    
    {else}
    <div data-reflect-state="true" data-section-outline-level="2" class="Institutional-269378647" data-section-layout-level="2">
        <details data-section-outline-level="2" class="Details-3185356244" data-section-layout-level="2">
            <summary class="subscription-type NoneDetailsMarker-2932877531" data-section-outline-level="2" data-section-layout-level="2"><div data-section-outline-level="2" class="SummaryStyle-270967514" data-section-layout-level="2"><svg width="14px" height="14px" viewBox="0 0 32 32" class="details-marker Marker-2218521826" data-section-outline-level="2" data-section-layout-level="2"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z" data-section-outline-level="2" data-section-layout-level="2"></path></svg><span data-section-outline-level="2" class="SubscriptionName-171795905" data-section-layout-level="2">For Institutions</span><span data-section-outline-level="2" class="SummaryPrice-4244084256" data-section-layout-level="2">on demand</span></div></summary>
            <article data-section-outline-level="3" data-section-layout-level="2">
                <div class="input-field InstitutionalInfoBox-270967514" data-section-outline-level="3" data-section-layout-level="3">Are you interested in an institutional license?</div>
                <a href="#" data-section-outline-level="3" class="ButtonLink-2686258585" data-section-layout-level="3"><span data-section-outline-level="3" class="ButtonLabel-2218521826" data-section-layout-level="3">Learn more</span></a>
            </article>
        </details>
    </div>
    {/if}
    <div data-section-outline-level="2" class="PaymentOption-851350800" data-section-layout-level="2"><svg width="1000" height="324" viewBox="0 0 1000 323.7" data-section-outline-level="2" class="PaymentOptionLogo-3185356244" data-section-layout-level="2"><g transform="matrix(4.4299631 0 0 4.4299631 -81.165783 -105.04783)" data-section-outline-level="2" data-section-layout-level="2"><polygon class="letter" points="116.1 95.7 97.9 95.7 109.3 25 127.6 25 " fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></polygon><path class="letter" d="m182.4 26.7c-3.6-1.4-9.3-3-16.4-3-18.1 0-30.8 9.6-30.9 23.4-0.1 10.2 9.1 15.8 16 19.2 7.1 3.5 9.5 5.7 9.5 8.8-0.1 4.7-5.7 6.9-11 6.9-7.3 0-11.2-1.1-17.2-3.8l-2.4-1.1-2.6 15.9c4.3 2 12.2 3.7 20.4 3.8 19.2 0 31.7-9.5 31.8-24.2 0.1-8.1-4.8-14.2-15.3-19.3-6.4-3.2-10.3-5.4-10.3-8.7 0.1-3 3.3-6.1 10.5-6.1 5.9-0.2 10.3 1.3 13.6 2.7l1.7 0.8 2.5-15.3 0 0z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><path class="letter" d="m206.7 70.7c1.5-4.1 7.3-19.8 7.3-19.8-0.1 0.2 1.5-4.1 2.4-6.8l1.3 6.1c0 0 3.5 16.9 4.2 20.5-2.9 0-11.6 0-15.2 0l0 0zm22.6-45.7-14.1 0c-4.4 0-7.7 1.3-9.6 5.9l-27.2 64.9 19.2 0c0 0 3.2-8.7 3.8-10.6 2.1 0 20.8 0 23.5 0 0.5 2.5 2.2 10.6 2.2 10.6l16.9 0-14.8-70.7 0 0z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><path class="letter" d="M82.6 25 64.7 73.2 62.7 63.4C59.4 52.2 49 39.9 37.4 33.8l16.4 61.8 19.3 0 28.7-70.6-19.3 0 0 0z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><path class="hook" d="m48 25-29.4 0-0.3 1.4c23 5.9 38.2 20 44.4 37L56.3 30.9c-1.1-4.5-4.3-5.8-8.3-5.9l0 0z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path></g></svg><svg width="146.8" height="120.41" viewBox="0 0 146.8 120.41" data-section-outline-level="2" class="PaymentOptionLogo-2678154903" data-section-layout-level="2"><style type="text/css" data-section-outline-level="2" data-section-layout-level="2">.cls-2{fill:#231f20;}.cls-3{fill:#ff5f00;}.cls-4{fill:#eb001b;}.cls-5{fill:#f79e1b;}</style><path class="middleGray" d="M36.35,105.26v-6a3.56,3.56,0,0,0-3.76-3.8,3.7,3.7,0,0,0-3.36,1.7,3.51,3.51,0,0,0-3.16-1.7,3.16,3.16,0,0,0-2.8,1.42V95.7H21.19v9.56h2.1V100a2.24,2.24,0,0,1,2.34-2.54c1.38,0,2.08.9,2.08,2.52v5.32h2.1V100a2.25,2.25,0,0,1,2.34-2.54c1.42,0,2.1.9,2.1,2.52v5.32ZM67.42,95.7H64V92.8h-2.1v2.9H60v1.9h1.94V102c0,2.22.86,3.54,3.32,3.54a4.88,4.88,0,0,0,2.6-.74l-.6-1.78a3.84,3.84,0,0,1-1.84.54c-1,0-1.38-.64-1.38-1.6V97.6h3.4Zm17.74-.24a2.82,2.82,0,0,0-2.52,1.4V95.7H80.58v9.56h2.08V99.9c0-1.58.68-2.46,2-2.46a3.39,3.39,0,0,1,1.3.24l.64-2a4.45,4.45,0,0,0-1.48-.26Zm-26.82,1a7.15,7.15,0,0,0-3.9-1c-2.42,0-4,1.16-4,3.06,0,1.56,1.16,2.52,3.3,2.82l1,.14c1.14.16,1.68.46,1.68,1,0,.74-.76,1.16-2.18,1.16a5.09,5.09,0,0,1-3.18-1l-1,1.62a6.9,6.9,0,0,0,4.14,1.24c2.76,0,4.36-1.3,4.36-3.12s-1.26-2.56-3.34-2.86l-1-.14c-.9-.12-1.62-.3-1.62-.94s.68-1.12,1.82-1.12a6.16,6.16,0,0,1,3,.82Zm55.71-1a2.82,2.82,0,0,0-2.52,1.4V95.7h-2.06v9.56h2.08V99.9c0-1.58.68-2.46,2-2.46a3.39,3.39,0,0,1,1.3.24l.64-2a4.45,4.45,0,0,0-1.48-.26Zm-26.8,5a4.83,4.83,0,0,0,5.1,5,5,5,0,0,0,3.44-1.14l-1-1.68a4.2,4.2,0,0,1-2.5.86,3.07,3.07,0,0,1,0-6.12,4.2,4.2,0,0,1,2.5.86l1-1.68a5,5,0,0,0-3.44-1.14,4.83,4.83,0,0,0-5.1,5Zm19.48,0V95.7h-2.08v1.16a3.63,3.63,0,0,0-3-1.4,5,5,0,0,0,0,10,3.63,3.63,0,0,0,3-1.4v1.16h2.08Zm-7.74,0a2.89,2.89,0,1,1,2.9,3.06,2.87,2.87,0,0,1-2.9-3.06Zm-25.1-5a5,5,0,0,0,.14,10A5.81,5.81,0,0,0,78,104.16l-1-1.54a4.55,4.55,0,0,1-2.78,1,2.65,2.65,0,0,1-2.86-2.34h7.1c0-.26,0-.52,0-.8,0-3-1.86-5-4.54-5Zm0,1.86a2.37,2.37,0,0,1,2.42,2.32h-5a2.46,2.46,0,0,1,2.54-2.32ZM126,100.48V91.86H124v5a3.63,3.63,0,0,0-3-1.4,5,5,0,0,0,0,10,3.63,3.63,0,0,0,3-1.4v1.16H126Zm3.47,3.39a1,1,0,0,1,.38.07,1,1,0,0,1,.31.2,1,1,0,0,1,.21.3.93.93,0,0,1,0,.74,1,1,0,0,1-.21.3,1,1,0,0,1-.31.2.94.94,0,0,1-.38.08,1,1,0,0,1-.9-.58.94.94,0,0,1,0-.74,1,1,0,0,1,.21-.3,1,1,0,0,1,.31-.2A1,1,0,0,1,129.5,103.87Zm0,1.69a.71.71,0,0,0,.29-.06.75.75,0,0,0,.23-.16.74.74,0,0,0,0-1,.74.74,0,0,0-.23-.16.72.72,0,0,0-.29-.06.75.75,0,0,0-.29.06.73.73,0,0,0-.24.16.74.74,0,0,0,0,1,.74.74,0,0,0,.24.16A.74.74,0,0,0,129.5,105.56Zm.06-1.19a.4.4,0,0,1,.26.08.25.25,0,0,1,.09.21.24.24,0,0,1-.07.18.35.35,0,0,1-.21.09l.29.33h-.23l-.27-.33h-.09v.33h-.19v-.88Zm-.22.17v.24h.22a.21.21,0,0,0,.12,0,.1.1,0,0,0,0-.09.1.1,0,0,0,0-.09.21.21,0,0,0-.12,0Zm-11-4.06a2.89,2.89,0,1,1,2.9,3.06,2.87,2.87,0,0,1-2.9-3.06Zm-70.23,0V95.7H46v1.16a3.63,3.63,0,0,0-3-1.4,5,5,0,0,0,0,10,3.63,3.63,0,0,0,3-1.4v1.16h2.08Zm-7.74,0a2.89,2.89,0,1,1,2.9,3.06A2.87,2.87,0,0,1,40.32,100.48Z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><rect class="darkGray" x="57.65" y="22.85" width="31.5" height="56.61" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></rect><path class="middleGray" d="M59.65,51.16A35.94,35.94,0,0,1,73.4,22.85a36,36,0,1,0,0,56.61A35.94,35.94,0,0,1,59.65,51.16Z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><path class="lightGray" d="M131.65,51.16A36,36,0,0,1,73.4,79.46a36,36,0,0,0,0-56.61,36,36,0,0,1,58.25,28.3Z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><path class="lightGray" d="M128.21,73.46V72.3h.47v-.24h-1.19v.24H128v1.16Zm2.31,0v-1.4h-.36l-.42,1-.42-1H129v1.4h.26V72.41l.39.91h.27l.39-.91v1.06Z" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path></svg><svg xmlns:xlink="http://www.w3.org/1999/xlink" width="300" height="300" viewBox="0 0 300 300" class="americanExpress PaymentOptionLogo-1902435926" data-section-outline-level="2" data-section-layout-level="2"><radialGradient id="SVGID_1_" cx="57.4" cy="57" r="264.6" gradientUnits="userSpaceOnUse" data-section-outline-level="2" data-section-layout-level="2"><stop offset="0" stop-color="#9DD5F6" data-section-outline-level="2" data-section-layout-level="2"></stop><stop offset="0.0711" stop-color="#98D3F5" data-section-outline-level="2" data-section-layout-level="2"></stop><stop offset="0.1575" stop-color="#89CEF3" data-section-outline-level="2" data-section-layout-level="2"></stop><stop offset="0.2516" stop-color="#70C6EF" data-section-outline-level="2" data-section-layout-level="2"></stop><stop offset="0.3514" stop-color="#4EBBEA" data-section-outline-level="2" data-section-layout-level="2"></stop><stop offset="0.4546" stop-color="#23ADE3" data-section-outline-level="2" data-section-layout-level="2"></stop><stop offset="0.5" stop-color="#0DA6E0" data-section-outline-level="2" data-section-layout-level="2"></stop><stop offset="1" stop-color="#2E77BC" data-section-outline-level="2" data-section-layout-level="2"></stop></radialGradient><path d="M289.6 7.6H7.6v283h281.9v-93.4c1.1-1.6 1.7-3.7 1.7-6.2 0-2.9-0.6-4.7-1.7-6.2" fill="inherit" data-section-outline-level="2" data-section-layout-level="2"></path><defs data-section-outline-level="2" data-section-layout-level="2"><path id="SVGID_2_" d="M289.6 7.6H7.6v283h281.9v-93.4c1.1-1.6 1.7-3.7 1.7-6.2 0-2.9-0.6-4.7-1.7-6.2" data-section-outline-level="2" data-section-layout-level="2"></path></defs><clipPath data-section-outline-level="2" data-section-layout-level="2"><use xlink:href="#SVGID_2_" data-section-outline-level="2" data-section-layout-level="2"></use></clipPath><path fill="#fff" d="M33.1 130l-5.4-13.2 -5.4 13.2M152.5 124.8c-1.1 0.7-2.4 0.7-3.9 0.7h-9.6v-7.4h9.8c1.4 0 2.8 0.1 3.8 0.6 1 0.5 1.7 1.5 1.7 2.9C154.2 123.1 153.6 124.2 152.5 124.8zM221.2 130l-5.5-13.2 -5.5 13.2H221.2zM93.2 144.3h-8.1l0-26 -11.5 26h-7l-11.5-26v26H39l-3-7.4h-16.5l-3.1 7.4H7.7l14.2-33.2h11.8l13.5 31.4v-31.4h12.9l10.4 22.5 9.5-22.5h13.2V144.3zM125.6 144.3H99.1v-33.2h26.5v6.9h-18.5v6h18.1v6.8h-18.1v6.6h18.5V144.3zM162.9 120.1c0 5.3-3.5 8-5.6 8.8 1.7 0.7 3.2 1.8 3.9 2.8 1.1 1.6 1.3 3.1 1.3 6.1v6.5h-8l0-4.2c0-2 0.2-4.9-1.3-6.5 -1.2-1.2-2.9-1.4-5.8-1.4h-8.5v12.1h-7.9v-33.2h18.2c4.1 0 7 0.1 9.6 1.6C161.4 114.3 162.9 116.4 162.9 120.1zM175.6 144.3h-8.1v-33.2h8.1V144.3zM269.4 144.3h-11.2l-15-24.9v24.9h-16.1l-3.1-7.4H207.5l-3 7.4h-9.3c-3.9 0-8.7-0.9-11.5-3.7 -2.8-2.8-4.2-6.6-4.2-12.7 0-4.9 0.9-9.4 4.3-13 2.6-2.6 6.6-3.9 12-3.9h7.7v7.1h-7.5c-2.9 0-4.5 0.4-6.1 2 -1.4 1.4-2.3 4-2.3 7.5 0 3.6 0.7 6.1 2.2 7.8 1.2 1.3 3.4 1.7 5.5 1.7h3.6l11.2-26.1h11.9l13.4 31.4v-31.4h12.1l13.9 23.1v-23.1h8.1V144.3zM7.6 150.9h13.5l3.1-7.4h6.8l3 7.4h26.6v-5.6l2.4 5.7h13.8l2.4-5.7v5.7h66.2l0-12.1h1.3c0.9 0 1.2 0.1 1.2 1.6v10.5h34.2v-2.8c2.8 1.5 7.1 2.8 12.7 2.8h14.4l3.1-7.4h6.8l3 7.4h27.8v-7l4.2 7h22.2v-46.2h-22v5.5l-3.1-5.5H228.8v5.5l-2.8-5.5h-30.5c-5.1 0-9.6 0.7-13.2 2.7v-2.7h-21.1v2.7c-2.3-2-5.5-2.7-8.9-2.7H75.3l-5.2 11.9 -5.3-11.9H40.6v5.5l-2.7-5.5H17.2l-9.6 22V150.9z" data-section-outline-level="2" data-section-layout-level="2"></path><path fill="#fff" d="M289.6 175.3h-14.4c-1.4 0-2.4 0.1-3.2 0.6 -0.8 0.5-1.2 1.3-1.2 2.4 0 1.3 0.7 2.1 1.7 2.5 0.8 0.3 1.7 0.4 3.1 0.4l4.3 0.1c4.3 0.1 7.2 0.9 9 2.7 0.3 0.3 0.5 0.5 0.7 0.8M289.6 197.2c-1.9 2.8-5.7 4.2-10.8 4.2h-15.3v-7.1h15.2c1.5 0 2.6-0.2 3.2-0.8 0.6-0.5 0.9-1.3 0.9-2.2 0-1-0.4-1.7-1-2.2 -0.6-0.5-1.4-0.7-2.8-0.7 -7.4-0.3-16.7 0.2-16.7-10.3 0-4.8 3.1-9.9 11.4-9.9h15.8v-6.6h-14.7c-4.4 0-7.6 1.1-9.9 2.7v-2.7h-21.7c-3.5 0-7.5 0.9-9.5 2.7v-2.7h-38.7v2.7c-3.1-2.2-8.3-2.7-10.7-2.7h-25.6v2.7c-2.4-2.4-7.9-2.7-11.2-2.7h-28.6l-6.5 7.1 -6.1-7.1H63.7v46.3h41.9l6.7-7.2 6.4 7.2 25.8 0v-10.9h2.5c3.4 0.1 7.5-0.1 11-1.6v12.5h21.3v-12.1h1c1.3 0 1.4 0.1 1.4 1.4v10.7h64.7c4.1 0 8.4-1.1 10.8-3v3h20.5c4.3 0 8.4-0.6 11.6-2.1V197.2zM258 183.9c1.5 1.6 2.4 3.6 2.4 7 0 7.1-4.5 10.5-12.4 10.5h-15.4v-7.1h15.4c1.5 0 2.6-0.2 3.2-0.8 0.5-0.5 0.9-1.3 0.9-2.2 0-1-0.4-1.7-1-2.2 -0.6-0.5-1.4-0.7-2.8-0.7 -7.4-0.3-16.7 0.2-16.7-10.3 0-4.8 3-9.9 11.3-9.9h15.9v7.1h-14.5c-1.4 0-2.4 0.1-3.2 0.6 -0.9 0.5-1.2 1.3-1.2 2.4 0 1.3 0.7 2.1 1.7 2.5 0.8 0.3 1.7 0.4 3.1 0.4l4.3 0.1C253.2 181.4 256.2 182.1 258 183.9zM186.5 181.9c-1.1 0.6-2.4 0.7-3.9 0.7h-9.6v-7.4h9.8c1.4 0 2.8 0 3.8 0.6 1 0.5 1.6 1.6 1.6 3S187.5 181.3 186.5 181.9zM191.3 186c1.8 0.7 3.2 1.8 3.9 2.8 1.1 1.6 1.3 3.1 1.3 6.1v6.6h-8v-4.1c0-2 0.2-4.9-1.3-6.5 -1.2-1.2-2.9-1.5-5.8-1.5h-8.5v12.1h-8v-33.2h18.3c4 0 6.9 0.2 9.5 1.6 2.5 1.5 4.1 3.6 4.1 7.4C196.9 182.5 193.3 185.2 191.3 186zM201.3 168.3h26.4v6.9h-18.6v6h18.1v6.8h-18.1v6.6l18.6 0v6.9h-26.4V168.3zM147.8 183.6h-10.2v-8.4h10.3c2.9 0 4.8 1.2 4.8 4.1C152.7 182.1 150.8 183.6 147.8 183.6zM129.7 198.4l-12.2-13.5 12.2-13.1V198.4zM98.2 194.5H78.8v-6.6h17.4v-6.8H78.8v-6h19.9l8.7 9.7L98.2 194.5zM161.2 179.2c0 9.2-6.9 11.1-13.8 11.1h-9.9v11.1h-15.4l-9.8-11 -10.1 11h-31.4v-33.2h31.9l9.8 10.9 10.1-10.9h25.3C154.2 168.3 161.2 170 161.2 179.2z" data-section-outline-level="2" data-section-layout-level="2"></path></svg><svg xmlns:xlink="http://www.w3.org/1999/xlink" width="650" height="595" viewBox="90 0 650 595" class="payPal PaymentOptionLogo-1121884433" data-section-outline-level="2" data-section-layout-level="2"><path class="darkGray" fill="inherit" d="M543.3 48.5c-16.7-19-46.9-27.2-85.5-27.2H345.7c-7.9 0-14.6 5.7-15.9 13.5l-46.7 296c-0.9 5.8 3.6 11.1 9.5 11.1h69.2l17.4-110.2 -0.5 3.5c1.2-7.8 7.9-13.5 15.8-13.5h32.9c64.6 0 115.2-26.2 130-102.2 0.4-2.2 0.8-4.4 1.1-6.6 -1.9-1-1.9-1 0 0C562.9 84.9 558.5 65.8 543.3 48.5" data-section-outline-level="2" data-section-layout-level="2"></path><path class="darkGray" fill="inherit" d="M543.3 48.5c-16.7-19-46.9-27.2-85.5-27.2H345.7c-7.9 0-14.6 5.7-15.9 13.5l-46.7 296c-0.9 5.8 3.6 11.1 9.5 11.1h69.2l17.4-110.2 -0.5 3.5c1.2-7.8 7.9-13.5 15.8-13.5h32.9c64.6 0 115.2-26.2 130-102.2 0.4-2.2 0.8-4.4 1.1-6.6 -1.9-1-1.9-1 0 0C562.9 84.9 558.5 65.8 543.3 48.5" data-section-outline-level="2" data-section-layout-level="2"></path><path class="darkGray" fill="inherit" d="M396.9 113.3c0.7-4.7 3.8-8.5 7.8-10.5 1.8-0.9 3.9-1.4 6.1-1.4h87.9c10.4 0 20.1 0.7 29 2.1 2.5 0.4 5 0.9 7.4 1.4 2.4 0.5 4.7 1.1 7 1.8 1.1 0.3 2.2 0.7 3.3 1 4.4 1.5 8.4 3.2 12.2 5.1 4.4-28.1 0-47.2-15.2-64.4 -16.7-19-46.9-27.2-85.5-27.2H344.7c-7.9 0-14.6 5.7-15.8 13.5l-46.7 296c-0.9 5.8 3.6 11.1 9.5 11.1h69.2l17.4-110.2L396.9 113.3 396.9 113.3z" data-section-outline-level="2" data-section-layout-level="2"></path><path class="lightGray" fill="inherit" d="M557.5 112.9L557.5 112.9c-0.3 2.1-0.7 4.3-1.1 6.6 -14.8 75.9-65.4 102.2-130 102.2h-32.9c-7.9 0-14.6 5.7-15.8 13.5L360.9 342l-4.8 30.3c-0.8 5.1 3.1 9.7 8.3 9.7h58.3c6.9 0 12.8-5 13.9-11.8l0.6-3 11-69.7 0.7-3.9c1.1-6.8 7-11.8 13.9-11.8h8.7c56.5 0 100.8-23 113.7-89.4 5.4-27.7 2.6-50.9-11.7-67.2C569.2 120.3 563.8 116.2 557.5 112.9" data-section-outline-level="2" data-section-layout-level="2"></path><path class="middleGray" fill="inherit" d="M543.1 106.8c-2.3-0.7-4.6-1.3-7-1.8 -2.4-0.5-4.9-1-7.4-1.4 -8.9-1.4-18.6-2.1-29-2.1h-87.9c-2.2 0-4.2 0.5-6.1 1.4 -4.1 1.9-7.1 5.8-7.8 10.5l-18.7 118.4 -0.5 3.5c1.2-7.8 7.9-13.5 15.8-13.5h32.9c64.6 0 115.2-26.2 130-102.2 0.4-2.2 0.8-4.4 1.1-6.6 -3.7-2-7.8-3.7-12.2-5.1C545.3 107.4 544.2 107.1 543.1 106.8" data-section-outline-level="2" data-section-layout-level="2"></path><path class="darkGray" fill="inherit" d="M447.9 464.1h-21c-2 0-3.9 1-5 2.7l-29 42.7 -12.3-41c-0.8-2.6-3.1-4.3-5.8-4.3h-20.6c-2.5 0-4.2 2.5-3.4 4.8l23.1 67.9 -21.8 30.7c-1.7 2.4 0 5.7 3 5.7h21c2 0 3.8-1 5-2.6l69.9-100.8C452.6 467.4 450.9 464.1 447.9 464.1M307.5 504.8c-2 11.9-11.5 20-23.6 20 -6.1 0-10.9-2-14-5.6 -3.1-3.7-4.3-8.9-3.3-14.7 1.9-11.8 11.5-20.1 23.4-20.1 5.9 0 10.8 2 13.9 5.7C307.1 493.8 308.4 499 307.5 504.8M336.6 464.1h-20.9c-1.8 0-3.3 1.3-3.6 3.1l-0.9 5.8 -1.5-2.1c-4.5-6.6-14.6-8.8-24.7-8.8 -23.1 0-42.8 17.5-46.7 42 -2 12.2 0.8 23.9 7.8 32.1 6.4 7.5 15.5 10.6 26.3 10.6 18.6 0 28.9-12 28.9-12l-0.9 5.8c-0.3 2.2 1.4 4.2 3.6 4.2h18.8c3 0 5.5-2.2 6-5.1l11.3-71.5C340.5 466.1 338.8 464.1 336.6 464.1M211.2 464.6c-2.4 15.7-14.4 15.7-25.9 15.7h-6.6l4.6-29.3c0.3-1.8 1.8-3.1 3.6-3.1h3c7.9 0 15.3 0 19.2 4.5C211.3 455.1 212 459.1 211.2 464.6M206.1 423.7h-43.6c-3 0-5.5 2.2-6 5.1l-17.6 111.9c-0.3 2.2 1.4 4.2 3.6 4.2h20.8c3 0 5.5-2.2 6-5.1l4.8-30.2c0.5-2.9 3-5.1 6-5.1h13.8c28.8 0 45.3-13.9 49.7-41.5 2-12.1 0.1-21.5-5.6-28.2C231.7 427.6 220.7 423.7 206.1 423.7" data-section-outline-level="2" data-section-layout-level="2"></path><path class="lightGray" fill="inherit" d="M672.5 426.8l-17.9 114c-0.3 2.2 1.4 4.2 3.6 4.2h18c3 0 5.5-2.2 6-5.1L699.9 428c0.3-2.2-1.4-4.2-3.6-4.2h-20.2C674.3 423.8 672.8 425.1 672.5 426.8M618.8 504.8c-2 11.9-11.5 20-23.6 20 -6.1 0-10.9-2-14-5.6 -3.1-3.7-4.3-8.9-3.3-14.7 1.9-11.8 11.5-20.1 23.4-20.1 5.9 0 10.8 2 13.9 5.7C618.4 493.8 619.7 499 618.8 504.8M647.9 464.1H627c-1.8 0-3.3 1.3-3.6 3.1l-0.9 5.8 -1.5-2.1c-4.5-6.6-14.6-8.8-24.7-8.8 -23.1 0-42.8 17.5-46.7 42 -2 12.2 0.8 23.9 7.8 32.1 6.4 7.5 15.5 10.6 26.3 10.6 18.6 0 28.9-12 28.9-12l-0.9 5.8c-0.3 2.2 1.4 4.2 3.6 4.2h18.8c3 0 5.5-2.2 6-5.1l11.3-71.5C651.9 466.1 650.1 464.1 647.9 464.1M522.5 464.6c-2.4 15.7-14.4 15.7-25.9 15.7H490l4.6-29.3c0.3-1.8 1.8-3.1 3.6-3.1h3c7.9 0 15.3 0 19.2 4.5C522.7 455.1 523.4 459.1 522.5 464.6M517.5 423.7h-43.6c-3 0-5.5 2.2-6 5.1l-17.6 111.9c-0.3 2.2 1.4 4.2 3.6 4.2h22.4c2.1 0 3.9-1.5 4.2-3.6l5-31.7c0.5-2.9 3-5.1 6-5.1h13.8c28.8 0 45.3-13.9 49.7-41.5 2-12.1 0.1-21.5-5.6-28.2C543.1 427.6 532.1 423.7 517.5 423.7" data-section-outline-level="2" data-section-layout-level="2"></path></svg>
    </div>
    <div id="vat-description" data-section-outline-level="2" class="VatDescription-3323122897" data-section-layout-level="2">All prices are NET prices.<br> VAT will be added later in the checkout.<br>Tax calculation will be finalised during checkout.</div>
</aside>

</div>

</div>
</div>
</div>

<section class="SubscriberFooter-1037694867">
    <div class="GridLiveArea-2081012695">
        <section data-section-outline-level="3">
            <h3 class="Type-1552298965">{translate key="about.contact.supportContact"}</h3>
            {if !empty($subscriptionName)}
            <p class="u-hide FooterLink-2407621270">{translate key="user.name"}: <b>{$subscriptionName|escape}</b></p>
            {/if}
        	{if !empty($subscriptionEmail)}
        	<p class="FooterLink-2407621270">{translate key="user.email"}: {mailto address=$subscriptionEmail|escape encode="hex"}</p>
        	{else}
        	<p class="FooterLink-2407621270">{translate key="user.email"}: <a href="mailto:journals@sangia.org" data-section-outline-level="3" data-section-layout-level="2">journals@sangia.org</a></p>
        	{/if}
        	{if !empty($subscriptionPhone)}
        	<p class="Type-269378647">{translate key="user.phone"}: {$subscriptionPhone|escape}</p>
        	{else}
        	<p class="Type-269378647">{translate key="user.phone"}: +6285343880383</p>
        	{/if}
        	{if !empty($subscriptionFax)}
        	<p class="Type-269378647">{translate key="user.fax"}: {$subscriptionFax|escape}</p>
        	{/if}
        	{if !empty($subscriptionMailingAddress)}
            <p class="Type-851350800">{translate key="user.mailingAddress"}:<br />{$subscriptionMailingAddress|strip_tags|escape|nl2br}</p>
            {/if}
            <p data-section-outline-level="3" class="Type-851350800" data-section-layout-level="2">Hours: 24 hours a day, 7 days a week</p>
        </section>
        <section data-section-outline-level="3" data-section-layout-level="2">
            <h3 data-section-outline-level="3" class="Type-1552298965" data-section-layout-level="2">{translate key="gifts.purchaseGiftSubscription"}</h3>
            {if $acceptGiftSubscriptionPayments}
            <p data-section-outline-level="3" class="Type-2407621270" data-section-layout-level="2">{translate key="gifts.giftSubscriptionsAvailable"}</p>
            {else}
            <p data-section-outline-level="3" class="Type-2407621270" data-section-layout-level="2">Gift subscriptions are available for any of the Sangia Research Media & Publishing journals.</p>
            {/if}
            <p data-section-outline-level="3" class="FooterLink-269378647" data-section-layout-level="2">Find out more: <a href="{url page="gifts" op="purchaseGiftSubscription"}" data-section-outline-level="3" data-section-layout-level="2">sangia.org/gifts</a></p>
        </section>
        <section data-section-outline-level="3" data-section-layout-level="2">
            <h3 data-section-outline-level="3" class="Type-1552298965" data-section-layout-level="2">Foreign transaction fees</h3>
            {if !empty($subscriptionAdditionalInformation)}
            <p data-section-outline-level="3" class="Type-2407621270" data-section-layout-level="2">{$subscriptionAdditionalInformation|nl2br}</p>
            {else}
            <p data-section-outline-level="3" class="Type-2407621270" data-section-layout-level="2">If you use a debit or credit card to pay for Sangia Publishing subscriptions you may be charged a Foreign Purchase Fee by your bank or card issuer.</p>
            {/if}
            <p data-section-outline-level="3" class="FooterLink-269378647" data-section-layout-level="2">More information: <a href="#" data-section-outline-level="3" data-section-layout-level="2">sangia.org/fees</a></p>
        </section>
    
    </div>
</section>

{include file="common/footer.tpl"}
