{**
 * templates/article/footer.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article View -- Footer component.
 *}

                                </div><!-- body -->
                            </article>
                        </div><!-- content -->
                    </div><!-- main -->
                </div>
            </section>
        </div>
    </div>
</div>

<section class="ads u-mt-32 u-mb-32 u-js-hide">
    <div class="u-container">
        <style>
        {literal}
        @media(max-width:500px) { .Sangia-970x90 {width:320px;height:90px;}} @media(min-width: 500px) { .Sangia-970x90 {width:970px;height:90px;}} @media(min-width:800px) { .Sangia-970x90 {width:970px;height:90px;}}
        {/literal}
        </style>
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721"
             crossorigin="anonymous"></script>
        <!-- Sangia-970x90 -->
        <ins class="adsbygoogle Sangia-970x90"
             style="display:inline-block;margin-left:auto;margin-right:auto;"
             data-ad-client="ca-pub-8416265824412721"
             data-ad-slot="5069561873"></ins>
        <script>
        {literal}
             (adsbygoogle = window.adsbygoogle || []).push({});
        {/literal}
        </script>
    </div>
</section>

{strip}
    {if $currentJournal && $currentJournal->getSetting('onlineIssn')}
        {assign var=issn value=$currentJournal->getSetting('onlineIssn')}
    {elseif $currentJournal && $currentJournal->getSetting('printIssn')}
        {assign var=issn value=$currentJournal->getSetting('printIssn')}
    {/if}
{/strip}
{if $displayCreativeCommons}
    {translate key="common.ccLicense"}
{/if}

