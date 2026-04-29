{**
 * templates/user/profile.tpl
 *
 * Copyright (c) 2017-2026 Wizdam Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User profile form - Modern Version.
 *
 *}
{strip}
{assign var="pageTitle" value="user.profile.editProfile"}
{url|assign:"url" op="profile"}
{include file="common/header-parts/header-user.tpl"}
{/strip}

<div class="auth-container">
    <div class="profile-card">
        <div class="auth-header u-mb-24">
            <p class="auth-subtitle">{translate key="user.profile.description"}</p>
        </div>
        
        <div class="alert alert-info">
            <p>{translate key="user.profile.alert"}</p>
        </div>

        <form id="profile" method="post" action="{url op="saveProfile"}" enctype="multipart/form-data">
            
            {* WIZDAM SECURITY: Token CSRF Wajib Ada *}
            <input value="{$csrfToken|escape}" name="csrfToken" type="hidden">

            {include file="common/formErrors.tpl"}

            <div class="form-section-note">
                <p>
                    <span class="required-indicator">*</span>
                    {translate key="common.requiredField"}
                </p>
            </div>
            
            {* Language Selection *}
            {if count($formLocales) > 1}
                <div class="form-section">
                    <h3 class="form-section-title">{translate key="common.language"}</h3>
                    <div class="form-group">
                        <div class="language-selector-container">
                            {url|assign:"userProfileUrl" page="user" op="profile" escape=false}
                            {form_language_chooser form="profile" url=$userProfileUrl}
                        </div>
                        <div class="form-help-text">{translate key="form.formLanguage.description"}</div>
                    </div>
                </div>
            {/if}

            {* Basic Information *}
            <div class="form-section">
                <h3 class="form-section-title">{translate key="user.accountInformation"}</h3>
                
                {* Username (Read-only) *}
                <div class="form-group">
                    <input type="text" id="username" class="form-control" value="{$username|escape}" readonly>
                    <label for="username" class="form-control-label">{translate key="user.username"}<span class="required-indicator">*</span></label>
                </div>
                <div class="form-group">
                    <input type="email" name="email" id="email" class="form-control" value="{$email|escape}" maxlength="90" required>
                    <label for="email" class="form-control-label">{translate key="user.email"}<span class="required-indicator">*</span></label>
                    <div class="error-message">{translate key="user.email"} is required</div>
                    <div class="success-message">{translate key="user.email"} is available!</div>
                </div>
            </div>

            {* Personal User Fields *}
            <div class="form-section">
                <h3 class="form-section-title">{translate key="user.personal"}</h3>
                
                {* Prefix *}
                <div class="form-group">
                    <input type="text" name="salutation" id="salutation" class="form-control" value="{$salutation|escape}" maxlength="40">
                    <label for="salutation" class="form-control-label">{translate key="user.salutation"}</label>
                    <div class="form-help-text">{translate key="user.salutation.description"}</div>
                    <div class="success-message">{translate key="user.salutation"} looks good!</div>
                </div>
                {* Given Name *}
                <div class="form-group">
                    <input type="text" name="firstName" id="firstName" class="form-control" value="{$firstName|escape}" maxlength="40" required>
                    <label for="firstName" class="form-control-label">{translate key="user.firstName"}<span class="required-indicator">*</span></label>
                    <div class="form-help-text">{translate key="user.firstName.description"}</div>
                    <div class="error-message">{translate key="user.firstName"} is required</div>
                    <div class="success-message">{translate key="user.firstName"} looks good!</div>
                </div>
                {* Middle Name *}
                <div class="form-group">
                    <input type="text" name="middleName" id="middleName" class="form-control" value="{$middleName|escape}" maxlength="40">
                    <label for="middleName" class="form-control-label">{translate key="user.middleName"}</label>
                    <div class="form-help-text">{translate key="user.middleName.description"}</div>
                    <div class="error-message">{translate key="user.middleName"} is required</div>
                    <div class="success-message">{translate key="user.middleName"} looks good!</div>
                </div>
                {* Family Name *}
                <div class="form-group">
                    <input type="text" name="lastName" id="lastName" class="form-control" value="{$lastName|escape}" maxlength="90" required>
                    <label for="lastName" class="form-control-label">{translate key="user.lastName"}<span class="required-indicator">*</span></label>
                    <div class="form-help-text">{translate key="user.firstName.description"}</div>
                    <div class="error-message">{translate key="user.lastName"} is required</div>
                    <div class="success-message">{translate key="user.lastName"} looks good!</div>
                </div>
                {* Suffix *}
                <div class="form-group">
                    <input type="text" id="suffix" name="suffix" class="form-control" 
                           value="{$suffix|escape}" maxlength="90">
                    <label for="suffix" class="form-control-label">{translate key="user.suffix"}</label>
                    <div class="form-help-text">{translate key="user.suffix.description"}</div>
                    <div class="success-message">{translate key="user.suffix"} Looks good!</div>
                </div>
                {* Initials and Gender *}        
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="initials" id="initials" class="form-control" value="{$initials|escape}" maxlength="5">
                        <label for="initials" class="form-control-label">{translate key="user.initials"}</label>
                        <div class="success-message">Looks good!</div>
                        <div class="form-help-text">{translate key="user.initialsExample"}</div>
                    </div>
                    <div class="form-group">
                        <select name="gender" id="gender" class="form-control">
                            <option value=""></option>
                            {html_options_translate options=$genderOptions selected=$gender}
                        </select>
                        <label for="gender" class="form-control-label">{translate key="user.gender"}</label>
                        <div class="success-message">Looks good!</div>
                        <div class="form-help-text">{translate key="user.gender.description"}</div>
                    </div>
                </div>
            </div>

            {* Professional Information *}
            <div class="form-section">
                <h3 class="form-section-title">{translate key="user.profile.professionalInformation"}</h3>
                
                <div class="form-group">
                    <textarea name="affiliation[{$formLocale|escape}]" id="affiliation" class="form-control form-textarea" rows="3" required>{$affiliation[$formLocale]|escape}</textarea>
                    <label for="affiliation" class="form-control-label">{translate key="user.affiliation"}<span class="required-indicator">*</span>
                    </label>
                    <div class="form-help-text">{translate key="user.affiliation.description"}</div>
                    <div class="error-message">{translate key="user.affiliation"} is required</div>
                    <div class="success-message">{translate key="user.affiliation"} is good!</div>
                </div>

                <div class="form-group">
                    <select name="country" id="country" class="form-control" required>
                        <option value=""></option>
                        {html_options options=$countries selected=$country}
                    </select>
                    <label for="country" class="form-control-label">{translate key="common.country"}<span class="required-indicator">*</span></label>
                    <div class="error-message">{translate key="common.country"} is required</div>
                    <div class="success-message">{translate key="common.country"} looks good!</div>
                </div>
                
                <div class="form-group">
                    <textarea name="biography[{$formLocale|escape}]" id="biography" class="form-control form-textarea" rows="7">{$biography[$formLocale]|escape}</textarea>
                    <label for="biography" class="form-control-label">{translate key="user.biography"}</label>
                    <div class="form-help-text">{translate key="user.biography.description"}</div>
                </div>
                
                <div class="form-group">
                    <textarea name="signature[{$formLocale|escape}]" id="signature" class="form-control form-textarea" rows="7">{$signature[$formLocale]|escape}</textarea>
                    <label for="signature" class="form-control-label">{translate key="user.signature"}</label>
                    <div class="form-help-text">{translate key="user.signature.description"}</div>
                </div>
            </div>

            {* Researcher Identifiers *}
            <div class="form-section">
                <h3 class="form-section-title">{translate key="user.identifiers"}</h3>

                <div class="form-group">
                    <input type="text" name="orcid" id="orcid" class="form-control" value="{$orcid|escape}" maxlength="255">
                    <label for="orcid" class="form-control-label">{translate key="user.orcid"}</label>
                    <div class="form-help-text">{translate key="user.orcid.description"}</div>
                </div>

                <div class="form-group">
                    <input type="url" name="googleScholar" id="googleScholar" class="form-control" value="{$googleScholar|escape}" maxlength="255">
                    <label for="googleScholar" class="form-control-label">{translate key="user.googleScholar"}</label>
                    <div class="form-help-text">{translate key="user.googleScholar.description"}</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="sintaId" id="sintaId" class="form-control" value="{$sintaId|escape}" maxlength="24">
                        <label for="sintaId" class="form-control-label">{translate key="user.sintaId"}</label>
                        <div class="form-help-text">{translate key="user.sintaId.description"}</div>
                    </div>
                    <div class="form-group">
                        <input type="text" name="scopusId" id="scopusId" class="form-control" value="{$scopusId|escape}" maxlength="24">
                        <label for="scopusId" class="form-control-label">{translate key="user.scopusId"}</label>
                        <div class="form-help-text">{translate key="user.scopusId.description"}</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="dimensionId" id="dimensionId" class="form-control" value="{$dimensionId|escape}" maxlength="24">
                        <label for="dimensionId" class="form-control-label">{translate key="user.dimensionId"}</label>
                        <div class="form-help-text">{translate key="user.dimensionId.description"}</div>
                    </div>
                    <div class="form-group">
                        <input type="text" name="researcherId" id="researcherId" class="form-control" value="{$researcherId|escape}" maxlength="24">
                        <label for="researcherId" class="form-control-label">{translate key="user.researcherId"}</label>
                        <div class="form-help-text">{translate key="user.researcherId.description"}</div>
                    </div>
                </div>

                <div class="form-group">
                    <input type="url" name="userUrl" id="userUrl" class="form-control" value="{$userUrl|escape}" maxlength="255">
                    <label for="userUrl" class="form-control-label">{translate key="user.url"}</label>
                    <div class="form-help-text">{translate key="user.url.description"}</div>
                </div>
            </div>

            {* Contact Information *}
            <div class="form-section">
                <h3 class="form-section-title">{translate key="user.profile.contactInformation"}</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <input type="tel" name="phone" id="phone" class="form-control" value="{$phone|escape}" maxlength="24">
                        <label for="phone" class="form-control-label">{translate key="user.phones"}</label>
                        <div class="form-help-text">{translate key="user.phone.description"}</div>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="fax" id="fax" class="form-control" value="{$fax|escape}" maxlength="24">
                        <label for="fax" class="form-control-label">{translate key="user.fax"}</label>
                        <div class="form-help-text">{translate key="user.fax.description"}</div>
                    </div>
                </div>

                <div class="form-group">
                    <textarea name="mailingAddress" id="mailingAddress" class="form-control form-textarea" rows="3">{$mailingAddress|escape}</textarea>
                    <label for="mailingAddress" class="form-control-label">{translate key="common.mailingAddress"}</label>
                    <div class="form-help-text">{translate key="user.mailingAddress.description"}</div>
                </div>

                <div class="form-group">
                    <textarea name="gossip[{$formLocale|escape}]" id="gossip" class="form-control form-textarea" rows="4">{$gossip[$formLocale]|escape}</textarea>
                    <label for="gossip" class="form-control-label">{translate key="user.gossip"}</label>
                    <div class="form-help-text">{translate key="user.gossip.description"}</div>
                </div>
            </div>

            {* Journal Roles *}
            {if $currentJournal}
                <div class="form-section">
                    <h3 class="form-section-title">{translate key="user.roles"}</h3>
                    <div class="modern-checkbox-group">
                        {if $allowRegReader}
                            <div class="modern-checkbox-item {if $isReader || $readerRole}checked{/if}" onclick="toggleModernCheckbox(this, 'readerRole')">
                                <div class="modern-checkbox-content">
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title">{translate key="user.role.reader"}</div>
                                        <div class="modern-checkbox-description">{translate key="user.register.readerDescription"}</div>
                                    </div>
                                </div>
                                <input type="checkbox" id="readerRole" name="readerRole" class="modern-checkbox-input" {if $isReader || $readerRole}checked="checked"{/if}>
                            </div>
                        {/if}
                        
                        {if $allowRegAuthor}
                            <div class="modern-checkbox-item {if $isAuthor || $authorRole}checked{/if}" onclick="toggleModernCheckbox(this, 'authorRole')">
                                <div class="modern-checkbox-content">
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title">{translate key="user.role.author"}</div>
                                        <div class="modern-checkbox-description">{translate key="user.register.authorDescription"}</div>
                                    </div>
                                </div>
                                <input type="checkbox" id="authorRole" name="authorRole" class="modern-checkbox-input" {if $isAuthor || $authorRole}checked="checked"{/if}>
                            </div>
                        {/if}
                        
                        {if $allowRegReviewer}
                            <div class="modern-checkbox-item {if $isReviewer || $reviewerRole}checked{/if}" onclick="toggleModernCheckbox(this, 'reviewerRole')">
                                <div class="modern-checkbox-content">
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title">{translate key="user.role.reviewer"}</div>
                                        <div class="modern-checkbox-description">{translate key="user.register.reviewerDescription"}</div>
                                    </div>
                                </div>
                                <input type="checkbox" id="reviewerRole" name="reviewerRole" class="modern-checkbox-input" {if $isReviewer || $reviewerRole}checked="checked"{/if}>
                            </div>
                        {/if}
                    </div>
                </div>
            {/if}

            {* --- [WIZDAM FIX] FAIL-SAFE INTERESTS INPUT --- *}
            <div class="form-section" id="reviewer-interests-section">
                <h3 class="form-section-title">{translate key="user.interests"}</h3>
                <div class="form-group u-js-hide">
                    <input type="text" name="interestsTextOnly" id="interestsTextOnly" class="form-control" value="{$interestsTextOnly|escape}" maxlength="255">
                    <label for="interestsTextOnly" class="form-control-label">{translate key="user.interests"}</label>
                    <div class="form-help-text">{translate key="user.interests.description"}</div>
                </div>
                <div class="form-group">
                    <textarea name="interestsTextOnly" id="interestsTextOnly" class="form-control form-textarea" rows="3">{$interestsTextOnly|escape}</textarea>
                    <label for="interestsTextOnly" class="form-control-label">{translate key="user.interests"}</label>
                    <div class="form-help-text">{translate key="user.interests.description"}</div>
                </div>
            
                {* Reviewing Interests - Tampil otomatis jika user reviewer *}
                {if $allowRegReviewer || $isReviewer}
                <div class="form-group">
                    <div id="interests-container">
                        {include file="form/interestsInput.tpl" FBV_interestsKeywords=$interestsKeywords FBV_interestsTextOnly=$interestsTextOnly}
                    </div>
                </div>
                {/if}

            </div>

            {* Profile Image *}
            <div class="form-section">
                <h3 class="form-section-title">{translate key="user.profile.form.profileImage"}</h3>
                
                <div class="auth-header u-mb-8">
                    <p class="auth-subtitle">Recommended size 150×150 pixels (max. 500 KB). <a href="javascript:void(0)" onclick="var url='https://apps.sangia.org/tools/compress/compress_image'; if(url.startsWith('https://apps.sangia.org') && confirm('Open Resize Image Tool?')) window.open(url, '_blank', 'noopener,noreferrer');">Resize your image</a> if needed. Verify before saving.</p>
                </div>
                
                <div class="form-group">
                    <div class="profile-image-upload-container">
                        <div class="file-input-wrapper">
                            <input type="file" id="profileImage" name="profileImage" class="file-input-hidden" accept="image/*">
                            <div class="file-input-display" onclick="document.getElementById('profileImage').click()">
                                <div class="file-input-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="9" cy="9" r="2"/>
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                    </svg>
                                </div>
                                <div class="file-input-content">
                                    <div class="file-input-text">Choose profile image</div>
                                    <div class="file-input-subtext">JPG, PNG, or GIF recommended</div>
                                </div>
                                <div class="file-browse-btn">Browse</div>
                            </div>
                        </div>
                        
                        <div class="profile-image-actions">
                            <input type="submit" name="uploadProfileImage" value="{translate key="common.upload"}" class="button" />
                        </div>
                    </div>
                    
                    {if $profileImage}
                        <div class="current-profile-image">
                            <div class="current-image-info">
                                <h4 class="image-info">Current Profile Image</h4>
                                <div class="image-details">
                                    <p><strong>File:</strong> {$profileImage.name|escape}</p>
                                    <p><strong>Uploaded:</strong> {$profileImage.dateUploaded|date_format:$datetimeFormatShort}</p>
                                </div>
                                <input type="submit" name="deleteProfileImage" value="{translate key="common.delete"}" class="button" />
                            </div>
                            <div class="current-image-preview">
                                <img src="{$sitePublicFilesDir}/{$profileImage.uploadName|escape:"url"}" 
                                     width="{$profileImage.width|escape}" 
                                     height="{$profileImage.height|escape}" 
                                     alt="{translate key="user.profile.form.profileImage"}" 
                                     class="profile-image-thumbnail">
                            </div>
                        </div>
                    {/if}
                </div>
            </div>

            {* Working Languages *}
            {if count($availableLocales) > 1}
                <div class="form-section">
                    <h3 class="form-section-title">{translate key="user.workingLanguages"}</h3>
                    <div class="checkbox-group">
                        {foreach from=$availableLocales key=localeKey item=localeName}
                            <div class="checkbox-container {if in_array($localeKey, $userLocales)}checked{/if}" onclick="toggleCheckbox(this, 'userLocales-{$localeKey|escape}')">
                                <div class="checkbox-content">
                                    <div class="checkbox-indicator"></div>
                                    <div class="checkbox-text">{$localeName|escape}</div>
                                </div>
                                <input type="checkbox" name="userLocales[]" id="userLocales-{$localeKey|escape}" 
                                       value="{$localeKey|escape}" class="checkbox-input" 
                                       {if in_array($localeKey, $userLocales)}checked="checked"{/if}>
                            </div>
                        {/foreach}
                    </div>
                </div>
            {/if}

            {* Open Access Notifications *}
            {if $displayOpenAccessNotification}
                <div class="form-section">
                    <h3 class="form-section-title">{translate key="user.profile.form.openAccessNotifications"}</h3>
                    <div class="checkbox-group">
                        {foreach from=$journals name=journalOpenAccessNotifications key=thisJournalId item=thisJournal}
                            {assign var=thisJournalId value=$thisJournal->getJournalId()}
                            {assign var=publishingMode value=$thisJournal->getSetting('publishingMode')}
                            {assign var=enableOpenAccessNotification value=$thisJournal->getSetting('enableOpenAccessNotification')}
                            {assign var=notificationEnabled value=$user->getSetting('openAccessNotification', $thisJournalId)}
                            
                            {if $publishingMode == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $enableOpenAccessNotification}
                                <div class="checkbox-container {if $notificationEnabled}checked{/if}" onclick="toggleCheckbox(this, 'openAccessNotify-{$thisJournalId|escape}')">
                                    <div class="checkbox-content">
                                        <div class="checkbox-indicator"></div>
                                        <div class="checkbox-text">{$thisJournal->getLocalizedTitle()|escape}</div>
                                    </div>
                                    <input type="checkbox" name="openAccessNotify[]" id="openAccessNotify-{$thisJournalId|escape}" 
                                           value="{$thisJournalId|escape}" class="checkbox-input" 
                                           {if $notificationEnabled}checked="checked"{/if}>
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                </div>
            {/if}

            {* [WIZDAM] Layer Security: Turnstile + reCAPTCHA v3 + v2 *}
            {if $turnstileEnabled || $reCaptchaEnabled}
            <div class="security-barrier">
                {* CLOUDFLARE TURNSTILE *}
                {if $turnstileEnabled}
                <div class="turnstile-group">
                    <div id="turnstile-container" class="cf-turnstile" 
                        data-sitekey="{$turnstilePublicKey|escape}" 
                        data-theme="light"
                        data-size="flexible"
                        data-callback="onTurnstileSuccess">
                    </div>
                    <div id="turnstile-loading"><span class="spinner"></span> Loading security verification...</div>
                    <div id="turnstile-error"></div>
                </div>
                {/if}
                    
                {* reCAPTCHA Support v3/v2 *}
                {if $reCaptchaEnabled}
                <div class="recaptcha-group">
                    {if $reCaptchaVersion == 3}
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                        <script src="https://www.google.com/recaptcha/api.js?render={$reCaptchaPublicKey|escape}"></script>
                        {literal}
                        <script>document.addEventListener("DOMContentLoaded",function(){var a=document.getElementById('g-recaptcha-response');if(a){var b=a.closest('form');if(b){b.addEventListener('submit',function(c){c.preventDefault();var d='{/literal}{$reCaptchaPublicKey|escape}{literal}';grecaptcha.ready(function(){grecaptcha.execute(d,{action:'register'}).then(function(e){document.getElementById('g-recaptcha-response').value=e;b.submit()})})})}}});</script>
                        {/literal}
                    {elseif $reCaptchaVersion == 2}
                        <div class="g-recaptcha" 
                            data-sitekey="{$reCaptchaPublicKey|escape}" 
                            data-theme="light"
                            data-size="flexible">
                        </div>
                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>        
                    {else}
                        {* Fallback: Legacy reCAPTCHA HTML *}
                        <div class="value u-mb-24">
                            {$reCaptchaHtml}
                        </div>
                    {/if}
                </div>
                {/if}
                    
                {* Teks Bantuan Dinamis *}
                {if $turnstileEnabled || $reCaptchaEnabled}
                    <div class="form-help-text u-hide">
                        ScholarWizdam register system protected by 
                        {if $turnstileEnabled && $reCaptchaEnabled}Cloudflare Turnstile & Google reCAPTCHA
                        {elseif $turnstileEnabled}Cloudflare Turnstile
                        {else}Google reCAPTCHA{/if}.
                    </div>
                {/if}
            </div>
            {/if}
            {* [WIZDAM] END Layer Security: Turnstile + reCAPTCHA v3 + v2 *}
                
            {* Turnstile Security Widget *}
            <div class="turnstile-group">
                <div id="turnstile-container" class="cf-turnstile"
                    data-sitekey="{$turnstilePublicKey|escape}"
                    data-theme="light"
                    data-size="flexible"
                    data-callback="onTurnstileSuccess">
                </div>
                <div id="turnstile-loading"><span class="spinner"></span> Loading security verification...</div>
                <div id="turnstile-error"></div>
            </div>
            
            {* Form Actions *}
            <div class="actions-button">
                <p>
                    <input type="submit" value="{translate key="common.save"}" class="button" />
                    <input type="button" value="{translate key="common.cancel"}" class="defaultButton" onclick="document.location.href='{url page="user"}'" />
                </p>
            </div>

            <div class="form-section-note">
                <p>
                    <span class="required-indicator">*</span>
                    {translate key="common.requiredField"}
                </p>
            </div>

        </form>
    </div>
