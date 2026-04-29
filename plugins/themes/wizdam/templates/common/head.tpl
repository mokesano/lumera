{**
 * head.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site header.
 *}
{translate|assign:"applicationName" key="common.openJournalSystems"}
<meta property="og:title" content="{$pageTitleTranslated}" />
<meta name="citation_publisher" content="{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Sekolah Tinggi Ilmu Pertanian Wuna{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing, LLC"}Sangia Research Media and Publishing Group{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}{$currentJournal->getSetting('publisherInstitution')}{else}{$currentJournal->getSetting('publisherInstitution')}{/if}" />
<meta name="prism.rightsAgent" content="journals@sangia.org" />
<meta name="copyright_theme" content="Sangia Research Media" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />   
<meta name="access" content="Yes" />
<meta name="robots" content="INDEX,FOLLOW,NOARCHIVE,NOODP,NOYDIR" />
<meta name="revisit-after" content="3 days" />
<meta name="applicable-device" content="pc,mobile" />
<meta name="application-name" content="{$applicationName}" />
<link rel="canonical" href="https://www.sangia.org" />
<meta property="og:url" content="{url base}" />
<meta property="og:type" content="website" />
<meta property="og:site_name" content="Sangia Research Media & Publishing" />
<meta name="referrer" content="origin-when-cross-origin" />

<meta name="twitter:site" content="@{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}" />
<meta name="twitter:card" content='summary_large_image' />
<meta name="twitter:image:alt" content="{$pageTitleTranslated} - Sangia" />

{call_hook name="Templates::Article::Article::ArticleCoverImage"}
{assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
{if $issue}
    {if $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
    <meta property="og:image" content="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}" />
    {/if}
{elseif $displayHomepageImage && is_array($displayHomepageImage)}
<meta property="og:image" content="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" />
{else}
<meta property="og:image" content="//media.stipwunaraha.ac.id/img/img-default.jpg" />
{/if}

<meta name="publisher" content="Sangia Research Media" />
<meta name="owner" content="PT. Sangia Research Media and Publishing" />
<meta name="website_owner" content="www.sangia.org" />

<link rel="apple-touch-icon" sizes="57x57" href="//assets.sangia.org/static/favicon/apple-icon-57x57.png" />
<link rel="apple-touch-icon" sizes="60x60" href="//assets.sangia.org/static/favicon/apple-icon-60x60.png" />
<link rel="apple-touch-icon" sizes="72x72" href="//assets.sangia.org/static/favicon/apple-icon-72x72.png" />
<link rel="apple-touch-icon" sizes="76x76" href="//assets.sangia.org/static/favicon/apple-icon-76x76.png" />
<link rel="apple-touch-icon" sizes="114x114" href="//assets.sangia.org/static/favicon/apple-icon-114x114.png" />
<link rel="apple-touch-icon" sizes="120x120" href="//assets.sangia.org/static/favicon/apple-icon-120x120.png" />
<link rel="apple-touch-icon" sizes="144x144" href="//assets.sangia.org/static/favicon/apple-icon-144x144.png" />
<link rel="apple-touch-icon" sizes="152x152" href="//assets.sangia.org/static/favicon/apple-icon-152x152.png" />
<link rel="apple-touch-icon" sizes="180x180" href="//assets.sangia.org/static/favicon/apple-icon-180x180.png" />
<link rel="icon" type="image/png" sizes="192x192"  href="//assets.sangia.org/static/favicon/android-icon-192x192.png" />
<link rel="icon" type="image/png" sizes="32x32" href="//assets.sangia.org/static/favicon/favicon-32x32.png" />
<link rel="icon" type="image/png" sizes="96x96" href="//assets.sangia.org/static/favicon/favicon-96x96.png" />
<link rel="icon" type="image/png" sizes="16x16" href="//assets.sangia.org/static/favicon/favicon-16x16.png" />
<link rel="manifest" href="//assets.sangia.org/static/favicon/manifest.json" />

<meta name="publisher" content="Sangia Research Media" />
<meta name="website_owner" content="www.sangia.org" />
<meta name="owner" content="PT. Sangia Research Media and Publishing" />

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/wizdam/css/typography.css" type="text/css" />
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/wizdam/css/font.css" type="text/css" />
    
<!-- Add theme style sheet -->
<link rel="preload" href="//assets.sangia.org/css/common/journal-mosaic-v1-branded.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="//assets.sangia.org/css/common/journal-mosaic-v1-branded.css"></noscript>
<link rel="stylesheet" href="//assets.sangia.org/css/common/journal-mosaic-v1-branded.css" type="text/css" />
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/wizdam/css/sangia.mosaic.css" type="text/css" />

<!-- Common style sheet -->
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/wizdam/css/print.css" media="print" type="text/css" />
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/wizdam/css/externalFeed.css" type="text/css" />
    
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-110581662-2"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
    
  gtag('config', 'UA-110581662-2');
</script>
    
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8416265824412721"
     crossorigin="anonymous"></script>

<!-- Common javascript -->
<script type="text/javascript" src="{$baseUrl}/plugins/themes/wizdam/js/sangia.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
