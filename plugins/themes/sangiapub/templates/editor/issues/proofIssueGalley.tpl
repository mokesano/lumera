<!DOCTYPE html>
<html lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
{**
 * templates/editor/issues/proofIssueGalley.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Proof an issue galley.
 *}
{assign var="pageTitle" value="editor.issues.viewingGalley"}
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<title>{translate key=$pageTitle}</title>

	<link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/common.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/styles/common.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/styles/compiled.css" type="text/css" />

	{foreach from=$stylesheets item=cssUrl}
		<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
	{/foreach}
	
	{include file="common/jqueryScripts.tpl"}

	<!-- Compiled scripts -->
	{if $useMinifiedJavaScript}
		<script type="text/javascript" src="{$baseUrl}/js/pkp.min.js"></script>
	{else}
		{include file="common/minifiedScripts.tpl"}
	{/if}

	{$additionalHeadData}
</head>
{url|assign:"galleyUrl" op="proofIssueGalleyFile" path=$issueId|to_array:$galleyId}
<frameset rows="40,*" style="border: 0;">
	<frame src="{url op="proofIssueGalleyTop" path=$issueId}" noresize="noresize" frameborder="0" scrolling="no" />
	<frame src="{$galleyUrl}" frameborder="0" />
<noframes>
<body>
	<table width="100%">
		<tr>
			<td align="center">
				{translate key="common.error.framesRequired" url=$galleyUrl}
			</td>
		</tr>
	</table>
</body>
</noframes>
</frameset>
</html>