</div>

<script>
{literal}
document.addEventListener('DOMContentLoaded', function() {
    // Initialize only essential components
    initializeOJSInterestsInput();
    initializeFileInput();
    initializeFormSubmission();
});

function initializeOJSInterestsInput() {
    // Wait for OJS scripts and tagit to be fully loaded
    setTimeout(function() {
        const interestsContainer = document.getElementById('interests-container');
        
        if (interestsContainer) {
            // Only apply styling, don't create new elements
            applyInterestsStyling(interestsContainer);
            observeInterestsChanges(interestsContainer);
        }
    }, 300);
}

function applyInterestsStyling(container) {
    // Find the actual OJS interests elements
    const interestsDiv = container.querySelector('#interests');
    const interestsList = container.querySelector('.interests');
    
    if (interestsDiv && interestsList) {
        // Add CSS class instead of inline styles
        interestsDiv.classList.add('tagit-modern');
        interestsList.classList.add('interests-modern');
        
        // Style existing tagit tags
        styleExistingTags(container);
        
        // Style the input field if it exists
        const tagitInput = interestsList.querySelector('input[type="text"]');
        if (tagitInput) {
            tagitInput.classList.add('tagit-input-modern');
        }
    }
    
    // Handle textarea fallback (when JavaScript is disabled)
    const textareaFallback = container.querySelector('.interestsTextOnly');
    if (textareaFallback && window.getComputedStyle(textareaFallback).display !== 'none') {
        textareaFallback.classList.add('form-control', 'form-textarea');
    }
}

