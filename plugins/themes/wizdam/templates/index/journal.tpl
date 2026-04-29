{**
 * templates/index/journal.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Journal index page.
 *
 *}
{strip}
{assign var="pageTitleTranslated" value=$siteTitle}
{include file="common/header-HOME.tpl"}
{/strip}

{call_hook name="Templates::Index::journal"}
{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
{* Display the table of contents or cover page of the current issue. *}
<section class="u-hide live-area-wrapper">
  <div class="live-area">
  	<div class="row">
  		<div class="column medium-12 c-section-heading">
  			<h2 class="headline-1283242569 articlesHome">Latest issue</h2>
  		</div>
  	</div>

  	<div class="row">
		<div class="column medium-2 journal-cover">
      		{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
			<noscript>
				<img loading="lazy" class="journal-cover__issue cover-lazy img-default" {if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}" {else} title="Cover issue"{/if} src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} width="100%" />
			</noscript>
			{else}
			<noscript>					
				<img loading="lazy" class="journal-cover__issue cover-lazy img-default" title="Cover issue default" src="//media.stipwunaraha.ac.id/img/img-default.jpg" alt="Cover issue" width="100%" />
			</noscript>
			{/if}

    		{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
    			<img loading="lazy" class="journal-cover__issue cover-lazy img-default" {if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}" {else} title="Cover issue"{/if} src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} width="100%" />
    		{else}	
    			{if $homepageImage}
    			    <img loading="lazy" class="journal-cover__issue cover-lazy" src="{$publicFilesDir}/{$homepageImage.uploadName|escape:"url"}" width="153px" height="auto" alt="Cover {$homepageImageAltText|escape}" />
    			{else}
    				<img loading="lazy" class="journal-cover__issue cover-lazy img-default" title="Cover issue default" src="//media.stipwunaraha.ac.id/img/img-default.jpg" alt="Cover issue default" width="153px" height="auto" />
    			{/if}
    		{/if}
    		{call_hook name="Templates::Article::Article::ArticleCoverImage"}

            <aside data-component-mpu="" class="adsbox c-ad c-ad--160x600 u-mt-32 null">
                <div class="c-ad__inner">
                    <p class="c-ad__label">Advertisement</p>
                    <script async="" src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- Banners -->
                    <ins class="adsbygoogle c-ad--160x600"
                         style="display:block"
                         data-ad-client="ca-pub-8416265824412721"
                         data-ad-slot="7137622495"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>	
                </div>
            </aside>
		</div>

	<section class="column medium-6">
		<h3 class="kicker headline-636847849">{translate key="issue.volume"} {$issue->getVolume()|escape}, {translate key="issue.number"} {$issue->getNumber()|escape}, {$issue->getDatePublished()|date_format:"%B %Y"}</h3>

		{if $issue->getLocalizedTitle($currentJournal)}
			<h3 class="issue-title headline-2545795530">{$issue->getLocalizedTitle($currentJournal)|escape}</h3>
		{/if}
		
		<div class="insight-label u-mb-16 u-font-sans">This issue is in progress but contains articles that are final and fully citable.</div>

		{include file="issue/issue.tpl"}
		<a href="{url page="issue" op="current"}" title="Browse Issue" style="width: 50%" class="button-base-2145177612"><span class="button-label-2586741800">Browse Issue</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-2388614187"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
	
	</section>

	<div class="column medium-4">
		<aside>		            
          <section class="cta-aside-section">
        
        		{if $currentJournal->getSetting('journalPaymentsEnabled') && ($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled'))}
        		{include file="common/payment.tpl"}	
        		{/if}
            
			<section class="teaser-navigation">
				<h4 class="headline-3740845194">Issues &amp; Articles</h4>
				<nav class="journal-subnav">
					<div class="live">
						<ul class="ul">
							<li class="journal-navigation-header">Header</li>
							<li class="  "><a href="{url page="issue" op="current"}" title="Browse Latest Issue">Latest Issue</a></li>
							<li class="  "><a href="{url page="issue" op="archive"}" title="Browse Volume & Issue">Issue One/Two Now Online!</a></li>
							{if $issue->getLocalizedTitle($currentJournal)}{if $issue->getVolume()}
							<li class="  "><a href="{url page="issue" op="archive"}" title="Browse Volume & Issue">{$issue->getLocalizedTitle($currentJournal)|escape}</a></li>
							{/if}{/if}
						</ul>
					</div>
				</nav>
			</section>

    		{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION}
    		<section class="u-hide sign-up-form box">
    			<div id="alert-widget_body">
    				<div class="sign-up">
    					<h4>Get informed about Updates</h4>
    					<p>Receive updates for all new issues and articles published in {$currentJournal->getLocalizedTitle()|strip_tags|escape}.</p>
    					<a href="{url page="notification" op="subscribeMailList"}" class="button-base-3126136790">
    						<span class="button-label-2057065834">Sign Up for Alerts</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-4260326089"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
    					</a>
    				</div>
    			</div>
    		</section>
    		{elseif $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
    		<section class="u-hide sign-up-form box">
    			<div id="alert-widget_body">
    			<div class="sign-up">
    				<h4>Get informed about Updates</h4>
    				<p>Receive updates for all new issues and articles published in {$currentJournal->getLocalizedTitle()|strip_tags|escape}.</p>
    				<a id="signUpSRMPub" href="{url page="notification" op="subscribeMailList"}">
    					<button type="submit">
    						<span>Sign Up for Alerts</span>
    						<svg width="32" height="32" viewBox="0 0 32 32"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg>
    					</button>
    				</a>
    			</div>
    		    </div>
    		</section>
    		{/if}{* $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION *}

            <aside data-component-mpu="" class="adsbox c-ad c-ad--300x300 u-mt-16">
                <div class="c-ad__inner">
                    <p class="c-ad__label">Advertisement</p>
                    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- Banners -->
                    <ins class="adsbygoogle"
                         style="display:block"
                         data-ad-client="ca-pub-8416265824412721"
                         data-ad-slot="6378045297"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>
            </aside>
            
			<section class="teaser-navigation" style="margin-top:30px;">
			    <a class="twitter-timeline" data-height="450" href="https://twitter.com/SangiaNews?ref_src=twsrc%5Etfw">Tweets by SangiaNews</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script> 
			</section>
	          		                    
           </section>
		</aside>
	</div>
	</div>
  </div>
</section>
{/if}

{call_hook name="Templates::Index::journal"}
{if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
{* Display the table of contents or cover page of the current issue. *}
<section class="area-wrapper">
  <div class="live-area">
  	<div class="row raw u-mb-32">
  		<div class="position-relative z-index-1">
  			<div class="u-container c-slice-heading" data-test="title">
                <h2>Latest issue <span class="sub-title">{translate key="issue.volume"} {$issue->getVolume()|escape}, {translate key="issue.issue"} {$issue->getNumber()|escape} ({$issue->getDatePublished()|date_format:"%Y"})</span></h2>
                {if $issue->getLocalizedTitle($currentJournal)}
				<h3 class="u-hide issue-title headline-2545795530">{$issue->getLocalizedTitle($currentJournal)|escape}</h3>
				{/if}
				<div class="insight-label u-font-sans">This issue is in progress but contains articles that are final and fully citable.</div>
            </div>
  		</div>
	  	<div id="contents" class="position-relative z-index-1" role="main">
	  		<section id="featured-content" class="u-mb-0" data-track-component="featured content">
	  			<h2 class="u-visually-hidden">Featured Content</h2>
	  			<div class="u-container">
	  				<ul class="app-featured-row">
	  					{include file="issue/issues.tpl"}	  				    
	  					<li class="app-featured-row__item app-featured-row__item--current-issue">
	  						<div class="c-card c-card--flush u-full-height">
	  							{if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
	  							<a class="c-card__image" href="{url page="issue" op="current"}" data-track="click" data-track-action="view current issue" data-track-label="image">
	  								<picture >
	  									<source srcset="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}?as=webp" type="image/webp">
	  									<img loading="lazy" src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}" {if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}" {else} title="Cover issue"{/if} {if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} itemprop="image">
	  								</picture>
	  							</a>
	  							{else}
	  							<a class="c-card__image" href="{url page="issue" op="current"}" data-track="click" data-track-action="view current issue" data-track-label="image">
	  								<picture >
	  									<source srcset="//media.stipwunaraha.ac.id/img/img-default.jpg?as=webp" type="image/webp">
	  									<img loading="lazy" src="//media.stipwunaraha.ac.id/img/img-default.jpg" {if $issue->getCoverPageAltText($locale) != ''} title="Cover issue {$issue->getCoverPageAltText($locale)|escape}" {else} title="Cover issue"{/if} {if $issue->getCoverPageAltText($locale) != ''} alt="{$issue->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="issue.coverPage.altText"}"{/if} itemprop="image">
	  								</picture>
	  							</a>	  							
	  							{/if}
	  							<div class="c-card__body u-display-flex u-flex-direction-column">
	  								<ul class="u-mb-16 u-list-reset u-display-flex">
	  									<li class="u-mr-4 u-flex-grow">
	  										<a class="u-button u-button--full-width" href="{url page="issue" op="current"}" data-track="click" data-track-action="view current issue" data-track-label="button">Contents</a>
	  									</li>
	  									<li class="u-ml-4 u-flex-grow">
	  										<a class="u-button u-button--primary u-button--full-width" href="{url page="notification" op="subscribeMailList"}" data-track="click" data-track-action="subscribe" data-track-label="button">Subscribe</a>
	  									</li>
	  								</ul>
	  							</div>
	  							<div class="c-card__section c-meta">
	  								<span class="c-meta__item"><span class="c-meta__type">{translate key="journal.currentIssue"}</span></span><time class="c-meta__item" datetime="{$issue->getDatePublished()|date_format:"$dateFormatShort"}" itemprop="datePublished">{$issue->getDatePublished()|date_format:"%d %B %Y"}</time>
	  							</div>
	  						</div>
	  					</li>
	  				</ul>
	  			</div>
	  		</section>
	  	</div>
	  </div>
	</div>
</section>
{/if}

{if $enableAnnouncementsHomepage}
	{* Display announcements *}
<section class="live-area-wrapper">
  <div class="live-area">
  	<div class="row">
  		<div class="column medium-12 c-section-heading">
  			<h2 class="headline-1283242569"><a href="{url page="announcement"}">{translate key="announcement.announcementsHome"}</a><svg class="c-section-heading__icon" aria-hidden="true" focusable="false" height="20" width="20" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="m4.08573416 5.70052374 2.48162731-2.4816273c.39282216-.39282216 1.02197315-.40056173 1.40306523-.01946965.39113012.39113012.3914806 1.02492687-.00014045 1.41654791l-4.17620791 4.17620792c-.39120769.39120768-1.02508144.39160691-1.41671995-.0000316l-4.17639421-4.1763942c-.39122513-.39122514-.39767006-1.01908149-.01657797-1.40017357.39113012-.39113012 1.02337105-.3930364 1.41951348.00310603l2.48183447 2.48183446.99770587 1.01367533z" transform="matrix(0 -1 1 0 2.081146 11.085734)"></path></svg></h2>
  		</div>
  	</div>

  	<section class="row" style="margin-top: 10px">
  		<section class="column medium-8">
            <div class="announcements">
            	{counter start=1 skip=1 assign="count"}
            	{iterate from=announcements item=announcement}
            	{if !$numAnnouncementsHomepage || $count <= $numAnnouncementsHomepage}
            
            	<section class="announcement">
            	{if $announcement->getTypeId()}
            	<h3 class="headline-2545795530">{$announcement->getAnnouncementTypeName()|escape}: {$announcement->getLocalizedTitle()|escape}</h3>
            	{else}
            	<h3 class="headline-2545795530">{$announcement->getLocalizedTitle()|escape}</h3>
            	{/if}
            		
            	<div class="description">{$announcement->getLocalizedDescriptionShort()|nl2br}</div>
            	<br />
            	<tbody>
            		<tr class="details">
            			<td class="posted">{translate key="announcement.posted"}: {$announcement->getDatePosted()|date_format:"%e %B %Y"}</td>
            			<td class="more">&nbsp;</td>
            			{if $announcement->getLocalizedDescription() != null}
            				<td class="more"><a href="{url page="announcement" op="view" path=$announcement->getId()}" style="float: right;">{translate key="announcement.viewLink"}</a></td>
            			{/if}
            		</tr>
            	</tbody>
            	</section>
            		{/if}
            	{counter}
            	{/iterate}
            </div>
  		</section>
  		<div class="column medium-4">
  			<div class="box">
  				<section class="teaser"><h4 class="headline-3789142952">Browse our latest news</h4>
  					<div><p>Stay up to date on events, announcements &amp; new releases</p></div>
  					<a title="News and Announcements" href="{url page="announcement"}" class="button-base-3588540778"><span class="button-label-1262423735">Browse now</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-248642068"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
  				</section>
  			</div>
  		</div>
	   
	  </section>
  </div>
</section>
{/if}

{if $externalHomeContent}
<section id="news-comment" class="live-area-wrapper">
    <div class="row raw">
    {$externalHomeContent}
    </div>
</section>
{/if}

{if !$enableAnnouncementsHomepage}
<section class="live-area-wrapper u-hide">
  <div class="live-area">
  	<div class="row">
  		<div class="column medium-12 c-section-heading">
  			<h2 class="headline-1283242569">Related Journals</h2>
  		</div>
  	</div>    
  	<section class="row">
      	{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}
  		<div class="column medium-4">
  			<div class="box">
  				<section class="teaser">
  					<h4 class="headline-3789142952">Publishing Open Access with us</h4>
  					<div><p>Learn how you can benefit from Open Access publishing</p></div>
  					<a title="Open Access &amp; Self Archiving" href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}" class="button-base-3588540778"><span class="button-label-1262423735">Learn more</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-248642068"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
  				</section>
  			</div>
  		</div>
  		{/if}
  		<div class="column medium-4">
  			<div class="box">
  				<section class="teaser"><h4 class="headline-3789142952">Submission manuscript</h4>
  					<div><p>Find out more about how to submission and/or publish within this journal</p></div>
  					<a title="News and Announcements" href="{url page="about" op="submissions"}" class="button-base-3588540778"><span class="button-label-1262423735">More Information</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-248642068"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
  				</section>
  			</div>
  		</div>   
    </section>
   </div>
</section>
{/if}

<section class="live-area-wrapper">
  <div class="live-area">
  	<section class="row">
  		<div class="columns medium-12 main-contents c-slice-heading">
  			<h2 class="headline">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</h2>
  		</div>
  	</section>
  	<section class="row">
  	    <section class="inline-flex medium-8" role="main">
      		<section class="column medium-4 teaser-navigation">
      			<h4 class="headline-3332373131">{translate key="navigation.infoForAuthors"}</h4>
      			<nav class="journal-subnav journal-subnav--1">
      			    <div class="live">
      				<ul class="ul">
      					<li class="journal-navigation-header">Header</li>
      					<li class="sub_menu"><a href="{url page="information" op="authors"}">{translate key="navigation.infoForAuthors.long"}</a></li>
      					{if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="focusAndScope"}">Aims &amp; Scope</a></li>{/if}
    			        {if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="publicationFrequency"}">{translate key="about.publicationFrequency"}</a></li>{/if}
    			        <li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="sectionPolicies"}">{translate key="about.sectionPolicies"}</a></li>
    			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}">{translate key="about.openAccessPolicy"}</a></li>{/if}
    			        {if $currentJournal->getLocalizedSetting('reviewPolicy') != ''}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="peerReviewProcess"}">{translate key="about.peerReviewProcess"}</a></li>{/if}
      					<li class="sub_menu"><a href="{url page="about" op="submissions"}">Submission</a></li>
    			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableAuthorSelfArchive')}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="authorSelfArchivePolicy"}">{translate key="about.authorSelfArchive"}</a></li>{/if}
    			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableDelayedOpenAccess')}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="delayedOpenAccessPolicy"}">{translate key="about.delayedOpenAccess"}</a></li>{/if}
    			        {foreach key=key from=$currentJournal->getLocalizedSetting('customAboutItems') item=customAboutItem}
    			        {if !empty($customAboutItem.title)}
    			            <li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor=$customAboutItem.title|replace:" ":""|escape $key}">{$customAboutItem.title|escape}</a></li>
    			         {/if}
    			         {/foreach}
      					{call_hook name="Templates::About::Index::Policies"}
      				</ul>
      			</div>
      			</nav>
      		</section>
    
      		<section class="column medium-4 teaser-navigation">
      			<h4 class="headline-3332373131">{translate key="navigation.infoForReaders"}</h4>
      			<nav class="journal-subnav journal-subnav--2">
      			    <div class="live">
      				<ul class="ul">
      					<li class="journal-navigation-header">Header</li>
    	  				<li class="sub_menu"><a href="{url page="information" op="readers"}">{translate key="navigation.infoForReaders.long"}</a></li>
      					{foreach key=key from=$customAboutItems item=customAboutItem}{if $customAboutItem.title!=''}
      					<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor=$customAboutItem.title|replace:" ":""|escape $key}">{$customAboutItem.title|escape}</a></li>{/if}
      					{/foreach}
      					{call_hook name="Templates::About::Index::Policies"}
    			        {if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="publicationFrequency"}">{translate key="about.publicationFrequency"}</a></li>{/if}
    			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}">{translate key="about.openAccessPolicy"}</a></li>{/if}
    			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableAuthorSelfArchive')}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="authorSelfArchivePolicy"}">{translate key="about.authorSelfArchive"}</a></li>{/if}
    			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableDelayedOpenAccess')}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="delayedOpenAccessPolicy"}">{translate key="about.delayedOpenAccess"}</a></li>{/if}
    			        {if $currentJournal->getSetting('enableLockss') && $currentJournal->getLocalizedSetting('lockssLicense') != ''}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="archiving"}">{translate key="about.archiving"}</a></li>{/if}
      					<li class="sub_menu"><a href="{url page="about" op="contact"}">Contacts</a></li>
      				</ul>
      			</div>
      			</nav>
      		</section>
    
      		<section class="column medium-4 teaser-navigation">
      			<h4 class="headline-3332373131">{translate key="navigation.infoForLibrarians"}</h4>
      			<nav class="journal-subnav journal-subnav--2">
      			    <div class="live">
      				<ul class="ul">
      					<li class="journal-navigation-header">Header</li>
      					<li class="sub_menu"><a href="{url page="information" op="librarians"}">{translate key="navigation.infoForLibrarians.long"}</a></li>
      					{foreach key=key from=$customAboutItems item=customAboutItem}{if $customAboutItem.title!=''}
      					<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor=$customAboutItem.title|replace:" ":""|escape $key}">{$customAboutItem.title|escape}</a></li>{/if}
      					{/foreach}
      					{call_hook name="Templates::About::Index::Policies"}
    			        {if $currentJournal->getLocalizedSetting('pubFreqPolicy') != ''}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="publicationFrequency"}">{translate key="about.publicationFrequency"}</a></li>{/if}
    			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}">{translate key="about.openAccessPolicy"}</a></li>{/if}
    			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableAuthorSelfArchive')}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="authorSelfArchivePolicy"}">{translate key="about.authorSelfArchive"}</a></li>{/if}
    			        {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $currentJournal->getSetting('enableDelayedOpenAccess')}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="delayedOpenAccessPolicy"}">{translate key="about.delayedOpenAccess"}</a></li>{/if}
    			        {if $currentJournal->getSetting('enableLockss') && $currentJournal->getLocalizedSetting('lockssLicense') != ''}<li class="sub_menu"><a href="{url page="about" op="editorialPolicies" anchor="archiving"}">{translate key="about.archiving"}</a></li>{/if}
      				</ul>
      			</div>
      			</nav>
      		</section>
  		</section>
  		
  		<div class="column medium-4">
      		{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN && $currentJournal->getLocalizedSetting('openAccessPolicy') != ''}
  			<div class="box">
  				<section class="teaser">
  					<h4 class="headline-3789142952">Publishing Open Access with us</h4>
  					<div><p>Learn how you can benefit from Open Access publishing</p></div>
  					<a title="Open Access &amp; Self Archiving" href="{url page="about" op="editorialPolicies" anchor="openAccessPolicy"}" class="button-base-3588540778"><span class="button-label-1262423735">Learn more</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-248642068"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
  				</section>
  			</div>
      		{/if}
  			<div class="box">
  				<section class="teaser"><h4 class="headline-3789142952">Submission manuscript</h4>
  					<div><p>Find out more about how to submission and/or publish within this journal</p></div>
  					<a title="News and Announcements" href="{url page="about" op="submissions"}" class="button-base-3588540778"><span class="button-label-1262423735">More Information</span><svg width="32" height="32" viewBox="0 0 32 32" class="button-icon-248642068"><path fill="inherit" fill-rule="evenodd" d="M11.5 28c-0.38 0-0.76-0.142-1.052-0.432-0.59-0.58-0.598-1.528-0.016-2.118l10.166-9.492-10.162-9.404c-0.584-0.588-0.58-1.538 0.008-2.118 0.59-0.588 1.54-0.578 2.122 0.008l10.86 10.104c0.772 0.776 0.774 2.028 0.006 2.808l-10.862 10.196c-0.294 0.298-0.682 0.448-1.070 0.448z"></path></svg></a>
  				</section>
  			</div>
  		</div>
    </section>
   </div>
