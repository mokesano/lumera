<?php /* Smarty version 2.6.26, created on 2026-04-05 12:34:36
         compiled from announcement/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'nl2br', 'announcement/index.tpl', 23, false),array('modifier', 'escape', 'announcement/index.tpl', 71, false),array('modifier', 'date_format', 'announcement/index.tpl', 80, false),array('block', 'iterate', 'announcement/index.tpl', 29, false),array('function', 'translate', 'announcement/index.tpl', 80, false),array('function', 'url', 'announcement/index.tpl', 82, false),array('function', 'page_info', 'announcement/index.tpl', 100, false),array('function', 'page_links', 'announcement/index.tpl', 103, false),)), $this); ?>
<?php echo ''; ?><?php $this->assign('pageTitle', "announcement.announcements"); ?><?php echo ''; ?><?php $this->assign('pageId', "announcement.announcements"); ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?>


<div id="announcementList" class="app-announcement-list-row announcements">
    
    <?php if ($this->_tpl_vars['announcementsIntroduction'] != null): ?>    
    <article class="app-announcement-list-row__item c-card--flush announcement">
    	<div class="c-card__layout u-full-height intro">
    		<div class="c-card__body u-display-flex u-flex-direction-column">
    		    <div itemprop="description" class="c-card__announcement u-hide-sm-max intro"><?php echo ((is_array($_tmp=$this->_tpl_vars['announcementsIntroduction'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</div>
    		</div>
    	</div>
    </article>
    <?php endif; ?>
    
    <?php $this->_tag_stack[] = array('iterate', array('from' => 'announcements','item' => 'announcement')); $_block_repeat=true;$this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
    <article class="app-announcement-list-row__item c-card--flush announcement">
        <div class="c-card__layout u-full-height">
            <?php if ($this->_tpl_vars['announcement']->getAnnouncementTypeName() == 'Call for' || $this->_tpl_vars['announcement']->getAnnouncementTypeName() == 'Editor'): ?>
        	<div class="c-card__image">
                <picture>
                    <source type="image/webp" srcset="//assets.sangia.org/static/images/logos/announcement-image-megaphone.png?as=webp 290w" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
                    <img data-test="image-1" src="//assets.sangia.org/static/images/logos/announcement-image-megaphone.png" alt="Icon of a megaphone, indicating the Call for Papers page." itemprop="image" loading="lazy">
                </picture>
            </div>                            
            <?php elseif ($this->_tpl_vars['announcement']->getAnnouncementTypeName() == 'Notice' || $this->_tpl_vars['announcement']->getAnnouncementTypeName() == 'Noted' || $this->_tpl_vars['announcement']->getAnnouncementTypeName() == 'News'): ?>
        	<div class="c-card__image">
                <picture>
                    <source type="image/webp" srcset="//assets.sangia.org/static/images/logos/announcement-image-noted.jpg?as=webp 290w" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
                    <img data-test="image-1" src="//assets.sangia.org/static/images/logos/announcement-image-noted.jpg" alt="Icon of a newspaper, indicating the News page." itemprop="image" loading="lazy">
                </picture>
            </div>                            
            <?php elseif ($this->_tpl_vars['announcement']->getAnnouncementTypeName() == 'Metric' || $this->_tpl_vars['announcement']->getAnnouncementTypeName() == 'Indexed'): ?>
        	<div class="c-card__image">
                <picture>
                    <source type="image/webp" srcset="//assets.sangia.org/static/images/logos/announcement-image-metric.png?as=webp 290w" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
                    <img data-test="image-1" src="//assets.sangia.org/static/images/logos/announcement-image-metric.png" alt="Icon of metric." itemprop="image" loading="lazy">
                </picture>
            </div>                            
            <?php elseif ($this->_tpl_vars['announcement']->getAnnouncementTypeName() == 'Social'): ?>
        	<div class="c-card__image">
                <picture>
                    <source type="image/webp" srcset="//assets.sangia.org/static/images/logos/announcement-image-twitter.png.png?as=webp 290w" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
                    <img data-test="image-1" src="//assets.sangia.org/static/images/logos/announcement-image-twitter.png" alt="Icon of Twitter." itemprop="image" loading="lazy">
                </picture>
            </div>                            
            <?php elseif ($this->_tpl_vars['announcement']->getAnnouncementTypeName() == 'Join Us'): ?>
        	<div class="c-card__image">
                <picture>
                    <source type="image/webp" srcset="//assets.sangia.org/static/images/logos/announcement-image-enginer.png.png?as=webp 290w" sizes="(max-width: 640px) 160px, (max-width: 1200px) 290px, 290px">
                    <img data-test="image-1" src="//assets.sangia.org/static/images/logos/announcement-image-enginer.png" alt="Icon of cogs, indicating the Engineering scope expansion announcement." itemprop="image" loading="lazy">
                </picture>
            </div>                            
            <?php endif; ?>
            <div class="c-card__body u-display-flex u-flex-direction-column">
                <?php if ($this->_tpl_vars['announcement']->getTypeId()): ?>
                <h2 class="headline headline-2545795530 u-h2" itemprop="name headline">
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getAnnouncementTypeName())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                </h2>
                <?php else: ?>
                <h2 class="headline headline-2545795530 u-h2" itemprop="name headline">
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedTitle())) ? $this->_run_mod_handler('escape', true, $_tmp) : $this->_plugins['modifier']['escape'][0][0]->smartyEscape($_tmp)); ?>

                </h2>
                <?php endif; ?>
                <div itemprop="description" class="c-card__announcement u-mb-24 u-hide-sm-max intro"><?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getLocalizedDescriptionShort())) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</div>
                <div class="c-card__summary u-flex-direction-column details">
                    <time class="published posted"><?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "announcement.posted"), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['announcement']->getDatePosted())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
</time>
                    <?php if ($this->_tpl_vars['announcement']->getLocalizedDescription() != null): ?>
                    <span class="more"><a itemprop="url" href="<?php echo $this->_plugins['function']['url'][0][0]->smartyUrl(array('op' => 'view','path' => $this->_tpl_vars['announcement']->getId()), $this);?>
">View <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "announcement.viewLink"), $this);?>
</a>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </article>
    <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['iterate'][0][0]->smartyIterate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    
    <div id="colspan" class="colspan u-mb-0">
    <?php if ($this->_tpl_vars['announcements']->wasEmpty()): ?>
        <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
            <div class="c-pagination">
                <?php echo $this->_plugins['function']['translate'][0][0]->smartyTranslate(array('key' => "announcement.noneExist"), $this);?>

            </div>
        </section>
    <?php else: ?>
    	<section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
    	    <div class="c-pagination"><?php echo $this->_plugins['function']['page_info'][0][0]->smartyPageInfo(array('iterator' => $this->_tpl_vars['announcements']), $this);?>
</div>
    	</section>
    	<section class="u-display-flex u-justify-content-center">
    	    <div class="c-pagination"><?php echo $this->_plugins['function']['page_links'][0][0]->smartyPageLinks(array('anchor' => 'announcements','name' => 'announcements','iterator' => $this->_tpl_vars['announcements']), $this);?>
</div>
    	</section>
    <?php endif; ?>
    </div>

</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>