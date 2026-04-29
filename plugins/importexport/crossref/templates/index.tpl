{**
 * @file plugins/importexport/crossref/templates/index.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * DataCite plug-in home page.
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.crossref.displayName"}
{include file="common/header-parts/header-manager.tpl"}
{/strip}

<div id="crossref" class="crossref-article">
    <p>
        {translate key="plugins.importexport.crossref.registrationIntro"}
        {capture assign="settingsUrl"}{plugin_url path="settings"}{/capture}
    </p>
</div>

<div id="crossref" class="manage-doi-crossref">
    <h3 class="u-h2">{translate key="plugins.importexport.common.export"}</h3>
    {if !empty($configurationErrors) || !$currentJournal->getSetting('publisherInstitution')|escape}
    	<p>{translate key="plugins.importexport.common.export.unAvailable"}</p>
    {else}
    	<ul>
    		<li><a href="{plugin_url path="articles"}">{translate key="plugins.importexport.crossref.manageArticleDOIs"}</a></li>
    		<li><a href="{plugin_url path="issues"}">{translate key="plugins.importexport.crossref.manageDOIs"}</a></li>
    	</ul>
    {/if}
</div>

<div id="crossref" class="settings-doi-crossref">
    <h3 class="u-h2">{translate key="plugins.importexport.common.settings"}</h3>
    <br />
    <p>
        {translate key="plugins.importexport.crossref.settings.description" settingsUrl=$settingsUrl}
    </p>
</div>

{include file="common/footer-parts/footer-user.tpl"}
