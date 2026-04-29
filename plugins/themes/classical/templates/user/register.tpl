{**
 * templates/user/register.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User registration form.
 *
 *}
{strip}
{assign var="pageTitle" value="user.register"}
{include file="common/header.tpl"}
{/strip}
{assign var=isloggedin value=Validation::isLoggedIn()}

<div class="auth-container">
    <div class="auth-card register-card">
        <div class="auth-header u-mb-24">
            <p class="auth-subtitle">{translate key="user.register.completeForm"}</p>
        </div>

        {* Display info messages for implicit auth *}
        {if $implicitAuth === true && !$isloggedin}
            <div class="alert alert-info">
                <p><a href="{url page="login" op="implicitAuthLogin"}">{translate key="user.register.implicitAuth"}</a></p>
            </div>
        {else}
            <form id="registerForm" method="post" action="{url op="registerUser"}">

                {* Existing user handling *}
                {if !$implicitAuth || ($implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL && !$isloggedin)}
                    {if !$existingUser}
                        {url|assign:"url" page="user" op="register" existingUser=1}
                        <div class="alert alert-info">
                            <p>{translate key="user.register.alreadyRegisteredOtherJournal" registerUrl=$url}</p>
                        </div>
                    {else}
                        {url|assign:"url" page="user" op="register"}
                        <div class="alert alert-info">
                            <p>{translate key="user.register.notAlreadyRegisteredOtherJournal" registerUrl=$url}</p>
                        </div>
                        <input type="hidden" name="existingUser" value="1"/>
                        <div class="alert alert-info">
                            <p>{translate key="user.register.loginToRegister"}</p>
                        </div>
                    {/if}

                    {* Optional implicit auth message *}
                    {if $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
                        <div class="alert alert-info">
                            <p><a href="{url page="login" op="implicitAuthLogin"}">{translate key="user.register.implicitAuth"}</a></p>
                        </div>
                    {/if}

                    {* Include form errors *}
                    {include file="common/formErrors.tpl"}
                {/if}

                {* Hidden source field *}
                {if $source}
                    <input type="hidden" name="source" value="{$source|escape}" />
                {/if}

                {* Required field note *}
                {if !$implicitAuth || $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
                    <div class="form-section-note">
                        <p><span class="required-indicator">*</span> {translate key="common.requiredField"}</p>
                    </div>
                {/if}

                {* Form Language Selection *}
                {if count($formLocales) > 1 && !$existingUser}
                <div class="form-group">
                    <label class="form-section-label">{translate key="form.formLanguage"}</label>
                    {url|assign:"userRegisterUrl" page="user" op="register" escape=false}
                    {form_language_chooser form="registerForm" url=$userRegisterUrl}
                    <div class="form-help-text">{translate key="form.formLanguage.description"}</div>
                </div>
                {/if}

                <h3 class="form-section-title">{translate key="user.profile"}</h3>

                {* Account Fields - Only show if not using implicit auth or using optional implicit auth *}
                {if !$implicitAuth || ($implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL && !$isloggedin)}
                    {* Username *}
                    <div class="form-group">
                        <input type="text" id="username" name="username" class="form-control" 
                               value="{$username|escape}" maxlength="32" required>
                        <label for="username" class="form-control-label">
                            {translate key="user.username"}<span class="required-indicator">*</span>
                        </label>
                        <div class="error-message">{translate key="user.username"} is required</div>
                        <div class="success-message">Username is available!</div>
                        {if !$existingUser}
                        <div class="form-help-text">{translate key="user.register.usernameRestriction"}</div>
                        {/if}
                    </div>

                    {* Password *}
                    <div class="form-group">
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-control" 
                                   value="{$password|escape}" required>
                            <label for="password" class="form-control-label">
                                {translate key="user.password"}<span class="required-indicator">*</span>
                            </label>
                            <div class="password-toggle" data-target="password">
                                <svg class="icon-eye" viewBox="0 0 24 24">
                                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                </svg>
                                <svg class="icon-eye-off" viewBox="0 0 24 24" style="display: none;">
                                    <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                                </svg>
                            </div>
                            {* Password Strength Indicator *}
                            <div class="password-strength-indicator" id="passwordStrengthIndicator">
                                <div class="strength-bar">
                                    <div class="strength-segment" data-level="very-weak"></div>
                                    <div class="strength-segment" data-level="weak"></div>
                                    <div class="strength-segment" data-level="fair"></div>
                                    <div class="strength-segment" data-level="good"></div>
                                    <div class="strength-segment" data-level="strong"></div>
                                    <div class="strength-segment" data-level="very-strong"></div>
                                </div>
                                <span class="strength-label" id="strengthLabel"></span>
                            </div>
                            <div class="error-message">Please enter your password</div>
                        </div>
                        
                        {* Password Requirements *}
                        <div class="password-requirements">
                            <p class="requirements-title">Password must contain at least:</p>
                            <div class="requirements-grid">
                                <div class="requirement-item" id="req-length">
                                    <span class="requirement-icon">✗</span>
                                    <span class="requirement-text">8 characters</span>
                                </div>
                                <div class="requirement-item" id="req-uppercase">
                                    <span class="requirement-icon">✗</span>
                                    <span class="requirement-text">1 uppercase character</span>
                                </div>
                                <div class="requirement-item" id="req-number">
                                    <span class="requirement-icon">✗</span>
                                    <span class="requirement-text">1 number</span>
                                </div>
                                <div class="requirement-item" id="req-special">
                                    <span class="requirement-icon">✗</span>
                                    <span class="requirement-text">1 special character</span>
                                </div>
                            </div>
                        </div>
                        {if !$existingUser}
                        <div class="form-help-text">{translate key="user.register.passwordLengthRestriction" length=$minPasswordLength}</div>
                        {/if}
                    </div>

                    {* Confirm Password - Only for new users *}
                    {if !$existingUser}
                        <div class="form-group">
                            <div class="password-wrapper">
                                <input type="password" id="password2" name="password2" class="form-control" 
                                       value="{$password2|escape}" required>
                                <label for="password2" class="form-control-label">
                                    {translate key="user.repeatPassword"}<span class="required-indicator">*</span>
                                </label>
                                <div class="password-toggle" data-target="password2">
                                    <svg class="icon-eye" viewBox="0 0 24 24">
                                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                    </svg>
                                    <svg class="icon-eye-off" viewBox="0 0 24 24" style="display: none;">
                                        <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                                    </svg>
                                </div>
                                <div class="error-message">Passwords do not match</div>
                                <div class="success-message">Passwords match!</div>
                            </div>
                        </div>

                        {* Email Fields Row *}
                        <div class="form-row">
                            <div class="form-group">
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="{$email|escape}" maxlength="90" required>
                                <label for="email" class="form-control-label">
                                    {translate key="user.email"}<span class="required-indicator">*</span>
                                </label>
                                <div class="error-message">Please enter a valid email address</div>
                                <div class="success-message">Email looks good!</div>
                                {if $privacyStatement}
                                <div class="form-help-text">
                                    <a href="#privacyStatement">{translate key="user.register.privacyStatement"}</a>
                                </div>
                                {/if}
                            </div>
                            
                            <div class="form-group">
                                <input type="email" id="confirmEmail" name="confirmEmail" class="form-control" 
                                       value="{$confirmEmail|escape}" maxlength="90" required>
                                <label for="confirmEmail" class="form-control-label">
                                    {translate key="user.confirmEmail"}<span class="required-indicator">*</span>
                                </label>
                                <div class="error-message">Email addresses do not match</div>
                                <div class="success-message">Email addresses match!</div>
                            </div>
                        </div>
                        
                        {* CAPTCHA Support *}
                        {if $captchaEnabled}
                        <div class="form-group">
                            <label class="form-section-label">{translate key="common.captchaField"}<span class="required-indicator">*</span></label>
                            {if $reCaptchaEnabled}
                                {$reCaptchaHtml}
                            {else}
                                <img src="{url page="user" op="viewCaptcha" path=$captchaId}" alt="{translate key="common.captchaField.altText"}" width="100%" height="100" />
                                <input name="captcha" id="captcha" value="" size="20" maxlength="32" class="form-control" />
                                <input type="hidden" name="captchaId" value="{$captchaId|escape:"quoted"}" />
                                <div class="form-help-text">{translate key="common.captchaField.description"}</div>
                            {/if}
                        </div>
                        {/if}

                        {* Personal Information Section *}
                        <h3 class="form-section-title">Personal Information</h3>

                        {* Salutation *}
                        <div class="form-group">
                            <input type="text" id="salutation" name="salutation" class="form-control" 
                                   value="{$salutation|escape}" maxlength="40">
                            <label for="salutation" class="form-control-label">{translate key="user.salutation"}</label>
                            <div class="success-message">Looks good!</div>
                        </div>

                        {* First Name *}
                        <div class="form-group">
                            <input type="text" id="firstName" name="firstName" class="form-control" 
                                   value="{$firstName|escape}" maxlength="40" required>
                            <label for="firstName" class="form-control-label">
                                {translate key="user.firstName"}<span class="required-indicator">*</span>
                            </label>
                            <div class="error-message">First name is required</div>
                            <div class="success-message">Looks good!</div>
                        </div>

                        {* Middle Name *}
                        <div class="form-group">
                            <input type="text" id="middleName" name="middleName" class="form-control" 
                                   value="{$middleName|escape}" maxlength="40">
                            <label for="middleName" class="form-control-label">{translate key="user.middleName"}</label>
                        </div>

                        {* Last Name *}
                        <div class="form-group">
                            <input type="text" id="lastName" name="lastName" class="form-control" 
                                   value="{$lastName|escape}" maxlength="90" required>
                            <label for="lastName" class="form-control-label">
                                {translate key="user.lastName"}<span class="required-indicator">*</span>
                            </label>
                            <div class="error-message">Last name is required</div>
                            <div class="success-message">Looks good!</div>
                        </div>
                        
                        {* Suffix *}
                        <div class="form-group">
                            <input type="text" id="suffix" name="suffix" class="form-control" 
                                   value="{$suffix|escape}" maxlength="90">
                            <label for="suffix" class="form-control-label">
                                {translate key="user.suffix"}
                            </label>
                            <div class="success-message">Looks good!</div>
                        </div>

                        {* Initials & Gender Row *}
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" id="initials" name="initials" class="form-control" 
                                       value="{$initials|escape}" maxlength="5">
                                <label for="initials" class="form-control-label">{translate key="user.initials"}</label>
                                <div class="form-help-text">{translate key="user.initialsExample"}</div>
                            </div>
                            
                            <div class="form-group">
                                <select id="gender" name="gender" class="form-control">
                                    <option value="" disabled selected></option>
                                    {html_options_translate options=$genderOptions selected=$gender}
                                </select>
                                <label for="gender" class="form-control-label">{translate key="user.gender"}</label>
                            </div>
                        </div>

                        {* Affiliation *}
                        <div class="form-group">
                            <textarea id="affiliation" name="affiliation[{$formLocale|escape}]" 
                                     class="form-control form-textarea" rows="3">{$affiliation[$formLocale]|escape}</textarea>
                            <label for="affiliation" class="form-control-label">{translate key="user.affiliation"}</label>
                            <div class="form-help-text">{translate key="user.affiliation.description"}</div>
                        </div>

                        {* Country *}
                        <div class="form-group">
                            <select id="country" name="country" class="form-control">
                                <option value="" disabled selected></option>
                                {html_options options=$countries selected=$country}
                            </select>
                            <label for="country" class="form-control-label">{translate key="common.country"}</label>
                        </div>
                        
                        {* Contact Information *}
                        <h3 class="form-section-title">Contact Information</h3>
                        
                        {* ORCID *}
                        <div class="form-group">
                            <input type="text" id="orcid" name="orcid" class="form-control" 
                                   value="{$orcid|escape}" maxlength="255">
                            <label for="orcid" class="form-control-label">{translate key="user.orcid"}</label>
                            <div class="form-help-text">{translate key="user.orcid.description"}</div>
                        </div>

                        {* URL *}
                        <div class="form-group">
                            <input type="url" id="userUrl" name="userUrl" class="form-control" 
                                   value="{$userUrl|escape}" maxlength="255">
                            <label for="userUrl" class="form-control-label">{translate key="user.url"}</label>
                        </div>

                        {* Phone = Sinta ID & Fax = Scopus ID in Row *}
                        <div class="form-row">
                            <div class="form-group">
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="{$phone|escape}" maxlength="24">
                                <label for="phone" class="form-control-label">{translate key="user.phone"}</label>
                            </div>
                            
                            <div class="form-group">
                                <input type="tel" id="fax" name="fax" class="form-control" 
                                       value="{$fax|escape}" maxlength="24">
                                <label for="fax" class="form-control-label">{translate key="user.fax"}</label>
                            </div>
                        </div>

                        {* System Settings *}
                        <h3 class="form-section-title">System Settings</h3>

                        {* Send Password - Modern Checkbox Style *}
                        <div class="checkbox-container" data-checkbox="sendPassword">
                            <div class="checkbox-content">
                                <input type="checkbox" id="sendPassword" name="sendPassword" 
                                       value="1" class="checkbox-input"{if $sendPassword}{else} checked="checked"{/if}>
                                <div class="checkbox-indicator"></div>
                                <div class="checkbox-text">
                                    {translate key="user.sendPassword.description"}
                                </div>
                            </div>
                        </div>

                        {* Working Languages *}
                        {if count($availableLocales) > 1}
                        <div class="form-group">
                            <label class="form-section-label">{translate key="user.workingLanguages"}</label>
                            <div class="checkbox-group">
                                {foreach from=$availableLocales key=localeKey item=localeName}
                                <div class="checkbox-container" data-checkbox="userLocales-{$localeKey|escape}">
                                    <div class="checkbox-content">
                                        <input type="checkbox" name="userLocales[]" 
                                               id="userLocales-{$localeKey|escape}" 
                                               value="{$localeKey|escape}" 
                                               class="checkbox-input"{if in_array($localeKey, $userLocales)} checked="checked"{/if}>
                                        <div class="checkbox-indicator"></div>
                                        <div class="checkbox-text">{$localeName|escape}</div>
                                    </div>
                                </div>
                                {/foreach}
                            </div>
                        </div>
                        {/if}
                    {/if}
                {/if}

                {* Registration Roles - Modern Checkbox Design *}
                {if !$implicitAuth || $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL || ($implicitAuth === true && $isloggedin)}
                    {if $allowRegReader || $allowRegReader === null || $allowRegAuthor || $allowRegAuthor === null || $allowRegReviewer || $allowRegReviewer === null || ($currentJournal && $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $enableOpenAccessNotification)}
                        <h3 class="form-section-title">{translate key="user.register.registerAs"}</h3>
                        
                        <div class="modern-checkbox-group">
                            {if $allowRegReader || $allowRegReader === null}
                            <div class="modern-checkbox-item" data-checkbox="registerAsReader">
                                <div class="modern-checkbox-content">
                                    <input type="checkbox" name="registerAsReader" id="registerAsReader" 
                                           value="1" class="modern-checkbox-input"{if $registerAsReader} checked="checked"{/if}>
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title">{translate key="user.role.reader"}</div>
                                        <div class="modern-checkbox-description">
                                            {translate key="user.register.readerDescription"}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {/if}
                            
                            {if $currentJournal && $currentJournal->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_SUBSCRIPTION && $enableOpenAccessNotification}
                            <div class="modern-checkbox-item" data-checkbox="openAccessNotification">
                                <div class="modern-checkbox-content">
                                    <input type="checkbox" name="openAccessNotification" id="openAccessNotification" 
                                           value="1" class="modern-checkbox-input"{if $openAccessNotification} checked="checked"{/if}>
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title">{translate key="user.role.reader"}</div>
                                        <div class="modern-checkbox-description">
                                            {translate key="user.register.openAccessNotificationDescription"}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {/if}
                            
                            {if $allowRegAuthor || $allowRegAuthor === null}
                            <div class="modern-checkbox-item" data-checkbox="registerAsAuthor">
                                <div class="modern-checkbox-content">
                                    <input type="checkbox" name="registerAsAuthor" id="registerAsAuthor" 
                                           value="1" class="modern-checkbox-input"{if $registerAsAuthor} checked="checked"{/if}>
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title">{translate key="user.role.author"}</div>
                                        <div class="modern-checkbox-description">
                                            {translate key="user.register.authorDescription"}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {/if}
                            
                            {if $allowRegReviewer || $allowRegReviewer === null}
                            <div class="modern-checkbox-item" data-checkbox="registerAsReviewer">
                                <div class="modern-checkbox-content">
                                    <input type="checkbox" name="registerAsReviewer" id="registerAsReviewer" 
                                           value="1" class="modern-checkbox-input"{if $registerAsReviewer} checked="checked"{/if}>
                                    <div class="modern-checkbox-indicator"></div>
                                    <div class="modern-checkbox-text">
                                        <div class="modern-checkbox-title">{translate key="user.role.reviewer"}</div>
                                        <div class="modern-checkbox-description">
                                            {if $existingUser}{translate key="user.register.reviewerDescriptionNoInterests"}{else}{translate key="user.register.reviewerDescription"}{/if}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {* Reviewer Interests *}
                            <div id="reviewerInterestsContainer" style="margin-top: 15px; display: none;">
                                <div class="form-group">
                                    <input type="text" id="interests" name="interests" class="form-control">
                                    <label for="interests" class="form-control-label">{translate key="user.register.reviewerInterests"}</label>
                                    <div class="form-help-text">Please list your areas of expertise or interests, separated by commas.</div>
                                </div>
                                {include file="form/interestsInput.tpl" FBV_interestsKeywords=$interestsKeywords FBV_interestsTextOnly=$interestsTextOnly}
                            </div>
                            {/if}
                        </div>
                    {/if}
                {/if}
                
                {* Turnstile Security Widget *}
                <div class="turnstile-group">
                    <div id="turnstile-container" class="cf-turnstile"
                        data-sitekey="0x4AAAAAAA7b4JHByoY2iX27"
                        data-theme="light"
                        data-size="flexible"
                        data-callback="onTurnstileSuccess">
                    </div>
                    <div id="turnstile-loading">
                        <span class="spinner"></span>
                        Loading security verification...
                    </div>
                    <div id="turnstile-error"></div>
                </div>
                        
                {* Privacy Agreement - Enhanced with unified checkbox style *}
                <div class="privacy-agreement">
                    <div class="checkbox-container" data-checkbox="privacyAgreement">
                        <div class="checkbox-content">
                            <input type="checkbox" id="privacyAgreement" name="privacyAgreement" 
                                   value="1" class="checkbox-input" required>
                            <div class="checkbox-indicator"></div>
                            <div class="checkbox-text">
                                I agree to the <a href="{url page="about" op="submissions" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a> and <a href="#privacyStatement">{translate key="user.register.privacyStatement"}</a>
                            </div>
                        </div>
                    </div>
                </div>

                {* Submit Buttons *}
                <div class="form-actions">
                    <button type="submit" id="registerButton" class="btn-primary">
                        {translate key="user.register"}
                    </button>
                    
                    <button type="button" class="btn-secondary" onclick="document.location.href='{url page="index" escape=false}'">
                        {translate key="common.cancel"}
                    </button>
                </div>

                {* Required field note at bottom *}
                {if !$implicitAuth || $implicitAuth === $smarty.const.IMPLICIT_AUTH_OPTIONAL}
                    <div class="form-section-note">
                        <p><span class="required-indicator">*</span> {translate key="common.requiredField"}</p>
                    </div>
                {/if}
            </form>
        {/if}
    </div>
</div>

{* Privacy Statement Content for Dialog - Hidden by default *}
<div id="privacyStatement" style="display: none;">
    {if $privacyStatement}
        <h3 class="u-hide">{translate key="user.register.privacyStatement"}</h3>
        <p>{$privacyStatement|nl2br}</p>
    {else}
        <h3 class="u-hide">Privacy Statement</h3>
        <p>Your privacy is important to us. This privacy statement explains the personal data we process, how we process it, and for what purposes.</p>
        <p>We collect and process personal data to provide our services, improve user experience, and comply with legal obligations.</p>
        <p>We do not share your personal information with third parties without your consent, except as required by law.</p>
        <p>You have the right to access, correct, or delete your personal data. Please contact us if you have any questions about our privacy practices.</p>
    {/if}
</div>

{* Method 1: Include init.php (recommended) *}
{php}
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches)) {
        $initFile = 'plugins/themes/' . $matches[1] . '/php/turnstile_cf/init.php';
        if (file_exists($initFile)) {
            include_once($initFile);
            break;
        }
    }
}
{/php}

{* Sisanya tinggal pakai variables yang sudah di-set oleh init.php *}
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script>
{literal}
AuthForms.setConfig({
    siteKey: '{$turnstileSiteKey}',
    proxyUrl: '{$baseUrl}/plugins/themes/{$themePath}/php/turnstile_cf/proxy.php',
    theme: '{$turnstileTheme}',
    size: '{$turnstileSize}'
});
{/literal}
</script>

{include file="common/footer.tpl"}