<footer class="composite-layer u-mt-48" role="contentinfo">
    <div class="u-mt-16 u-mb-16">
            <div class="u-container">
                <div class="u-display-flex u-flex-wrap u-justify-content-space-between">                
                    
                    {if $homepageImage && !$setupStep}
                    <p class="c-meta u-ma-0 u-mr-24 u-flex-shrink">Issue cover: {if $issue->getLocalizedCoverPageDescription()}{$issue->getLocalizedCoverPageDescription()|strip_unsafe_html|nl2br}{else}SRM Publishing/Sangia Publishing{/if}</p>
                    {/if}
    
                    <p class="c-meta u-ma-0 u-flex-shrink">
                        <span class="c-meta__item">{if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{/if} {if $currentJournal->getSetting('abbreviation')}(<i>{$currentJournal->getSetting('abbreviation', $currentJournal->getPrimaryLocale())}</i>){/if}</span>
                        
                        {strip}
                        {if $currentJournal && $currentJournal->getSetting('onlineIssn')}
                            {assign var=issn value=$currentJournal->getSetting('onlineIssn')}
                        {elseif $currentJournal && $currentJournal->getSetting('printIssn')}
                            {assign var=issn value=$currentJournal->getSetting('printIssn')}
                        {/if}
                        {if $displayCreativeCommons}
                            {translate key="common.ccLicense"}
                        {/if}
                        {/strip}
                        
                        {if $printIssn} {else if $onlineIssn}
                        {if $currentJournal->getSetting('printIssn')}
                        <span class="c-meta__item">
                            <abbr title="International Standard Serial Number">ISSN</abbr> <span itemprop="onlineIssn">{$currentJournal->getSetting('printIssn')}</span> (print)</span>{/if}
                        <span class="c-meta__item">
                            <abbr title="International Standard Serial Number">ISSN</abbr> {if $currentJournal->getSetting('onlineIssn')}<span itemprop="printIssn">{$currentJournal->getSetting('onlineIssn')}</span>{else} <i>on proccess</i>{/if} (online)</span>
                        {/if}
                    </p>
    
                </div>
            </div>
        </div>
    <div itemscope="" itemtype="http://schema.org/Periodical">
            <meta itemprop="publisher" content="Sangia Publishing">
            <div class="c-footer">
                <div class="u-container">
                    <h2 aria-level="2" class="u-visually-hidden">sangia.org sitemap</h2>
                    <div class="u-hide-print" data-track-component="footer">
                        <div class="c-footer__header">
                            <div class="c-footer__logo"><img loading="lazy" alt="Sangia publishing" src="//assets.sangia.org/img/sangia-future-branded-v2.svg" width="200" height="31">
                            </div>
                            <ul class="c-menu c-menu--inherit u-mr-32">
                                <li class="c-menu__item"><a class="c-menu__link" href="//sangia.org/spg_/company_info/index.html" data-track="click" data-track-action="about us" data-track-label="link">About us</a></li>
                                <li class="u-hide c-menu__item"><a class="c-menu__link" href="//sangia.org/spg_/press_room/press_releases.html" data-track="click" data-track-action="press releases" data-track-label="link">Press releases</a></li>
                                <li class="u-hide c-menu__item"><a class="c-menu__link" href="//press.sangia.org/" data-track="click" data-track-action="press office" data-track-label="link">Press office</a></li>
                                <li class="c-menu__item"><a class="c-menu__link" href="//support.sangia.org/support/home" data-track="click" data-track-action="contact us" data-track-label="link">Contact us</a></li>
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
                                    <a class="c-menu__link" href="//www.tiktok.com/@rochmady" aria-label="TikTok" data-track="click" data-track-action="tiktok" data-track-label="link" target="_blank">
                                        <svg fill="none" xmlns="http://www.w3.org/2000/svg" height="18" viewBox="0 0 28 28"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8.45095 19.7926C8.60723 18.4987 9.1379 17.7743 10.1379 17.0317C11.5688 16.0259 13.3561 16.5948 13.3561 16.5948V13.2197C13.7907 13.2085 14.2254 13.2343 14.6551 13.2966V17.6401C14.6551 17.6401 12.8683 17.0712 11.4375 18.0775C10.438 18.8196 9.90623 19.5446 9.7505 20.8385C9.74562 21.5411 9.87747 22.4595 10.4847 23.2536C10.3345 23.1766 10.1815 23.0889 10.0256 22.9905C8.68807 22.0923 8.44444 20.7449 8.45095 19.7926ZM22.0352 6.97898C21.0509 5.90039 20.6786 4.81139 20.5441 4.04639H21.7823C21.7823 4.04639 21.5354 6.05224 23.3347 8.02482L23.3597 8.05134C22.8747 7.7463 22.43 7.38624 22.0352 6.97898ZM28 10.0369V14.293C28 14.293 26.42 14.2312 25.2507 13.9337C23.6179 13.5176 22.5685 12.8795 22.5685 12.8795C22.5685 12.8795 21.8436 12.4245 21.785 12.3928V21.1817C21.785 21.6711 21.651 22.8932 21.2424 23.9125C20.709 25.246 19.8859 26.1212 19.7345 26.3001C19.7345 26.3001 18.7334 27.4832 16.9672 28.28C15.3752 28.9987 13.9774 28.9805 13.5596 28.9987C13.5596 28.9987 11.1434 29.0944 8.96915 27.6814C8.49898 27.3699 8.06011 27.0172 7.6582 26.6277L7.66906 26.6355C9.84383 28.0485 12.2595 27.9528 12.2595 27.9528C12.6779 27.9346 14.0756 27.9528 15.6671 27.2341C17.4317 26.4374 18.4344 25.2543 18.4344 25.2543C18.5842 25.0754 19.4111 24.2001 19.9423 22.8662C20.3498 21.8474 20.4849 20.6247 20.4849 20.1354V11.3475C20.5435 11.3797 21.2679 11.8347 21.2679 11.8347C21.2679 11.8347 22.3179 12.4734 23.9506 12.8889C25.1204 13.1864 26.7 13.2483 26.7 13.2483V9.91314C27.2404 10.0343 27.7011 10.0671 28 10.0369Z" fill="#EE1D52"></path> <path d="M26.7009 9.91314V13.2472C26.7009 13.2472 25.1213 13.1853 23.9515 12.8879C22.3188 12.4718 21.2688 11.8337 21.2688 11.8337C21.2688 11.8337 20.5444 11.3787 20.4858 11.3464V20.1364C20.4858 20.6258 20.3518 21.8484 19.9432 22.8672C19.4098 24.2012 18.5867 25.0764 18.4353 25.2553C18.4353 25.2553 17.4337 26.4384 15.668 27.2352C14.0765 27.9539 12.6788 27.9357 12.2604 27.9539C12.2604 27.9539 9.84473 28.0496 7.66995 26.6366L7.6591 26.6288C7.42949 26.4064 7.21336 26.1717 7.01177 25.9257C6.31777 25.0795 5.89237 24.0789 5.78547 23.7934C5.78529 23.7922 5.78529 23.791 5.78547 23.7898C5.61347 23.2937 5.25209 22.1022 5.30147 20.9482C5.38883 18.9122 6.10507 17.6625 6.29444 17.3494C6.79597 16.4957 7.44828 15.7318 8.22233 15.0919C8.90538 14.5396 9.6796 14.1002 10.5132 13.7917C11.4144 13.4295 12.3794 13.2353 13.3565 13.2197V16.5948C13.3565 16.5948 11.5691 16.028 10.1388 17.0317C9.13879 17.7743 8.60812 18.4987 8.45185 19.7926C8.44534 20.7449 8.68897 22.0923 10.0254 22.991C10.1813 23.0898 10.3343 23.1775 10.4845 23.2541C10.7179 23.5576 11.0021 23.8221 11.3255 24.0368C12.631 24.8632 13.7249 24.9209 15.1238 24.3842C16.0565 24.0254 16.7586 23.2167 17.0842 22.3206C17.2888 21.7611 17.2861 21.1978 17.2861 20.6154V4.04639H20.5417C20.6763 4.81139 21.0485 5.90039 22.0328 6.97898C22.4276 7.38624 22.8724 7.7463 23.3573 8.05134C23.5006 8.19955 24.2331 8.93231 25.1734 9.38216C25.6596 9.61469 26.1722 9.79285 26.7009 9.91314Z" fill="#d5d5d5"></path> <path d="M4.48926 22.7568V22.7594L4.57004 22.9784C4.56076 22.9529 4.53074 22.8754 4.48926 22.7568Z" fill="#69C9D0"></path> <path d="M10.5128 13.7916C9.67919 14.1002 8.90498 14.5396 8.22192 15.0918C7.44763 15.7332 6.79548 16.4987 6.29458 17.354C6.10521 17.6661 5.38897 18.9168 5.30161 20.9528C5.25223 22.1068 5.61361 23.2983 5.78561 23.7944C5.78543 23.7956 5.78543 23.7968 5.78561 23.798C5.89413 24.081 6.31791 25.0815 7.01191 25.9303C7.2135 26.1763 7.42963 26.4111 7.65924 26.6334C6.92357 26.1457 6.26746 25.5562 5.71236 24.8839C5.02433 24.0451 4.60001 23.0549 4.48932 22.7626C4.48919 22.7605 4.48919 22.7584 4.48932 22.7564V22.7527C4.31677 22.2571 3.95431 21.0651 4.00477 19.9096C4.09213 17.8736 4.80838 16.6239 4.99775 16.3108C5.4985 15.4553 6.15067 14.6898 6.92509 14.0486C7.608 13.4961 8.38225 13.0567 9.21598 12.7484C9.73602 12.5416 10.2778 12.3891 10.8319 12.2934C11.6669 12.1537 12.5198 12.1415 13.3588 12.2575V13.2196C12.3808 13.2349 11.4148 13.4291 10.5128 13.7916Z" fill="#69C9D0"></path> <path d="M20.5438 4.04635H17.2881V20.6159C17.2881 21.1983 17.2881 21.76 17.0863 22.3211C16.7575 23.2167 16.058 24.0253 15.1258 24.3842C13.7265 24.923 12.6326 24.8632 11.3276 24.0368C11.0036 23.823 10.7187 23.5594 10.4844 23.2567C11.5962 23.8251 12.5913 23.8152 13.8241 23.341C14.7558 22.9821 15.4563 22.1734 15.784 21.2774C15.9891 20.7178 15.9864 20.1546 15.9864 19.5726V3H20.4819C20.4819 3 20.4315 3.41188 20.5438 4.04635ZM26.7002 8.99104V9.9131C26.1725 9.79263 25.6609 9.61447 25.1755 9.38213C24.2352 8.93228 23.5026 8.19952 23.3594 8.0513C23.5256 8.1559 23.6981 8.25106 23.8759 8.33629C25.0192 8.88339 26.1451 9.04669 26.7002 8.99104Z" fill="#69C9D0"></path> </g></svg>
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
                </div>
            </div>
    
        </div>        
    
        </div>
    <div id="pageFooter" class="c-corporate-footer" role="contentinfo">
        <div class="u-container">        
            <div class="srm-footer text-xs u-padding-l-ver">
                <div class="u-margin-m-bottom u-margin-0-bottom-from-md u-margin-m-right-from-md u-margin-l-right-from-lg"><img src="//assets.sangia.org/img/sangia-mono-branded-72x89-v2.svg" loading="lazy" alt="Sangia Publishing Group" width="68" height="78" />
                </div>
                <div id="standardFooter" class="srm-footer-content u-margin-l-right-from-lg u-margin-m-right-from-md">
                    <div  class="u-hide u-remove-if-print"><div class="legal" role="contentinfo"></div>
                    </div>
                    <div class="c-footer__ads">
                        <p class="ads_cookies-state">We use cookies to enhance our service and ads. By using this website, you agree to our <a class="anchor" href="/ISLE/pages/view/Terms%20and%20Conditions"><span class="anchor-text">Terms and Conditions</span></a>, <a class="anchor" href="{url page="about" anchor="privacyStatement"}"><span class="anchor-text">{translate key="about.privacyStatement"}</span></a> and <a class="anchor" href="/ISLE/pages/view/Cookies"><span class="anchor-text">Cookies</span></a> policy.</p>
                    </div>
                    <div class="c-footer__lisence">
                    {if $currentJournal && $currentJournal->getSetting('onlineIssn')}
                    	{assign var=issn value=$currentJournal->getSetting('onlineIssn')}
                    {elseif $currentJournal && $currentJournal->getSetting('printIssn')}
                    	{assign var=issn value=$currentJournal->getSetting('printIssn')}
                    {/if}            
                        <p class="srm-lisensing u-hide" style="margin-bottom: 0;">{$currentJournal->getLocalizedTitle()|strip_tags|escape} {if $printIssn}{else if $onlineIssn}{if $currentJournal->getSetting('printIssn')}ISSN: <a class="anchor" href="https://issn.org/resource/issn/{$currentJournal->getSetting('printIssn')}" target="_blank"><span class="anchor-text">{$currentJournal->getSetting('printIssn')}</span></a> (Print) {/if}ISSN: {if $currentJournal->getSetting('onlineIssn')}<a class="anchor" href="https://issn.org/resource/issn/{$currentJournal->getSetting('onlineIssn')}" target="_blank"><span class="anchor-text">{$currentJournal->getSetting('onlineIssn')}</span></a> (Online){else}on proccess (Online){/if}. {/if} {translate|assign:"applicationName" key="common.openJournalSystems"}<span class="srm-lisensing">Powered by <a class="anchor" href="https://pkp.sfu.ca/ojs/" target="_blank"><span class="anchor-text">{$applicationName}</span></a> and <a class="anchor" href="https://github.com/masonpublishing/OJS-Theme" target="_blank"><span class="anchor-text">Mason Publishing</span></a> theme.</span></p>
                        <p class="srm-footer-copyright" style="margin-bottom:0">Copyright © 2017-{$smarty.now|date_format:"%Y"} <a class="anchor" href="https://www.sangia.org" target="_blank"><span class="anchor-text">Sangia Publishing</span></a> unless otherwise stated. Part of <a class="anchor" href="//www.insw.go.id/nib" target="_blank"><span class="anchor-text">Sangia Research Media and Publishing (SRM™)</span> | NIB: 1111220205313</a>.</p>
                        <p class="sangia-footer-legal">Dirjen AHU No. <span class="anchor"><span class="anchor-text">AHU-050003.AH.01.30.Tahun 2022</span></span>. Certificate No. <span class="anchor"><span class="anchor-text">11112202053130002</span></span>.</p>
                    </div>
                    <div class="c-footer__stats">
                        <p class="footer-section anchor">{$pageFooter}</p>
                        <p id="diagnostic-info" class="footer-section">
                            <span id="diagnostic-login-status">{if $isUserLoggedIn}You logged in{else}Not logged in{/if}</span>
                            <span class="diagnostic-business-partners">{if $isUserLoggedIn}{if $hasOtherJournals}Affiliated{/if}{else}Unaffiliated{/if}</span>
                            <span id="diagnostic-ip" class="ip_diagnostic">Fetching IP address...</span>
                        </p>
                    </div>
                </div>
                <div class="u-margin-0-top u-margin-m-top-from-xs u-margin-0-top-from-md"><a aria-label="SRM home page (opens in a new tab)" href="https://www.sangia.org/" target="_blank" rel="nofollow"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="93px" height="22px" viewBox="0 0 93 22" enable-background="new 0 0 93 22" xml:space="preserve"><image id="image0" width="93" height="22" x="0" y="0" href="//assets.sangia.org/image/sangia.png"/></svg></a>
                </div>
            </div>
        </div>
    </div>    
    {call_hook name="Templates::Common::Footer::PageFooter"}
</footer>
</section>
</div>
</div>
</div>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-110581662-2" referrerpolicy="strict-origin-when-cross-origin"></script>

    {if $defineTermsContextId}
    <script type="text/javascript">
        {literal}
        <!--
        // Open "Define Terms" context when double-clicking any text
        function openSearchTermWindow(url) {
            var term;
            if (window.getSelection) {
                term = window.getSelection();
            } else if (document.getSelection) {
                term = document.getSelection();
            } else if(document.selection && document.selection.createRange && document.selection.type.toLowerCase() == 'text') {
                var range = document.selection.createRange();
                term = range.text;
            }
            if (term != ""){
                if (url.indexOf('?') > -1) openRTWindowWithToolbar(url + '&defineTerm=' + term);
                else openRTWindowWithToolbar(url + '?defineTerm=' + term);
            }
        }

        if(document.captureEvents) {
            document.captureEvents(Event.DBLCLICK);
        }

        // Make sure to only open the reading tools when double clicking within the galley  
        if (document.getElementById('inlinePdfResizer')) {
            context = document.getElementById('inlinePdfResizer');  
        }
        else if (document.getElementById('content')) {
            context = document.getElementById('content');   
        }
        else {
            context = document;
        }

        context.ondblclick = new Function("openSearchTermWindow('{/literal}{url page="rt" op="context" path=$articleId|to_array:$galleyId:$defineTermsContextId escape=false}{literal}')");
        // -->
        {/literal}
    </script>
    {/if}
        
    {if $article->getPubId('doi')}    
    <script src="//content.readcube.com/ping?doi={$article->getPubId('doi')}&amp;format=js" async="" type="text/javascript" referrerpolicy="strict-origin-when-cross-origin"></script>
    {/if}
    
    <script type="text/javascript" src="{$baseUrl}/assets/js/lazyload.js" defer></script>
    <script type="text/javascript" src="{$baseUrl}/assets/js/WizdamCitedby.js" defer></script>
    <script type="text/javascript" src="{$baseUrl}/assets/js/sangiastyle.js" defer></script>
    <script type="text/javascript" src="{$baseUrl}/assets/js/Wizdam-Article.js" defer></script>

    {if $reCaptchaEnabled && $reCaptchaVersion == 3}
    <script>
    document.addEventListener('DOMContentLoaded', function() {ldelim}
        grecaptcha.ready(function() {ldelim}
            grecaptcha.execute('{$reCaptchaPublicKey|escape}', {ldelim}action: 'read'{rdelim});
        {rdelim});
    {rdelim});
    </script>
    {/if}

    {get_debug_info}
    {if $enableDebugStats}{include file=$pqpTemplate}{/if}

</body>

</html>
