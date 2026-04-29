{**
 * templates/issue/view.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * View issue -- Menampilkan TOC atau halaman cover issue.
 * (Tanpa header/footer HTML, lihat viewPage.tpl)
 *
 * [WIZDAM v2] Logika URL Bertingkat:
 *   L1 (Normal)      : volume ada + number ada  → /volumes/{vol}/issue/{slug}
 *   L2 (Degradasi)   : volume ada + number NULL → /volumes/{vol}
 *   L3 (Deg. Penuh)  : volume NULL              → /year/{year} atau /volumes/
 *
 * PERHATIAN SMARTY 2.x:
 *   {if $someVar} mengevaluasi "0" sebagai FALSY.
 *   Gunakan |strlen > 0 untuk cek keberadaan nilai string.
 *   number="0" adalah VALID dan harus lolos ke L1 jika volume ada.
 *}

{if !$showToc && $issue}

	{* ================================================================
	   HITUNG currentUrl UNTUK LINK COVER → TOC (tombol "showToc")

	   Dijalankan saat issue punya cover page dan belum tampil TOC.
	   URL ini dipakai di: tombol "HERE", link gambar cover, judul TOC.
	   ================================================================ *}

	{* --- Step 1: Hitung issueVolume --- *}
	{assign var="issueVolume" value=$issue->getVolume()}

	{* --- Step 2: Hitung issueSlug dari number ---
	     number=NULL/""  → strlen=0 → issueSlug = "" → trigger L2
	     number="0"      → strlen=1 → slugify("0") = "0" → valid L1
	     number="1"      → strlen=1 → slugify("1") = "1" → valid L1
	     number="Supplement" → slugify = "supplement"    → valid L1
	*}
	{if $issueId}
		{assign var="issueNum" value=$issue->getNumber()}
		{if $issueNum|strlen == 0}
			{* number kosong/NULL: tidak ada identifier issue *}
			{assign var="issueSlug" value=""}
		{else}
			{assign var="issueSlug" value=$issueNum|slugify}
			{* Jika slugify menghasilkan string kosong (karakter aneh semua), fallback ke ID *}
			{if $issueSlug|strlen == 0}
				{assign var="issueSlug" value=$issue->getId()}
			{/if}
		{/if}

		{* --- Step 3: Pilih URL sesuai level degradasi ---
		     Gunakan strlen bukan truthy check:
		       issueVolume="0" → strlen=1 → valid (tidak didegradasi)
		       issueVolume=""  → strlen=0 → trigger L2/L3
		*}
		{if $issueSlug|strlen > 0 && $issueVolume|strlen > 0}
			{* L1 (Normal): Ada volume DAN ada slug → URL issue lengkap *}
			{native_url|assign:"currentUrl" page="issue" volume=$issueVolume slug=$issueSlug showToc=true}
		{elseif $issueVolume|strlen > 0}
			{* L2 (Degradasi): Ada volume, number NULL → URL volume *}
			{url|assign:"currentUrl" page="volumes" op="view" path=$issueVolume}
		{else}
			{* L3 (Degradasi Penuh): Volume NULL → coba tahun *}
			{assign var="issueYear" value=$issue->getYear()}
			{if $issueYear|strlen == 0}
				{assign var="issueYear" value=$issue->getDatePublished()|date_format:"%Y"}
			{/if}
			{if $issueYear|strlen > 0}
				{url|assign:"currentUrl" page="volumes" op="year" path=$issueYear}
			{else}
				{url|assign:"currentUrl" page="volumes" op="displayArchive"}
			{/if}
		{/if}
	{else}
		{* Tidak ada issueId → ke current issue *}
		{url|assign:"currentUrl" page="issue" op="current" path="showToc"}
	{/if}

	{* ================================================================
	   TAMPILKAN COVER PAGE
	   (atau gambar default jika tidak ada cover page yang diupload)
	   ================================================================ *}
	{if $coverPagePath}
	<div id="issueCoverImage" class="u-hide"><a href="{$currentUrl}"><img class="lazyload cover__image" {if $coverPageAltText != ''} title="Cover issue {$coverPageAltText|escape}"{else} title="Cover issue {translate key="issue.coverPage.altText"}"{/if} {if $coverPageAltText != ''} alt="{$coverPageAltText|escape}" {else} alt="{translate key="issue.coverPage.altText"}"{/if}{if $width} width="{$width|escape}"{/if}{if $height} height="{$height|escape}"{/if} {if $coverPagePath} src="{$coverPagePath|escape}{$issue->getFileName($locale)|escape}"{/if} width="100%" /></a>
	</div>
	{else}
	<div id="issueCoverImage" class="u-hide"><a href="{$currentUrl}"><img class="lazyload journal-cover__image cover-lazy img-default" title="Cover issue default" src="{$publicFilesDir}/{$homepageImage.uploadName|escape}/homepageImage_en_US.jpg" alt="Cover issue default" style="margin-top: 0" width="100%" />
	</div>
	{/if}

