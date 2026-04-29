{**
 * templates/about/displayMembership.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display group membership information.
 *
 *}
{strip}
{assign var="pageTitle" value="about.people"}
{include file="common/header.tpl"}
{/strip}

<div id="displayMembership" class="member">
<h2 class="sub_title u-font-sans">{$group->getLocalizedTitle()}</h2>
{assign var=groupId value=$group->getId()}
<div class="editor-detail">
    <ul class="editorialTeam">
        {foreach from=$memberships item=member}
        	{assign var=user value=$member->getUser()}
        	<div id="group" class="member"><li><a href="{url op="editorialTeamBio" path=$user->getId()}">{$user->getFullName()|escape},</a>{if $user->getLocalizedAffiliation()} <em>{$user->getLocalizedAffiliation()|escape}{/if}{if $user->getCountry()}{assign var=countryCode value=$user->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</em></li>
    	</div>
		{/foreach}
	</ul>
</div>
</div>

{include file="common/footer.tpl"}

