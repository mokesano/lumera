{**
 * templates/common/journal-insight.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Journal Insights
 *
 *}

<div id="__next" class="journal-metrics">
    <!-- editing -->
    {assign var=currentYear value="now"|date_format:"%Y"}
	{assign var=threeYearsAgo value="-3 years"|strtotime|date_format:"%Y"}
	{assign var="lastYear" value=$currentYear-1}
    <div class="medium-12 main-insights-contents">
        <h2 class="headline-1283242569 jour-insight u-font-sans">
            <span title="Journal Insights">Publication timeline</span>
            <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle">
                <button aria-label="More information about Publication timeline" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg>
                </button>
                <span role="status"></span>
                <div id="popover-content-metric-popover-PublicationTimeline" class="popover-content popover-align-left heading_inline u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">Publication timeline presents the median publication time, calculated using all available data from the journal’s inaugural issue up to the year {$lastYear} preceding the current one. The data are processed without annual grouping to ensure greater accuracy, enabling a comprehensive median calculation based on the entire dataset rather than segmented yearly values. <span class="anchor u-display-block"><a href="{url page="about" op="statistics"}"><span class="anchor-text">For more click HERE</span></a><svg focusable="false" viewBox="0 0 8 8" height="10" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg></span></div></div>
                </div>
            </div>
        </h2>
    </div>
    
    <div class="max-width__MaxWidth-sc-1dxr8k6-0 metrics__Metrics-sc-ewcnzs-0 ilPtKO jFWzXz u-font-sangia-sans">
        
        {if $submissionToFirstDecision > 0}
        <div class="metric__Wrapper-sc-1q1u28e-0 kzQOoQ">
            <div class="label__Wrapper-sc-11qqina-1 koaLwK">
                <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle">
                    <button aria-label="More information about Time to first decision" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg>
                    </button>
                    <span role="status"></span>
                    <div id="popover-content-metric-popover-TimetoFirstDecision" class="popover-content popover-align-left u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">The median number of days it takes for an article to go from submission to first editorial decision (e.g., desk reject, or invite the first reviewer).</div></div></div>
                </div>
                <a href="{url page="about" op="statistics"}" rel="noreferrer noopener" target="_self" data-aa-name="Time to first decision" data-aa-region="metrics" class="label__DefaultLabel-sc-11qqina-0-a BKOrl metrics-time-to-first-decision anchor">
                    <span class="label__DefaultLabel-sc-11qqina-0 humCsR anchor-text">Time to first decision</span>
                    <svg width="1em" height="1em" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg" class="label__ExternalLinkIcon-sc-11qqina-2 label___StyledExternalLinkIcon-sc-11qqina-3 bmMkto hwvhTt"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg>
                </a>
            </div>
            <p class="shared__MetricContent-sc-nfthpa-0 dQJdMx"><span class="shared__MetricValue-sc-nfthpa-1 value__MetricValue-sc-16w49ij-0 jpHFUu hTFtMC">{$submissionToFirstDecision}<span class="days u-ml-4">days</span></span>
                <div class="trend__ChartWrapper-sc-12iojgp-1 jOlahf">Data through {$lastYear} (median)</div>
            </p>
        </div>
		{/if}
		
        {if $daysPerReview > 0}
        <div class="metric__Wrapper-sc-1q1u28e-0 kzQOoQ">
            <div class="label__Wrapper-sc-11qqina-1 koaLwK">
                <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle">
                    <button aria-label="More information about Review time" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg>
                    </button>
                    <span role="status"></span>
                    <div id="popover-content-metric-popover-DaysPerReview" class="popover-content popover-align-left u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">The median number of days from submission to the end of the editorial review process.</div></div></div>
                </div>
                <a href="{url page="about" op="statistics"}" rel="noreferrer noopener" target="_self" data-aa-name="Review time" data-aa-region="metrics" class="label__DefaultLabel-sc-11qqina-0-a BKOrl metrics-days-peer-review anchor">
                    <span class="label__DefaultLabel-sc-11qqina-0 humCsR anchor-text">Review time</span>
                    <svg width="1em" height="1em" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg" class="label__ExternalLinkIcon-sc-11qqina-2 label___StyledExternalLinkIcon-sc-11qqina-3 bmMkto hwvhTt"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg>
                </a>
            </div>
            <p class="shared__MetricContent-sc-nfthpa-0 dQJdMx"><span class="shared__MetricValue-sc-nfthpa-1 value__MetricValue-sc-16w49ij-0 jpHFUu hTFtMC">{$daysPerReview}<span class="days u-ml-4">days</span></span>
                <div class="trend__ChartWrapper-sc-12iojgp-1 jOlahf">Data through {$lastYear} (median)</div>
            </p>
        </div>
		{/if}
		
		{if $submissionToAcceptance > 0}
        <div class="metric__Wrapper-sc-1q1u28e-0 kzQOoQ">
            <div class="label__Wrapper-sc-11qqina-1 koaLwK">
                <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle">
                    <button aria-label="More information about Submission to acceptance" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg>
                    </button>
                    <span role="status"></span>
                    <div id="popover-content-metric-popover-SubmissionToAcceptance" class="popover-content popover-align-left u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">The median number of days from submission to receipt of accept decision for all papers accepted at the journal.</div></div></div>
                </div>
                <a href="{url page="about" op="statistics"}" rel="noreferrer noopener" target="_self" data-aa-name="Submission to acceptance" data-aa-region="metrics" class="label__DefaultLabel-sc-11qqina-0-a BKOrl metrics-submission-to-acceptance anchor">
                    <span class="label__DefaultLabel-sc-11qqina-0 humCsR anchor-text">Submission to acceptance</span>
                    <svg width="1em" height="1em" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg" class="label__ExternalLinkIcon-sc-11qqina-2 label___StyledExternalLinkIcon-sc-11qqina-3 bmMkto hwvhTt"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg>
                </a>
            </div>
            <p class="shared__MetricContent-sc-nfthpa-0 dQJdMx"><span class="shared__MetricValue-sc-nfthpa-1 value__MetricValue-sc-16w49ij-0 jpHFUu hTFtMC">{$submissionToAcceptance}<span class="days u-ml-4">days</span></span>
                <div class="trend__ChartWrapper-sc-12iojgp-1 jOlahf">Data through {$lastYear} (median)</div>
            </p>
        </div>
		{/if}
		
		{if $acceptanceToPublication > 0}
        <div class="metric__Wrapper-sc-1q1u28e-0 kzQOoQ">
            <div class="label__Wrapper-sc-11qqina-1 koaLwK">
                <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle">
                    <button aria-label="More information about Acceptance to publication" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg>
                    </button>
                    <span role="status"></span>
                    <div id="popover-content-metric-popover-AcceptanceToPublication" class="popover-content popover-align-left u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">The median number of days from receipt of accept decision to first online publication for all papers accepted at the journal.</div></div></div>
                </div>
                <a href="{url page="about" op="statistics"}" rel="noreferrer noopener" target="_self" data-aa-name="Acceptance to publication" data-aa-region="metrics" class="label__DefaultLabel-sc-11qqina-0-a BKOrl metrics-acceptance-to-publication anchor">
                    <span class="label__DefaultLabel-sc-11qqina-0 humCsR anchor-text">Acceptance to publication</span>
                    <svg width="1em" height="1em" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg" class="label__ExternalLinkIcon-sc-11qqina-2 label___StyledExternalLinkIcon-sc-11qqina-3 bmMkto hwvhTt"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg>
                </a>
            </div>
            <p class="shared__MetricContent-sc-nfthpa-0 dQJdMx"><span class="shared__MetricValue-sc-nfthpa-1 value__MetricValue-sc-16w49ij-0 jpHFUu hTFtMC">{$acceptanceToPublication}<span class="days u-ml-4">days</span></span>
                <div class="trend__ChartWrapper-sc-12iojgp-1 jOlahf">Data through {$lastYear} (median)</div>
            </p>
        </div>
		{/if}
		
	</div>
	
    <!-- editing -->
</div>
