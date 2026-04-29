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
        <div class="c-footer">
            <div class="u-container">
                <h2 aria-level="2" class="u-visually-hidden">sangia.org sitemap</h2>
                <div class="u-hide-print" data-track-component="footer" style="">
                    <div class="c-footer__header">
                        <div class="c-footer__logo"><img loading="lazy" alt="Sangia publishing" src="//assets.sangia.org/img/sangia-future-branded-v2.svg" width="200" height="31">
                        </div>
                        <ul class="c-menu c-menu--inherit u-mr-32">
                            <li class="c-menu__item"><a class="c-menu__link" href="https://sangia.org/gp/company_info/about" data-track="click" data-track-action="about us" data-track-label="link" target="_blank">About us</a></li>
                            <li class="u-hide c-menu__item"><a class="c-menu__link" href="https://sangia.org/gp/press_room/press_releases" data-track="click" data-track-action="press releases" data-track-label="link" target="_blank">Press releases</a></li>
                            <li class="u-hide c-menu__item"><a class="c-menu__link" href="https://press.sangia.org/" data-track="click" data-track-action="press office" data-track-label="link" target="_blank">Press office</a></li>
                            <li class="c-menu__item"><a class="c-menu__link" href="https://support.sangia.org/support/home" data-track="click" data-track-action="contact us" data-track-label="link" target="_blank">Contact us</a></li>
                        </ul>
                        <ul class="c-menu c-menu--inherit">
                            <li class="c-menu__item">
                                <a class="c-menu__link" href="//www.linkedin.com/company/68901582" aria-label="Linkedln Sangia Research" data-track="click" data-track-action="linkedln" data-track-label="link" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 34 34" class="u-icon u-mt-2 u-mb-2"><g><path d="M34,2.5v29A2.5,2.5,0,0,1,31.5,34H2.5A2.5,2.5,0,0,1,0,31.5V2.5A2.5,2.5,0,0,1,2.5,0h29A2.5,2.5,0,0,1,34,2.5ZM10,13H5V29h5Zm.45-5.5A2.88,2.88,0,0,0,7.59,4.6H7.5a2.9,2.9,0,0,0,0,5.8h0a2.88,2.88,0,0,0,2.95-2.81ZM29,19.28c0-4.81-3.06-6.68-6.1-6.68a5.7,5.7,0,0,0-5.06,2.58H17.7V13H13V29h5V20.49a3.32,3.32,0,0,1,3-3.58h.19c1.59,0,2.77,1,2.77,3.52V29h5Z" fill="currentColor"></path></g></svg>
                                </a>
                            </li>                            
                            <li class="c-menu__item">
                                <a class="c-menu__link" href="//www.facebook.com/sangiapublishing/" aria-label="Facebook" data-track="click" data-track-action="facebook" data-track-label="link" target="_blank">
                                    <svg class="u-icon u-mt-2 u-mb-2" role="img" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20"><path d="M2.5 20C1.1 20 0 18.9 0 17.5v-15C0 1.1 1.1 0 2.5 0h15C18.9 0 20 1.1 20 2.5v15c0 1.4-1.1 2.5-2.5 2.5h-3.7v-7.7h2.6l.4-3h-3v-2c0-.9.2-1.5 1.5-1.5h1.6V3.1c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H8.1v3h2.6V20H2.5z"></path></svg>
                                </a>
                            </li>
                            <li class="c-menu__item">
                                <a class="c-menu__link" href="//twitter.com/SangiaNews?lang=en" aria-label="Twitter" data-track="click" data-track-action="twitter" data-track-label="link" target="_blank">
                                    <svg class="u-icon" role="img" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M17.6 4.1c.8-.5 1.5-1.4 1.8-2.4-.8.5-1.7.9-2.6 1-.7-.8-1.8-1.4-3-1.4-2.3 0-4.1 1.9-4.1 4.3 0 .3 0 .7.1 1-3.4 0-6.4-1.8-8.4-4.4C1 2.9.8 3.6.8 4.4c0 1.5.7 2.8 1.8 3.6C2 8 1.4 7.8.8 7.5v.1c0 2.1 1.4 3.8 3.3 4.2-.3.1-.7.2-1.1.2-.3 0-.5 0-.8-.1.5 1.7 2 3 3.8 3-1.3 1.1-3.1 1.8-5 1.8-.3 0-.7 0-1-.1 1.8 1.2 4 1.9 6.3 1.9C13.8 18.6 18 12 18 6.3v-.6c.8-.6 1.5-1.4 2-2.2-.7.3-1.5.5-2.4.6z"></path></svg>
                                </a>
                            </li>
                            <li class="c-menu__item">
                                <a class="c-menu__link" href="//web.telegram.org/k/#@sangiapublishing" aria-label="Telegram" data-track="click" data-track-action="telegram" data-track-label="link" target="_blank">
                                    <svg fill="#d5d5d5" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" stroke="#d5d5d5" height="18"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M29.919 6.163l-4.225 19.925c-0.319 1.406-1.15 1.756-2.331 1.094l-6.438-4.744-3.106 2.988c-0.344 0.344-0.631 0.631-1.294 0.631l0.463-6.556 11.931-10.781c0.519-0.462-0.113-0.719-0.806-0.256l-14.75 9.288-6.35-1.988c-1.381-0.431-1.406-1.381 0.288-2.044l24.837-9.569c1.15-0.431 2.156 0.256 1.781 2.013z"></path> </g></svg>
                                </a>
                            </li>
                            <li class="c-menu__item">
                                <a class="c-menu__link" href="//whatsapp.com/channel/0029VaHRmFzIt5s3NMnJkP3B" aria-label="WhatsApp" data-track="click" data-track-action="whatsapp" data-track-label="link" target="_blank">
                                    <svg viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#ffffff" stroke="#ffffff" style="vertical-align: -0.125em;-ms-transform: rotate(360deg);-webkit-transform: rotate(360deg);transform: rotate(360deg);" width="1em" height="1em"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>whatsapp [#128]</title> <desc>Created with Sketch.</desc> <defs> </defs> <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Dribbble-Light-Preview" transform="translate(-300.000000, -7599.000000)" fill="#d5d5d5"> <g id="icons" transform="translate(56.000000, 160.000000)"> <path d="M259.821,7453.12124 C259.58,7453.80344 258.622,7454.36761 257.858,7454.53266 C257.335,7454.64369 256.653,7454.73172 254.355,7453.77943 C251.774,7452.71011 248.19,7448.90097 248.19,7446.36621 C248.19,7445.07582 248.934,7443.57337 250.235,7443.57337 C250.861,7443.57337 250.999,7443.58538 251.205,7444.07952 C251.446,7444.6617 252.034,7446.09613 252.104,7446.24317 C252.393,7446.84635 251.81,7447.19946 251.387,7447.72462 C251.252,7447.88266 251.099,7448.05372 251.27,7448.3478 C251.44,7448.63589 252.028,7449.59418 252.892,7450.36341 C254.008,7451.35771 254.913,7451.6748 255.237,7451.80984 C255.478,7451.90987 255.766,7451.88687 255.942,7451.69881 C256.165,7451.45774 256.442,7451.05762 256.724,7450.6635 C256.923,7450.38141 257.176,7450.3464 257.441,7450.44643 C257.62,7450.50845 259.895,7451.56477 259.991,7451.73382 C260.062,7451.85686 260.062,7452.43903 259.821,7453.12124 M254.002,7439 L253.997,7439 L253.997,7439 C248.484,7439 244,7443.48535 244,7449 C244,7451.18666 244.705,7453.21526 245.904,7454.86076 L244.658,7458.57687 L248.501,7457.3485 C250.082,7458.39482 251.969,7459 254.002,7459 C259.515,7459 264,7454.51465 264,7449 C264,7443.48535 259.515,7439 254.002,7439" id="whatsapp-[#128]"> </path> </g> </g> </g> </g></svg>
                                </a>
                            </li>
                            <li class="c-menu__item">
                                <a class="c-menu__link" href="//www.youtube.com/channel/UCAx2FDkLH77Phh5zRSIVRfw" aria-label="YouTube" data-track="click" data-track-action="youtube" data-track-label="link" target="_blank">
                                    <svg class="u-icon" role="img" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M7.9 12.6V6.9l5.4 2.8c0 .1-5.4 2.9-5.4 2.9zM19.8 6s-.2-1.4-.8-2c-.8-.8-1.6-.8-2-.9-2.8-.2-7-.2-7-.2s-4.2 0-7 .2c-.4 0-1.2 0-2 .9-.6.6-.8 2-.8 2S0 7.6 0 9.2v1.5c0 1.7.2 3.3.2 3.3s.2 1.4.8 2c.8.8 1.8.8 2.2.9 1.6.1 6.8.2 6.8.2s4.2 0 7-.2c.4 0 1.2-.1 2-.9.6-.6.8-2 .8-2s.2-1.6.2-3.3V9.2c0-1.6-.2-3.2-.2-3.2z"></path></svg>
                                </a>
                            </li>
                        </ul>
                    </div>

        <div id="footer-nav" class="section">
        <div id="footer-nav-misc" class="text-xs row">
            <div class="large-3 medium-4 column" role="navigation">
                <h6>Explore contents</h6>
                <ul >
                    <li ><a href="https://blogs.stipwunaraha.ac.id/">Read more on blogs</a></li>
                    <li ><a href="/ISLE/pages/view/authorship-guidelines">Guide for Author</a></li>
                    <li ><a title="Browse Journals" href="{$baseUrl}" target="_blank">View Journals</a></li>
                    <li ><a title="View Journal by Subject" href="{$baseUrl}/index/search/categories" target="_blank">Journals Subjects</a></li>
                    <li ><a title="View All Articles" href="{$baseUrl}/index/search/titles" target="_blank">All Articles</a></li>
                    <li ><a title="View Authors Index" href="{$baseUrl}/index/search/authors" target="_blank">Authors Index</a></li>
                    {if $currentJournal->getLocalizedSetting('privacyStatement') != ''}
                    <li ><a href="{url page="about" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></li>{/if}
                </ul>
            </div>            
         
            <div class="large-3 medium-4 column" role="navigation">
                <h6>Information</h6>
                <ul >
                    <li class=" "><a href="{url page="information" op="authors"}">{translate key="navigation.infoForAuthors.long"}</a></li>
                    <li ><a href="/ISLE/pages/view/guide-for-registration">Registration Guide</a></li>
                    <li ><a href="/ISLE/pages/view/publication-ethics">Publication Ethics</a></li>
                    <li ><a href="/ISLE/about/editorialPolicies#custom-0">Plagiarisme Check</a></li>
                    <li class=" "><a href="{url page="information" op="readers"}">{translate key="navigation.infoForReaders.long"}</a></li>
                    <li class=" "><a href="{url page="information" op="librarians"}">{translate key="navigation.infoForLibrarians.long"}</a></li>
                    {if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}
                    <li class=" "><a href="{url page="about" anchor="copyrightNotice"}">{translate key="about.copyrightNotice"}</a></li>{/if}
                    {if $donationEnabled}<li ><a href="{url page="donations"}">{translate key="payment.type.donation"}</a></li>{/if}
                </ul>
            </div>

            <div class="large-3 medium-4 column" role="navigation">
                <h6>Other sites and content</h6>
                <ul >
                    <li class=" "><a href="//science.sangia.org" title="Sciences Sangia" target="_blank">Sciences Sangia</a></li>
                    <li class=" "><a href="//www.sangia.org" target="_blank">Sangia Indonesia</a></li>
                    <li class=" "><a href="//scholar.google.co.id/citations?user=sxSxyCQAAAAJ&hl" target="_blank">Google Scholar</a></li>
                    <li class=" "><a href="{url page="about" op="contact"}">Contacts us</a></li>
                    <li class=" "><a href="/ISLE/pages/view/faq">FAQ</a></li>
                </ul>
            </div>


            <div class="large-3 medium-12 column" role="navigation">
                <div class="about">
                    <h3 class="u-hide"></h3>
                    <p>Sangia Research Media and Publishing (SRM) on behalf of Sangia Publishing publishes journals, monographs, and reference in print and online.</p>
                    <p><a class="btn btn-link" href="//www.sangia.org/about" target="_blank">About Us</a></p>
                </div>
            </div>
            </div>
        </div>
        
            </div>
        </div>

    </div>
    <div class="sangia-legal">
        <div id="footer-legal" role="contentinfo">
            <h2 aria-level="2" class="u-visually-hidden">sangia.org sitemap</h2>
            <div id="pageFooter" class="srm-footer text-xs u-padding-s-hor u-padding-m-hor-from-sm u-padding-s-right-from-md u-padding-s-left-from-md u-padding-l-ver">
                <div class="srm-footer-sangia u-margin-m-bottom u-margin-0-bottom-from-md u-margin-s-right u-margin-m-right-from-md u-margin-l-right-from-lg"><img src="//assets.sangia.org/img/sangia-mono-branded-72x89-v2.svg" loading="lazy" alt="Sangia Publishing Group" width="68" height="78" /></div>
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
    
    <button class="StickySideButton StickySideButton--feedback" onclick="FreshWidget.show();">Support<img alt="" src="//assets.sangia.org/image/classical/freshdesk-support.png"></button>
        
    <!-- Google Analytics -->
    <script type="text/plain" cookie-consent="tracking" async src="https://www.googletagmanager.com/gtag/js?id=UA-110581662-2"></script>
    <!-- end of Google Analytics-->

    <!-- Back to top -->
    <script src="{$baseUrl}/plugins/themes/wizdam/js/lazyload.js"></script>
    <script src="{$baseUrl}/plugins/themes/wizdam/js/common.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        
    {get_debug_info}
    {if $enableDebugStats}{include file=$pqpTemplate}{/if}
        
</body>
</html>
