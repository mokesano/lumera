<?php /* Smarty version 2.6.26, created on 2026-04-04 12:24:35
         compiled from about/journalSponsorship.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'translate', 'about/journalSponsorship.tpl', 20, false),array('modifier', 'nl2br', 'about/journalSponsorship.tpl', 24, false),array('modifier', 'escape', 'about/journalSponsorship.tpl', 30, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "about.journalSponsorship"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ABOUT.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<section class="collection">
<?php if (! ( empty ( $this->_tpl_vars['sponsorNote'] ) && empty ( $this->_tpl_vars['sponsors'] ) )): ?>
<section class="collection" id="sponsors" class="block">

    <h3><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "manager.setup.sponsors"), $this);?>
</h3>
  
  <section class="article">
    <section class="article-body">
      <?php if ($this->_tpl_vars['sponsorNote']): ?><p><?php echo ((is_array($_tmp=$this->_tpl_vars['sponsorNote'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p><?php endif; ?>

      <?php if ($this->_tpl_vars['sponsors']): ?>
      <ul>
        <?php $_from = $this->_tpl_vars['sponsors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['sponsor']):
?>
          <?php if ($this->_tpl_vars['sponsor']['url']): ?>
            <li><a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['sponsor']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['sponsor']['institution'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li>
            <?php else: ?>
            <li><?php echo ((is_array($_tmp=$this->_tpl_vars['sponsor']['institution'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
          <?php endif; ?>
        <?php endforeach; endif; unset($_from); ?>
      </ul>
      <?php endif; ?>

    </section>
  </section>
</section>
<?php endif; ?>

<?php if (! empty ( $this->_tpl_vars['contributorNote'] ) || ( ! empty ( $this->_tpl_vars['contributors'] ) && ! empty ( $this->_tpl_vars['contributors'][0]['name'] ) )): ?>
<section id="contributors" class="collection block">

    <h2>Academic Affiliation Support</h2>

    <section class="article">

      <section class="article-body">

        <?php if ($this->_tpl_vars['contributorNote']): ?><p><?php echo ((is_array($_tmp=$this->_tpl_vars['contributorNote'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p><?php endif; ?>

        <?php if ($this->_tpl_vars['contributors']): ?>
        <ul>
          <?php $_from = $this->_tpl_vars['contributors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['contributor']):
?>
            <?php if ($this->_tpl_vars['contributor']['name']): ?>
              <?php if ($this->_tpl_vars['contributor']['url']): ?>
              <li><a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['contributor']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['contributor']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a></li>
              <?php else: ?>
              <li><?php echo ((is_array($_tmp=$this->_tpl_vars['contributor']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</li>
              <?php endif; ?>
            <?php endif; ?>
          <?php endforeach; endif; unset($_from); ?>
        </ul>
        <?php endif; ?>

    </section>
  </section>
</section>
<?php endif; ?>

<?php if (! ( empty ( $this->_tpl_vars['publisherNote'] ) && empty ( $this->_tpl_vars['publisherInstitution'] ) )): ?>
<section id="publisher" class="collection block">
  
  <h2><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.publisher"), $this);?>
</h2>
  
  <section class="article">
    <section class="article-body">
      <p><?php if ($this->_tpl_vars['publisherInstitution'] == 'Sekolah Tinggi Ilmu Pertanian Wuna'): ?><a>Sangia Publishing</a> <span class="italic">collaborated with</span> <a href="<?php echo $this->_tpl_vars['publisherUrl']; ?>
" target="_blank"><?php echo ((is_array($_tmp=$this->_tpl_vars['publisherInstitution'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a><?php else: ?><a href="<?php echo $this->_tpl_vars['publisherUrl']; ?>
" target="_blank"><?php echo ((is_array($_tmp=$this->_tpl_vars['publisherInstitution'])) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
</a><?php endif; ?></p>
      <?php if ($this->_tpl_vars['publisherNote']): ?><p><?php echo ((is_array($_tmp=$this->_tpl_vars['publisherNote'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</p><?php endif; ?>
    </section>
  </section>
</section>
<?php endif; ?>

</section>

        </div>
    </div>
</div>

<div class="live-area-wrapper">
	<div class="row">
	    <div role="main" class="column">
	        <iframe class="lazyload" src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15919.707735119626!2d122.5556084!3d-4.0353334!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x59d6a213a880ac1a!2sSangia%20News%20%26%20Media!5e0!3m2!1sid!2sid!4v1658598581283!5m2!1sid!2sid" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" width="100%" height="300"></iframe>
        </div>
    </div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
