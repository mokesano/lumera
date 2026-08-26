{**
 * templates/submission/metadata/metadata.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the submission metadata table. Non-form implementation.
 *}
<div id="metadata" class="block">
	<h3>{translate key="submission.metadata"}</h3>
	{if $canEditMetadata}
		<p><a href="{url op="viewMetadata" path=$submission->getId()}" class="action">{translate key="submission.editMetadata"}</a></p>
		{call_hook name="Templates::Submission::Metadata::Metadata::AdditionalEditItems"}
	{/if}

	<div id="authors" class="block">
		<h4>{translate key="article.authors"}</h4>
			
		<table width="100%" class="data">
			{foreach name=authors from=$submission->getAuthors() item=author}
			<tr valign="top">
				<td width="20%" class="label">{translate key="user.name"}</td>
				<td width="80%" class="value">
					{assign var=emailString value=$author->getFullName()|concat:" <":$author->getEmail():">"}
					{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$emailString|to_array subject=$submission->getLocalizedTitle()|strip_tags articleId=$submission->getId()}
					{$author->getFullName()|escape} {icon name="mail" url=$url}
				</td>
			</tr>
			{if $author->getData('orcid')}
				<tr valign="top">
					<td class="label">{translate key="user.orcid"}</td>
					<td class="value"><a href="{$author->getData('orcid')|escape}" target="_blank">{$author->getData('orcid')|escape}</a></td>
				</tr>
			{/if}
			{if $author->getUrl()}
				<tr valign="top">
					<td class="label">{translate key="user.url"}</td>
					<td class="value"><a href="{$author->getUrl()|escape:"quotes"}">{$author->getUrl()|escape}</a></td>
				</tr>
			{/if}
			<tr valign="top">
				<td class="label">{translate key="user.affiliation"}</td>
				<td class="value">{$author->getLocalizedAffiliation()|escape|nl2br|default:"&mdash;"}</td>
			</tr>
			<tr valign="top">
				<td class="label">{translate key="common.country"}</td>
				<td class="value">{$author->getCountryLocalized()|escape|default:"&mdash;"}</td>
			</tr>
			{* [WIZDAM] CRediT -- MENGGANTIKAN competingInterests per-penulis
			   lama (sudah dipindah jadi field level artikel, lihat bagian
			   Deklarasi di bawah). foreach datar satu tingkat. *}
			<tr valign="top">
				<td class="label">{translate key="author.credit.label"}</td>
				<td class="value">
					{if $author->getCreditRolesArray()}
						{foreach from=$author->getCreditRolesArray() item=roleCode name=creditRolesList}{translate key="author.credit.role.`$roleCode`"}{if !$smarty.foreach.creditRolesList.last}, {/if}{/foreach}
					{else}
						&mdash;
					{/if}
				</td>
			</tr>
			<tr valign="top">
				<td class="label">{translate key="user.biography"}</td>
				<td class="value">{$author->getLocalizedBiography()|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
			</tr>
			{if $author->getPrimaryContact()}
				<tr valign="top">
					<td colspan="2" class="label">{translate key="author.submit.selectPrincipalContact"}</td>
				</tr>
			{/if}
			{if !$smarty.foreach.authors.last}
				<tr>
					<td colspan="2" class="separator">&nbsp;</td>
				</tr>
			{/if}
			{/foreach}
		</table>
	</div>

	<div id="titleAndAbstract" class="block">
		<h4>{translate key="submission.titleAndAbstract"}</h4>

		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label">{translate key="article.title"}</td>
				<td width="80%" class="value">{$submission->getLocalizedTitle()|strip_unsafe_html|default:"&mdash;"}</td>
			</tr>

			<tr>
				<td colspan="2" class="separator">&nbsp;</td>
			</tr>
			<tr valign="top">
				<td class="label">{translate key="article.abstract"}</td>
				<td class="value">{$submission->getLocalizedAbstract()|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
			</tr>
		</table>
	</div>

	<div id="indexing" class="block">
		<h4>{translate key="submission.indexing"}</h4>
			
		<table width="100%" class="data">
			{if $currentJournal->getSetting('metaDiscipline')}
				<tr valign="top">
					<td width="20%" class="label">{translate key="article.discipline"}</td>
					<td width="80%" class="value">{$submission->getLocalizedDiscipline()|escape|default:"&mdash;"}</td>
				</tr>
				<tr>
					<td colspan="2" class="separator">&nbsp;</td>
				</tr>
			{/if}
			{if $currentJournal->getSetting('metaSubjectClass')}
				<tr valign="top">
					<td width="20%" class="label">{translate key="article.subjectClassification"}</td>
					<td width="80%" class="value">{$submission->getLocalizedSubjectClass()|escape|default:"&mdash;"}</td>
				</tr>
				<tr>
					<td colspan="2" class="separator">&nbsp;</td>
				</tr>
			{/if}
			{if $currentJournal->getSetting('metaSubject')}
				<tr valign="top">
					<td width="20%" class="label">{translate key="article.subject"}</td>
					<td width="80%" class="value">{$submission->getLocalizedSubject()|strip_unsafe_html|default:"&mdash;"}</td>
				</tr>
				<tr>
					<td colspan="2" class="separator">&nbsp;</td>
				</tr>
			{/if}
			{if $currentJournal->getSetting('metaCoverage')}
				<tr valign="top">
					<td width="20%" class="label">{translate key="article.coverageGeo"}</td>
					<td width="80%" class="value">{$submission->getLocalizedCoverageGeo()|escape|default:"&mdash;"}</td>
				</tr>
				<tr>
					<td colspan="2" class="separator">&nbsp;</td>
				</tr>
				<tr valign="top">
					<td class="label">{translate key="article.coverageChron"}</td>
					<td class="value">{$submission->getLocalizedCoverageChron()|escape|default:"&mdash;"}</td>
				</tr>
				<tr>
					<td colspan="2" class="separator">&nbsp;</td>
				</tr>
				<tr valign="top">
					<td class="label">{translate key="article.coverageSample"}</td>
					<td class="value">{$submission->getLocalizedCoverageSample()|escape|default:"&mdash;"}</td>
				</tr>
				<tr>
					<td colspan="2" class="separator">&nbsp;</td>
				</tr>
			{/if}
			{if $currentJournal->getSetting('metaType')}
				<tr valign="top">
					<td width="20%" class="label">{translate key="article.type"}</td>
					<td width="80%" class="value">{$submission->getLocalizedType()|escape|default:"&mdash;"}</td>
				</tr>
				<tr>
					<td colspan="2" class="separator">&nbsp;</td>
				</tr>
			{/if}
			<tr valign="top">
				<td width="20%" class="label">{translate key="article.language"}</td>
				<td width="80%" class="value">{$submission->getLanguage()|escape|default:"&mdash;"}</td>
			</tr>
		</table>
	</div>

	<div id="supportingAgencies" class="block">
		<h4>{translate key="submission.supportingAgencies"}</h4>
			
		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label">{translate key="submission.agencies"}</td>
				<td width="80%" class="value">{$submission->getLocalizedSponsor()|escape|default:"&mdash;"}</td>
			</tr>
		</table>
	</div>

	{* [WIZDAM] Funders (pendanaan/hibah terstruktur) -- berdampingan
	   dengan supportingAgencies (sponsor) di atas, TIDAK menggantikannya. *}
	<div id="funders" class="block">
		<h4>{translate key="author.submit.funders"}</h4>

		<table width="100%" class="data">
			{foreach from=$funders item=funder name=fundersList}
				<tr valign="top">
					<td width="20%" class="label">{translate key="author.submit.funderName"}</td>
					<td width="80%" class="value">{$funder->getFunderName()|escape}{if $funder->getAwardNumber()} ({$funder->getAwardNumber()|escape}){/if}</td>
				</tr>
				{if !$smarty.foreach.fundersList.last}
					<tr>
						<td colspan="2" class="separator">&nbsp;</td>
					</tr>
				{/if}
			{foreachelse}
				<tr valign="top">
					<td colspan="2" class="value">&mdash;</td>
				</tr>
			{/foreach}
		</table>
	</div>

	{* [WIZDAM] Deklarasi level artikel. *}
	<div id="declarations" class="block">
		<h4>{translate key="author.submit.declarations"}</h4>

		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label">{translate key="author.submit.competingInterestLabel"}</td>
				<td width="80%" class="value">{$submission->getLocalizedCompetingInterest()|escape|nl2br|default:"&mdash;"}</td>
			</tr>
			<tr>
				<td colspan="2" class="separator">&nbsp;</td>
			</tr>
			<tr valign="top">
				<td class="label">{translate key="author.submit.ethicalApprovalLabel"}</td>
				<td class="value">{$submission->getLocalizedEthicalApproval()|escape|nl2br|default:"&mdash;"}</td>
			</tr>
			<tr>
				<td colspan="2" class="separator">&nbsp;</td>
			</tr>
			<tr valign="top">
				<td class="label">{translate key="author.submit.generativeAiDeclarationLabel"}</td>
				<td class="value">{$submission->getLocalizedGenerativeAiDeclaration()|escape|nl2br|default:"&mdash;"}</td>
			</tr>
		</table>
	</div>

	{call_hook name="Templates::Submission::Metadata::Metadata::AdditionalMetadata"}

	{if $currentJournal->getSetting('metaCitations')}
	<div id="citations">
		<h4>{translate key="submission.citations"}</h4>

		<table width="100%" class="data">
			<tr valign="top">
				<td width="20%" class="label">{translate key="submission.citations"}</td>
				<td width="80%" class="value">{$submission->getCitations()|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
			</tr>
		</table>
	</div>
	{/if}

</div><!-- metadata -->