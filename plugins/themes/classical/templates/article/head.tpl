{**
 * templates/article/head.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article View -- Head component.
 *
 *}

<!-- Compiled sheet  -->
<link rel="stylesheet" href="{$baseUrl}/styles/compiled.css" type="text/css" />

<!-- Add theme style sheet -->
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/sangia-clas/css/message.css" type="text/css" />

<!-- Default global locale keys for JavaScript -->
{include file="common/jsLocaleKeys.tpl" }

<!-- Compiled scripts -->
{if $useMinifiedJavaScript}
	<script type="text/javascript" src="{$baseUrl}/js/pkp.min.js"></script>
	{include file="common/minifiedScripts.tpl"}
{/if}

<!-- Common style sheet -->
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/sangia-clas/css/print.css" media="print" type="text/css" />
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/sangia-clas/css/summary.css" type="text/css" />

{foreach from=$stylesheets name="testUrl" item=cssUrl}
	{if $cssUrl != "$baseUrl/styles/ojs.css"}
		<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
	{/if}
{/foreach}

{$additionalHeadData}
