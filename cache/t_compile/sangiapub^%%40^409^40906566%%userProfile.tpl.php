<?php /* Smarty version 2.6.26, created on 2026-04-29 17:49:51
         compiled from sectionEditor/userProfile.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'sectionEditor/userProfile.tpl', 16, false),array('modifier', 'substr', 'sectionEditor/userProfile.tpl', 30, false),array('modifier', 'concat', 'sectionEditor/userProfile.tpl', 96, false),array('modifier', 'to_array', 'sectionEditor/userProfile.tpl', 97, false),array('modifier', 'assign', 'sectionEditor/userProfile.tpl', 97, false),array('modifier', 'strip_unsafe_html', 'sectionEditor/userProfile.tpl', 128, false),array('modifier', 'nl2br', 'sectionEditor/userProfile.tpl', 128, false),array('modifier', 'explode', 'sectionEditor/userProfile.tpl', 138, false),array('modifier', 'trim', 'sectionEditor/userProfile.tpl', 142, false),array('modifier', 'date_format', 'sectionEditor/userProfile.tpl', 220, false),array('function', 'translate', 'sectionEditor/userProfile.tpl', 18, false),array('function', 'url', 'sectionEditor/userProfile.tpl', 97, false),array('function', 'icon', 'sectionEditor/userProfile.tpl', 98, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "manager.people"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-USER027.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<h3 id="userFullName"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h3>

<h4><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.profile"), $this);?>
</h4>

<div id="profile" class="page">
	<div class="profile-header">
		<div class="profile-avatar">
						<?php $this->assign('profileImage', $this->_tpl_vars['user']->getSetting('profileImage')); ?>
			
						<?php if ($this->_tpl_vars['profileImage'] && $this->_tpl_vars['profileImage']['uploadName']): ?>
				<img src="<?php echo $this->_tpl_vars['sitePublicFilesDir']; ?>
/<?php echo $this->_tpl_vars['profileImage']['uploadName']; ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
" class="profile-photo" />
			<?php else: ?>
				<span class="profile-initials"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getFirstName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 1) : substr($_tmp, 0, 1)); ?>
<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 1) : substr($_tmp, 0, 1)); ?>
</span>
			<?php endif; ?>
		</div>
		<div class="profile-main-info">
			<h2><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</h2>
			<?php if ($this->_tpl_vars['user']->getLocalizedAffiliation()): ?>
				<p class="affiliation"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getLocalizedAffiliation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</p>
			<?php endif; ?>
		</div>
	</div>

	<div class="profile-sections">
		
		<div class="section u-mb-48">
			<h3 class="section-title">Personal Information</h3>
			<div class="field-list">
				<?php if ($this->_tpl_vars['user']->getSalutation()): ?>
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.salutation"), $this);?>
:</span>
					<span class="field-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getSalutation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				</div>
				<?php endif; ?>
				
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.username"), $this);?>
:</span>
					<span class="field-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getUsername())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				</div>
				
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.firstName"), $this);?>
:</span>
					<span class="field-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFirstName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				</div>
				
				<?php if ($this->_tpl_vars['user']->getMiddleName()): ?>
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.middleName"), $this);?>
:</span>
					<span class="field-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getMiddleName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				</div>
				<?php endif; ?>
				
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.lastName"), $this);?>
:</span>
					<span class="field-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getLastName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				</div>
				
				<?php if ($this->_tpl_vars['user']->getGender()): ?>
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.gender"), $this);?>
:</span>
					<span class="field-value">
						<?php if ($this->_tpl_vars['user']->getGender() == 'M'): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.masculine"), $this);?>

						<?php elseif ($this->_tpl_vars['user']->getGender() == 'F'): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.feminine"), $this);?>

						<?php elseif ($this->_tpl_vars['user']->getGender() == 'O'): ?><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.other"), $this);?>

						<?php endif; ?>
					</span>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="section u-mb-48">
			<h3 class="section-title">Contact Information</h3>
			<div class="field-list">
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.email"), $this);?>
:</span>
					<span class="field-value">
						<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getEmail())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

						<?php $this->assign('emailString', ((is_array($_tmp=$this->_tpl_vars['user']->getFullName())) ? $this->_run_mod_handler('concat', true, $_tmp, " <", $this->_tpl_vars['user']->getEmail(), ">") : $this->_plugins['modifier']['concat'][0][0]->smartyConcat($_tmp, " <", $this->_tpl_vars['user']->getEmail(), ">"))); ?>
						<?php echo ((is_array($_tmp=$this->_plugins['function']['url'][0][0]->smartyUrl(array('page' => 'user','op' => 'email','to' => ((is_array($_tmp=$this->_tpl_vars['emailString'])) ? $this->_run_mod_handler('to_array', true, $_tmp) : $this->_plugins['modifier']['to_array'][0][0]->smartyToArray($_tmp)),'redirectUrl' => $this->_tpl_vars['currentUrl']), $this))) ? $this->_run_mod_handler('assign', true, $_tmp, 'url') : $this->_plugins['modifier']['assign'][0][0]->smartyAssign($_tmp, 'url'));?>

						<a href="<?php echo $this->_tpl_vars['url']; ?>
" class="email-action"><?php echo $this->_plugins['function']['icon'][0][0]->smartyIcon(array('name' => 'mail'), $this);?>
</a>
					</span>
				</div>
				
				<?php if ($this->_tpl_vars['user']->getUrl()): ?>
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.url"), $this);?>
:</span>
					<span class="field-value">
						<a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'quotes')); ?>
" target="_blank"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getUrl())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a>
					</span>
				</div>
				<?php endif; ?>
				
				<?php if ($this->_tpl_vars['user']->getPhone()): ?>
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.phone"), $this);?>
:</span>
					<span class="field-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getPhone())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				</div>
				<?php endif; ?>
				
				<?php if ($this->_tpl_vars['user']->getFax()): ?>
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.fax"), $this);?>
:</span>
					<span class="field-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getFax())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				</div>
				<?php endif; ?>
				
				<?php if ($this->_tpl_vars['user']->getMailingAddress()): ?>
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.mailingAddress"), $this);?>
:</span>
					<span class="field-value"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getMailingAddress())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</span>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ($this->_tpl_vars['user']->getLocalizedAffiliation()): ?>
		<div class="section u-mb-48">
			<h3 class="section-title">Affiliation</h3>
			<div class="field-list">
				<?php $this->assign('affiliations', ((is_array($_tmp=$this->_tpl_vars['user']->getLocalizedAffiliation())) ? $this->_run_mod_handler('explode', true, $_tmp, "\n") : $this->_plugins['modifier']['explode'][0][0]->smartyExplode($_tmp, "\n"))); ?>
				
				<?php if (count ( $this->_tpl_vars['affiliations'] ) > 1): ?>
					<?php $_from = $this->_tpl_vars['affiliations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['affiliationLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['affiliationLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['affiliation']):
        $this->_foreach['affiliationLoop']['iteration']++;
?>
						<?php if (((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp))): ?>
							<div class="field-item">
								<span class="field-label">Affiliation <?php echo $this->_foreach['affiliationLoop']['iteration']; ?>
:</span>
								<span class="field-value">
									<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['affiliation'])) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if (($this->_foreach['affiliationLoop']['iteration'] == $this->_foreach['affiliationLoop']['total']) && $this->_tpl_vars['country']): ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['country'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
								</span>
							</div>
						<?php endif; ?>
					<?php endforeach; endif; unset($_from); ?>
				<?php else: ?>
					<div class="field-item">
						<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.affiliation"), $this);?>
:</span>
						<span class="field-value">
							<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getLocalizedAffiliation())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php if ($this->_tpl_vars['country']): ?>, <?php echo ((is_array($_tmp=$this->_tpl_vars['country'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
<?php endif; ?>
						</span>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['user']->getLocalizedSignature() || $this->_tpl_vars['userInterests']): ?>
		<div class="section u-mb-48">
			<h3 class="section-title">Academic Information</h3>
			<div class="field-list">
				<?php if ($this->_tpl_vars['user']->getLocalizedSignature()): ?>
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.signature"), $this);?>
:</span>
					<span class="field-value"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getLocalizedSignature())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</span>
				</div>
				<?php endif; ?>
				
				<?php if ($this->_tpl_vars['userInterests']): ?>
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.interests"), $this);?>
:</span>
					<span class="field-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['userInterests'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['user']->getLocales()): ?>
		<div class="section u-mb-48">
			<h3 class="section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.workingLanguages"), $this);?>
</h3>
			<div class="languages-container">
				<?php $_from = $this->_tpl_vars['user']->getLocales(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['workingLanguages'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['workingLanguages']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['localeKey']):
        $this->_foreach['workingLanguages']['iteration']++;
?>
					<span class="language-tag"><?php echo ((is_array($_tmp=$this->_tpl_vars['localeNames'][$this->_tpl_vars['localeKey']])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				<?php endforeach; endif; unset($_from); ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['user']->getLocalizedBiography()): ?>
		<div class="section u-mb-48 biography-section">
			<h3 class="section-title"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.biography"), $this);?>
</h3>
			<div class="biography-content">
				<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['user']->getLocalizedBiography())) ? $this->_run_mod_handler('strip_unsafe_html', true, $_tmp) : PKPString::stripUnsafeHtml($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

			</div>
		</div>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['user']->getLocalizedGossip()): ?>
		<div class="section u-mb-48">
			<h3 class="section-title">Additional Information</h3>
			<div class="field-list">
				<div class="field-item">
					<span class="field-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.gossip"), $this);?>
:</span>
					<span class="field-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getLocalizedGossip())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</span>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<div class="section u-mb-48 system-section">
			<h3 class="section-title">Account Information</h3>
			<div class="system-info">
				<div class="system-item">
					<span class="system-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.dateRegistered"), $this);?>
:</span>
					<span class="system-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getDateRegistered())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatLong']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatLong'])); ?>
</span>
				</div>
				<div class="system-item">
					<span class="system-label"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "user.dateLastLogin"), $this);?>
:</span>
					<span class="system-value">
						<?php if ($this->_tpl_vars['user']->getDateLastLogin()): ?>
							<?php echo ((is_array($_tmp=$this->_tpl_vars['user']->getDateLastLogin())) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['datetimeFormatLong']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['datetimeFormatLong'])); ?>

						<?php else: ?>
							<em>Never</em>
						<?php endif; ?>
					</span>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer-parts/footer-user.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>