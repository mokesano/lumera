<div class="sangia-header bolmHa"><!-- editing -->

{if $currentJournal}
<div class="journal-header" style="transform: translateZ(0px);" role="banner">
    <section {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}class="lazyload sc-1oj9st5-1 kSjVSRM" style="background-image: url('{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}');"{else}class="lazyload sc-1oj9st5-1 fQjVRM" style="background-image: rgb(85, 187, 221);"{/if}>
        <div {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}class="sc-thb58v-10 spg-1oj9st5"{else}class="sc-thb58v-10 xEmXg"{/if}>
            <div class="sc-thb58v-0 iLjulU text-s">
                <div class="sc-thb58v-2 ixNkIC">
                    <div class="sc-thb58v-3 fkokXa">
                        <h1 class="sc-thb58v-4 kGdUpp js-title-text u-text-light u-h2" style="color: rgb(0, 0, 0);">
                            <a class="js-title-link anchor-has-background-color anchor-has-inherit-color" href="{url page="$currentJournal"}" id="journal-title">
                                <span class="anchor-text">
                                {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}
                                {elseif $displayPageHeaderTitle}
                                    {$displayPageHeaderTitle}
                                {elseif $siteTitle}
                                    {$siteTitle}
                                {else}
                                    {$applicationName}
                                {/if}    
                                </span>
                            </a>
                        </h1>
                        <div class="open-statement sc-thb58v-5 js-open-statement" style="color: rgb(0, 0, 0);">
                            <div class="open-statement-item u-display-inline-block">
                                {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}
                                <a class="js-open-access-link u-clr-blue" id="openStatement-supports" href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}">
                                    <span class="js-open-statement-text publishing-type open-statement-text" style="color: rgb(0, 0, 0);">
                                        <span class="u-text-italic open-access">Open access</span>
                                    </span>
                                </a>
                                {elseif $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
                                <a class="js-open-access-link u-clr-blue" id="openStatement-supports" href="{url page="about" op="editorialPolicies" anchor="delayedOpenAccessPolicy"}">
                                    <span class="js-open-statement-text publishing-type open-statement-text" style="color: rgb(0, 0, 0);">
                                            Supports <span class="u-text-italic open-access">open access</span>
                                    </span>
                                </a>
                                {/if}                                    
                            </div>
                        </div>
                    </div>
                    <div class="sc-thb58v-7 keEZHq">
                        <!-- Mendapatkan ISSN cetak dan online dari Smarty -->
                        <input type="hidden" id="printIssn" value="{$currentJournal->getSetting('printIssn')}">
                        <input type="hidden" id="eIssn" value="{$currentJournal->getSetting('onlineIssn')}">
                        {if $alternatePageHeader}
                        <div class="tooltip__Wrapper-sc-1lc2ea0-0 sc-thb58v-8 cxpGrv iiSeIx js-sinta-score metric">
                            <button class="button-link button-link button-link-secondary u-text-left" type="button" aria-label="SintaScore: Sinta Impact Value" aria-expanded="false" aria-haspopup="false">
                                <span class="button-link-text">
                                    <span style="color: rgb(0, 0, 0);" class="text-l u-display-block">{$alternatePageHeader}</span>
                                    <span style="color: rgb(0, 0, 0);" class="text-xs __info" aria-title="Sinta: Science and Technology Index by Kemdikbud & Ristek Indonesia">SintaScore</span>
                                </span>
                            </button>
                            <div id="popover-content-metric-popover-DaysPerReview" class="popover-content popover-align-right heading_inline u-js-hide" style="width: 350px; opacity: 1; transform: translateY(0px); transition: opacity 0.25s, transform 0.25s;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">SintaScore is an impact indicator sourced from SINTA (Science and Technology Index), a national research evaluation platform managed by the Ministry of Higher Education, Sains, and Technology of the Republic of Indonesia. While SINTA aggregates data on Indonesian journals, the exact formula behind SintaScore remains a well-kept secret — known only to its creators and perhaps, to God.</div></div>
                            </div>
                        </div>
                        {/if}
                        <div class="tooltip__Wrapper-sc-1lc2ea0-0 sc-thb58v-8 cxpGrv iiSeIx sc-thb58v-9 jrMqZ js-sinta-grade metric u-js-hide">
                            <button class="button-link button-link button-link-secondary u-text-left" type="button" aria-label="National Grade Accredited" aria-expanded="false" aria-haspopup="false">
                                <span class="button-link-text">
                                    <span style="color: rgb(0, 0, 0);" class="text-l u-display-block">Grade</span>
                                    <span style="color: rgb(0, 0, 0);" class="text-xs __info" aria-title="Score from Sinta: Science and Technology Index by Kemdikbud & Ristek Indonesia">SintaGrade</span>
                                </span>
                            </button>
                            <div id="popover-content-metric-popover-DaysPerReview" class="popover-content popover-align-right heading_inline u-js-hide" style="width: 350px; opacity: 1; transform: translateY(0px); transition: opacity 0.25s, transform 0.25s;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">The number Grade is National Level Accreditation Rating by ARJUNA: Akreditasi Jurnal National. Accreditation ranking results are displayed by SINTA: Science and Technology Index by Kemdikti Saintek, Republic of Indonesia as the only journal ranking agency in Indonesia.</div></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sc-thb58v-1 iChZEk">
                    <a class="anchor js-cover-image-link" href="{url page="$currentJournal"}">
                        {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
                        {assign var="displayJournalThumbnail" value=$currentJournal->getLocalizedSetting('journalThumbnail')}
                        {if $displayHomepageImage && is_array($displayHomepageImage)}
                        <div class="anchor-text">
                            <picture>
                                <source srcset="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp" type="image/webp">
                                <img loading="lazy" class="lazyload cover-image-large cover-image js-cover-image" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" alt="Go to journal home page - {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{elseif $displayPageHeaderTitle}{$displayPageHeaderTitle}{elseif $siteTitle}{$siteTitle}{else}{$applicationName}{/if}" style="max-height: 11rem;" />
                            </picture>
                        </div>
                        {elseif $displayJournalThumbnail && is_array($displayJournalThumbnail)}
                        <div class="anchor-text">
                            <picture>
                                <source srcset="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}?as=webp" type="image/webp">
                                <img loading="lazy" class="lazyload cover-image-large cover-image js-cover-image" src="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}" alt="Go to journal home page - {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{elseif $displayPageHeaderTitle}{$displayPageHeaderTitle}{elseif $siteTitle}{$siteTitle}{else}{$applicationName}{/if}" style="max-height: 11rem;" />
                            </picture>
                        </div>
                        {else}                            
                        <div class="fallback-cover-large fallback-cover u-bg-grey7 js-fallback-cover">
                            <div class="fallback-cover-content">
                                <p class="text-m u-clr-white js-fallback-cover-text u-font-sans">
                                    {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}
                                    {elseif $displayPageHeaderTitle}
                                        {$displayPageHeaderTitle}
                                    {elseif $siteTitle}
                                        {$siteTitle}
                                    {else}
                                        {$applicationName}
                                    {/if}                            
                                </p>
                            </div>
                        </div>
                        {/if}
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
</div><!-- editing -->
{/if}
