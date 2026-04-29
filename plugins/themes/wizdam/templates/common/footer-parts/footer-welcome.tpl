{**
 * templates/common/footer.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site footer.
 *
 *}

                </div>
            </div>
        </div>
    </div>
</div>

<footer class="composite-layer u-margin-l-top u-margin-xl-top-from-sm u-margin-l-top-from-md" role="contentinfo">
    
    {if $homepageImage && !$setupStep}
    <div class="u-mt-16 u-mb-16">
        <div class="u-container">
            <p class="c-meta u-ma-0">Cover image: {if $homepageImageAltText != ''}{$homepageImageAltText|escape}{else}{translate key="common.journalHomepageImage.altText"}{/if}</p>
        </div>
    </div>
    {/if}

    <div class="u-mt-16 u-mb-16">
        <div class="u-container">
            <div class="u-display-flex u-flex-wrap u-justify-content-space-between">
                {if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
                    {if $homepageImage && !$setupStep}
                    <p class="c-meta u-ma-0 u-mr-24 u-flex-shrink">Issue cover: {if $issue->getLocalizedCoverPageDescription()}{$issue->getLocalizedCoverPageDescription()|strip_unsafe_html|nl2br}{else}SRM Publishing/Sangia Publishing{/if}</p>
                    {/if}
                {/if}
                <p class="c-meta u-ma-0 u-flex-shrink">
                    <span class="c-meta__item">{if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{/if} {if $currentJournal->getSetting('abbreviation')}(<i>{$currentJournal->getSetting('abbreviation', $currentJournal->getPrimaryLocale())}</i>){/if}</span>
                    {strip}
                    {if $currentJournal && $currentJournal->getSetting('onlineIssn')}{assign var=issn value=$currentJournal->getSetting('onlineIssn')}{elseif $currentJournal && $currentJournal->getSetting('printIssn')}{assign var=issn value=$currentJournal->getSetting('printIssn')}
                    {/if}
                    {if $displayCreativeCommons}{translate key="common.ccLicense"}{/if}
                    {/strip}
                    {if $printIssn}{else if $onlineIssn}{if $currentJournal->getSetting('printIssn')}<span class="c-meta__item"><abbr title="International Standard Serial Number">ISSN</abbr> <span itemprop="printIssn">{$currentJournal->getSetting('printIssn')}</span> (print)</span>{/if}<span class="c-meta__item"><abbr title="International Standard Serial Number">ISSN</abbr> {if $currentJournal->getSetting('onlineIssn')}<span itemprop="onlineIssn">{$currentJournal->getSetting('onlineIssn')}</span>{else} <i>on proccess</i>{/if} (online)</span>
                    {/if}
                </p>
            </div>
        </div>
    </div>
    
    <div id="footer" itemscope="" itemtype="http://schema.org/Periodical">
    <div class="sangia-legal">
        <div id="footer-legal" role="contentinfo">
            <h2 aria-level="2" class="u-visually-hidden">sangia.org sitemap</h2>
            <div id="pageFooter" class="srm-footer text-xs u-padding-s-hor u-padding-m-hor-from-sm u-padding-s-right-from-md u-padding-s-left-from-md u-padding-l-ver">
                <div class="srm-footer-sangia u-margin-m-bottom u-margin-0-bottom-from-md u-margin-s-right u-margin-m-right-from-md u-margin-l-right-from-lg"><img src="//www.assets.sangia.org/img/sangia-mono-branded-72x89-v2.svg" loading="lazy" alt="Sangia Publishing Group" width="68" height="78" /></div>
                <div id="standardFooter" class="srm-footer-content u-margin-l-right-from-lg u-margin-m-right-from-md">
                    <div  class="u-hide u-remove-if-print"><div class="legal" role="contentinfo">Sangia Research Media & Publishing</div></div>
                    <p class="">We use cookies to enhance our service and ads. By using this website, you agree to our <a class="anchor" href="/ISLE/pages/view/Terms%20and%20Conditions"><span class="anchor-text">Terms and Conditions</span></a>, <a class="anchor" href="{url page="about" anchor="privacyStatement"}"><span class="anchor-text">{translate key="about.privacyStatement"}</span></a> and <a class="anchor" href="/ISLE/pages/view/Cookies"><span class="anchor-text">Cookies</span></a> policy.</p>
                    {if $currentJournal && $currentJournal->getSetting('onlineIssn')}
                    	{assign var=issn value=$currentJournal->getSetting('onlineIssn')}
                    {elseif $currentJournal && $currentJournal->getSetting('printIssn')}
                    	{assign var=issn value=$currentJournal->getSetting('printIssn')}
                    {/if}            
                    <p class="srm-lisensing u-hide" style="margin-bottom: 0;">{$currentJournal->getLocalizedTitle()|strip_tags|escape} {if $printIssn}{else if $onlineIssn}{if $currentJournal->getSetting('printIssn')}ISSN: <a class="anchor" href="//portal.issn.org/resource/ISSN/{$currentJournal->getSetting('printIssn')}" target="_blank"><span class="anchor-text">{$currentJournal->getSetting('printIssn')}</span></a> (Print) {/if}ISSN: {if $currentJournal->getSetting('onlineIssn')}<a class="anchor" href="//portal.issn.org/resource/ISSN/{$currentJournal->getSetting('onlineIssn')}" target="_blank"><span class="anchor-text">{$currentJournal->getSetting('onlineIssn')}</span></a> (Online){else}on proccess (Online){/if}. {/if}
                    {translate|assign:"applicationName" key="common.openJournalSystems"}
                    <span class="srm-lisensing">Powered by <a class="anchor" href="//pkp.sfu.ca/ojs/" target="_blank"><span class="anchor-text">{$applicationName}</span></a> and <a class="anchor" href="//github.com/masonpublishing/OJS-Theme" target="_blank"><span class="anchor-text">Mason Publishing</span></a> theme.</span></p>
                    <p class="sangia-footer-copyright u-mb-0" style="margin-bottom:0">Copyright © 2017-{$smarty.now|date_format:"%Y"} <a class="anchor" href="//www.publishing.sangia.org" target="_blank"><span class="anchor-text">Sangia Publishing</span></a> unless otherwise stated. Part of <a class="anchor" href="//www.insw.go.id/nib" target="_blank"><span class="anchor-text">Sangia Research Media and Publishing</span></a> | NIB: 1111220205313.</p>
                    <p class="sangia-footer-legal">Dirjen AHU No. <span class="anchor"><span class="anchor-text">AHU-050003.AH.01.30.Tahun 2022</span></span>. Certificate No. <span class="anchor"><span class="anchor-text">11112202053130002</span></span>.</p>
                    <p class="footer-section anchor">{$pageFooter}</p>       
                    <p id="diagnostic-info" class="footer-section">
                        <span id="diagnostic-login-status">{if $isUserLoggedIn}You logged in{else}Not logged in{/if}</span>
                        <span class="diagnostic-business-partners">{if $isUserLoggedIn}{if $hasOtherJournals}Affiliated{/if}{else}Unaffiliated{/if}</span>
                        <span id="diagnostic-ip" class="ip_diagnostic">Fetching Your IP address...</span>
                    </p>   
                </div>
                <div class="u-margin-0-top u-margin-m-top-from-xs u-margin-0-top-from-md"><a aria-label="SRM home page (opens in a new tab)"  href="//www.sangia.org/" target="_blank" rel="nofollow"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="93px" height="22px" viewBox="0 0 93 22" enable-background="new 0 0 93 22" xml:space="preserve"><image id="image0" width="93" height="22" x="0" y="0" href="//assets.sangia.org/image/sangia.png"/></svg></a>
                </div>
            </div>
        </div>
    </div>    

        {call_hook name="Templates::Common::Footer::PageFooter"}

    </div>
</footer>
</div>

    <button aria-label="Feedback" type="button" id="_pendo-badge" data-layout="badgeBlank" style="z-index: 19000; margin: 0px; height: 32px; width: 128px; font-size: 0px; background: rgba(255, 255, 255, 0); padding: 0px; line-height: 1; min-width: auto; box-shadow: rgb(136, 136, 136) 0px 0px 0px 0px; border: 0px; float: none; vertical-align: baseline; cursor: pointer;   top: auto !important;left: auto !important;bottom: 0px !important;right: 110px !important;position: fixed !important;" class="u-hide _pendo-badge _pendo-badge_"><img id="pendo-image-badge-270ed9e9" style="display: block; height: 32px; width: 128px; box-shadow: rgb(136, 136, 136) 0px 0px 0px 0px; float: none; vertical-align: baseline;" src="https://pendo-static-5661679399600128.storage.googleapis.com/D_T2uHq_M1r-XQq8htU6Z3GjHfE/guide-media-4ffbc674-4fbf-417f-ba24-e81dba0953d6" alt="Feedback" data-_pendo-image-1="" class="_pendo-image _pendo-badge-image"></button>
    
    <button class="StickySideButton StickySideButton--feedback" onclick="FreshWidget.show();">Support<img alt="" src="//www.assets.sangia.org/image/classical/freshdesk-support.png"></button>
        
    <!-- Google Analytics -->
    <script type="text/plain" cookie-consent="tracking" async src="https://www.googletagmanager.com/gtag/js?id=UA-110581662-2"></script>
    <!-- end of Google Analytics-->

    <!-- Back to top -->
    <script src="{$baseUrl}/plugins/themes/stipwunaraha/js/lazyload.js"></script>
    <script src="{$baseUrl}/plugins/themes/stipwunaraha/js/auth-forms.js"></script>
    <script src="{$baseUrl}/plugins/themes/stipwunaraha/js/common.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        
    {get_debug_info}
    {if $enableDebugStats}{include file=$pqpTemplate}{/if}
        
</body>
</html>
