{**
 * templates/rt/header.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common header for RT pages.
 *}
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
<head>
	<!-- Viewport -->
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	
	<!-- Meta Developer -->
	<meta name="designer" content="@Rochmady & SRM Team">
	<meta name="publisher" content="Stipwunaraha Publishing">
	<meta name="copyright" content="Stipwunaraha R&D (SRM Group)">
	
	<!-- {$pageTitleTranslated} -->
	<title>{translate key="rt.readingTools"} - Stipwunaraha</title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />

	{if $displayFavicon}<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />{/if}

	<!-- Others link -->
	<link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/common.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/styles/common.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/styles/compiled.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/rt.css" type="text/css" />

	{foreach from=$stylesheets item=cssUrl}
		<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
	{/foreach}

	<!-- Base Jquery -->
	{if $allowCDN}<script type="text/javascript" src="//www.google.com/jsapi"></script>
	<script type="text/javascript">{literal}
		// Provide a local fallback if the CDN cannot be reached
		if (typeof google == 'undefined') {
			document.write(unescape("%3Cscript src='{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/jquery/jquery.min.js' type='text/javascript'%3E%3C/script%3E"));
			document.write(unescape("%3Cscript src='{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js' type='text/javascript'%3E%3C/script%3E"));
		} else {
			google.load("jquery", "{/literal}{$smarty.const.CDN_JQUERY_VERSION}{literal}");
			google.load("jqueryui", "{/literal}{$smarty.const.CDN_JQUERY_UI_VERSION}{literal}");
		}
	{/literal}</script>
	{else}
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js"></script>
	{/if}

	<!-- Compiled scripts -->
	{if $useMinifiedJavaScript}
		<script type="text/javascript" src="{$baseUrl}/js/pkp.min.js"></script>
	{else}
		{include file="common/minifiedScripts.tpl"}
	{/if}

		{include file="common/head.tpl"}

	{$additionalHeadData}
</head>

<body id="COSIRE">
{literal}
<script type="text/javascript">
<!--
	if (self.blur) { self.focus(); }
// -->
</script>
{/literal}

{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}

<div id="wrapper">

<div id="header" role="banner">
	<div class="cross-nav cross-nav--wide">
    	{if $isUserLoggedIn}
    		{if $hasOtherJournals}
    		<div id="userJournal" class="auth flyout"><a class="register-link flyout-caption" href="{url journal="$currentJournal" page="user"}">My Journals</a></div>
    		{/if}
    		<div id="userProfile" class="auth flyout"><a class="register-link flyout-caption" href="{url page="user" op="profile"}">My Profile</a></div>
    		<div id="userHome" class="auth flyout"><a class="register-link flyout-caption" href="{url page="user"}">User Home</a></div>
    		<div id="userLogOut" class="auth flyout"><a class="register-link flyout-caption" href="{url page="login" op="signOut"}">Log Out</a></div>
    	{else}
    	<div id="userlogin" class="auth flyout"><a class="register-link flyout-caption" href="{url page="login"}">{translate key="navigation.login"}</a></div>		
		{if !$hideRegisterLink}
    	<div id="userRegister" class="auth flyout"><a class="register-link flyout-caption" href="{url page="user" op="register"}">{translate key="navigation.register"}</a></div>
			{/if}
    	{/if}{* $isUserLoggedIn *}

<div class="lang flyout">
  <button class="flyout-caption cur" aria-expanded="false">
    <span>English</span>
    <img class="flyout-caption-icon" src="//www.stipwunaraha.ac.id/static/images/classical/flyout-arrow.png" alt="">
  </button>
  <div class="flyout-content">
  	<ul>
  		<li><a class="change-language-Deutsch" href="/language/Deutsch" rel="nofollow">Deutsch</a></li>
  	</ul>
  </div>
</div>

  </div>
  <a id="logo" href="/">
  <img src="{$baseUrl}/plugins/themes/eserem-classical/img/classical/logo_high_res.png" alt="Logo Stipwunaraha" title="Stipwunaraha">