function styleExistingTags(container) {
    const existingTags = container.querySelectorAll('.tagit-tag');
    
    existingTags.forEach(tag => {
        if (!tag.hasAttribute('data-modern-styled')) {
            tag.classList.add('tagit-tag-modern');
            
            // Style the close button if it exists
            const closeBtn = tag.querySelector('.tagit-close');
            if (closeBtn) {
                closeBtn.classList.add('tagit-close-modern');
            }
            
            tag.setAttribute('data-modern-styled', 'true');
        }
    });
}

function observeInterestsChanges(container) {
    // Only observe changes to apply styling to new tags
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                // Style any new tags that are added dynamically
                setTimeout(function() {
                    styleExistingTags(container);
                    
                    // Re-style input if it changes
                    const interestsList = container.querySelector('.interests');
                    if (interestsList) {
                        const tagitInput = interestsList.querySelector('input[type="text"]:not(.tagit-input-modern)');
                        if (tagitInput) {
                            tagitInput.classList.add('tagit-input-modern');
                        }
                    }
                }, 10);
            }
        });
    });
    
    observer.observe(container, {
        childList: true,
        subtree: true
    });
    
    container._observer = observer;
}

function initializeFileInput() {
    const fileInput = document.getElementById('profileImage');
    const fileDisplay = document.querySelector('.file-input-display');
    const textElement = document.querySelector('.file-input-text');
    const subtextElement = document.querySelector('.file-input-subtext');
    
    if (fileInput && fileDisplay) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                textElement.textContent = file.name;
                subtextElement.textContent = `${Math.round(file.size / 1024)} KB`;
                fileDisplay.classList.add('file-selected');
                
                // File validation
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!allowedTypes.includes(file.type)) {
                    showProfileFeedback('Please select a valid image file (JPG, PNG, or GIF)', 'error');
                    resetFileInput();
                    return;
                }
                
                if (file.size > maxSize) {
                    showProfileFeedback('File size too large. Please use an image under 5MB', 'error');
                    resetFileInput();
                    return;
                }
                
                // Enable upload button by removing disabled attribute
                const uploadBtn = document.querySelector('input[name="uploadProfileImage"]');
                if (uploadBtn) {
                    uploadBtn.removeAttribute('disabled');
                }
                
            } else {
                resetFileInput();
            }
        });
    }
}

