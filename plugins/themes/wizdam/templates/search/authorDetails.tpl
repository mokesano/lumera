{**
 * templates/search/authorDetails.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Index of published articles by author.
 *
 *}
{strip}
{assign var="pageTitle" value="search.authorDetails"}
{include file="common/header-AUTH27.tpl"}
{/strip}

            	<div class="sc-uosj0-0 AcWHr">
            	    <a data-aa-name="Journal Cover" data-aa-region="header" href="{url context=$homeContext page="index"}" class="sc-uosj0-1 fAiWUX header-journal-cover">
            	        <img loading="lazy" src="{$publicFilesDir}/homepageImage_en_US.jpg" width="75" height="auto" alt="Journal cover for {$currentJournal->getLocalizedTitle()|strip_tags|escape}" />
            	    </a>
            	   
            	    {if $printIssn} {else if $onlineIssn}
            		<p class="sc-1q3g1nv-0 sc-jsgros-0 eTETae ddyzPP">ISSN: {if $currentJournal->getSetting('onlineIssn')}{$currentJournal->getSetting('onlineIssn')}{elseif $currentJournal->getSetting('printIssn')}{$currentJournal->getSetting('printIssn')}{/if}</p>
            		{/if}
            	</div>
            </div>
        </div>
        <div class="columns medium-10">
            <div class="jUaFts">
            	<div class="cms-common cms-person"><h1 class="journal-name">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</h1>
            	</div>
            </div>
            <div class="sc-orwwe2-6 dtzDFi">
                <a data-button-size="small" data-aa-name="Editorial Team" data-aa-region="sub-page-header" size="small" href="{url page="about" op="editorialTeam"}" class="sc-4ky1f6-0 sc-4ky1f6-1 hnoKGz hLnlQv sub-page-header-submit-your-paper">
                    <span>Editorial Team</span></a>                
                <a data-button-size="small" data-aa-name="Submit your paper" data-aa-region="sub-page-header" size="small" href="{url page="submission" op="submit"}" class="sc-4ky1f6-0 sc-4ky1f6-1 hnoKGz hLnlQv sub-page-header-submit-your-paper">
                    <span>Submit your Paper</span></a>
                <a data-button-size="small" data-aa-name="View articles" data-aa-region="sub-page-header" size="small" href="{url page="issue" op="current"}" class="sc-4ky1f6-0 sc-4ky1f6-3 hnoKGz hcBixJ sub-page-header-view-articles">
                    <span>View Articles</span></a>
            </div>
            <div class="sc-1f27thb-0 cRFLRd">
                <a target="_self" rel="noopener noreferrer" href="{url page="about" anchor="authorGuidelines"}" data-aa-name="Guide for authors" data-aa-region="header" class="sc-kvjqii-0 fYuYRT header-guide-for-authors">
                    <span class="sc-kvjqii-1 sc-kvjqii-2 hjlxNa bDcWjJ"><svg width="2em" height="2em" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg></span>
                    <span>Guide for authors</span>
                </a>
                <a target="_self" rel="noopener noreferrer" href="{url page="information" op="librarians"}" data-aa-name="Abstract and Indexing" data-aa-region="header" class="sc-kvjqii-0 fYuYRT header-guide-for-authors">
                    <span class="sc-kvjqii-1 sc-kvjqii-2 hjlxNa bDcWjJ"><svg width="2em" height="2em" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg></span>
                    <span>Abstract and Indexing</span>
                </a>                
            </div>
        </div>
    </div>
</div>

