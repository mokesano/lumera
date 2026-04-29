{**
 * templates/article/footer.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article View -- Footer component.
 *}

        {if $sharingEnabled}
        <!-- start AddThis -->

                    <!-- Go to www.addthis.com/dashboard to customize your tools --> <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5b1b8c88d8801350"></script> 

        <!-- end AddThis -->
        {/if}

    </div><!-- body -->
</article>

</div><!-- content -->
</div><!-- main -->
{strip}
{if $currentJournal && $currentJournal->getSetting('onlineIssn')}
    {assign var=issn value=$currentJournal->getSetting('onlineIssn')}
{elseif $currentJournal && $currentJournal->getSetting('printIssn')}
    {assign var=issn value=$currentJournal->getSetting('printIssn')}
{/if}

{if $displayCreativeCommons}
    {translate key="common.ccLicense"}
{/if}
<footer class="u-margin-l-top-from-md">
        
    <div id="pageFooter" class="srm-footer text-xs u-padding-s-hor u-padding-m-hor-from-sm u-padding-l-hor-from-md u-padding-l-ver u-margin-l-top u-margin-xl-top-from-sm u-margin-l-top-from-md">
        <div class="u-margin-m-bottom u-margin-0-bottom-from-md u-margin-s-right u-margin-m-right-from-md u-margin-l-right-from-lg"><img src="//www.assets.sangia.org/img/sangia-mono-branded-72x89-v2.svg" loading="lazy" alt="Sangia Publishing Group" width="68" height="78">
        </div>
        <div id="standardFooter" class="srm-footer-content">
            <div  class="u-hide u-remove-if-print"><div class="legal" role="contentinfo"></div></div>
            <p class="">By using this website, you agree to our <a class="anchor" href="/index.php/ISLE/pages/view/Terms%20and%20Conditions" rel="noopener noreferrer" target="_blank"><span class="anchor-text">Terms and Conditions</span></a>, <a class="anchor" href="{url page="about" anchor="privacyStatement"}" rel="noopener noreferrer" target="_blank"><span class="anchor-text">{translate key="about.privacyStatement"}</span></a> and <a class="anchor" href="/index.php/ISLE/pages/view/Cookies" rel="noopener noreferrer" target="_blank"><span class="anchor-text">Cookies</span></a> policy.</p>
            {if $currentJournal && $currentJournal->getSetting('onlineIssn')}
                {assign var=issn value=$currentJournal->getSetting('onlineIssn')}
            {elseif $currentJournal && $currentJournal->getSetting('printIssn')}
                {assign var=issn value=$currentJournal->getSetting('printIssn')}
            {/if}            
            <p class="srm-lisensing" style="margin-bottom: 0;">{$currentJournal->getLocalizedTitle()|strip_tags|escape} {if $printIssn}{else if $onlineIssn}{if $currentJournal->getSetting('printIssn')}ISSN: <a class="anchor" href="https://issn.org/resource/issn/{$currentJournal->getSetting('printIssn')}" rel="noopener noreferrer" target="_blank" ><span class="anchor-text">{$currentJournal->getSetting('printIssn')}</span></a> (Print) {/if}ISSN: {if $currentJournal->getSetting('onlineIssn')}<a class="anchor" href="https://issn.org/resource/issn/{$currentJournal->getSetting('onlineIssn')}" rel="noopener noreferrer" target="_blank"><span class="anchor-text">{$currentJournal->getSetting('onlineIssn')}</span></a> (Online){else}on proccess (Online){/if}{/if}, under the terms of the <a class="anchor" href="https://creativecommons.org/licenses/by/4.0/" rel="noopener noreferrer" target="_blank"><span class="anchor-text">Creative Commons Attribution 4.0</span></a> License.</p>
            <span class="srm-lisensing">Powered by <a class="anchor" href="https://pkp.sfu.ca/" rel="noopener noreferrer" target="_blank"><span class="anchor-text">Public Knowledge Project</span></a> within <a class="anchor" href="https://pkp.sfu.ca/ojs/" rel="noopener noreferrer" target="_blank"><span class="anchor-text">Open Journal Systems</span></a>.</span>
            <p class="srm-footer-copyright">Copyright © 2019 <a class="anchor" href="http://www.stipwunaraha.ac.id" rel="noopener noreferrer"><span class="anchor-text">SRM Publishing</span></a> unless otherwise stated. Part of <a class="anchor" href="http://www.sangia.org" rel="noopener noreferrer"><span class="anchor-text">Sangia Research Media (SRM)</span></a>.</p>
            <p class="footer-section anchor">{$pageFooter}</p>       
            <p id="diagnostic-info" class="footer-section">
                <span id="diagnostic-login-status">{if $isUserLoggedIn}You logged in{else}Not logged in{/if}</span>
                <span class="diagnostic-business-partners">{if $isUserLoggedIn}{if $hasOtherJournals}Affiliated{else}Unaffiliated{/if}{/if}</span>
                <span id="diagnostic-ip" class="u-hide">Under development</span>
            </p>   
        </div>
        <div class="u-margin-0-top u-margin-m-top-from-xs u-margin-0-top-from-md"><a aria-label="SRM home page (opens in a new tab)" href="https://www.srm.com/" target="_blank" rel="nofollow"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="93px" height="22px" viewBox="0 0 93 22" enable-background="new 0 0 93 22" xml:space="preserve"><image id="image0" width="93" height="22" x="0" y="0" href="data:image/png;base64,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"/></svg></a></div>
        </div>
        {call_hook name="Templates::Common::Footer::PageFooter"}
    </div>
</footer>
        {/strip}
        
</section>
</div>
</div>
</div>

        <script type="text/javascript">
        {literal}
          window.twttr = (function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0],
            t = window.twttr || {};
          if (d.getElementById(id)) return t;
          js = d.createElement(s);
          js.id = id;
          js.src = "https://platform.twitter.com/widgets.js";
          fjs.parentNode.insertBefore(js, fjs);
         
          t._e = [];
          t.ready = function(f) {
            t._e.push(f);
          };
         
          return t;
        }(document, "script", "twitter-wjs"));
         {/literal}
        </script>

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

        {get_debug_info}
        {if $enableDebugStats}{include file=$pqpTemplate}{/if}
        
    {assign var="doi" value=$article->getStoredPubId('doi')}
    {if $article->getPubId('doi')}    
    <script src="//content.readcube.com/ping?doi={$article->getPubId('doi')}&amp;format=js" async=""></script>
    {/if}
    
</body>
</html>
