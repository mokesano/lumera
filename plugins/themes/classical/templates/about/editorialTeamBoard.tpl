{**
 * templates/about/editorialTeamBoard.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Journal index.
 *
 *}
{strip}
{assign var="pageTitle" value="about.editorialTeam"}
{include file="common/header.tpl"}
{/strip}

{call_hook name="Templates::About::EditorialTeam::Information"}

{foreach from=$groups item=group}
<div id="group" class="editor-board pseudoMenu">
	<h2 class="sub_title">{$group->getLocalizedTitle()}</h2>

<div class="editor-detail">
	{assign var=groupId value=$group->getId()}
	{assign var=members value=$teamInfo[$groupId]}

	<ul class="editorialTeam">
		{foreach from=$members item=member}
			{assign var=user value=$member->getUser()}
			<div class="member"><li><a href="{url op="editorialTeamBio" path=$user->getId()}">{$user->getFullName()|escape}</a>{if $user->getLocalizedAffiliation()}, <em>{$user->getLocalizedAffiliation()|escape}{/if}{if $user->getCountry()}{assign var=countryCode value=$user->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</em></li></div>
		{/foreach}{* $members *}
	</ul>
</div>
</div>
{/foreach}{* $groups *}

{include file="common/footer.tpl"}

