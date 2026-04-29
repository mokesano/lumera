<?php /* Smarty version 2.6.26, created on 2026-04-04 22:16:56
         compiled from section/about.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'section/about.tpl', 17, false),array('modifier', 'explode', 'section/about.tpl', 47, false),array('modifier', 'trim', 'section/about.tpl', 49, false),array('modifier', 'nl2br', 'section/about.tpl', 232, false),array('modifier', 'strip_unsafe_html', 'section/about.tpl', 257, false),array('function', 'url', 'section/about.tpl', 20, false),array('function', 'translate', 'section/about.tpl', 21, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitleTranslated', $this->_tpl_vars['section']->getLocalizedTitle()); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-index.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div class="section-about">

        <div class="section-header">
        <?php if ($this->_tpl_vars['section']->getLocalizedAbbrev()): ?>
        <span class="section-abbrev"><?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedAbbrev())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
        <?php endif; ?>
        <div class="section-nav">
            <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'section','op' => $this->_tpl_vars['section']->getSectionUrlTitle()), $this);?>
">
                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "section.sectionTheSection"), $this);?>

            </a>
            <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'section','op' => $this->_tpl_vars['section']->getSectionUrlTitle(),'path' => 'about'), $this);?>
">
                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "section.aboutTheSection"), $this);?>

            </a>
            <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'section','op' => $this->_tpl_vars['section']->getSectionUrlTitle(),'path' => 'articles'), $this);?>
" class="active">
                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "section.sectionArticle"), $this);?>

            </a>
        </div>
    </div>

            <?php if ($this->_tpl_vars['leadEditor']): ?>
    <div class="section-lead-editor">
        <h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "section.leadEditor"), $this);?>
</h2>
        <div class="editor-item">
            <?php if ($this->_tpl_vars['leadEditor']->getProfileImageUrl()): ?>
            <img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['leadEditor']->getProfileImageUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"
                 alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['leadEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"
                 class="editor-photo" />
            <?php endif; ?>
            <div class="editor-identity">
                <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['leadEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong>
    
                <?php if ($this->_tpl_vars['leadEditor']->getLocalizedAffiliation()): ?>
                <?php $this->assign('affiliations', ((is_array($_tmp=$this->_tpl_vars['leadEditor']->getLocalizedAffiliation())) ? $this->_run_mod_handler('explode', true, $_tmp, "\n") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "\n"))); ?>
                <?php $_from = $this->_tpl_vars['affiliations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['affiliation']):
?>
                    <?php if (((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp))): ?>
                    <p class="affiliation"><?php echo ((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
                    <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?>
                <?php endif; ?>
    
                <?php if ($this->_tpl_vars['leadEditor']->getCountry()): ?>
                <p class="country"><?php echo ((is_array($_tmp=$this->_tpl_vars['leadEditor']->getCountryName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
                <?php endif; ?>
    
                <?php if ($this->_tpl_vars['leadEditor']->getData('orcid')): ?>
                <a href="https://orcid.org/<?php echo ((is_array($_tmp=$this->_tpl_vars['leadEditor']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"
                   target="_blank">ORCID</a>
                <?php endif; ?>
    
                <?php if ($this->_tpl_vars['leadEditor']->getSintaId()): ?>
                <a href="https://sinta.kemdikbud.go.id/authors/profile/<?php echo ((is_array($_tmp=$this->_tpl_vars['leadEditor']->getSintaId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"
                   target="_blank">Sinta</a>
                <?php endif; ?>
    
                <?php if ($this->_tpl_vars['leadEditor']->getScopusId()): ?>
                <a href="https://www.scopus.com/authid/detail.uri?authorId=<?php echo ((is_array($_tmp=$this->_tpl_vars['leadEditor']->getScopusId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"
                   target="_blank">Scopus</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

        <div class="section-identity">
        <?php if ($this->_tpl_vars['section']->getLocalizedIdentifyType()): ?>
        <div class="section-type">
            <strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "section.identifyType"), $this);?>
:</strong>
            <?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedIdentifyType())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

        </div>
        <?php endif; ?>

        <?php if ($this->_tpl_vars['section']->getLocalizedPolicy()): ?>
        <div class="section-policy">
            <h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.sectionPolicies"), $this);?>
</h2>
            <?php echo $this->_tpl_vars['section']->getLocalizedPolicy(); ?>

        </div>
        <?php endif; ?>

        <div class="section-flags">
            <?php if ($this->_tpl_vars['section']->getMetaReviewed()): ?>
            <span class="flag-reviewed"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "section.peerReviewed"), $this);?>
</span>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['section']->getMetaIndexed()): ?>
            <span class="flag-indexed"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "section.openArchive"), $this);?>
</span>
            <?php endif; ?>
        </div>
    </div>

        <?php if ($this->_tpl_vars['authorGuidelines']): ?>
    <div class="section-guidelines">
        <h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.authorGuidelines"), $this);?>
</h2>
        <?php echo $this->_tpl_vars['authorGuidelines']; ?>

    </div>
    <?php endif; ?>

    <?php if ($this->_tpl_vars['submissionChecklist']): ?>
    <div class="section-checklist">
        <h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.submissionPreparationChecklist"), $this);?>
</h2>
        <ol>
        <?php $_from = $this->_tpl_vars['submissionChecklist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['checklistItem']):
?>
            <li><?php echo $this->_tpl_vars['checklistItem']['content']; ?>
</li>
        <?php endforeach; endif; unset($_from); ?>
        </ol>
    </div>
    <?php endif; ?>

    <?php if ($this->_tpl_vars['copyrightNotice']): ?>
    <div class="section-copyright">
        <h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.copyrightNotice"), $this);?>
</h2>
        <?php echo $this->_tpl_vars['copyrightNotice']; ?>

    </div>
    <?php endif; ?>

    <?php if ($this->_tpl_vars['privacyStatement']): ?>
    <div class="section-privacy">
        <h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.privacyStatement"), $this);?>
</h2>
        <?php echo $this->_tpl_vars['privacyStatement']; ?>

    </div>
    <?php endif; ?>

</div>

<div id="sectionMiniJournal" class="u-js-hide">

        <div id="sectionEditors" class="block">
        <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.sectionEditors"), $this);?>
</h3>
        <div class="section-content">
            <?php if ($this->_tpl_vars['sectionEditors'] && count ( $this->_tpl_vars['sectionEditors'] ) > 0): ?>
                <div class="editor-list">
                <?php $_from = $this->_tpl_vars['sectionEditors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['editorData']):
?>
                    <div class="editors-profile">
                        
                                                <?php if ($this->_tpl_vars['editorData']['profileImage']): ?>
                            <div class="editor-photo">
                                <img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/site/<?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['profileImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['fullName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
                            </div>
                        <?php endif; ?>

                                                <div class="editor-identity">
                            <span class="editor-salutation"><?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['salutation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                            <span class="editor-fullname"><?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['fullName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                            <span class="editor-initials">(<?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['initials'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
)</span>
                            <span class="editor-gender">
                                <?php if ($this->_tpl_vars['editorData']['gender'] == 'M'): ?> [Male]
                                <?php elseif ($this->_tpl_vars['editorData']['gender'] == 'F'): ?> [Female]
                                <?php elseif ($this->_tpl_vars['editorData']['gender'] == 'O'): ?> [Other]
                                <?php endif; ?>
                            </span>
                        </div>

                                                <div class="editor-system-info">
                            <ul>
                                <li><strong>Username:</strong> <?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['username'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
                                <li><strong>Date Registered:</strong> <?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['dateRegistered'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
                                <li><strong>Last Login:</strong> <?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['dateLastLogin'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
                            </ul>
                        </div>

                                                <div class="editor-academic-info">
                            
                                                        <div class="editor-affiliations">
                                <strong>Affiliations:</strong>
                                <?php if ($this->_tpl_vars['editorData']['affiliations']): ?>
                                    <ul>
                                    <?php $_from = $this->_tpl_vars['editorData']['affiliations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['affiliation']):
?>
                                        <li><?php echo ((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
                                    <?php endforeach; endif; unset($_from); ?>
                                    </ul>
                                <?php else: ?>
                                    <span><em>Tidak ada data afiliasi</em></span>
                                <?php endif; ?>
                            </div>
                            
                                                        <div class="editor-country">
                                <strong>Country:</strong> 
                                <span><?php if ($this->_tpl_vars['editorData']['country']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['country'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><em>Tidak ada data</em><?php endif; ?></span>
                            </div>

                                                        <div class="editor-interests">
                                <strong>Reviewing Interests:</strong>
                                <div>
                                    <?php if ($this->_tpl_vars['editorData']['interests']): ?>
                                        <?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['interests'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                                    <?php else: ?>
                                        <em>Tidak ada data minat</em>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>

                                                <div class="editor-contacts">
                            <ul>
                                <?php if ($this->_tpl_vars['editorData']['email']): ?><li><strong>Email:</strong> <?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['email'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li><?php endif; ?>
                                <?php if ($this->_tpl_vars['editorData']['phone']): ?><li><strong>Phone:</strong> <?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['phone'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li><?php endif; ?>
                                <?php if ($this->_tpl_vars['editorData']['fax']): ?><li><strong>Fax:</strong> <?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['fax'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li><?php endif; ?>
                                <?php if ($this->_tpl_vars['editorData']['url']): ?><li><strong>Website:</strong> <a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li><?php endif; ?>
                                <?php if ($this->_tpl_vars['editorData']['orcid']): ?><li><strong>ORCID:</strong> <a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['orcid'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['orcid'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li><?php endif; ?>
                            </ul>
                        </div>

                                                <div class="editor-addresses">
                            <?php if ($this->_tpl_vars['editorData']['mailingAddress']): ?>
                                <div>
                                    <strong>Mailing Address:</strong>
                                    <p><?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['mailingAddress'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
                                </div>
                            <?php endif; ?>
                            <?php if ($this->_tpl_vars['editorData']['billingAddress']): ?>
                                <div>
                                    <strong>Billing Address:</strong>
                                    <p><?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['billingAddress'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
                                </div>
                            <?php endif; ?>
                        </div>

                                                <div class="editor-privilege">
                            <p><strong>Section Privileges:</strong></p>
                            <ul>
                                <li>Can Edit: <?php if ($this->_tpl_vars['editorData']['canEdit']): ?> Yes <?php else: ?> No <?php endif; ?></li>
                                <li>Can Review: <?php if ($this->_tpl_vars['editorData']['canReview']): ?> Yes <?php else: ?> No <?php endif; ?></li>
                            </ul>
                        </div>

                                                <div class="editor-bio-signature">
                            <?php if ($this->_tpl_vars['editorData']['biography']): ?>
                                <div class="editor-bio">
                                    <strong>Biography:</strong>
                                    <div><?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['biography'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</div>
                                </div>
                            <?php endif; ?>
                            <?php if ($this->_tpl_vars['editorData']['signature']): ?>
                                <div class="editor-signature">
                                    <strong>Signature:</strong>
                                    <div><?php echo ((is_array($_tmp=$this->_tpl_vars['editorData']['signature'])) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</div>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                    <hr>                 <?php endforeach; endif; unset($_from); ?>
                </div>
            <?php else: ?>
                <p><em><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.none"), $this);?>
</em></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="separator"></div>

        <div id="sectionPolicy" class="block">
        <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.focusAndScope"), $this);?>
 / <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.sectionPolicies"), $this);?>
</h3>
        <?php if ($this->_tpl_vars['section']->getLocalizedPolicy()): ?>
            <div class="section-content">
                <?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedPolicy())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

            </div>
        <?php else: ?>
            <p><em><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.none"), $this);?>
</em></p>
        <?php endif; ?>
    </div>

    <div class="separator"></div>
    
        <div id="identityInfo" class="block">
        <h3>Identitas Jurnal & Rubrik</h3>
        <div class="identity-content">
            <ul>
                <li><strong>Nama Jurnal:</strong> <?php echo ((is_array($_tmp=$this->_tpl_vars['journalTitle'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
                <li><strong>Abbreviation Jurnal:</strong> <?php echo ((is_array($_tmp=$this->_tpl_vars['journalInitials'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
                <li><strong>Print ISSN:</strong> <?php if ($this->_tpl_vars['printIssn']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['printIssn'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><em>Tidak ada</em><?php endif; ?></li>
                <li><strong>Online ISSN:</strong> <?php if ($this->_tpl_vars['onlineIssn']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['onlineIssn'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?><em>Tidak ada</em><?php endif; ?></li>
                <br>
                <li><strong>Nama Section:</strong> <?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
                <li><strong>Abbreviation Section:</strong> <?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedAbbrev())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
            </ul>
        </div>
    </div>
    
    <div class="separator"></div>

        <?php if ($this->_tpl_vars['authorGuidelines'] || $this->_tpl_vars['submissionChecklist']): ?>
        <div id="sectionSubmission" class="block">
            <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.submissions"), $this);?>
</h3>
            <div class="section-content">
                <?php if ($this->_tpl_vars['authorGuidelines']): ?>
                    <h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.authorGuidelines"), $this);?>
</h4>
                    <div class="guidelines">
                        <?php echo ((is_array($_tmp=$this->_tpl_vars['authorGuidelines'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

                    </div>
                <?php endif; ?>

                <?php if ($this->_tpl_vars['submissionChecklist']): ?>
                    <h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.submissionPreparationChecklist"), $this);?>
</h4>
                    <ul class="checklist">
                    <?php $_from = $this->_tpl_vars['submissionChecklist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['checklistItem']):
?>
                        <li><?php echo ((is_array($_tmp=$this->_tpl_vars['checklistItem']['content'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</li>
                    <?php endforeach; endif; unset($_from); ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <div class="separator"></div>
    <?php endif; ?>

        <?php if ($this->_tpl_vars['copyrightNotice'] || $this->_tpl_vars['privacyStatement']): ?>
        <div id="sectionPolicies" class="block">
            <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.policies"), $this);?>
</h3>
            <div class="section-content">
                <?php if ($this->_tpl_vars['copyrightNotice']): ?>
                    <h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.copyrightNotice"), $this);?>
</h4>
                    <div class="policy-item">
                        <?php echo ((is_array($_tmp=$this->_tpl_vars['copyrightNotice'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

                    </div>
                <?php endif; ?>

                <?php if ($this->_tpl_vars['privacyStatement']): ?>
                    <h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.privacyStatement"), $this);?>
</h4>
                    <div class="policy-item">
                        <?php echo ((is_array($_tmp=$this->_tpl_vars['privacyStatement'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>