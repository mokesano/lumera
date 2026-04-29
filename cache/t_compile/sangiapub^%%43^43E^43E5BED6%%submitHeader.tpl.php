<?php /* Smarty version 2.6.26, created on 2026-04-16 16:32:50
         compiled from author/submit/submitHeader.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'author/submit/submitHeader.tpl', 20, false),array('modifier', 'escape', 'author/submit/submitHeader.tpl', 22, false),array('function', 'math', 'author/submit/submitHeader.tpl', 30, false),array('function', 'translate', 'author/submit/submitHeader.tpl', 34, false),array('function', 'url', 'author/submit/submitHeader.tpl', 72, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageCrumbTitle', "author.submit"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header-ROLE.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<script type="text/javascript">
<?php echo '
<!--
window.submitStep = '; ?>
<?php echo ((is_array($_tmp=@$this->_tpl_vars['submitStep'])) ? $this->_run_mod_handler('default', true, $_tmp, 1) : smarty_modifier_default($_tmp, 1)); ?>
<?php echo ';
window.submissionProgress = '; ?>
<?php echo ((is_array($_tmp=@$this->_tpl_vars['submissionProgress'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0)); ?>
<?php echo ';
window.articleId = \''; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['articleId'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp, 'javascript')); ?>
<?php echo '\';
// -->
'; ?>

</script>

<!-- Simple Progress Bar - Fixed Right Side -->
<div class="simple-progress-container">
    <div class="progress-line">
        <div class="progress-fill" id="progressFill" style="height: <?php if ($this->_tpl_vars['submissionProgress'] > 0): ?><?php echo smarty_function_math(array('equation' => "(x / 5) * 100",'x' => $this->_tpl_vars['submissionProgress']), $this);?>
<?php else: ?>0<?php endif; ?>%"></div>
        <div class="step-indicators">
            <div class="step-indicator pending" 
                 data-step="1" 
                 title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.start"), $this);?>
">1</div>
            <div class="step-indicator pending" 
                 data-step="2" 
                 title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.upload"), $this);?>
">2</div>
            <div class="step-indicator pending" 
                 data-step="3" 
                 title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.metadata"), $this);?>
">3</div>
            <div class="step-indicator pending" 
                 data-step="4" 
                 title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.supplementaryFiles"), $this);?>
">4</div>
            <div class="step-indicator pending" 
                 data-step="5" 
                 title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.confirmation"), $this);?>
">5</div>
            <div class="complete-indicator pending" 
                 title="<?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.complete"), $this);?>
">
                <span class="complete-icon">✓</span>
                <span class="complete-text">Complete</span>
            </div>
        </div>
    </div>
    <div class="progress-info">
        <span class="progress-percentage" id="progressPercentage"><?php if ($this->_tpl_vars['submissionProgress'] > 0): ?><?php echo smarty_function_math(array('equation' => "(x / 5) * 100",'x' => $this->_tpl_vars['submissionProgress'],'format' => "%.0f"), $this);?>
<?php else: ?>0<?php endif; ?>%</span>
        <small><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.complete"), $this);?>
</small>
    </div>
</div>

<!-- Keep Original Step Navigation -->
<div class="pseudoMenu u-mb-24">
    <ul class="steplist menu">
        <li id="step1" 
            <?php if ($this->_tpl_vars['submitStep'] == 1): ?>
                class="current"
            <?php elseif ($this->_tpl_vars['submissionProgress'] >= 1): ?>
                class="success"
            <?php else: ?>
                class="disable"
            <?php endif; ?>>
            <?php if ($this->_tpl_vars['submitStep'] != 1 && $this->_tpl_vars['submissionProgress'] >= 1): ?>
                <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submit','path' => '1','articleId' => $this->_tpl_vars['articleId']), $this);?>
">
            <?php endif; ?>
            <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.start"), $this);?>

            <?php if ($this->_tpl_vars['submitStep'] != 1 && $this->_tpl_vars['submissionProgress'] >= 1): ?>
                </a>
            <?php endif; ?>
        </li>
        
        <li id="step2" 
            <?php if ($this->_tpl_vars['submitStep'] == 2): ?>
                class="current"
            <?php elseif ($this->_tpl_vars['submissionProgress'] >= 2): ?>
                class="success"
            <?php else: ?>
                class="disable"
            <?php endif; ?>>
            <?php if ($this->_tpl_vars['submitStep'] != 2 && $this->_tpl_vars['submissionProgress'] >= 2): ?>
                <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submit','path' => '2','articleId' => $this->_tpl_vars['articleId']), $this);?>
">
            <?php endif; ?>
            <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.upload"), $this);?>

            <?php if ($this->_tpl_vars['submitStep'] != 2 && $this->_tpl_vars['submissionProgress'] >= 2): ?>
                </a>
            <?php endif; ?>
        </li>
        
        <li id="step3" 
            <?php if ($this->_tpl_vars['submitStep'] == 3): ?>
                class="current"
            <?php elseif ($this->_tpl_vars['submissionProgress'] >= 3): ?>
                class="success"
            <?php else: ?>
                class="disable"
            <?php endif; ?>>
            <?php if ($this->_tpl_vars['submitStep'] != 3 && $this->_tpl_vars['submissionProgress'] >= 3): ?>
                <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submit','path' => '3','articleId' => $this->_tpl_vars['articleId']), $this);?>
">
            <?php endif; ?>
            <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.metadata"), $this);?>

            <?php if ($this->_tpl_vars['submitStep'] != 3 && $this->_tpl_vars['submissionProgress'] >= 3): ?>
                </a>
            <?php endif; ?>
        </li>
        
        <li id="step4" 
            <?php if ($this->_tpl_vars['submitStep'] == 4): ?>
                class="current"
            <?php elseif ($this->_tpl_vars['submissionProgress'] >= 4): ?>
                class="success"
            <?php else: ?>
                class="disable"
            <?php endif; ?>>
            <?php if ($this->_tpl_vars['submitStep'] != 4 && $this->_tpl_vars['submissionProgress'] >= 4): ?>
                <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submit','path' => '4','articleId' => $this->_tpl_vars['articleId']), $this);?>
">
            <?php endif; ?>
            <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.supplementaryFiles"), $this);?>

            <?php if ($this->_tpl_vars['submitStep'] != 4 && $this->_tpl_vars['submissionProgress'] >= 4): ?>
                </a>
            <?php endif; ?>
        </li>
        
        <li id="step5" 
            <?php if ($this->_tpl_vars['submitStep'] == 5): ?>
                class="current"
            <?php elseif ($this->_tpl_vars['submissionProgress'] >= 5): ?>
                class="success"
            <?php else: ?>
                class="disable"
            <?php endif; ?>>
            <?php if ($this->_tpl_vars['submitStep'] != 5 && $this->_tpl_vars['submissionProgress'] >= 5): ?>
                <a href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'submit','path' => '5','articleId' => $this->_tpl_vars['articleId']), $this);?>
">
            <?php endif; ?>
            <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "author.submit.confirmation"), $this);?>

            <?php if ($this->_tpl_vars['submitStep'] != 5 && $this->_tpl_vars['submissionProgress'] >= 5): ?>
                </a>
            <?php endif; ?>
        </li>
    </ul>
</div>

<!-- Auto-save Indicator -->
<div id="autoSaveIndicator" class="auto-save-indicator">
    ✓ <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "common.changesSaved"), $this);?>

</div>