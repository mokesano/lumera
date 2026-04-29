<?php /* Smarty version 2.6.26, created on 2026-04-04 06:12:56
         compiled from about/editorialTeamBio.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'about/editorialTeamBio.tpl', 16, false),array('modifier', 'assign', 'about/editorialTeamBio.tpl', 17, false),array('modifier', 'strip_tags', 'about/editorialTeamBio.tpl', 29, false),array('modifier', 'string_format', 'about/editorialTeamBio.tpl', 77, false),array('modifier', 'explode', 'about/editorialTeamBio.tpl', 118, false),array('modifier', 'count', 'about/editorialTeamBio.tpl', 119, false),array('modifier', 'trim', 'about/editorialTeamBio.tpl', 121, false),array('modifier', 'mask_email', 'about/editorialTeamBio.tpl', 151, false),array('modifier', 'dotat_mail', 'about/editorialTeamBio.tpl', 151, false),array('modifier', 'strip_unsafe_html', 'about/editorialTeamBio.tpl', 256, false),array('modifier', 'nl2br', 'about/editorialTeamBio.tpl', 256, false),array('function', 'translate', 'about/editorialTeamBio.tpl', 17, false),array('function', 'url', 'about/editorialTeamBio.tpl', 23, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "about.editorialTeamBio"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-PROF27.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php $this->assign('pageTitleTranslated', ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))); ?>
<?php if (! $this->_tpl_vars['pageTitleTranslated']): ?><?php echo ((is_array($_tmp=$this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['pageTitle']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'pageTitleTranslated') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'pageTitleTranslated'));?>
<?php endif; ?>
<div class="live-area-wrapper">
    <div class="profile row null u-hide">
        <div class="columns medium-2">
            <div class="jUaFts">                
            	<div class="sc-uosj0-0 AcWHr">
            	    <a data-aa-name="Journal Cover" data-aa-region="header" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['currentJournal']->getPath()), $this);?>
" class="sc-uosj0-1 fAiWUX header-journal-cover">
            	        <?php $this->assign('displayHomepageImage', $this->_tpl_vars['currentJournal']->getLocalizedSetting('homepageImage')); ?>
            	        <?php $this->assign('displayJournalThumbnail', $this->_tpl_vars['currentJournal']->getLocalizedSetting('journalThumbnail')); ?>
            	        <?php if ($this->_tpl_vars['displayHomepageImage'] && is_array ( $this->_tpl_vars['displayHomepageImage'] )): ?>
            	        <picture sangiaHome>
            	            <source srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
?as=webp" type="image/webp" loading="lazy">
            	            <img src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayHomepageImage']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" width="75" height="auto" alt="Journal cover for <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" loading="lazy" class="lazyload" />
            	        </picture>
            	        <?php elseif ($this->_tpl_vars['displayJournalThumbnail'] && is_array ( $this->_tpl_vars['displayJournalThumbnail'] )): ?>
            	        <picture sangia>
            	            <source srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayJournalThumbnail']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
?as=webp" type="image/webp" loading="lazy">
            	            <img class="lazyload" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['displayJournalThumbnail']['uploadName'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'url')); ?>
" width="75" height="auto" alt="Journal cover for <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" loading="lazy" />
            	        </picture>
            	        <?php else: ?>
            	        <picture>
            	            <source srcset="//assets.sangia.org/img/img-default.jpg?as=webp" type="image/webp" loading="lazy">
            	            <img src="//assets.sangia.org/img/img-default.jpg" width="75" height="auto" alt="Journal cover for <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="lazyload" loading="lazy" />
            	        </picture>
            	        <?php endif; ?>
            	    </a>
            	    <?php if ($this->_tpl_vars['printIssn']): ?> <?php else: ?>
            		<p class="sc-1q3g1nv-0 sc-jsgros-0 eTETae ddyzPP">ISSN: <?php if ($this->_tpl_vars['currentJournal']->getSetting('onlineIssn')): ?><?php echo $this->_tpl_vars['currentJournal']->getSetting('onlineIssn'); ?>
<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('printIssn')): ?><?php echo $this->_tpl_vars['currentJournal']->getSetting('printIssn'); ?>
<?php else: ?>On process<?php endif; ?></p>
            		<?php endif; ?>
            	</div>
            </div>
        </div>
        <div class="columns medium-10 u-font-sans">
            <div class="jUaFts">
                <div class="cms-common cms-person">
                    <h1 class="journal-name"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h1>
                </div>
            </div>
            <div class="sc-orwwe2-6 dtzDFi">
                <a data-button-size="small" data-aa-name="Submit your paper" data-aa-region="sub-page-header" size="small" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => "editorial-team"), $this);?>
" class="sc-4ky1f6-0 sc-4ky1f6-1 hnoKGz hLnlQv sub-page-header-submit-your-paper">
                    <span>Editorial Team</span></a>                
                <a data-button-size="small" data-aa-name="Submit your paper" data-aa-region="sub-page-header" size="small" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','op' => 'submit'), $this);?>
" class="sc-4ky1f6-0 sc-4ky1f6-1 hnoKGz hLnlQv sub-page-header-submit-your-paper">
                    <span>Submit your Paper</span></a>
                <a data-button-size="small" data-aa-name="View articles" data-aa-region="sub-page-header" size="small" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'issue','op' => 'current'), $this);?>
" class="sc-4ky1f6-0 sc-4ky1f6-3 hnoKGz hcBixJ sub-page-header-view-articles">
                    <span>View Articles</span></a>
            </div>
            <div class="sc-1f27thb-0 cRFLRd">
                <a target="_self" rel="noopener noreferrer" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions','anchor' => 'authorGuidelines'), $this);?>
" data-aa-name="Guide for authors" data-aa-region="header" class="sc-kvjqii-0 fYuYRT header-guide-for-authors">
                    <span class="sc-kvjqii-1 sc-kvjqii-2 hjlxNa bDcWjJ"><svg width="10" height="10" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg></span>
                    <span>Guide for authors</span>
                </a>
                <a target="_self" rel="noopener noreferrer" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'information','op' => 'librarians'), $this);?>
" data-aa-name="Abstract and Indexing" data-aa-region="header" class="sc-kvjqii-0 fYuYRT header-guide-for-authors">
                    <span class="sc-kvjqii-1 sc-kvjqii-2 hjlxNa bDcWjJ"><svg width="10" height="10" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path fill="currentColor" fill-rule="nonzero" d="M.445 1.829H10.9L0 12.705 1.294 14 12.167 3.149v10.38H14V0H.445z"></path></svg></span>
                    <span>Abstract and Indexing</span>
                </a>    
            </div>
        </div>
    </div>
    <div class="u-row row">
		<div class="column small-12">
		    <h2 data-editor-id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%012d") : smarty_modifier_string_format($_tmp, "%012d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="editor-profile" itemprop="name" itemtype="http://schema.org/Person"><span class="fullname u-hide"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['user']->getData('salutation')): ?><span class="text degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('salutation'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['user']->getFirstName() !== $this->_tpl_vars['user']->getLastName()): ?><span class="text given-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFirstName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['user']->getMiddleName()): ?><span class="text middle-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span class="text surname"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['user']->getData('suffix')): ?><span class="text last-degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('suffix'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?></h2>
		</div>
	</div>
</div>

<section class="live-area-wrapper">
    <div class="profile row">
        <aside class="columns medium-2"></aside>
        <section class="column medium-10 cms-person">
            <h2 data-editor-id="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%012d") : smarty_modifier_string_format($_tmp, "%012d")); ?>
" class="editor-name" itemprop="name" itemtype="http://schema.org/Person"><span class="fullname u-hide"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['user']->getData('salutation')): ?><span class="text degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('salutation'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['user']->getFirstName() !== $this->_tpl_vars['user']->getLastName()): ?><span class="text given-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFirstName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['user']->getMiddleName()): ?><span class="text middle-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span class="text surname"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['user']->getData('suffix')): ?><span class="text last-degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('suffix'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?></h2>
        </section>
    </div>
    <div class="profile row">
		<section class="column medium-2">
			<?php $this->assign('profileImage', $this->_tpl_vars['user']->getSetting('profileImage')); ?>
			<div class="avatar bJniPh islvFm" size="150">
			    <span style="box-sizing: border-box; display: inline-block; overflow: hidden; width: 100%; height: 100%; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; position: relative; max-width: 150px;">
				    <span style="box-sizing: border-box; display: block; width: 100%; height: 100%; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; max-width: 150px;">
				        <img style="display: block; max-width: 100%; width: initial; height: initial; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; width:100%" alt="" aria-hidden="true" src="//assets.sangia.org/img/128x150-image.png" class="lazyload"/>
				    </span>
				    <picture>
				    <?php if ($this->_tpl_vars['profileImage']): ?>
				    <source srcset="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
?as=webp" type="image/webp">
					<img loading="lazy" editorid="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%012d") : smarty_modifier_string_format($_tmp, "%012d")); ?>
" height="auto" width="150" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
" style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; border: medium none; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%; object-fit: cover;" class="lazyload avatar editor sangia-author" />
					<?php else: ?>
					<source <?php if ($this->_tpl_vars['user']->getGender() == 'M'): ?>srcset="//assets.sangia.org/static/images/contactPersonM.png?as=webp"<?php elseif ($this->_tpl_vars['user']->getGender() == 'F'): ?>srcset="//assets.sangia.org/static/images/contactPersonF.png?as=webp"<?php elseif ($this->_tpl_vars['user']->getGender() == 'O'): ?>srcset="//scholar.google.co.id/citations/images/avatar_scholar_128.png?as=webp"<?php elseif ($this->_tpl_vars['user']->getGender() == ""): ?>srcset="//assets.sangia.org/static/images/default_203.jpg?as=webp"<?php endif; ?> type="image/webp">
					<img class="lazyload avatar editor sangia-author" loading="lazy" editorid="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%012d") : smarty_modifier_string_format($_tmp, "%012d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['user']->getGender() == 'M'): ?>src="//assets.sangia.org/static/images/contactPersonM.png"<?php elseif ($this->_tpl_vars['user']->getGender() == 'F'): ?>src="//assets.sangia.org/static/images/contactPersonF.png"<?php elseif ($this->_tpl_vars['user']->getGender() == 'O'): ?>src="//scholar.google.co.id/citations/images/avatar_scholar_128.png"<?php elseif ($this->_tpl_vars['user']->getGender() == ""): ?>src="//assets.sangia.org/static/images/default_203.jpg"<?php endif; ?> width="150" height="auto" style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; border: medium none; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%; object-fit: cover;" />
				    <?php endif; ?>
				    </picture>
                </span>
			</div>		
		</section>
		<section class="column medium-7 cms-person">
			<div class="overview description">
			    			    <?php if ($this->_tpl_vars['userMembership']): ?>
                <section class="paragraph" itemprop="creator" itemtype="http://schema.org/Person"><span class="editors" itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['userMembership'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                </section>
                <?php endif; ?>

				<?php if ($this->_tpl_vars['user']->getLocalizedAffiliation()): ?>
                <?php $this->assign('affiliations', ((is_array($_tmp=$this->_tpl_vars['user']->getLocalizedAffiliation())) ? $this->_run_mod_handler('explode', true, $_tmp, "\n") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "\n"))); ?>
                <?php $this->assign('affiliationCount', count($this->_tpl_vars['affiliations'])); ?>
                <?php $_from = $this->_tpl_vars['affiliations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['index'] => $this->_tpl_vars['affiliation']):
?>
                    <?php if (((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp)) != ''): ?>
    				<div class="affiliation bLswwL">
        				<div class="iggNhe dlPwne"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path></svg></div>
    				    <p class="paragraph" itemprop="affiliation" itemtype="http://schema.org/Affiliation"><?php echo ((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['index'] == $this->_tpl_vars['affiliationCount'] - 1 && $this->_tpl_vars['user']->getCountry()): ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['country'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></p>
    				</div>
    				<?php endif; ?>
                <?php endforeach; endif; unset($_from); ?>
                <?php endif; ?>
    
    			<?php if ($this->_tpl_vars['user']->getLocalizedGossip()): ?>
                <div class="sc-1mur6on-9 bLswwL GooSSiP u-js-hide">
                    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj">
                        <svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
                    </div>
            		<p class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="affiliation" itemtype="http://schema.org/Affiliation"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getLocalizedGossip())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
                </div>
                <?php endif; ?>
                				
				<?php if ($this->_tpl_vars['user']->getInterestString()): ?>
				<div class="sc-1mur6on-9 bLswwL">
				    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg width="1em" height="1em" viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg" class="sc-1mur6on-14 bPLcdE"><path id="Shape" fill="currentColor" fill-rule="nonzero" d="M4.98913043,4.69565217 L6.75,4.69565217 L6.75,8.09706522 L5.86956522,7.45728261 L4.98913043,8.09706522 L4.98913043,4.69565217 Z M9.39130435,4.10869565 L9.39130435,5.57608696 L12.0326087,5.57608696 L12.0326087,16.1413043 L2.70586957,16.1413043 C2.02206522,16.1413043 1.4673913,15.5866304 1.4673913,14.9028261 L1.4673913,5.2826087 C1.8225,5.4675 2.21869565,5.57608696 2.64130435,5.57608696 L3.52173913,5.57608696 L3.52173913,10.9790217 L5.86956522,9.27097826 L8.2173913,10.9790217 L8.2173913,3.22826087 L3.52173913,3.22826087 L3.52173913,4.10869565 L2.64130435,4.10869565 C1.99271739,4.10869565 1.4673913,3.51586957 1.4673913,2.78804348 C1.4673913,2.06021739 1.99271739,1.4673913 2.64130435,1.4673913 L12.6195652,1.4673913 L12.6195652,0 L2.64130435,0 C1.18565217,0 0,1.25021739 0,2.78804348 L0,14.9028261 C0,16.3936957 1.215,17.6086957 2.70586957,17.6086957 L13.5,17.6086957 L13.5,4.10869565 L9.39130435,4.10869565 Z"></path></svg>
				    </div>
				    <p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl" itemprop="interest" itemtype="http://schema.org/Interest"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getInterestString())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
				</div>
				<?php endif; ?>
    
                <?php if ($this->_tpl_vars['user']->getEmail()): ?>
                <div class="scwizdam-1mur6wiz-7 bLswwL">
                    <div class="iggNhe dlPwne sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width="1em" height="1em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg>
                    </div>
                    <p class="paragraph"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.email"), $this);?>
: <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getEmail())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('mask_email', true, $_tmp) : $this->_plugins['modifier']['mask_email'][0][0]->smartyMaskEmail($_tmp)))) ? $this->_run_mod_handler('dotat_mail', true, $_tmp) : $this->_plugins['modifier']['dotat_mail'][0][0]->smartyDotatEmail($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
                </div>
                <?php endif; ?>

                                
				<div class="editor-person eTETae">
					<?php if ($this->_tpl_vars['user']->getData('orcid')): ?>
					<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
						<img src="//assets.sangia.org/img/orcid_16x16.svg" width="16" height="16" alt="orcid" class="brand" />
						<span class="orcid"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.orcid"), $this);?>
 </span><a title="Go to Orcid profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
					</p><?php endif; ?>
					<?php if ($this->_tpl_vars['user']->getData('sintaId')): ?>
					<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
						<img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/ico/brand_sinta.png" width="16" height="16" alt="sinta" class="brand" referrerpolicy="strict-origin-when-cross-origin" />
						<span class="sinta">Sinta: </span><a title="Go to Sinta profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="https://sinta.kemdiktisaintek.go.id/authors/profile/<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('sintaId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="sintaid">Science and Technology Index</a><span class="u-ml-4 articleCount">Profile by Kemendikti Saintek Indonesia</span>
					</p><?php endif; ?>
					<?php if ($this->_tpl_vars['user']->getData('scopusId')): ?>
					<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
					    <img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/ico/scopus.ico" alt="Scopus" width="16" height="16" style="vertical-align: middle; margin-right: 3px;" class="brand" referrerpolicy="strict-origin-when-cross-origin" />
						<span class="scopus">Scopus</span> ID <a title="Go to Scopus profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="https://www.scopus.com/authid/detail.uri?authorId=<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('scopusId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="scopusid"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('scopusId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
					</p><?php endif; ?>
					<?php if ($this->_tpl_vars['user']->getData('dimensionId')): ?>
					<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
					    <img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/ico/dimension.ico" alt="Dimension" width="16" height="16" style="vertical-align: middle; margin-right: 3px;" class="brand" referrerpolicy="strict-origin-when-cross-origin" />
						<span class="dimension">Dimension</span> ID <a title="Go to Dimension profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="https://app.dimensions.ai/details/entities/publication/author/<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('dimensionId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="dimensionid"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('dimensionId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
					</p><?php endif; ?>
					<?php if ($this->_tpl_vars['user']->getData('researcherId')): ?>
					<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
					    <img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/ico/wos.svg" alt="Web of Science Researcher (Publons)" width="16" height="16" style="vertical-align: middle; margin-right: 3px;" class="brand" referrerpolicy="strict-origin-when-cross-origin" />
						<span class="researcher">Web of Science ResearcherID</span> <a title="Go to Web of Science ResearcherID profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="https://www.webofscience.com/wos/author/record/<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('researcherId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="researcherid"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('researcherId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
					</p><?php endif; ?>
				</div>
				
				<?php if ($this->_tpl_vars['user']->getData('mailingAddress')): ?>
				<div class="sc-1mur6on-15 fTcNcY anchor">
				    <a href="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getEmail())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="sc-kvjqii-0 fYuYRT sc-1mur6on-16 bneccj"><span class="sc-kvjqii-1 hjlxNa"><svg aria-hidden="true" focusable="false" viewBox="0 0 15 15" height="2em" width="2em"><path d="M.156 13.469L6.094 7.53.156 1.594 1.25.5l7.031 7.031-7.031 7.032z" fill="currentColor" fill-rule="nonzero"></path></svg></span>
				        <span class="anchor-text editor-name">Contact mail <span class="fullname u-hide"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['user']->getSalutation()): ?><span class="text degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getSalutation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span> <?php endif; ?><?php if ($this->_tpl_vars['user']->getFirstName() !== $this->_tpl_vars['user']->getLastName()): ?><span class="text given-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFirstName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span> <?php endif; ?><?php if (((is_array($_tmp=$this->_tpl_vars['user']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?><span class="text middle-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span> <?php endif; ?><span class="text surname"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['user']->getSuffix()): ?><span class="degree">, <?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('suffix'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?></span>
				    </a>
				</div>
				<?php endif; ?>
			</div>	    
		</section>
		
		<aside class="column medium-3">
			<section class="box">
				<section class="description">
				    <p class="headline-524909129">Available Contact<?php if (((is_array($_tmp=$this->_tpl_vars['user']->getData('phone'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?>s<?php endif; ?>
				        <span class="__info" title="Phone number and email addresses editor/reviewer in <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 are not displayed. See more about Privacy Statements."></span>
				    </p>
				    <p class=" "><span class="dlPwne"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width="1em" height=".7em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.email"), $this);?>
 Addressed</span>
					<?php if ($this->_tpl_vars['user']->getData('phone')): ?><br /><span class="dlPwne"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 1280.000000" preserveAspectRatio="xMidYMid meet" width="1em" height=".9em"><g transform="translate(0.000000,1280.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M6145 12794 c-216 -13 -391 -28 -530 -45 -995 -122 -1927 -467 -2760 -1022 -907 -604 -1648 -1433 -2146 -2402 -395 -769 -615 -1549 -690 -2450 -17 -193 -17 -757 0 -950 75 -901 295 -1681 690 -2450 610 -1188 1578 -2156 2766 -2766 769 -395 1549 -615 2450 -690 193 -17 757 -17 950 0 901 75 1681 295 2450 690 1187 610 2156 1579 2766 2766 395 769 615 1549 690 2450 17 193 17 757 0 950 -75 901 -295 1681 -690 2450 -610 1188 -1578 2156 -2766 2766 -753 387 -1531 610 -2390 684 -164 15 -666 27 -790 19z m739 -779 c1098 -94 2121 -499 3001 -1188 401 -314 804 -738 1106 -1162 598 -840 944 -1793 1030 -2840 16 -193 16 -657 0 -850 -114 -1385 -693 -2628 -1672 -3591 -960 -942 -2172 -1494 -3524 -1605 -193 -16 -657 -16 -850 0 -1352 111 -2561 661 -3523 1605 -979 960 -1561 2210 -1673 3591 -15 193 -15 657 0 850 112 1384 693 2629 1678 3596 872 856 1985 1403 3183 1563 416 55 832 66 1244 31z"></path><path d="M5060 10738 c-55 -15 -679 -379 -716 -418 -85 -87 -103 -206 -47 -315 67 -129 1153 -2002 1181 -2035 57 -68 178 -105 267 -81 46 12 662 365 715 409 71 59 108 190 78 277 -8 22 -276 495 -596 1050 -471 817 -591 1018 -628 1052 -68 64 -164 87 -254 61z"></path><path d="M3945 9869 c-444 -268 -654 -492 -755 -805 -51 -159 -63 -256 -62 -489 2 -581 148 -1249 437 -2005 660 -1719 1900 -3438 3021 -4187 344 -229 566 -316 844 -330 257 -12 511 63 877 260 113 61 151 86 147 97 -6 17 -1182 2058 -1197 2077 -7 10 -18 8 -47 -7 -131 -67 -310 -98 -455 -80 -282 37 -555 190 -841 475 -264 262 -461 538 -645 905 -404 805 -456 1555 -134 1933 33 39 144 132 166 139 8 3 -217 401 -590 1049 -332 574 -606 1047 -610 1051 -3 4 -73 -33 -156 -83z"></path><path d="M8220 5330 c-55 -7 -46 -3 -413 -214 -164 -94 -314 -186 -333 -204 -76 -74 -104 -192 -66 -287 32 -82 1166 -2037 1203 -2075 82 -84 203 -104 314 -51 86 41 614 347 655 380 56 45 90 120 90 201 0 37 -7 83 -15 103 -33 80 -1170 2035 -1203 2069 -61 64 -141 91 -232 78z"></path></g></svg> Phone Number</span><?php endif; ?></p>
				</section>
			</section>
		</aside>
		
	</div>
</section>

<?php if ($this->_tpl_vars['user']->getData('scopusId')): ?>
<div class="live-area">
    <div class="profile row graph">
        <!-- Elemen untuk grafik artikel -->
        <aside class="medium-12">
			<section class="graph_box">
				<section class="description" id="scopus-graph"></section>
			</section>
		</aside>
	</div>	
</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['user']->getLocalizedBiography() || $this->_tpl_vars['user']->getLocalizedSignature() || $this->_tpl_vars['user']->getData('mailingAddress') || $this->_tpl_vars['user']->getGoogleScholar() || $this->_tpl_vars['user']->getUrl()): ?>
<div class="live-area-wrapper">
    <div class="profile row">
        <aside class="column medium-2 null">
            <?php if ($this->_tpl_vars['user']->getLocalizedBiography() || $this->_tpl_vars['user']->getLocalizedSignature() || $this->_tpl_vars['user']->getData('mailingAddress')): ?>
    		<header class="anchored">
    		    <h3 class=" "><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.biography"), $this);?>
</h3>
    		</header>
    		<?php endif; ?>
    		<?php if ($this->_tpl_vars['user']->getLocalizedBiography() || $this->_tpl_vars['user']->getLocalizedSignature() || $this->_tpl_vars['user']->getData('mailingAddress')): ?>
    		<section class="ads">
        		<!-- Sangia_Publishing_ads -->
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="ca-pub-8416265824412721"
                     data-ad-slot="2864083864"
                     data-ad-format="auto"
                     data-full-width-responsive="true"></ins>
                <script>
                <?php echo '
                     (adsbygoogle = window.adsbygoogle || []).push({});
                '; ?>

                </script>
    		</section>
    		<?php endif; ?>
		</aside>
    	<div class="column medium-10">
    		<div class="person-detail" itemprop="description"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getLocalizedBiography())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
<a aria-label="Go to Editor's personal profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['user']->getUrl()): ?>href="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php elseif ($this->_tpl_vars['user']->getGoogleScholar()): ?>href="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getGoogleScholar())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php endif; ?> target="_blank"><?php if ($this->_tpl_vars['userMembership']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['userMembership'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> Personal Website</a>
    		</div>
    		<?php if ($this->_tpl_vars['user']->getLocalizedSignature()): ?>
    		<div class="Signature u-mt-16" itemprop="signature">
    		    <p><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getLocalizedSignature())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
    		</div>
    		<?php endif; ?>
    		<?php if ($this->_tpl_vars['user']->getData('mailingAddress')): ?>
    		<div class="Address u-mt-16" itemprop="address"><p><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getData('mailingAddress'))) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
    		</div>
    		<?php endif; ?>
    	</div>
    </div>
</div>    
<?php endif; ?>

<?php if ($this->_tpl_vars['user']->getData('scopusId')): ?>
<div class="live-area-wrapper">
    <div class="profile row">
        <div class="column medium-12">
            <!-- Elemen untuk daftar artikel -->
            <div class="articles-list" id="scopus-articles">
                <div data-test="title" class="anchored">
                    <h3 class="anchored"><span class="content-break">Article author in Scopus</span></h3>
                </div>
                <div class="scopus-article-detail" itemprop="scopus"></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-profile.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>