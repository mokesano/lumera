{**
 * head.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site header.
 *}
{assign var="owner" value="Sangia"}
{assign var="brandOwner" value="PT. Sangia Research Media and Publishing"}
{assign var="siteOwner" value="www.sangia.org"}
{assign var="publishingBrand" value="Sangia Publishing"}
{assign var="themeBrand" value="Sangia Wizdam Indonesia"}
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta property="og:title" content="{$pageTitleTranslated}{if $currentJournal} - {$currentJournal->getLocalizedTitle()|strip_tags|escape} | {$owner}{else} | {$siteTitle}{/if}" />
{if $currentJournal}
<meta name="citation_publisher" content="{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}{$currentJournal->getSetting('publisherInstitution')}{else}{$currentJournal->getSetting('publisherInstitution')}{/if}" />
{/if}
<meta name="prism.rightsAgent" content="journals@sangia.org" />
<meta name="copyright_theme" content="{$themeBrand}" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />   
<meta name="access" content="Yes" />
<meta name="robots" content="INDEX,FOLLOW,NOARCHIVE,NOCACHE,NOODP,NOYDIR" />
<meta name="revisit-after" content="3 days" />
<meta name="applicable-device" content="pc,mobile" />
<link rel="canonical" href="{$currentUrl|escape}" />
<meta name="360-site-verification" content="d192909d8ce61cb208a46fd481a78272" />
<meta name="csrf-token" content="{$csrfToken}">
<meta name="google-site-verification" content="9mVvrkXamiUxutovEQqEk2eiRcjLUWHLHcwssZo3GYs" />
<meta property="og:url" content="{$currentUrl|escape}" />
<meta property="og:type" content="website" />
<meta property="og:site_name" content="{$publishingBrand}" />
{if $currentJournal}
    {call_hook name="Templates::Article::Article::ArticleCoverImage"}
    {assign var="displayHomepageImage" value=$currentJournal->getLocalizedSetting('homepageImage')}
    {if $issue && $issue->getLocalizedFileName() && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageArchives($locale)}
    <meta property="og:image" content="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}" />
    {elseif $displayHomepageImage && is_array($displayHomepageImage)}
    <meta property="og:image" content="{$publicFilesDir}/{$displayHomepageImage.uploadName|escape:"url"}" />
    {else}
    <meta property="og:image" content="{$baseUrl}/assets/static/images/not-available.webp" />
    {/if}
{/if}
<meta name="robots" content="max-image-preview:large">
<meta name="referrer" content="origin-when-cross-origin" />
{if $currentJournal}
<meta name="twitter:site" content="@{$currentJournal->getSetting('initials', $currentJournal->getPrimaryLocale())}" />
{/if}
<meta name="twitter:card" content='summary_large_image' />
<meta name="twitter:image:alt" content="{$pageTitleTranslated|escape}{if $currentJournal} - {$currentJournal->getLocalizedTitle()|strip_tags|escape} | {$owner}{/if}" />
{if $metaSearchDescription}
    <meta property="og:description" content="{$metaSearchDescription|escape}" />
{elseif $journalDescription}
    <meta property="og:description" content="{$journalDescription|strip_tags|escape}" />
{/if}
{if $currentJournal}
<meta name="publisher" content="{if $currentJournal->getSetting('publisherInstitution') == "Sekolah Tinggi Ilmu Pertanian Wuna"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Research Media and Publishing"}Sangia Publishing{elseif $currentJournal->getSetting('publisherInstitution') == "Sangia Publishing"}{$currentJournal->getSetting('publisherInstitution')}{else}{$currentJournal->getSetting('publisherInstitution')}{/if}" />
{/if}
<meta name="website_owner" content="{$siteOwner}" />
<meta name="owner" content="{$brandOwner}" />

<link rel="apple-touch-icon" type="image/png" sizes="180x180" href="{$baseUrl}/assets/static/favicon/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="48x48" href="{$baseUrl}/assets/static/favicon/android-icon-48x48.png">
<link rel="icon" type="image/png" sizes="32x32" href="{$baseUrl}/assets/static/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="{$baseUrl}/assets/static/favicon/favicon-16x16.png">
<link rel="manifest" href="{$baseUrl}/assets/static/favicon/manifest.json">
{** <link rel="icon" type="image/icon" href="{$baseUrl}/favicon.ico" /> **}