{elseif $issue}

	{* ================================================================
	   TAMPILKAN TOC (Table of Contents)
	   ================================================================ *}
	
    {if $issueGalleys}
    <section id="{translate key="issue.fullIssue"}" class="u-mb-48 u-mt-48" aria-labelledby="{translate key="issue.fullIssue"}" data-container-type="issue-section-list" data-track-component="issue section list">
        <div class="c-section-heading" data-test="title">
            <h2><span class="content-break">{translate key="issue.fullIssue"}</span><span class="text-gray-light altSize u-hide">({$issue->getNumArticles()|escape} {translate key="article.articlesCount"})</span></h2>
        </div>
    <ul class="app-article-list-row">
		{if (!$subscriptionRequired || $issue->getAccessStatus() == $smarty.const.ISSUE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $issueExpiryPartial))}
			{assign var=hasAccess value=1}
		{else}
			{assign var=hasAccess value=0}
		{/if}
		<div class="issue tocArticle">
			<h3 class="link tocTitle title-issue">{translate key="issue.viewIssueDescription"}</h3>
			<div class="tocArticleGalleysPages">
				<ul id="author-article-InfoList" class="tocMenuArticle ul-list">
				{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
					{foreach from=$issueGalleys item=issueGalley}
					{if $issueGalley->isPdfGalley()}
					<li class="tocMenuArticle pubDOI galley-issue">
						<a href="{url page="issue" op="download" path=$issue->getBestIssueId()|to_array:$issueGalley->getBestGalleyId($currentJournal)}" class="file" target="_blank">Download {$issueGalley->getGalleyLabel()|escape} <span class="fileSize">({$issueGalley->getNiceFileSize()})</span> <span class="fileView">{$issueGalley->getViews()} views</span></a>
					</li>
					<li class="tocMenuArticle pubDOI galley-issue">
						<a href="{url page="issue" op="viewFile" path=$issue->getBestIssueId()|to_array:$issueGalley->getBestGalleyId($currentJournal)}" class="file" target="_blank">View {$issueGalley->getGalleyLabel()|escape} file <span class="fileSize">({$issueGalley->getNiceFileSize()})</span> <span class="fileView">{$issueGalley->getViews()} views</span></a>
					</li>					
					{else}
					<li class="tocMenuArticle pubDOI galley-issue">
						<a href="{url page="issue" op="viewDownloadInterstitial" path=$issue->getBestIssueId()|to_array:$issueGalley->getBestGalleyId($currentJournal)}" class="file">Download {$issueGalley->getGalleyLabel()|escape} <span class="fileSize">({$issueGalley->getNiceFileSize()})</span> <span class="fileView">{$issueGalley->getViews()} views</span></a>
					</li>
					{/if}
					{/foreach}
				{/if}
				</ul>
			</div>
		</div>
	{/if}
	
	</section>
    
    {include file="issue/issue.tpl"}
    
{else}

	{* ================================================================
	   TIDAK ADA ISSUE (belum ada issue yang dipublikasikan)
	   ================================================================ *}
	<div class="container cleared container-type-title">
	    <div class="border-top-1 border-gray-medium"></div>
	    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
	        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
	        </div>
	        <div class="c-empty-state-card__text">
	            <h3 class="c-empty-state-card__text--title headline-5">{translate key="current.noCurrentIssueDesc"}</h3>
	            <div class="c-empty-state-card__text--description">We are currently in the early stages of our publication process. Please visit our <a href="{url page="about" op="editorialPolicies" anchor="focusAndScope"}">About</a> page to learn more about our scope and objectives, or consider <a href="{url page="author" op="submit"}">submitting your manuscript</a> to contribute to our upcoming inaugural issue.</div>
	        </div>
	    </div>
	</div>

{/if}
            
</main>