<section class="live-area-wrapper">
    <div class="profile row">
        <aside class="columns medium-2">	
			<header class="anchored u-hide"></header>
		</aside>
        <section class="column medium-10 cms-person">
            <h2 class="editor-name">{$firstName|escape} {if $middleName} {$middleName|escape} {/if}{$lastName|escape}</h2>
        </section>
    </div>    
    <div class="profile row">
		<section class="columns medium-2 null">
			<div class="avatar bJniPh islvFm" size="150">
				<span style="box-sizing: border-box; display: inline-block; overflow: hidden; width: initial; height: initial; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; position: relative; max-width: 100%;">
				    <span style="box-sizing: border-box; display: block; width: initial; height: initial; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; max-width: 100%;">
				        <img style="display: block; max-width: 100%; width: initial; height: initial; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px;" alt="" aria-hidden="true" src="//media.stipwunaraha.ac.id/img/150-image.png">
                	</span>
                </span>
            </div>
        </section>
		<section class="column medium-2">
			<div class="avatar bJniPh islvFm" size="150">
				<span style="box-sizing: border-box; display: inline-block; overflow: hidden; width: initial; height: initial; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; position: relative; max-width: 100%;">
				    <span style="box-sizing: border-box; display: block; width: initial; height: initial; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; max-width: 100%;">
				        <img style="display: block; max-width: 100%; width: initial; height: initial; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px;" alt="" aria-hidden="true" src="//media.stipwunaraha.ac.id/img/128x150-image.png">
                	</span>
				    <picture>
                	{if $author->getUrl}
                	<source type="image/webp" srcset="//media.stipwunaraha.ac.id/img/128-avatar.png?as=webp 160w,//media.stipwunaraha.ac.id/img/128-avatar.png?as=webp 290w" sizes="(max-width: 640px) 160px,(max-width: 1200px) 290px,290px" >
                	<img loading="lazy" id="id=" class="avatar sangia-author" title="{$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" src="" alt="{$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" height="auto" width="100" />
                	{else}	
                	<source type="image/webp" srcset="//media.stipwunaraha.ac.id/img/128-avatar.png?as=webp 160w,//media.stipwunaraha.ac.id/img/128-avatar.png?as=webp 290w" sizes="(max-width: 640px) 160px,(max-width: 1200px) 290px,290px" >
                	<img loading="lazy" id="id=" class="avatar sangia-author" title="{$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" src="//media.stipwunaraha.ac.id/static/contactPersonM.png" alt="{$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; border: medium none; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%; object-fit: cover;" height="auto" width="150" />
                	{/if}
                	</picture>
                </span>
            </div>
        </section>
        <section class="column medium-8 cms-person">
            <div class="overview description">
                <p class="paragraph" itemprop="creator" itemtype="http://schema.org/Person"><span class="creator" itemprop="name">Author</span>
                </p>
                <div class="affiliation bLswwL">
                    {if $affiliation}
                    <div class="iggNhe dlPwne"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path></svg></div>
                    <p class="paragraph" itemprop="affiliation" itemtype="http://schema.org/Affiliation"> {$affiliation|escape}{/if}{if $country}, {$country|escape}</p>
                    {/if}
                </div>
                
                {if $affiliation}
                <div class="affiliation bLswwL">
                    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width="1em" height=".7em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg></div>
                    <p class="paragraph">{translate key="user.email"}: <i>Not available</i></p>
                </div>
                {/if}
                
                <div class="sc-1mur6on-9 bLswwL">
                    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj">
                        <svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
                    </div>
        			<p class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="" itemtype="http://schema.org/Journal">{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Published by Sangia Publishing.{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}Published by {$currentJournal->getSetting('publisherInstitution')}.{else}{$currentJournal->getSetting('publisherInstitution')}.{/if} Production and hosting by <i>Sangia Research Media & Publishing</i>
        			</p>
                </div>       
			</div>
        </section>
        <aside class="columns medium-2 u-hide">	
			<header class="anchored u-hide"></header>
			<!-- Sangia_Publishing_ads -->
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="ca-pub-8416265824412721"
                 data-ad-slot="5917495376"
                 data-ad-format="auto"
                 data-full-width-responsive="true"></ins>
            <script>
                 (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
		</aside>        
    </div>
</section>

<div class="live-area-wrapper">
    <div class="profile row">
        <aside class="column medium-2">	
			<section class="box">
				<section>
				    <h4 class="headline-524909129 u-font-sans">{translate key="article.articles"} counts {$publishedArticles|@count}</h4>
				</section>	
			</section>
			<aside data-component-mpu="" class="adsbox c-ad c-ad--160x600 u-mt-16 null">
                <div class="c-ad__inner">
                    <p class="c-ad__label">Advertisement</p>
                    <style>@media(max-width:500px){.Sangia-600x160{width:auto !important;height:600px;}}@media(min-width: 500px){.Sangia-600x160{width:auto !important;height:600px;}}@media(min-width:800px){.Sangia-600x160{width:auto !important;height:600px;}}
                    </style>
                    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721"
                         crossorigin="anonymous"></script>
                    <!-- Sangia-Advertisement -->
                    <ins class="adsbygoogle Sangia-600x160"
                         style="display:inline-block;"
                         data-ad-client="ca-pub-8416265824412721"
                         data-ad-slot="7285743241"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>
            </aside>
		</aside>
    	<div class="columns medium-10">
            <div class="article__list">
            
                <div id="results" class="article-by--author">
                	{foreach from=$publishedArticles item=article}
                		{assign var=issueId value=$article->getIssueId()}
                		{assign var=issue value=$issues[$issueId]}
                		{assign var=issueUnavailable value=$issuesUnavailable.$issueId}
                		{assign var=sectionId value=$article->getSectionId()}
                		{assign var=journalId value=$article->getJournalId()}
                		{assign var=journal value=$journals[$journalId]}
                		{assign var=section value=$sections[$sectionId]}
                
                	{if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $articleExpiryPartial.$articleId))}
                		{assign var=hasAccess value=1}
                	{else}
                		{assign var=hasAccess value=0}
                	{/if}
                
                	{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
                	{if $subscriptionRequired && $showGalleyLinks && $restrictOnly}
                
                	<div id="articleList" >
                		{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_SUBSCRIPTION}
                	    <span class="tocMenuArticle">
                    	    <span class="li-last u-font-sans">{if $issue->getPublished() && $section && $journal}<span class="tocSectionTitle">{$section->getLocalizedTitle()|escape}</span>{/if}</span>
                	        <div class="ArtType" title="open">Open Access</div>
                	    </span>
                	    {else}
                	    <span class="no-access-message">
                	    	<img class="content-type u-font-sans" src="//www.stipwunaraha.ac.id/static/images/classical/lock.png" alt="PDF file Get Access" title="PDF file Get Access">
                	    	<span class="content-type li-last u-font-sans">{if $issue->getPublished() && $section && $journal}<span class="tocSectionTitle">{$section->getLocalizedTitle()|escape}</span>{/if}</span>
                	    </span>
                	    {/if}
                
                		<div class="articleList--value">
                			<div class="tocTitle"><a href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">{$article->getLocalizedTitle()|strip_unsafe_html}</a></div>
                
                			<div class="tocAuthors">
                				{assign var=count value=0}
                				{assign var=authors value=$article->getAuthors()}
                				{foreach from=$authors item=author name=authors key=i}
                					{assign var=authorCount value=$authors|@count}
                					{assign var=fullname value=$author->getFullName()}
                					{assign var="pageTitle" value="search.authorIndex"}
                					{assign var=authorFirstName value=$author->getFirstName()}
                					{assign var=authorMiddleName value=$author->getMiddleName()}
                					{assign var=authorLastName value=$author->getLastName()}
                					{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}
                					{assign var=authorCountry value=$author->getCountry()}
                					{assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}
                					{assign var=authorName value="$authorName $authorMiddleName"}{/if}
                					{assign var="contact" value=$author->getData('primaryContact')}
                					{assign var=count value=$count+1} <a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" class="authorName" title="View {if $fullname}{$fullname|escape}{/if} profile" aria-label="{if $fullname}{$fullname|escape}{/if}">{if $fullname}{$fullname|escape}</a>{else}{/if}{if $i==$authorCount-2}, and {elseif $i<$authorCount-1}, {/if}
                				{/foreach}
                			</div> 
                
                			{if $article->getLocalizedAbstract()}
                			<div class="abstract authorDetails">{$article->getLocalizedAbstract()|truncate:200:"..."|strip_unsafe_html|nl2br}</div>
                			{/if}
                
                			<div id="value" class="info--article">
                				<div class="infoPubJournal">in <a title="Go to {$journal->getLocalizedTitle()|escape}" target="_blank" href="{url journal=$journal->getPath()}">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</a> (<em title="Published: {$article->getDatePublished()|date_format:"%e %B %Y"} in Volume {$issue->getVolume()|strip_tags|escape}, Issue {$issue->getNumber()|escape}, {$article->getPages()|escape} pp">{$article->getDatePublished()|date_format:'%B %Y'}</em>)</div>
                				<div class="info--DOI">{assign var="doi" value=$article->getStoredPubId('doi')}
                                    {if $article->getPubId('doi')}<a title="Permanent link for {$article->getLocalizedTitle()|strip_tags|escape}" href="http://doi.org/{$article->getPubId('doi')|escape}"><span class="fileDOI">DOI:</span> {$article->getPubId('doi')}</a>{/if} {if $article->getViews('doi')}<span class="fileHit" title="{$article->getViews('doi')} click">{$article->getViews('doi')}</span>{/if}</div>
                				<div class="infoPubDate"></div>
                			</div>
                		</div>
                
                		<ul id="author-article-InfoList" class="tocMenuArticle ul-list">
                			{foreach from=$article->getGalleys() item=galley name=galleyList}
                			{if $galley->isPdfGalley()}{if $hasAccess || ($subscriptionRequired)}
                			<li class="tocMenuArticle pubDOI galley-issue"><a title="{$article->getLocalizedTitle()|strip_tags|escape}" href="{url journal=$journal->getPath() page="article" op="download" path=$article->getBestArticleId()|to_array:$galley->getBestGalleyId($journal)}" {if $galley->getRemoteURL()}target="_blank" {/if}class="file">Download {$galley->getGalleyLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</span> <span class="fileView">{$galley->getViews()} views</span></a></li>
                			{/if}{/if}{/foreach}
                
                			<li class="tocMenuArticle li-list pubDOI"><a title="{$article->getLocalizedTitle()|strip_tags|escape}" target="_blank" href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}" class="file">{if $article->getLocalizedAbstract()}View {translate key="article.abstract"}{else}{translate key="article.details"}{/if}</a> <span class="fileView">{$article->getViews()} views</span></li>
                		
                			<div class="tocPages page-issue">{if $article->getPages()}Pages {$article->getPages()|escape}{else}Article {$article->getID()|escape}{/if}</div>
                			<li class="tocMenuArticle li-list pubDOI"></li>
                			<div class="tocPages"></div>
                		</ul>
                	</div>
                
                {else}
                
                    <div id="articleList" >
                        {if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}
                        {if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley()}
                	    <span class="tocMenuArticle">
                    	    <span class="li-last u-font-sans">{if $issue->getPublished() && $section && $journal}<span class="tocSectionTitle">{$section->getLocalizedTitle()|escape}</span>{/if}</span>
                	        <div class="ArtType" title="open access">Open Access</div>
                	    </span>
                	   {else}
                	   <span class="no-access-message">
                	    	<img class="content-type u-font-sans" src="//www.stipwunaraha.ac.id/static/images/classical/lock.png" alt="PDF file Not Available" title="PDF file Not Available">
                	    	<span class="content-type li-last u-font-sans">{if $issue->getPublished() && $section && $journal}<span class="tocSectionTitle">{$section->getLocalizedTitle()|escape}</span>{/if}</span>
                	    </span>{/if}
                	    {else}
                	    <span class="tocMenuArticle">
                    	    <span class="li-last u-font-sans">{if $issue->getPublished() && $section && $journal}<span class="tocSectionTitle">{$section->getLocalizedTitle()|escape}</span>{/if}</span>
                	        <div class="ArtType" title="free access">Open Access</div>
                	    </span>
                	    {/if}
                
                		<div class="articleList--value">
                			<div class="tocTitle"><a href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">{$article->getLocalizedTitle()|strip_unsafe_html}</a></div>
                
                			<div class="tocAuthors">
                				{assign var=count value=0}
                				{assign var=authors value=$article->getAuthors()}
                				{foreach from=$authors item=author name=authors key=i}
                					{assign var=authorCount value=$authors|@count}
                					{assign var=fullname value=$author->getFullName()}
                					{assign var="pageTitle" value="search.authorIndex"}
                					{assign var=authorFirstName value=$author->getFirstName()}
                					{assign var=authorMiddleName value=$author->getMiddleName()}
                					{assign var=authorLastName value=$author->getLastName()}
                					{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}
                					{assign var=authorCountry value=$author->getCountry()}
                					{assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}
                					{assign var=authorName value="$authorName $authorMiddleName"}{/if}
                					{assign var="contact" value=$author->getData('primaryContact')}
                					{assign var=count value=$count+1} <a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" class="authorName" title="View {if $fullname}{$fullname|escape}{/if} profile" aria-label="{if $fullname}{$fullname|escape}{/if}">{if $fullname}{$fullname|escape}</a>{else}{/if}{if $i==$authorCount-2}, and {elseif $i<$authorCount-1}, {/if}
                				{/foreach}
                			</div> 
                
                			{if $article->getLocalizedAbstract()}
                			<div class="abstract authorDetails">{$article->getLocalizedAbstract()|truncate:200:"..."|strip_unsafe_html|nl2br}</div>
                			{/if}
                
                			<div id="value" class="info--article">
                				<div class="infoPubJournal">in <a title="Go to {$journal->getLocalizedTitle()|escape}" target="_blank" href="{url journal=$journal->getPath()}">{$currentJournal->getLocalizedTitle()|strip_tags|escape}</a> (<em title="Published: {$article->getDatePublished()|date_format:"%e %B %Y"} in Volume {$issue->getVolume()|strip_tags|escape}, Issue {$issue->getNumber()|escape}, {$article->getPages()|escape} pp">{$article->getDatePublished()|date_format:'%B %Y'}</em>)</div>
                				<div class="info--DOI">{assign var="doi" value=$article->getStoredPubId('doi')}
                                    {if $article->getPubId('doi')}<a title="Permanent link for {$article->getLocalizedTitle()|strip_tags|escape}" href="http://doi.org/{$article->getPubId('doi')|escape}"><span class="fileDOI">DOI:</span> {$article->getPubId('doi')}</a>{/if} {if $article->getViews('doi')}<span class="fileHit" title="{$article->getViews('doi')} click">{$article->getViews('doi')}</span>{/if}</div>
                				<div class="infoPubDate"></div>
                			</div>
                		</div>
                
                		<ul id="author-article-InfoList" class="tocMenuArticle ul-list">
                			{foreach from=$article->getGalleys() item=galley name=galleyList}
                			{if $galley->isPdfGalley()}{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
                			<li class="tocMenuArticle pubDOI galley-issue"><a title="{$article->getLocalizedTitle()|strip_tags|escape}" href="{url journal=$journal->getPath() page="article" op="download" path=$article->getBestArticleId()|to_array:$galley->getBestGalleyId($journal)}" {if $galley->getRemoteURL()}target="_blank" {/if}class="file">Download {$galley->getGalleyLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</span> <span class="fileView">{$galley->getViews()} views</span></a></li>
                			{/if}{/if}
                			{/foreach}
                				
                			<li class="tocMenuArticle pubDOI link-issue"><a title="{$article->getLocalizedTitle()|strip_tags|escape}" href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">{if $article->getLocalizedAbstract()}View {translate key="article.abstract"}{else}View {translate key="article.details"}{/if} <span class="fileView">{$article->getViews()} views</span></a></li>
                
                			<div class="tocPages page-issue">{if $article->getPages()}Pages {$article->getPages()|escape}{else}Article {$article->getID()|escape}{/if}</div>
                		</ul>
                
                	</div>
                	{/if}
                	{/if}
                	{/foreach}
                    
            	</div>
            </div>
        </div>
        
{include file="common/footer.tpl"}