</section>

<section class="u-container u-mb-32 u-mt-32 content-loaded">
	<div class="live-area">
		<div class="row">					
    	    {if $currentJournal->getSetting('publisherInstitution')}
    	    <div class="membership medium-6 u-mb-32">
    	        <section class="membership__item">
    	            <h3 class="headfoot u-mb-16">Copyright</h3>
    	            <div class="c-meta__copyright text-s">Copyright © {$smarty.now|date_format:"%Y"} {if $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing" || $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"} {$currentJournal->getSetting('publisherInstitution')}{else}{$currentJournal->getSetting('publisherInstitution')}. Production & hosting by Sangia Publishing{/if}, under <a href="{$currentJournal->getSetting('licenseURL')}" target="_blank">Creative Commons Attribution Licensed</a>.{if $currentJournal->getLocalizedSetting('copyrightNotice') != ''} <span class="popup">Go to <a href="{url page="about" op="submissions" anchor="copyrightNotice"}" target="_blank">{translate key="about.copyrightNotice"}</a> for more information about copyrights.</span>{/if}</div>
    	        </section>
    	    </div>
    	    {/if}
			
    	    {assign var="contactMailingAddress" value=$currentJournal->getLocalizedSetting('contactMailingAddress')}
    	    {if $currentJournal->getLocalizedSetting('contactMailingAddress')}
    	    <div class="membership medium-6 u-mb-32">
    	        <section class="membership__item">
    	            <h3 class="headfoot u-mb-16">Editorial Office</h3>
    	            <div class="c-meta__editorial text-s">{$currentJournal->getLocalizedSetting('contactMailingAddress')|strip_tags}</div>
    	        </section>
    	    </div>
    		{/if}
    		
			{if $currentJournal && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE && $alternatePageHeader}
			<div class="indexedin u-mt-16">
				<ul class="indexedin-link">
					<li class="indexedin-item" data-test="logo-listing"><a href="https://garuda.kemdikbud.go.id/journal/" target="_blank">
					    <picture>
					        <source srcset="{$baseUrl}/plugins/themes/stipwunaraha/img/garuda.png?as=webp" type="image/webp">
					        <img class="garuda u-mb-0" src="{$baseUrl}/plugins/themes/stipwunaraha/img/garuda.png" loading="lazy" alt="Garba Rujukan Digital" />
					    </picture>
					    </a></li>
					<li class="indexedin-item" data-test="logo-listing"><a href="http://www.crossref.org/" target="_blank">
					    <picture>
					        <source srcset="{$baseUrl}/plugins/themes/stipwunaraha/img/indexedin_06.png?as=webp" type="image/webp">
					        <img class="crossref u-mb-0" src="{$baseUrl}/plugins/themes/stipwunaraha/img/indexedin_06.png" loading="lazy" alt="CrossRef" />
					    </picture>
					    </a></li>
					<li class="indexedin-item" data-test="logo-listing"><a href="http://sinta.kemdikbud.go.id/journals" target="_blank">
					    <picture>
					        <source srcset="{$baseUrl}/plugins/themes/stipwunaraha/img/sinta.png?as=webp" type="image/webp">
					        <img class="sinta u-mb-0" src="{$baseUrl}/plugins/themes/stipwunaraha/img/sinta.png" loading="lazy" alt="Sinta" />
					    </picture>
					    </a></li>
					<li class="indexedin-item" data-test="logo-listing"><a href="http://scholar.google.com/" target="_blank">
					    <picture>
					        <source srcset="{$baseUrl}/plugins/themes/stipwunaraha/img/indexedin_02.gif?as=webp" type="image/webp">
					        <img class="scholar u-mb-0" src="{$baseUrl}/plugins/themes/stipwunaraha/img/indexedin_02.gif" loading="lazy" alt="Google Scholar" />
					    </picture>
					    </a></li>
				</ul>
			</div>
			{/if}
		</div>
	</div>
