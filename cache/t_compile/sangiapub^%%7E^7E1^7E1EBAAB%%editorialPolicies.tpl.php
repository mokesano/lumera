<?php /* Smarty version 2.6.26, created on 2026-04-04 14:42:01
         compiled from about/editorialPolicies.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'about/editorialPolicies.tpl', 17, false),array('function', 'translate', 'about/editorialPolicies.tpl', 17, false),array('function', 'icon', 'about/editorialPolicies.tpl', 62, false),array('modifier', 'replace', 'about/editorialPolicies.tpl', 31, false),array('modifier', 'escape', 'about/editorialPolicies.tpl', 31, false),array('modifier', 'nl2br', 'about/editorialPolicies.tpl', 42, false),array('modifier', 'string_format', 'about/editorialPolicies.tpl', 83, false),array('modifier', 'explode', 'about/editorialPolicies.tpl', 115, false),array('modifier', 'trim', 'about/editorialPolicies.tpl', 117, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "about.aboutTheJournal"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-gfa.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<ul class="c-nav-menu">
	<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('focusScopeDesc') != ''): ?><li id="linkFocusScopeDesc" class="c-nav__link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialPolicies','anchor' => 'focusAndScope'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.focusAndScope"), $this);?>
</a></li><?php endif; ?>
	<li id="linkEditorialPolicies" class="c-nav__link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialPolicies','anchor' => 'sectionPolicies'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.sectionPolicies"), $this);?>
</a></li>
	<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('reviewPolicy') != ''): ?><li id="linkReviewPolicy" class="c-nav__link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialPolicies','anchor' => 'peerReviewProcess'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.peerReviewProcess"), $this);?>
</a></li><?php endif; ?>
	<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('pubFreqPolicy') != ''): ?><li id="linkPubFreqPolicy" class="c-nav__link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialPolicies','anchor' => 'publicationFrequency'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.publicationFrequency"), $this);?>
</a></li><?php endif; ?>
	<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN && $this->_tpl_vars['currentJournal']->getLocalizedSetting('openAccessPolicy') != ''): ?><li id="linkOpenAccessPolicy" class="c-nav__link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialPolicies','anchor' => 'openAccessPolicy'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.openAccessPolicy"), $this);?>
</a></li><?php endif; ?>
	<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION): ?>
		<?php if ($this->_tpl_vars['currentJournal']->getSetting('enableAuthorSelfArchive')): ?><li id="linkenabledAuthorSelfArchive" class="c-nav__link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialPolicies','anchor' => 'authorSelfArchivePolicy'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.authorSelfArchive"), $this);?>
</a></li><?php endif; ?>
		<?php if ($this->_tpl_vars['currentJournal']->getSetting('enableDelayedOpenAccess')): ?><li id="linkenabledDelayedOpenAccess" class="c-nav__link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialPolicies','anchor' => 'delayedOpenAccessPolicy'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.delayedOpenAccess"), $this);?>
</a></li><?php endif; ?>
		<?php if ($this->_tpl_vars['paymentConfigured'] && $this->_tpl_vars['currentJournal']->getSetting('journalPaymentsEnabled') && $this->_tpl_vars['currentJournal']->getSetting('acceptSubscriptionPayments') && $this->_tpl_vars['currentJournal']->getSetting('purchaseIssueFeeEnabled') && $this->_tpl_vars['currentJournal']->getSetting('purchaseIssueFee') > 0): ?><li id="linkpurchaseIssue" class="c-nav__link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialPolicies','anchor' => 'purchaseIssue'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.purchaseIssue"), $this);?>
</a></li><?php endif; ?>
		<?php if ($this->_tpl_vars['paymentConfigured'] && $this->_tpl_vars['currentJournal']->getSetting('journalPaymentsEnabled') && $this->_tpl_vars['currentJournal']->getSetting('acceptSubscriptionPayments') && $this->_tpl_vars['currentJournal']->getSetting('purchaseArticleFeeEnabled') && $this->_tpl_vars['currentJournal']->getSetting('purchaseArticleFee') > 0): ?><li id="linkpurchaseArticle" class="c-nav__link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialPolicies','anchor' => 'purchaseArticle'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.purchaseArticle"), $this);?>
</a></li><?php endif; ?>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['currentJournal']->getSetting('enableLockss') && $this->_tpl_vars['currentJournal']->getLocalizedSetting('lockssLicense') != ''): ?><li id="linkLockssLicense" class="c-nav__link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialPolicies','anchor' => 'archiving'), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.archiving"), $this);?>
</a></li><?php endif; ?>
	<?php $_from = $this->_tpl_vars['currentJournal']->getLocalizedSetting('customAboutItems'); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['customAboutItem']):
?>
		<?php if (! empty ( $this->_tpl_vars['customAboutItem']['title'] )): ?>
			<li id="link<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['customAboutItem']['title'])) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', "") : smarty_modifier_replace($_tmp, ' ', "")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="c-nav__link"><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialPolicies','anchor' => ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['customAboutItem']['title'])) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', "") : smarty_modifier_replace($_tmp, ' ', "")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
" data-track="click" data-track-label="link" data-test="explore-nav-item"><?php echo ((is_array($_tmp=$this->_tpl_vars['customAboutItem']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li>
		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
</ul>

<section class="content u-mt-48">
	
<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('focusScopeDesc') != ''): ?>
<div id="focusAndScope" class="block">
    <header class="c-anchored-heading"><h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.focusAndScope"), $this);?>
</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
<p><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('focusScopeDesc'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>

<div class="separator">&nbsp;</div>
</div>
<?php endif; ?>

<div id="sectionPolicies" class="block">
    <header class="c-anchored-heading"><h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.sectionPolicies"), $this);?>
</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
<?php $_from = $this->_tpl_vars['sections']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['section']):
?><?php if (! $this->_tpl_vars['section']->getHideAbout()): ?>
<section id="<?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedTitle())) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', "-") : smarty_modifier_replace($_tmp, ' ', "-")); ?>
" class="section-area clearfix" aria-labelledby="<?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedTitle())) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', "") : smarty_modifier_replace($_tmp, ' ', "")); ?>
">
    <div class="section-describe">
    	<h4 class="section-title">
    	    <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'section','op' => $this->_tpl_vars['section']->getSectionUrlTitle()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
    	</h4>
    	<?php if (strlen ( $this->_tpl_vars['section']->getLocalizedPolicy() ) > 0): ?>
    	<p class="section-describes"><?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedPolicy())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
    	<?php endif; ?>
    	<table width="60%">
    		<tr>
    			<td width="33%"><?php if (! $this->_tpl_vars['section']->getEditorRestricted()): ?><?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'checked'), $this);?>
<?php else: ?><?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'unchecked'), $this);?>
<?php endif; ?> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.sections.open"), $this);?>
</td>
    			<td width="33%"><?php if ($this->_tpl_vars['section']->getMetaIndexed()): ?><?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'checked'), $this);?>
<?php else: ?><?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'unchecked'), $this);?>
<?php endif; ?> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.sections.indexed"), $this);?>
</td>
    			<td width="34%"><?php if ($this->_tpl_vars['section']->getMetaReviewed()): ?><?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'checked'), $this);?>
<?php else: ?><?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'unchecked'), $this);?>
<?php endif; ?> <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.sections.reviewed"), $this);?>
</td>
    		</tr>
    	</table>
	</div>
	<?php $this->assign('hasEditors', 0); ?>
	<?php $_from = $this->_tpl_vars['sectionEditorEntriesBySection']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['sectionEditorEntries']):
?>
	<?php if ($this->_tpl_vars['key'] == $this->_tpl_vars['section']->getId()): ?>
	<div class="editors-section">
	<?php if (0 == $this->_tpl_vars['hasEditors']++): ?><?php if ($this->_tpl_vars['hasEditors']): ?>
	<h2 class="section-title u-h2 u-mt-32"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.sectionEditor"), $this);?>
 on <span class="section-name italic"><?php echo $this->_tpl_vars['section']->getLocalizedTitle(); ?>
</span></h2>
	<?php else: ?>
	<h2 class="section-title u-h2 u-mt-32"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.sectionEditors"), $this);?>
 on <span class="section-name italic"><?php echo $this->_tpl_vars['section']->getLocalizedTitle(); ?>
</span></h2>
	<?php endif; ?><?php endif; ?>
	<ul class="app-editor-row">
		<?php $_from = $this->_tpl_vars['sectionEditorEntries']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['sectionEditorEntry']):
?>
		<?php $this->assign('sectionEditor', $this->_tpl_vars['sectionEditorEntry']['user']); ?>
    	<?php $this->assign('profileImage', $this->_tpl_vars['sectionEditor']->getSetting('profileImage')); ?>
    	<?php $this->assign('sectionEditorAffiliation', $this->_tpl_vars['sectionEditorEntry']['affiliationString']); ?>
    	<?php $this->assign('sectionEditorCountry', $this->_tpl_vars['sectionEditorEntry']['countryString']); ?>
		<li id="SE-<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
" class="app-editor-row__item editor-member">
		    <div class="sangia-editor--profile member editor">
    		    <div class="section-editor-avatar cms-person">
			        <?php if ($this->_tpl_vars['profileImage']): ?>
			        <picture>
			            <source srcset="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
?as=webp" type="image/webp">
    			        <img class="lazyload editor sangia-author" loading="lazy" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
" width="100" height="auto">
	    			</picture>
	    			<?php else: ?>
			        <picture>
			            <source <?php if ($this->_tpl_vars['sectionEditor']->getGender() == 'M'): ?>srcset="//assets.sangia.org/static/images/contactPersonM.png?as=webp"<?php elseif ($this->_tpl_vars['sectionEditor']->getGender() == 'F'): ?>srcset="//assets.sangia.org/static/images/contactPersonF.png?as=webp"<?php elseif ($this->_tpl_vars['sectionEditor']->getGender() == 'O'): ?>srcset="//scholar.google.co.id/citations/images/avatar_scholar_128.png?as=webp"<?php elseif ($this->_tpl_vars['sectionEditor']->getGender() == ""): ?>srcset="//loop.frontiersin.org/images/profile/479531/203?as=webp"<?php endif; ?> type="image/webp">
    			        <img class="lazyload editor sangia-author" loading="lazy" id="editor-<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" <?php if ($this->_tpl_vars['sectionEditor']->getGender() == 'M'): ?>src="//assets.sangia.org/static/images/contactPersonM.png"<?php elseif ($this->_tpl_vars['sectionEditor']->getGender() == 'F'): ?>src="//assets.sangia.org/static/images/contactPersonF.png"<?php elseif ($this->_tpl_vars['sectionEditor']->getGender() == 'O'): ?>src="//scholar.google.co.id/citations/images/avatar_scholar_128.png"<?php elseif ($this->_tpl_vars['sectionEditor']->getGender() == ""): ?>src="//loop.frontiersin.org/images/profile/479531/203"<?php endif; ?> width="100" height="auto">
	    			</picture>	    			
			        <?php endif; ?>
    	        </div>
			 </div>
			 <div class="sangia-editor--link">
			     <div class="section-editor-name u-font-sans">
			         <span class="sangia-editor--name">
			             <span class="fullname u-hide"><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
			             <?php if ($this->_tpl_vars['sectionEditor']->getData('salutation') !== "Ph.D" && $this->_tpl_vars['sectionEditor']->getData('salutation') !== 'DEA'): ?>
			             <span class="text degree"><?php echo $this->_tpl_vars['sectionEditor']->getData('salutation'); ?>
</span><?php endif; ?>
			             <?php if ($this->_tpl_vars['sectionEditor']->getFirstName() !== $this->_tpl_vars['sectionEditor']->getLastName()): ?><span class="text given-name"><?php echo $this->_tpl_vars['sectionEditor']->getFirstName(); ?>
</span><?php endif; ?>
			             <?php if (((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?>
			             <span class="text middle-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
			             <span class="text surname"><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
			             <?php if ($this->_tpl_vars['sectionEditor']->getData('salutation') == "Ph.D" && $this->_tpl_vars['sectionEditor']->getData('salutation') == 'DEA'): ?>
			             <span class="text last-degree"><?php echo $this->_tpl_vars['sectionEditor']->getData('salutation'); ?>
</span><?php endif; ?>
			         </span>
			     </div>

                <?php if ($this->_tpl_vars['sectionEditorAffiliation']): ?>
                    <?php $this->assign('affiliations', ((is_array($_tmp=$this->_tpl_vars['sectionEditorAffiliation'])) ? $this->_run_mod_handler('explode', true, $_tmp, "\n") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "\n"))); ?>
                    <?php $_from = $this->_tpl_vars['affiliations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['affiliationLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['affiliationLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['index'] => $this->_tpl_vars['affiliation']):
        $this->_foreach['affiliationLoop']['iteration']++;
?>
                    <?php if (((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp)) != ''): ?>
                    <div class="sangia-editor--affiliation description">
                        <div class="sc-1mur6on-9 bLswwL">
                            <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj">
                                <svg class="sc-1mur6on-14 bPLcdE" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path>
                                </svg>
                            </div>
                            <span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="affiliation" itemtype="http://schema.org/Affiliation"><?php echo ((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['sectionEditorCountry'] && ($this->_foreach['affiliationLoop']['iteration'] == $this->_foreach['affiliationLoop']['total'])): ?>, <?php echo $this->_tpl_vars['sectionEditorCountry']; ?>
<?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?>
                <?php endif; ?>
			     <?php if ($this->_tpl_vars['sectionEditor']->getLocalizedGossip()): ?>
			     <div class="sc-1mur6on-9 bLswwL description GooSSiP">
			         <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
			         </div>
			         <span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl"><?php echo $this->_tpl_vars['sectionEditor']->getLocalizedGossip(); ?>
</span>
			     </div>
			     <?php endif; ?>
			     <?php if ($this->_tpl_vars['sectionEditor']->getInterestString()): ?>
			     <div class="sc-1mur6on-9 bLswwL description">
			         <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg width="1em" height="1em" viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg" class="sc-1mur6on-14 bPLcdE"><path id="Shape" fill="currentColor" fill-rule="nonzero" d="M4.98913043,4.69565217 L6.75,4.69565217 L6.75,8.09706522 L5.86956522,7.45728261 L4.98913043,8.09706522 L4.98913043,4.69565217 Z M9.39130435,4.10869565 L9.39130435,5.57608696 L12.0326087,5.57608696 L12.0326087,16.1413043 L2.70586957,16.1413043 C2.02206522,16.1413043 1.4673913,15.5866304 1.4673913,14.9028261 L1.4673913,5.2826087 C1.8225,5.4675 2.21869565,5.57608696 2.64130435,5.57608696 L3.52173913,5.57608696 L3.52173913,10.9790217 L5.86956522,9.27097826 L8.2173913,10.9790217 L8.2173913,3.22826087 L3.52173913,3.22826087 L3.52173913,4.10869565 L2.64130435,4.10869565 C1.99271739,4.10869565 1.4673913,3.51586957 1.4673913,2.78804348 C1.4673913,2.06021739 1.99271739,1.4673913 2.64130435,1.4673913 L12.6195652,1.4673913 L12.6195652,0 L2.64130435,0 C1.18565217,0 0,1.25021739 0,2.78804348 L0,14.9028261 C0,16.3936957 1.215,17.6086957 2.70586957,17.6086957 L13.5,17.6086957 L13.5,4.10869565 L9.39130435,4.10869565 Z"></path></svg>
			         </div>
			         <span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl"><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getInterestString())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
			     </div>
			     <?php endif; ?>
			 </div>
		</li>
		<?php endforeach; endif; unset($_from); ?>
	</ul>
	</div>
	<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
	
</section>
<?php endif; ?><?php endforeach; endif; unset($_from); ?>

<div class="separator">&nbsp;</div>
</div>

<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('reviewPolicy') != ''): ?><div id="peerReviewProcess" class="block">
    <header class="c-anchored-heading"><h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.peerReviewProcess"), $this);?>
</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
<p><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('reviewPolicy'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>

<div class="separator">&nbsp;</div>
</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('pubFreqPolicy') != ''): ?>
<div id="publicationFrequency" class="block">
    <header class="c-anchored-heading"><h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.publicationFrequency"), $this);?>
</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
<p><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('pubFreqPolicy'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>

<div class="separator">&nbsp;</div>
</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN && $this->_tpl_vars['currentJournal']->getLocalizedSetting('openAccessPolicy') != ''): ?> 
<div id="openAccessPolicy" class="block">
    <header class="c-anchored-heading"><h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.openAccessPolicy"), $this);?>
</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
<p><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('openAccessPolicy'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>

<div class="separator">&nbsp;</div>
</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION && $this->_tpl_vars['currentJournal']->getSetting('enableAuthorSelfArchive')): ?> 
<div id="authorSelfArchivePolicy" class="block">
    <header class="c-anchored-heading"><h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.authorSelfArchive"), $this);?>
</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header> 
<p><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('authorSelfArchivePolicy'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>

<div class="separator">&nbsp;</div>
</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_SUBSCRIPTION && $this->_tpl_vars['currentJournal']->getSetting('enableDelayedOpenAccess')): ?>
<div id="delayedOpenAccessPolicy" class="block">
    <header class="c-anchored-heading"><h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.delayedOpenAccess"), $this);?>
</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header> 
<p><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.delayedOpenAccessDescription1"), $this);?>
 <?php echo $this->_tpl_vars['currentJournal']->getSetting('delayedOpenAccessDuration'); ?>
 <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.delayedOpenAccessDescription2"), $this);?>
</p>
<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('delayedOpenAccessPolicy') != ''): ?>
	<p><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('delayedOpenAccessPolicy'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
<?php endif; ?>

<div class="separator">&nbsp;</div>
</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['currentJournal']->getSetting('enableLockss') && $this->_tpl_vars['currentJournal']->getLocalizedSetting('lockssLicense') != ''): ?>
<div id="archiving" class="block">
    <header class="c-anchored-heading"><h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.archiving"), $this);?>
</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
    </header>
<p><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedSetting('lockssLicense'))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>

<div class="separator">&nbsp;</div>
</div>
<?php endif; ?>

<?php $_from = $this->_tpl_vars['currentJournal']->getLocalizedSetting('customAboutItems'); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['customAboutItems'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['customAboutItems']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['customAboutItem']):
        $this->_foreach['customAboutItems']['iteration']++;
?>
	<?php if (! empty ( $this->_tpl_vars['customAboutItem']['title'] )): ?>
		<div id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['customAboutItem']['title'])) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', "") : smarty_modifier_replace($_tmp, ' ', "")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="block">
		    <header class="c-anchored-heading"><h2><?php echo ((is_array($_tmp=$this->_tpl_vars['customAboutItem']['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h2><a class="c-anchored-heading__helper" href="#menu">Back to top</a>
		    </header>
		<p><?php echo ((is_array($_tmp=$this->_tpl_vars['customAboutItem']['content'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p>
		<?php if (! ($this->_foreach['customAboutItems']['iteration'] == $this->_foreach['customAboutItems']['total'])): ?><div class="separator">&nbsp;</div><?php endif; ?>
		</div>
	<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>

</section>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
