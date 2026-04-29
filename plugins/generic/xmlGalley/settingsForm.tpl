{**
 * plugins/generic/xmlGalley/settingsForm.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * XML galley plugin settings
 * MODERNIZED FOR SCHOLARWIZDAM FORK
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.xmlGalley.displayName"}
{include file="common/header.tpl"}
{/strip}

<div id="xmlGalleySettings">
<div id="description">{translate key="plugins.generic.xmlGalley.settings.description"}</div>

<div class="separator">&nbsp;</div>

<h3>{translate key="plugins.generic.xmlGalley.manager.settings"}</h3>

<form method="post" action="{plugin_url path="settings"}" enctype="multipart/form-data">
{include file="common/formErrors.tpl"}

{if $testSuccess}
    <div style="font-weight: bold; color: green; margin-bottom: 1em;">
        <ul><li>{translate key="plugins.generic.xmlGalley.settings.externalXSLTSuccess"}</li></ul>
    </div>
{/if}

<table width="100%" class="data">
    <tr valign="top">
        <td width="100%" class="label" colspan="2"><h4 id="XSLTrenderer">{fieldLabel name="XSLTrenderer" required="true" key="plugins.generic.xmlGalley.settings.renderer"}:</h4></td>
    </tr>
    <tr valign="top">
        {* WIZDAM FIX: Sinkronisasi dengan variabel xsltNative dan value Native *}
        <td width="10%" class="label" align="right"><input type="radio" name="XSLTrenderer" id="XSLTrenderer-Native" value="Native" {if !$xsltNative}disabled="disabled"{/if} {if $XSLTrenderer eq "Native"}checked="checked" {/if}/></td>
        <td width="90%" class="value">{translate key="plugins.generic.xmlGalley.settings.native"}
        {if !$xsltNative}<span class="formError">{translate key="plugins.generic.xmlGalley.settings.notAvailable"}</span>{/if}
        </td>
    </tr>
    
    <tr valign="top">
        <td width="10%" class="label" align="right"><input type="radio" name="XSLTrenderer" id="XSLTrenderer-external" value="external" {if $XSLTrenderer eq "external"}checked="checked" {/if}/></td>
        <td width="90%" class="value">{translate key="plugins.generic.xmlGalley.settings.externalXSLT"}</td>
    </tr>
    <tr valign="top">
        <td width="10%" class="label">&nbsp;</td>
        <td width="90%" class="value">{translate key="plugins.generic.xmlGalley.settings.externalXSLTDescription"}</td>
    </tr>
    <tr valign="top">
        <td width="10%" class="label">&nbsp;</td>
        <td width="90%" class="value"><input type="text" name="externalXSLT" id="externalXSLT" value="{$externalXSLT|escape}" size="60" maxlength="90" class="textField" /></td>
    </tr>

{if $XSLTrenderer eq "external"}
    <tr valign="top">
        <td width="10%" class="label">&nbsp;</td>
        <td width="90%" class="value">
        <a href="{plugin_url path="test"}">{translate key="plugins.generic.xmlGalley.settings.externalXSLTTest"}</a>
        </td>
    </tr>
{/if}

</table>

<div class="separator">&nbsp;</div>

<table width="100%" class="data">
    <tr valign="top">
        <td width="100%" class="label" colspan="2"><h4 id="XSLstylesheet">{fieldLabel name="XSLstylesheet" required="true" key="plugins.generic.xmlGalley.settings.stylesheet"}:</h4></td>
    </tr>
    <tr valign="top">
        <td width="10%" class="label" align="right"><input type="radio" name="XSLstylesheet" id="XSLstylesheet-NLM" value="NLM" {if $XSLstylesheet eq "NLM"}checked="checked" {/if}/></td>
        <td width="90%" class="value">{translate key="plugins.generic.xmlGalley.settings.xslNLM"}</td>
    </tr>
    <tr valign="top">
        <td width="10%" class="label" align="right"><input type="checkbox" name="nlmPDF" id="nlmPDF" value="1"{if $nlmPDF==1} checked="checked"{/if} /></td>
        <td width="90%" class="value">{translate key="plugins.generic.xmlGalley.settings.xslFOP"}</td>
    </tr>
    <tr valign="top">
        <td width="10%" class="label">&nbsp;</td>
        <td width="90%" class="value">{translate key="plugins.generic.xmlGalley.settings.xslFOPDescription"}</td>
    </tr>
    <tr valign="top">
        <td width="10%" class="label">&nbsp;</td>
        <td width="90%" class="value"><input type="text" name="externalFOP" id="externalFOP" value="{$externalFOP|escape}" size="60" maxlength="90" class="textField" /></td>
    </tr>
    <tr valign="top">
        <td width="10%" class="label" align="right"><input type="radio" name="XSLstylesheet" id="XSLstylesheet-custom" value="custom" {if $XSLstylesheet eq "custom"}checked="checked" {/if}/></td>
        <td width="90%" class="value">{translate key="plugins.generic.xmlGalley.settings.customXSL"}</td>
    </tr>
    <tr valign="top">
        <td width="10%" />
        <td width="90%" class="value"><input type="file" name="customXSL" class="uploadField" /> <input type="submit" name="uploadCustomXSL" value="{translate key="common.upload"}" class="button" /></td>
    </tr>

{if $customXSL}
    <tr valign="top">
        <td width="10%" class="label">&nbsp;</td>
        <td width="90%" class="value">{translate key="common.fileName"}: {$customXSL|escape} <input type="submit" name="deleteCustomXSL" value="{translate key="common.delete"}" class="button" /></td>
    </tr>
{/if}

</table>

<br />

<input type="submit" name="save" class="button defaultButton" value="{translate key="common.save"}"/> <input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>
</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</div>
{include file="common/footer.tpl"}