</section>    

<section id="cope-ithenticate-container" class="u-container u-mb-48">
    <ul class="app-membership-row">
        <li class="app-membership-row__item" data-test="logo-listing">
            <a href="http://publicationethics.org/" data-test="logo-link" target="_blank">
                <picture>
                    <source srcset="//www.assets.sangia.org/static/images/logos/cope-8d92c4b829.png?as=webp" type="image/webp">
                    <img class="u-mb-0" alt="Committee on Publication Ethics" data-test="logo-image" src="//www.assets.sangia.org/static/images/logos/cope-8d92c4b829.png" loading="lazy">
                </picture>
            </a>
            <p class="c-meta u-mt-16" data-test="logo-label">This journal is a member of and subscribes to the principles of the Committee on Publication Ethics.</p>
        </li>
        <li class="app-membership-row__item" data-test="logo-listing">
            <a href="http://www.ithenticate.com/" data-test="logo-link" target="_blank">
                <picture>
                    <source srcset="//www.assets.sangia.org/static/images/logos/ithenticate-eabe2377a3.png?as=webp" type="image/webp">
                    <img class="u-mb-0" alt="Ithenticate Plagiarism Detection" data-test="logo-image" src="//www.assets.sangia.org/static/images/logos/ithenticate-eabe2377a3.png" loading="lazy">
                </picture>
            </a>
            <p class="c-meta u-mt-16" data-test="logo-label"></p>
        </li>
    </ul>
