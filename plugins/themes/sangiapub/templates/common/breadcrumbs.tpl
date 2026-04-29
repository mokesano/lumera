{**
 * breadcrumbs.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Breadcrumbs
 *
 *}
<div id="breadcrumb" class="u-show-at-md u-hide-sm-max">
<div class="row">
    <div class="columns">
    
    {* 1. Link Home *}
    {if $currentJournal}
    <a href="//www.sangia.org">sangia.org</a> 
    <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
    {else}
    <a href="{$baseUrl}">{translate key="navigation.home"}</a> 
    <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
    {/if}
    
    {* 2. Link Jurnal *}
    {if $currentJournal}
        {if $currentJournal->getLocalizedInitials()}
        <a href="{url page="$currentJournal"}">{$currentJournal->getLocalizedInitials()|strip_tags|escape}</a> 
        <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
        {else}
        <a href="{url page="$currentJournal"}">Journal Initials</a> 
        <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
        {/if}
    {/if}

    {* 3. LOOP HYBRID (Aman untuk Kode Lama & Kode Baru) *}
    {foreach from=$pageHierarchy item=hierarchyLink}
        
        {* LOGIKA DETEKSI FORMAT *}
        {if isset($hierarchyLink.url)}
            {* >>> KASUS A: Format Modern/Wizdam (Associative Array) <<< *}
            {* Dipakai oleh PoliciesHandler baru Anda *}
            <a href="{$hierarchyLink.url|escape}" class="hierarchyLink">
                {$hierarchyLink.name|escape}
            </a>
        {else}
            {* >>> KASUS B: Format Klasik/Legacy (Indexed Array) <<< *}
            {* Dipakai oleh ArticleHandler, IssueHandler, dll. *}
            <a href="{$hierarchyLink[0]|escape}" class="hierarchyLink">
                {if not $hierarchyLink[2]}
                    {translate key=$hierarchyLink[1]} {* Perlu Translate *}
                {else}
                    {$hierarchyLink[1]|escape} {* Sudah Teks *}
                {/if}
            </a>
        {/if}

        {* Ikon Chevron (Sama untuk keduanya) *}
        <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
    {/foreach}

    {* 4. Halaman Saat Ini *}
    {if $requiresFormRequest}
        <span class="current">
    {else}
        <span href="{$currentUrl|escape}" class="current">
    {/if}
        {$pageCrumbTitleTranslated}
    </span>

    </div>
</div>
</div>
