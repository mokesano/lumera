{**
 * templates/section/about.tpl
 *
 * [WIZDAM] Section About Page
 * Menampilkan informasi section ini.
 *}
{strip}
{assign var="pageTitleTranslated" value=$section->getLocalizedTitle()}
{include file="common/header-index.tpl"}
{/strip}

<div class="section-about">

    {* Header Section *}
    <div class="section-header">
        {if $section->getLocalizedAbbrev()}
        <span class="section-abbrev">{$section->getLocalizedAbbrev()|escape}</span>
        {/if}
        <div class="section-nav">
            <a href="{url page="section" op=$section->getSectionUrlTitle()}">
                {translate key="section.sectionTheSection"}
            </a>
            <a href="{url page="section" op=$section->getSectionUrlTitle() path="about"}">
                {translate key="section.aboutTheSection"}
            </a>
            <a href="{url page="section" op=$section->getSectionUrlTitle() path="articles"}" class="active">
                {translate key="section.sectionArticle"}
            </a>
        </div>
    </div>

    {* Section Editors *}
    {* Lead Editor — User object langsung, tidak ada array terbatas *}
    {if $leadEditor}
    <div class="section-lead-editor">
        <h2>{translate key="section.leadEditor"}</h2>
        <div class="editor-item">
            {if $leadEditor->getProfileImageUrl()}
            <img src="{$leadEditor->getProfileImageUrl()|escape}"
                 alt="{$leadEditor->getFullName()|escape}"
                 class="editor-photo" />
            {/if}
            <div class="editor-identity">
                <strong>{$leadEditor->getFullName()|escape}</strong>
    
                {if $leadEditor->getLocalizedAffiliation()}
                {assign var="affiliations" value=$leadEditor->getLocalizedAffiliation()|explode:"\n"}
                {foreach from=$affiliations item=affiliation}
                    {if $affiliation|trim}
                    <p class="affiliation">{$affiliation|escape}</p>
                    {/if}
                {/foreach}
                {/if}
    
                {if $leadEditor->getCountry()}
                <p class="country">{$leadEditor->getCountryName()|escape}</p>
                {/if}
    
                {if $leadEditor->getData('orcid')}
                <a href="https://orcid.org/{$leadEditor->getData('orcid')|escape}"
                   target="_blank">ORCID</a>
                {/if}
    
                {if $leadEditor->getSintaId()}
                <a href="https://sinta.kemdikbud.go.id/authors/profile/{$leadEditor->getSintaId()|escape}"
                   target="_blank">Sinta</a>
                {/if}
    
                {if $leadEditor->getScopusId()}
                <a href="https://www.scopus.com/authid/detail.uri?authorId={$leadEditor->getScopusId()|escape}"
                   target="_blank">Scopus</a>
                {/if}
            </div>
        </div>
    </div>
    {/if}

    {* Identitas Section *}
    <div class="section-identity">
        {if $section->getLocalizedIdentifyType()}
        <div class="section-type">
            <strong>{translate key="section.identifyType"}:</strong>
            {$section->getLocalizedIdentifyType()|escape}
        </div>
        {/if}

        {if $section->getLocalizedPolicy()}
        <div class="section-policy">
            <h2>{translate key="about.sectionPolicies"}</h2>
            {$section->getLocalizedPolicy()}
        </div>
        {/if}

        <div class="section-flags">
            {if $section->getMetaReviewed()}
            <span class="flag-reviewed">{translate key="section.peerReviewed"}</span>
            {/if}
            {if $section->getMetaIndexed()}
            <span class="flag-indexed">{translate key="section.openArchive"}</span>
            {/if}
        </div>
    </div>

    {* Kebijakan Diwariskan dari Jurnal *}
    {if $authorGuidelines}
    <div class="section-guidelines">
        <h2>{translate key="about.authorGuidelines"}</h2>
        {$authorGuidelines}
    </div>
    {/if}

    {if $submissionChecklist}
    <div class="section-checklist">
        <h2>{translate key="about.submissionPreparationChecklist"}</h2>
        <ol>
        {foreach from=$submissionChecklist item=checklistItem}
            <li>{$checklistItem.content}</li>
        {/foreach}
        </ol>
    </div>
    {/if}

    {if $copyrightNotice}
    <div class="section-copyright">
        <h2>{translate key="about.copyrightNotice"}</h2>
        {$copyrightNotice}
    </div>
    {/if}

    {if $privacyStatement}
    <div class="section-privacy">
        <h2>{translate key="about.privacyStatement"}</h2>
        {$privacyStatement}
    </div>
    {/if}

