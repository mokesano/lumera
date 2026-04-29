<?php /* Smarty version 2.6.26, created on 2026-04-04 05:39:17
         compiled from search/authorDetails.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'string_format', 'search/authorDetails.tpl', 16, false),array('modifier', 'escape', 'search/authorDetails.tpl', 16, false),array('modifier', 'explode', 'search/authorDetails.tpl', 65, false),array('modifier', 'mask_email', 'search/authorDetails.tpl', 99, false),array('modifier', 'strip_tags', 'search/authorDetails.tpl', 147, false),array('modifier', 'strip_unsafe_html', 'search/authorDetails.tpl', 180, false),array('modifier', 'nl2br', 'search/authorDetails.tpl', 180, false),array('modifier', 'count', 'search/authorDetails.tpl', 195, false),array('modifier', 'truncate', 'search/authorDetails.tpl', 258, false),array('modifier', 'date_format', 'search/authorDetails.tpl', 283, false),array('modifier', 'to_array', 'search/authorDetails.tpl', 295, false),array('modifier', 'number_format', 'search/authorDetails.tpl', 304, false),array('function', 'translate', 'search/authorDetails.tpl', 99, false),array('function', 'url', 'search/authorDetails.tpl', 148, false),array('function', 'call_hook', 'search/authorDetails.tpl', 250, false),array('function', 'math', 'search/authorDetails.tpl', 304, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "search.authorDetails"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-AUTH27.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


            <h2 data-author-id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['authorId'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="profiles" itemprop="name" itemtype="http://schema.org/Person"><?php if ($this->_tpl_vars['user']->getFullName()): ?><span class="fullname u-hide"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['user']->getSalutation()): ?><span class="text degree prefix"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getSalutation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['firstName'] !== $this->_tpl_vars['lastName']): ?><span class="text given-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['middleName']): ?><span class="text middle-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span class="text surname"><?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['user']->getSuffix()): ?><span class="last-degree suffix">, <?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getSuffix())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
                <?php if ($this->_tpl_vars['matchedUserId']): ?>
                    <span class="text verified-badge">Verified</span>
                <?php endif; ?>
            </h2>
            
        </div>
    </div>
</div>

<div class="live-area-wrapper">
    <div class="u-row row">
        <aside class="columns medium-2"></aside>
        <section class="column medium-10 cms-person">
            <h2 data-author-id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['authorId'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="person-name" itemprop="name" itemtype="http://schema.org/Person"><?php if ($this->_tpl_vars['user']->getFullName()): ?><span class="fullname u-hide"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['user']->getSalutation()): ?><span class="text degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getSalutation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['firstName'] !== $this->_tpl_vars['lastName']): ?><span class="text given-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['middleName']): ?><span class="text middle-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span class="text surname"><?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['user']->getSuffix()): ?><span class="last-degree suffix">, <?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getSuffix())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
                <?php if ($this->_tpl_vars['matchedUserId']): ?>
                    <span class="text verified-badge">Verified</span>
                <?php endif; ?>
            </h2>
        </section>
    </div>
    <div class="u-row row">
    	<div class="author-profiles">
    		<section class="column medium-2">
    			<div class="cms-common">
    				<div class="avatar bJniPh islvFm overview" size="150">
    				<span style="box-sizing: border-box; display: inline-block; overflow: hidden; width: 100%; height: 100%; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; position: relative; max-width: 150px;">
    				    <span style="box-sizing: border-box; display: block; width: 100%; height: 100%; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; max-width: 150px;">
    				        <img style="display: block; max-width: 100%; width: initial; height: initial; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; width: 100%;" alt="" aria-hidden="true" src="//assets.sangia.org/static/images/128x150-image.png">
    				    </span>
    				    <picture>
    				    <?php if ($this->_tpl_vars['hasProfileImage'] && $this->_tpl_vars['profileImageUrl']): ?>
    				    <source srcset="<?php echo ((is_array($_tmp=$this->_tpl_vars['profileImageUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" type="image/webp">
        				<img data-author-id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['authorId'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="lazyload avatar sangia-author" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['profileImageUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" height="auto" width="150" style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%; object-fit: cover;" />
        				<?php else: ?>
        				<source <?php if ($this->_tpl_vars['userGender'] == 'M'): ?>srcset="//assets.sangia.org/img/contactPersonM.png?as=webp"<?php elseif ($this->_tpl_vars['userGender'] == 'F'): ?>srcset="//assets.sangia.org/img/contactPersonF.png?as=webp"<?php else: ?>srcset="//assets.sangia.org/static/images/default_203.jpg?as=webp"<?php endif; ?> type="image/webp">
        				<img data-author-id="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['authorId'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="lazyload avatar sangia-author" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" src="//assets.sangia.org/static/images/default_203.jpg" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" height="auto" width="150" style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%; object-fit: cover;" />
        				<?php endif; ?>
        				</picture>
        			</span>	
    				</div>
    			</div>
    		</section>	
            <section class="column medium-7 cms-person">
                <div class="overview description">
                    <section class="paragraph" itemprop="creator" itemtype="http://schema.org/Person">
                        <span class="creator" itemprop="name">Author Profile</span>
                    </section>
                    <?php if ($this->_tpl_vars['affiliation']): ?>
                        <?php $this->assign('affiliationsArray', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'htmlall') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'htmlall')))) ? $this->_run_mod_handler('explode', true, $_tmp, "\n") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "\n"))); ?>
                        <?php $_from = $this->_tpl_vars['affiliationsArray']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['affiliationsLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['affiliationsLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['singleAffiliation']):
        $this->_foreach['affiliationsLoop']['iteration']++;
?>
                            <div class="affiliation bLswwL">
                                <div class="iggNhe dlPwne"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path></svg>
                                </div>
                                <p class="paragraph" itemprop="affiliation" itemtype="http://schema.org/Affiliation">
                                    <?php echo ((is_array($_tmp=$this->_tpl_vars['singleAffiliation'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if (($this->_foreach['affiliationsLoop']['iteration'] == $this->_foreach['affiliationsLoop']['total'])): ?><?php if ($this->_tpl_vars['country']): ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['country'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php endif; ?>
                                </p>
                            </div>
                        <?php endforeach; endif; unset($_from); ?>
                    <?php else: ?>
                        <div class="affiliation bLswwL">
                            <div class="iggNhe dlPwne">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em">
                                    <path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path>
                                </svg>
                            </div>
                            <p class="paragraph" itemprop="affiliation" itemtype="http://schema.org/Affiliation">
                                Author affiliation not available<?php if ($this->_tpl_vars['country']): ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['country'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
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
				
                    <?php if ($this->_tpl_vars['authorEmail']): ?>
                    <div class="scwizdam-1mur6wiz-7 bLswwL">
                        <div class="iggNhe dlPwne sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width="1em" height="1em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg></div>
                        <p class="paragraph"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.email"), $this);?>
: <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['authorEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('mask_email', true, $_tmp) : $this->_plugins['modifier']['mask_email'][0][0]->smartyMaskEmail($_tmp)); ?>
</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="scwizdam-1mur6wiz-9 bLswwL u-hide">
                        <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj">
                            <svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
                        </div>
                		<p class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="" itemtype="http://schema.org/Journal"><?php if ($this->_tpl_vars['currentJournal']): ?><?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sekolah Tinggi Ilmu Pertanian Wuna'): ?>Published by Sangia Publishing.<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Publishing'): ?>Published by <?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
.<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['siteTitle'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></p>
            		</div>
            		
        			<div class="author-person eTETae">
        			    <?php if ($this->_tpl_vars['authorOrcid']): ?>
        				<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
        					<img class="lazyload" src="//assets.sangia.org/img/orcid_16x16.svg" style="height:16px" alt="orcid" />
        					<span class="orcid"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.orcid"), $this);?>
 </span><a title="Go to Orcid profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="https://orcid.org/<?php echo ((is_array($_tmp=$this->_tpl_vars['authorOrcid'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorOrcid'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
        					</p>
        			    <?php elseif ($this->_tpl_vars['user']->getData('orcid')): ?>
        				<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
        					<img class="lazyload" src="//assets.sangia.org/img/orcid_16x16.svg" style="height:16px" alt="orcid" />
        					<span class="orcid"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.orcid"), $this);?>
 </span><a title="Go to Orcid profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('orcid'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
        					</p>
        				<?php endif; ?>
        				<?php if ($this->_tpl_vars['user']->getSintaId()): ?>
        				<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
        					<img class="lazyload" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/ico/brand_sinta.png" style="height:16px" alt="sinta" />
        					<span class="sinta">Sinta: </span><a title="Go to Sinta profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="//sinta.kemdiktisaintek.go.id/authors/profile/<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getSintaId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="sintaid">Science And Technology Index</a><span class="articleCount"> Academic Profile by Dikti, Saintek Indonesia</span>
        				</p><?php endif; ?>
        				<?php if ($this->_tpl_vars['user']->getScopusId()): ?>
        				<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
        				    <img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/ico/scopus.ico" alt="Scopus" width="16" height="16" style="vertical-align: middle; margin-right: 3px;">
        					<span class="scopus">Scopus</span> ID <a title="Go to Scopus profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="https://www.scopus.com/authid/detail.uri?authorId=<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getScopusId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="scopusid"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getScopusId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
        				</p><?php endif; ?>
        				<?php if ($this->_tpl_vars['user']->getData('dimensionId')): ?>
    					<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
    					    <img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/ico/dimension.ico" alt="Dimension" width="16" height="16" style="vertical-align: middle; margin-right: 3px;" class="brand" referrerpolicy="strict-origin-when-cross-origin" />
    						<span class="dimension">Dimension</span> ID <a title="Go to Dimension profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="https://app.dimensions.ai/details/entities/publication/author/<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('dimensionId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="scopusid"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getData('dimensionId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
    					</p><?php endif; ?>
        				<?php if ($this->_tpl_vars['user']->getResearcherId()): ?>
        				<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
        				    <img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/assets/ico/wos.svg" alt="Scopus" width="16" height="16" style="vertical-align: middle; margin-right: 3px;">
        					<span class="researcher">ResearcherId</span> ID <a title="Go to Scopus profile of <?php echo ((is_array($_tmp=$this->_tpl_vars['firstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 <?php if ($this->_tpl_vars['middleName']): ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['middleName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?> <?php echo ((is_array($_tmp=$this->_tpl_vars['lastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="https://www.scopus.com/authid/detail.uri?authorId=<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getResearcherId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" class="scopusid"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getResearcherId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
        				</p><?php endif; ?>
        			</div>        		
    			</div>
            </section>
    		<section class="column medium-3">
    			<section class="box">
    				<section><h4 class="headline-524909129">Want to publish with <?php if ($this->_tpl_vars['currentJournal']): ?><i><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</i><?php else: ?>us<?php endif; ?>? Submit your Manuscript online.</h4></section>
    				<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','op' => 'submit'), $this);?>
" target="_blank" data-track="click" class="button-base-2906877647">
    					<span class="button-label-1281676810">Submit paper</span>
    					<svg width="16" height="16" viewBox="0 0 16 16" class="button-icon-1969128361"><path fill="inherit" fill-rule="evenodd" d="M13.161 12.387c.428 0 .774.347.774.774v1.033c0 .996-.81 1.806-1.806 1.806H1.677A1.68 1.68 0 0 1 0 14.323V3.87c0-.996.81-1.806 1.806-1.806H2.84a.774.774 0 0 1 0 1.548H1.806a.258.258 0 0 0-.258.258v10.452a.13.13 0 0 0 .13.129h10.451a.258.258 0 0 0 .258-.258V13.16c0-.427.347-.774.774-.774zM14.323 0A1.68 1.68 0 0 1 16 1.677V8a.774.774 0 0 1-1.548 0V2.644l-9.002 9a.768.768 0 0 1-.547.227.773.773 0 0 1-.547-1.321l9-9.002H8A.774.774 0 0 1 8 0h6.323z"></path></svg>
    				</a>
    			</section>
    		</section>        
    	</div>
    </div>
</div>

<?php if ($this->_tpl_vars['user']->getScopusId()): ?>
<div class="live-area-wrapper u-pt-0 u-pb-0 u-bb-s2">
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
<div class="live-area-wrapper u-bb-s2">
    <div class="u-row row">
        <aside class="column medium-2 null">
    		<header class="anchored">
    		    <h3 class=" "><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.biography"), $this);?>
</h3>
    		</header>
		</aside>
    	<div class="columns medium-10 person-detail">
    		<div id="<?php echo $this->_tpl_vars['authorId']; ?>
" class="person-detail editor-bio" itemprop="description"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getLocalizedBiography())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
 <?php if ($this->_tpl_vars['authorUrl']): ?><a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['authorUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank">Author Personal Website</a><?php elseif ($this->_tpl_vars['user']->getUrl()): ?><a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank">Author Personal Website</a><?php elseif ($this->_tpl_vars['user']->getGoogleScholar()): ?><a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getGoogleScholar())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank">Author Personal Website</a><?php endif; ?>
    		</div>
    	</div>
    </div>
</div>
<?php endif; ?>

<div class="<?php if ($this->_tpl_vars['user']->getLocalizedBiography()): ?>live-area-wrapper<?php else: ?>live-area u-pt-48 u-pb-48<?php endif; ?>">
    <div class="u-row row">
        <div class="column medium-12">
            <div data-test="title" class="article-list">
                <h3 class="articleAuthor">
                    <span class="header-content">Article's by author</span>
                    <div class="info-article" style="display: inherit;font-size: 15px;">
                        <span class="info-publisher u-mr-8"><?php if ($this->_tpl_vars['currentJournal']): ?><?php if ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sekolah Tinggi Ilmu Pertanian Wuna'): ?>Published by Sangia Publishing.<?php elseif ($this->_tpl_vars['currentJournal']->getSetting('publisherInstitution') == 'Sangia Publishing'): ?>Published by <?php echo ((is_array($_tmp=$this->_tpl_vars['currentJournal']->getSetting('publisherInstitution'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>SANGIA Research Media & Publishing<?php endif; ?><?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['siteTitle'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>.</span>
                        <span class="article-counts u-mr-8"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.articles"), $this);?>
 count <?php echo ((is_array($_tmp=count($this->_tpl_vars['publishedArticles']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
.</span>
                        <span class="time-stamp"></span>
                    </div>
                </h3>
            </div>
        </div>    
    </div>

<section id="search-article-list" <?php if ($this->_tpl_vars['user']->getScopusId()): ?>class="u-bb-s2" <?php endif; ?>data-track-component="search grid">
    <div class="u-row row">
        <div class="columns">    
            <div class="article__list" id="articleList">
<ul class="app-article-list-row">
	<?php $_from = $this->_tpl_vars['publishedArticles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['article']):
?>
		<?php $this->assign('issueId', $this->_tpl_vars['article']->getIssueId()); ?>
		<?php $this->assign('issue', $this->_tpl_vars['issues'][$this->_tpl_vars['issueId']]); ?>
		<?php $this->assign('issueUnavailable', $this->_tpl_vars['issuesUnavailable'][$this->_tpl_vars['issueId']]); ?>
		<?php $this->assign('sectionId', $this->_tpl_vars['article']->getSectionId()); ?>
		<?php $this->assign('journalId', $this->_tpl_vars['article']->getJournalId()); ?>
		<?php $this->assign('journal', $this->_tpl_vars['journals'][$this->_tpl_vars['journalId']]); ?>
		<?php $this->assign('section', $this->_tpl_vars['sections'][$this->_tpl_vars['sectionId']]); ?>
		<?php if ($this->_tpl_vars['issue']->getPublished() && $this->_tpl_vars['section'] && $this->_tpl_vars['journal']): ?>

	<?php if ($this->_tpl_vars['article']->getLocalizedFileName() && $this->_tpl_vars['article']->getLocalizedShowCoverPage()): ?>
		<?php $this->assign('showCoverPage', true); ?>
	<?php else: ?>
		<?php $this->assign('showCoverPage', false); ?>
	<?php endif; ?>

	<?php if (( ! $this->_tpl_vars['subscriptionRequired'] || $this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN || $this->_tpl_vars['subscribedUser'] || $this->_tpl_vars['subscribedDomain'] || ( $this->_tpl_vars['subscriptionExpiryPartial'] && $this->_tpl_vars['articleExpiryPartial'][$this->_tpl_vars['articleId']] ) )): ?>
		<?php $this->assign('hasAccess', 1); ?>
	<?php else: ?>
		<?php $this->assign('hasAccess', 0); ?>
	<?php endif; ?>

	<?php if ($this->_tpl_vars['article']->getGalleys()): ?>

	<li class="app-article-list-row__item sangia-wizdam">
		<div class="u-full-height" data-native-ad-placement="false">
			<article class="u-full-height c-card c-card--flush" itemtype="http://schema.org/ScholarlyArticle">
				<div class="c-card__layout u-full-heights">
				    <?php if ($this->_tpl_vars['showCoverPage']): ?>
					<div class="c-card__image">
						<picture>
                        <?php if ($this->_tpl_vars['currentJournal']): ?>
							<source type="image/webp" srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 160w,<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 290w">
							<img class="lazyload" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if ($this->_tpl_vars['article']->getLocalizedCoverPageAltText() != ''): ?> alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedCoverPageAltText())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?> alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.coverPage.altText"), $this);?>
"<?php endif; ?> itemprop="image">
                        <?php else: ?>
                                                        <source type="image/webp" srcset="<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/journals/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getJournalId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 160w,<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/journals/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getJournalId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 290w">
                            <img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/journals/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getJournalId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedCoverPageAltText())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="image">
                        <?php endif; ?>
						</picture>
					</div>
					<?php endif; ?>
					<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Issue::Issue::ArticleCoverImage"), $this);?>

					<div class="c-card__body u-display-flex u-flex-direction-column">
						<h3 class="c-card__title" itemprop="name headline">
							<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId()), $this);?>
" class="c-card__link u-link-inherit" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</a>
						</h3>
						<?php if ($this->_tpl_vars['article']->getLocalizedAbstract()): ?>
						<div class="c-card__summary abstract-content u-mb-16 u-hide-sm-max" itemprop="description"><p><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p></div>
						<?php endif; ?>
						<ul class="c-author-list c-author-list--compact c-author-list--separated u-mt-auto" data-test="author-list"><?php $this->assign('authors', $this->_tpl_vars['article']->getAuthors()); ?><?php $_from = $this->_tpl_vars['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authors']['iteration']++;
?><?php $this->assign('fullname', $this->_tpl_vars['author']->getFullName()); ?><?php $this->assign('authorFirstName', $this->_tpl_vars['author']->getFirstName()); ?><?php $this->assign('authorMiddleName', $this->_tpl_vars['author']->getMiddleName()); ?><?php $this->assign('authorLastName', $this->_tpl_vars['author']->getLastName()); ?><li itemprop="creator" itemtype="http://schema.org/Person"><?php if ($this->_tpl_vars['fullname']): ?><span class="u-hide" itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['fullname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['authorFirstName'] !== $this->_tpl_vars['authorLastName']): ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorFirstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['authorMiddleName']): ?><span itemprop="name"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['authorMiddleName'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 1, ".") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 1, ".")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorLastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?></li><?php endforeach; endif; unset($_from); ?>
						</ul>
					</div>
				</div>
				<div class="c-card__section c-meta">
					<?php if ($this->_tpl_vars['issue']->getPublished() && $this->_tpl_vars['section'] && $this->_tpl_vars['journal']): ?>
					<span class="c-meta__item c-meta__item--block-at-lg" data-test="article.type">
						<span class="c-meta__type"><?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
					</span>
					<?php endif; ?>
					
                    <?php if ($this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN): ?>
                        <span class="c-meta__item c-meta__item--block-at-lg 1" itemprop="openAccess" data-test="open-access">
                            <span class="u-color-open-access">Open Access</span>
                        </span>
                    <?php elseif ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getAccessStatus() == @ISSUE_ACCESS_OPEN): ?>
                        <span class="c-meta__item c-meta__item--block-at-lg 2" itemprop="openAccess" data-test="open-access">
                            <span class="u-color-open-access">Open Access</span>
                        </span>
                    <?php elseif ($this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN): ?>
                        <span class="c-meta__item c-meta__item--block-at-lg 3" itemprop="openAccess" data-test="open-access">
                            <span class="u-color-open-access">Open Access</span>
                        </span>
                    <?php endif; ?>
					
					<time class="c-meta__item c-meta__item--block-at-lg" datetime="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, '%e %b %Y') : smarty_modifier_date_format($_tmp, '%e %b %Y')))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</time>
					
					<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg u-text-bold" data-test="journal-title-and-link"><a title="Go to <?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></div>
					
					<?php $this->assign('doi', $this->_tpl_vars['article']->getStoredPubId('doi')); ?>
					<?php if ($this->_tpl_vars['article']->getPubId('doi')): ?>
					<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="info-DOI"><a title="Permanent link for <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="http://doi.org/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">DOI: <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></div>
					<?php endif; ?>
					
					<?php if ($this->_tpl_vars['currentJournal']): ?>
    					<?php $_from = $this->_tpl_vars['article']->getGalleys(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['galleyList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['galleyList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['galley']):
        $this->_foreach['galleyList']['iteration']++;
?>
    					<?php if ($this->_tpl_vars['galley']->isPdfGalley() && $this->_tpl_vars['hasAccess'] || ( $this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] )): ?>
    					<div class="u-hide c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="volume-and-page-info"><a title="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['articlePath'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal']))))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
" <?php if ($this->_tpl_vars['galley']->getRemoteURL()): ?>target="_blank" <?php endif; ?>class="file">Fulltext <span class="fileSize">(<?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
, <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getNiceFileSize())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
)</span> <span class="fileView"><?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getViews())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 views</span></a></div>
    					<?php endif; ?>
    					<?php endforeach; endif; unset($_from); ?>
					<?php endif; ?>
					
					<div class="u-hide c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="volume-and-page-info"><a title="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if ($this->_tpl_vars['galley'] && $this->_tpl_vars['galley']->isHTMLGalley()): ?> href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal']))))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
"<?php else: ?>href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['articlePath']), $this);?>
"<?php endif; ?>><?php if ($this->_tpl_vars['galley'] && $this->_tpl_vars['galley']->isHTMLGalley()): ?>Article <?php elseif ($this->_tpl_vars['article']->getLocalizedAbstract()): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.abstract"), $this);?>
 <?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.details"), $this);?>
 view<?php endif; ?> <span class="fileView"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getViews())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 views</span></a></div>

                    <?php if ($this->_tpl_vars['galley']): ?>
                        <?php if ($this->_tpl_vars['galley']->isPdfGalley() && $this->_tpl_vars['galley']->isHTMLGalley()): ?>
                        <span class="total-views">Total <?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x + y + z",'x' => ((is_array($_tmp=$this->_tpl_vars['article']->getViews())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)),'y' => ((is_array($_tmp=$this->_tpl_vars['galley']->getViews($this->_tpl_vars['isPdfGalley']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)),'z' => ((is_array($_tmp=$this->_tpl_vars['galley']->getViews($this->_tpl_vars['isHTMLGalley']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this))) ? $this->_run_mod_handler('number_format', true, $_tmp, 0) : number_format($_tmp, 0));?>
 views</span>
                        <?php elseif ($this->_tpl_vars['galley']->isPdfGalley() || $this->_tpl_vars['galley']->isHTMLGalley()): ?>
                        <span class="total-views">Total <?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x + y",'x' => ((is_array($_tmp=$this->_tpl_vars['article']->getViews())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)),'y' => ((is_array($_tmp=$this->_tpl_vars['galley']->getViews())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this))) ? $this->_run_mod_handler('number_format', true, $_tmp, 0) : number_format($_tmp, 0));?>
 views</span>
                        <?php else: ?>
                        <span class="total-views">Total <?php echo ((is_array($_tmp=smarty_function_math(array('equation' => "x + y + z",'x' => ((is_array($_tmp=$this->_tpl_vars['article']->getViews())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)),'y' => ((is_array($_tmp=$this->_tpl_vars['galley']->getViews($this->_tpl_vars['isPdfGalley']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)),'z' => ((is_array($_tmp=$this->_tpl_vars['galley']->getViews($this->_tpl_vars['isHTMLGalley']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this))) ? $this->_run_mod_handler('number_format', true, $_tmp, 0) : number_format($_tmp, 0));?>
 views</span>
                        <?php endif; ?>
                    <?php else: ?>
                    <span class="total-views">Total <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getViews())) ? $this->_run_mod_handler('number_format', true, $_tmp, 0) : number_format($_tmp, 0)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 views</span>
                    <?php endif; ?>
					
					<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="volume-and-page-info"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.vol"), $this);?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
, <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.no"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['article']->getPages()): ?>, P: <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPages())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>Article <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></div>
				</div>
			</article>
		</div>
	</li>

<?php else: ?>

	<li class="app-article-list-row__item wizdam-last">
		<div class="u-full-height" data-native-ad-placement="false">
			<article class="u-full-height c-card c-card--flush" itemtype="http://schema.org/ScholarlyArticle">
				<div class="c-card__layout u-full-heights">
					<?php if ($this->_tpl_vars['showCoverPage']): ?>
					<div class="c-card__image">
						<picture>
                        <?php if ($this->_tpl_vars['currentJournal']): ?>
							<source type="image/webp" srcset="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 160w,<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 290w">
							<img class="lazyload" src="<?php echo $this->_tpl_vars['publicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if ($this->_tpl_vars['article']->getLocalizedCoverPageAltText() != ''): ?> alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedCoverPageAltText())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php else: ?> alt="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.coverPage.altText"), $this);?>
"<?php endif; ?> itemprop="image">
                        <?php else: ?>
                                                        <source type="image/webp" srcset="<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/journals/<?php echo $this->_tpl_vars['article']->getJournalId(); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 160w,<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/journals/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getJournalId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
?as=webp 290w">
                            <img src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/public/journals/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getJournalId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedFileName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedCoverPageAltText())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="image">
                        <?php endif; ?>
						</picture>
					</div>
					<?php endif; ?>
					<?php echo $this->_plugins['function']['call_hook'][0][0]->smartyCallHook(array('name' => "Templates::Issue::Issue::ArticleCoverImage"), $this);?>

					
					<div class="c-card__body u-display-flex u-flex-direction-column">
						<h3 class="c-card__title" itemprop="name headline">
							<a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath(),'page' => 'article','op' => 'view','path' => $this->_tpl_vars['article']->getBestArticleId()), $this);?>
" class="c-card__link u-link-inherit" itemprop="url" data-track="click" data-track-action="view article" data-track-label="link"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)); ?>
</a>
						</h3>
						<?php if ($this->_tpl_vars['article']->getLocalizedAbstract()): ?>
						<div class="c-card__summary abstract-content u-mb-16 u-hide-sm-max" itemprop="description"><p><?php if ($this->_tpl_vars['showCoverPage']): ?><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 170, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 170, "...")); ?>
<?php else: ?><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedAbstract())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 270, "...") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 270, "...")); ?>
<?php endif; ?></p></div>
						<?php endif; ?>
						<ul class="c-author-list c-author-list--compact c-author-list--separated u-mt-auto" data-test="author-list"><?php $this->assign('authors', $this->_tpl_vars['article']->getAuthors()); ?><?php $_from = $this->_tpl_vars['authors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['authors'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['authors']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['author']):
        $this->_foreach['authors']['iteration']++;
?><?php $this->assign('fullname', $this->_tpl_vars['author']->getFullName()); ?><?php $this->assign('authorFirstName', $this->_tpl_vars['author']->getFirstName()); ?><?php $this->assign('authorMiddleName', $this->_tpl_vars['author']->getMiddleName()); ?><?php $this->assign('authorLastName', $this->_tpl_vars['author']->getLastName()); ?><li itemprop="creator" itemtype="http://schema.org/Person"><?php if ($this->_tpl_vars['fullname']): ?><span class="u-hide" itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['fullname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['authorFirstName'] !== $this->_tpl_vars['authorLastName']): ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorFirstName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['authorMiddleName']): ?><span itemprop="name"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['authorMiddleName'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 1, ".") : $this->_plugins['modifier']['truncate'][0][0]->smartyTruncate($_tmp, 1, ".")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span itemprop="name"><?php echo ((is_array($_tmp=$this->_tpl_vars['authorLastName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?></li><?php endforeach; endif; unset($_from); ?>
						</ul>
					</div>
				</div>
				<div class="c-card__section c-meta">
					<?php if ($this->_tpl_vars['issue']->getPublished() && $this->_tpl_vars['section'] && $this->_tpl_vars['journal']): ?>
					<span class="c-meta__item c-meta__item--block-at-lg" data-test="article.type">
						<span class="c-meta__type"><?php echo ((is_array($_tmp=$this->_tpl_vars['section']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
					</span>
					<?php endif; ?>
					
                    <?php if ($this->_tpl_vars['article']->getAccessStatus() == @ARTICLE_ACCESS_OPEN): ?>
                        <span class="c-meta__item c-meta__item--block-at-lg 1" itemprop="openAccess" data-test="open-access">
                            <span class="u-color-open-access">Open Access</span>
                        </span>
                    <?php elseif ($this->_tpl_vars['issue'] && $this->_tpl_vars['issue']->getAccessStatus() == @ISSUE_ACCESS_OPEN): ?>
                        <span class="c-meta__item c-meta__item--block-at-lg 2" itemprop="openAccess" data-test="open-access">
                            <span class="u-color-open-access">Open Access</span>
                        </span>
                    <?php elseif ($this->_tpl_vars['currentJournal'] && $this->_tpl_vars['currentJournal']->getSetting('publishingMode') == @PUBLISHING_MODE_OPEN): ?>
                        <span class="c-meta__item c-meta__item--block-at-lg 3" itemprop="openAccess" data-test="open-access">
                            <span class="u-color-open-access">Open Access</span>
                        </span>
                    <?php endif; ?>
					
					<time class="c-meta__item c-meta__item--block-at-lg" datetime="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="datePublished"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getDatePublished())) ? $this->_run_mod_handler('date_format', true, $_tmp, '%e %b %Y') : smarty_modifier_date_format($_tmp, '%e %b %Y')))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</time>
					
    				<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg u-text-bold" data-test="journal-title-and-link"><a title="Go to <?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('journal' => $this->_tpl_vars['journal']->getPath()), $this);?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['journal']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></div>
    
    				<?php $this->assign('doi', $this->_tpl_vars['article']->getStoredPubId('doi')); ?>
    				<?php if ($this->_tpl_vars['article']->getPubId('doi')): ?>
    				<div class="u-hide c-meta__item c-meta__item--block-at-lg" data-test="info-DOI"><a title="Permanent link for <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="http://doi.org/<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
">DOI: <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPubId('doi'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></div>
    				<?php endif; ?>
					
    				<?php $_from = $this->_tpl_vars['article']->getGalleys(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['galleyList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['galleyList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['galley']):
        $this->_foreach['galleyList']['iteration']++;
?>
    				<?php if ($this->_tpl_vars['galley']->isPdfGalley() && $this->_tpl_vars['hasAccess'] || ( $this->_tpl_vars['subscriptionRequired'] && $this->_tpl_vars['showGalleyLinks'] )): ?>
    				<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="volume-and-page-info"><a title="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['articlePath'])) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal']))))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
" <?php if ($this->_tpl_vars['galley']->getRemoteURL()): ?>target="_blank" <?php endif; ?>class="file">Download <span class="fileSize">(<?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getLabel())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
, <?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getNiceFileSize())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
)</span> <span class="fileView"><?php echo ((is_array($_tmp=$this->_tpl_vars['galley']->getViews())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 views</span></a>
    				</div>
    				<?php endif; ?>
    				<?php endforeach; endif; unset($_from); ?>
    
    				<div class="u-hide c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="volume-and-page-info"><a title="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getLocalizedTitle())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"<?php if ($this->_tpl_vars['galley'] && $this->_tpl_vars['galley']->isHTMLGalley()): ?> href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId($this->_tpl_vars['currentJournal']))) ? $this->_run_mod_handler('to_array', true, $_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal'])) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp, $this->_tpl_vars['galley']->getBestGalleyId($this->_tpl_vars['currentJournal']))))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
"<?php else: ?>href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'article','op' => 'view','path' => $this->_tpl_vars['articlePath']), $this);?>
"<?php endif; ?>><?php if ($this->_tpl_vars['galley'] && $this->_tpl_vars['galley']->isHTMLGalley()): ?>Article <?php elseif ($this->_tpl_vars['article']->getLocalizedAbstract()): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.abstract"), $this);?>
 <?php else: ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "article.details"), $this);?>
 view<?php endif; ?> <span class="fileView"><?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getViews())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 views</span></a>
    				</div>
					
					<div class="c-meta__item c-meta__item--block-at-lg u-show-lg u-show-at-lg" data-test="volume-and-page-info"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.vol"), $this);?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
, <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.no"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getNumber())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['article']->getPages()): ?>, P: <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getPages())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php else: ?>Article <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getBestArticleId())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
					</div>
				</div>
			</article>
		</div>
	</li>
	<?php endif; ?>

	<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
</ul>
            </div>
            <!-- Result list -->
        </div>
    </div>    
</section>

<?php if ($this->_tpl_vars['user']->getScopusId()): ?>
<div class="live-area-wrapper">
    <div class="u-row row">
        <div class="column">
            <!-- Elemen untuk daftar artikel -->
            <div class="articles-list" id="scopus-articles">
                <div data-test="title" class="anchored">
                    <h3 class="anchored"><span class="content-break">Article author in Scopus</span></h3>
                </div>
                <div class="scopus-article-detail" itemprop="scopus"></div>
            </div>
        </div>
<?php endif; ?>
    </div>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-profile.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>