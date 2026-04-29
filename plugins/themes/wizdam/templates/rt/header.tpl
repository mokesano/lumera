{**
 * templates/rt/header.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common header for RT pages.
 *}
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
<head>
	<title>{translate key="rt.readingTools"}</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />

	{if $displayFavicon}<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" type="{$displayFavicon.mimeType|escape}" />{/if}

	<!-- <link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/common.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/styles/common.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/styles/compiled.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/rt.css" type="text/css" /> -->

	{foreach from=$stylesheets item=cssUrl}
		<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
	{/foreach}

	{include file="common/head.tpl"}
	<!-- Base Jquery -->
	{if $allowCDN}<script type="text/javascript" src="http://www.google.com/jsapi"></script>
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

	{$additionalHeadData}

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-110581662-2"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    
      gtag('config', 'UA-110581662-2');
    </script>
	
    <!-- OneTrust Cookies Consent Notice start for ejournal.stipwunaraha.ac.id -->
    <script src="https://cdn.cookielaw.org/scripttemplates/otSDKStub.js" data-document-language="true" type="text/javascript" charset="UTF-8" data-domain-script="ef8d6a4d-3871-4684-91c9-80259f6aacfe" ></script>
    <script type="text/javascript">
        function OptanonWrapper() {
            window.dataLayer.push({event:'OneTrustGroupsUpdated'});
            document.activeElement.blur();
        }
    </script> 
    <script>
        (function(w,d,t,dn) {
            function cc() {
                var h = w.location.hostname;
                if (h.indexOf('preview-www.stipwunaraha.ac.id') > -1) return;
                var e = d.createElement(t),
                    s = d.getElementsByTagName(t)[0],
                    p = h.indexOf(dn) > -1;
                e.src = p ? 'https://cdn.cookielaw.org/scripttemplates/otSDKStub.js' : '/static/js/cookie-consent-bundle.5d8614bae2.js';
                p ? e.setAttribute('data-domain-script', 'ef8d6a4d-3871-4684-91c9-80259f6aacfe') : e.setAttribute('data-consent', w);
                s.parentNode.insertBefore(e, s);
            }
    
            !!w.google_tag_manager ? cc() : window.addEventListener('gtm_loaded', function() {cc()});
        })(window,document,'script','ejournal.stipwunaraha.ac.id');
    </script>    
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;
    
        
        j.addEventListener('load', function() {
        var _ge = new CustomEvent('gtm_loaded', { bubbles: true });
        d.dispatchEvent(_ge);
        });
    
        f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-NWDMT9Q');</script>    
    <script src="https://cdn.cookielaw.org/scripttemplates/6.7.0/otBannerSdk.js" async="" type="text/javascript"></script>        
    <!-- OneTrust Cookies Consent Notice end for ejournal.stipwunaraha.ac.id -->
    
</head>
<body id="{$pageTitle|replace:'.':'-'}" class="popup">
{literal}
<script type="text/javascript">
<!--
	if (self.blur) { self.focus(); }
// -->
</script>
{/literal}

{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}

<div id="container">
<nav>
<div id="header">
	<div class="row">
		<div class="column">
		<div id="headerTitle" >
			<h1>{if $currentJournal && $currentJournal->getLocalizedInitials()}{$currentJournal->getLocalizedInitials()}&nbsp;{/if}{translate key="rt.readingTools"}</h1>
		</div>
		</div>
	</div>
</div>
</nav>
    <div class="c-journal-header__identity c-journal-header__identity--default"></div>

<div id="body" class="row">
<div id="top"></div>

<div id="main">

{literal}
<script type="text/javascript">
<!--
	if (self.blur) { self.focus(); }
// -->
</script>
{/literal}

<h2>{$pageTitleTranslated}</h2>

<div id="content">

