{**
 * templates/author/submit/submitHeader.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for the article submission pages with simple progress indicator.
 *
 *}
{strip}
{assign var="pageCrumbTitle" value="author.submit"}
{include file="common/header-ROLE.tpl"}
{/strip}

{* Pass variables to external JS file *}
<script type="text/javascript">
{literal}
<!--
window.submitStep = {/literal}{$submitStep|default:1}{literal};
window.submissionProgress = {/literal}{$submissionProgress|default:0}{literal};
window.articleId = '{/literal}{$articleId|escape:"javascript"}{literal}';
// -->
{/literal}
</script>

<!-- Simple Progress Bar - Fixed Right Side -->
<div class="simple-progress-container">
    <div class="progress-line">
        <div class="progress-fill" id="progressFill" style="height: {if $submissionProgress > 0}{math equation="(x / 5) * 100" x=$submissionProgress}{else}0{/if}%"></div>
        <div class="step-indicators">
            <div class="step-indicator pending" 
                 data-step="1" 
                 title="{translate key="author.submit.start"}">1</div>
            <div class="step-indicator pending" 
                 data-step="2" 
                 title="{translate key="author.submit.upload"}">2</div>
            <div class="step-indicator pending" 
                 data-step="3" 
                 title="{translate key="author.submit.metadata"}">3</div>
            <div class="step-indicator pending" 
                 data-step="4" 
                 title="{translate key="author.submit.supplementaryFiles"}">4</div>
            <div class="step-indicator pending" 
                 data-step="5" 
                 title="{translate key="author.submit.confirmation"}">5</div>
            <div class="complete-indicator pending" 
                 title="{translate key="common.complete"}">
                <span class="complete-icon">✓</span>
                <span class="complete-text">Complete</span>
            </div>
        </div>
    </div>
    <div class="progress-info">
        <span class="progress-percentage" id="progressPercentage">{if $submissionProgress > 0}{math equation="(x / 5) * 100" x=$submissionProgress format="%.0f"}{else}0{/if}%</span>
        <small>{translate key="common.complete"}</small>
    </div>
</div>

<!-- Keep Original Step Navigation -->
<div class="pseudoMenu u-mb-24">
    <ul class="steplist menu">
        <li id="step1" 
            {if $submitStep == 1}
                class="current"
            {elseif $submissionProgress >= 1}
                class="success"
            {else}
                class="disable"
            {/if}>
            {if $submitStep != 1 && $submissionProgress >= 1}
                <a href="{url op="submit" path="1" articleId=$articleId}">
            {/if}
            {translate key="author.submit.start"}
            {if $submitStep != 1 && $submissionProgress >= 1}
                </a>
            {/if}
        </li>
        
        <li id="step2" 
            {if $submitStep == 2}
                class="current"
            {elseif $submissionProgress >= 2}
                class="success"
            {else}
                class="disable"
            {/if}>
            {if $submitStep != 2 && $submissionProgress >= 2}
                <a href="{url op="submit" path="2" articleId=$articleId}">
            {/if}
            {translate key="author.submit.upload"}
            {if $submitStep != 2 && $submissionProgress >= 2}
                </a>
            {/if}
        </li>
        
        <li id="step3" 
            {if $submitStep == 3}
                class="current"
            {elseif $submissionProgress >= 3}
                class="success"
            {else}
                class="disable"
            {/if}>
            {if $submitStep != 3 && $submissionProgress >= 3}
                <a href="{url op="submit" path="3" articleId=$articleId}">
            {/if}
            {translate key="author.submit.metadata"}
            {if $submitStep != 3 && $submissionProgress >= 3}
                </a>
            {/if}
        </li>
        
        <li id="step4" 
            {if $submitStep == 4}
                class="current"
            {elseif $submissionProgress >= 4}
                class="success"
            {else}
                class="disable"
            {/if}>
            {if $submitStep != 4 && $submissionProgress >= 4}
                <a href="{url op="submit" path="4" articleId=$articleId}">
            {/if}
            {translate key="author.submit.supplementaryFiles"}
            {if $submitStep != 4 && $submissionProgress >= 4}
                </a>
            {/if}
        </li>
        
        <li id="step5" 
            {if $submitStep == 5}
                class="current"
            {elseif $submissionProgress >= 5}
                class="success"
            {else}
                class="disable"
            {/if}>
            {if $submitStep != 5 && $submissionProgress >= 5}
                <a href="{url op="submit" path="5" articleId=$articleId}">
            {/if}
            {translate key="author.submit.confirmation"}
            {if $submitStep != 5 && $submissionProgress >= 5}
                </a>
            {/if}
        </li>
    </ul>
</div>

<!-- Auto-save Indicator -->
<div id="autoSaveIndicator" class="auto-save-indicator">
    ✓ {translate key="common.changesSaved"}
</div>