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
{assign var="pageTitleTranslated" value=$pageTitleTranslated|default:$pageTitle}
{include file="common/header-gfa.tpl"}

<div id="wizdamPolicyPage" class="policy-content-wrapper">

    <div class="content-body u-mt-48">
        <p>{$content}</p>
    </div>
    
</div>

{/strip}

{include file="common/footer.tpl"} 