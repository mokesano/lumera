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

            <h2 data-author-id="{$authorId|string_format:"%011d"|escape}" class="profiles" itemprop="name" itemtype="http://schema.org/Person">{if $user->getFullName()}<span class="fullname u-hide">{$user->getFullName()|escape}</span>{/if}{if $user->getSalutation()}<span class="text degree prefix">{$user->getSalutation()|escape}</span>{/if}{if $firstName !== $lastName}<span class="text given-name">{$firstName|escape}</span>{/if}{if $middleName}<span class="text middle-name">{$middleName|escape}</span>{/if}<span class="text surname">{$lastName|escape}</span>{if $user->getSuffix()}<span class="last-degree suffix">, {$user->getSuffix()|escape}</span>{/if}
                {if $matchedUserId}
                    <span class="text verified-badge">Verified</span>
                {/if}
            </h2>
            
        </div>
    </div>
</div>

<div class="live-area-wrapper">
    <div class="u-row row">
        <aside class="columns medium-2"></aside>
        <section class="column medium-10 cms-person">
            <h2 data-author-id="{$authorId|string_format:"%011d"|escape}" class="person-name" itemprop="name" itemtype="http://schema.org/Person">{if $user->getFullName()}<span class="fullname u-hide">{$user->getFullName()|escape}</span>{/if}{if $user->getSalutation()}<span class="text degree">{$user->getSalutation()|escape}</span>{/if}{if $firstName !== $lastName}<span class="text given-name">{$firstName|escape}</span>{/if}{if $middleName}<span class="text middle-name">{$middleName|escape}</span>{/if}<span class="text surname">{$lastName|escape}</span>{if $user->getSuffix()}<span class="last-degree suffix">, {$user->getSuffix()|escape}</span>{/if}
                {if $matchedUserId}
                    <span class="text verified-badge">Verified</span>
                {/if}
            </h2>
        </section>
    </div>
    <div class="u-row row">
    	<div class="author-profiles">
    		<section class="column medium-2">
    			<div class="cms-common">
    				<div class="avatar bJniPh islvFm overview" size="150">
    				<span style="box-sizing: border-box; display: inline-block; overflow: hidden; width: 100%; height: 100%; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; position: relative; max-width: 150px;">
    				    <span style="box-sizing: border-box; display: block; width: 100%; height: 100%; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; max-width: 150px;">
    				        <img style="display: block; max-width: 100%; width: initial; height: initial; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; width: 100%;" alt="" aria-hidden="true" src="//assets.sangia.org/static/images/128x150-image.png">
    				    </span>
    				    <picture>
    				    {if $hasProfileImage && $profileImageUrl}
    				    <source srcset="{$profileImageUrl|escape}" type="image/webp">
        				<img data-author-id="{$authorId|string_format:"%011d"|escape}" class="lazyload avatar sangia-author" title="{$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" src="{$profileImageUrl|escape}?as=webp" alt="{$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" height="auto" width="150" style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%; object-fit: cover;" />
        				{else}
        				<source {if $userGender == 'M'}srcset="//assets.sangia.org/img/contactPersonM.png?as=webp"{elseif $userGender == 'F'}srcset="//assets.sangia.org/img/contactPersonF.png?as=webp"{else}srcset="//assets.sangia.org/static/images/default_203.jpg?as=webp"{/if} type="image/webp">
        				<img data-author-id="{$authorId|string_format:"%011d"|escape}" class="lazyload avatar sangia-author" title="{$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" src="//assets.sangia.org/static/images/default_203.jpg" alt="{$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" height="auto" width="150" style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%; object-fit: cover;" />
        				{/if}
        				</picture>
        			</span>	
    				</div>
    			</div>
    		</section>	
            <section class="column medium-7 cms-person">
                <div class="overview description">
                    <section class="paragraph" itemprop="creator" itemtype="http://schema.org/Person">
                        <span class="creator" itemprop="name">Author Profile</span>
                    </section>
                    {if $affiliation}
                        {assign var="affiliationsArray" value=$affiliation|escape:"htmlall"|explode:"\n"}
                        {foreach from=$affiliationsArray item=singleAffiliation name=affiliationsLoop}
                            <div class="affiliation bLswwL">
                                <div class="iggNhe dlPwne"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path></svg>
                                </div>
                                <p class="paragraph" itemprop="affiliation" itemtype="http://schema.org/Affiliation">
                                    {$singleAffiliation|escape}{if $smarty.foreach.affiliationsLoop.last}{if $country}, {$country|escape}{/if}{/if}
                                </p>
                            </div>
                        {/foreach}
                    {else}
                        <div class="affiliation bLswwL">
                            <div class="iggNhe dlPwne">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em">
                                    <path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path>
                                </svg>
                            </div>
                            <p class="paragraph" itemprop="affiliation" itemtype="http://schema.org/Affiliation">
                                Author affiliation not available{if $country}, {$country|escape}{/if}
                            </p>
                        </div>
                    {/if}

    				{if $user->getInterestString()}
    				<div class="sc-1mur6on-9 bLswwL">
    				    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg width="1em" height="1em" viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg" class="sc-1mur6on-14 bPLcdE"><path id="Shape" fill="currentColor" fill-rule="nonzero" d="M4.98913043,4.69565217 L6.75,4.69565217 L6.75,8.09706522 L5.86956522,7.45728261 L4.98913043,8.09706522 L4.98913043,4.69565217 Z M9.39130435,4.10869565 L9.39130435,5.57608696 L12.0326087,5.57608696 L12.0326087,16.1413043 L2.70586957,16.1413043 C2.02206522,16.1413043 1.4673913,15.5866304 1.4673913,14.9028261 L1.4673913,5.2826087 C1.8225,5.4675 2.21869565,5.57608696 2.64130435,5.57608696 L3.52173913,5.57608696 L3.52173913,10.9790217 L5.86956522,9.27097826 L8.2173913,10.9790217 L8.2173913,3.22826087 L3.52173913,3.22826087 L3.52173913,4.10869565 L2.64130435,4.10869565 C1.99271739,4.10869565 1.4673913,3.51586957 1.4673913,2.78804348 C1.4673913,2.06021739 1.99271739,1.4673913 2.64130435,1.4673913 L12.6195652,1.4673913 L12.6195652,0 L2.64130435,0 C1.18565217,0 0,1.25021739 0,2.78804348 L0,14.9028261 C0,16.3936957 1.215,17.6086957 2.70586957,17.6086957 L13.5,17.6086957 L13.5,4.10869565 L9.39130435,4.10869565 Z"></path></svg>
    				    </div>
    				    <p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl" itemprop="interest" itemtype="http://schema.org/Interest">{$user->getInterestString()|escape}</p>
    				</div>
    				{/if}
				
                    {if $authorEmail}
                    <div class="scwizdam-1mur6wiz-7 bLswwL">
                        <div class="iggNhe dlPwne sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width="1em" height="1em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg></div>
                        <p class="paragraph">{translate key="user.email"}: {$authorEmail|escape|mask_email}</p>
                    </div>
                    {/if}
                    
                    <div class="scwizdam-1mur6wiz-9 bLswwL u-hide">
                        <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj">
                            <svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
                        </div>
                		<p class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="" itemtype="http://schema.org/Journal">{if $currentJournal}{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Published by Sangia Publishing.{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}Published by {$currentJournal->getSetting('publisherInstitution')|escape}.{else}{$currentJournal->getSetting('publisherInstitution')|escape}{/if}{else}{$siteTitle|escape}{/if}</p>
            		</div>
            		
        			<div class="author-person eTETae">
        			    {if $authorOrcid}
        				<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
        					<img class="lazyload" src="//assets.sangia.org/img/orcid_16x16.svg" style="height:16px" alt="orcid" />
        					<span class="orcid">{translate key="user.orcid"} </span><a title="Go to Orcid profile of {$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" href="https://orcid.org/{$authorOrcid|escape}" target="_blank">{$authorOrcid|escape}</a>
        					</p>
        			    {elseif $user->getData('orcid')}
        				<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
        					<img class="lazyload" src="//assets.sangia.org/img/orcid_16x16.svg" style="height:16px" alt="orcid" />
        					<span class="orcid">{translate key="user.orcid"} </span><a title="Go to Orcid profile of {$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" href="{$user->getData('orcid')|escape}" target="_blank">{$user->getData('orcid')|escape}</a>
        					</p>
        				{/if}
        				{if $user->getSintaId()}
        				<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
        					<img class="lazyload" src="{$baseUrl}/assets/ico/brand_sinta.png" style="height:16px" alt="sinta" />
        					<span class="sinta">Sinta: </span><a title="Go to Sinta profile of {$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" href="//sinta.kemdiktisaintek.go.id/authors/profile/{$user->getSintaId()|escape}" target="_blank" class="sintaid">Science And Technology Index</a><span class="articleCount"> Academic Profile by Dikti, Saintek Indonesia</span>
        				</p>{/if}
        				{if $user->getScopusId()}
        				<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
        				    <img src="{$baseUrl}/assets/ico/scopus.ico" alt="Scopus" width="16" height="16" style="vertical-align: middle; margin-right: 3px;">
        					<span class="scopus">Scopus</span> ID <a title="Go to Scopus profile of {$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" href="https://www.scopus.com/authid/detail.uri?authorId={$user->getScopusId()|escape}" target="_blank" class="scopusid">{$user->getScopusId()|escape}</a>
        				</p>{/if}
        				{if $user->getData('dimensionId')}
    					<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
    					    <img src="{$baseUrl}/assets/ico/dimension.ico" alt="Dimension" width="16" height="16" style="vertical-align: middle; margin-right: 3px;" class="brand" referrerpolicy="strict-origin-when-cross-origin" />
    						<span class="dimension">Dimension</span> ID <a title="Go to Dimension profile of {$user->getFullName()|escape}" href="https://app.dimensions.ai/details/entities/publication/author/{$user->getData('dimensionId')|escape}" target="_blank" class="scopusid">{$user->getData('dimensionId')|escape}</a>
    					</p>{/if}
        				{if $user->getResearcherId()}
        				<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
        				    <img src="{$baseUrl}/assets/ico/wos.svg" alt="Scopus" width="16" height="16" style="vertical-align: middle; margin-right: 3px;">
        					<span class="researcher">ResearcherId</span> ID <a title="Go to Scopus profile of {$firstName|escape} {if $middleName} {$middleName|escape}{/if} {$lastName|escape}" href="https://www.scopus.com/authid/detail.uri?authorId={$user->getResearcherId()|escape}" target="_blank" class="scopusid">{$user->getResearcherId()|escape}</a>
        				</p>{/if}
        			</div>        		
    			</div>
            </section>
    		<section class="column medium-3">
    			<section class="box">
    				<section><h4 class="headline-524909129">Want to publish with {if $currentJournal}<i>{$currentJournal->getLocalizedTitle()|strip_tags|escape}</i>{else}us{/if}? Submit your Manuscript online.</h4></section>
    				<a href="{url page="author" op="submit"}" target="_blank" data-track="click" class="button-base-2906877647">
    					<span class="button-label-1281676810">Submit paper</span>
    					<svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1969128361"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg>
    				</a>
    			</section>
    		</section>        
    	</div>
    </div>
</div>

{if $user->getScopusId()}
<div class="live-area-wrapper u-pt-0 u-pb-0 u-bb-s2">
    <div class="profile row graph">
        <!-- Elemen untuk grafik artikel -->
        <aside class="medium-12">
			<section class="graph_box">
				<section class="description" id="scopus-graph"></section>
			</section>
		</aside>
	</div>	
</div>
{/if}

{if $user->getLocalizedBiography() || $user->getLocalizedSignature() || $user->getData('mailingAddress') || $user->getGoogleScholar() || $user->getUrl()}
<div class="live-area-wrapper u-bb-s2">
    <div class="u-row row">
        <aside class="column medium-2 null">
    		<header class="anchored">
    		    <h3 class=" ">{translate key="user.biography"}</h3>
    		</header>
		</aside>
    	<div class="columns medium-10 person-detail">
    		<div id="{$authorId}" class="person-detail editor-bio" itemprop="description">{$user->getLocalizedBiography()|strip_unsafe_html|nl2br} {if $authorUrl}<a href="{$authorUrl|escape}" target="_blank">Author Personal Website</a>{elseif $user->getUrl()}<a href="{$user->getUrl()|escape}" target="_blank">Author Personal Website</a>{elseif $user->getGoogleScholar()}<a href="{$user->getGoogleScholar()|escape}" target="_blank">Author Personal Website</a>{/if}
    		</div>
    	</div>
    </div>
</div>
{/if}

<div class="{if $user->getLocalizedBiography()}live-area-wrapper{else}live-area u-pt-48 u-pb-48{/if}">
    <div class="u-row row">
        <div class="column medium-12">
            <div data-test="title" class="article-list">
                <h3 class="articleAuthor">
                    <span class="header-content">Article's by author</span>
                    <div class="info-article" style="display: inherit;font-size: 15px;">
                        <span class="info-publisher u-mr-8">{if $currentJournal}{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Published by Sangia Publishing.{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}Published by {$currentJournal->getSetting('publisherInstitution')|escape}{else}SANGIA Research Media & Publishing{/if}{else}{$siteTitle|escape}{/if}.</span>
                        <span class="article-counts u-mr-8">{translate key="article.articles"} count {$publishedArticles|@count|escape}.</span>
                        <span class="time-stamp"></span>
                    </div>
                </h3>
            </div>
        </div>    
    </div>

<section id="search-article-list" {if $user->getScopusId()}class="u-bb-s2" {/if}data-track-component="search grid">
    <div class="u-row row">
        <div class="columns">    
            <div class="article__list" id="articleList">
<ul class="app-article-list-row">
	{foreach from=$publishedArticles item=article}
		{assign var=issueId value=$article->getIssueId()}
		{assign var=issue value=$issues[$issueId]}
		{assign var=issueUnavailable value=$issuesUnavailable.$issueId}
		{assign var=sectionId value=$article->getSectionId()}
		{assign var=journalId value=$article->getJournalId()}
		{assign var=journal value=$journals[$journalId]}
		{assign var=section value=$sections[$sectionId]}
		{if $issue->getPublished() && $section && $journal}

	{if $article->getLocalizedFileName() && $article->getLocalizedShowCoverPage()}
		{assign var=showCoverPage value=true}
	{else}
		{assign var=showCoverPage value=false}
	{/if}

	{if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $articleExpiryPartial.$articleId))}
		{assign var=hasAccess value=1}
	{else}
		{assign var=hasAccess value=0}
	{/if}

	{if $article->getGalleys()}

	<li class="app-article-list-row__item sangia-wizdam">
		<div class="u-full-height" data-native-ad-placement="false">
			<article class="u-full-height c-card c-card--flush" itemtype="http://schema.org/ScholarlyArticle">
				<div class="c-card__layout u-full-heights">
				    {if $showCoverPage}
					<div class="c-card__image">
						<picture>
                        {if $currentJournal}
							<source type="image/webp" srcset="{$publicFilesDir}/{$article->getLocalizedFileName()|escape}?as=webp 160w,{$publicFilesDir}/{$article->getLocalizedFileName()|escape}?as=webp 290w">
							<img class="lazyload" src="{$publicFilesDir}/{$article->getLocalizedFileName()|escape}"{if $article->getLocalizedCoverPageAltText() != ''} alt="{$article->getLocalizedCoverPageAltText()|escape}"{else} alt="{translate key="article.coverPage.altText"}"{/if} itemprop="image">
                        {else}
                            {* Konteks site search - gunakan path journal spesifik *}
                            <source type="image/webp" srcset="{$baseUrl}/public/journals/{$article->getJournalId()|escape}/{$article->getLocalizedFileName()|escape}?as=webp 160w,{$baseUrl}/public/journals/{$article->getJournalId()|escape}/{$article->getLocalizedFileName()|escape}?as=webp 290w">
                            <img src="{$baseUrl}/public/journals/{$article->getJournalId()|escape}/{$article->getLocalizedFileName()|escape}" alt="{$article->getLocalizedCoverPageAltText()|escape}" itemprop="image">
                        {/if}
						</picture>
					</div>
					{/if}
					{call_hook name="Templates::Issue::Issue::ArticleCoverImage"}
					<div class="c-card__body u-display-flex u-flex-direction-column">
						<h3 class="c-card__title" itemprop="name headline">
							<a href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}" class="c-card__link u-link-inherit" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link">{$article->getLocalizedTitle()|strip_unsafe_html}</a>
						</h3>
						{if $article->getLocalizedAbstract()}
						<div class="c-card__summary abstract-content u-mb-16 u-hide-sm-max" itemprop="description"><p>{$article->getLocalizedAbstract()|nl2br}</p></div>
						{/if}
						<ul class="c-author-list c-author-list--compact c-author-list--separated u-mt-auto" data-test="author-list">{assign var=authors value=$article->getAuthors()}{foreach from=$authors item=author name=authors}{assign var=fullname value=$author->getFullName()}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}<li itemprop="creator" itemtype="http://schema.org/Person">{if $fullname}<span class="u-hide" itemprop="name">{$fullname|escape}</span>{if $authorFirstName !== $authorLastName}<span itemprop="name">{$authorFirstName|escape}</span>{/if}{if $authorMiddleName}<span itemprop="name">{$authorMiddleName|truncate:1:"."|escape}</span>{/if}<span itemprop="name">{$authorLastName|escape}</span>{/if}</li>{/foreach}
						</ul>
					</div>
				</div>
				<div class="c-card__section c-meta">
					{if $issue->getPublished() && $section && $journal}
					<span class="c-meta__item c-meta__item--block-at-lg" data-test="article.type">
						<span class="c-meta__type">{$section->getLocalizedTitle()|escape}</span>
					</span>
					{/if}
					
                    {if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}
                        <span class="c-meta__item c-meta__item--block-at-lg 1" itemprop="openAccess" data-test="open-access">
                            <span class="u-color-open-access">Open Access</span>
                        </span>
                    {elseif $issue && $issue->getAccessStatus() == $smarty.const.ISSUE_ACCESS_OPEN}
                        <span class="c-meta__item c-meta__item--block-at-lg 2" itemprop="openAccess" data-test="open-access">
                            <span class="u-color-open-access">Open Access</span>
                        </span>
                    {elseif $currentJournal && $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}
                        <span class="c-meta__item c-meta__item--block-at-lg 3" itemprop="openAccess" data-test="open-access">
                            <span class="u-color-open-access">Open Access</span>
                        </span>
                    {/if}
					
					<time class="c-meta__item c-meta__item--block-at-lg" datetime="{$article->getDatePublished()|date_format:"%e %B %Y"|escape}" itemprop="datePublished">{$article->getDatePublished()|date_format:'%e %b %Y'|escape}</time>
					
					<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg u-text-bold" data-test="journal-title-and-link"><a title="Go to {$journal->getLocalizedTitle()|escape}" target="_blank" href="{url journal=$journal->getPath()}">{$journal->getLocalizedTitle()|escape}</a></div>
					
					{assign var="doi" value=$article->getStoredPubId('doi')}
					{if $article->getPubId('doi')}
					<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="info-DOI"><a title="Permanent link for {$article->getLocalizedTitle()|strip_tags|escape}" href="http://doi.org/{$article->getPubId('doi')|escape}">DOI: {$article->getPubId('doi')|escape}</a></div>
					{/if}
					
					{if $currentJournal}
    					{foreach from=$article->getGalleys() item=galley name=galleyList}
    					{if $galley->isPdfGalley() && $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
    					<div class="u-hide c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="volume-and-page-info"><a title="{$article->getLocalizedTitle()|strip_tags|escape}" href="{url page="article" op="view" path=$articlePath|to_array:$galley->getBestGalleyId($currentJournal)|escape}" {if $galley->getRemoteURL()}target="_blank" {/if}class="file">Fulltext <span class="fileSize">({$galley->getLabel()|escape}, {$galley->getNiceFileSize()|escape})</span> <span class="fileView">{$galley->getViews()|escape} views</span></a></div>
    					{/if}
    					{/foreach}
					{/if}
					
					<div class="u-hide c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="volume-and-page-info"><a title="{$article->getLocalizedTitle()|strip_tags|escape}"{if $galley && $galley->isHTMLGalley()} href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)|escape}"{else}href="{url page="article" op="view" path=$articlePath}"{/if}>{if $galley && $galley->isHTMLGalley()}Article {elseif $article->getLocalizedAbstract()}{translate key="article.abstract"} {else}{translate key="article.details"} view{/if} <span class="fileView">{$article->getViews()|escape} views</span></a></div>

                    {if $galley}
                        {if $galley->isPdfGalley() && $galley->isHTMLGalley()}
                        <span class="total-views">Total {math|number_format:0 equation="x + y + z" x=$article->getViews()|escape y=$galley->getViews($isPdfGalley)|escape z=$galley->getViews($isHTMLGalley)|escape} views</span>
                        {elseif $galley->isPdfGalley() || $galley->isHTMLGalley()}
                        <span class="total-views">Total {math|number_format:0 equation="x + y" x=$article->getViews()|escape y=$galley->getViews()|escape} views</span>
                        {else}
                        <span class="total-views">Total {math|number_format:0 equation="x + y + z" x=$article->getViews()|escape y=$galley->getViews($isPdfGalley)|escape z=$galley->getViews($isHTMLGalley)|escape} views</span>
                        {/if}
                    {else}
                    <span class="total-views">Total {$article->getViews()|number_format:0|escape} views</span>
                    {/if}
					
					<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="volume-and-page-info">{translate key="issue.vol"} {$issue->getVolume()|strip_tags|escape}, {translate key="issue.no"} {$issue->getNumber()|escape}{if $article->getPages()}, P: {$article->getPages()|escape}{else}Article {$article->getBestArticleId()|escape}{/if}</div>
				</div>
			</article>
		</div>
	</li>

{else}

	<li class="app-article-list-row__item wizdam-last">
		<div class="u-full-height" data-native-ad-placement="false">
			<article class="u-full-height c-card c-card--flush" itemtype="http://schema.org/ScholarlyArticle">
				<div class="c-card__layout u-full-heights">
					{if $showCoverPage}
					<div class="c-card__image">
						<picture>
                        {if $currentJournal}
							<source type="image/webp" srcset="{$publicFilesDir}/{$article->getLocalizedFileName()|escape}?as=webp 160w,{$publicFilesDir}/{$article->getLocalizedFileName()|escape}?as=webp 290w">
							<img class="lazyload" src="{$publicFilesDir}/{$article->getLocalizedFileName()|escape}"{if $article->getLocalizedCoverPageAltText() != ''} alt="{$article->getLocalizedCoverPageAltText()|escape}"{else} alt="{translate key="article.coverPage.altText"}"{/if} itemprop="image">
                        {else}
                            {* Konteks site search - gunakan path journal spesifik *}
                            <source type="image/webp" srcset="{$baseUrl}/public/journals/{$article->getJournalId()}/{$article->getLocalizedFileName()|escape}?as=webp 160w,{$baseUrl}/public/journals/{$article->getJournalId()|escape}/{$article->getLocalizedFileName()|escape}?as=webp 290w">
                            <img src="{$baseUrl}/public/journals/{$article->getJournalId()|escape}/{$article->getLocalizedFileName()|escape}" alt="{$article->getLocalizedCoverPageAltText()|escape}" itemprop="image">
                        {/if}
						</picture>
					</div>
					{/if}
					{call_hook name="Templates::Issue::Issue::ArticleCoverImage"}
					
					<div class="c-card__body u-display-flex u-flex-direction-column">
						<h3 class="c-card__title" itemprop="name headline">
							<a href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}" class="c-card__link u-link-inherit" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link">{$article->getLocalizedTitle()|strip_unsafe_html}</a>
						</h3>
						{if $article->getLocalizedAbstract()}
						<div class="c-card__summary abstract-content u-mb-16 u-hide-sm-max" itemprop="description"><p>{if $showCoverPage}{$article->getLocalizedAbstract()|strip_tags|nl2br|truncate:170:"..."}{else}{$article->getLocalizedAbstract()|strip_tags|nl2br|truncate:270:"..."}{/if}</p></div>
						{/if}
						<ul class="c-author-list c-author-list--compact c-author-list--separated u-mt-auto" data-test="author-list">{assign var=authors value=$article->getAuthors()}{foreach from=$authors item=author name=authors}{assign var=fullname value=$author->getFullName()}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}<li itemprop="creator" itemtype="http://schema.org/Person">{if $fullname}<span class="u-hide" itemprop="name">{$fullname|escape}</span>{if $authorFirstName !== $authorLastName}<span itemprop="name">{$authorFirstName|escape}</span>{/if}{if $authorMiddleName}<span itemprop="name">{$authorMiddleName|truncate:1:"."|escape}</span>{/if}<span itemprop="name">{$authorLastName|escape}</span>{/if}</li>{/foreach}
						</ul>
					</div>
				</div>
				<div class="c-card__section c-meta">
					{if $issue->getPublished() && $section && $journal}
					<span class="c-meta__item c-meta__item--block-at-lg" data-test="article.type">
						<span class="c-meta__type">{$section->getLocalizedTitle()|escape}</span>
					</span>
					{/if}
					
                    {if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}
                        <span class="c-meta__item c-meta__item--block-at-lg 1" itemprop="openAccess" data-test="open-access">
                            <span class="u-color-open-access">Open Access</span>
                        </span>
                    {elseif $issue && $issue->getAccessStatus() == $smarty.const.ISSUE_ACCESS_OPEN}
                        <span class="c-meta__item c-meta__item--block-at-lg 2" itemprop="openAccess" data-test="open-access">
                            <span class="u-color-open-access">Open Access</span>
                        </span>
                    {elseif $currentJournal && $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN}
                        <span class="c-meta__item c-meta__item--block-at-lg 3" itemprop="openAccess" data-test="open-access">
                            <span class="u-color-open-access">Open Access</span>
                        </span>
                    {/if}
					
					<time class="c-meta__item c-meta__item--block-at-lg" datetime="{$article->getDatePublished()|date_format:"%e %B %Y"|escape}" itemprop="datePublished">{$article->getDatePublished()|date_format:'%e %b %Y'|escape}</time>
					
    				<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg u-text-bold" data-test="journal-title-and-link"><a title="Go to {$journal->getLocalizedTitle()|escape}" target="_blank" href="{url journal=$journal->getPath()}">{$journal->getLocalizedTitle()|escape}</a></div>
    
    				{assign var="doi" value=$article->getStoredPubId('doi')}
    				{if $article->getPubId('doi')}
    				<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="info-DOI"><a title="Permanent link for {$article->getLocalizedTitle()|strip_tags|escape}" href="http://doi.org/{$article->getPubId('doi')|escape}">DOI: {$article->getPubId('doi')|escape}</a></div>
    				{/if}
					
    				{foreach from=$article->getGalleys() item=galley name=galleyList}
    				{if $galley->isPdfGalley() && $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
    				<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="volume-and-page-info"><a title="{$article->getLocalizedTitle()|strip_tags|escape}" href="{url page="article" op="view" path=$articlePath|to_array:$galley->getBestGalleyId($currentJournal)|escape}" {if $galley->getRemoteURL()}target="_blank" {/if}class="file">Download <span class="fileSize">({$galley->getLabel()|escape}, {$galley->getNiceFileSize()|escape})</span> <span class="fileView">{$galley->getViews()|escape} views</span></a>
    				</div>
    				{/if}
    				{/foreach}
    
    				<div class="u-hide c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="volume-and-page-info"><a title="{$article->getLocalizedTitle()|strip_tags|escape}"{if $galley && $galley->isHTMLGalley()} href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)|escape}"{else}href="{url page="article" op="view" path=$articlePath}"{/if}>{if $galley && $galley->isHTMLGalley()}Article {elseif $article->getLocalizedAbstract()}{translate key="article.abstract"} {else}{translate key="article.details"} view{/if} <span class="fileView">{$article->getViews()|escape} views</span></a>
    				</div>
					
					<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="volume-and-page-info">{translate key="issue.vol"} {$issue->getVolume()|strip_tags|escape}, {translate key="issue.no"} {$issue->getNumber()|escape}{if $article->getPages()}, P: {$article->getPages()|escape}{else}Article {$article->getBestArticleId()|escape}{/if}
					</div>
				</div>
			</article>
		</div>
	</li>
	{/if}

	{/if}
	{/foreach}
</ul>
            </div>
            <!-- Result list -->
        </div>
    </div>    
</section>

{if $user->getScopusId()}
<div class="live-area-wrapper">
    <div class="u-row row">
        <div class="column">
            <!-- Elemen untuk daftar artikel -->
            <div class="articles-list" id="scopus-articles">
                <div data-test="title" class="anchored">
                    <h3 class="anchored"><span class="content-break">Article author in Scopus</span></h3>
                </div>
                <div class="scopus-article-detail" itemprop="scopus"></div>
            </div>
        </div>
{/if}
    </div>
</div>

{include file="common/footer-parts/footer-profile.tpl"}
