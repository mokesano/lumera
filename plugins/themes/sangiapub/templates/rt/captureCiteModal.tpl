{**
 * templates/rt/captureCiteModal.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article reading tools -- Capture Citation Modal
 *
 *}
<div id="captureCiteModal">
    <h3>{$article->getLocalizedTitle()|strip_unsafe_html}</h3>
    <div class="citation-format-selector">
        <label for="citeType">{translate key="rt.captureCite.format"}</label>
        <select id="citeType" onchange="loadCitationFormat(this.value, {$articleId}, {$galleyId})">
            {foreach from=$citationPlugins item=thisCitationPlugin}
                <option {if $citationPlugin && $citationPlugin->getName() == $thisCitationPlugin->getName()}selected="selected"{/if}
                    value="{$thisCitationPlugin->getName()|escape}">
                    {$thisCitationPlugin->getCitationFormatName()|escape}
                </option>
            {/foreach}
        </select>
    </div>
    <div id="citationContent">
        {call_hook name="Template::RT::CaptureCite"}
    </div>
</div>

{literal}
<script>
function loadCitationFormat(format, articleId, galleyId) {
    var url = '{url page="rt" op="captureCite" path=$articleId|to_array:$galleyId}/' + format + '?isModal=1';
    $.get(url, function(data) {
        $('#citationContent').html(data);
    });
}
</script>
{/literal}