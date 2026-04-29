{**
 * templates/about/index.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Journal / Site Map.
 *
 * TODO: Show the site map.
 *
 *}
{strip}
{assign var="pageTitle" value="submission.guidelines"}
{include file="common/header.tpl"}
{/strip}

<div class="c-page-layout__main">
	<h2>Our 3-step submission process</h2>
<ol class="c-steps" data-fragment="7489832">
<li class="c-steps__item">
	<h3 class="c-steps__title">Before you submit</h3>	
    <div class="c-steps__body">
        <div class="c-steps__intro">
        	<p>Now you’ve identified a journal to submit to, there are a few things you should be familiar with before you submit.</p>
        </div>
        <ul class="c-list-group c-list-group--md c-list-group--bordered c-steps__list">   
            {if $currentJournal->getLocalizedSetting('focusScopeDesc') != ''}
        	<li class="c-list-group__item">
        		Make sure you are submitting to the most suitable journal - <a href="{url page="about" op="editorialPolicies" anchor="focusAndScope"}"> Aims and scope </a>
    		</li>
            {/if}

            {if $currentJournal->getSetting('journalPaymentsEnabled') && ($currentJournal->getSetting('submissionFeeEnabled') || $currentJournal->getSetting('fastTrackFeeEnabled') || $currentJournal->getSetting('publicationFeeEnabled'))}
            <li class="c-list-group__item">
            	Understand the costs and funding options - <a href="{url page="about" anchor="authorFees"}"> Fees and funding </a>
            </li>
            {/if}

            <!-- <li class="c-list-group__item">
            	Make sure your manuscript is accurate and readable - <a href="{url page="pages" op="view/language-editing-services}"> Language editing services </a>
            </li> -->

            {if $currentJournal->getLocalizedSetting('copyrightNotice') != ''}
            <li class="c-list-group__item">
            	Understand the copyright agreement - <a href="{url page="about" anchor="copyrightNotice"}"> Copyright </a>
            </li>
            {/if}
        </ul>
    </div>
</li>
<li class="c-steps__item">
	<h3 class="c-steps__title">Ready to submit</h3>
    <div class="c-steps__body">
        <div class="c-steps__intro">
        	<p>To give your manuscript the best chance of publication,&nbsp;follow these policies and formatting guidelines.</p>
        </div>
        <ul class="c-list-group c-list-group--md c-list-group--bordered c-steps__list">
        	<li class="c-list-group__item">
        		General formatting rules for all article types - <a href="{url page="pages" op="view/preparing-your-manuscript}"> Preparing your manuscript </a>
        	</li>
        	<li class="c-list-group__item">
        		Make sure your submission is complete - <a href="{url page="pages" op="view/prepare-supporting-information}"> Prepare supporting information </a>
        	</li>
        	<li class="c-list-group__item">
        		Copyright and license agreement - <a href="{url page="pages" op="view/conditions-of-publication}"> Conditions of publication </a>
        	</li>
        	<li class="c-list-group__item">
        		Read and agree to our Editorial Policies - <a href="{url page="pages" op="view/editorial-policies}"> Editorial policies </a>
        	</li>
        </ul>
    </div>
</li>
<li class="c-steps__item">
	<h3 class="c-steps__title">Submit and promote</h3>
	<div class="c-steps__body">
		<div class="c-steps__intro">
			<p>After acceptance, we provide support so your article gains maximum impact in the scientific community and beyond.</p><p>Please note that manuscript can only be submitted by an author of the manuscript and may not be submitted by a third party.&nbsp;<br></p>
		</div>
		<ul class="c-list-group c-list-group--md c-list-group--bordered c-steps__list">
			<li class="c-list-group__item">
				Who decides whether my work will be accepted? - <a href="{url page="about" op="editorialPolicies" anchor="peerReviewProcess"}"> Peer-review policy </a>
			</li>
			<li class="c-list-group__item">
				Want to submit to a different journal? - <a href="{url page="pages" op="view/manuscript-transfers}"> Manuscript transfers </a>
			</li>
			<li class="c-list-group__item">
				Spreading the word - <a href="{url page="pages" op="view/promoting-your-publication}"> Promoting your publication </a>
			</li>
        </ul>
    </div>
</li>
</ol>

<p class="submit-manuscript">
	<a id="linkToSubmission" data-track="click" data-track-category="Submission guidelines" data-track-action="Click Submit a manuscript" data-track-label="Marine Biodiversity Records" href="{url page="author" op="submit"}">Submit your manuscript</a>
</p>
</div>

{include file="common/footer.tpl"}

