<?php /* Smarty version 2.6.26, created on 2026-04-04 05:47:11
         compiled from search/authorIndex.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'search/authorIndex.tpl', 18, false),array('function', 'translate', 'search/authorIndex.tpl', 18, false),array('function', 'page_info', 'search/authorIndex.tpl', 145, false),array('function', 'page_links', 'search/authorIndex.tpl', 149, false),array('modifier', 'escape', 'search/authorIndex.tpl', 18, false),array('modifier', 'String_substr', 'search/authorIndex.tpl', 25, false),array('modifier', 'lower', 'search/authorIndex.tpl', 27, false),array('modifier', 'string_format', 'search/authorIndex.tpl', 64, false),array('modifier', 'explode', 'search/authorIndex.tpl', 106, false),array('block', 'iterate', 'search/authorIndex.tpl', 23, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "search.authorIndex"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-parts/header-overview.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div class="c-jump u-mb-48 u-mt-32">
	<p class="describe u-font-sans">Click the alphabet to chose name authors</p>
	<span class="c-jump-navigation"><?php $_from = $this->_tpl_vars['alphaList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['letter']):
?><a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'authors','searchInitial' => $this->_tpl_vars['letter']), $this);?>
"><?php if ($this->_tpl_vars['letter'] == $this->_tpl_vars['searchInitial']): ?><strong><?php echo ((is_array($_tmp=$this->_tpl_vars['letter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</strong><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['letter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></a> <?php endforeach; endif; unset($_from); ?><a class="c-jump-navigation__link u-margin-bottom-xxs-at-md" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'authors'), $this);?>
"><?php if ($this->_tpl_vars['searchInitial'] == ''): ?><strong><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.all"), $this);?>
</strong><?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.all"), $this);?>
<?php endif; ?></a></span>
</div>

<div id="authors-index" class="Editors">
<div id="author" class="helping-header author-card">
<?php $this->_tag_stack[] = array('iterate', array('from' => 'authors','item' => 'author')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php $this->assign('lastFirstLetter', $this->_tpl_vars['firstLetter']); ?>
	<?php $this->assign('firstLetter', ((is_array($_tmp=$this->_tpl_vars['author']->getLastName())) ? $this->_run_mod_handler('String_substr', true, $_tmp, 0, 1) : PKPString::substr($_tmp, 0, 1))); ?>

	<?php if (((is_array($_tmp=$this->_tpl_vars['lastFirstLetter'])) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)) != ((is_array($_tmp=$this->_tpl_vars['firstLetter'])) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp))): ?>
		<?php if (! $this->_tpl_vars['firstTime']): ?>
			</div> 		<?php else: ?>
			<?php $this->assign('firstTime', 0); ?>
		<?php endif; ?>
		
				<div id="<?php echo ((is_array($_tmp=$this->_tpl_vars['firstLetter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="cosire-author--index">
			<header class="c-anchored-heading"><h3><?php echo ((is_array($_tmp=$this->_tpl_vars['firstLetter'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3><a class="c-anchored-heading__helper" href="#main">Back to top</a>
			</header>
		</div>
				<div class="CardWrapper">
	<?php endif; ?>
	
	    <?php $this->assign('authorId', $this->_tpl_vars['author']->getId()); ?>
    <?php $this->assign('isVerifiedAuthor', $this->_tpl_vars['author']->getData('isVerifiedAuthor')); ?>
    <?php $this->assign('userGender', $this->_tpl_vars['author']->getData('userGender')); ?>
    <?php $this->assign('hasProfileImage', $this->_tpl_vars['author']->getData('hasProfileImage')); ?>
    <?php $this->assign('profileImageUrl', $this->_tpl_vars['author']->getData('profileImageUrl')); ?>
    <?php $this->assign('userInterests', $this->_tpl_vars['author']->getData('userInterests')); ?>
    <?php $this->assign('userSalutation', $this->_tpl_vars['author']->getData('userSalutation')); ?>
    <?php $this->assign('userPhone', $this->_tpl_vars['author']->getData('userPhone')); ?>
    <?php $this->assign('userFax', $this->_tpl_vars['author']->getData('userFax')); ?>
        
	<?php $this->assign('authorMiddleName', $this->_tpl_vars['author']->getMiddleName()); ?>
	<?php $this->assign('authorFirstName', $this->_tpl_vars['author']->getFirstName()); ?>
	<?php $this->assign('authorLastName', $this->_tpl_vars['author']->getLastName()); ?>
	<?php $this->assign('authorAffiliation', $this->_tpl_vars['author']->getLocalizedAffiliation()); ?>
	<?php $this->assign('authorCountry', $this->_tpl_vars['author']->getCountry()); ?>
	<?php $this->assign('authorName', ($this->_tpl_vars['authorLastName']).", ".($this->_tpl_vars['authorFirstName'])); ?>
	<?php if ($this->_tpl_vars['authorMiddleName'] != ''): ?><?php $this->assign('authorName', ($this->_tpl_vars['authorName'])." ".($this->_tpl_vars['authorMiddleName'])); ?><?php endif; ?>
	<?php echo '<article data-author-id="'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['authorId'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")); ?><?php echo '" class="CardEditor">'; ?><?php if ($this->_tpl_vars['isVerifiedAuthor']): ?><?php echo '<span class="verified-stamp" style="color: white; padding: 4px 8px;text-align: center;">Verified</span>'; ?><?php endif; ?><?php echo '<a class="CardEditor__wrapper" href="'; ?><?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'authors','path' => 'view','firstName' => $this->_tpl_vars['authorFirstName'],'middleName' => $this->_tpl_vars['authorMiddleName'],'lastName' => $this->_tpl_vars['authorLastName'],'affiliation' => $this->_tpl_vars['authorAffiliation'],'country' => $this->_tpl_vars['authorCountry']), $this);?><?php echo '" target="_blank" data-event="cardAuthor-a-'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['authorId'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?><?php echo '"><div class="CardEditor__info"><figure class="CardEditor__mask'; ?><?php if ($this->_tpl_vars['userGender']): ?><?php echo ' data-gender="'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['userGender'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo '"'; ?><?php endif; ?><?php echo '"><figure class="Avatar Avatar--size-96">'; ?><?php echo ''; ?><?php if ($this->_tpl_vars['hasProfileImage']): ?><?php echo '<img class="lazyload cosire-author Avatar__img is-inside-mask" loading="lazy" alt="'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo '" src="'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['profileImageUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo '" width="150" height="auto">'; ?><?php elseif ($this->_tpl_vars['profileImage']): ?><?php echo ''; ?><?php $this->assign('profileImage', $this->_tpl_vars['user']->getSetting('profileImage')); ?><?php echo '<img class="lazyload cosire-author editor-image Avatar__img is-inside-mask" loading="lazy" height="'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['profileImage']['height'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo '" width="'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['profileImage']['width'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo '" alt="'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo '" src="'; ?><?php echo $this->_tpl_vars['sitePublicFilesDir']; ?><?php echo '/'; ?><?php echo $this->_tpl_vars['profileImage']['uploadName']; ?><?php echo '" />'; ?><?php else: ?><?php echo ''; ?><?php echo '<div class="cosire-author--profile member">'; ?><?php if ($this->_tpl_vars['userGender'] == 'M'): ?><?php echo '<img class="lazyload cosire-author Avatar__img is-inside-mask" loading="lazy" alt="'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo '" src="//assets.sangia.org/img/contactPersonM.png" width="150" height="auto">'; ?><?php elseif ($this->_tpl_vars['userGender'] == 'F'): ?><?php echo '<img class="lazyload cosire-author Avatar__img is-inside-mask" loading="lazy" alt="'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo '" src="//assets.sangia.org/img/contactPersonF.png" width="150" height="auto">'; ?><?php else: ?><?php echo '<img class="lazyload cosire-author Avatar__img is-inside-mask" loading="lazy" alt="'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['author']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo '" src="//assets.sangia.org/static/images/default_203.jpg" width="150" height="auto">'; ?><?php endif; ?><?php echo '</div>'; ?><?php endif; ?><?php echo '</figure></figure><div class="CardEditor__detail notranslate"><div class="CardEditor__name cosire-author--name u-fonts-sans">'; ?><?php if ($this->_tpl_vars['userSalutation'] || $this->_tpl_vars['userPrefix']): ?><?php echo '<div class="italic surname full">'; ?><?php if ($this->_tpl_vars['userSalutation']): ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['userSalutation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo ' '; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['userPrefix']): ?><?php echo ' '; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['userPrefix'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo ''; ?><?php endif; ?><?php echo '</div>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['authorFirstName'] !== $this->_tpl_vars['authorLastName']): ?><?php echo '<span class="text given-name">'; ?><?php echo $this->_tpl_vars['authorFirstName']; ?><?php echo '</span>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['authorMiddleName']): ?><?php echo '<span class="text middle-name">'; ?><?php echo $this->_tpl_vars['authorMiddleName']; ?><?php echo '</span>'; ?><?php endif; ?><?php echo '<span class="text surname">'; ?><?php echo $this->_tpl_vars['authorLastName']; ?><?php echo '</span></div>'; ?><?php if ($this->_tpl_vars['authorAffiliation']): ?><?php echo '<div class="CardEditor__affiliation__name u-font-sangia-sans" itemprop="affiliation" itemtype="http://schema.org/Affiliation">'; ?><?php $this->assign('affiliations', ((is_array($_tmp=$this->_tpl_vars['authorAffiliation'])) ? $this->_run_mod_handler('explode', true, $_tmp, "\n") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "\n"))); ?><?php echo ''; ?><?php $_from = $this->_tpl_vars['affiliations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['affiliationLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['affiliationLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['affiliation']):
        $this->_foreach['affiliationLoop']['iteration']++;
?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo '</div>'; ?><?php if (($this->_foreach['affiliationLoop']['iteration'] == $this->_foreach['affiliationLoop']['total']) && $this->_tpl_vars['authorCountry']): ?><?php echo '<div class="CardEditor__affiliation__location u-font-sangia-sans">'; ?><?php echo $this->_tpl_vars['author']->getCountryLocalized(); ?><?php echo '</div>'; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo '</div></div><div class="CardEditor__more u-font-sangia-sans"><section class="CardEditor__role">AuthorID: '; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['authorId'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")); ?><?php echo '</section>'; ?><?php if ($this->_tpl_vars['isEditor'] && ! empty ( $this->_tpl_vars['editorSections'] )): ?><?php echo '<section class="CardEditor__role">'; ?><?php $_from = $this->_tpl_vars['editorSections']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sectionLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sectionLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['section']):
        $this->_foreach['sectionLoop']['iteration']++;
?><?php echo ''; ?><?php echo $this->_tpl_vars['section']; ?><?php echo ''; ?><?php if (! ($this->_foreach['sectionLoop']['iteration'] == $this->_foreach['sectionLoop']['total'])): ?><?php echo ', '; ?><?php endif; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo '</section>'; ?><?php endif; ?><?php echo ''; ?><?php if (! empty ( $this->_tpl_vars['userInterests'] )): ?><?php echo '<section class="CardEditor__journal">'; ?><?php $_from = $this->_tpl_vars['userInterests']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['interestLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['interestLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['interest']):
        $this->_foreach['interestLoop']['iteration']++;
?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['interest'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?><?php echo ''; ?><?php if (! ($this->_foreach['interestLoop']['iteration'] == $this->_foreach['interestLoop']['total'])): ?><?php echo ', '; ?><?php endif; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo '</section>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['authorUrl'] || $this->_tpl_vars['userPhone'] || $this->_tpl_vars['authorOrcid'] || $this->_tpl_vars['userFax']): ?><?php echo ''; ?><?php endif; ?><?php echo '</div></a></article>'; ?>


<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<?php if ($this->_tpl_vars['authors']->getCount()): ?>
</div>
<?php endif; ?>

</div>

<?php if (! $this->_tpl_vars['authors']->wasEmpty()): ?>
<div class="colspan u-mb-0 u-mt-32" id="colspan">	    
    <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
        <div class="c-pagination">View <?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['authors']), $this);?>
</div>
    </section>
    <?php if ($this->_tpl_vars['authors']->getPageCount() > 1): ?>
    <section class="u-display-flex u-justify-content-center">
        <div class="c-pagination"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'authors','iterator' => $this->_tpl_vars['authors'],'name' => 'authors','searchInitial' => $this->_tpl_vars['searchInitial']), $this);?>

        </div>
    </section>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="container cleared container-type-title" data-container-type="title">
    <div class="border-top-1 border-gray-medium"></div>
    <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
        <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
            </div>
        <div class="c-empty-state-card__text">
            <h2 class="c-empty-state-card__text--title headline-5"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "search.noResults"), $this);?>
</h2>
            <div class="c-empty-state-card__text--description">There are currently no authors registered in the system, or your search criteria did not match any existing author profiles. Try adjusting your search terms or browse all available content.</div>
        </div>
    </div>
</div>
<?php endif; ?>

</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>