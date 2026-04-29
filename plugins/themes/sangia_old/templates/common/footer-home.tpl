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
                    <li><a title="View Journal by Subject" href="{$baseUrl}/index.php/index/search/categories" target="_blank">Category</a></li>
                    <li><a title="View All Articles" href="{$baseUrl}/index.php/index/search/titles" target="_blank">Articles</a></li>
                    <li><a title="View Authors Index" href="{$baseUrl}/index.php/index/search/authors" target="_blank">Author Index</a></li>
                </ul>
            </div>
            <div id="footer-other-sites" class="block" role="navigation">
                <h2>Other Sites</h2>
                <ul>
                    <li><a title="Visit kumebano.com" href="//www.kumebano.com" target="_blank">kumebano.com</a></li>
                    <li><a title="Visit Sangia Blogs" href="//www.blog.sangia.org/" target="_blank">Sangia Publishing Blogs</a></li>
                    <li><a title="Visit COSIRE Indonesia" href="//www.cosire.sangia.org/" target="_blank">COSIRE Indonesia</a></li>
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
            <span id="footer-copyright">© 2017 <a href="//www.publishing.sangia.org">Sangia Publishing</a> unless otherwise stated. Part of <a target="_blank" rel="noreferrer noopener" href="//www.archive.sangia.org">Sangia Research Media Group</a>.</span>
            <div id="diagnostic-info" class="footer-section">
                <span id="diagnostic-login-status">Not logged in</span>
                <span class="diagnostic-business-partners">Unaffiliated</span>
                <span id="diagnostic-ip">114.125.164.64</span>
            </div>            
            <div class="footer-section">{$pageFooter}</div>
        </div>
    </div>
    
    <div id="google-analytics-account" style="display: none;">UA-110581662-2</div>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-110581662-2"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-110581662-2');
</script>
		
{/strip}
<script type="text/javascript" src="{$baseUrl}/plugins/themes/eserem-classical/js/menu.js"></script>
</div>

{get_debug_info}
{if $enableDebugStats}{include file=$pqpTemplate}{/if}

</div><!-- wrapper -->
</div><!-- footer -->
<button class="StickySideButton StickySideButton--feedback" onclick="FreshWidget.show();">Support<img alt="" src="//www.stipwunaraha.ac.id/static/images/classical/freshdesk-support.png"></button>
</body>
</html>
