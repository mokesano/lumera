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

<ol class="toc" style="margin-top: 0;">
    
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

	{if $article->getGalleys()}

	<li {if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf && !$article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}class="no-access"{/if}>
	
		<div class="toc-item">
		    
		    {if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}
    			<p class="no-access-message more">
                  <img src="//assets.sangia.org/image/classical/lock.png" alt="PDF file No Access" title="PDF file No Access">
                </p>
    			{if $section.title}
    	        <p class="content-type">{$section.title|escape}</p>
    	        {/if}
	        {else}
    			{if $section.title}
    	        <p class="content-type">{$section.title|escape}</p>
    	        {/if}
    	        <div class="lozenges">
    	        	<div class="open-access"><span class="lozenge lozenge--style1">Open Access</span></div>
    	        </div>
	        {/if}
	           
	        <h3 class="title">
			{if !$hasAccess || $hasAbstract}
				<a href="{url page="article" op="view" path=$articlePath}">{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}</a>
			{else}
				{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}
			{/if}
			</h3>
			    
			{if $article->getLocalizedAbstract()}
			<div class="abstract-content formatted">{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}</div>
			{/if}
			
			{if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
			{else}    
			<span class="authors">{assign var=count value=0}{assign var=authors value=$article->getAuthors()}{foreach from=$authors item=author name=authors key=i}{assign var=authorCount value=$authors|@count}{assign var=fullname value=$author->getFullName()}{assign var="pageTitle" value="search.authorIndex"}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}{assign var=authorCountry value=$author->getCountry()}{assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}{assign var=authorName value="$authorName $authorMiddleName"}{/if}{assign var="contact" value=$author->getData('primaryContact')}{assign var=count value=$count+1}<a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" title="Go to profile: {if $fullname}{$fullname|escape}{/if}" target="_blank">{if $authorFirstName !== $authorLastName}<span class="text given-name">{$authorFirstName|escape} </span>{/if}{if $authorMiddleName}<span class="text middle-name">{$authorMiddleName|escape}</span>{/if}<span class="text surname">{$authorLastName|escape}</span></a>{/foreach}
            </span>
            {/if}

            <small class="citation-info">{if $article->getPages()}Pages {$article->getPages()|escape}{else}Article {$article|@count}{/if}</small>

            <div class="actions">
				{foreach from=$article->getGalleys() item=galley name=galleyList}
            	{if $galley->isPdfGalley() && $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
				<span class="action">
					<a id="toc-pdf-link" class="webtrekk-track pdf-link" title="{$article->getLocalizedTitle()|strip_unsafe_html}" href="{url page="article" op="view" path=$articlePath|to_array:$galley->getBestGalleyId($currentJournal)}" target="_blank" {if $galley->getRemoteURL()}target="_blank" {/if}class="file">Download {$galley->getLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</a> <span>{$galley->getViews()} views</span>
					{if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf && $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley()}<img class="accessLogo u-hide" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />{else}<img class="accessLogo u-hide" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />{/if}
					{if $subscriptionRequired && $showGalleyLinks && !$restrictOnlyPdf}
					{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}<img class="accessLogo u-hide" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />{else}<img class="accessLogo u-hide" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />{/if}{/if}
				</span>
				{/if}
				{/foreach}

				<span class="action">
					<a class="file" title="{$article->getLocalizedTitle()|strip_unsafe_html}" {if $galley}{if $galley->isHTMLGalley()} href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}"{else}href="{url page="article" op="view" path=$articlePath}"{/if}{/if}>{if $galley->isHTMLGalley()}View Article{elseif $article->getLocalizedAbstract()}View {translate key="article.abstract"}{else}View {translate key="article.details"}{/if}</a> <span class="fileView">{$article->getViews()} views</span>
				</span>
			</div>
    	{call_hook name="Templates::Issue::Issue::Article"}			
	    </div>
    	
	</li>

	{else}

	<li class="no-access">
		<div class="toc-item">
			<p class="no-access-message nomore">
              <img src="//assets.sangia.org/image/classical/lock.png" alt="PDF file No Access" title="PDF file No Access">
            </p>
			{if $section.title}
	        <p class="content-type">{$section.title|escape}</p>
	        {/if}
	           
	        <h3 class="title">
			{if !$hasAccess || $hasAbstract}
				<a href="{url page="article" op="view" path=$articlePath}">{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}</a>
			{else}
				{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}
			{/if}
			</h3>
			    
			{if $article->getLocalizedAbstract()}
			<div class="abstract-content formatted">{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}</div>
			{/if}
			    
			{if (!$article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
            {else}
			<span class="authors">{assign var=count value=0}{assign var=authors value=$article->getAuthors()}{foreach from=$authors item=author name=authors key=i}{assign var=authorCount value=$authors|@count}{assign var=fullname value=$author->getFullName()}{assign var="pageTitle" value="search.authorIndex"}{assign var=authorFirstName value=$author->getFirstName()}{assign var=authorMiddleName value=$author->getMiddleName()}{assign var=authorLastName value=$author->getLastName()}{assign var=authorAffiliation value=$author->getLocalizedAffiliation()}{assign var=authorCountry value=$author->getCountry()}{assign var=authorName value="$authorLastName, $authorFirstName"}{if $authorMiddleName != ''}{assign var=authorName value="$authorName $authorMiddleName"}{/if}{assign var="contact" value=$author->getData('primaryContact')}{assign var=count value=$count+1}<a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" title="Go to profile: {if $fullname}{$fullname|escape}{/if}" target="_blank">{if $authorFirstName !== $authorLastName}<span class="text given-name">{$authorFirstName|escape} </span>{/if}{if $authorMiddleName}<span class="text middle-name">{$authorMiddleName|escape}</span>{/if}<span class="text surname">{$authorLastName|escape}</span></a>{/foreach}
            </span>
            {/if}

            <small class="citation-info">{if $article->getPages()}Pages {$article->getPages()|escape}{else}Article {$article|@count}{/if}</small>

            <div class="u-hide actions">
				{foreach from=$article->getGalleys() item=galley name=galleyList}
            	{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
				<span class="action">
					<a id="toc-pdf-link" class="webtrekk-track pdf-link" title="{$article->getLocalizedTitle()|strip_unsafe_html}" href="{url page="article" op="view" path=$articlePath|to_array:$galley->getBestGalleyId($currentJournal)}" target="_blank" {if $galley->getRemoteURL()}target="_blank" {/if}class="file">Download {$galley->getLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</a> <span>{$galley->getViews()} views</span>
					{if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}
					{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley()}<img class="accessLogo u-hide" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />{else}<img class="accessLogo u-hide" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />{/if}{/if}
					{if $subscriptionRequired && $showGalleyLinks && !$restrictOnlyPdf}
					{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}<img class="accessLogo u-hide" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />{else}<img class="accessLogo u-hide" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />{/if}{/if}
				</span>
				{/if}
				{/foreach}

				<span class="action">
					<a class="file" title="{$article->getLocalizedTitle()|strip_unsafe_html}" href="{url page="article" op="view" path=$articlePath}">{if $article->getLocalizedAbstract()}View Article{else}{translate key="article.details"}{/if}</a> <span class="fileView">{$article->getViews()} views</span>
				</span>
			</div>
    	{call_hook name="Templates::Issue::Issue::Article"}
	    </div>
	</li>
	{/if}	

{/foreach}

{/foreach}

	{if $issueGalleys}
		<li>
			<div class="toc-item">
				<p class="content-type">{translate key="issue.fullIssue"}</p>

		    <div class="lozenges">
		    	<div class="open-access"><span class="lozenge">Open Access</span></div>
	        </div>	
		{if (!$subscriptionRequired || $issue->getAccessStatus() == $smarty.const.ISSUE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $issueExpiryPartial))}
			{assign var=hasAccess value=1}
		{else}
			{assign var=hasAccess value=0}
		{/if}

		<h3 class="title">{translate key="issue.viewIssueDescription"} {$issue->getLocalizedTitle($currentJournal)|escape}</h3>

		<div class="actions">
			<span class="action">
			{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
				{foreach from=$issueGalleys item=issueGalley}
					{if $issueGalley->isPdfGalley()}
						<a href="{url page="issue" op="viewIssue" path=$issue->getBestIssueId()|to_array:$issueGalley->getBestGalleyId($currentJournal)}" class="file">Download {$issueGalley->getGalleyLabel()|escape} <span class="fileSize">({$issueGalley->getNiceFileSize()})</a> <span>{$issueGalley->getViews()} views</span></a>
					{else}
						<a href="{url page="issue" op="viewDownloadInterstitial" path=$issue->getBestIssueId()|to_array:$issueGalley->getBestGalleyId($currentJournal)}" class="file">Download {$issueGalley->getGalleyLabel()|escape}</a>
					{/if}
					{if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}
						{if $issue->getAccessStatus() == $smarty.const.ISSUE_ACCESS_OPEN || !$issueGalley->isPdfGalley()}
							<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
						{else}
							<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />
						{/if}
					{/if}
				{/foreach}
				{if $subscriptionRequired && $showGalleyLinks && !$restrictOnlyPdf}
					{if $issue->getAccessStatus() == $smarty.const.ISSUE_ACCESS_OPEN}
						<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
					{else}
						<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />
					{/if}
				{/if}
			{/if}
			</span>
		</div>
	</div>
	</li>
	{/if}

</ol>
