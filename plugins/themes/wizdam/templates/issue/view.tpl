{**
 * templates/issue/view.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * View issue -- This displays the issue TOC or title page, as appropriate,
 * *without* header or footer HTML (see viewPage.tpl)
 *}
{if $subscriptionRequired && $showGalleyLinks && $showToc}
	<div id="accessKey" class="access-key block">
	    <section class="access">
	        <span class="open">
	            <img class="u-hide" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
	            <span aria-hidden="false" aria-label="Open Access" data-color="gold" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">OA</span></span>
	            <span class="open-access" data-color="gold">{translate key="reader.openAccess"}</span>
	        </span>&nbsp;|&nbsp;
	        <span class="subscribe">
	            <img src="//www.stipwunaraha.ac.id/static/images/classical/lock.png" alt="{translate key="article.accessLogoRestricted.altText"}" height="17" />
	            <span aria-hidden="false" aria-label="Open Access" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig" data-color="silver"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">S</span></span>
        		<span class="subscribe-access" data-color="silver">
        		{if $purchaseArticleEnabled}
        			{translate key="reader.subscriptionOrFeeAccess"}
        		{else}
        			{translate key="reader.subscriptionAccess"}
        		{/if}
        		</span>
        	</span>
		</section>
	</div>
{/if}


{if !$showToc && $issue}
	{if $issueId}
		{url|assign:"currentUrl" page="issue" op="view" path=$issueId|to_array:"showToc"}
	{else}
		{url|assign:"currentUrl" page="issue" op="current" path="showToc"}
	{/if}

	{if $issue->getLocalizedDescription()}
	<div id="issueDescription" class="block">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</div>
	{/if}
	
	{if $issue->getLocalizedCoverPageDescription()}
	<div id="issueCoverDescription" class="block text-gray-light">{$issue->getLocalizedCoverPageDescription()|strip_unsafe_html|nl2br}</div>
	{/if}
	
	<a href="{$currentUrl}" itemprop="url"><h3 class="issue box-shadow">{translate key="issue.toc"} <span class="articleCount">({$issue->getNumArticles()|escape} {translate key="article.articlesCount"})</span></h3></a>
	
	<ul class="u-hide menu">
		<li><a href="{$currentUrl}">{translate key="issue.toc"}</a></li>
	</ul>

	{if $coverPagePath}
	<div id="issueCoverImage">
	    <a href="{$currentUrl}">
	        <img class="cover__image" {if $coverPageAltText != ''} title="Cover issue {$coverPageAltText|escape}"{else} title="Cover issue {translate key="issue.coverPage.altText"}"{/if} {if $coverPageAltText != ''} alt="{$coverPageAltText|escape}" {else} alt="{translate key="issue.coverPage.altText"}"{/if}{if $width} width="{$width|escape}"{/if}{if $height} height="{$height|escape}"{/if} {if $coverPagePath} src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{/if} width="100%" />
	    </a>
	</div>
	{else}
    	{assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
    	{if $displayHomepageImage && is_array($displayHomepageImage)}
    	<div id="issueCoverImage">
    	    <a href="{$currentUrl}">
    	        <img class="journal-cover__image cover-lazy img-default" title="Cover issue default" src="{$publicFilesDir}/{$displayJournalThumbnail.uploadName|escape:"url"}" alt="Go to {if $currentJournal->getLocalizedTitle()}{$currentJournal->getLocalizedTitle()|strip_tags|escape}{elseif $displayPageHeaderTitle}{$displayPageHeaderTitle}{elseif $siteTitle}{$siteTitle}{else}{$applicationName}{/if}" loading="lazy" style="margin-top: 0" width="100%" />
            </a>
    	</div>
    	{else}
    	<div id="issueCoverImage">
    	    <a href="{$currentUrl}">
    	        <img class="journal-cover__image cover-lazy img-default" title="Cover issue default" src="//media.stipwunaraha.ac.id/img/img-default.jpg" alt="Cover issue default" loading="lazy" style="margin-top: 0" width="100%" />
    	    </a>
    	</div>
    	{/if}
	{/if}

	{if $issue->getLocalizedCoverPageDescription()}
	<div class="credit text-gray-light">{$issue->getLocalizedCoverPageDescription()|strip_unsafe_html|nl2br}</div>
	{/if}
	
	{if $issue->getLocalizedDescription()}
	<div id="issueCoverDescription" class="block">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</div>
	{/if}		

{elseif $issue}
	
	{if $issue->getLocalizedDescription()}
	<div id="issueDescription" class="block">{$issue->getLocalizedDescription()|strip_unsafe_html|nl2br}</div>
	{/if}
	
	{if $issue->getLocalizedCoverPageDescription()}
	<div id="issueCoverDescription" class="block text-gray-light">{$issue->getLocalizedCoverPageDescription()|strip_unsafe_html|nl2br}</div>
	{/if}
	
	{if $issueGalleys}
		<h3 class="issue box-shadow">{translate key="issue.fullIssue"} <span class="articleCount">({$issue->getNumArticles()|escape} {translate key="article.articlesCount"})</span></h3>
		{if (!$subscriptionRequired || $issue->getAccessStatus() == $smarty.const.ISSUE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $issueExpiryPartial))}
			{assign var=hasAccess value=1}
		{else}
			{assign var=hasAccess value=0}
		{/if}
		<div class="issue tocArticle">
			<h3 class="link tocTitle title-issue">{translate key="issue.viewIssueDescription"}</h3>
			<div class="tocArticleGalleysPages">
				<ul id="author-article-InfoList" class="tocMenuArticle ul-list">
				{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
					{foreach from=$issueGalleys item=issueGalley}
					{if $issueGalley->isPdfGalley()}
					<li class="tocMenuArticle pubDOI galley-issue">
						<a href="{url page="issue" op="download" path=$issue->getBestIssueId()|to_array:$issueGalley->getBestGalleyId($currentJournal)}" class="file" target="_blank">Download {$issueGalley->getLabel()|escape} <span class="fileSize">({$issueGalley->getNiceFileSize()})</span> <span class="fileView">{$issueGalley->getViews()} views</span></a>
					</li>
					<li class="tocMenuArticle pubDOI galley-issue">
						<a href="{url page="issue" op="viewFile" path=$issue->getBestIssueId()|to_array:$issueGalley->getBestGalleyId($currentJournal)}" class="file" target="_blank">View {$issueGalley->getLabel()|escape} file <span class="fileSize">({$issueGalley->getNiceFileSize()})</span> <span class="fileView">{$issueGalley->getViews()} views</span></a>
					</li>					
					{else}
					<li class="tocMenuArticle pubDOI galley-issue">
						<a href="{url page="issue" op="viewDownloadInterstitial" path=$issue->getBestIssueId()|to_array:$issueGalley->getBestGalleyId($currentJournal)}" class="file">Download {$issueGalley->getLabel()|escape} <span class="fileSize">({$issueGalley->getNiceFileSize()})</span> <span class="fileView">{$issueGalley->getViews()} views</span></a>
					</li>
					{/if}
					{/foreach}
				{/if}
				</ul>
			</div>
		</div>
	{/if}
	
	</section>
	
    <h3 article_count="{$issue->getNumArticles()|escape} {translate key="article.articlesCount"}" class="issue box-shadow" itemprop="toc__title">{translate key="issue.toc"} <span class="articleCount">({$issue->getNumArticles()|escape} {translate key="article.articlesCount"})</span></h3>

        {include file="issue/issue.tpl"}
    {else}
        {translate key="current.noCurrentIssueDesc"}
    {/if}
            
</div>
