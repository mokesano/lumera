<?php /* Smarty version 2.6.26, created on 2026-04-04 15:08:06
         compiled from issue/archive.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'url', 'issue/archive.tpl', 21, false),array('function', 'translate', 'issue/archive.tpl', 21, false),array('function', 'native_url', 'issue/archive.tpl', 54, false),array('block', 'iterate', 'issue/archive.tpl', 46, false),array('modifier', 'escape', 'issue/archive.tpl', 50, false),array('modifier', 'date_format', 'issue/archive.tpl', 52, false),)), $this); ?>

<?php echo ''; ?><?php $this->assign('pageTitle', "archive.archives"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ISSUE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php if ($this->_tpl_vars['currentJournal']->getLocalizedSetting('history') != ''): ?>
<div id="journal-history-link" class="content">
    <span class="icon-container info history-link">
        This journal was previously published under other titles
        <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'history','anchor' => ""), $this);?>
">(view Journal <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.history"), $this);?>
)</a>
    </span>
</div>
<?php endif; ?>

<div class="content mb30 mq1200-padded position-relative">
    <section>
                <?php if ($this->_tpl_vars['issues']->wasEmpty()): ?>
            <div class="container cleared container-type-title" data-container-type="title">
                <div class="border-top-1 border-gray-medium"></div>
                <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
                    <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center">
                        <svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
                    </div>
                    <div class="c-empty-state-card__text">
                        <h3 class="c-empty-state-card__text--title headline-5"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "current.noCurrentIssueDesc"), $this);?>
</h3>
                        <div class="c-empty-state-card__text--description">We are currently preparing our inaugural content. Please check back soon for our upcoming publications, or consider <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'author','op' => 'submit'), $this);?>
">submitting your manuscript</a> to be part of our first issue. Visit our <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'about','op' => 'submissions'), $this);?>
">Submission Guidelines</a> for more information.</div>
                    </div>
                </div>
            </div>
        <?php else: ?>
                        <div class="pa40 cleared background-white">
                <ul id="volume-decade-list" class="clean-list ma0 grid-auto-fill medium-row-gap background-white" style="--column-width: 120px;">
                    <?php $this->_tag_stack[] = array('iterate', array('from' => 'issues','item' => 'issue')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
                        <?php if ($this->_tpl_vars['issue']->getYear() != $this->_tpl_vars['lastYear']): ?>
                            <?php $this->assign('lastYear', $this->_tpl_vars['issue']->getYear()); ?>
                            <li>
                                <div id="<?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="volumes-detail">
                                    <h2 class="mb6 strong tighten-line-height text-gray volumes-detail__year">
                                        <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getDatePublished('issue.firstYear'))) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>

                                    </h2>
                                    <a href="<?php echo $this->_plugins['function']['native_url'][0][0]->smartyNativeUrl(array('page' => 'volume','volume' => $this->_tpl_vars['issue']->getVolume()), $this);?>
" class="volumes-detail__link">
                                        <span class="title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "issue.volume"), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['issue']->getVolume())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
                                    </a>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                </ul>
            </div>
        <?php endif; ?>
    </section>
</div>

</main>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>