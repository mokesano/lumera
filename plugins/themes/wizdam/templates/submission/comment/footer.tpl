{**
 * templates/submission/comment/footer.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common footer for comment pages.
 *
 *}

                </div>
            </div>
        </div>
    </div>
<style>
        
    .issue-title {
        color: #2b2b2b;
        font-size: 22px;
        font-family: "Bliss Regular", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif;
        line-height: 26px;
        margin-bottom: 30px;
        margin-top: 18px !important;
    }

    .journal-content .kicker,
    .article-body .kicker {
        font-family: "Bliss Regular", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif;
        font-size: 18px;
        font-weight: normal;
        margin-top: 0;
    }
    
    .article-body .kicker + h3 {
        font-size: 28px;
        margin-top: 18px;
    }
    
    .issue {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #ddd;
    }

    h3 + .issue {
        margin-top: 30px;
    }
    
    .issue p.type {
        color: #999;
    }

    .issue p.type small {
        font-size: 12px;
    }
    
    .issue h3.link {
        margin-top: 0;
        font-size: 18px;
        font-weight: normal;
        font-family: "Bliss Regular", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif;
    }

    .issue h3.link a {
        color: #d83b5b;
    }
    
    .issue p.editors small {
        color: #555;
        font-size: 14px;
        line-height: 28px;
    }

    
    .article h3 {
        margin-top: 30px;
    }

    .article-body {
        margin-top: 10px;
    }

    .article-body > * + * {
        margin-top: 10px;
    }

    .article-body .twoColumnSeparatorLeft--paragraph {
        border-top: 1px solid #ddd;
        height: 0;
        margin-top: 20px;
        padding-top: 10px;
    }

    .article-body .float--right {
        float: right;
        margin-left: 15px;
    }

    .article-body .float--left {
        float: left;
        margin-right: 15px;
    }

    .article-body table {
        border-collapse: collapse;
        margin-bottom: 30px;
        width: 100%;
    }

    .article-body table a {
        display: block;
    }

    .article-body table a img {
        width: 100%;
    }

    .article-body tbody tr td {
        border-bottom: 1px solid #eee;
        padding: 15px 0;
        vertical-align: top;
    }

    .article-body tbody tr:first-child td {
        border-bottom: 3px solid #777;
        padding: 10px 0;
    }

    .article-body tbody tr td:first-child {
        width: 29%;
        padding-right: 20px;
    }

    .article-body.cover-gallery table {
        table-layout: fixed;
        margin-bottom: 10px;
    }

    .article-body.cover-gallery tbody tr td {
        width: auto;
        padding: 15px 10px;
    }

    .article-body.cover-gallery tbody tr td:first-child {
        padding-left: 0;
        padding-right: 20px;
    }

    .article-body.cover-gallery tbody tr td:last-child {
        padding-left: 20px;
        padding-right: 0;
    }

    .article-body .flapHead {
        border-top: 1px solid #ddd;
        color: #00768A;
        cursor: pointer;
        padding-bottom: 15px;
        padding-left: 10px;
        padding-right: 10px;
        padding-top: 15px;
        user-select: none;
    }

    .article-body .collapsible-wrapper {
        padding-left: 10px;
        padding-right: 10px;
        transition: height 0.5s;
    }

    .collapsible {
        padding-bottom: 10px;
    }

    .collapsible-wrapper,
    .collapsible-wrapper + .flapHead {
        margin-top: 0;
    }
    
    .collapsible-wrapper {
        display: none;
    }

    .collapsible-wrapper.show,
    html.no-js .collapsible-wrapper {
        display: block;
    }

.headline-656086398 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-4241089976 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-2571032058 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-1417046068 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-3388887021 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-1987702510 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-2456938728 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-4088315882 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-3370858980 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-350502735 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-3514808013 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-2451116107 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-1320454089 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-3365036359 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-493455150 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.headline-3657760428 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 22px; font-weight: normal; line-height: 26px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}


    .box {
        background-color: #f4f4f4;
        padding: 30px 20px;
    }
    
    .box > * + *,
    .box > div > * + *,
    .box > section > * + * {
        margin-top: 15px;
    }


