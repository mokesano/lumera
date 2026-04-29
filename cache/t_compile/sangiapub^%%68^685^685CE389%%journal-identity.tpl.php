<?php /* Smarty version 2.6.26, created on 2026-04-04 05:50:37
         compiled from common/journal-identity.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'common/journal-identity.tpl', 5, false),array('modifier', 'strip_tags', 'common/journal-identity.tpl', 13, false),array('function', 'url', 'common/journal-identity.tpl', 11, false),)), $this); ?>
<div class="sangia-header bolmHa"><!-- editing -->

<?php if ($this->_tpl_vars['currentJournal']): ?>
<div class="journal-header" style="transform: translateZ(0px);" role="banner">
    <section <?php if ($this->_tpl_vars['displayPageHeaderTitle'] && is_array ( $this->_tpl_vars['displayPageHeaderTitle'] )): ?>class="lazyload sc-1oj9st5-1 kSjVSRM" style="background-image: url('<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayPageHeaderTitle']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
');"<?php else: ?>class="lazyload sc-1oj9st5-1 fQjVRM" style="background-image: rgb(85, 187, 221);"<?php endif; ?>>
        <div <?php if ($this->_tpl_vars['displayPageHeaderTitle'] && is_array ( $this->_tpl_vars['displayPageHeaderTitle'] )): ?>class="sc-thb58v-10 spg-1oj9st5"<?php else: ?>class="sc-thb58v-10 xEmXg"<?php endif; ?>>
            <div class="sc-thb58v-0 iLjulU text-s">
                <div class="sc-thb58v-2 ixNkIC">
                    <div class="sc-thb58v-3 fkokXa">
                        <h1 class="sc-thb58v-4 kGdUpp js-title-text u-text-light u-h2" style="color: rgb(0, 0, 0);">
                            <a class="js-title-link anchor-has-background-color anchor-has-inherit-color" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => ($this->_tpl_vars['currentJournal'])), $this);?>
" id="journal-title">
                                <span class="anchor-text">
                                <?php if ($this->_tpl_vars['currentJournal']->getLocalizedTitle()): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                                <?php elseif ($this->_tpl_vars['displayPageHeaderTitle']): ?>
                                    <?php echo $this->_tpl_vars['displayPageHeaderTitle']; ?>

                                <?php elseif ($this->_tpl_vars['siteTitle']): ?>
                                    <?php echo $this->_tpl_vars['siteTitle']; ?>

                                <?php else: ?>
                                    <?php echo $this->_tpl_vars['applicationName']; ?>

                                <?php endif; ?>    
                                </span>
                            </a>
                        </h1>
                        <div class="open-statement sc-thb58v-5 js-open-statement" style="color: rgb(0, 0, 0);">
                            <div class="open-statement-item u-display-inline-block">
                                <?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN): ?>
                                <a class="js-open-access-link u-clr-blue" id="openStatement-supports" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'openAccessPolicy'), $this);?>
">
                                    <span class="js-open-statement-text publishing-type open-statement-text" style="color: rgb(0, 0, 0);">
                                        <span class="u-text-italic open-access">Open access</span>
                                    </span>
                                </a>
                                <?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?>
                                <a class="js-open-access-link u-clr-blue" id="openStatement-supports" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialPolicies','anchor' => 'delayedOpenAccessPolicy'), $this);?>
">
                                    <span class="js-open-statement-text publishing-type open-statement-text" style="color: rgb(0, 0, 0);">
                                            Supports <span class="u-text-italic open-access">open access</span>
                                    </span>
                                </a>
                                <?php endif; ?>                                    
                            </div>
                        </div>
                    </div>
                    <div class="sc-thb58v-7 keEZHq">
                        <!-- Mendapatkan ISSN cetak dan online dari Smarty -->
                        <input type="hidden" id="printIssn" value="<?php echo $this->_tpl_vars['currentJournal']->getSetting('printIssn'); ?>
">
                        <input type="hidden" id="eIssn" value="<?php echo $this->_tpl_vars['currentJournal']->getSetting('onlineIssn'); ?>
">
                        <?php if ($this->_tpl_vars['alternatePageHeader']): ?>
                        <div class="tooltip__Wrapper-sc-1lc2ea0-0 sc-thb58v-8 cxpGrv iiSeIx js-sinta-score metric">
                            <button class="button-link button-link button-link-secondary u-text-left" type="button" aria-label="SintaScore: Sinta Impact Value" aria-expanded="false" aria-haspopup="false">
                                <span class="button-link-text">
                                    <span style="color: rgb(0, 0, 0);" class="text-l u-display-block"><?php echo $this->_tpl_vars['alternatePageHeader']; ?>
</span>
                                    <span style="color: rgb(0, 0, 0);" class="text-xs __info" aria-title="Sinta: Science and Technology Index by Kemdikbud & Ristek Indonesia">SintaScore</span>
                                </span>
                            </button>
                            <div id="popover-content-metric-popover-DaysPerReview" class="popover-content popover-align-right heading_inline u-js-hide" style="width: 350px; opacity: 1; transform: translateY(0px); transition: opacity 0.25s, transform 0.25s;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">SintaScore is an impact indicator sourced from SINTA (Science and Technology Index), a national research evaluation platform managed by the Ministry of Higher Education, Sains, and Technology of the Republic of Indonesia. While SINTA aggregates data on Indonesian journals, the exact formula behind SintaScore remains a well-kept secret — known only to its creators and perhaps, to God.</div></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="tooltip__Wrapper-sc-1lc2ea0-0 sc-thb58v-8 cxpGrv iiSeIx sc-thb58v-9 jrMqZ js-sinta-grade metric u-js-hide">
                            <button class="button-link button-link button-link-secondary u-text-left" type="button" aria-label="National Grade Accredited" aria-expanded="false" aria-haspopup="false">
                                <span class="button-link-text">
                                    <span style="color: rgb(0, 0, 0);" class="text-l u-display-block">Grade</span>
                                    <span style="color: rgb(0, 0, 0);" class="text-xs __info" aria-title="Score from Sinta: Science and Technology Index by Kemdikbud & Ristek Indonesia">SintaGrade</span>
                                </span>
                            </button>
                            <div id="popover-content-metric-popover-DaysPerReview" class="popover-content popover-align-right heading_inline u-js-hide" style="width: 350px; opacity: 1; transform: translateY(0px); transition: opacity 0.25s, transform 0.25s;" role="region"><div class="popover-content-inner popover-close-button-hidden"><div class="popover-children">The number Grade is National Level Accreditation Rating by ARJUNA: Akreditasi Jurnal National. Accreditation ranking results are displayed by SINTA: Science and Technology Index by Kemdikti Saintek, Republic of Indonesia as the only journal ranking agency in Indonesia.</div></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sc-thb58v-1 iChZEk">
                    <a class="anchor js-cover-image-link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => ($this->_tpl_vars['currentJournal'])), $this);?>
">
                        <?php $this->assign('displayHomepageImage', $this->_tpl_vars['currentJournal']->getLocalizedSetting('homepageImage')); ?>
                        <?php $this->assign('displayJournalThumbnail', $this->_tpl_vars['currentJournal']->getLocalizedSetting('journalThumbnail')); ?>
                        <?php if ($this->_tpl_vars['displayHomepageImage'] && is_array ( $this->_tpl_vars['displayHomepageImage'] )): ?>
                        <div class="anchor-text">
                            <picture>
                                <source srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
?as=webp" type="image/webp">
                                <img loading="lazy" class="lazyload cover-image-large cover-image js-cover-image" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" alt="Go to journal home page - <?php if ($this->_tpl_vars['currentJournal']->getLocalizedTitle()): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php elseif ($this->_tpl_vars['displayPageHeaderTitle']): ?><?php echo $this->_tpl_vars['displayPageHeaderTitle']; ?>
<?php elseif ($this->_tpl_vars['siteTitle']): ?><?php echo $this->_tpl_vars['siteTitle']; ?>
<?php else: ?><?php echo $this->_tpl_vars['applicationName']; ?>
<?php endif; ?>" style="max-height: 11rem;" />
                            </picture>
                        </div>
                        <?php elseif ($this->_tpl_vars['displayJournalThumbnail'] && is_array ( $this->_tpl_vars['displayJournalThumbnail'] )): ?>
                        <div class="anchor-text">
                            <picture>
                                <source srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayJournalThumbnail']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
?as=webp" type="image/webp">
                                <img loading="lazy" class="lazyload cover-image-large cover-image js-cover-image" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayJournalThumbnail']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" alt="Go to journal home page - <?php if ($this->_tpl_vars['currentJournal']->getLocalizedTitle()): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php elseif ($this->_tpl_vars['displayPageHeaderTitle']): ?><?php echo $this->_tpl_vars['displayPageHeaderTitle']; ?>
<?php elseif ($this->_tpl_vars['siteTitle']): ?><?php echo $this->_tpl_vars['siteTitle']; ?>
<?php else: ?><?php echo $this->_tpl_vars['applicationName']; ?>
<?php endif; ?>" style="max-height: 11rem;" />
                            </picture>
                        </div>
                        <?php else: ?>                            
                        <div class="fallback-cover-large fallback-cover u-bg-grey7 js-fallback-cover">
                            <div class="fallback-cover-content">
                                <p class="text-m u-clr-white js-fallback-cover-text u-font-sans">
                                    <?php if ($this->_tpl_vars['currentJournal']->getLocalizedTitle()): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                                    <?php elseif ($this->_tpl_vars['displayPageHeaderTitle']): ?>
                                        <?php echo $this->_tpl_vars['displayPageHeaderTitle']; ?>

                                    <?php elseif ($this->_tpl_vars['siteTitle']): ?>
                                        <?php echo $this->_tpl_vars['siteTitle']; ?>

                                    <?php else: ?>
                                        <?php echo $this->_tpl_vars['applicationName']; ?>

                                    <?php endif; ?>                            
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
</div><!-- editing -->
<?php endif; ?>