</div>

<div id="sectionMiniJournal" class="u-js-hide">

    {* 2. SECTION EDITORS (DEWAN REDAKSI RUBRIK) *}
    <div id="sectionEditors" class="block">
        <h3>{translate key="user.role.sectionEditors"}</h3>
        <div class="section-content">
            {if $sectionEditors && count($sectionEditors) > 0}
                <div class="editor-list">
                {foreach from=$sectionEditors item=editorData}
                    <div class="editors-profile">
                        
                        {* 2.1 Foto Profil *}
                        {if $editorData.profileImage}
                            <div class="editor-photo">
                                <img src="{$baseUrl}/public/site/{$editorData.profileImage.uploadName|escape:"url"}" alt="{$editorData.fullName|escape}">
                            </div>
                        {/if}

                        {* 2.2 Identitas Utama *}
                        <div class="editor-identity">
                            <span class="editor-salutation">{$editorData.salutation|escape}</span>
                            <span class="editor-fullname">{$editorData.fullName|escape}</span>
                            <span class="editor-initials">({$editorData.initials|escape})</span>
                            <span class="editor-gender">
                                {if $editorData.gender == 'M'} [Male]
                                {elseif $editorData.gender == 'F'} [Female]
                                {elseif $editorData.gender == 'O'} [Other]
                                {/if}
                            </span>
                        </div>

                        {* 2.3 Info Akun & Sistem *}
                        <div class="editor-system-info">
                            <ul>
                                <li><strong>Username:</strong> {$editorData.username|escape}</li>
                                <li><strong>Date Registered:</strong> {$editorData.dateRegistered|escape}</li>
                                <li><strong>Last Login:</strong> {$editorData.dateLastLogin|escape}</li>
                            </ul>
                        </div>

                        {* 2.4 Afiliasi, Negara & Interests *}
                        <div class="editor-academic-info">
                            
                            {* Multi-Afiliasi *}
                            <div class="editor-affiliations">
                                <strong>Affiliations:</strong>
                                {if $editorData.affiliations}
                                    <ul>
                                    {foreach from=$editorData.affiliations item=affiliation}
                                        <li>{$affiliation|escape}</li>
                                    {/foreach}
                                    </ul>
                                {else}
                                    <span><em>Tidak ada data afiliasi</em></span>
                                {/if}
                            </div>
                            
                            {* Negara *}
                            <div class="editor-country">
                                <strong>Country:</strong> 
                                <span>{if $editorData.country}{$editorData.country|escape}{else}<em>Tidak ada data</em>{/if}</span>
                            </div>

                            {* Reviewing Interests *}
                            <div class="editor-interests">
                                <strong>Reviewing Interests:</strong>
                                <div>
                                    {if $editorData.interests}
                                        {$editorData.interests|escape}
                                    {else}
                                        <em>Tidak ada data minat</em>
                                    {/if}
                                </div>
                            </div>

                        </div>

                        {* 2.5 Kontak & Tautan *}
                        <div class="editor-contacts">
                            <ul>
                                {if $editorData.email}<li><strong>Email:</strong> {$editorData.email|escape}</li>{/if}
                                {if $editorData.phone}<li><strong>Phone:</strong> {$editorData.phone|escape}</li>{/if}
                                {if $editorData.fax}<li><strong>Fax:</strong> {$editorData.fax|escape}</li>{/if}
                                {if $editorData.url}<li><strong>Website:</strong> <a href="{$editorData.url|escape}">{$editorData.url|escape}</a></li>{/if}
                                {if $editorData.orcid}<li><strong>ORCID:</strong> <a href="{$editorData.orcid|escape}">{$editorData.orcid|escape}</a></li>{/if}
                            </ul>
                        </div>

                        {* 2.6 Alamat *}
                        <div class="editor-addresses">
                            {if $editorData.mailingAddress}
                                <div>
                                    <strong>Mailing Address:</strong>
                                    <p>{$editorData.mailingAddress|nl2br}</p>
                                </div>
                            {/if}
                            {if $editorData.billingAddress}
                                <div>
                                    <strong>Billing Address:</strong>
                                    <p>{$editorData.billingAddress|nl2br}</p>
                                </div>
                            {/if}
                        </div>

                        {* 2.7 Hak Akses (Privilege) *}
                        <div class="editor-privilege">
                            <p><strong>Section Privileges:</strong></p>
                            <ul>
                                <li>Can Edit: {if $editorData.canEdit} Yes {else} No {/if}</li>
                                <li>Can Review: {if $editorData.canReview} Yes {else} No {/if}</li>
                            </ul>
                        </div>

                        {* 2.8 Biografi & Signature *}
                        <div class="editor-bio-signature">
                            {if $editorData.biography}
                                <div class="editor-bio">
                                    <strong>Biography:</strong>
                                    <div>{$editorData.biography|strip_unsafe_html}</div>
                                </div>
                            {/if}
                            {if $editorData.signature}
                                <div class="editor-signature">
                                    <strong>Signature:</strong>
                                    <div>{$editorData.signature|strip_unsafe_html}</div>
                                </div>
                            {/if}
                        </div>

                    </div>
                    <hr> {* Pemisah antar editor *}
                {/foreach}
                </div>
            {else}
                <p><em>{translate key="common.none"}</em></p>
            {/if}
        </div>
    </div>

    <div class="separator"></div>

    {* 1. DESKRIPSI & KEBIJAKAN SECTION *}
    <div id="sectionPolicy" class="block">
        <h3>{translate key="about.focusAndScope"} / {translate key="about.sectionPolicies"}</h3>
        {if $section->getLocalizedPolicy()}
            <div class="section-content">
                {$section->getLocalizedPolicy()|nl2br}
            </div>
        {else}
            <p><em>{translate key="common.none"}</em></p>
        {/if}
    </div>

    <div class="separator"></div>
    
    {* 0. INFORMASI IDENTITAS JURNAL & SECTION *}
    <div id="identityInfo" class="block">
        <h3>Identitas Jurnal & Rubrik</h3>
        <div class="identity-content">
            <ul>
                <li><strong>Nama Jurnal:</strong> {$journalTitle|escape}</li>
                <li><strong>Abbreviation Jurnal:</strong> {$journalInitials|escape}</li>
                <li><strong>Print ISSN:</strong> {if $printIssn}{$printIssn|escape}{else}<em>Tidak ada</em>{/if}</li>
                <li><strong>Online ISSN:</strong> {if $onlineIssn}{$onlineIssn|escape}{else}<em>Tidak ada</em>{/if}</li>
                <br>
                <li><strong>Nama Section:</strong> {$section->getLocalizedTitle()|escape}</li>
                <li><strong>Abbreviation Section:</strong> {$section->getLocalizedAbbrev()|escape}</li>
            </ul>
        </div>
    </div>
    
    <div class="separator"></div>

    {* 3. AUTHOR GUIDELINES & SUBMISSION CHECKLIST *}
    {if $authorGuidelines || $submissionChecklist}
        <div id="sectionSubmission" class="block">
            <h3>{translate key="about.submissions"}</h3>
            <div class="section-content">
                {if $authorGuidelines}
                    <h4>{translate key="about.authorGuidelines"}</h4>
                    <div class="guidelines">
                        {$authorGuidelines|nl2br}
                    </div>
                {/if}

                {if $submissionChecklist}
                    <h4>{translate key="about.submissionPreparationChecklist"}</h4>
                    <ul class="checklist">
                    {foreach from=$submissionChecklist item=checklistItem}
                        <li>{$checklistItem.content|nl2br}</li>
                    {/foreach}
                    </ul>
                {/if}
            </div>
        </div>
        <div class="separator"></div>
    {/if}

    {* 4. COPYRIGHT & PRIVACY STATEMENT *}
    {if $copyrightNotice || $privacyStatement}
        <div id="sectionPolicies" class="block">
            <h3>{translate key="about.policies"}</h3>
            <div class="section-content">
                {if $copyrightNotice}
                    <h4>{translate key="about.copyrightNotice"}</h4>
                    <div class="policy-item">
                        {$copyrightNotice|nl2br}
                    </div>
                {/if}

                {if $privacyStatement}
                    <h4>{translate key="about.privacyStatement"}</h4>
                    <div class="policy-item">
                        {$privacyStatement|nl2br}
                    </div>
                {/if}
            </div>
        </div>
    {/if}

</div>

{include file="common/footer.tpl"}