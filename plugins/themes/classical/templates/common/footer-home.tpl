{**
 * templates/common/footer-home.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site footer.
 *
 *}
    
            </div><!-- main -->
        </div><!-- warpper -->
        
        {strip}
        {if $currentJournal && $currentJournal->getSetting('onlineIssn')}
        	{assign var=issn value=$currentJournal->getSetting('onlineIssn')}
        {elseif $currentJournal && $currentJournal->getSetting('printIssn')}
        	{assign var=issn value=$currentJournal->getSetting('printIssn')}
        {/if}
        
        {if $displayCreativeCommons}
        	{translate key="common.ccLicense"}
        {/if}
        
        <div id="footer">
            <div id="footer-wunaraha" class="wunaraha" role="navigation">
                <span class="strapline">We brings your life with scientific documents at your fingertips</span>
            </div>
        
            <div id="footer-info" class="wunaraha">
                <div id="footer-nav-misc">
                    <div id="footer-our-content" class="block" role="navigation">
                        <h2>Our Content</h2>
                        <ul>
                            <li><a title="View Journals" href="{$baseUrl}" target="_blank">Journals</a></li>
                            <li><a title="View Journal by Subject" href="{$baseUrl}/search/categories" target="_blank">Category</a></li>
                            <li><a title="View All Articles" href="{$baseUrl}/search/titles" target="_blank">Articles</a></li>
                            <li><a title="View Authors Index" href="{$baseUrl}/search/authors" target="_blank">Author Index</a></li>
                        </ul>
                    </div>
                    <div id="footer-other-sites" class="block" role="navigation">
                        <h2>Other Sites</h2>
                        <ul>
                            <li><a title="Visit kumebano.com" href="//www.kumebano.com" target="_blank">kumebano.com</a></li>
                            <li><a title="Visit Sangia Blogs" href="//blog.sangia.org/" target="_blank">Sangia Publishing Blogs</a></li>
                            <li><a title="Visit CSISC Indonesia" href="//csisc.sangia.org/" target="_blank">CSISC Indonesia</a></li>
                            <li><a title="Visit Sangia" href="/" target="_blank">Sangia Research Media</a></li>
                        </ul>
                    </div>
                    <div class="block" role="navigation">
                        <h2>Help &amp; Contacts</h2>
                        <ul>
                            <li><a class="legal-information-link" title="Legal information" href="/termsandconditions" target="_blank">Legal information</a></li>
                            <li><a class="privacy-statement-link" title="Privacy statement" href="/privacystatement" target="_blank">Privacy statement</a></li>
                            <li><a class="contact-us-link" title="Contact us" href="/contactus" target="_blank">Contact Us</a></li>
                        </ul>
                    </div>
                </div>
                <a id="secret-team-link" title="View Team Page" href="/static/team.html" rel="nofollow"></a>
            </div>
        
            {call_hook name="Templates::Common::Footer::PageFooter"}
        
            <div id="footer-legal" class="section" role="contentinfo">
                <div id="legal" role="contentinfo">
                    <a target="_blank" rel="noreferrer noopener" href="//www.publishing.sangia.org">
                        <img src="{$baseUrl}/public/site/images/cosire-foother.svg" alt="Sangia Publishing" width="auto" height="32">
                    </a>
                    <span id="footer-copyright">© <span>2017-</span>{$smarty.now|date_format:"%Y"} <a href="//publishing.sangia.org">Sangia Publishing</a> unless otherwise stated. Part of <a target="_blank" rel="noreferrer noopener" href="//www.archive.sangia.org">Sangia Research Media Group</a>.</span>
                    <div id="diagnostic-info" class="footer-section">
                        <span id="diagnostic-login-status">{if $isUserLoggedIn}You logged in{else}Not logged in{/if}</span>
                        <span class="diagnostic-business-partners">{if $isUserLoggedIn}{if $hasOtherJournals}Affiliated{/if}{else}Unaffiliated{/if}</span>
                        <span id="diagnostic-ip" class="ip_diagnostic">Fetching IP address...</span>
                    </div>            
                    <div class="footer-section">{$pageFooter}</div>
                </div>
            </div>
            
            <div id="google-analytics-account" style="display: none;">UA-110581662-2</div>
            
        </div><!-- wrapper -->
        
    </div><!-- footer -->
    
    </div>
        
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-110581662-2"></script>
        <script>
        {literal}
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
        
          gtag('config', 'UA-110581662-2');
        {/literal}
        </script>
        		
        {/strip}
        
        
        <!-- OneTrust Cookies Settings button start -->
        <div id="ot-sdk-btn-floating" title="Manage cookies" class="ot-floating-button">
            <div class="ot-floating-button__front">
                <button id="ot-sdk-btn" type="button" class="ot-floating-button__open" aria-label="" aria-hidden="true">
                    <svg role="presentation" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path class="ot-sdk-show-settings ot-floating-button__svg-fill" d="M14.588 0l.445.328c1.807 1.303 3.961 2.533 6.461 3.688 2.015.93 4.576 1.746 7.682 2.446 0 14.178-4.73 24.133-14.19 29.864l-.398.236C4.863 30.87 0 20.837 0 6.462c3.107-.7 5.668-1.516 7.682-2.446 2.709-1.251 5.01-2.59 6.906-4.016zm5.87 13.88a.75.75 0 00-.974.159l-5.475 6.625-3.005-2.997-.077-.067a.75.75 0 00-.983 1.13l4.172 4.16 6.525-7.895.06-.083a.75.75 0 00-.16-.973z" fill="#FFF" fill-rule="evenodd"></path></svg>
                </button>
            </div>
            <div class="ot-floating-button__back">
                <button id="ot-sdk-btn" type="button" class="ot-floating-button__close" aria-label="Close Preferences" aria-hidden="false"><!--?xml version="1.0" encoding="UTF-8"?--> <svg role="presentation" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="Banner_02" class="ot-floating-button__svg-fill" transform="translate(-318.000000, -725.000000)" fill="#ffffff" fill-rule="nonzero"><g id="Group-2" transform="translate(305.000000, 712.000000)"><g id="icon/16px/white/close"><polygon id="Line" points="13.3333333 14.9176256 35.0823744 36.6666667 36.6666667 35.0823744 14.9176256 13.3333333"></polygon><polygon id="Line" transform="translate(25.000000, 25.000000) scale(-1, 1) translate(-25.000000, -25.000000) " points="13.3333333 14.9176256 35.0823744 36.6666667 36.6666667 35.0823744 14.9176256 13.3333333"></polygon></g></g></g></g></svg></button>
            </div>
        </div>
        <!-- OneTrust Cookies Settings button end -->
    
    {get_debug_info}
    {if $enableDebugStats}{include file=$pqpTemplate}{/if}
    
    <button class="StickySideButton StickySideButton--feedback" onclick="FreshWidget.show();">Support<img alt="" src="//assets.sangia.org/image/classical/freshdesk-support.png"></button>
        
    <!--    <script type="text/javascript" src="{$baseUrl}/plugins/themes/sangia-clas/js/menu.js"></script> -->
    <!--    <script type="text/javascript" src="/plugins/themes/classical/js/pagination.js"></script> -->
        <script type="text/javascript" src="/plugins/themes/classical/js/classical-modern--style.js"></script>
        
</body>
    
</html>