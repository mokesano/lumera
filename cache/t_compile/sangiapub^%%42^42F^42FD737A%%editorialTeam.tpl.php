<?php /* Smarty version 2.6.26, created on 2026-04-07 16:10:03
         compiled from about/editorialTeam.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'string_format', 'about/editorialTeam.tpl', 25, false),array('modifier', 'escape', 'about/editorialTeam.tpl', 30, false),array('modifier', 'explode', 'about/editorialTeam.tpl', 86, false),array('modifier', 'count', 'about/editorialTeam.tpl', 87, false),array('modifier', 'trim', 'about/editorialTeam.tpl', 89, false),array('function', 'url', 'about/editorialTeam.tpl', 42, false),array('function', 'translate', 'about/editorialTeam.tpl', 152, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "about.editorialTeam"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ABOUT.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div class="publication-editors editor-in-chief-section">
<?php if (count ( $this->_tpl_vars['editorInChiefs'] ) > 0): ?>
    <?php if (count ( $this->_tpl_vars['editorInChiefs'] ) == 1): ?>
    <div class="publication-editor-type">Editor-in-Chief</div>
    <?php else: ?>
    <div class="publication-editor-type">Editors-in-Chief members</div>
    <?php endif; ?>
    
    <?php $_from = $this->_tpl_vars['editorInChiefs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['editor']):
?>
    <div id="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")); ?>
" class="publication-editor c-card__name editor-in-chief" itemtype="https://schema.org/Person">
        <?php $this->assign('profileImage', $this->_tpl_vars['editor']->getSetting('profileImage')); ?>
        <?php if ($this->_tpl_vars['profileImage']): ?>
        <picture>
            <source srcset="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
?as=webp" type="image/webp" loading="lazy" >
            <img class="lazyload avatar editor" loading="lazy" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
?as=webp" />
        </picture>
        <?php else: ?>
        <picture>
            <source srcset="//assets.sangia.org/static/images/contactPersonM.png?as=webp" type="image/webp" loading="lazy" >
            <img id="ID-<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
" class="lazyload avatar editor" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" src="//assets.sangia.org/static/images/contactPersonM.png" width="150" height="auto" loading="lazy"/>
        </picture>
        <?php endif; ?>
        
        <div class="publication-editor-name">
            <?php if ($this->_tpl_vars['editor']->getLocalizedBiography()): ?>
            <h3 class="card__title">
                <a class="card__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialTeamBio','path' => ((is_array($_tmp=$this->_tpl_vars['editor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")),'from' => 'team','anchor' => $this->_tpl_vars['editor']->getFullName()), $this);?>
">
                    <span class="fullname u-hide"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                    <?php if ($this->_tpl_vars['editor']->getSalutation()): ?>
                    <span class="text degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getSalutation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
                    <?php if ($this->_tpl_vars['editor']->getFirstName() !== $this->_tpl_vars['editor']->getLastName()): ?><span class="text given-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFirstName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
                    <?php if (((is_array($_tmp=$this->_tpl_vars['editor']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?><span class="text middle-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
                    <span class="text surname"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                    <?php if ($this->_tpl_vars['editor']->getSuffix()): ?><span class="text last-degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getSuffix())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
                </a>
                                <?php if ($this->_tpl_vars['editor']->getData('orcid')): ?>
                <span class="orcid">
                    <a title="Go to Orcid profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank">
                        <img src="//assets.sangia.org/img/orcid_16x16.svg" style="height:16px" class="lazyload" alt="orcid" />
                    </a>
                </span>
                <?php endif; ?>
                                <?php if ($this->_tpl_vars['editor']->getSintaId()): ?>
                <span class="sinta">
                    <a title="Go to Sinta profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="//sinta.kemdiktisaintek.go.id/authors/profile/<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getSintaId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="sintaid">
                        <img src="//sinta.kemdiktisaintek.go.id/public/assets/img/brand_sinta.png" style="height:18px" class="lazyload" alt="sinta" referrerpolicy="strict-origin-when-cross-origin" />
                    </a>
                </span>
                <?php endif; ?>
                                <?php if ($this->_tpl_vars['editor']->getData('scopusId')): ?>
                <span class="scopus">
                    <a title="Go to Scopus profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="https://www.scopus.com/authid/detail.uri?authorId=<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getData('scopusId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="scopusid"><svg xmlns="http://www.w3.org/2000/svg" role="img" version="1.1" class="gh-wordmark" focusable="false" aria-hidden="true" height="20" viewBox="0 0 70 27" width="55"><g fill="#f36d21"><path class="a" d="M4.23,21a9.79,9.79,0,0,1-4.06-.83l.29-2.08a7.17,7.17,0,0,0,3.72,1.09c2.13,0,3-1.22,3-2.39C7.22,13.85.3,13.43.3,9c0-2.37,1.56-4.29,5.2-4.29a9.12,9.12,0,0,1,3.77.75l-.1,2.08a7.58,7.58,0,0,0-3.67-1c-2.24,0-2.91,1.22-2.91,2.39,0,3,6.92,3.61,6.92,7.8C9.5,19.1,7.58,21,4.23,21Z"></path><path class="a" d="M20.66,20A6.83,6.83,0,0,1,16.76,21c-3,0-5.23-2.18-5.23-6.29,0-4.29,2.91-6.11,5.28-6.11,2.16,0,3.67.54,3.85,2.11,0,.23,0,.57,0,.86H18.81c0-1-.55-1.25-1.9-1.25a2.85,2.85,0,0,0-1.35.21c-.21.13-1.85.94-1.85,4.11s1.9,4.65,3.59,4.65a5.91,5.91,0,0,0,3.2-1.2Z"></path><path class="a" d="M27.29,21c-3.28,0-5.46-2.44-5.46-6.19,0-3.46,2-6.21,5.75-6.21,3.3,0,5.49,2.37,5.49,6.21C33.06,18.5,30.85,21,27.29,21Zm0-10.69a3.3,3.3,0,0,0-2,.65A5.83,5.83,0,0,0,24,14.73c0,3.74,2,4.6,3.56,4.6a3.45,3.45,0,0,0,2-.65A5.53,5.53,0,0,0,30.9,15C30.9,12.86,30.2,10.36,27.31,10.36Z"></path><path class="a" d="M40.37,21a5.63,5.63,0,0,1-2.6-.57v5.46h-2V12.23c0-.91-.05-1.72-.1-2.31l-.1-1H37.4l.31,1.74a4.86,4.86,0,0,1,4-2.05c2.39,0,4.26,1.56,4.26,5.72S43.69,21,40.37,21Zm.91-10.61a4.49,4.49,0,0,0-1.56.31,11.57,11.57,0,0,0-2,2.11v5.8a4.35,4.35,0,0,0,.7.34,4.12,4.12,0,0,0,1.61.34c2.57,0,3.74-1.9,3.74-4.73C43.82,12.94,43.51,10.44,41.27,10.44Z"></path><path class="a" d="M58.36,20.74H56.54L56.22,19a4.06,4.06,0,0,1-3.85,2.05c-2.08,0-3.77-.86-3.77-3.87V9h2V15.8c0,1.92.16,3.54,2,3.54a4.47,4.47,0,0,0,2-.47,6.77,6.77,0,0,0,1.64-2.08V9h2v8.53a19,19,0,0,0,.1,2.31Z"></path><path class="a" d="M64.86,21.07a6.87,6.87,0,0,1-3.67-1l.23-1.87a5.54,5.54,0,0,0,3.28,1.2c1.66,0,2.44-.75,2.44-1.66,0-2.39-5.88-2.26-5.88-5.9,0-1.77,1.38-3.22,4.21-3.22a6.59,6.59,0,0,1,3.38.88l-.21,1.87a4.67,4.67,0,0,0-3.15-1.14c-1.33,0-2.24.52-2.24,1.46,0,2.37,5.88,2.16,5.88,5.9C69.15,19.36,67.85,21.07,64.86,21.07Z"></path></g></svg>
                    </a>
                </span>
                <?php endif; ?>
            </h3>
            <?php else: ?>
            <h3 class="card__title">
                <span class="c-card__link"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
            </h3>
            <?php endif; ?>
        </div>
        
        <input id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['editor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" type="hidden" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getGender())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="gender">
        
                <?php if ($this->_tpl_vars['editor']->getLocalizedAffiliation()): ?>
            <?php $this->assign('affiliations', ((is_array($_tmp=$this->_tpl_vars['editor']->getLocalizedAffiliation())) ? $this->_run_mod_handler('explode', true, $_tmp, "\n") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "\n"))); ?>
            <?php $this->assign('affiliationCount', count($this->_tpl_vars['affiliations'])); ?>
            <?php $_from = $this->_tpl_vars['affiliations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['index'] => $this->_tpl_vars['affiliation']):
?>
                <?php if (((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp)) != ''): ?>
                <div class="sc-1mur6on-9 bLswwL publication-editor-affiliation">
                    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj">
                        <svg class="sc-1mur6on-14 bPLcdE" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path></svg>
                    </div>
                    <span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="affiliation" itemtype="http://schema.org/Affiliation">
                        <?php echo ((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                        <?php if ($this->_tpl_vars['index'] == $this->_tpl_vars['affiliationCount'] - 1 && $this->_tpl_vars['editor']->getCountry()): ?>
                            <?php $this->assign('countryCode', $this->_tpl_vars['editor']->getCountry()); ?>
                            <?php $this->assign('country', $this->_tpl_vars['countries'][$this->_tpl_vars['countryCode']]); ?>
                            , <?php echo ((is_array($_tmp=$this->_tpl_vars['country'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?>
        <?php endif; ?>
        
                <?php if ($this->_tpl_vars['editor']->getLocalizedGossip()): ?>
        <div class="sc-1mur6on-9 bLswwL GooSSiP">
            <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj">
                <svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em">
                    <path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path>
                </svg>
            </div>
            <span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="" itemtype="http://schema.org/Affiliation">
                <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getLocalizedGossip())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

            </span>
        </div>
        <?php endif; ?>
        
                <?php if ($this->_tpl_vars['editor']->getInterestString()): ?>
        <div class="sc-1mur6on-9 bLswwL publication-editor-affiliation">
            <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj">
                <svg width="1em" height="1em" viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg" class="sc-1mur6on-14 bPLcdE"><path id="Shape" fill="currentColor" fill-rule="nonzero" d="M4.98913043,4.69565217 L6.75,4.69565217 L6.75,8.09706522 L5.86956522,7.45728261 L4.98913043,8.09706522 L4.98913043,4.69565217 Z M9.39130435,4.10869565 L9.39130435,5.57608696 L12.0326087,5.57608696 L12.0326087,16.1413043 L2.70586957,16.1413043 C2.02206522,16.1413043 1.4673913,15.5866304 1.4673913,14.9028261 L1.4673913,5.2826087 C1.8225,5.4675 2.21869565,5.57608696 2.64130435,5.57608696 L3.52173913,5.57608696 L3.52173913,10.9790217 L5.86956522,9.27097826 L8.2173913,10.9790217 L8.2173913,3.22826087 L3.52173913,3.22826087 L3.52173913,4.10869565 L2.64130435,4.10869565 C1.99271739,4.10869565 1.4673913,3.51586957 1.4673913,2.78804348 C1.4673913,2.06021739 1.99271739,1.4673913 2.64130435,1.4673913 L12.6195652,1.4673913 L12.6195652,0 L2.64130435,0 C1.18565217,0 0,1.25021739 0,2.78804348 L0,14.9028261 C0,16.3936957 1.215,17.6086957 2.70586957,17.6086957 L13.5,17.6086957 L13.5,4.10869565 L9.39130435,4.10869565 Z"></path>
                </svg>
            </div>
            <span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getInterestString())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
        </div>
        <?php endif; ?>
        
                <?php if ($this->_tpl_vars['editor']->getData('mailingAddress')): ?>
        <a class="editorMail" href="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getEmail())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_self" itemprop="url" title="Use contact to email <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">
            Email <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

        </a>
        <?php endif; ?>
        
        <div class="clearfix"></div>
    </div>
    <?php endforeach; endif; unset($_from); ?>
<?php endif; ?>
</div>


<div class="publication-editors regular-editors-section">
<?php if (count ( $this->_tpl_vars['regularEditors'] ) > 0): ?>
    <?php if (count ( $this->_tpl_vars['regularEditors'] ) == 1): ?>
    <div class="publication-editor-type"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.editor"), $this);?>
 Member</div>
    <?php else: ?>
    <div class="publication-editor-type"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.editors"), $this);?>
 Members</div>
    <?php endif; ?>
    
    <?php $_from = $this->_tpl_vars['regularEditors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['editor']):
?>
    <div id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['editor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="publication-editor c-card__name regular-editor" itemtype="https://schema.org/Person">
        <?php $this->assign('profileImage', $this->_tpl_vars['editor']->getSetting('profileImage')); ?>
        <?php if ($this->_tpl_vars['profileImage']): ?>
        <picture>
            <source srcset="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
?as=webp" type="image/webp" loading="lazy" >
            <img class="lazyload avatar editor" loading="lazy" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
?as=webp" />
        </picture>
        <?php else: ?>
        <picture>
            <source srcset="//assets.sangia.org/static/images/contactPersonM.png?as=webp" type="image/webp" loading="lazy" >
            <img id="ID-<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%07d") : smarty_modifier_string_format($_tmp, "%07d")); ?>
" class="lazyload avatar editor" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" src="//assets.sangia.org/static/images/contactPersonM.png?as=webp" width="150" height="auto" loading="lazy"/>
        </picture>
        <?php endif; ?>
        
        <div class="publication-editor-name">
            <?php if ($this->_tpl_vars['editor']->getLocalizedBiography()): ?>
            <h3 class="card__title">
                <a class="card__link" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialTeamBio','path' => ((is_array($_tmp=$this->_tpl_vars['editor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")),'from' => 'team','anchor' => $this->_tpl_vars['editor']->getFullName()), $this);?>
">
                    <span class="fullname u-hide"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                    <?php if ($this->_tpl_vars['editor']->getSalutation()): ?>
                    <span class="text degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getSalutation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
                    <?php if ($this->_tpl_vars['editor']->getFirstName() !== $this->_tpl_vars['editor']->getLastName()): ?><span class="text given-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFirstName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
                    <?php if (((is_array($_tmp=$this->_tpl_vars['editor']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?><span class="text middle-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
                    <span class="text surname"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                    <?php if ($this->_tpl_vars['editor']->getSuffix()): ?><span class="text last-degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getSuffix())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
                </a>
                                <?php if ($this->_tpl_vars['editor']->getData('orcid')): ?>
                <span class="orcid">
                    <a title="Go to Orcid profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank">
                        <img src="//assets.sangia.org/img/orcid_16x16.svg" style="height:16px" class="lazyload" alt="orcid" />
                    </a>
                </span>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['editor']->getData('sintaId')): ?>
                <span class="sinta">
                    <a title="Go to Sinta profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="//sinta.kemdiktisaintek.go.id/authors/profile/<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getData('sintaId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="sintaid">
                        <img src="//sinta.kemdiktisaintek.go.id/public/assets/img/brand_sinta.png" style="height:18px" class="lazyload" alt="sinta" referrerpolicy="strict-origin-when-cross-origin" />
                    </a>
                </span>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['editor']->getData('scopusId')): ?>
                <span class="scopus">
                    <a title="Go to Scopus profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="https://www.scopus.com/authid/detail.uri?authorId=<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getData('scopusId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="scopusid"><svg xmlns="http://www.w3.org/2000/svg" role="img" version="1.1" class="gh-wordmark" focusable="false" aria-hidden="true" height="20" viewBox="0 0 70 27" width="55"><g fill="#f36d21"><path class="a" d="M4.23,21a9.79,9.79,0,0,1-4.06-.83l.29-2.08a7.17,7.17,0,0,0,3.72,1.09c2.13,0,3-1.22,3-2.39C7.22,13.85.3,13.43.3,9c0-2.37,1.56-4.29,5.2-4.29a9.12,9.12,0,0,1,3.77.75l-.1,2.08a7.58,7.58,0,0,0-3.67-1c-2.24,0-2.91,1.22-2.91,2.39,0,3,6.92,3.61,6.92,7.8C9.5,19.1,7.58,21,4.23,21Z"></path><path class="a" d="M20.66,20A6.83,6.83,0,0,1,16.76,21c-3,0-5.23-2.18-5.23-6.29,0-4.29,2.91-6.11,5.28-6.11,2.16,0,3.67.54,3.85,2.11,0,.23,0,.57,0,.86H18.81c0-1-.55-1.25-1.9-1.25a2.85,2.85,0,0,0-1.35.21c-.21.13-1.85.94-1.85,4.11s1.9,4.65,3.59,4.65a5.91,5.91,0,0,0,3.2-1.2Z"></path><path class="a" d="M27.29,21c-3.28,0-5.46-2.44-5.46-6.19,0-3.46,2-6.21,5.75-6.21,3.3,0,5.49,2.37,5.49,6.21C33.06,18.5,30.85,21,27.29,21Zm0-10.69a3.3,3.3,0,0,0-2,.65A5.83,5.83,0,0,0,24,14.73c0,3.74,2,4.6,3.56,4.6a3.45,3.45,0,0,0,2-.65A5.53,5.53,0,0,0,30.9,15C30.9,12.86,30.2,10.36,27.31,10.36Z"></path><path class="a" d="M40.37,21a5.63,5.63,0,0,1-2.6-.57v5.46h-2V12.23c0-.91-.05-1.72-.1-2.31l-.1-1H37.4l.31,1.74a4.86,4.86,0,0,1,4-2.05c2.39,0,4.26,1.56,4.26,5.72S43.69,21,40.37,21Zm.91-10.61a4.49,4.49,0,0,0-1.56.31,11.57,11.57,0,0,0-2,2.11v5.8a4.35,4.35,0,0,0,.7.34,4.12,4.12,0,0,0,1.61.34c2.57,0,3.74-1.9,3.74-4.73C43.82,12.94,43.51,10.44,41.27,10.44Z"></path><path class="a" d="M58.36,20.74H56.54L56.22,19a4.06,4.06,0,0,1-3.85,2.05c-2.08,0-3.77-.86-3.77-3.87V9h2V15.8c0,1.92.16,3.54,2,3.54a4.47,4.47,0,0,0,2-.47,6.77,6.77,0,0,0,1.64-2.08V9h2v8.53a19,19,0,0,0,.1,2.31Z"></path><path class="a" d="M64.86,21.07a6.87,6.87,0,0,1-3.67-1l.23-1.87a5.54,5.54,0,0,0,3.28,1.2c1.66,0,2.44-.75,2.44-1.66,0-2.39-5.88-2.26-5.88-5.9,0-1.77,1.38-3.22,4.21-3.22a6.59,6.59,0,0,1,3.38.88l-.21,1.87a4.67,4.67,0,0,0-3.15-1.14c-1.33,0-2.24.52-2.24,1.46,0,2.37,5.88,2.16,5.88,5.9C69.15,19.36,67.85,21.07,64.86,21.07Z"></path></g></svg>
                    </a>
                </span>
                <?php endif; ?>
            </h3>
            <?php else: ?>
            <h3 class="card__title">
                <span class="c-card__link"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
            </h3>
            <?php endif; ?>
        </div>
        
		<input id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['editor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" type="hidden" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getGender())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="gender">
		
        <?php if ($this->_tpl_vars['editor']->getLocalizedAffiliation()): ?>
            <?php $this->assign('affiliations', ((is_array($_tmp=$this->_tpl_vars['editor']->getLocalizedAffiliation())) ? $this->_run_mod_handler('explode', true, $_tmp, "\n") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "\n"))); ?>
            <?php $this->assign('affiliationCount', count($this->_tpl_vars['affiliations'])); ?>
            <?php $_from = $this->_tpl_vars['affiliations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['index'] => $this->_tpl_vars['affiliation']):
?>
                <?php if (((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp)) != ''): ?>
                <div class="sc-1mur6on-9 bLswwL publication-editor-affiliation">
                    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg class="sc-1mur6on-14 bPLcdE" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path></svg>
                    </div>
                    <span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="affiliation" itemtype="http://schema.org/Affiliation"><?php echo ((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['index'] == $this->_tpl_vars['affiliationCount'] - 1 && $this->_tpl_vars['editor']->getCountry()): ?><?php $this->assign('countryCode', $this->_tpl_vars['editor']->getCountry()); ?><?php $this->assign('country', $this->_tpl_vars['countries'][$this->_tpl_vars['countryCode']]); ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['country'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?>
        <?php endif; ?>
		
		<?php if ($this->_tpl_vars['editor']->getLocalizedGossip()): ?>
        <div class="sc-1mur6on-9 bLswwL GooSSiP">
            <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
            </div>
        	<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="" itemtype="http://schema.org/Affiliation"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getLocalizedGossip())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
        </div>
        <?php endif; ?>
            
		<?php if ($this->_tpl_vars['editor']->getInterestString()): ?>
        <div class="sc-1mur6on-9 bLswwL publication-editor-affiliation">
			<div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg width="1em" height="1em" viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg" class="sc-1mur6on-14 bPLcdE"><path id="Shape" fill="currentColor" fill-rule="nonzero" d="M4.98913043,4.69565217 L6.75,4.69565217 L6.75,8.09706522 L5.86956522,7.45728261 L4.98913043,8.09706522 L4.98913043,4.69565217 Z M9.39130435,4.10869565 L9.39130435,5.57608696 L12.0326087,5.57608696 L12.0326087,16.1413043 L2.70586957,16.1413043 C2.02206522,16.1413043 1.4673913,15.5866304 1.4673913,14.9028261 L1.4673913,5.2826087 C1.8225,5.4675 2.21869565,5.57608696 2.64130435,5.57608696 L3.52173913,5.57608696 L3.52173913,10.9790217 L5.86956522,9.27097826 L8.2173913,10.9790217 L8.2173913,3.22826087 L3.52173913,3.22826087 L3.52173913,4.10869565 L2.64130435,4.10869565 C1.99271739,4.10869565 1.4673913,3.51586957 1.4673913,2.78804348 C1.4673913,2.06021739 1.99271739,1.4673913 2.64130435,1.4673913 L12.6195652,1.4673913 L12.6195652,0 L2.64130435,0 C1.18565217,0 0,1.25021739 0,2.78804348 L0,14.9028261 C0,16.3936957 1.215,17.6086957 2.70586957,17.6086957 L13.5,17.6086957 L13.5,4.10869565 L9.39130435,4.10869565 Z"></path></svg>
			</div>
			<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getInterestString())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
		</div>
		<?php endif; ?>
		
		<?php if ($this->_tpl_vars['editor']->getData('mailingAddress')): ?>
		<a class="editorMail" href="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getEmail())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_self" itemprop="url" title="Use contact to email <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">Email <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
		<?php endif; ?>
		
        <div class="clearfix"></div>
    </div>
    <?php endforeach; endif; unset($_from); ?>
<?php endif; ?>
    <?php if (! $this->_tpl_vars['sectionEditors'] || ! $this->_tpl_vars['copyEditors'] || ! $this->_tpl_vars['proofreaders'] || ! $this->_tpl_vars['layoutEditors']): ?>
    <div class="clearfix"></div>
    <?php endif; ?>
</div>


<?php if ($this->_tpl_vars['sectionEditors'] || $this->_tpl_vars['copyEditors'] || $this->_tpl_vars['proofreaders'] || $this->_tpl_vars['layoutEditors']): ?>
<h3 class="editorial-members">Editorial Board Members</h3>
<?php endif; ?>
<div class="publication-editors">
<?php if (count ( $this->_tpl_vars['sectionEditors'] ) > 0): ?>
	<?php if (count ( $this->_tpl_vars['sectionEditors'] ) == 1): ?>
	<div class="publication-editor-type"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.sectionEditor"), $this);?>
</div>
    <?php else: ?>
    <div class="publication-editor-type"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.sectionEditors"), $this);?>
</div>
    <?php endif; ?>
    <?php $_from = $this->_tpl_vars['sectionEditors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['sectionEditor']):
?>
    <div id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="publication-editor c-card__name" itemtype="https://schema.org/Person">
    	<?php $this->assign('profileImage', $this->_tpl_vars['sectionEditor']->getSetting('profileImage')); ?>
    	<?php if ($this->_tpl_vars['profileImage']): ?>
    	<picture>
    	    <source srcset="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
?as=webp" type="image/webp" loading="lazy" >
    	    <img class="lazyload" loading="lazy" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
" />
    	</picture>
    	<?php endif; ?>		
    	<div class="publication-editor-name">
    		<?php if ($this->_tpl_vars['sectionEditor']->getLocalizedBiography() || $this->_tpl_vars['sectionEditor']->getUrl() || $this->_tpl_vars['sectionEditor']->getGoogleScholar() || $this->_tpl_vars['sectionEditor']->getSintaId() || $this->_tpl_vars['sectionEditor']->getScopusId()): ?>
    		<h3>
    		    <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialTeamBio','path' => ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")),'from' => 'editorial','anchor' => ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
"><span class="fullname u-hide"><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['sectionEditor']->getSalutation()): ?><span class="text degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getSalutation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['sectionEditor']->getFirstName() !== $this->_tpl_vars['sectionEditor']->getLastName()): ?><span class="text given-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getFirstName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if (((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))): ?><span class="text middle-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span class="text surname"><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['sectionEditor']->getSuffix()): ?><span class="text last-degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getSuffix())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
    		    </a>
    		    <?php if ($this->_tpl_vars['sectionEditor']->getData('orcid')): ?><span class="orcid"><a title="Go to Orcid profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank"><img class="lazyload" src="//assets.sangia.org/img/orcid_16x16.svg" style="height:16px" alt="orcid" /></a></span><?php endif; ?><?php if ($this->_tpl_vars['sectionEditor']->getData('sintaId')): ?><span class="sinta"><a title="Go to Sinta profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="//sinta.kemdiktisaintek.go.id/authors/profile/<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getData('sintaId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="sintaid"><img class="lazyload" src="//sinta.kemdiktisaintek.go.id/public/assets/img/brand_sinta.png" style="height:18px" alt="sinta" referrerpolicy="strict-origin-when-cross-origin" /></a></span><?php endif; ?><?php if ($this->_tpl_vars['sectionEditor']->getData('scopusId')): ?><span class="scopus"><a title="Go to Scopus profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="https://www.scopus.com/authid/detail.uri?authorId=<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getData('scopusId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="scopusid"><svg xmlns="http://www.w3.org/2000/svg" role="img" version="1.1" class="gh-wordmark" focusable="false" aria-hidden="true" height="20" viewBox="0 0 70 27" width="55"><g fill="#f36d21"><path class="a" d="M4.23,21a9.79,9.79,0,0,1-4.06-.83l.29-2.08a7.17,7.17,0,0,0,3.72,1.09c2.13,0,3-1.22,3-2.39C7.22,13.85.3,13.43.3,9c0-2.37,1.56-4.29,5.2-4.29a9.12,9.12,0,0,1,3.77.75l-.1,2.08a7.58,7.58,0,0,0-3.67-1c-2.24,0-2.91,1.22-2.91,2.39,0,3,6.92,3.61,6.92,7.8C9.5,19.1,7.58,21,4.23,21Z"></path><path class="a" d="M20.66,20A6.83,6.83,0,0,1,16.76,21c-3,0-5.23-2.18-5.23-6.29,0-4.29,2.91-6.11,5.28-6.11,2.16,0,3.67.54,3.85,2.11,0,.23,0,.57,0,.86H18.81c0-1-.55-1.25-1.9-1.25a2.85,2.85,0,0,0-1.35.21c-.21.13-1.85.94-1.85,4.11s1.9,4.65,3.59,4.65a5.91,5.91,0,0,0,3.2-1.2Z"></path><path class="a" d="M27.29,21c-3.28,0-5.46-2.44-5.46-6.19,0-3.46,2-6.21,5.75-6.21,3.3,0,5.49,2.37,5.49,6.21C33.06,18.5,30.85,21,27.29,21Zm0-10.69a3.3,3.3,0,0,0-2,.65A5.83,5.83,0,0,0,24,14.73c0,3.74,2,4.6,3.56,4.6a3.45,3.45,0,0,0,2-.65A5.53,5.53,0,0,0,30.9,15C30.9,12.86,30.2,10.36,27.31,10.36Z"></path><path class="a" d="M40.37,21a5.63,5.63,0,0,1-2.6-.57v5.46h-2V12.23c0-.91-.05-1.72-.1-2.31l-.1-1H37.4l.31,1.74a4.86,4.86,0,0,1,4-2.05c2.39,0,4.26,1.56,4.26,5.72S43.69,21,40.37,21Zm.91-10.61a4.49,4.49,0,0,0-1.56.31,11.57,11.57,0,0,0-2,2.11v5.8a4.35,4.35,0,0,0,.7.34,4.12,4.12,0,0,0,1.61.34c2.57,0,3.74-1.9,3.74-4.73C43.82,12.94,43.51,10.44,41.27,10.44Z"></path><path class="a" d="M58.36,20.74H56.54L56.22,19a4.06,4.06,0,0,1-3.85,2.05c-2.08,0-3.77-.86-3.77-3.87V9h2V15.8c0,1.92.16,3.54,2,3.54a4.47,4.47,0,0,0,2-.47,6.77,6.77,0,0,0,1.64-2.08V9h2v8.53a19,19,0,0,0,.1,2.31Z"></path><path class="a" d="M64.86,21.07a6.87,6.87,0,0,1-3.67-1l.23-1.87a5.54,5.54,0,0,0,3.28,1.2c1.66,0,2.44-.75,2.44-1.66,0-2.39-5.88-2.26-5.88-5.9,0-1.77,1.38-3.22,4.21-3.22a6.59,6.59,0,0,1,3.38.88l-.21,1.87a4.67,4.67,0,0,0-3.15-1.14c-1.33,0-2.24.52-2.24,1.46,0,2.37,5.88,2.16,5.88,5.9C69.15,19.36,67.85,21.07,64.86,21.07Z"></path></g></svg></a></span><?php endif; ?></h3>
    		<?php else: ?>
    		<h3><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3>
    		<?php endif; ?>
    	</div>
    	
    	<input id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" type="hidden" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getGender())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" name="gender">
    	
        <?php if ($this->_tpl_vars['sectionEditor']->getLocalizedAffiliation()): ?>
            <?php $this->assign('affiliations', ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getLocalizedAffiliation())) ? $this->_run_mod_handler('explode', true, $_tmp, "\n") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "\n"))); ?>
            <?php $this->assign('affiliationCount', count($this->_tpl_vars['affiliations'])); ?>
            <?php $_from = $this->_tpl_vars['affiliations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['index'] => $this->_tpl_vars['affiliation']):
?>
                <?php if (((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp)) != ''): ?>
                <div class="sc-1mur6on-9 bLswwL publication-editor-affiliation">
                    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg class="sc-1mur6on-14 bPLcdE" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path></svg>
                    </div>
                    <span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="affiliation" itemtype="http://schema.org/Affiliation"><?php echo ((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['index'] == $this->_tpl_vars['affiliationCount'] - 1 && $this->_tpl_vars['sectionEditor']->getCountry()): ?><?php $this->assign('countryCode', $this->_tpl_vars['sectionEditor']->getCountry()); ?><?php $this->assign('country', $this->_tpl_vars['countries'][$this->_tpl_vars['countryCode']]); ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['country'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?>
        <?php endif; ?>
    	
		<?php if ($this->_tpl_vars['sectionEditor']->getLocalizedGossip()): ?>
        <div class="sc-1mur6on-9 bLswwL GooSSiP">
            <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
            </div>
        	<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="" itemtype="http://schema.org/Affiliation"><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getLocalizedGossip())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
        </div>
        <?php endif; ?>
        
		<?php if ($this->_tpl_vars['sectionEditor']->getInterestString()): ?>
        <div class="sc-1mur6on-9 bLswwL publication-editor-affiliation">
		    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg width="1em" height="1em" viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg" class="sc-1mur6on-14 bPLcdE"><path id="Shape" fill="currentColor" fill-rule="nonzero" d="M4.98913043,4.69565217 L6.75,4.69565217 L6.75,8.09706522 L5.86956522,7.45728261 L4.98913043,8.09706522 L4.98913043,4.69565217 Z M9.39130435,4.10869565 L9.39130435,5.57608696 L12.0326087,5.57608696 L12.0326087,16.1413043 L2.70586957,16.1413043 C2.02206522,16.1413043 1.4673913,15.5866304 1.4673913,14.9028261 L1.4673913,5.2826087 C1.8225,5.4675 2.21869565,5.57608696 2.64130435,5.57608696 L3.52173913,5.57608696 L3.52173913,10.9790217 L5.86956522,9.27097826 L8.2173913,10.9790217 L8.2173913,3.22826087 L3.52173913,3.22826087 L3.52173913,4.10869565 L2.64130435,4.10869565 C1.99271739,4.10869565 1.4673913,3.51586957 1.4673913,2.78804348 C1.4673913,2.06021739 1.99271739,1.4673913 2.64130435,1.4673913 L12.6195652,1.4673913 L12.6195652,0 L2.64130435,0 C1.18565217,0 0,1.25021739 0,2.78804348 L0,14.9028261 C0,16.3936957 1.215,17.6086957 2.70586957,17.6086957 L13.5,17.6086957 L13.5,4.10869565 L9.39130435,4.10869565 Z"></path></svg>
			</div>
			<span class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl"><?php echo ((is_array($_tmp=$this->_tpl_vars['sectionEditor']->getInterestString())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
		</div>
		<?php endif; ?>
		
    	<div class="clearfix"></div>
    </div>
	<?php endforeach; endif; unset($_from); ?>
</div>	
<?php endif; ?>

<div class="publication-editors">
    <?php if (count ( $this->_tpl_vars['copyEditors'] ) > 0): ?>
    <div class="editors-area">
    	<?php if (count ( $this->_tpl_vars['copyEditors'] ) == 1): ?>
    		<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.copyeditor"), $this);?>
<br></h3>
    	<?php else: ?>
    		<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.copyeditors"), $this);?>
<br></h3>
    	<?php endif; ?>
    </div>
    	<?php $_from = $this->_tpl_vars['copyEditors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['copyEditor']):
?>
    	<section class="article-body"><?php if ($this->_tpl_vars['copyEditor']->getLocalizedBiography()): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialTeamBio','path' => ((is_array($_tmp=$this->_tpl_vars['copyEditor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")),'from' => 'editorial','anchor' => $this->_tpl_vars['copyEditor']->getFullName()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['copyEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['copyEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php if ($this->_tpl_vars['copyEditor']->getData('orcid')): ?><span class="orcid"><a title="Go to Orcid profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['copyEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['copyEditor']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank"><img class="lazyload" src="//assets.sangia.org/img/orcid_16x16.svg" style="height:16px" alt="orcid" /></a></span><?php endif; ?><?php if ($this->_tpl_vars['copyEditor']->getData('sintaId')): ?><span class="sinta"><a title="Go to Sinta profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['copyEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="//sinta.kemdiktisaintek.go.id/authors/profile/<?php echo ((is_array($_tmp=$this->_tpl_vars['copyEditor']->getData('sintaId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="sintaid"><img class="lazyload" src="//sinta.kemdiktisaintek.go.id/public/assets/img/brand_sinta.png" style="height:18px" alt="sinta" referrerpolicy="strict-origin-when-cross-origin" /></a></span><?php endif; ?><?php if ($this->_tpl_vars['copyEditor']->getData('scopusId')): ?><span class="scopus"><a title="Go to Scopus profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['copyEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="https://www.scopus.com/authid/detail.uri?authorId=<?php echo ((is_array($_tmp=$this->_tpl_vars['copyEditor']->getData('scopusId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="scopusid"><svg xmlns="http://www.w3.org/2000/svg" role="img" version="1.1" class="gh-wordmark" focusable="false" aria-hidden="true" height="20" viewBox="0 0 70 27" width="55"><g fill="#f36d21"><path class="a" d="M4.23,21a9.79,9.79,0,0,1-4.06-.83l.29-2.08a7.17,7.17,0,0,0,3.72,1.09c2.13,0,3-1.22,3-2.39C7.22,13.85.3,13.43.3,9c0-2.37,1.56-4.29,5.2-4.29a9.12,9.12,0,0,1,3.77.75l-.1,2.08a7.58,7.58,0,0,0-3.67-1c-2.24,0-2.91,1.22-2.91,2.39,0,3,6.92,3.61,6.92,7.8C9.5,19.1,7.58,21,4.23,21Z"></path><path class="a" d="M20.66,20A6.83,6.83,0,0,1,16.76,21c-3,0-5.23-2.18-5.23-6.29,0-4.29,2.91-6.11,5.28-6.11,2.16,0,3.67.54,3.85,2.11,0,.23,0,.57,0,.86H18.81c0-1-.55-1.25-1.9-1.25a2.85,2.85,0,0,0-1.35.21c-.21.13-1.85.94-1.85,4.11s1.9,4.65,3.59,4.65a5.91,5.91,0,0,0,3.2-1.2Z"></path><path class="a" d="M27.29,21c-3.28,0-5.46-2.44-5.46-6.19,0-3.46,2-6.21,5.75-6.21,3.3,0,5.49,2.37,5.49,6.21C33.06,18.5,30.85,21,27.29,21Zm0-10.69a3.3,3.3,0,0,0-2,.65A5.83,5.83,0,0,0,24,14.73c0,3.74,2,4.6,3.56,4.6a3.45,3.45,0,0,0,2-.65A5.53,5.53,0,0,0,30.9,15C30.9,12.86,30.2,10.36,27.31,10.36Z"></path><path class="a" d="M40.37,21a5.63,5.63,0,0,1-2.6-.57v5.46h-2V12.23c0-.91-.05-1.72-.1-2.31l-.1-1H37.4l.31,1.74a4.86,4.86,0,0,1,4-2.05c2.39,0,4.26,1.56,4.26,5.72S43.69,21,40.37,21Zm.91-10.61a4.49,4.49,0,0,0-1.56.31,11.57,11.57,0,0,0-2,2.11v5.8a4.35,4.35,0,0,0,.7.34,4.12,4.12,0,0,0,1.61.34c2.57,0,3.74-1.9,3.74-4.73C43.82,12.94,43.51,10.44,41.27,10.44Z"></path><path class="a" d="M58.36,20.74H56.54L56.22,19a4.06,4.06,0,0,1-3.85,2.05c-2.08,0-3.77-.86-3.77-3.87V9h2V15.8c0,1.92.16,3.54,2,3.54a4.47,4.47,0,0,0,2-.47,6.77,6.77,0,0,0,1.64-2.08V9h2v8.53a19,19,0,0,0,.1,2.31Z"></path><path class="a" d="M64.86,21.07a6.87,6.87,0,0,1-3.67-1l.23-1.87a5.54,5.54,0,0,0,3.28,1.2c1.66,0,2.44-.75,2.44-1.66,0-2.39-5.88-2.26-5.88-5.9,0-1.77,1.38-3.22,4.21-3.22a6.59,6.59,0,0,1,3.38.88l-.21,1.87a4.67,4.67,0,0,0-3.15-1.14c-1.33,0-2.24.52-2.24,1.46,0,2.37,5.88,2.16,5.88,5.9C69.15,19.36,67.85,21.07,64.86,21.07Z"></path></g></svg></a></span><?php endif; ?>,
    		<?php if ($this->_tpl_vars['copyEditor']->getLocalizedAffiliation()): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['copyEditor']->getLocalizedAffiliation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php if ($this->_tpl_vars['copyEditor']->getCountry()): ?><?php $this->assign('countryCode', $this->_tpl_vars['copyEditor']->getCountry()); ?><?php $this->assign('country', $this->_tpl_vars['countries'][$this->_tpl_vars['countryCode']]); ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['country'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
    	</section>
    	<?php endforeach; endif; unset($_from); ?>
    	<br>
    <?php endif; ?>

    <?php if (count ( $this->_tpl_vars['proofreaders'] ) > 0): ?>
    <div class="editors-area">
    	<?php if (count ( $this->_tpl_vars['copyEditors'] ) == 1): ?>
    		<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.proofreader"), $this);?>
<br></h3>
    	<?php else: ?>
    		<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.proofreaders"), $this);?>
<br></h3>
    	<?php endif; ?>
    </div>
    	<?php $_from = $this->_tpl_vars['proofreaders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['proofreader']):
?>
    	<section class="article-body"><?php if ($this->_tpl_vars['proofreader']->getLocalizedBiography()): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialTeamBio','path' => ((is_array($_tmp=$this->_tpl_vars['proofreader']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")),'from' => 'editorial','anchor' => $this->_tpl_vars['proofreader']->getFullName()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['proofreader']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['proofreader']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>,
    		<?php if ($this->_tpl_vars['proofreader']->getLocalizedAffiliation()): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['proofreader']->getLocalizedAffiliation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php if ($this->_tpl_vars['proofreader']->getCountry()): ?><?php $this->assign('countryCode', $this->_tpl_vars['proofreader']->getCountry()); ?><?php $this->assign('country', $this->_tpl_vars['countries'][$this->_tpl_vars['countryCode']]); ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['country'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
    	</section>
    	<?php endforeach; endif; unset($_from); ?>
    	<br>
    <?php endif; ?>
</div>

<div class="publication-editors">
    <?php if (count ( $this->_tpl_vars['layoutEditors'] ) > 0): ?>
    <div class="editors-area">
    	<?php if (count ( $this->_tpl_vars['copyEditors'] ) == 1): ?>
    		<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.layoutEditor"), $this);?>
<br></h3>
    	<?php else: ?>
    		<h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.role.layoutEditors"), $this);?>
<br></h3>
    	<?php endif; ?>
    </div>
    	<?php $_from = $this->_tpl_vars['layoutEditors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['layoutEditor']):
?>
    	<section class="article-body"><?php if ($this->_tpl_vars['layoutEditor']->getLocalizedBiography()): ?><a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'editorialTeamBio','path' => ((is_array($_tmp=$this->_tpl_vars['layoutEditor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")),'from' => 'editorial','anchor' => $this->_tpl_vars['layoutEditor']->getFullName()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['layoutEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['layoutEditor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>,
    		<?php if ($this->_tpl_vars['layoutEditor']->getLocalizedAffiliation()): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['layoutEditor']->getLocalizedAffiliation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php if ($this->_tpl_vars['layoutEditor']->getCountry()): ?><?php $this->assign('countryCode', $this->_tpl_vars['layoutEditor']->getCountry()); ?><?php $this->assign('country', $this->_tpl_vars['countries'][$this->_tpl_vars['countryCode']]); ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['country'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
    	</section>
    	<?php endforeach; endif; unset($_from); ?>
    <?php endif; ?>
</div>	

</section>
</section>

<?php if ($this->_tpl_vars['editors'] || $this->_tpl_vars['sectionEditors'] || $this->_tpl_vars['copyEditors'] || $this->_tpl_vars['proofreaders'] || $this->_tpl_vars['layoutEditors']): ?>
<div class="statement u-font-serif">All members of the Editorial Board have identified their affiliated institutions or organizations, along with the corresponding country or geographic region. <?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Research Media and Publishing'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>Production & hosted of Sangia Research Media & Publishing<?php endif; ?> remains neutral with regard to any jurisdictional claims.
</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>