.headline-524909129 {
    /* undefined */
    color: #2b2b2b; font-family: "Bliss Bold", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 18px; font-weight: normal; line-height: 22px; margin-bottom: calc(-0.25em - 4px); transform: translateY(calc(-0.1em - 2px));
}

.button-base-2906877647 {
    /* undefined */
    background-color: #00768a; border: 0; border-radius: 0; display: flex; justify-content: space-between; padding-bottom: 9px; padding-left: 11px; padding-right: 11px; padding-top: 9px; transition: all 0.2s; -webkit-font-smoothing: antialiased; width: 100%;
}
.button-base-2906877647:hover {background-color: #0698b1; text-decoration: none;}
.button-label-1281676810 {
    /* undefined */
    color: #ecfafd; font-family: "Bliss Regular", "Gill Sans", Calibri, "Helvetica Neue", Arial, sans-serif; font-size: 16px; line-height: 22px; text-decoration: none; transition: all 0.2s;
}
.button-base-2906877647:hover .button-label-1281676810 {color: #effbfd;}
.button-icon-1969128361 {
    /* undefined */
    align-self: center; display: inline-flex; fill: #ecfafd; height: 16px; transform-box: fill-box; transition: all 0.2s; width: 16px;
}
.button-base-2906877647:hover .button-icon-1969128361 {fill: #effbfd;}</style>    
</div>

<footer>
    <div id="footer">
        {strip}
        {if $currentJournal && $currentJournal->getSetting('onlineIssn')}
        	{assign var=issn value=$currentJournal->getSetting('onlineIssn')}
        {elseif $currentJournal && $currentJournal->getSetting('printIssn')}
        	{assign var=issn value=$currentJournal->getSetting('printIssn')}
        {/if}

        {if $displayCreativeCommons}
        	{translate key="common.ccLicense"}
        {/if}

        {if $currentJournal->getLocalizedTitle()}
        <div id="c-journal-footer">
            <div  id="c-journal-footer__inner" class="col-srm row">    
                <div id="c-journal-footer__summary">
                    <div id="c-journal-footer-logo">
                    <h4 class="c-journal-footer__title-text">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</h4>
        			<p class="c-journal-footer__issn">ISSN: {if $issn}{$issn}{else}on proccess{/if}</p>
        		    </div></div>
        		<div id="c-journal-footer__contact">
        		    <h7 class="c-journal-footer__contact-title">Contact us</h7>
        		    <ul class="c-journal-footer__contact-list" style="list-style:none;padding-left:0">
        		        <li class="c-journal-footer__contact-item">Submission enquiries: <a href="{url page="login"}" target="_blank">Access here and click Contact Us</a></li>
        		        <li class="c-journal-footer__contact-item">General enquiries: <a href="mailto:admin@stipwunaraha.ac.id">admin@stipwunaraha.ac.id</a></li>
        		        </ul>
        		    </div>
        		</div>
        	</div>
        {/if}

        <div id="footer-nav" class="section">
        <div id="footer-nav-misc" class="text-xs u-padding-m-hor-from-sm">
            <div class="large-2 medium-4 columns" role="navigation">
                <h6>Explore SRM</h6>
                <ul >
                    <li ><a href="https://blogs.stipwunaraha.ac.id/">Read more on blogs</a></li>
                    <li ><a href="/index.php/ISLE/pages/view/Authorship%20Guidelines">Guide for Author</a></li>
                    <li ><a title="Browse Journals" href="{$baseUrl}" target="_blank">View Journals</a></li>
                    <li ><a title="View Journal by Subject" href="{$baseUrl}/index.php/index/search/categories" target="_blank">Journals Subjects</a></li>
                    <li ><a title="View All Articles" href="{$baseUrl}/index.php/index/search/titles" target="_blank">All Articles</a></li>
                    <li ><a title="View Authors Index" href="{$baseUrl}/index.php/index/search/authors" target="_blank">Authors Index</a></li>
                    {if $currentJournal->getLocalizedSetting('privacyStatement') != ''}
                    <li ><a href="{url page="about" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></li>{/if}
                </ul>
            </div>            
         
            <div class="large-2 medium-4 columns" role="navigation">
                <h6>Information</h6>
                <ul >
                    <li class=" "><a href="{url page="information" op="authors"}">{translate key="navigation.infoForAuthors"}</a></li>
                    <li ><a href="/index.php/ISLE/pages/view/Guide%20for%20Registration">Registration Guide</a></li>
                    <li ><a href="/index.php/ISLE/pages/view/Publication%20Ethics">Publication Ethics</a></li>
                    <li ><a href="/index.php/ISLE/about/editorialPolicies#custom-0">Plagiarisme Check</a></li>
                    <li class=" "><a href="{url page="information" op="readers"}">{translate key="navigation.infoForReaders"}</a></li>
                    <li class=" "><a href="{url page="information" op="librarians"}">{translate key="navigation.infoForLibrarians"}</a></li>
                    {if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}
                    <li ><a href="{url page="about" anchor="copyrightNotice"}">{translate key="about.copyrightNotice"}</a></li>{/if}
                    {if $donationEnabled}<li ><a href="{url page="donations"}">{translate key="payment.type.donation"}</a></li>{/if}
                </ul>
            </div>

            <div class="large-2 medium-4 columns" role="navigation">
                <h6>Other sites and content</h6>
                <ul >
                    <li ><a href="#" title="the site Under Development" target="_blank">Sciences Sangia</a></li>
                    <li ><a href="//www.scofi.stipwunaraha.ac.id" target="_blank">SCOFCI Indonesia</a></li>
                    <li ><a href="//scholar.google.co.id/citations?user=sxSxyCQAAAAJ&hl=id&authuser=6" target="_blank">Google Scholar</a></li>
                    <li ><a href="{url page="about" op="contact"}">Contacts us</a></li>
                    <li ><a href="/index.php/ISLE/pages/view/faq">FAQ</a></li>
                </ul>
            </div>


            <div class="large-4 medium-12 columns" role="navi">
                <div class="about">
                    <h3></h3>
                    <p>Sangia Research Media or SRM Publication (www.stipwunaraha.ac.id) publishes journals, monographs, and reference in print and online.</p>
                    <p><a class="btn btn-link" href="//www.stipwunaraha.ac.id/gp/about-us">About Us</a></p>
                </div>
            </div>
            </div>
        </div>

        <div id="footer-legal" role="contentinfo"> 
            <div id="pageFooter" class="srm-footer text-xs u-padding-s-hor u-padding-m-hor-from-sm u-padding-l-hor-from-md u-padding-l-ver u-margin-l-top u-margin-xl-top-from-sm u-margin-l-top-from-md">
                <div class="srm-footer-sangia u-margin-m-bottom u-margin-0-bottom-from-md u-margin-s-right u-margin-m-right-from-md u-margin-l-right-from-lg"><img src="https://stipwunaraha.ac.id/media/img/SRM_mono_72x89.png" alt="Sangia Group" width="auto"></div>
                <div id="standardFooter" class="srm-footer-content">
                    <div  class="u-hide u-remove-if-print"><div class="legal" role="contentinfo"></div></div>
                    <p class="">By using this website, you agree to our <a class="anchor" href="/index.php/ISLE/pages/view/Terms%20and%20Conditions"><span class="anchor-text">Terms and Conditions</span></a>, <a class="anchor" href="{url page="about" anchor="privacyStatement"}"><span class="anchor-text">{translate key="about.privacyStatement"}</span></a> and <a class="anchor" href="/index.php/ISLE/pages/view/Cookies"><span class="anchor-text">Cookies</span></a> policy.</p>
                    {if $currentJournal && $currentJournal->getSetting('onlineIssn')}
                    	{assign var=issn value=$currentJournal->getSetting('onlineIssn')}
                    {elseif $currentJournal && $currentJournal->getSetting('printIssn')}
                    	{assign var=issn value=$currentJournal->getSetting('printIssn')}
                    {/if}            
                    <p class="srm-lisensing" style="margin-bottom: 0;">{$currentJournal->getLocalizedTitle()|strip_tags|escape} {if $printIssn}{else if $onlineIssn}{if $currentJournal->getSetting('printIssn')}ISSN: <a class="anchor" href="https://issn.org/resource/issn/{$currentJournal->getSetting('printIssn')}" target="_blank"><span class="anchor-text">{$currentJournal->getSetting('printIssn')}</span></a> (Print) {/if}ISSN: {if $currentJournal->getSetting('onlineIssn')}<a class="anchor" href="https://issn.org/resource/issn/{$currentJournal->getSetting('onlineIssn')}" target="_blank"><span class="anchor-text">{$currentJournal->getSetting('onlineIssn')}</span></a> (Online){else}on proccess (Online){/if}. {/if}
                    {translate|assign:"applicationName" key="common.openJournalSystems"}
                    <span class="srm-lisensing">Powered by <a class="anchor" href="https://pkp.sfu.ca/ojs/" target="_blank"><span class="anchor-text">{$applicationName}</span></a> and <a class="anchor" href="https://github.com/masonpublishing/OJS-Theme" target="_blank"><span class="anchor-text">Mason Publishing</span></a> theme.</span></p>
                    <p class="srm-footer-copyright">Copyright © 2019 <a class="anchor" href="http://www.stipwunaraha.ac.id"><span class="anchor-text">SRM Publishing</span></a> unless otherwise stated. Part of <a class="anchor" href="http://www.sangia.org"><span class="anchor-text">Sangia Research Media (SRM)</span></a>.</p>
                    <p class="footer-section anchor">{$pageFooter}</p>       
                    <p id="diagnostic-info" class="footer-section">
                        <span id="diagnostic-login-status">{if $isUserLoggedIn}You logged in{else}Not logged in{/if}</span>
                        <span class="diagnostic-business-partners">{if $isUserLoggedIn}{if $hasOtherJournals}Affiliated{/if}{else}Unaffiliated{/if}</span>
                        <span id="diagnostic-ip" class="u-hide">Under development</span>
                    </p>   
                </div>
                <div class="u-margin-0-top u-margin-m-top-from-xs u-margin-0-top-from-md"><a aria-label="SRM home page (opens in a new tab)"  href="https://www.srm.com/" target="_blank" rel="nofollow"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="93px" height="22px" viewBox="0 0 93 22" enable-background="new 0 0 93 22" xml:space="preserve"><image id="image0" width="93" height="22" x="0" y="0" href="data:image/png;base64,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"/></svg></a></div>
            </div>          
            <div class="large-12 row u-hide">                          
                <div class="legal" role="contentinfo"><span id="footer-copyright">© 2019 <a href="http://www.stipwunaraha.ac.id">SRM Publishing</a> unless otherwise stated. Part of <a href="http://www.sangia.org">Sangia Group</a>. </span></div>
            </div>
        </div>
        {/strip}

        <!-- Back to top button -->
        <a class="buttontop"></a>
        <script type="text/javascript" src="{$baseUrl}/plugins/themes/stipwunaraha/js/sangia.js"></script>
        <script type="text/javascript" src="{$baseUrl}/plugins/themes/stipwunaraha/js/menu.js"></script>

        {call_hook name="Templates::Common::Footer::PageFooter"}

        {get_debug_info}
        {if $enableDebugStats}{include file=$pqpTemplate}{/if}

    </div>
</footer>
</div>

<!-- Google Analytics -->
<script type="text/plain" cookie-consent="tracking" async src="https://www.googletagmanager.com/gtag/js?id=UA-110581662-2"></script>
<!-- end of Google Analytics-->

</body>
</html>

