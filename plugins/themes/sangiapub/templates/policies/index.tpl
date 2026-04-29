{**
 * templates/about/editorialPolicies.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Journal / Editorial Policies.
 * 
 *}
{strip}
{assign var="pageTitle" value="about.journalPolicies"}
{include file="common/header-gfa.tpl"}
{/strip}

<div class="policies-index">
    <h2 class="u-hide">{translate key="about.journalPolicies"}</h2>
    <ul class="policies-list">
        <li><a href="{url op="privacy-statement"}">{translate key="about.privacyStatement"}</a></li>
        <li><a href="{url op="peer-review"}">{translate key="about.peerReviewProcess"}</a></li>
        <li class="u-hide"><a href="{url op="ethics"}">{translate key="about.publicationEthics"}</a></li>
        <li><a href="{url op="open-access"}">{translate key="about.openAccessPolicy"}</a></li>
        <li><a href="{url op="archiving"}">{translate key="about.archiving"}</a></li>
        <li><a href="{url op="copyright"}">{translate key="about.copyrightNotice"}</a></li>
        <li><a href="{url op="publication-frequency"}">{translate key="about.publicationFrequency"}</a></li>
        <li><a href="{url op="section-policies"}">{translate key="about.sectionPolicies"}</a></li>

        {* Custom Items Loop *}
        {foreach from=$customPolicies item=policy}
            <li><a href="{url op=$policy.slug}">{$policy.title|escape}</a></li>
        {/foreach}
    </ul>
</div>

{include file="common/footer.tpl"}

