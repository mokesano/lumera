{**
 * templates/issue/issue.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Issue
 *
 *}

{foreach name=sections from=$publishedArticles item=section key=sectionId}

{foreach from=$section.articles item=article}
{assign var=articlePath value=$article->getBestArticleId($currentJournal)}
{assign var=articleId value=$article->getId()}

{if $article->getLocalizedFileName() && $article->getLocalizedShowCoverPage() && !$article->getHideCoverPageToc($locale)}
	{assign var=showCoverPage value=true}
{else}
	{assign var=showCoverPage value=false}
{/if}

{if $article->getLocalizedAbstract() == ""}
	{assign var=hasAbstract value=0}
{else}
	{assign var=hasAbstract value=1}
{/if}

{if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $articleExpiryPartial.$articleId))}
	{assign var=hasAccess value=1}
{else}
	{assign var=hasAccess value=0}
{/if}
	
{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
{if $subscriptionRequired && $showGalleyLinks && $restrictOnly && $restrictOnlyPdf || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_SUBSCRIPTION}

<article id="{$article->getID()|escape|string_format:"%07d"}" class="issue{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}{else} no-access{/if} first" itemtype="http://schema.org/ScholarlyArticle">
    {if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}
    <section class="tocMenuArticle" itemprop="name section">
	    <span class="c-meta li-last">
	        {if $section.title}<type class="c-meta__item tocSectionTitle u-mb-0" title="Article type">{$section.title|escape}</type>{/if}<time title="Published" datetime="{$article->getDatePublished()|date_format:"$dateFormatShort"}" itemprop="datePublished" > {$article->getDatePublished()|date_format:"%d %B %Y"}</time>
	    </span>
	    <type class="ArtType" title="Type of article published" itemprop="openAccess" data-test="open-access">Open Access</type>
    </section>
    {elseif $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_SUBSCRIPTION}
    <section class="tocMenuArticle" itemprop="name section">
		<span class="message">
			<img class="content-type" loading="lazy" src="//www.stipwunaraha.ac.id/static/images/classical/lock.png" alt="Purchase" title="PDF not avalable or No access"><span class="c-meta content-type li-last">
			    {if $section.title}<type class="c-meta__item tocSectionTitle u-mb-0" title="Article type">{$section.title|escape}</type>{/if}<time title="Published date"> {$article->getDatePublished()|date_format:"%d %B %Y"}</time>
			    </span>
		</span>
    </section>    
    {/if}
	           
    <h3 class="link tocTitle title-issue" itemprop="name headline">
	{if !$hasAccess || $hasAbstract}
		<a itemprop="url" href="{url page="article" op="view" path=$articlePath}">{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}</a>
	{else}
		<a itemprop="url" href="{url page="article" op="view" path=$articlePath}">{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}</a>
	{/if}
	</h3>
			    
	{if $article->getLocalizedAbstract()}
	<div class="u-hide abstract-content formatted" itemprop="description">{$article->getLocalizedAbstract()|truncate:170:"..."|strip_unsafe_html|nl2br}</div>
	{/if}
			    
	{if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
	{else}
	<div class="tocAuthors u-mb-4" itemprop="creator" itemtype="http://schema.org/Person">{assign var=count value=0}{assign var=authors value=$article->getAuthors()}{foreach from=$authors item=author name=authors key=i}{assign var=authorCount value=$authors|@count}{assign var=fullname value=$author->getFullName()}{assign var="pageTitle" value="search.authorIndex"}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}{assign var=authorCountry value=$author->getCountry()}{assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}{assign var=authorName value="$authorName $authorMiddleName"}{/if}{assign var="contact" value=$author->getData('primaryContact')}{assign var=count value=$count+1}{if $fullname}<a class="anchor" itemprop="creator" href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" title="Go to profile: {if $fullname}{$fullname|escape}{/if}" target="_blank">{if $authorFirstName !== $authorLastName}<span class="anchor-text given-name" itemprop="name">{if $authorMiddleName}{$authorFirstName|truncate:1:"."}{else}{$authorFirstName}{/if}</span>{/if}{if $authorMiddleName}<span class="anchor-text middle-name" itemprop="name">{$authorMiddleName|truncate:1:"."}</span>{/if}{if $authorLastName}<span class="anchor-text surname" itemprop="name">{$authorLastName}</span>{/if}</a>{else}{/if}{/foreach}
    </div>
    {/if}

    <div class="tocArticleGalleysPages">
		<ul id="author-article-InfoList" class="tocMenuArticle ul-list">
            <page class="tocPages page-issue">{if $article->getPages()}Pages {$article->getPages()|escape}{else}Article {$article->getId()}{/if}</page>
	                
			{foreach from=$article->getGalleys() item=galley name=galleyList}
        	{if $hasAccess || ($subscriptionRequired && $showGalleyLinks) && $galley->isPdfGalley() && $issueAvailable}
        	<li class="tocMenuArticle pubDOI galley-issue">
				<a itemprop="url" id="toc-pdf-link" class="webtrekk-track pdf-link" title="{$article->getLocalizedTitle()|strip_unsafe_html}" href="{url page="article" op="view" path=$articlePath|to_array:$galley->getBestGalleyId($currentJournal)}" target="_blank" class="file">{if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley() || $galley->getRemoteURL()}Download {$galley->getGalleyLabel()|escape}{elseif $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_SUBSCRIPTION}Get {$galley->getGalleyLabel()|escape} Access{/if}{else}Download {$galley->getGalleyLabel()|escape}{/if} <span class="fileSize">({$galley->getNiceFileSize()}) <span>{$galley->getViews()} views</span></a>
			</li>				
			{/if}
			{/foreach}			
    				
			{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
			<li class="tocMenuArticle pubDOI link-issue"><a itemprop="url" title="{$article->getLocalizedTitle()|strip_tags|escape}"{if $galley}{if $galley->isHTMLGalley()} href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}"{else}href="{url page="article" op="view" path=$articlePath}"{/if}{/if}>{if $galley->isHTMLGalley()}View {translate key="article.article"}{elseif $article->getLocalizedAbstract()}View {translate key="article.abstract"}{else}View {translate key="article.details"}{/if} <span class="fileView">{$article->getViews()} views</span></a></li>
			{/if}

		</ul>
	</div>
	<div class="c-meta">
	    {call_hook name="Templates::Issue::Issue::Article"}
	</div>		
</article>

{else}
    	        
<article id="{$article->getID()|escape|string_format:"%07d"}" class="issue{if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_SUBSCRIPTION} no-access{/if} last" itemtype="http://schema.org/ScholarlyArticle">
    {if $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}
    <section class="tocMenuArticle" itemprop="name section">
	    <span class="c-meta li-last">
	        {if $section.title}<type class="c-meta__item tocSectionTitle u-mb-0" title="Article type">{$section.title|escape}</type>{/if}<time title="Published" datetime="{$article->getDatePublished()|date_format:"$dateFormatShort"}" itemprop="datePublished" > {$article->getDatePublished()|date_format:"%d %B %Y"}</time>
	    </span>
	    <type class="ArtType" title="Type of article published" itemprop="openAccess" data-test="open-access">Open Access</type>
    </section>
    {elseif $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_SUBSCRIPTION}
    <section class="tocMenuArticle" itemprop="name section">
		<span class="message">
			<img class="content-type" loading="lazy" src="//www.stipwunaraha.ac.id/static/images/classical/lock.png" alt="Purchase" title="PDF not avalable or No acces">
			<span class="c-meta content-type li-last">
			    {if $section.title}<type class="c-meta__item tocSectionTitle u-mb-0" title="Article type">{$section.title|escape}</type>{/if}<time title="Published date"> {$article->getDatePublished()|date_format:"%d %B %Y"}</time>
			    </span>
		</span>
    </section>    
    {/if}
	           
    <h3 class="link tocTitle title-issue" itemprop="name headline">
	{if !$hasAccess || $hasAbstract}
		<a itemprop="url" href="{url page="article" op="view" path=$articlePath}">{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}</a>
	{else}
		<a itemprop="url" href="{url page="article" op="view" path=$articlePath}">{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}</a>
	{/if}
	</h3>
			    
	{if $article->getLocalizedAbstract()}
	<div class="abstract-content formatted" itemprop="description">{$article->getLocalizedAbstract()|nl2br}</div>
	{/if}
			    
	{if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
	{else}
	<div class="tocAuthors u-mb-4" itemprop="creator" itemtype="http://schema.org/Person">{assign var=count value=0}{assign var=authors value=$article->getAuthors()}{foreach from=$authors item=author name=authors key=i}{assign var=authorCount value=$authors|@count}{assign var=fullname value=$author->getFullName()}{assign var="pageTitle" value="search.authorIndex"}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}{assign var=authorCountry value=$author->getCountry()}{assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}{assign var=authorName value="$authorName $authorMiddleName"}{/if}{assign var="contact" value=$author->getData('primaryContact')}{assign var=count value=$count+1}{if $fullname}<a class="anchor" itemprop="creator" href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" title="Go to profile: {if $fullname}{$fullname|escape}{/if}" target="_blank">{if $authorFirstName !== $authorLastName}<span class="anchor-text given-name" itemprop="name">{if $authorMiddleName}{$authorFirstName|truncate:1:"."}{else}{$authorFirstName}{/if}</span>{/if}{if $authorMiddleName}<span class="anchor-text middle-name" itemprop="name">{$authorMiddleName|truncate:1:"."}</span>{/if}{if $authorLastName}<span class="anchor-text surname" itemprop="name">{$authorLastName}</span>{/if}</a>{else}{/if}{/foreach}
    </div>
    {/if}

    <div class="tocArticleGalleysPages">
		<ul id="author-article-InfoList" class="tocMenuArticle ul-list">
            {assign var=count value=0}
            {assign var=pages value=$publishedArticles()}
            {assign var=pagesCount value=$pages|@count}
            {assign var=count value=$count+1}
            <page class="tocPages page-issue">{if $article->getPages()}Pages {$article->getPages()|escape}{else}Article ID-{$article->getID()|escape}{/if}</page>
	                
			{foreach from=$article->getGalleys() item=galley name=galleyList}
        	{if $hasAccess || ($subscriptionRequired && $showGalleyLinks) && $galley->isPdfGalley()}
        	<li class="tocMenuArticle pubDOI galley-issue">
				<a itemprop="url" id="toc-pdf-link" class="webtrekk-track pdf-link" title="{$article->getLocalizedTitle()|strip_unsafe_html}" href="{url page="article" op="view" path=$articlePath|to_array:$galley->getBestGalleyId($currentJournal)}" target="_blank" class="file">{if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley() || $galley->getRemoteURL()}Download {$galley->getLabel()|escape}{elseif $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_SUBSCRIPTION}Get {$galley->getLabel()|escape} Access{/if}{else}Download {$galley->getLabel()|escape}{/if} <span class="fileSize">({$galley->getNiceFileSize()}) <span>{$galley->getViews()} views</span></a>
			</li>				
			{/if}
			{/foreach}

			{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
			<li class="tocMenuArticle pubDOI link-issue">
				<a itemprop="url" title="{$article->getLocalizedTitle()|strip_tags|escape}" href="{url page="article" op="view" path=$articlePath}">{if $article->getLocalizedAbstract()}View {translate key="article.abstract"}{else}View {translate key="article.details"}{/if} <span class="fileView">{$article->getViews()} views</span></a>
			</li>
			{/if}
		</ul>
	</div>
	<div class="c-meta">
	    {call_hook name="Templates::Issue::Issue::Article"}
	</div>
</article>
	    
{/if}
{/if}

{/foreach}

{/foreach}

