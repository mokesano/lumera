{**
 * templates/about/editorialTeam.tpl
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

<div id="editorialTeam">
{if count($manager) > 0} <!-- settings for view EDITOR-IN-CHIEF-->
	<div id="editors">
	{if count($manager) == 1}
		<h2 class="sub_title">Editor-in-Chief</h2>
	{else}
		<h2 class="sub_title">Editors-in-Chief</h2>
	{/if}

	<ol class="editorialTeam">
		{foreach from=$manager item=manager}
			<li><a href="javascript:openRTWindow('{url op="editorialTeamBio" path=$manager->getId()}')">{$manager->getFullName()|escape}</a>{if $manager->getLocalizedAffiliation()}, {$manager->getLocalizedAffiliation()|escape}{/if}{if $manager->getCountry()}{assign var=countryCode value=$manager->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</li>
		{/foreach}
	</ol>
	</div>
{/if}

{if count($editors) > 0}
	<div id="editors">
	{if count($editors) == 1}
		<h2 class="sub_title">{translate key="user.role.editor"}</h2>
	{else}
		<h2 class="sub_title">{translate key="user.role.editors"}</h2>
	{/if}

	<ol class="editorialTeam">
		{foreach from=$editors item=editor}
			<li><a href="javascript:openRTWindow('{url op="editorialTeamBio" path=$editor->getId()}')">{$editor->getFullName()|escape}</a>{if $editor->getLocalizedAffiliation()}, {$editor->getLocalizedAffiliation()|escape}{/if}{if $editor->getCountry()}{assign var=countryCode value=$editor->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</li>
		{/foreach}
	</ol>
	</div>
{/if}

{if count($sectionEditors) > 0}
	<div id="sectionEditors">
	{if count($sectionEditors) == 1}
		<h2 class="sub_title">{translate key="user.role.sectionEditor"}</h2>
	{else}
		<h2 class="sub_title">{translate key="user.role.sectionEditors"}</h2>
	{/if}

	<ol class="editorialTeam">
		{foreach from=$sectionEditors item=sectionEditor}
			<li><a href="javascript:openRTWindow('{url op="editorialTeamBio" path=$sectionEditor->getId()}')">{$sectionEditor->getFullName()|escape}</a>{if $sectionEditor->getLocalizedAffiliation()}, {$sectionEditor->getLocalizedAffiliation()|escape}{/if}{if $sectionEditor->getCountry()}{assign var=countryCode value=$sectionEditor->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</li>
		{/foreach}
	</ol>
	</div>
{/if}

{if count($reviewers) > 0} <!-- settings for view REVIEWER -->
	<div id="sectionEditors">
	{if count($reviewers) == 1}
		<h2 class="sub_title">{translate key="user.role.reviewer"}</h2>
	{else}
		<h2 class="sub_title">{translate key="user.role.reviewers"}</h2>
	{/if}

	<ol class="editorialTeam">
		{foreach from=$reviewers item=reviewer}
			<li><a href="javascript:openRTWindow('{url op="editorialTeamBio" path=$reviewer->getId()}')">{$reviewer->getFullName()|escape}</a>{if $reviewer->getLocalizedAffiliation()}, {$reviewer->getLocalizedAffiliation()|escape}{/if}{if $reviewer->getCountry()}{assign var=countryCode value=$reviewer->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</li>
		{/foreach}
	</ol>
	</div>
{/if}

{if count($copyEditors) > 0}
	<div id="copyEditors">
	{if count($copyEditors) == 1}
		<h2 class="sub_title">{translate key="user.role.copyeditor"}</h2>
	{else}
		<h2 class="sub_title">{translate key="user.role.copyeditors"}</h2>
	{/if}

	<ol class="editorialTeam">
		{foreach from=$copyEditors item=copyEditor}
			<li><a href="javascript:openRTWindow('{url op="editorialTeamBio" path=$copyEditor->getId()}')">{$copyEditor->getFullName()|escape}</a>{if $copyEditor->getLocalizedAffiliation()}, {$copyEditor->getLocalizedAffiliation()|escape}{/if}{if $copyEditor->getCountry()}{assign var=countryCode value=$copyEditor->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</li>
		{/foreach}
	</ol>
	</div>
{/if}

{if count($proofreaders) > 0}
	<div id="proofreaders">
	{if count($proofreaders) == 1}
		<h2 class="sub_title">{translate key="user.role.proofreader"}</h2>
	{else}
		<h2 class="sub_title">{translate key="user.role.proofreaders"}</h2>
	{/if}

	<ol class="editorialTeam">
		{foreach from=$proofreaders item=proofreader}
			<li><a href="javascript:openRTWindow('{url op="editorialTeamBio" path=$proofreader->getId()}')">{$proofreader->getFullName()|escape}</a>{if $proofreader->getLocalizedAffiliation()}, {$proofreader->getLocalizedAffiliation()|escape}{/if}{if $proofreader->getCountry()}{assign var=countryCode value=$proofreader->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</li>
		{/foreach}
	</ol>
	</div>
{/if}
</div>

{if count($layoutEditors) > 0}
	<div id="layoutEditors">
	{if count($layoutEditors) == 1}
		<h2 class="sub_title">{translate key="user.role.layoutEditor"}</h2>
	{else}
		<h2 class="sub_title">{translate key="user.role.layoutEditors"}</h2>
	{/if}

	<ol class="editorialTeam">
		{foreach from=$layoutEditors item=layoutEditor}
			<li><a href="javascript:openRTWindow('{url op="editorialTeamBio" path=$layoutEditor->getId()}')">{$layoutEditor->getFullName()|escape}</a>{if $layoutEditor->getLocalizedAffiliation()}, {$layoutEditor->getLocalizedAffiliation()|escape}{/if}{if $layoutEditor->getCountry()}{assign var=countryCode value=$layoutEditor->getCountry()}{assign var=country value=$countries.$countryCode}, {$country|escape}{/if}</li>
		{/foreach}
	</ol>
	</div>
{/if}

{include file="common/footer.tpl"}

