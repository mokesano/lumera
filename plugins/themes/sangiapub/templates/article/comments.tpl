{**
 * templates/article/comments.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article View -- Comments component.
 *
 *}
{if $comments}
<div id="comment" class="comment">
    <h6 class="sub-title u-font-serif">{translate key="comments.commentsOnArticle"}</h6>
	<div class="comment_desc">
	    <p class="u-fonts-sans">By submitting a comment you agree to abide by our Terms and Community Guidelines. If you find something abusive or that does not comply with our terms or guidelines please flag it as inappropriate.</p>
	</div>

<ul>
{foreach from=$comments item=comment}
{assign var=poster value=$comment->getUser()}
	<li>
		<a href="{url page="comment" op="view" path=$article->getId()|to_array:$galleyId:$comment->getId()}" target="_parent">{$comment->getTitle()|escape|default:"&nbsp;"}</a>
		{if $comment->getChildCommentCount()==1}
			{translate key="comments.oneReply"}
		{elseif $comment->getChildCommentCount()>0}
			{translate key="comments.nReplies" num=$comment->getChildCommentCount()}
		{/if}

		<br/>

		{if $poster}
			{url|assign:"publicProfileUrl" page="user" op="viewPublicProfile" path=$poster->getId()}
			{translate key="comments.authenticated" userName=$poster->getFullName()|escape publicProfileUrl=$publicProfileUrl}
		{elseif $comment->getPosterName()}
			{translate key="comments.anonymousNamed" userName=$comment->getPosterName()|escape}
		{else}
			{translate key="comments.anonymous"}
		{/if}
		({$comment->getDatePosted()|date_format:$dateFormatShort})
	</li>
{/foreach}
</ul>

<a href="{url page="comment" op="view" path=$article->getId()|to_array:$galleyId}" class="action" target="_parent">{translate key="comments.viewAllComments"}</a>

{assign var=needsSeparator value=1}
</div>
{else}
<div id="comment" class="comment">
	<h6 class="sub-title u-font-serif">{translate key="comments.commentsOnArticle"}</h6>
	<div class="comment_desc">
	    <p class="u-fonts-sans">By submitting a comment you agree to abide by our Terms and Community Guidelines. If you find something abusive or that does not comply with our terms or guidelines please flag it as inappropriate.</p>
	</div>
<div id="disqus_thread"></div>
<script>
/**
*  RECOMMENDED CONFIGURATION VARIABLES: EDIT AND UNCOMMENT THE SECTION BELOW TO INSERT DYNAMIC VALUES FROM YOUR PLATFORM OR CMS.
*  LEARN WHY DEFINING THESE VARIABLES IS IMPORTANT: https://disqus.com/admin/universalcode/#configuration-variables*/
/*
var disqus_config = function () {
this.page.url = PAGE_URL;  // Replace PAGE_URL with your page's canonical URL variable
this.page.identifier = PAGE_IDENTIFIER; // Replace PAGE_IDENTIFIER with your page's unique identifier variable
};
*/
(function() { // DON'T EDIT BELOW THIS LINE
var d = document, s = d.createElement('script');
s.src = 'https://cosire.disqus.com/embed.js';
s.setAttribute('data-timestamp', +new Date());
(d.head || d.body).appendChild(s);
})();
</script>
<noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
                            
<script id="dsq-count-scr" src="//cosire.disqus.com/count.js" async></script>                            	
</div>
{/if}{* $comments *}

{if $postingAllowed}
	{if $needsSeparator}
		&nbsp;|&nbsp;
	{else}
		<br/><br/>
	{/if}
	<a class="action" href="{url page="comment" op="add" path=$article->getId()|to_array:$galleyId}" target="_parent">{translate key="rt.addComment"}</a>
{/if}

