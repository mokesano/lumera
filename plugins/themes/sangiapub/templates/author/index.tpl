{**
 * templates/author/index.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Journal author index.
 *
 *}
{strip}
{assign var="pageTitle" value="common.queue.long.$pageToDisplay"}
{include file="common/header-ROLE.tpl"}
{/strip}

<div id="submitStart" class="block submit-box">
    <div class="submit-author c-facet__submit">
        <h3>{translate key="author.submit.startHereTitle"}</h3>
        <p>{url|assign:"submitUrl" op="submit"}
        {translate submitUrl=$submitUrl key="author.submit.startHereLink"}</p>
    </div>
</div>

<ul class="menu">
	<li{if ($pageToDisplay == "active")} class="current"{/if}><a href="{url op="index" path="active"}">{translate key="common.queue.short.active"}</a></li>
	<li{if ($pageToDisplay == "completed")} class="current"{/if}><a href="{url op="index" path="completed"}">{translate key="common.queue.short.completed"}</a></li>
</ul>

{include file="author/$pageToDisplay.tpl"}

{capture assign="additionalItems"}{call_hook name="Templates::Author::Index::AdditionalItems"}{/capture}
{if $additionalItems|trim}
<div class="block refbacks">
{$additionalItems}
</div>
{/if}

{include file="common/footer-parts/footer-user.tpl"}

