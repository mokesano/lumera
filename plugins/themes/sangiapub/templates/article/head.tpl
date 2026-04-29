{**
 * templates/article/head.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article View -- Head component.
 *
 *}
<!-- Compiled sheet  -->
<link rel="stylesheet" href="{$baseUrl}/styles/compiled.css" type="text/css" />
<link rel="stylesheet" href="//stipwunaraha.ac.id/media/static/css/sangia.html.css" type="text/css" />
<link rel="stylesheet" href="//stipwunaraha.ac.id/media/static/css/sangia.modern.css" type="text/css" />
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/wizdam/css/message.css" type="text/css" />

<!-- Default global locale keys for JavaScript -->
{include file="common/jsLocaleKeys.tpl" }

<!-- Compiled scripts -->
{if $useMinifiedJavaScript}
	<script type="text/javascript" src="{$baseUrl}/js/pkp.min.js"></script>
{else}
	{include file="common/minifiedScripts.tpl"}
{/if}

<!-- Common style sheet -->
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/wizdam/css/print.css" media="print" type="text/css" />
<link rel="stylesheet" href="{$baseUrl}/plugins/themes/wizdam/css/summary.css" type="text/css" />

{foreach from=$stylesheets name="testUrl" item=cssUrl}
	{if $cssUrl != "$baseUrl/styles/ojs.css"}
		<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
	{/if}
{/foreach}

<style>
{literal}
    /* Optional: Style to indicate loading */
    .lazyload {
        opacity: 0;
        transition: opacity 0.3s;
    }
    .lazyloaded {
        opacity: 1;
    }
{/literal}
</style>
    
<script>
{literal}
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
{/literal}
</script>

<!-- AJAX Math Formulas -->
<script type="text/javascript" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
<script type="text/x-mathjax-config;executed=true">
{literal}
    MathJax.Hub.Config({
      displayAlign: 'left',
      "fast-preview": {
        disabled: true
      },
      CommonHTML: { linebreaks: { automatic: true } },
      PreviewHTML: { linebreaks: { automatic: true } },
      'HTML-CSS': { linebreaks: { automatic: true } },
      SVG: {
        scale: 90,
        linebreaks: { automatic: true }
      }
    });
{/literal}
</script>

{$additionalHeadData}
