{**
 * templates/common/current.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Issue
 *
 *}

<ol class="content-item-list">
    
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
	<li {if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf && $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley()}{else}class="no-access"{/if}>

		{if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf && $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley()}

			{if $section.title}
	        <p class="content-type">{$section.title|escape}</p>
	        {/if}

	        <div class="lozenge lozenge--style1 lozenge--article-list help">Open Access</div>

	    {else}
	        
    	   	<p class="no-access-message">
    	   		<img src="//www.stipwunaraha.ac.id/static/images/classical/lock.png" alt="PDF file No Access" title="PDF file No Access">
            </p>
            
			{if $section.title}
	        <p class="content-type">{$section.title|escape}</p>
	        {/if}

	        <div class="lozenges"></div>
	        
	    {/if}
	           
	    <h3>
		{if !$hasAccess || $hasAbstract}
			<a href="{url page="article" op="view" path=$articlePath}">{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}</a>
		{else}
			{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}
		{/if}
		</h3>
			    
		<p class="meta">
			<span class="authors">
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
    	    {assign var=count value=$count+1} <a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" title="Go to profile: {if $fullname}{$fullname|escape}{/if}" target="_blank">{if $fullname}{$fullname|escape}</a>{else}{/if}{if $i==$authorCount-2}, {elseif $i<$authorCount-1}, {/if}
	    {/foreach}
           	</span>
           	<span class="date"><em>({$issue->getDatePublished()|date_format:'%B %Y'})</em></span>
        </p>

        <small class="citation-info">{if $article->getPages()}Pages {$article->getPages()|escape}{else}Article {$article|@count}{/if}</small>

        <div class="actions">
            {foreach from=$article->getGalleys() item=galley name=galleyList}
			{if $galley->isPdfGalley() && $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
			<span class="action">
				<a id="toc-pdf-link" class="webtrekk-track pdf-link" title="{$article->getLocalizedTitle()|strip_tags|escape}" href="{url page="article" op="view" path=$articlePath|to_array:$galley->getBestGalleyId($currentJournal)}" target="_blank" {if $galley->getRemoteURL()}target="_blank" {/if}class="file">Download {$galley->getLabel()|escape} <span class="fileSize">({$galley->getNiceFileSize()})</a> <span>{$galley->getViews()} views</span></span>
			</span>
			{/if}
			{/foreach}
		
			<span class="action">
				<a class="file" title="{$article->getLocalizedTitle()|strip_tags|escape}" href="{url page="article" op="view" path=$articlePath}">{if $galley->isHTMLGalley()}View Article{elseif $article->getLocalizedAbstract()}View {translate key="article.abstract"}{else}View {translate key="article.details"}{/if}</a> <span class="fileView">{$article->getViews()} views</span>
			</span>
			
		{call_hook name="Templates::Issue::Issue::Article"}
		
		</div>
	</li>

	{else}

	<li {if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf && $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}{else}class="no-access"{/if}>
	
		{if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf && $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}

			{if $section.title}
	        <p class="content-type">{$section.title|escape}</p>
	        {/if}

	        <div class="lozenge lozenge--style1 lozenge--article-list help">Open Access</div>

	    {else}
	        
    	   	<p class="no-access-message">
    	   		<img src="//www.stipwunaraha.ac.id/static/images/classical/lock.png" alt="PDF file No Access" title="PDF file No Access">
            </p>
            
			{if $section.title}
	        <p class="content-type">{$section.title|escape}</p>
	        {/if}

	        <div class="lozenges"></div>
	        
	    {/if}
	           
	    <h3 >
		{if !$hasAccess || $hasAbstract}
			<a href="{url page="article" op="view" path=$articlePath}">{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}</a>
		{else}
			{$article->getLocalizedTitle()|strip_unsafe_html|nl2br}
		{/if}
		</h3>
			    
		<p class="meta">
			<span class="authors">
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
    	    {assign var=count value=$count+1} <a href="{url page="search" op="authors" path="view" firstName=$authorFirstName middleName=$authorMiddleName lastName=$authorLastName affiliation=$authorAffiliation country=$authorCountry}" title="Go to profile: {if $fullname}{$fullname|escape}{/if}" target="_blank">{if $fullname}{$fullname|escape}</a>{else}{/if}{if $i==$authorCount-2}, {elseif $i<$authorCount-1}, {/if}
	    {/foreach}
	    	</span>
	       	<span class="date"><em>({$issue->getDatePublished()|date_format:'%B %Y'})</em></span>
	    </p>
	   
        <small class="citation-info">{if $article->getPages()}Pages {$article->getPages()|escape}{else}Article {$article|@count}{/if}</small>
        
	    {call_hook name="Templates::Issue::Issue::Article"}
	    
	</li>
	{/if}
	
{/foreach}
{/foreach}

</ol>
