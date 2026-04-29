<?php /* Smarty version 2.6.26, created on 2026-04-04 06:42:25
         compiled from about/contact.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'about/contact.tpl', 26, false),array('modifier', 'nl2br', 'about/contact.tpl', 49, false),array('function', 'translate', 'about/contact.tpl', 36, false),array('function', 'mailto', 'about/contact.tpl', 42, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "about.journalContact"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ABOUT.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<?php if ($this->_tpl_vars['currentJournal']): ?>
<section id="contact" class="collection block">
    
    <?php if (! ( $this->_tpl_vars['currentJournal']->getLocalizedSetting('contactTitle') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('contactAffiliation') == '' && $this->_tpl_vars['currentJournal']->getLocalizedSetting('contactMailingAddress') == '' && empty ( $this->_tpl_vars['journalSettings']['contactPhone'] ) && empty ( $this->_tpl_vars['journalSettings']['contactFax'] ) && empty ( $this->_tpl_vars['journalSettings']['contactEmail'] ) )): ?>
    <section id="principalContact" class="collection">
        <h3>Publishing Contact</h3>
        <p>General questions about the journal, pre-submission queries, editorial policy or procedure, or special issue proposals.</p>
        
        <?php if (! empty ( $this->_tpl_vars['journalSettings']['contactName'] )): ?>
        <section class="collection-data">
        	<h4><?php echo ((is_array($_tmp=$this->_tpl_vars['journalSettings']['contactName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h4>
        </section>
        <?php endif; ?>
        
    	<section class="collection-data">
        	<p>
        	<?php $this->assign('title', $this->_tpl_vars['currentJournal']->getLocalizedSetting('contactTitle')); ?>
        	<?php if ($this->_tpl_vars['title']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['title'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<br /><?php endif; ?>
        
        	<?php if (! empty ( $this->_tpl_vars['journalSettings']['contactPhone'] )): ?>
        		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact.phone"), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['journalSettings']['contactPhone'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<br />
        	<?php endif; ?>
        	<?php if (! empty ( $this->_tpl_vars['journalSettings']['contactFax'] )): ?>
        		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact.fax"), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['journalSettings']['contactFax'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<br />
        	<?php endif; ?>
        	<?php if (! empty ( $this->_tpl_vars['journalSettings']['contactEmail'] )): ?>
        		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact.email"), $this);?>
: <?php echo smarty_function_mailto(array('address' => ((is_array($_tmp=$this->_tpl_vars['journalSettings']['contactEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)),'encode' => 'hex'), $this);?>

        	<?php endif; ?></p>
        
        	<?php $this->assign('contacInstitution', $this->_tpl_vars['currentJournal']->getLocalizedSetting('contactAffiliation')); ?>
        	<?php if ($this->_tpl_vars['contacInstitution']): ?><p><?php echo ((is_array($_tmp=$this->_tpl_vars['contacInstitution'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p><?php endif; ?>
        
        	<?php $this->assign('contactAddres', $this->_tpl_vars['currentJournal']->getLocalizedSetting('contactMailingAddress')); ?>
        	<?php if ($this->_tpl_vars['contactAddres']): ?><p><?php echo ((is_array($_tmp=$this->_tpl_vars['contactAddres'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p><?php endif; ?>
        	<br />
    	</section>
    </section>
    <?php endif; ?>

    <?php if (! ( empty ( $this->_tpl_vars['journalSettings']['supportName'] ) && empty ( $this->_tpl_vars['journalSettings']['supportPhone'] ) && empty ( $this->_tpl_vars['journalSettings']['supportEmail'] ) )): ?>
    <section id="supportContact" class="collection block">
        <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact.supportContact"), $this);?>
</h3>
        <p>Questions about manuscripts already sent to production.</p>
        <section class="support-name">
        	<?php if (! empty ( $this->_tpl_vars['journalSettings']['supportName'] )): ?>
        		<h4><?php echo ((is_array($_tmp=$this->_tpl_vars['journalSettings']['supportName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h4>
        	<?php endif; ?>
        	<section class="article-body block">
        	<p>
        	<?php $this->assign('s', $this->_tpl_vars['currentJournal']->getLocalizedSetting('contactTitle')); ?>
        	<?php if ($this->_tpl_vars['s']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['s'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<br /><?php endif; ?>
        
        	<?php if (! empty ( $this->_tpl_vars['journalSettings']['supportPhone'] )): ?>
        		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact.phone"), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['journalSettings']['supportPhone'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<br />
        	<?php endif; ?>
        	<?php if (! empty ( $this->_tpl_vars['journalSettings']['supportEmail'] )): ?>
        		<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "about.contact.email"), $this);?>
: <?php echo smarty_function_mailto(array('address' => ((is_array($_tmp=$this->_tpl_vars['journalSettings']['supportEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)),'encode' => 'hex'), $this);?>
<br />
        	<?php endif; ?>
        	</p>
        	</section>
        </section>
    </section>
    <br />
    <?php endif; ?>
    
    <?php if (! empty ( $this->_tpl_vars['journalSettings']['mailingAddress'] )): ?>
    <section id="mailingAddress" class="collection">
        <h3>Editorial Office</h3>
        <p>Questions about the suitability of a topic, how to submit, manuscripts under consideration, and the online submission system (if applicable).</p>
    	<section class="collection-data block"><p><?php echo ((is_array($_tmp=$this->_tpl_vars['journalSettings']['mailingAddress'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p></section>
    </section>
    <br />
    <?php endif; ?>
    
    <?php if ($this->_tpl_vars['sitePrincipalContactName'] || $this->_tpl_vars['sitePrincipalContactEmail']): ?>
    <section class="collection block">
    	<?php if ($this->_tpl_vars['sitePrincipalContactName']): ?>
    	    <h3><?php echo ((is_array($_tmp=$this->_tpl_vars['sitePrincipalContactName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
 (Customer Service)</h3>
    	<?php endif; ?>
    	<?php if ($this->_tpl_vars['sitePrincipalContactEmail']): ?>
    	    <p><a href="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['sitePrincipalContactEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['sitePrincipalContactEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></p>
    	<?php endif; ?>
    </section>
    <?php endif; ?>

</section>
<?php else: ?>
<section class="collection block">
	<?php if ($this->_tpl_vars['sitePrincipalContactName']): ?>
	    <h3><?php echo ((is_array($_tmp=$this->_tpl_vars['sitePrincipalContactName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['siteMailingAddress']): ?>
	    <p><?php echo ((is_array($_tmp=$this->_tpl_vars['siteMailingAddress'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['sitePrincipalContactEmail']): ?>
	    <p><a href="mailto:<?php echo ((is_array($_tmp=$this->_tpl_vars['sitePrincipalContactEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['sitePrincipalContactEmail'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></p>
	<?php endif; ?>
</section>
<?php endif; ?>
        </div>
    </div>
</div>    

<div class="live-area-wrapper">
	<div class="row">
	    <div role="main" class="column">
	        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15919.707735119626!2d122.5556084!3d-4.0353334!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x59d6a213a880ac1a!2sSangia%20News%20%26%20Media!5e0!3m2!1sid!2sid!4v1658598581283!5m2!1sid!2sid" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" width="100%" height="300"></iframe>
        </div>
    
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
