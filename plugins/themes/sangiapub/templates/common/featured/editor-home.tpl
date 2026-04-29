{**
 * templates/common/featured/editor-home.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Editor Home Journal
 *
 * @file editor_display_home.tpl
 * @brief Template untuk menampilkan cuplikan journal managers dan editors di halaman utama
 *}

{* Elemen HTML untuk menampilkan editor jurnal dan manager *}
<section style="background-color: rgba(230, 242, 255, 0.85);" class="live-area-wrapper editorial-section-wrapper">
    <div class="row raw">
        <section data-aa-region="aa-journal-editorial-section" class="editorial-section grid u-margin-m-top section-container column">
            <div class="editorial-header">
                <h2 style="/*! color: rgb(255, 255, 255); */" class="title u-h2"><b>Editor-in-Chief</b>
                </h2>
                <a href="{url page="about" op="editorialTeam"}" style="/*! color: rgb(255, 255, 255); */" class="anchor view-all u-text-- anchor-primary anchor-has-background-color">
                    <span class="anchor-text-container">
                        <span class="anchor-text">View full editorial board</span>
                    </span>
                </a>
            </div>
            <div class="carousel">
                <div class="slide active">
                    <div class="editorial-row">
                        {* Tampilkan editor sebagai Editor-in-Chief *}
                        {if $journalManagers && !empty($journalManagers)}
                        {foreach from=$journalManagers item=manager}
                        <div class="editor u-margin-l-right">
                            <div class="editor-img-container u-margin-m-right">
                                {if $manager.hasProfileImage}
                                    <img alt="{$manager.fullName}" class="col-xs-4 u-display-inline-block editor-img js-editor-img" src="{$manager.imageUrl}">
                                {else}
                                    <svg width="100" height="100" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="60" cy="60" r="60" fill="#FFFFFF"></circle><path d="M84.4302 92.21L81.8402 90.49V91.35L82.7002 94.8L81.8402 98.24L80.1202 93.07L76.6702 88.77L74.0802 86.18L70.6402 83.59L63.7402 81.01L67.1902 86.18L69.7802 92.18L71.5002 95.63L74.9502 98.21L78.3902 99.94L81.8402 100.8V104.25L83.5602 106.83L85.3302 110.31L87.4702 112.49C87.6002 112.41 87.7402 112.35 87.8702 112.28V97.38L87.0102 94.8L84.4302 92.21ZM74.0902 95.66L72.3302 93.07L69.7502 88.77L67.1602 83.6L70.6102 85.32L74.0502 87.9L77.5002 92.21L79.2202 94.8L80.0902 99.1L74.0902 95.66ZM83.9302 105.34L83.0702 100.57L83.9802 97.09L83.1902 92.57L85.9302 95.79L86.2202 99.37L87.0802 110.31L83.9302 105.34Z" fill="#1F1F1F" fill-opacity="0.1"></path><path d="M80.3302 109.99L74.3302 108.26V103.95L73.4602 101.37L71.7402 97.92L70.0302 95.34L64.8602 91.03L60.3302 85.99L59.9002 90.3L60.3302 93.96L60.5302 95.34L62.2502 98.78L66.5602 103.09L71.7302 107.4V108.26H70.0302L66.5802 109.99L62.2702 110.85L58.8302 112.57L55.3802 115.16L54.5202 116.88L53.7402 119.22C54.4102 119.28 55.0902 119.34 55.7402 119.38L57.4702 115.93L60.9102 113.35L67.8102 110.76L65.2202 115.07L62.6702 118.49L60.5802 119.49C61.4802 119.49 62.3702 119.49 63.2502 119.38L64.8602 117.77L68.3302 113.43L70.9102 109.12L76.9102 110.85L82.9102 113.43L83.8502 114.22L84.5102 113.93L78.3302 110.29L83.5602 112.06L85.7102 113.36L86.7702 112.82L86.3502 112.57L80.3302 109.99ZM70.8102 104.92L65.6402 99.75L62.2402 95.49L61.3802 92.91V89.41L69.9902 97.17L72.5802 101.49L73.4402 106.65L70.8102 104.92Z" fill="#1F1F1F" fill-opacity="0.1"></path><path d="M102.77 97.06H99.3301L96.7401 97.92H95.0201L93.3301 97.06L97.6301 91.89L99.3301 88.49V85L97.6001 75.49L94.1601 81.49L90.7101 87.49L89.8501 90.08V93.52L90.7101 96.11L91.5701 98.69L89.8501 106.45V110.76L89.9201 111.02C90.4801 110.68 91.0301 110.34 91.5701 110.02V109.87L92.4401 105.29L93.4401 100.77L96.2401 104.38L96.6701 106.38C97.2201 105.95 97.7601 105.51 98.2901 105.05L96.5701 101.6L93.9801 99.02L98.2901 100.74L100.92 102.74C101.23 102.44 101.56 102.14 101.87 101.83L97.3301 99.11H101.64L103.95 99.57C104.52 98.93 105.08 98.29 105.63 97.63L104.48 97.06H102.77ZM92.2301 96.49L92.0101 91.25L93.3301 87.85L95.2701 84L97.2101 80.15L98.4901 85.25L97.8401 89.34L95.1601 93.63L92.2301 96.49Z" fill="#1F1F1F" fill-opacity="0.1"></path><path d="M107.08 86.72L105.33 85.86L106.19 87.58L107.91 91.89L108.49 93.82C108.85 93.32 109.19 92.82 109.49 92.29L108.33 88.65L110.87 90.17C111.11 89.78 111.33 89.37 111.56 88.97L110.56 88.44L107.08 86.72Z" fill="#1F1F1F" fill-opacity="0.1"></path><path d="M59.2202 104.28L58.3302 102.55V98.24L59.1902 94.8V92.21L57.4702 86.21L54.8802 81.9L51.4302 77.59L50.5702 78.45V79.32L51.4302 81.9L50.5702 87.07L51.4302 92.24L53.1602 97.41L56.6102 102.58L50.6102 101.72H44.6102L42.0302 102.58L39.4402 104.31L36.8602 106.89L35.9502 109.49L40.2602 110.35L45.4302 109.49L51.4302 108.63L53.1602 107.76L55.7402 106.04L59.2202 104.28ZM56.5602 100.04L53.2702 93.43L51.9802 88.05L52.6902 82.49L55.5902 86.61L57.4202 90.73V94.85L56.5602 100.04ZM51.6802 106.86L47.3702 107.72L38.7502 108.59L41.3302 105.14L45.6402 103.42H51.6402L55.9502 104.29L51.6802 106.86Z" fill="#1F1F1F" fill-opacity="0.1"></path><g clip-path="url(#clip0)"><path d="M43.4541 81.875L43.7822 78.2461C44.5518 70.9062 52.8994 69.2891 59.833 69.2891C66.7627 69.2891 75.1104 70.9023 75.8799 78.2109L76.208 81.875H80.1299L79.7666 77.8359C78.9268 69.8555 71.8994 65.4688 59.833 65.4688C47.7666 65.4688 40.7354 69.8516 39.8916 77.8633L39.5361 81.875H43.4541ZM59.833 41.918C56.0361 41.918 52.708 45.3125 52.708 49.1797C52.708 54.5195 55.7705 58.5469 59.833 58.5469C63.8955 58.5469 66.958 54.5195 66.958 49.1797C66.958 45.3125 63.6299 41.918 59.833 41.918ZM59.833 62.3438C53.5986 62.3438 48.8955 56.6875 48.8955 49.1914C48.8955 43.1914 53.9033 38.125 59.833 38.125C65.7627 38.125 70.7705 43.1914 70.7705 49.1914C70.7705 56.6875 66.0674 62.3438 59.833 62.3438Z" fill="#535BA8"></path></g><defs><clipPath id="clip0"><rect width="41.6667" height="50" fill="white" transform="translate(39 35)"></rect></clipPath></defs></svg>
                                {/if}
                            </div>
                            <div class="editor-info-container">
                                <h3 class="js-editor-name name" style="/*! color: rgb(255, 255, 255); */">{if $manager.salutation}{$manager.salutation} {/if}{if $manager.firstName !== $manager.lastName}{$manager.firstName}{/if}{if $manager.middleName} {$manager.middleName}{/if}{if $manager.lastName} {$manager.lastName}{/if}{if $manager.suffix}, {$manager.suffix}{/if}
                                </h3>
                                {if $manager.affiliation}
                                <p class="js-editor-affiliation branded text-s u-margin-xs-top" style="/*! color: rgb(255, 255, 255); */">{$manager.affiliation}{if $manager.country}, {$manager.country}{/if}</p>
                                {/if}
                            </div>
                        </div>
                        {/foreach}
                        {/if}
                        
                        {if $journalEditors && !empty($journalEditors)}
                        {foreach from=$journalEditors item=editor}
                        <div class="editor u-margin-l-right">
                            <div class="editor-img-container u-margin-m-right">
                                {if $editor.hasProfileImage}
                                    <img alt="{$editor.fullName}" class="col-xs-4 u-display-inline-block editor-img js-editor-img" src="{$editor.imageUrl}">
                                {else}
                                    <svg width="100" height="100" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="60" cy="60" r="60" fill="#FFFFFF"></circle><path d="M84.4302 92.21L81.8402 90.49V91.35L82.7002 94.8L81.8402 98.24L80.1202 93.07L76.6702 88.77L74.0802 86.18L70.6402 83.59L63.7402 81.01L67.1902 86.18L69.7802 92.18L71.5002 95.63L74.9502 98.21L78.3902 99.94L81.8402 100.8V104.25L83.5602 106.83L85.3302 110.31L87.4702 112.49C87.6002 112.41 87.7402 112.35 87.8702 112.28V97.38L87.0102 94.8L84.4302 92.21ZM74.0902 95.66L72.3302 93.07L69.7502 88.77L67.1602 83.6L70.6102 85.32L74.0502 87.9L77.5002 92.21L79.2202 94.8L80.0902 99.1L74.0902 95.66ZM83.9302 105.34L83.0702 100.57L83.9802 97.09L83.1902 92.57L85.9302 95.79L86.2202 99.37L87.0802 110.31L83.9302 105.34Z" fill="#1F1F1F" fill-opacity="0.1"></path><path d="M80.3302 109.99L74.3302 108.26V103.95L73.4602 101.37L71.7402 97.92L70.0302 95.34L64.8602 91.03L60.3302 85.99L59.9002 90.3L60.3302 93.96L60.5302 95.34L62.2502 98.78L66.5602 103.09L71.7302 107.4V108.26H70.0302L66.5802 109.99L62.2702 110.85L58.8302 112.57L55.3802 115.16L54.5202 116.88L53.7402 119.22C54.4102 119.28 55.0902 119.34 55.7402 119.38L57.4702 115.93L60.9102 113.35L67.8102 110.76L65.2202 115.07L62.6702 118.49L60.5802 119.49C61.4802 119.49 62.3702 119.49 63.2502 119.38L64.8602 117.77L68.3302 113.43L70.9102 109.12L76.9102 110.85L82.9102 113.43L83.8502 114.22L84.5102 113.93L78.3302 110.29L83.5602 112.06L85.7102 113.36L86.7702 112.82L86.3502 112.57L80.3302 109.99ZM70.8102 104.92L65.6402 99.75L62.2402 95.49L61.3802 92.91V89.41L69.9902 97.17L72.5802 101.49L73.4402 106.65L70.8102 104.92Z" fill="#1F1F1F" fill-opacity="0.1"></path><path d="M102.77 97.06H99.3301L96.7401 97.92H95.0201L93.3301 97.06L97.6301 91.89L99.3301 88.49V85L97.6001 75.49L94.1601 81.49L90.7101 87.49L89.8501 90.08V93.52L90.7101 96.11L91.5701 98.69L89.8501 106.45V110.76L89.9201 111.02C90.4801 110.68 91.0301 110.34 91.5701 110.02V109.87L92.4401 105.29L93.4401 100.77L96.2401 104.38L96.6701 106.38C97.2201 105.95 97.7601 105.51 98.2901 105.05L96.5701 101.6L93.9801 99.02L98.2901 100.74L100.92 102.74C101.23 102.44 101.56 102.14 101.87 101.83L97.3301 99.11H101.64L103.95 99.57C104.52 98.93 105.08 98.29 105.63 97.63L104.48 97.06H102.77ZM92.2301 96.49L92.0101 91.25L93.3301 87.85L95.2701 84L97.2101 80.15L98.4901 85.25L97.8401 89.34L95.1601 93.63L92.2301 96.49Z" fill="#1F1F1F" fill-opacity="0.1"></path><path d="M107.08 86.72L105.33 85.86L106.19 87.58L107.91 91.89L108.49 93.82C108.85 93.32 109.19 92.82 109.49 92.29L108.33 88.65L110.87 90.17C111.11 89.78 111.33 89.37 111.56 88.97L110.56 88.44L107.08 86.72Z" fill="#1F1F1F" fill-opacity="0.1"></path><path d="M59.2202 104.28L58.3302 102.55V98.24L59.1902 94.8V92.21L57.4702 86.21L54.8802 81.9L51.4302 77.59L50.5702 78.45V79.32L51.4302 81.9L50.5702 87.07L51.4302 92.24L53.1602 97.41L56.6102 102.58L50.6102 101.72H44.6102L42.0302 102.58L39.4402 104.31L36.8602 106.89L35.9502 109.49L40.2602 110.35L45.4302 109.49L51.4302 108.63L53.1602 107.76L55.7402 106.04L59.2202 104.28ZM56.5602 100.04L53.2702 93.43L51.9802 88.05L52.6902 82.49L55.5902 86.61L57.4202 90.73V94.85L56.5602 100.04ZM51.6802 106.86L47.3702 107.72L38.7502 108.59L41.3302 105.14L45.6402 103.42H51.6402L55.9502 104.29L51.6802 106.86Z" fill="#1F1F1F" fill-opacity="0.1"></path><g clip-path="url(#clip0)"><path d="M43.4541 81.875L43.7822 78.2461C44.5518 70.9062 52.8994 69.2891 59.833 69.2891C66.7627 69.2891 75.1104 70.9023 75.8799 78.2109L76.208 81.875H80.1299L79.7666 77.8359C78.9268 69.8555 71.8994 65.4688 59.833 65.4688C47.7666 65.4688 40.7354 69.8516 39.8916 77.8633L39.5361 81.875H43.4541ZM59.833 41.918C56.0361 41.918 52.708 45.3125 52.708 49.1797C52.708 54.5195 55.7705 58.5469 59.833 58.5469C63.8955 58.5469 66.958 54.5195 66.958 49.1797C66.958 45.3125 63.6299 41.918 59.833 41.918ZM59.833 62.3438C53.5986 62.3438 48.8955 56.6875 48.8955 49.1914C48.8955 43.1914 53.9033 38.125 59.833 38.125C65.7627 38.125 70.7705 43.1914 70.7705 49.1914C70.7705 56.6875 66.0674 62.3438 59.833 62.3438Z" fill="#535BA8"></path></g><defs><clipPath id="clip0"><rect width="41.6667" height="50" fill="white" transform="translate(39 35)"></rect></clipPath></defs></svg>
                                {/if}
                            </div>
                            <div class="editor-info-container">
                                <h3 class="js-editor-name name" style="/*! color: rgb(255, 255, 255); */">{if $editor.salutation}{$editor.salutation} {/if}{if $editor.firstName !== $editor.lastName}{$editor.firstName}{/if}{if $editor.middleName} {$editor.middleName}{/if}{if $editor.lastName} {$editor.lastName}{/if}{if $editor.suffix}, {$editor.suffix}{/if}
                                </h3>
                                {if $editor.affiliation}
                                <p class="js-editor-affiliation branded text-s u-margin-xs-top" style="/*! color: rgb(255, 255, 255); */">{$editor.affiliation}{if $editor.country}, {$editor.country}{/if}
                                </p>
                                {/if}
                            </div>
                        </div>
                        {/foreach}
                        {/if}
                    </div>
                </div>
            </div>
        </section>
    </div>
</section>
