{**
 * templates/about/insight.tpl
 *
 * Halaman kustom 'Journal Insight' yang berisi metrik
 * dan informasi spesifik jurnal.
 *}
{strip}
{assign var="pageTitle" value="about.journalInsight"}
{include file="common/header.tpl"}

<div id="journalInsight">

    <h3>Aim and Scope</h3>
    <p>{$currentJournal->getLocalizedSetting('focusScopeDesc')|nl2br|default:'[Aim and Scope not set]'}</p>

    <hr>

    <h3>Journal Information</h3>
    <ul>
        <li><strong>ISSN (Online):</strong> {$currentJournal->getSetting('onlineIssn')|escape|default:'N/A'}</li>
        <li><strong>ISSN (Print):</strong> {$currentJournal->getSetting('printIssn')|escape|default:'N/A'}</li>
    </ul>

</div>

{/strip}

{include file="common/footer.tpl"}