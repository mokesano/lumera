{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
<banner class="u-hide panel-s info-banner">
    <div class="alert alert-container">
        <span class="alert-icon-box u-bg-info-blue"><svg focusable="false" viewBox="0 0 16 128" width="24" height="24" class="icon icon-information u-fill-white"><path d="m2.72 42.24h9.83l0.64 59.06h-11.15l0.63-59.06zm-1.97-19.02c0-3.97 3.25-7.22 7.22-7.22 3.98 0 7.23 3.25 7.23 7.22 0 3.98-3.25 7.23-7.23 7.23-2.42 0-4.57-1.19-5.85-3.02-0.87-1.19-1.37-2.65-1.37-4.21z"></path></svg></span>
        <span class="alert-text">
            <span class="text-s">Selected articles from this journal and other medical research on<b> Novel Coronavirus (SARS-Cov-2) </b>and related viruses are now available for free on GoogleScholar – <a class="anchor" href="//scholar.google.com/scholar?q=SARS-Cov-2" target="_blank"><span class="anchor-text">start exploring directly</span></a> or visit the <a class="anchor" href="https://www.elsevier.com/connect/coronavirus-information-center" target="_blank"><span class="anchor-text">Elsevier Novel Coronavirus Information Center</span></a></span>
        </span>
    </div>
</banner>
{/if}

<sangia-header class="sangia-header bolmHa"><!-- editing -->
<div class="journal-header" style="transform: translateZ(0px);" role="banner">
    <section {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}class="sc-1oj9st5-1 kSjVSRM" style="background-image: url('{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}');"{else}class="sc-1oj9st5-1 fQjVRM" style="background-image: rgb(85, 187, 221);"{/if}>
        <div {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}class="sc-thb58v-10 spg-1oj9st5"{else}class="sc-thb58v-10 xEmXg"{/if}>
            <div class="row sc-thb58v-0 iLjulU text-s">
                <div class="sc-thb58v-2 ixNkIC">
                    <div class="sc-thb58v-3 fkokXa">
                        <h1 class="sc-thb58v-4 kGdUpp js-title-text">
                            <a id="journal-title" class="js-title-link anchor-has-background-color anchor-has-inherit-color" href="{url page="$currentJournal"}">
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
                        
                        <div class="open-statement sc-thb58v-5 js-open-statement">
                            <div class="open-statement-item u-display-inline-block">
                                {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}
                                <a class="js-open-access-link u-clr-blue" id="openStatement-supports" href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}">
                                    <span class="js-open-statement-text publishing-type open-statement-text">
                                        <span class="u-text-italic open-access">Open access</span>
                                    </span>
                                </a>
                                {elseif $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
                                <a class="js-open-access-link u-clr-blue" id="openStatement-supports" href="{url page="about" op="editorialPolicies" anchor="delayedOpenAccessPolicy"}">
                                    <span class="js-open-statement-text publishing-type open-statement-text">
                                            Supports <span class="u-text-italic open-access">open access</span>
                                    </span>
                                </a>
                                {/if}                                    
                            </div>
                        </div>
                    </div>
                    <div class="sc-thb58v-7 keEZHq">
                        {if $alternatePageHeader}
                        <div class="sc-thb58v-8 cxpGrv iiSeIx js-cite-score metric">
                            <button class="button-link button-link button-link-secondary u-text-left" type="button">
                                <span class="button-link-text">
                                    <span class="text-l u-display-block">{$alternatePageHeader}</span>
                                    <span class="text-xs __info" title="Sinta: Science and Technology Index by Ristek/BRIN Indonesia">SintaScore</span>
                                </span>
                            </button>
                        </div>
                        {/if}
                        <div class="sc-thb58v-8 cxpGrv iiSeIx sc-thb58v-9 jrMqZ js-impact-factor metric">
                            <button class="button-link button-link button-link-secondary u-text-left" type="button">
                                <span class="button-link-text">
                                    <span class="text-l u-display-block">5.678</span>
                                    <span class="text-xs __info" title="Sinta IF by Science and Technology Index Ristek/BRIN Indonesia">Sinta Impact</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>                
                <div class="sc-thb58v-1 iChZEk">
                    <a class="anchor js-cover-image-link" href="{url page="$currentJournal"}">
                        {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
                        {assign var="displayJournalThumbnail" value=$currentJournal->getLocalizedSetting('journalThumbnail')}
                        {if $displayHomepageImage && is_array($displayHomepageImage)}
                        <span class="anchor-text">
                            <picture>
                                <source srcset="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp" type="image/webp">
                                <img loading="lazy" class="cover-image-large cover-image js-cover-image" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" alt="Go to journal home page - {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{elseif $displayPageHeaderTitle}{$displayPageHeaderTitle}{elseif $siteTitle}{$siteTitle}{else}{$applicationName}{/if}" style="max-height: 20rem;" />
                            </picture>
                        </span>
                        {elseif $displayJournalThumbnail && is_array($displayJournalThumbnail)}
                        <span class="anchor-text">
                            <picture>
                                <source srcset="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}?as=webp" type="image/webp">
                                <img loading="lazy" class="cover-image-large cover-image js-cover-image" src="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}" alt="Go to journal home page - {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{elseif $displayPageHeaderTitle}{$displayPageHeaderTitle}{elseif $siteTitle}{$siteTitle}{else}{$applicationName}{/if}" style="max-height: 20rem;" />
                            </picture>
                        </span>
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
</sangia-header>

<div class="u-hide journal-header" role="banner" loading="lazy" {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)} style="background-image: url('{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}')"{else} style="background-color: #555;"{/if}>
    <div class="journal-stage">
        <div class="live-area row">
            <div class="column">
                <h1 class="kGdUpp js-title-text" {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}style="color:#2b2b2b;"{/if}><a class="js-title-link anchor-has-background-color anchor-has-inherit-color" href="{url page="$currentJournal"}"><span class="anchor-text">{if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}
                    {elseif $displayPageHeaderTitle}
                        {$displayPageHeaderTitle}
                    {elseif $siteTitle}
                        {$siteTitle}
                    {else}
                        {$applicationName}
                    {/if}
                    </span></a>
                    <div class="publishing u-font-sans anchor">
                    {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
                        <span class="open-statement-text anchor-text">Supports <em class="open-access">open access</em></span>
                    {elseif $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}
                        <span class="open-statement-text anchor-text"><em class="open-access">Open access</em></span>
                    {/if}    
                    </div>                    
                </h1>
                {if $alternatePageHeader}
                <div class="button-link button-link button-link-secondary score" title="Sinta: Science and Technology Index by Ristek/BRIN">
                    <span class="score__value">{$alternatePageHeader}</span>
                    <span class="button-link-text __info">Sinta Score</span>
                </div>
                {/if}
            </div>
    	</div>
    </div>
</div>

<div class="u-hide journal-header" role="banner" loading="lazy" {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)} style="background-image: url('{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}')"{else} style="background-color: #555;"{/if}>
    <div class="journal-stage">
        <div class="live-area row">
            <div class="iLjulU">
                <div class="sc-thb58v-2 ixNkIC">
                    <div class="sc-thb58v-3 fkokXa">
                        <h1 class="kGdUpp js-title-text" {if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}style="color:#2b2b2b;"{/if}><a class="js-title-link anchor-has-background-color anchor-has-inherit-color" href="{url page="$currentJournal"}"><span class="anchor-text">{if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}
                            {elseif $displayPageHeaderTitle}
                                {$displayPageHeaderTitle}
                            {elseif $siteTitle}
                                {$siteTitle}
                            {else}
                                {$applicationName}
                            {/if}
                            </span></a>
                        </h1>
                        <div class="open-statement sc-thb58v-5 js-open-statement">
                            <div class="publishing u-font-sans anchor u-display-inline-block">
                            {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
                                <span class="js-open-statement-text publishing-type open-statement-text">Supports <em class="open-access">open access</em></span>
                            {elseif $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}
                                <span class="js-open-statement-text publishing-type open-statement-text"><em class="open-access">Open access</em></span>
                            {/if}
                            </div>
                        </div>
                    </div>
                    
                    {if $alternatePageHeader}
                    <div class="sc-thb58v-7 keEZHq">
                        <div class="sc-thb58v-8 cxpGrv iiSeIx js-cite-score metric">
                            <div class="button-link button-link button-link-secondary" title="Sinta: Science and Technology Index by Ristek/BRIN">
                                <span class="score__value">{$alternatePageHeader}</span>
                                <span class="button-link-text __info">Sinta Score</span>
                            </div>
                        </div>
                    </div>
                    {/if}
                    
                </div>

                <div class="sc-thb58v-1 iChZEk">
                    <a class="anchor js-cover-image-link" href="{url page="$currentJournal"}">
                        {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
                        {assign var="displayJournalThumbnail" value=$currentJournal->getLocalizedSetting('journalThumbnail')}
                        {if $displayHomepageImage && is_array($displayHomepageImage)}
                        <div class="anchor-text">
                            <picture>
                                <source srcset="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}?as=webp" type="image/webp">
                                <img loading="lazy" class="cover-image-large cover-image js-cover-image" src="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" alt="Go to journal home page - {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{elseif $displayPageHeaderTitle}{$displayPageHeaderTitle}{elseif $siteTitle}{$siteTitle}{else}{$applicationName}{/if}" style="max-height: 25rem;" />
                            </picture>
                        </div>
                        {elseif $displayJournalThumbnail && is_array($displayJournalThumbnail)}
                        <div class="anchor-text">
                            <picture>
                                <source srcset="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}?as=webp" type="image/webp">
                                <img loading="lazy" class="cover-image-large cover-image js-cover-image" src="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}" alt="Go to journal home page - {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{elseif $displayPageHeaderTitle}{$displayPageHeaderTitle}{elseif $siteTitle}{$siteTitle}{else}{$applicationName}{/if}" style="max-height: 25rem;" />
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
    </div>
</div>
