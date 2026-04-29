<?php /* Smarty version 2.6.26, created on 2026-04-04 05:48:04
         compiled from article/editorial.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', 'article/editorial.tpl', 14, false),array('modifier', 'escape', 'article/editorial.tpl', 30, false),array('modifier', 'string_format', 'article/editorial.tpl', 45, false),array('modifier', 'nl2br', 'article/editorial.tpl', 51, false),array('modifier', 'date_format', 'article/editorial.tpl', 76, false),array('function', 'translate', 'article/editorial.tpl', 16, false),array('function', 'url', 'article/editorial.tpl', 45, false),)), $this); ?>

<?php if ($this->_tpl_vars['editAssignments'] && count($this->_tpl_vars['editAssignments']) > 0): ?>
<h3 class="u-h4 article-span u-mb-8 u-font-sans-sang">
    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.article.editors"), $this);?>

</h3>

<div id="editors" class="p-separator cms-person overview">
    <ul class="app-editor-row">
    <?php $_from = $this->_tpl_vars['editAssignments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['assignment']):
?>
                <?php $this->assign('editor', $this->_tpl_vars['assignment']->getData('editorUser')); ?>
        
        <li class="app-editor-row__item">
            <div class="editor">
                <div class="editor-name cms-person">
                    <?php if ($this->_tpl_vars['editor']->getProfilePictureName()): ?>
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img class="Avatar__img is-inside-mask" src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getProfilePictureName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" alt="Profile Picture" width="50" height="50">
                    </figure>
                    <?php else: ?>
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img class="Avatar__img is-inside-mask" 
                            <?php if ($this->_tpl_vars['editor']->getGender() == 'M'): ?>src="//assets.sangia.org/static/images/contactPersonM.png"
                            <?php elseif ($this->_tpl_vars['editor']->getGender() == 'F'): ?>src="//assets.sangia.org/static/images/contactPersonF.png"
                            <?php elseif ($this->_tpl_vars['editor']->getGender() == 'O'): ?>src="//assets.sangia.org/static/images/default_203.jpg"
                            <?php else: ?>src="//scholar.google.co.id/citations/images/avatar_scholar_128.png"<?php endif; ?> 
                            alt="Profile Picture" width="50" height="50">
                    </figure>
                    <?php endif; ?>

                                        <h3 class="u-h4 u-fonts-sans u-mb-4" itemprop="name">
                        <a class="button-link author size-m workspace-trigger button-link-primary" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => "editorial-team-bio",'path' => ((is_array($_tmp=$this->_tpl_vars['editor']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")),'from' => 'board','anchor' => ((is_array($_tmp=$this->_tpl_vars['editor']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
"><?php if ($this->_tpl_vars['editor']->getSalutation()): ?><span class="text degree" itemprop="degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getSalutation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['editor']->getFirstName() !== $this->_tpl_vars['editor']->getLastName()): ?><span class="text given-name" itemprop="given-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getFirstName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['editor']->getMiddleName()): ?><span class="text middle-name" itemprop="middle-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span class="text surname" itemprop="surname"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['editor']->getSuffix()): ?><span class="last-degree" itemprop="last-degree">, <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getSuffix())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
                        </a>
                    </h3>

                                        <?php if ($this->_tpl_vars['editor']->getAffiliation($this->_tpl_vars['locale'])): ?>
                        <dl><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['editor']->getAffiliation($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
<?php if ($this->_tpl_vars['editor']->getCountryName()): ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getCountryName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></dl>
                    <?php endif; ?>

                    <p class="u-js-hide u-mb-0">
                                                <a rel="noreferrer noopener" class="icon anchor" title="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getEmail())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" href="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getEmail())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" target="_blank">
                            <span class="anchor-text"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getEmail())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                        </a>
                    </p>

                    <dl class="u-js-hide">
                        <?php if ($this->_tpl_vars['editor']->getUrl()): ?>
                            <p class="u-mb-0">URL Personal: <a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></p>
                        <?php endif; ?>
                                                <?php if ($this->_tpl_vars['editor']->getData('sintaId')): ?>
                            <p class="u-mb-0">Sinta ID: <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getData('sintaId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
                        <?php endif; ?>
                        <?php if ($this->_tpl_vars['editor']->getData('scopusId')): ?>
                            <p class="u-mb">Scopus ID: <?php echo ((is_array($_tmp=$this->_tpl_vars['editor']->getData('scopusId'))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
                        <?php endif; ?>
                    </dl>
                    
                                        <div class="action-dates u-font-size-small u-mt-8 u-js-hide" itemprop="assignment">
                        <time datetime="<?php echo ((is_array($_tmp=$this->_tpl_vars['assignment']->getDateNotified())) ? $this->_run_mod_handler('date_format', true, $_tmp, ($this->_tpl_vars['dateFormatShort'])) : smarty_modifier_date_format($_tmp, ($this->_tpl_vars['dateFormatShort']))); ?>
" itemprop="date-assignment" >Assigned: <?php echo ((is_array($_tmp=$this->_tpl_vars['assignment']->getDateNotified())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
</time>
                    </div>
                </div>
            </div>
        </li>
    <?php endforeach; endif; unset($_from); ?>
    </ul>
</div>
<?php else: ?>
<div id="edited" class="p-separator overview">
    <h3 class="u-h4 u-mb-8 u-font-sans">
        <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.noEditorsAssigned"), $this);?>

    </h3>
    <?php if ($this->_tpl_vars['article']->getLastModified()): ?>
    <p class="action-dates u-mb-0">
        <time datetime="<?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLastModified())) ? $this->_run_mod_handler('date_format', true, $_tmp, ($this->_tpl_vars['dateFormatShort'])) : smarty_modifier_date_format($_tmp, ($this->_tpl_vars['dateFormatShort']))); ?>
" itemprop="last-modified"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "submission.lastModified"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['article']->getLastModified())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
</time>
    </p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['reviewAssignments'] && count($this->_tpl_vars['reviewAssignments']) > 0): ?>
<h3 class="u-h4 article-span u-mb-8 u-font-sans-sang">
    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.article.reviewed"), $this);?>

</h3>

<div id="reviewers" class="p-separator cms-person overview">
    <ul class="app-reviewer-row">
    <?php $_from = $this->_tpl_vars['reviewAssignments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['assignment']):
?>
                <?php $this->assign('reviewer', $this->_tpl_vars['assignment']->getData('reviewerUser')); ?>
        
        <li class="app-reviewer-row__item">
            <div class="reviewer">
                <div class="reviewer-name cms-person">
                    <?php if ($this->_tpl_vars['reviewer']->getProfilePictureName()): ?>
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img class="Avatar__img is-inside-mask" src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['reviewer']->getProfilePictureName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" alt="Profile Picture" width="50" height="50">
                    </figure>
                    <?php else: ?>
                    <figure class="avatar editor Avatar Avatar--size-60">
                        <img class="Avatar__img is-inside-mask" 
                            <?php if ($this->_tpl_vars['reviewer']->getGender() == 'M'): ?>src="//assets.sangia.org/static/images/contactPersonM.png"
                            <?php elseif ($this->_tpl_vars['reviewer']->getGender() == 'F'): ?>src="//assets.sangia.org/static/images/contactPersonF.png"
                            <?php else: ?>src="//scholar.google.co.id/citations/images/avatar_scholar_128.png"<?php endif; ?> 
                            alt="Profile Picture" width="50" height="50">
                    </figure>
                    <?php endif; ?>

                    <h3 class="u-h4 u-mb-4 u-mt-0">
                        <a class="button-link author size-m workspace-trigger button-link-primary" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'editorialTeamBio','path' => ((is_array($_tmp=$this->_tpl_vars['reviewer']->getId())) ? $this->_run_mod_handler('string_format', true, $_tmp, "%011d") : smarty_modifier_string_format($_tmp, "%011d")),'from' => 'membership','anchor' => ((is_array($_tmp=$this->_tpl_vars['reviewer']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp))), $this);?>
"><?php if ($this->_tpl_vars['reviewer']->getSalutation()): ?><span class="text degree" itemprop="degree"><?php echo ((is_array($_tmp=$this->_tpl_vars['reviewer']->getSalutation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['reviewer']->getFirstName() !== $this->_tpl_vars['reviewer']->getLastName()): ?><span class="text given-name" itemprop="given-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['reviewer']->getFirstName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><?php if ($this->_tpl_vars['reviewer']->getMiddleName()): ?><span class="text middle-name" itemprop="middle-name"><?php echo ((is_array($_tmp=$this->_tpl_vars['reviewer']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?><span class="text surname" itemprop="surname"><?php echo ((is_array($_tmp=$this->_tpl_vars['reviewer']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php if ($this->_tpl_vars['reviewer']->getSuffix()): ?><span class="last-degree" itemprop="last-degree">, <?php echo ((is_array($_tmp=$this->_tpl_vars['reviewer']->getSuffix())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span><?php endif; ?>
                        </a>
                    </h3>

                    <?php if ($this->_tpl_vars['reviewer']->getAffiliation($this->_tpl_vars['locale'])): ?>
                        <dl><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['reviewer']->getAffiliation($this->_tpl_vars['locale']))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
<?php if ($this->_tpl_vars['reviewer']->getCountryName()): ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['reviewer']->getCountryName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?></dl>
                    <?php endif; ?>
                    
                                        <div class="action-dates u-font-size-small u-mt-8 u-js-hide"  itemprop="assignment">
                        <time datetime="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['assignment']->getDateCompleted())) ? $this->_run_mod_handler('date_format', true, $_tmp, ($this->_tpl_vars['dateFormatShort'])) : smarty_modifier_date_format($_tmp, ($this->_tpl_vars['dateFormatShort']))))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" itemprop="date-assignment">Review Completed: <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['assignment']->getDateCompleted())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</time>
                    </div>
                </div>
            </div>
        </li>
    <?php endforeach; endif; unset($_from); ?>
    </ul>
</div>
<?php endif; ?>