</a>

  <div class="panel-search">
    <form id="global-search" class="big-search lm-site-search" action="{url page="search" op="search"}" method="get" role="search">
	{capture assign="queryFilter"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName="query" filterValue=$query}{/capture}
	{if empty($queryFilter)}
      <div class="search-field">
        <input id="query" class="text ui-autocomplete-input" name="query" type="text" autocomplete="off" value="{$query|escape}" placeholder="Search" title="Search">
      <ul id="ui-id-1" tabindex="0" class="ui-menu ui-widget ui-widget-content ui-autocomplete ui-front" style="display: none;"></ul></div>
      	{else}
		{$queryFilter}
	{/if}
      <input id="search" class="search-submit" type="submit" value="Submit"><img class="loading js-hidden" src="//www.stipwunaraha.ac.id/static/images/classical/loading_search.gif" alt="">
      <img class="search-submit-image" src="//www.stipwunaraha.ac.id/static/images/classical/search-submit.png" alt="Search">
      <div id="search-options" class="flyout">
        <button class="pillow-btn open-search-options" type="button" aria-expanded="false">
          <img src="//www.stipwunaraha.ac.id/static/images/classical/settings-active.png" alt="Search Options">
        </button>
        <div class="flyout-content">
          <ul>
            <li>
              <a id="advanced-search-link" href="/advanced-search">Advanced Search</a>
            </li>
            <li>
              <a id="search-help-link" href="/searchhelp">Search Help</a>
            </li>
          </ul>
        </div>
      </div>
    </form>
  </div>
  <button class="pillow-btn open-search" aria-expanded="false">Search</button>
  <button class="pillow-btn open-menu" aria-expanded="false">Menu</button>
  <div class="panel-menu">
  	<div class="cross-nav cross-nav--narrow">
    	{if $isUserLoggedIn}
    		{if $hasOtherJournals}
    		<div id="userJournal" class="auth flyout"><a class="register-link flyout-caption" href="{url journal="$currentJournal" page="user"}">My Journals</a></div>
    		{/if}
    		<div id="userProfile" class="auth flyout"><a class="register-link flyout-caption" href="{url page="user" op="profile"}">My Profile</a></div>
    		<div id="userHome" class="auth flyout"><a class="register-link flyout-caption" href="{url page="user"}">User Home</a></div>
    		<div id="userLogOut" class="auth flyout"><a class="register-link flyout-caption" href="{url page="login" op="signOut"}">Log Out</a></div>
    	{else}
    	<div id="userlogin" class="auth flyout"><a class="register-link flyout-caption" href="{url page="login"}">{translate key="navigation.login"}</a></div>		
		{if !$hideRegisterLink}
    	<div id="userRegister" class="auth flyout"><a class="register-link flyout-caption" href="{url page="user" op="register"}">{translate key="navigation.register"}</a></div>
			{/if}
    	{/if}{* $isUserLoggedIn *}

<div class="lang flyout">
  <button class="flyout-caption cur" aria-expanded="false">
    <span>English</span>
    <img class="flyout-caption-icon" src="/static/64ea173f12e65c09652ad2a9e33a74208684c1c5/images/sprites/springer-sprite/flyout-arrow.png" alt="">
  </button>
  <div class="flyout-content">
  	<ul>
    	<li><a class="change-language-Deutsch" href="/language/Deutsch" rel="nofollow">Deutsch</a></li>
    </ul>
  </div>
</div>

</div>
    <ul id="global-nav" class="clearfix" role="navigation" aria-label="Global">
      <li id="home"><a href="{url page="$currentJournal"}" >{translate key="navigation.home"}</a></li>
      <li id="about"><a href="{url page="about"}" >{translate key="navigation.about"}</a></li>

		{if $currentJournal && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
			<li id="archives"><a href="{url page="issue" op="archive"}" >{translate key="navigation.categories.browse"}</a></li>
      <li id="articles"><a href="{url page="search" op="titles"}" >Articles</a></li>
		{/if}

    <li id="articles"><a href="{url page="search" op="categories"}" >Journal A-Z</a></li>

		{if $enableAnnouncements}
			<li id="announcements"><a href="{url page="announcement"}" >{translate key="announcement.announcements"}</a></li>
		{/if}{* enableAnnouncements *}

    </ul>
  </div>
</div>

    <div class="c-journal-header__identity c-journal-header__identity--default"></div>

<!--
<div id="header">
	<div id="headerTitle">
	</div>
</div>
-->

<div id="content">
<div id="top"></div>

<div id="body">

{literal}
<script type="text/javascript">
<!--
	if (self.blur) { self.focus(); }
// -->
</script>
{/literal}

<div id="main">

<h1>{if $currentJournal && $currentJournal->getLocalizedInitials()}{$currentJournal->getLocalizedInitials()}&nbsp;{/if}{translate key="rt.readingTools"}</h1>

<h2>{$pageTitleTranslated}</h2>


