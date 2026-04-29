<?php /* Smarty version 2.6.26, created on 2026-04-04 05:39:07
         compiled from common/breadcrumbs.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'common/breadcrumbs.tpl', 20, false),array('function', 'url', 'common/breadcrumbs.tpl', 27, false),array('modifier', 'strip_tags', 'common/breadcrumbs.tpl', 27, false),array('modifier', 'escape', 'common/breadcrumbs.tpl', 27, false),)), $this); ?>
<div id="breadcrumb" class="u-show-at-md u-hide-sm-max">
<div class="row">
    <div class="columns">
    
        <?php if ($this->_tpl_vars['currentJournal']): ?>
    <a href="//www.sangia.org">sangia.org</a> 
    <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
    <?php else: ?>
    <a href="<?php echo $this->_tpl_vars['baseUrl']; ?>
"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "navigation.home"), $this);?>
</a> 
    <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
    <?php endif; ?>
    
        <?php if ($this->_tpl_vars['currentJournal']): ?>
        <?php if ($this->_tpl_vars['currentJournal']->getLocalizedInitials()): ?>
        <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => ($this->_tpl_vars['currentJournal'])), $this);?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['currentJournal']->getLocalizedInitials())) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a> 
        <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
        <?php else: ?>
        <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => ($this->_tpl_vars['currentJournal'])), $this);?>
">Journal Initials</a> 
        <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
        <?php endif; ?>
    <?php endif; ?>

        <?php $_from = $this->_tpl_vars['pageHierarchy']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['hierarchyLink']):
?>
        
                <?php if (isset ( $this->_tpl_vars['hierarchyLink']['url'] )): ?>
                                    <a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['hierarchyLink']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="hierarchyLink">
                <?php echo ((is_array($_tmp=$this->_tpl_vars['hierarchyLink']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

            </a>
        <?php else: ?>
                                    <a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['hierarchyLink'][0])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="hierarchyLink">
                <?php if (! $this->_tpl_vars['hierarchyLink'][2]): ?>
                    <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => $this->_tpl_vars['hierarchyLink'][1]), $this);?>
                 <?php else: ?>
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['hierarchyLink'][1])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
                 <?php endif; ?>
            </a>
        <?php endif; ?>

                <svg class="c-breadcrumbs__chevron" role="img" aria-hidden="true" focusable="false" height="10" viewBox="0 0 10 10" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m5.96738168 4.70639573 2.39518594-2.41447274c.37913917-.38219212.98637524-.38972225 1.35419292-.01894278.37750606.38054586.37784436.99719163-.00013556 1.37821513l-4.03074001 4.06319683c-.37758093.38062133-.98937525.38100976-1.367372-.00003075l-4.03091981-4.06337806c-.37759778-.38063832-.38381821-.99150444-.01600053-1.3622839.37750607-.38054587.98772445-.38240057 1.37006824.00302197l2.39538588 2.4146743.96295325.98624457z" fill="#666" fill-rule="evenodd" transform="matrix(0 -1 1 0 0 10)"></path></svg>
    <?php endforeach; endif; unset($_from); ?>

        <?php if ($this->_tpl_vars['requiresFormRequest']): ?>
        <span class="current">
    <?php else: ?>
        <span href="<?php echo ((is_array($_tmp=$this->_tpl_vars['currentUrl'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="current">
    <?php endif; ?>
        <?php echo $this->_tpl_vars['pageCrumbTitleTranslated']; ?>

    </span>

    </div>
</div>
</div>