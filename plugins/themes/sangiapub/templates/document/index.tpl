{**
 * templates/document/index.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *}
{strip}
    {assign var="pageTitle" value="document.index.pageTitle"}
    {include file="common/header.tpl"}
{/strip}

{literal}
<style>
    .wi-doc-landing { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .wi-doc-card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 30px; text-align: center; text-decoration: none !important; color: #333; transition: 0.2s; }
    .wi-doc-card:hover { border-color: #1a4f8b; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .wi-doc-card .count { font-size: 32px; font-weight: 800; color: #1a4f8b; }
    .wi-doc-card h3 { margin: 10px 0 5px; }
</style>
{/literal}

<div class="wi-doc-landing">
    <a href="{$loaIndexUrl|escape}" class="wi-doc-card">
        <div class="count">{$loaCount}</div>
        <h3>{translate key="billing.loa.indexTitle"}</h3>
        <p>{translate key="document.index.loaDescription"}</p>
    </a>
    <a href="{$certificateIndexUrl|escape}" class="wi-doc-card">
        <div class="count">{$certificateCount}</div>
        <h3>{translate key="document.cert.indexTitle"}</h3>
        <p>{translate key="document.index.certificateDescription"}</p>
    </a>
</div>

{include file="common/footer.tpl"}