function resetFileInput() {
    const textElement = document.querySelector('.file-input-text');
    const subtextElement = document.querySelector('.file-input-subtext');
    const fileDisplay = document.querySelector('.file-input-display');
    const uploadBtn = document.querySelector('input[name="uploadProfileImage"]');
    
    if (textElement) textElement.textContent = 'Choose profile image';
    if (subtextElement) subtextElement.textContent = 'JPG, PNG, or GIF recommended';
    if (fileDisplay) fileDisplay.classList.remove('file-selected');
    if (uploadBtn) uploadBtn.setAttribute('disabled', 'disabled');
}

function initializeFormSubmission() {
    const form = document.getElementById('profile');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        const submitter = document.activeElement;
        
        const isImageUpload = submitter && submitter.name === 'uploadProfileImage';
        const isImageDelete = submitter && submitter.name === 'deleteProfileImage';
        const isLanguageChange = submitter && submitter.name === 'setLocale';
        
        if (isImageUpload) {
            console.log('Image upload initiated');
            const fileInput = document.getElementById('profileImage');
            if (fileInput && fileInput.files && fileInput.files[0]) {
                const file = fileInput.files[0];
                console.log('Uploading file:', file.name, 'Size:', file.size, 'Type:', file.type);
                showProfileFeedback('Profile image is being uploaded...', 'info');
            }
            return true;
        }
        
        if (isImageDelete) {
            console.log('Image delete initiated');
            showProfileFeedback('Profile image is being deleted...', 'info');
            return true;
        }
        
        if (isLanguageChange) {
            console.log('Language change initiated');
            return true;
        }
        
        // Regular profile save - validate required fields
        console.log('Profile save initiated');
        const isValid = validateProfileForm(form);
        if (!isValid) {
            e.preventDefault();
            showProfileFeedback('Please fill in all required fields', 'error');
            return false;
        }
        
        return true;
    });
}

