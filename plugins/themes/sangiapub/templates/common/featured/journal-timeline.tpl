{**
 * templates/common/journal-insights.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Journal Insights
 *
 *}

{php}
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($statsFile = 'plugins/themes/' . $matches[1] . '/php/journal_stats/getJournalStats.php')) {
        include_once($statsFile);
        $journalStats = getJournalStats($this->_tpl_vars['currentJournal']->getId(), Request::getUserVar('refresh_stats') == 'true');
            
        foreach ($journalStats as $key => $value) {
            if ($key != 'yearlyStats') $this->assign($key, $value);
        }
            
        $jsonPath = Request::getBasePath() . '/plugins/themes/' . $matches[1] . '/php/journal_stats/cache/journal_' . $this->_tpl_vars['currentJournal']->getId() . '_stats.json';
        $this->assign('statsJsonPath', file_exists('.' . $jsonPath . '.gz') ? $jsonPath . '.gz' : $jsonPath);
        break;
    }
}
{/php}

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
                <div id="popover-content-metric-popover-PublicationTimeline" class="popover-content popover-align-left heading_inline u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">Publication timeline presents the median publication time, calculated using all available data from the journal’s inaugural issue up to the year {$lastYear} preceding the current one. The data are processed without annual grouping to ensure greater accuracy, enabling a comprehensive median calculation based on the entire dataset rather than segmented yearly values.</div></div>
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
                <span class="label__DefaultLabel-sc-11qqina-0 humCsR">Time to first decision</span>
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
                <span class="label__DefaultLabel-sc-11qqina-0 humCsR">Review time</span>
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
                <span class="label__DefaultLabel-sc-11qqina-0 humCsR">Submission to acceptance</span>
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
                <span class="label__DefaultLabel-sc-11qqina-0 humCsR">Acceptance to publication</span>
            </div>
            <p class="shared__MetricContent-sc-nfthpa-0 dQJdMx"><span class="shared__MetricValue-sc-nfthpa-1 value__MetricValue-sc-16w49ij-0 jpHFUu hTFtMC">{$acceptanceToPublication}<span class="days u-ml-4">days</span></span>
                <div class="trend__ChartWrapper-sc-12iojgp-1 jOlahf">Data through {$lastYear} (median)</div>
            </p>
        </div>
		{/if}
		
		{if $daysToPublication > 0}
		<div class="metric__Wrapper-sc-1q1u28e-0 kzQOoQ">
		    <div class="label__Wrapper-sc-11qqina-1 koaLwK">
		        <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle">
		            <button aria-label="More information about Days to Publication" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg>
		            </button>
		            <span role="status"></span>
		            <div id="popover-content-metric-popover-DaysToPublication" class="popover-content popover-align-left u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">The median number of days from submission to publication time for all papers accepted at the journal.</div></div></div>
		        </div>
		        <span class="label__DefaultLabel-sc-11qqina-0 humCsR">Days to Publication</span>
		    </div>
		    <p class="shared__MetricContent-sc-nfthpa-0 dQJdMx"><span class="shared__MetricValue-sc-nfthpa-1 value__MetricValue-sc-16w49ij-0 jpHFUu hTFtMC">{$daysToPublication}<span class="days u-ml-4">days</span></span>
		        <div class="trend__ChartWrapper-sc-12iojgp-1 jOlahf">Data through {$lastYear} (median)</div>
		    </p>
		</div>
		{/if}
		
		{if $acceptRate > 0}
		<div class="metric__Wrapper-sc-1q1u28e-0 kzQOoQ">
		    <div class="label__Wrapper-sc-11qqina-1 koaLwK">
		        <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle"><button aria-label="More information about Acceptance rate" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg></button>
		            <span role="status"></span>
		            <div id="popover-content-metric-popover-AcceptanceRate" class="popover-content popover-align-left u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">The acceptance rate for journal is calculated by dividing the total number of accepted papers (initially and after standard review) by the total number of submissions (accepted and rejected).</div></div></div>
		        </div>
		        <span class="label__DefaultLabel-sc-11qqina-0 humCsR">Acceptance rate</span>
		    </div>
		    <p class="shared__MetricContent-sc-nfthpa-0 dQJdMx"><span class="shared__MetricValue-sc-nfthpa-1 percentage__MetricValue-sc-wf8bqi-0 jpHFUu fyajhE">{$acceptRate}%</span><div class="percentage__Background-sc-wf8bqi-1 fUIkQs">
		        <div class="percentage__Value-sc-wf8bqi-2 fWbXIe">Data through {$lastYear} (median)</div></div>
		    </p>
		</div>
		{/if}

		{if $declineRate > 0}
		<div class="metric__Wrapper-sc-1q1u28e-0 kzQOoQ">
		    <div class="label__Wrapper-sc-11qqina-1 koaLwK">
		        <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle"><button aria-label="More information about Declined rate" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg></button>
		            <span role="status"></span>
		            <div id="popover-content-metric-popover-DeclineRate" class="popover-content popover-align-left u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">The decline rate for journal is calculated by dividing the total number of decline papers (initially and after standard review) by the total number of submissions.</div></div>
		            </div>
		        </div>
		        <span class="label__DefaultLabel-sc-11qqina-0 humCsR">Declined rate</span>
		        <p class="shared__MetricContent-sc-nfthpa-0 dQJdMx"><span class="shared__MetricValue-sc-nfthpa-1 percentage__MetricValue-sc-wf8bqi-0 jpHFUu fyajhE">{$declineRate}%</span><div class="percentage__Background-sc-wf8bqi-1 fUIkQs">
		            <div class="percentage__Value-sc-wf8bqi-2 fWbXIe">Data through {$lastYear} (median)</div></div>
		        </p>
		     </div>
		</div>
		{/if}
	</div>
    <!-- editing -->
