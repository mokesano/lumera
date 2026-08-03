{**
 * templates/document/certificate/certificateIndex.tpl
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *}
{strip}
    {assign var="pageTitle" value="document.cert.indexTitle"}
    {include file="common/header.tpl"}
{/strip}

<table class="wizdam-table" width="100%" style="border-collapse: collapse;">
    <thead>
        <tr style="border-bottom: 2px solid #ccc;">
            <th style="padding: 10px; text-align: left;">{translate key="billing.statusLabel"}</th>
            <th style="padding: 10px; text-align: left;">{translate key="billing.titleLabel"}</th>
            <th style="padding: 10px; text-align: left;" width="15%">{translate key="document.index.statusDate"}</th>
            <th style="padding: 10px; text-align: left;" width="20%">{translate key="document.index.actions"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$certificateEntries item=entry}
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px;">{$entry.statusLabel|escape}</td>
                <td style="padding: 10px;">{$entry.articleTitle|strip_unsafe_html}</td>
                <td style="padding: 10px;">{$entry.dateCompleted|date_format:"%d %b %Y"}</td>
                <td style="padding: 10px;">
                    <a href="{$entry.detailUrl|escape}">{translate key="document.index.viewDetail"}</a>
                    &bull;
                    <a href="{$entry.pdfUrl|escape}" target="_blank">{translate key="document.index.downloadPdf"}</a>
                </td>
            </tr>
        {foreachelse}
            <tr><td colspan="4" style="padding: 20px; text-align: center; color: #666;">{translate key="document.index.certificateEmpty"}</td></tr>
        {/foreach}
    </tbody>
</table>

{include file="common/footer.tpl"}