function validateProfileForm(form) {
    const requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    requiredFields.forEach(function(field) {
        if (!field.value || !field.value.trim()) {
            field.classList.add('form-error');
            isValid = false;
        } else {
            field.classList.remove('form-error');
        }
    });
    
    return isValid;
}

function showProfileFeedback(message, type) {
    // Remove existing feedback
    const existingFeedback = document.querySelector('.profile-feedback');
    if (existingFeedback) {
        existingFeedback.remove();
    }
    
    // Create new feedback element
    const feedback = document.createElement('div');
    feedback.className = `profile-feedback profile-feedback-${type}`;
    feedback.innerHTML = `<strong>${type === 'error' ? 'Error: ' : type === 'success' ? 'Success: ' : 'Info: '}</strong>${message}`;
    
    // Insert feedback at top of form
    const form = document.getElementById('profile');
    if (form && form.firstChild) {
        form.insertBefore(feedback, form.firstChild);
    }
    
    // Auto remove after delay
    setTimeout(function() {
        if (feedback.parentNode) {
            feedback.remove();
        }
    }, 5000);
}

// Global error handler
window.onerror = function(msg, url, lineNo, columnNo, error) {
    console.log('JavaScript error:', msg);
    return true; // Suppress error display
};

// Ensure console exists
if (typeof console === 'undefined') {
    window.console = {
        log: function() {},
        error: function() {},
        warn: function() {}
    };
}

console.log('Clean profile form script loaded successfully');
{/literal}
</script>

{include file="common/footer-parts/footer-welcome.tpl"}