</div>

{* Hidden container untuk data JSON *}
<div id="journalStatsCharts" data-json-path="{$statsJsonPath}" class="u-mb-48">
  <div class="loading-stats">Menyiapkan data statistik...</div>
</div>

<div id="__next" class="journal-metrics">
    <!-- editing -->
    {assign var=currentYear value="now"|date_format:"%Y"}
	{assign var=threeYearsAgo value="-3 years"|strtotime|date_format:"%Y"}
	{assign var="lastYear" value=$currentYear-1}
    <div class="medium-12 main-insights-contents">
        <h2 class="headline-1283242569 jour-insight u-font-sans">
            <span title="Journal Insights">Journal Metrics Insights</span>
            <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle">
                <button aria-label="More information about Publication timeline" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg>
                </button>
                <span role="status"></span>
                <div id="popover-content-metric-popover-PublicationTimeline" class="popover-content popover-align-left heading_inline u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">Publication timeline presents the median publication time, calculated using all available data from the journal’s inaugural issue up to the year {$lastYear} preceding the current one. The data are processed without annual grouping to ensure greater accuracy, enabling a comprehensive median calculation based on the entire dataset rather than segmented yearly values.</div></div>
                </div>
            </div>
        </h2>
    </div>
    
    <div class="max-width__MaxWidth-sc-1dxr8k6-0 metrics__Metrics-sc-ewcnzs-0 ilPtKO jFWzXz u-font-sangia-sans">
        
		{if $currentJournal->getSetting('publicationFeeEnabled')}
		<div class="metric__Wrapper-sc-1q1u28e-0 kzQOoQ">
		    <div class="label__Wrapper-sc-11qqina-1 koaLwK">
		        <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle">
		            <button aria-label="More information about {if $currentJournal->getLocalizedSetting('publicationFeeName')}{$currentJournal->getLocalizedSetting('publicationFeeName')|escape}{else}Article Publishing Charge{/if}" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg>
					</button>
					<span role="status"></span>
					{if $currentJournal->getLocalizedSetting('waiverPolicy') != ''}
					<div id="popover-content-metric-popover-PublicationTimeline" class="popover-content popover-align-left heading_inline u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">{$currentJournal->getLocalizedSetting('waiverPolicy')|strip_tags|escape}</div></div>
					</div>
					{elseif $currentJournal->getLocalizedSetting('publicationFeeDescription')}
					<div id="popover-content-metric-popover-PublicationTimeline" class="popover-content popover-align-left heading_inline u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">{$currentJournal->getLocalizedSetting('publicationFeeDescription')|strip_tags|escape} List price excluding taxes. Discount may apply. For further details see <a target="_self" rel="noopener noreferrer" href="{url page="about" op="submissions" anchor="authorFees"}" class="primary-link__Anchor-sc-kvjqii-0 nrSBe"><span>Open Access details</span>.</div></div>
					</div>
					{/if}
				</div>
				<span class="label__DefaultLabel-sc-11qqina-0 humCsR">{if $currentJournal->getLocalizedSetting('publicationFeeName')}{$currentJournal->getLocalizedSetting('publicationFeeName')|escape}{else}Article Publishing Charge{/if}</span>
			</div>
			<div class="shared__MetricContent-sc-nfthpa-0 price__MetricContent-sc-1vpr8ci-2 dQJdMx bgWknh">
			    <p class="tag__TextWrapper-sc-1fw5i3t-5 dOqPha price__OA-sc-1vpr8ci-0 ha-DFQY"><span aria-hidden="true" data-color="gold" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig icNxLig"><span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">OA</span></span>
			    </p>
			    {if $currentJournal->getSetting('publicationFee') && $currentJournal->getLocalizedSetting('waiverPolicy') != ''}
			    <span class="shared__MetricValue-sc-nfthpa-1 price__WaivedPrice-sc-1vpr8ci-1 jpHFUu ifruuq">{$currentJournal->getSetting('currency')}{$currentJournal->getSetting('publicationFee')|string_format:"%.2f"|number_format:2:".":","}</span>
				<span class="shared__MetricValue-sc-nfthpa-1 price___StyledMetricValue-sc-1vpr8ci-3 jpHFUu bKSuCu" title="{$currentJournal->getLocalizedTitle()|strip_tags|escape} journal waiver Article Publication Charge (APC)">*</span>
				{else}
				<span class="shared__MetricValue-sc-nfthpa-1 jpHFUu">{$currentJournal->getSetting('currency')} {$currentJournal->getSetting('publicationFee')|string_format:"%.2f"|number_format:2:".":","}{if $currentJournal->getLocalizedSetting('publicationFeeDescription')|strip_tags|escape}<span class="required">*</span>{/if}
				</span>
				{/if}
			</div>
			{if $currentJournal->getLocalizedSetting('waiverPolicy') != ''}
			<div class="trend__ChartWrapper-sc-12iojgp-1 jOlahf"><span class="required">*</span>Term and Conditions waiver apply.</div>
			{/if}
		</div>
		{else}
		<div class="metric__Wrapper-sc-1q1u28e-0 kzQOoQ">
		    <div class="label__Wrapper-sc-11qqina-1 koaLwK">
				<span class="label__DefaultLabel-sc-11qqina-0 humCsR">Article Publishing Charge</span>
			</div>
			<div class="shared__MetricContent-sc-nfthpa-0 price__MetricContent-sc-1vpr8ci-2 dQJdMx bgWknh">
			    <p class="tag__TextWrapper-sc-1fw5i3t-5 dOqPha price__OA-sc-1vpr8ci-0 ha-DFQY">
			        <span aria-hidden="true" data-color="gold" class="tag__TagWrapper-sc-1fw5i3t-0 cNxLig icNxLig">
			            <span class="tag__TagText-sc-1fw5i3t-1 gcYJkb">OA</span>
			        </span>
			    </p>
				<span class="shared__MetricValue-sc-nfthpa-1 jpHFUu">{if $currentJournal->getSetting('publicationFee')}{$currentJournal->getSetting('currency')}{$currentJournal->getSetting('publicationFee')|string_format:"%.2f"|number_format:2:".":","}{else}<span title="{$currentJournal->getLocalizedTitle()|strip_tags|escape} journal no Article Publication Charge (APC)">Free</span>{/if}</span>
			</div>
		</div>
		{/if}
		
		{if $totalArticles > 0}
        <div class="metric__Wrapper-sc-1q1u28e-0 kzQOoQ">
            <div class="label__Wrapper-sc-11qqina-1 koaLwK">
                <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle">
                    <button aria-label="More information about Articles published counts" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg>
                    </button>
                    <span role="status"></span>
                    <div id="popover-content-metric-popover-ArticlesPublishedCounts" class="popover-content popover-align-left u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">Total number of articles that have been published at the journal.</div></div></div>
                </div>
                <span class="label__DefaultLabel-sc-11qqina-0">Articles published counts</span>
            </div>
            <p class="shared__MetricContent-sc-nfthpa-0 dQJdMx"><span class="shared__MetricValue-sc-nfthpa-1 value__MetricValue-sc-16w49ij-0 jpHFUu hTFtMC">{$totalArticles}</span>
                <div class="trend__ChartWrapper-sc-12iojgp-1 jOlahf">Since  {$firstYear|escape} until {$lastPublicationYear}</div>
            </p>
        </div>
        {/if}
		
		{if $journalTotalViews}
		<div class="metric__Wrapper-sc-1q1u28e-0 kzQOoQ">
		    <div class="label__Wrapper-sc-11qqina-1 koaLwK">
		        <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle">
		            <button aria-label="More information about Articles readership counts" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg>
		            </button>
		            <span role="status"></span>
		            <div id="popover-content-metric-popover-ArticlesReadeshipsCount" class="popover-content popover-align-left u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">The number of total views online publication for all articles at the journal.</div></div>
		            </div>
		        </div>
		        <span class="label__DefaultLabel-sc-11qqina-0 humCsR">Articles readership counts</span>
		    </div>
		    <p class="shared__MetricContent-sc-nfthpa-0 dQJdMx"><span class="shared__MetricValue-sc-nfthpa-1 value__MetricValue-sc-16w49ij-0 jpHFUu hTFtMC">{$journalTotalViews|number_format|default:"0"|escape}</span>
		        {assign var=firstYear value=$currentJournal->getSetting('initialYear')}
		        <div class="trend__ChartWrapper-sc-12iojgp-1 jOlahf">Updated: {$lastUpdated|date_format:"%d %b %Y %H:%M"|escape} (weekly)</div>
		    </p>
		</div>
		{/if}

		{if $journalTotalDownloads}
		<div class="metric__Wrapper-sc-1q1u28e-0 kzQOoQ">
		    <div class="label__Wrapper-sc-11qqina-1 koaLwK">
		        <div class="tooltip__Wrapper-sc-1lc2ea0-0 iqKRTS label___StyledTooltip-sc-11qqina-5 ipqtle"><button aria-label="More information about Articles downloads" type="button" class="button__Button-sc-1qzfzkl-0 tooltip__Button-sc-1lc2ea0-1 bmFPFJ kfcsiK" aria-expanded="false" aria-haspopup="false"><svg width="17" height="17" viewBox="0 0 13 13" xmlns="http://www.w3.org/2000/svg" class="label__InfoIcon-sc-11qqina-4 kxcWvB"><g fill="none" fill-rule="evenodd"><path d="M6.983 4.096V3H6v1.096h.983zm0 6.069v-5H6v5h.983z" fill="currentColor" fill-rule="nonzero"></path><circle stroke="currentColor" cx="6.5" cy="6.5" r="6"></circle></g></svg></button>
		            <span role="status"></span>
		            <div id="popover-content-metric-popover-ArticlesDownloads" class="popover-content popover-align-left u-js-hide" style="width: 350px;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">The number of total downloads of all articles published at the journal.</div></div></div>
		        </div>
		        <span class="label__DefaultLabel-sc-11qqina-0 humCsR">Articles downloads</span>
		    </div>
		    <p class="shared__MetricContent-sc-nfthpa-0 dQJdMx"><span class="shared__MetricValue-sc-nfthpa-1 value__MetricValue-sc-16w49ij-0 jpHFUu hTFtMC">{$journalTotalDownloads|number_format|default:"0"|escape}</span>
		        {assign var=firstYear value=$currentJournal->getSetting('initialYear')}
		        <div class="trend__ChartWrapper-sc-12iojgp-1 jOlahf">Updated: {$calculationDate|date_format:"%d %b %Y %H:%M"|escape} (weekly)</div>
		    </p>
		</div>
		{/if}
		
	</div>
									
	{if $currentJournal->getSetting('publicationFeeEnabled')}
	<div class="max-width__MaxWidth-sc-1dxr8k6-0 fa-dqFD">
	    {if $currentJournal->getLocalizedSetting('waiverPolicy') != ''}
	    <p class="eyVTMB kXEFzQ u-mb-8 u-font-sangia-sans"><span class="required">*</span>{$currentJournal->getLocalizedSetting('waiverPolicy')|strip_tags|escape}</p>
	    {/if}
	    {if $currentJournal->getLocalizedSetting('publicationFeeDescription')}
	    <p class="eyVTMB kXEFzQ u-font-sangia-sans"><span class="required optional">*</span>{$currentJournal->getLocalizedSetting('publicationFeeDescription')|strip_tags|escape} List price excluding taxes. Discount may apply. For further details see <a target="_self" rel="noopener noreferrer" href="{url page="about" op="submissions" anchor="authorFees"}" class="primary-link__Anchor-sc-kvjqii-0 nrSBe"><span>Open Access details</span></a>.</p>
	    {/if}
	</div>
	{/if}
	
    <!-- editing -->
</div>