</section>

</div> <!--close section contents-->

<section class="live-area-wrapper {if $issue && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}cms-highlight-2{else}{if $enableAnnouncementsHomepage}{else}cms-highlight{/if}{/if}">
<div class="live-area">
<div class="row">
	<div class="columns small-12 ">
		<div class="cms-grid-collection"><h2 id="c6611792">Stay informed</h2>
		<div class="cms-collection-list u-mt-24">
			<ul class="small-block-grid-1 medium-block-grid-3">
				<li>
					<div id="id19" class=""><a class="cms-teaser-text" href="{url page="about" op="editorialTeam"}" style="text-decoration: none;" target="_blank"><div class="cms-teaser-box cms-teaser-box-with-icon cms-teaser-box-titled-icon" data-mh="mh-id3" style="height: 74px;"><div class="article-meta"></div><div class="cms-font-icon">N</div><h3>Contact an Editor</h3></div></a></div></li>
				<li>
					<div id="id1a" class=""><a class="cms-teaser-text" href="https://twitter.com/{if $currentJournal->getSetting('initials')}{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}{else}SRMadhy{/if}" style="text-decoration: none;" target="_blank"><div class="cms-teaser-box cms-teaser-box-with-icon cms-teaser-box-titled-icon" data-mh="mh-id3" style="height: 74px;"><div class="article-meta"></div><div class="cms-font-icon">1</div><h3>Follow {if $currentJournal->getSetting('initials')}@{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}{else}@SRMadhy{/if}</h3></div></a></div></li>
				<li>
					<div id="id1b" class=""><a class="cms-teaser-text" href="https://twitter.com/SangiaNews" style="text-decoration: none;" target="_blank"><div class="cms-teaser-box cms-teaser-box-with-icon cms-teaser-box-titled-icon" data-mh="mh-id3" style="height: 74px;"><div class="article-meta"></div><div class="cms-font-icon">1</div><h3>Follow @Sangia_News</h3></div></a></div></li>
			</ul>
		</div>
		</div>
	</div>
</div>
</div>
</section>

{include file="common/footer.tpl"}
