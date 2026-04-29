{**
 * templates/rtadmin/settings.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * RT Administration settings.
 *
 * [WIZDAM EDITION] Amputated legacy frontend options
 *}
{strip}
{assign var="pageTitle" value="rt.admin.settings"}
{include file="common/header-USER027.tpl"}
{/strip}

<form method="post" action="{url op="saveSettings"}">

<p>{translate key="rt.admin.settings.description"}</p>

<div id="enableRT"><input type="checkbox" {if $enabled}checked="checked" {/if}name="enabled" value="1" id="enabled"/>&nbsp;&nbsp;<label for="enabled">{translate key="rt.admin.settings.enableReadingTools"}</label></div><br/>

<div class="separator"></div>
<div id="rtAdminOptions">
<h3>{translate key="rt.admin.options"}</h3>
<table width="100%" class="data">
    <tr valign="top">
        <td class="label" width="3%"><input type="checkbox" name="abstract" id="abstract" {if $abstract}checked="checked" {/if}/></td>
        <td class="value" width="97%"><label for="abstract">{translate key="rt.admin.settings.abstract"}</label></td>
    </tr>
    <tr valign="top">
        <td class="label"><input type="checkbox" name="captureCite" id="captureCite" {if $captureCite}checked="checked" {/if}/></td>
        <td class="value">
            <label for="captureCite">{translate key="rt.admin.settings.captureCite"}</label><br />
        </td>
    </tr>
    <tr valign="top">
        <td class="label"><input type="checkbox" name="viewMetadata" id="viewMetadata" {if $viewMetadata}checked="checked" {/if}/></td>
        <td class="value"><label for="viewMetadata">{translate key="rt.admin.settings.viewMetadata"}</label></td>
    </tr>
    <tr valign="top">
        <td class="label"><input type="checkbox" name="supplementaryFiles" id="supplementaryFiles" {if $supplementaryFiles}checked="checked" {/if}/></td>
        <td class="value"><label for="supplementaryFiles">{translate key="rt.admin.settings.supplementaryFiles"}</label></td>
    </tr>
</table>
</div>
<div class="separator">&nbsp;</div>
<div id="rtAdminRelatedItems">
    <h3>{translate key="rt.admin.relatedItems"}</h3>
    
    <label for="version">{translate key="rt.admin.settings.relatedItems"}</label>&nbsp;&nbsp;<select name="version" id="version" class="selectMenu">
    <option value="">{translate key="rt.admin.settings.disableRelatedItems"}</option>
    {html_options options=$versionOptions selected=$version}
    </select>
    
    <div class="u-mt-24 u-mb-24">{url|assign:"relatedItemsLink" op="versions"}
    {translate key="rt.admin.settings.relatedItemsLink" relatedItemsLink=$relatedItemsLink}</div>
</div>
<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url page="rtadmin" escape=false}'" /></p>

</form>

{include file="common/footer-parts/footer-user.tpl"}