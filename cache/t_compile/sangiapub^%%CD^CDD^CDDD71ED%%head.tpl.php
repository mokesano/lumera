<?php /* Smarty version 2.6.26, created on 2026-04-05 00:35:13
         compiled from article/head.tpl */ ?>
<!-- Compiled sheet  -->
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/styles/compiled.css" type="text/css" />
<link rel="stylesheet" href="//stipwunaraha.ac.id/media/static/css/sangia.html.css" type="text/css" />
<link rel="stylesheet" href="//stipwunaraha.ac.id/media/static/css/sangia.modern.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/themes/wizdam/css/message.css" type="text/css" />

<!-- Default global locale keys for JavaScript -->
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/jsLocaleKeys.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<!-- Compiled scripts -->
<?php if ($this->_tpl_vars['useMinifiedJavaScript']): ?>
	<script type="text/javascript" src="<?php echo $this->_tpl_vars['baseUrl']; ?>
/js/pkp.min.js"></script>
<?php else: ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/minifiedScripts.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<!-- Common style sheet -->
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/themes/wizdam/css/print.css" media="print" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['baseUrl']; ?>
/plugins/themes/wizdam/css/summary.css" type="text/css" />

<?php $_from = $this->_tpl_vars['stylesheets']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['testUrl'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['testUrl']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['cssUrl']):
        $this->_foreach['testUrl']['iteration']++;
?>
	<?php if ($this->_tpl_vars['cssUrl'] != ($this->_tpl_vars['baseUrl'])."/styles/ojs.css"): ?>
		<link rel="stylesheet" href="<?php echo $this->_tpl_vars['cssUrl']; ?>
" type="text/css" />
	<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>

<style>
<?php echo '
    /* Optional: Style to indicate loading */
    .lazyload {
        opacity: 0;
        transition: opacity 0.3s;
    }
    .lazyloaded {
        opacity: 1;
    }
'; ?>

</style>
    
<script>
<?php echo '
    document.addEventListener("DOMContentLoaded", function() {
        let lazyImages = [].slice.call(document.querySelectorAll("img.lazyload"));

        if ("IntersectionObserver" in window) {
            let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
                        lazyImage.src = lazyImage.dataset.src;
                        lazyImage.classList.remove("lazyload");
                        lazyImage.classList.add("lazyloaded");
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            });

            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        } else {
            // Fallback for older browsers
            let lazyLoad = function() {
                lazyImages.forEach(function(lazyImage) {
                    if (lazyImage.offsetTop < window.innerHeight + window.pageYOffset) {
                        lazyImage.src = lazyImage.dataset.src;
                        lazyImage.classList.remove("lazyload");
                        lazyImage.classList.add("lazyloaded");
                    }
                });
            };

            lazyLoad();
            window.addEventListener("scroll", lazyLoad);
            window.addEventListener("resize", lazyLoad);
        }
    });
'; ?>

</script>

<!-- AJAX Math Formulas -->
<script type="text/javascript" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
<script type="text/x-mathjax-config;executed=true">
<?php echo '
    MathJax.Hub.Config({
      displayAlign: \'left\',
      "fast-preview": {
        disabled: true
      },
      CommonHTML: { linebreaks: { automatic: true } },
      PreviewHTML: { linebreaks: { automatic: true } },
      \'HTML-CSS\': { linebreaks: { automatic: true } },
      SVG: {
        scale: 90,
        linebreaks: { automatic: true }
      }
    });
'; ?>

</script>

<?php echo $this->_tpl_vars['additionalHeadData']; ?>
