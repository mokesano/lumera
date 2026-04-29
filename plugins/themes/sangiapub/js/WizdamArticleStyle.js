/**
 * Inisialisasi nonaktif tombol next previous pada artikel.
 */
$(document).ready(function() {
    /**
     * Mengatur tombol alternatif yang dinonaktifkan
     */
    const disableButtons = document.querySelectorAll('.button-alternative');

    disableButtons.forEach(function(button) {
        if (button.classList.contains('disabled')) {
            const svgIcon = button.querySelector('svg.icon');
            if (svgIcon) {
                if (button.classList.contains('button-alternative-primary')) {
                    svgIcon.style.backgroundColor = '#b9b9b9';
                    svgIcon.style.borderColor = '#b9b9b9';
                    svgIcon.style.fill = '#fff';
                } else if (button.classList.contains('button-alternative-secondary') || button.classList.contains('button-alternative-tertiary')) {
                    svgIcon.style.backgroundColor = '#fff';
                    svgIcon.style.borderColor = '#b9b9b9';
                    svgIcon.style.fill = '#b9b9b9';
                }
            }

            const buttonText = button.querySelector('.button-alternative-text');
            if (buttonText) {
                buttonText.style.color = '#b9b9b9';
                buttonText.style.cursor = 'default';
            }

            button.style.pointerEvents = 'none';
            button.style.cursor = 'not-allowed';

            button.addEventListener('click', function(event) {
                event.preventDefault();
            });
        }
    });
});

/**
 * Menginisiasi elemen terkait
 */
$(document).ready(function() {
    /**
     * Menginisiasi elemen terkait
     */
    function initRelatedItems() {
        $("#relatedItems").hide();
        $("#toggleRelatedItems").show();
        $("#hideRelatedItems").click(function() {
            $("#relatedItems").hide('fast');
            $("#hideRelatedItems").hide();
            $("#showRelatedItems").show();
        });
        $("#showRelatedItems").click(function() {
            $("#relatedItems").show('fast');
            $("#showRelatedItems").hide();
            $("#hideRelatedItems").show();
        });
    }
    initRelatedItems();
});

/**
 * Mengatur tampilan "Show More/Less" pada elemen afiliasi author
 */
document.addEventListener("DOMContentLoaded", function () {
    const showMoreBtn = document.getElementById("show-more-btn");
    const wrapper = document.querySelector(".wrapper");

    const elementsToToggle = [
        document.getElementById("affiliation-group"),
        wrapper.querySelector("p"),
        document.querySelector(".crossmark-button")
    ];

    /**
     * Mengatur arah panah
     * @param {boolean} isExpanded - Menentukan apakah panah diperluas atau tidak.
     */
    const toggleArrowDirection = (isExpanded) => {
        const svgIcon = showMoreBtn.querySelector("svg");
        if (svgIcon) {
            svgIcon.style.transform = isExpanded ? "rotate(180deg)" : "rotate(0deg)";
        }
    }

    showMoreBtn.addEventListener("click", function () {
        const isExpanded = showMoreBtn.getAttribute("aria-expanded") === "true";

        if (isExpanded) {
            elementsToToggle.forEach(element => {
                if (element) {
                    element.hidden = true;
                }
            });
            wrapper.classList.add("truncated");
            showMoreBtn.setAttribute("aria-expanded", "false");
            showMoreBtn.setAttribute("data-aa-button", "icon-collapse");
            showMoreBtn.querySelector(".anchor-text").textContent = "Show more";
            toggleArrowDirection(false);
        } else {
            elementsToToggle.forEach(element => {
                if (element) {
                    element.hidden = false;
                }
            });
            wrapper.classList.remove("truncated");
            showMoreBtn.setAttribute("aria-expanded", "true");
            showMoreBtn.setAttribute("data-aa-button", "icon-expand");
            showMoreBtn.querySelector(".anchor-text").textContent = "Show less";
            toggleArrowDirection(true);
        }
    });

    const initialState = showMoreBtn.getAttribute("aria-expanded") === "true";
    if (!initialState) {
        wrapper.classList.add("truncated");
        elementsToToggle.forEach(element => {
            if (element) {
                element.hidden = true;
            }
        });
    }
    toggleArrowDirection(initialState);
});

/**
 * Format indeks afiliasi penulis pada artikel
 * author Rochmady and Wizdam team
 * @version 1.0.0
 */
document.addEventListener("DOMContentLoaded", function() {
    function processAffiliations() {
        var authors = document.querySelectorAll("#author-group a");
        var affiliations = document.querySelectorAll(".affiliation");

        var affMap = new Map();
        var newIndex = new Map();
        var currentIndex = 1;

        // Mengumpulkan semua afiliasi unik dan memberi mereka indeks
        affiliations.forEach(function(affiliation) {
            var affTexts = affiliation.querySelector("dd").textContent.split("\n").map(function(text) {
                return text.trim();
            }).filter(function(text) {
                return text.length > 0;
            });

            affTexts.forEach(function(affText) {
                if (!affMap.has(affText)) {
                    affMap.set(affText, currentIndex);
                    currentIndex++;
                }
                var sup = affiliation.querySelector("dt sup").textContent;
                if (!newIndex.has(sup)) {
                    newIndex.set(sup, []);
                }
                newIndex.get(sup).push(affMap.get(affText));
            });
        });

        // Fungsi untuk membuat ID sesuai format berdasarkan nilai indeks
        function formatAffiliationId(index) {
            if (index < 10) {
                return "baff000" + index;
            } else if (index < 100) {
                return "baff00" + index;
            } else if (index < 1000) {
                return "baff0" + index;
            } else {
                return "baff" + index;
            }
        }

        // Memperbarui elemen afiliasi dengan indeks yang benar
        affiliations.forEach(function(affiliation) {
            var sup = affiliation.querySelector("dt sup");
            var affTexts = affiliation.querySelector("dd").textContent.split("\n").map(function(text) {
                return text.trim();
            }).filter(function(text) {
                return text.length > 0;
            });
            var affIndexes = affTexts.map(function(affText) {
                return affMap.get(affText);
            }).join(", ");
            sup.textContent = affIndexes;
        });

        // Memperbarui elemen penulis dengan indeks afiliasi yang benar
        authors.forEach(function(author) {
            var authorRefSpan = author.querySelector(".author-ref");
            var sups = authorRefSpan.querySelectorAll("sup");
            
            // Clear existing sup elements
            sups.forEach(function(sup) {
                sup.remove();
            });
            
            // Get original indices
            var originalIndices = [];
            if (sups.length > 0) {
                sups.forEach(function(sup) {
                    var indices = sup.textContent.split(",").map(function(idx) {
                        return idx.trim();
                    });
                    originalIndices = originalIndices.concat(indices);
                });
            } else {
                // If no sups found, try to get from ID
                var id = authorRefSpan.id;
                if (id && id.startsWith("baff")) {
                    // Extract the numeric part regardless of how many digits
                    var numericPart = id.replace("baff", "").replace(/^0+/, "");
                    if (numericPart) {
                        originalIndices.push(numericPart);
                    }
                }
            }
            
            // Map original indices to new ones
            var newIndices = [];
            originalIndices.forEach(function(idx) {
                if (newIndex.has(idx)) {
                    newIndices = newIndices.concat(newIndex.get(idx));
                }
            });
            
            // Remove duplicates and sort
            var uniqueNewIndices = newIndices.filter(function(item, pos) {
                return newIndices.indexOf(item) === pos;
            }).sort(function(a, b) {
                return a - b;
            });
            
            // Create the new structure with separate spans for each affiliation
            if (uniqueNewIndices.length > 0) {
                // Remove the existing author-ref span
                var parent = authorRefSpan.parentNode;
                var nextSibling = authorRefSpan.nextSibling;
                authorRefSpan.remove();
                
                // Create new spans for each affiliation index
                uniqueNewIndices.forEach(function(index, i) {
                    var newSpan = document.createElement("span");
                    newSpan.className = "author-ref";
                    newSpan.id = formatAffiliationId(index);
                    
                    var supElement = document.createElement("sup");
                    if (i < uniqueNewIndices.length - 1) {
                        supElement.textContent = index + ",";
                    } else {
                        supElement.textContent = index;
                    }
                    
                    newSpan.appendChild(supElement);
                    
                    // Insert the new span
                    if (nextSibling) {
                        parent.insertBefore(newSpan, nextSibling);
                    } else {
                        parent.appendChild(newSpan);
                    }
                });
            }
        });

        // Menghapus afiliasi duplikat dan menyusun ulang afiliasi berdasarkan indeks
        var uniqueAffiliations = [];
        var uniqueAffiliationsMap = new Map();
        var affIndex = 1;

        document.querySelectorAll(".affiliation-group .affiliation").forEach(function(affiliation) {
            var affTexts = affiliation.querySelector("dd").textContent.split("\n").map(function(text) {
                return text.trim();
            }).filter(function(text) {
                return text.length > 0;
            });
            affTexts.forEach(function(affText) {
                if (!uniqueAffiliationsMap.has(affText)) {
                    uniqueAffiliationsMap.set(affText, affIndex);
                    uniqueAffiliations.push({ text: affText, index: affIndex });
                    affIndex++;
                }
            });
        });

        // Menampilkan afiliasi unik
        var affGroup = document.querySelector(".affiliation-group");
        affGroup.innerHTML = '';
        uniqueAffiliations.forEach(function(aff) {
            var dl = document.createElement("dl");
            dl.className = "affiliation";
            var dt = document.createElement("dt");
            var sup = document.createElement("sup");
            sup.textContent = aff.index;
            dt.appendChild(sup);
            var dd = document.createElement("dd");
            dd.textContent = aff.text;
            dl.appendChild(dt);
            dl.appendChild(dd);
            affGroup.appendChild(dl);
        });

        // Menyimpan status di sessionStorage untuk mencegah pemrosesan ulang
        sessionStorage.setItem("affiliationsProcessed", "true");

        // Menampilkan elemen yang telah diproses
        document.querySelector(".affiliation-group").style.display = '';
        document.querySelector("#author-group").style.display = '';
    }

    // Inisialisasi pertama kali
    processAffiliations();

    var observer = new MutationObserver(function(mutations) {
        if (sessionStorage.getItem("affiliationsProcessed") !== "true") {
            processAffiliations();
        }
    });
    
    var config = { childList: true, subtree: true, characterData: true };
    var target = document.querySelector('.affiliation-group');
    if (target) {
        observer.observe(target, config);
    }
});

/**
 * Tampilkan elemen share, hapus elemen dari DOM segera setelah halaman dimuat
 */
document.addEventListener("DOMContentLoaded", function() {
    // Hapus elemen dari DOM segera setelah halaman dimuat
    var popoverContentSocial = document.getElementById("popover-content-social-popover");
    var popoverContentSocialJQ;
    var popoverContentExportCitation = document.getElementById("popover-content-export-citation-popover");
    var popoverContentExportCitationJQ;
    var popoverContentAnother = document.getElementById("popover-content-another-popover");
    var popoverContentAnotherJQ;

    if (popoverContentSocial) {
        popoverContentSocialJQ = $(popoverContentSocial).remove();
    }

    if (popoverContentExportCitation) {
        popoverContentExportCitationJQ = $(popoverContentExportCitation).remove();
    }

    if (popoverContentAnother) {
        popoverContentAnotherJQ = $(popoverContentAnother).remove();
    }

    var isAnimating = false;

    function hidePopover(popoverContent) {
        popoverContent.slideUp(150, function() {
            $(this).remove();
            isAnimating = false;
        });
    }

    function showPopover(popoverContainer, popoverContent) {
        popoverContainer.append(popoverContent);
        popoverContent.hide().removeClass('u-js-hide').slideDown(150, function() {
            isAnimating = false;
        });
    }

    function isElementInViewport(el) {
        var rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    /**
     * Menangani klik pada elemen pemicu untuk menampilkan atau menyembunyikan elemen popover
     * @param {Event} event - Event klik pada elemen pemicu
     */
    $("#popover-trigger-social-popover").on("click", function(event) {
        if (isAnimating) return;
        isAnimating = true;

        if ($("#social-popover").find("#popover-content-social-popover").length) {
            hidePopover($("#popover-content-social-popover"));
        } else {
            if ($("#export-citation-popover").find("#popover-content-export-citation-popover").length) {
                hidePopover($("#popover-content-export-citation-popover"));
            }
            if ($("#another-popover").find("#popover-content-another-popover").length) {
                hidePopover($("#popover-content-another-popover"));
            }
            showPopover($("#social-popover"), popoverContentSocialJQ);
        }
        event.stopPropagation();
    });

    $("#popover-trigger-export-citation-popover").on("click", function(event) {
        if (isAnimating) return;
        isAnimating = true;

        if ($("#export-citation-popover").find("#popover-content-export-citation-popover").length) {
            hidePopover($("#popover-content-export-citation-popover"));
        } else {
            if ($("#social-popover").find("#popover-content-social-popover").length) {
                hidePopover($("#popover-content-social-popover"));
            }
            if ($("#another-popover").find("#popover-content-another-popover").length) {
                hidePopover($("#popover-content-another-popover"));
            }
            showPopover($("#export-citation-popover"), popoverContentExportCitationJQ);
        }
        event.stopPropagation();
    });

    $("#popover-trigger-another-popover").on("click", function(event) {
        if (isAnimating) return;
        isAnimating = true;

        if ($("#another-popover").find("#popover-content-another-popover").length) {
            hidePopover($("#popover-content-another-popover"));
        } else {
            if ($("#social-popover").find("#popover-content-social-popover").length) {
                hidePopover($("#popover-content-social-popover"));
            }
            if ($("#export-citation-popover").find("#popover-content-export-citation-popover").length) {
                hidePopover($("#popover-content-export-citation-popover"));
            }
            showPopover($("#another-popover"), popoverContentAnotherJQ);
        }
        event.stopPropagation();
    });

    /**
     * Menangani klik di luar elemen popover untuk menutup popover
     * @param {Event} event - Event klik pada dokumen
     */
    $(document).on("click", function(event) {
        if (isAnimating) return;

        if (!$(event.target).closest("#popover-content-social-popover, #popover-trigger-social-popover").length) {
            if ($("#social-popover").find("#popover-content-social-popover").length) {
                isAnimating = true;
                hidePopover($("#popover-content-social-popover"));
            }
        }
        if (!$(event.target).closest("#popover-content-export-citation-popover, #popover-trigger-export-citation-popover").length) {
            if ($("#export-citation-popover").find("#popover-content-export-citation-popover").length) {
                isAnimating = true;
                hidePopover($("#popover-content-export-citation-popover"));
            }
        }
        if (!$(event.target).closest("#popover-content-another-popover, #popover-trigger-another-popover").length) {
            if ($("#another-popover").find("#popover-content-another-popover").length) {
                isAnimating = true;
                hidePopover($("#popover-content-another-popover"));
            }
        }
    });

    /**
     * Menangani scroll pada jendela untuk menutup popover jika elemen berada di luar viewport
     */
    $(window).on("scroll", function() {
        if (isAnimating) return;

        var visiblePopoverContent = $(".popover-content:visible");

        visiblePopoverContent.each(function() {
            if (!isElementInViewport(this)) {
                isAnimating = true;
                hidePopover($(this));
            }
        });
    });
});

/**
 * Article Highlight Generator (Supports Indonesian and English)
 * Generates 4 key points from article titles and abstracts with enhanced paraphrasing
 */

(function() {
  'use strict';
  
    // Main function to generate highlights from article abstract
    function generateArticleHighlights(title, abstract) {
      // Detect language from abstract
      const language = detectLanguage(abstract);
      
      // Variable to store the generated highlights
      const highlights = [];
      
      // If not a supported language, return empty array
      if (language !== 'id' && language !== 'en') {
        console.log('Language not supported:', language);
        return highlights;
      }
      
      // 1. Clean and split abstract into sentences
      const sentences = splitIntoSentences(abstract, language);
      if (sentences.length === 0) return highlights;
      
      // 2. Extract main concepts from title
      const titleConcepts = extractMainConcepts(title, language);
      
      // 3. Extract topic models from the abstract for better context understanding
      const abstractTopics = extractTopicModel(abstract, language);
      
      // 4. Score each sentence with enhanced algorithm
      const scoredSentences = sentences.map((sentence, index) => {
        return {
          text: sentence,
          score: scoreSentence(sentence, titleConcepts, abstractTopics, index, sentences.length, language),
          index: index
        };
      });
      
      // 5. Sort sentences based on score (highest to lowest)
      scoredSentences.sort((a, b) => b.score - a.score);
      
      // 6. Set constraints for highlights
      const minHighlights = 3; // Minimal jumlah highlight
      const maxHighlights = 6; // Maksimal jumlah highlight
      const maxTotalWords = 125; // Maksimal total kata untuk semua highlight
      const selectedIndexes = new Set();
      
      // Track concepts already covered to ensure diverse highlights
      const coveredConcepts = new Set();
      let totalWords = 0;
      
      // Iterate through sentences, choose highest scoring ones
      for (const scored of scoredSentences) {
        // Calculate word count for this potential highlight
        const wordsInSentence = scored.text.split(/\s+/).length;
        
        // Check if adding this sentence would exceed our max word limit
        if (totalWords + wordsInSentence > maxTotalWords && highlights.length >= minHighlights) {
          break; // Stop if we've reached min highlights and would exceed word limit
        }
        
        // Check if we already have enough highlights
        if (highlights.length >= maxHighlights) {
          break;
        }
        
        // Avoid sentences that are too close in index (consecutive sentences)
        if (selectedIndexes.size > 0) {
          let tooClose = false;
          for (const idx of selectedIndexes) {
            if (Math.abs(scored.index - idx) <= 1) {
              tooClose = true;
              break;
            }
          }
          if (tooClose) continue;
        }
        
        // Extract concepts from this sentence
        const sentenceConcepts = extractMainConcepts(scored.text, language);
        
        // Check if this sentence covers new concepts
        let hasNewConcept = false;
        for (const concept of sentenceConcepts) {
          if (!coveredConcepts.has(concept)) {
            hasNewConcept = true;
            coveredConcepts.add(concept);
          }
        }
        
        // Prefer sentences with new concepts, but don't exclude if scoring high enough
        if (hasNewConcept || scored.score > 5) {
          // Get highlight text from selected sentence, with advanced paraphrasing
          const highlight = paraphraseHighlight(scored.text, titleConcepts, abstractTopics, language);
          if (highlight && highlight.length >= 5) { // minimum 5 characters
            highlights.push(highlight);
            selectedIndexes.add(scored.index);
            totalWords += highlight.split(/\s+/).length;
            
            // Add all concepts from this sentence to covered concepts
            sentenceConcepts.forEach(concept => coveredConcepts.add(concept));
          }
        }
      }
      
      // 7. If fewer than minHighlights, add from first sentence or title (only if we have room left)
      if (highlights.length < minHighlights) {
        // Use title as highlight if not yet at minHighlights
        if (title && !highlights.includes(title) && highlights.length < minHighlights) {
          const titleHighlight = paraphraseHighlight(title, titleConcepts, abstractTopics, language);
          if (titleHighlight && titleHighlight.length >= 5) {
            const titleWordCount = titleHighlight.split(/\s+/).length;
            if (totalWords + titleWordCount <= maxTotalWords) {
              highlights.unshift(titleHighlight); // Add title as first highlight
              totalWords += titleWordCount;
            }
          }
        }
        
        // If still fewer than minHighlights, use first sentences that weren't selected
        for (let i = 0; i < sentences.length && highlights.length < minHighlights; i++) {
          if (!selectedIndexes.has(i)) {
            const highlight = paraphraseHighlight(sentences[i], titleConcepts, abstractTopics, language);
            if (highlight && highlight.length >= 5 && !highlights.includes(highlight)) {
              const wordCount = highlight.split(/\s+/).length;
              if (totalWords + wordCount <= maxTotalWords) {
                highlights.push(highlight);
                totalWords += wordCount;
              }
            }
          }
        }
      }
      
      // 8. Apply advanced text processing to enhance readability and clarity
      const enhancedHighlights = highlights.map(h => enhanceHighlight(h, language));
      
      // Ensure each highlight is not too long (max 150 characters)
      return enhancedHighlights
        .map(h => shortenHighlight(h, language))
        .filter(h => h.length > 0);
    }
    
    // Advanced function to paraphrase and improve highlight quality
    function paraphraseHighlight(sentence, concepts, topics, language) {
      if (!sentence) return '';
      
      // Clean the sentence
      let cleaned = sentence.trim();
      
      // Remove section labels based on language
      if (language === 'en') {
        cleaned = cleaned.replace(/^(introduction|methods|results|conclusions|discussion):\s*/i, '');
      } else {
        cleaned = cleaned.replace(/^(pendahuluan|metode|hasil|kesimpulan|pembahasan):\s*/i, '');
      }
      
      // Normalize spaces
      cleaned = cleaned.replace(/\s+/g, ' ');
      
      // Extract important information from sentence with enhanced recognition
      const info = extractImportantInfo(cleaned, language, topics);
      
      // If sentence is already short enough (less than 100 characters), use original text
      if (cleaned.length <= 100) {
        // Add punctuation if missing
        return ensureProperPunctuation(cleaned, language);
      }
      
      // Use advanced paraphrasing techniques to make it more concise and clear
      return advancedParaphrase(cleaned, info, topics, language);
    }
    
    // Enhanced extraction of important information from sentence
    function extractImportantInfo(sentence, language, topics = []) {
      // Remove HTML tags if any
      const plainText = stripHtmlTags(sentence);
      
      // Split into words
      const words = plainText.split(/\s+/);
      
      // Information to extract
      const info = {
        subject: '',        // Sentence subject
        predicate: '',      // Predicate/main verb
        object: '',         // Sentence object
        numbers: [],        // Numbers in sentence
        locations: [],      // Location names
        keyTerms: [],       // Key terms
        comparison: '',     // Comparison phrase
        entities: [],       // Named entities
        significance: '',   // Significance phrases
        temporalMarkers: [] // Time-related expressions
      };
      
      // Extract subject (usually at start of sentence)
      info.subject = extractSubject(plainText, language);
      
      // Extract numbers (important for quantitative data)
      info.numbers = extractNumbers(plainText);
      
      // Extract location names with improved recognition
      info.locations = extractLocations(plainText, language);
      
      // Extract key terms with domain awareness
      info.keyTerms = extractKeyTerms(plainText, language, topics);
      
      // Extract comparison phrases
      info.comparison = extractComparison(plainText, language);
      
      // Extract named entities (people, organizations, etc.)
      info.entities = extractEntities(plainText, language);
      
      // Extract significance markers
      info.significance = extractSignificance(plainText, language);
      
      // Extract temporal markers
      info.temporalMarkers = extractTemporalMarkers(plainText, language);
      
      return info;
    }
    
    // Extract named entities (new function)
    function extractEntities(text, language) {
      const entities = [];
      
      // Simple entity detection based on capitalization patterns
      const entityRegex = /\b([A-Z][a-z]+(\s+[A-Z][a-z]+){0,3})\b/g;
      
      let match;
      while ((match = entityRegex.exec(text)) !== null) {
        const potentialEntity = match[1];
        
        // Skip common words that can be capitalized at beginning of sentence
        if (language === 'en') {
          if (!['The', 'A', 'An', 'This', 'These', 'Those', 'It', 'They'].includes(potentialEntity)) {
            entities.push(potentialEntity);
          }
        } else {
          if (!['Ini', 'Itu', 'Para', 'Mereka', 'Hal'].includes(potentialEntity)) {
            entities.push(potentialEntity);
          }
        }
      }
      
      return Array.from(new Set(entities)); // Remove duplicates
    }
    
    // Extract significance markers (new function)
    function extractSignificance(text, language) {
      if (language === 'en') {
        const significancePatterns = [
          /\b(significant(ly)?|important|crucial|critical|essential|key|major|notable)\b/i,
          /\b(highlight(s|ed)?|emphasize[ds]?|stress(es|ed)?)\b/i,
          /\b(demonstrate[ds]?|prove[ds]?|confirm[eds]?|verify|validate[ds]?)\b/i
        ];
        
        for (const pattern of significancePatterns) {
          const match = text.match(pattern);
          if (match) {
            const index = match.index;
            const start = Math.max(0, index - 20);
            const end = Math.min(text.length, index + match[0].length + 30);
            
            return text.substring(start, end).trim();
          }
        }
      } else {
        const significancePatterns = [
          /\b(signifikan|penting|krusial|kritis|esensial|utama|notable)\b/i,
          /\b(menekankan|ditekankan|menonjolkan|ditonjolkan)\b/i,
          /\b(menunjukkan|membuktikan|mengkonfirmasi|memvalidasi)\b/i
        ];
        
        for (const pattern of significancePatterns) {
          const match = text.match(pattern);
          if (match) {
            const index = match.index;
            const start = Math.max(0, index - 20);
            const end = Math.min(text.length, index + match[0].length + 30);
            
            return text.substring(start, end).trim();
          }
        }
      }
      
      return '';
    }
    
    // Extract temporal markers (new function)
    function extractTemporalMarkers(text, language) {
      const markers = [];
      
      if (language === 'en') {
        const temporalPatterns = [
          /\b(in|during|after|before|since|for|over)\s+(\d+\s+)?(years?|months?|weeks?|days?|decades?|centuries?)\b/gi,
          /\b(recent(ly)?|current(ly)?|previous(ly)?|historical(ly)?|contemporary)\b/gi,
          /\b(\d{4}s?|\d{4}-\d{2,4})\b/g // Years or year ranges
        ];
        
        for (const pattern of temporalPatterns) {
          let match;
          while ((match = pattern.exec(text)) !== null) {
            markers.push(match[0]);
          }
        }
      } else {
        const temporalPatterns = [
          /\b(pada|selama|setelah|sebelum|sejak|untuk|selama)\s+(\d+\s+)?(tahun|bulan|minggu|hari|dekade|abad)\b/gi,
          /\b(baru-baru ini|saat ini|sebelumnya|secara historis|kontemporer)\b/gi,
          /\b(\d{4}|\d{4}-\d{2,4})\b/g // Years or year ranges
        ];
        
        for (const pattern of temporalPatterns) {
          let match;
          while ((match = pattern.exec(text)) !== null) {
            markers.push(match[0]);
          }
        }
      }
      
      return Array.from(new Set(markers)); // Remove duplicates
    }
    
    // Extract a topic model from abstract (new function)
    function extractTopicModel(abstract, language) {
      if (!abstract) return [];
      
      // Clean the text
      const cleaned = abstract.toLowerCase()
        .replace(/[^\w\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
      
      // Get stopwords based on language
      const stopWords = getStopwords(language);
      
      // Extract all words, filtering out stopwords and short words
      const words = cleaned.split(' ')
        .filter(word => word.length > 2 && !stopWords.includes(word));
      
      // Count word frequencies
      const wordFreq = {};
      words.forEach(word => {
        wordFreq[word] = (wordFreq[word] || 0) + 1;
      });
      
      // Find bigrams (pairs of words that often appear together)
      const bigrams = [];
      for (let i = 0; i < words.length - 1; i++) {
        const bigram = `${words[i]} ${words[i+1]}`;
        bigrams.push(bigram);
      }
      
      // Count bigram frequencies
      const bigramFreq = {};
      bigrams.forEach(bigram => {
        bigramFreq[bigram] = (bigramFreq[bigram] || 0) + 1;
      });
      
      // Get top words (weighted by frequency)
      const topWords = Object.entries(wordFreq)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 10)
        .map(entry => entry[0]);
      
      // Get top bigrams (weighted by frequency)
      const topBigrams = Object.entries(bigramFreq)
        .filter(entry => entry[1] > 1) // Only include bigrams that appear more than once
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5)
        .map(entry => entry[0]);
      
      // Combine top words and bigrams to form topic model
      return [...topWords, ...topBigrams];
    }
    
    // Get appropriate stopwords based on language
    function getStopwords(language) {
      if (language === 'en') {
        return [
          'the', 'a', 'an', 'and', 'or', 'but', 'is', 'are', 'was', 'were', 'be', 'been',
          'being', 'in', 'on', 'at', 'to', 'for', 'with', 'by', 'about', 'against', 'between',
          'into', 'through', 'during', 'before', 'after', 'above', 'below', 'from', 'up',
          'down', 'of', 'off', 'over', 'under', 'again', 'further', 'then', 'once', 'here',
          'there', 'when', 'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few', 'more',
          'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so',
          'than', 'too', 'very', 's', 't', 'can', 'will', 'just', 'don', 'should', 'now', 'i',
          'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'you', 'your', 'yours',
          'yourself', 'yourselves', 'he', 'him', 'his', 'himself', 'she', 'her', 'hers',
          'herself', 'it', 'its', 'itself', 'they', 'them', 'their', 'theirs', 'themselves',
          'what', 'which', 'who', 'whom', 'this', 'that', 'these', 'those', 'am', 'have', 'has',
          'had', 'having', 'do', 'does', 'did', 'doing', 'would', 'shall', 'should', 'could',
          'may', 'might', 'must', 'study', 'research', 'paper', 'article'
        ];
      } else {
        return [
          'yang', 'di', 'dan', 'itu', 'dengan', 'untuk', 'pada', 'adalah', 'ini', 'dari', 'dalam',
          'akan', 'tidak', 'mereka', 'ke', 'kepada', 'telah', 'tersebut', 'oleh', 'atau', 'sebagai',
          'dapat', 'bisa', 'saya', 'kamu', 'kita', 'kami', 'dia', 'nya', 'anda', 'mereka', 'menjadi',
          'tentang', 'bahwa', 'secara', 'juga', 'ada', 'tanpa', 'para', 'tetapi', 'seperti', 'jika',
          'ketika', 'maka', 'karena', 'belum', 'sudah', 'saat', 'waktu', 'lebih', 'sebuah', 'hingga',
          'penelitian', 'studi', 'jurnal', 'artikel', 'makalah'
        ];
      }
    }
    
    // Advanced paraphrasing technique
    function advancedParaphrase(sentence, info, topics, language) {
      // If extraction of information wasn't successful, do direct shortening
      if (!info.subject) {
        return shortenDirectly(sentence, language);
      }
      
      // Determine new sentence based on language
      let paraphrased = '';
      
      if (language === 'en') {
        // English paraphrasing
        
        // If there are locations, add to subject
        let subject = info.subject;
        if (info.locations.length > 0) {
          if (!subject.includes(info.locations[0])) {
            subject = `${subject} in ${info.locations[0]}`;
          }
        }
        
        // If there are entities, prioritize them
        if (info.entities.length > 0 && !subject.includes(info.entities[0])) {
          subject = info.entities[0] + (subject.length > 0 ? ` ${subject}` : '');
        }
        
        // Create paraphrase based on sentence pattern
        if (sentence.match(/\b(show|indicate|reveal|demonstrate|conclude|find|discover)\w*\b/i)) {
          // Research findings pattern
          let result = '';
          
          // Add significance if available
          if (info.significance) {
            result = `${subject} ${info.significance}`;
          }
          // Add numbers if available
          else if (info.numbers.length > 0) {
            result = `${subject} showed ${info.numbers.join(' and ')}`;
          } 
          // Or key terms
          else if (info.keyTerms.length > 0) {
            result = `${subject} demonstrated ${info.keyTerms[0]}`;
          } 
          // Or comparison phrase
          else if (info.comparison) {
            result = `${subject} ${info.comparison}`;
          } 
          // Or temporal context if available
          else if (info.temporalMarkers.length > 0) {
            result = `${subject} showed significant results ${info.temporalMarkers[0]}`;
          }
          // Or fallback to subject + general verb
          else {
            result = `${subject} was found to be significant`;
          }
          
          paraphrased = result;
        } 
        else if (sentence.match(/\b(aim|goal|purpose|objective|hypothes[ie]s|question)\w*\b/i)) {
          // Research purpose pattern
          if (info.keyTerms.length > 0) {
            paraphrased = `${subject} aims to investigate ${info.keyTerms[0]}`;
          } else {
            paraphrased = `The purpose of ${subject} is to examine this issue`;
          }
        } 
        else if (info.comparison) {
          // Comparison pattern
          paraphrased = `${subject} ${info.comparison}`;
        } 
        else if (info.significance) {
          // Significance pattern
          paraphrased = `${info.significance}`;
        }
        else {
          // General pattern: subject + predicate + key terms
          // Find the most relevant topic from our topic model
          let relevantTopic = '';
          for (const topic of topics) {
            if (sentence.toLowerCase().includes(topic)) {
              relevantTopic = topic;
              break;
            }
          }
          
          if (relevantTopic) {
            paraphrased = `${subject} is related to ${relevantTopic}`;
          } else if (info.keyTerms.length > 0) {
            paraphrased = `${subject} is associated with ${info.keyTerms[0]}`;
          } else {
            paraphrased = `${subject} is significant for the research`;
          }
        }
      } else {
        // Indonesian paraphrasing
        
        // If there are locations, add to subject
        let subject = info.subject;
        if (info.locations.length > 0) {
          if (!subject.includes(info.locations[0])) {
            subject = `${subject} di ${info.locations[0]}`;
          }
        }
        
        // If there are entities, prioritize them
        if (info.entities.length > 0 && !subject.includes(info.entities[0])) {
          subject = info.entities[0] + (subject.length > 0 ? ` ${subject}` : '');
        }
        
        // Create paraphrase based on sentence pattern
        if (sentence.match(/\b(menunjukkan|mengindikasikan|memperlihatkan|membuktikan|menyimpulkan|menemukan)\w*\b/i)) {
          // Research findings pattern
          let result = '';
          
          // Add significance if available
          if (info.significance) {
            result = `${subject} ${info.significance}`;
          }
          // Add numbers if available
          else if (info.numbers.length > 0) {
            result = `${subject} menunjukkan nilai ${info.numbers.join(' dan ')}`;
          } 
          // Or key terms
          else if (info.keyTerms.length > 0) {
            result = `${subject} memperlihatkan adanya ${info.keyTerms[0]}`;
          } 
          // Or comparison phrase
          else if (info.comparison) {
            result = `${subject} ${info.comparison}`;
          } 
          // Or temporal context if available
          else if (info.temporalMarkers.length > 0) {
            result = `${subject} menunjukkan hasil signifikan ${info.temporalMarkers[0]}`;
          }
          // Or fallback to subject + general verb
          else {
            result = `${subject} ditemukan signifikan`;
          }
          
          paraphrased = result;
        } 
        else if (sentence.match(/\b(tujuan|bertujuan|dimaksudkan|diharapkan|hipotesis|pertanyaan)\w*\b/i)) {
          // Research purpose pattern
          if (info.keyTerms.length > 0) {
            paraphrased = `${subject} bertujuan untuk menyelidiki ${info.keyTerms[0]}`;
          } else {
            paraphrased = `Tujuan dari ${subject} adalah untuk mengkaji masalah ini`;
          }
        } 
        else if (info.comparison) {
          // Comparison pattern
          paraphrased = `${subject} ${info.comparison}`;
        } 
        else if (info.significance) {
          // Significance pattern
          paraphrased = `${info.significance}`;
        }
        else {
          // General pattern: subject + predicate + key terms
          // Find the most relevant topic from our topic model
          let relevantTopic = '';
          for (const topic of topics) {
            if (sentence.toLowerCase().includes(topic)) {
              relevantTopic = topic;
              break;
            }
          }
          
          if (relevantTopic) {
            paraphrased = `${subject} berkaitan dengan ${relevantTopic}`;
          } else if (info.keyTerms.length > 0) {
            paraphrased = `${subject} berhubungan dengan ${info.keyTerms[0]}`;
          } else {
            paraphrased = `${subject} signifikan untuk penelitian ini`;
          }
        }
      }
      
      // Ensure the paraphrased result isn't too long
      if (paraphrased.length > 125) {
        paraphrased = paraphrased.substring(0, 120) + '...';
      }
      
      // Ensure it ends with proper punctuation
      return ensureProperPunctuation(paraphrased, language);
    }
    
    // Enhanced highlight processing function
    function enhanceHighlight(text, language) {
      if (!text) return '';
      
      // Replace weak verbs with stronger alternatives based on language
      let enhanced = text;
      
      if (language === 'en') {
        // English verb strengthening
        const verbReplacements = {
          'is': 'proves to be',
          'was': 'proved to be',
          'shows': 'demonstrates',
          'showed': 'demonstrated',
          'indicates': 'reveals',
          'indicated': 'revealed',
          'suggests': 'establishes',
          'suggested': 'established',
          'uses': 'utilizes',
          'used': 'utilized'
        };
        
        // Replace weak verbs with stronger alternatives
        for (const [weak, strong] of Object.entries(verbReplacements)) {
          // Only replace whole words, not parts of words
          const weakRegex = new RegExp(`\\b${weak}\\b`, 'g');
          // Don't replace verbs at the beginning of sentences to maintain clarity
          if (!enhanced.match(new RegExp(`^${weak}\\b`, 'i'))) {
            enhanced = enhanced.replace(weakRegex, strong);
          }
        }
      } else {
        // Indonesian verb strengthening
        const verbReplacements = {
          'adalah': 'terbukti sebagai',
          'menunjukkan': 'membuktikan',
          'mengindikasikan': 'mengungkapkan',
          'menyarankan': 'menetapkan',
          'menggunakan': 'memanfaatkan'
        };
        
        // Replace weak verbs with stronger alternatives
        for (const [weak, strong] of Object.entries(verbReplacements)) {
          // Only replace whole words, not parts of words
          const weakRegex = new RegExp(`\\b${weak}\\b`, 'g');
          // Don't replace verbs at the beginning of sentences to maintain clarity
          if (!enhanced.match(new RegExp(`^${weak}\\b`, 'i'))) {
            enhanced = enhanced.replace(weakRegex, strong);
          }
        }
      }
      
      // Fix capitalization of helper words in the middle of sentences
      // List of helper words that should not be capitalized unless at the start of a sentence
      const helperWords = language === 'en' ? 
        ['the', 'a', 'an', 'in', 'on', 'at', 'by', 'for', 'with', 'to', 'that', 'this', 'these', 'those', 'and', 'or', 'but', 'of'] : 
        ['yang', 'di', 'ke', 'dari', 'pada', 'oleh', 'untuk', 'dengan', 'dan', 'atau', 'tetapi', 'ini', 'itu'];
      
      // Fix capitalization of helper words not at the beginning of the sentence
      const words = enhanced.split(' ');
      
      for (let i = 1; i < words.length; i++) { // Start from index 1 to skip the first word
        const word = words[i];
        const lowerWord = word.toLowerCase();
        
        // If it's a helper word and currently capitalized, make it lowercase
        if (helperWords.includes(lowerWord) && word[0] === word[0].toUpperCase()) {
          words[i] = lowerWord;
        }
      }
      
      // Rejoin the words
      enhanced = words.join(' ');
      
      return enhanced;
    }
    
    // Extract subject from sentence
    function extractSubject(text, language) {
      // Check if sentence starts with common subject phrases
      const plainText = text.toLowerCase();
      let subject = '';
      
      if (language === 'en') {
        // English subject patterns
        const subjectPatterns = [
          /^(this|the|our|these)\s+([a-z]+\s){1,3}/i,
          /^([a-z]+\s){1,2}(is|are|was|were)/i,
          /^([A-Z][a-z]+(\s+[A-Z][a-z]+)*)\s+/i  // Named entities at beginning
        ];
        
        for (const pattern of subjectPatterns) {
          const match = text.match(pattern);
          if (match) {
            subject = match[0].trim();
            // Remove verbs from subject if present
            subject = subject.replace(/\s+(is|are|was|were)$/i, '');
            return subject;
          }
        }
        
        // Take first 2-3 words as estimated subject
        const firstWords = text.split(/\s+/).slice(0, 3).join(' ');
        return firstWords;
      } else {
        // Indonesian subject patterns
        const subjectPatterns = [
          /^(ini|itu|para|beberapa)\s+([a-z]+\s){1,3}/i,
          /^([a-z]+\s){1,3}(adalah|merupakan|ialah)/i,
          /^([A-Z][a-z]+(\s+[A-Z][a-z]+)*)\s+/i  // Named entities at beginning
        ];
        
        for (const pattern of subjectPatterns) {
          const match = text.match(pattern);
          if (match) {
            subject = match[0].trim();
            // Remove 'adalah/merupakan' from subject if present
            subject = subject.replace(/\s+(adalah|merupakan|ialah)$/i, '');
            return subject;
          }
        }
        
        // Take first 2-3 words as estimated subject
        const firstWords = text.split(/\s+/).slice(0, 3).join(' ');
        return firstWords;
      }
    }
    
    // Extract numbers from text
    function extractNumbers(text) {
      const numbers = [];
      const numberRegex = /\d+([.,]\d+)?(\s*%|\s*kg|\s*g|\s*mg|\s*m|\s*cm|\s*mm|\s*l|\s*ml|\s*ha|\s*°C|\s*°F)?/g;
      
      let match;
      while ((match = numberRegex.exec(text)) !== null) {
        numbers.push(match[0]);
      }
      
      return numbers;
    }
    
    // Extract location names with improved recognition
    function extractLocations(text, language) {
      const locations = []; // Deklarasi array yang benar
      
      // Detect locations based on patterns
      if (language === 'en') {
        const locationRegex = /\b(in|at|from|to|across|throughout)\s+([A-Z][a-z]+(\s+[A-Z][a-z]+)*)/g;
        let match;
        while ((match = locationRegex.exec(text)) !== null) {
          locations.push(match[2]);
        }
      } else {
        const locationRegex = /\b(di|pada|dari|ke|melalui|seluruh)\s+([A-Z][a-z]+(\s+[A-Z][a-z]+)*)/g;
        let match;
        while ((match = locationRegex.exec(text)) !== null) {
          locations.push(match[2]);
        }
      }
      
      return Array.from(new Set(locations)); // Remove duplicates
    }
    
    // Extract key terms with improved domain awareness
    function extractKeyTerms(text, language, topics = []) {
      const keyTerms = [];
      
      // For English, look for terms followed by definitions
      if (language === 'en') {
        const termPatterns = [
          /\b([A-Z][a-z]+(\s+[a-z]+){0,2})\s+is\s+(a|an|the)/gi, // Tambahkan flag 'g'
          /\b([A-Z][a-z]+(\s+[a-z]+){0,2})\s+are\s+(a|an|the)/gi, // Tambahkan flag 'g'
          /\btermed\s+as\s+([a-z]+(\s+[a-z]+){0,2})/gi, // Tambahkan flag 'g'
          /\b(known|referred)\s+as\s+([a-z]+(\s+[a-z]+){0,2})/gi, // Tambahkan flag 'g'
          /\b(key|important|crucial|essential)\s+([a-z]+(\s+[a-z]+){0,2})/gi // Tambahkan flag 'g'
        ];
        
        for (const pattern of termPatterns) {
          const matches = text.matchAll(pattern);
          for (const match of matches) {
            keyTerms.push(match[1] || match[2]);
          }
        }
      } else {
        // For Indonesian
        const termPatterns = [
          /\b([A-Z][a-z]+(\s+[a-z]+){0,2})\s+(adalah|merupakan|ialah)\s+/gi, // Tambahkan flag 'g'
          /\bdikenal\s+sebagai\s+([a-z]+(\s+[a-z]+){0,2})/gi, // Tambahkan flag 'g'
          /\bdisebut\s+(sebagai|dengan|juga)\s+([a-z]+(\s+[a-z]+){0,2})/gi, // Tambahkan flag 'g'
          /\b(kunci|penting|krusial|esensial)\s+([a-z]+(\s+[a-z]+){0,2})/gi // Tambahkan flag 'g'
        ];
        
        for (const pattern of termPatterns) {
          const matches = text.matchAll(pattern);
          for (const match of matches) {
            keyTerms.push(match[1] || match[2]);
          }
        }
      }
      
      // Sisa kode tidak perlu diubah
      const acronymRegex = /\b[A-Z]{2,}\b/g;
      let match;
      while ((match = acronymRegex.exec(text)) !== null) {
        keyTerms.push(match[0]);
      }
      
      for (const topic of topics) {
        if (text.toLowerCase().includes(topic.toLowerCase())) {
          keyTerms.push(topic);
        }
      }
      
      return Array.from(new Set(keyTerms)); // Remove duplicates
    }
    
    // Extract comparison phrases
    function extractComparison(text, language) {
      if (language === 'en') {
        // English comparison patterns
        const comparisonPatterns = [
          /\b(higher|lower|greater|less|more|fewer|increased|decreased)\s+than\b/i,
          /\b(compared|relative)\s+to\b/i,
          /\b(significant(ly)?)\s+(higher|lower|greater|less|more|fewer)/i,
          /\b(outperform(s|ed)?|exceed(s|ed)?)\b/i,
          /\b(superior|inferior)\s+to\b/i
        ];
        
        for (const pattern of comparisonPatterns) {
          const match = text.match(pattern);
          if (match) {
            // Find context around comparison phrase
            const index = match.index;
            const start = Math.max(0, index - 20);
            const end = Math.min(text.length, index + match[0].length + 30);
            
            return text.substring(start, end).trim();
          }
        }
      } else {
        // Indonesian comparison patterns
        const comparisonPatterns = [
          /\b(lebih|kurang)\s+(tinggi|rendah|besar|kecil|banyak|sedikit)\s+dari(pada)?\b/i,
          /\b(dibandingkan|relatif)\s+dengan\b/i,
          /\b(secara|sangat|cukup)\s+(signifikan|nyata|berarti)\s+(lebih|kurang)/i,
          /\b(melebihi|mengungguli)\b/i,
          /\b(superior|inferior)\s+terhadap\b/i
        ];
        
        for (const pattern of comparisonPatterns) {
          const match = text.match(pattern);
          if (match) {
            // Find context around comparison phrase
            const index = match.index;
            const start = Math.max(0, index - 20);
            const end = Math.min(text.length, index + match[0].length + 30);
            
            return text.substring(start, end).trim();
          }
        }
      }
      
      return '';
    }
    
    // Direct shortening for sentences
    function shortenDirectly(sentence, language) {
      const words = sentence.split(/\s+/);
      
      // If sentence is already short enough, use as is
      if (words.length <= 15) {
        return sentence;
      }
      
      // Find a good cut point
      let cutPoint = 15; // Default: first 15 words
      
      // Find cut point based on key phrases according to language
      if (language === 'en') {
        // English key phrases
        for (let i = 10; i < Math.min(30, words.length); i++) {
          const phrase = words.slice(i-2, i+1).join(' ').toLowerCase();
          if (phrase.match(/\b(however|therefore|thus|in conclusion|as a result|furthermore|in addition|consequently|meanwhile|nevertheless)\b/)) {
            cutPoint = i-2;
            break;
          }
        }
      } else {
        // Indonesian key phrases
        for (let i = 10; i < Math.min(30, words.length); i++) {
          const phrase = words.slice(i-2, i+1).join(' ').toLowerCase();
          if (phrase.match(/\b(namun|tetapi|oleh karena itu|dengan demikian|sebagai kesimpulan|akibatnya|selain itu|konsekuensinya|sementara itu|meskipun demikian)\b/)) {
            cutPoint = i-2;
            break;
          }
        }
      }
      
      // Cut at determined point
      const shortened = words.slice(0, cutPoint).join(' ');
      
      // Ensure it doesn't end with a connector word
      const lastWord = shortened.split(/\s+/).pop().toLowerCase();
      
      // Connector words based on language
      let connectors = [];
      
      if (language === 'en') {
        connectors = ['and', 'or', 'but', 'that', 'which', 'when', 'where', 'while', 'because', 
                      'as', 'if', 'for', 'to', 'with', 'by', 'from', 'of', 'in', 'on', 'at'];
      } else {
        connectors = ['dan', 'atau', 'tetapi', 'yang', 'ketika', 'dimana', 'sementara', 'karena',
                     'sebagai', 'jika', 'untuk', 'ke', 'dengan', 'oleh', 'dari', 'di', 'pada'];
      }
      
      let final = shortened;
      if (connectors.includes(lastWord)) {
        final = shortened.substring(0, shortened.lastIndexOf(' '));
      }
      
      // Ensure it ends with proper punctuation
      return ensureProperPunctuation(final, language);
    }
    
    // Ensure sentence ends with proper punctuation
    function ensureProperPunctuation(text, language) {
      const cleaned = text.trim();
      
      // If already ends with punctuation, use as is
      if (cleaned.match(/[.!?]$/)) {
        return cleaned;
      }
      
      // Check if it's a statement or question
      return cleaned.match(/\bapa\b|\bsiapa\b|\bbagaimana\b|\bkapan\b|\bdimana\b|\bwhat\b|\bwhy\b|\bhow\b|\bwhen\b|\bwhere\b/i) ? 
        `${cleaned}?` : `${cleaned}.`;
    }
    
    // Improve scoring algorithm for sentences with additional criteria
    function scoreSentence(sentence, titleConcepts, abstractTopics, position, totalSentences, language) {
      if (!sentence) return 0;
      
      const words = sentence.toLowerCase().split(/\s+/);
      let score = 0;
      
      // 1. Position score: sentences at beginning and end typically more important
      if (position === 0 || position === totalSentences - 1) {
        score += 3; // First and last sentences
      } else if (position <= Math.floor(totalSentences * 0.2)) {
        score += 2; // First 20% of sentences
      } else if (position >= Math.floor(totalSentences * 0.8)) {
        score += 1; // Last 20% of sentences
      }
      
      // 2. Length score: sentences too short or too long are less ideal
      const length = words.length;
      if (length >= 10 && length <= 25) {
        score += 2; // Ideal length
      } else if (length < 10 && length >= 5) {
        score += 1; // Somewhat short but still ok
      } else if (length > 25 && length <= 40) {
        score -= 1; // Somewhat long
      } else if (length > 40) {
        score -= 2; // Too long
      }
      
      // 3. Title keyword score: contains important words from title
      for (const concept of titleConcepts) {
        if (sentence.toLowerCase().includes(concept.toLowerCase())) {
          score += 2; // Contains concept from title
        }
      }
      
      // 4. Abstract topic score: contains important topics from abstract
      for (const topic of abstractTopics) {
        if (sentence.toLowerCase().includes(topic.toLowerCase())) {
          score += 1.5; // Contains topic from abstract
        }
      }
      
      // 5. Important content indicator score based on language
      let importantPatterns = [];
      
      if (language === 'en') {
        // Patterns for English
        importantPatterns = [
          /\b(result|found|show|reveal|conclude|significant|important)\w*\b/i, // Important results
          /\b(aim|goal|purpose|objective)\w*\b/i,                              // Purpose
          /\b(\d+\s*%|\d+\.\d+)\b/,                                            // Statistics/numbers
          /\b(increase|decrease|higher|lower|better|worse|more|less)\b/i,     // Comparisons
          /\b(first|novel|new|unique|innovative)\b/i,                         // Novelty
          /\b(evidence|prove|confirm|validate|support|consistent)\b/i,        // Evidence
          /\b(method|approach|technique|procedure|protocol)\b/i               // Methods
        ];
      } else {
        // Patterns for Indonesian
        importantPatterns = [
          /\b(hasil|menunjukkan|ditemukan|disimpulkan|signifikan|penting)\w*\b/i, // Important results
          /\b(tujuan|bertujuan|dimaksudkan|diharapkan)\w*\b/i,                    // Purpose
          /\b(\d+\s*%|\d+\,\d+)\b/,                                               // Statistics/numbers
          /\b(meningkat|menurun|lebih tinggi|lebih rendah|lebih baik|lebih buruk)\b/i, // Comparisons
          /\b(pertama|baru|unik|inovatif)\b/i,                                   // Novelty
          /\b(bukti|membuktikan|mengkonfirmasi|memvalidasi|mendukung|konsisten)\b/i, // Evidence
          /\b(metode|pendekatan|teknik|prosedur|protokol)\b/i                    // Methods
        ];
      }
      
      for (const pattern of importantPatterns) {
        if (pattern.test(sentence)) {
          score += 1;
        }
      }
      
      // 6. Entity score: contains named entities
      const entityRegex = /\b([A-Z][a-z]+(\s+[A-Z][a-z]+){0,3})\b/g;
      let entityCount = 0;
      let match;
      
      while ((match = entityRegex.exec(sentence)) !== null) {
        entityCount++;
      }
      
      // Add score based on entity density
      if (entityCount > 0) {
        score += Math.min(1.5, entityCount * 0.3); // Cap at 1.5 points
      }
      
      // 7. Temporal marker score: references to time periods
      if (language === 'en') {
        if (sentence.match(/\b(\d{4}|\d{4}-\d{2,4}|recent(ly)?|current(ly)?|previous(ly)?)\b/i)) {
          score += 1;
        }
      } else {
        if (sentence.match(/\b(\d{4}|\d{4}-\d{2,4}|baru-baru ini|saat ini|sebelumnya)\b/i)) {
          score += 1;
        }
      }
      
      return score;
    }
    
    // Language detection from text
    function detectLanguage(text) {
      if (!text || text.trim().length === 0) return 'unknown';
      
      // Count characteristic words in Indonesian vs English
      const idWords = ['yang', 'dan', 'di', 'dengan', 'untuk', 'pada', 'adalah', 'ini', 'dari', 'dalam',
                       'tidak', 'akan', 'mereka', 'ke', 'telah', 'tersebut', 'oleh', 'atau', 'sebagai',
                       'dapat', 'bisa', 'menjadi', 'tentang', 'bahwa', 'secara', 'juga'];
                       
      const enWords = ['the', 'and', 'of', 'to', 'in', 'is', 'that', 'for', 'it', 'as',
                       'with', 'was', 'on', 'are', 'by', 'this', 'be', 'from', 'an', 'not',
                       'have', 'were', 'they', 'has', 'their', 'which'];
      
      // Prepare text for analysis
      const lowercased = text.toLowerCase();
      const words = lowercased.split(/\s+/);
      
      // Count occurrences of characteristic words
      let idCount = 0;
      let enCount = 0;
      
      for (const word of words) {
        if (idWords.includes(word)) idCount++;
        if (enWords.includes(word)) enCount++;
      }
      
      // Determine language based on comparison of word counts
      // Normalize based on number of characteristic words in list
      const idRatio = idCount / idWords.length;
      const enRatio = enCount / enWords.length;
      
      // Use ratio to determine language
      if (idRatio > enRatio && idCount >= 3) {
        return 'id';
      } else if (enRatio > idRatio && enCount >= 3) {
        return 'en';
      }
      
      // If still uncertain, check grammatical patterns
      // Patterns typical of Indonesian
      const idPatterns = [
        /\b(yang|untuk|dengan)\s+di/i,
        /\b(telah|sudah|akan)\s+(di|me)/i,
        /\bdi\s+[a-z]+kan\b/i
      ];
      
      // Patterns typical of English
      const enPatterns = [
        /\b(has|have|had)\s+been\b/i,
        /\b(is|are|was|were)\s+(the|a|an)\b/i,
        /\b(in|on|at)\s+the\b/i
      ];
      
      for (const pattern of idPatterns) {
        if (pattern.test(lowercased)) {
          return 'id';
        }
      }
      
      for (const pattern of enPatterns) {
        if (pattern.test(lowercased)) {
          return 'en';
        }
      }
      
      // Default if unable to determine with certainty
      return (idCount >= enCount) ? 'id' : 'en';
    }
    
    // Split text into sentences (supporting Indonesian)
    function splitIntoSentences(text, language) {
      if (!text) return [];
      
      // Different regex for Indonesian and English
      let sentences = [];
      
      // Pre-process: handle common abbreviations and special patterns
      let preprocessed = text;
      
      if (language === 'en') {
        // Pre-process for English
        preprocessed = text
          .replace(/(\b[A-Z][a-z]*\.)(\s*[A-Z][a-z]*\.)+/g, match => match.replace(/\./g, '@DOT@')) // Abbreviations like U.S.A.
          .replace(/([A-Za-z]\.)(\d)/g, '$1@DOT@$2') // Patterns like "Fig.2" or "p.34"
          .replace(/(\b[A-Za-z]\.)(\s+[a-z])/g, '$1@DOT@$2') // Abbreviations in middle of sentence
          .replace(/(\b[A-Z][a-z]*\.)(\s+[A-Z])/g, '$1@DOT@$2'); // Proper handling of abbreviations
          
        // Split based on end-of-sentence punctuation for English
        sentences = preprocessed.split(/(?<=[.!?])\s+(?=[A-Z"])/);
      } else {
        // Pre-process for Indonesian
        preprocessed = text
          .replace(/(\b[A-Z][a-z]*\.)(\s*[A-Z][a-z]*\.)+/g, match => match.replace(/\./g, '@DOT@')) // Abbreviations
          .replace(/([A-Za-z]\.)(\d)/g, '$1@DOT@$2') // Figure or page
          .replace(/(\b[A-Za-z]\.)(\s+[a-z])/g, '$1@DOT@$2') // Other abbreviations
          .replace(/(\bdrs?\.)(\s+[A-Z])/g, '$1@DOT@$2') // dr. or drs.
          .replace(/(\bbpk?\.)(\s+[A-Z])/g, '$1@DOT@$2'); // bp. or bpk.
          
        // Split based on end-of-sentence punctuation for Indonesian
        // Indonesian may use lowercase after periods
        sentences = preprocessed.split(/(?<=[.!?])\s+/);
      }
      
      // Post-split processing: restore abbreviations and fix sentences
      sentences = sentences
        .map(s => s.replace(/@DOT@/g, '.').trim())
        .filter(s => s.length > 10); // Filter sentences that are too short
      
      return sentences;
    }
    
    // Extract main concepts from text based on language
    function extractMainConcepts(text, language) {
      if (!text) return [];
      
      // Clean and lowercase
      const cleaned = text.toLowerCase()
        .replace(/[^\w\s]/g, ' ') // Replace punctuation with spaces
        .replace(/\s+/g, ' ')     // Normalize spaces
        .trim();
      
      // Choose stopwords based on language
      const stopWords = getStopwords(language);
      
      // Extract words, filter stopwords and short words
      const words = cleaned.split(' ')
        .filter(word => word.length > 2 && !stopWords.includes(word));
      
      // Count word frequency
      const wordFreq = {};
      words.forEach(word => {
        wordFreq[word] = (wordFreq[word] || 0) + 1;
      });
      
      // Get words with highest frequency (max 5 words)
      return Object.entries(wordFreq)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5)
        .map(entry => entry[0]);
    }
    
    // Utility function to remove HTML tags
    function stripHtmlTags(html) {
      if (!html || typeof html !== 'string') return "";
      return html.replace(/<\/?[^>]+(>|$)/g, "");
    }
    
    // Make highlight shorter with focus on important parts
    function shortenHighlight(text, language) {
      if (!text) return '';
      
      const maxLength = 125; // Maximum 150 characters default
      
      // If already short enough, use as is
      if (text.length <= maxLength) return text;
      
      // Cut based on sentence
      const sentences = text.split(/(?<=[.!?])\s+/);
      let shortened = sentences[0];
      
      // Add next sentence if still within limit
      for (let i = 1; i < sentences.length; i++) {
        if ((shortened + ' ' + sentences[i]).length <= maxLength) {
          shortened += ' ' + sentences[i];
        } else {
          break;
        }
      }
      
      // If still too long, make intelligent cut
      if (shortened.length > maxLength) {
        // Find last position of "., !, ?" markers
        const lastPunctuation = Math.max(
          shortened.lastIndexOf('. ', maxLength),
          shortened.lastIndexOf('! ', maxLength),
          shortened.lastIndexOf('? ', maxLength)
        );
        
        if (lastPunctuation > maxLength / 2) {
          // Cut at last punctuation still within limit
          shortened = shortened.substring(0, lastPunctuation + 1);
        } else {
          // If no suitable punctuation, cut at last space
          const lastSpace = shortened.lastIndexOf(' ', maxLength - 3);
          if (lastSpace > maxLength / 2) {
            shortened = shortened.substring(0, lastSpace) + '...';
          } else {
            // If no good cut point found, cut at maximum limit
            shortened = shortened.substring(0, maxLength - 3) + '...';
          }
        }
      }
      
      return shortened;
    }
    
    // Function to get article components from page
    function getArticleComponents() {
      try {
        // Article components
        const components = {
          title: "",
          abstract: ""
        };
        
        // Try to find article title
        const titleSelectors = [
          'h1.article-title',
          'h1.title',
          'h1.page-title',
          'h1:first-of-type',
          '.article-title',
          '#article-title',
          'meta[name="citation_title"]',
          'meta[property="og:title"]',
          // Added more selectors for better coverage
          '.journal-article-title',
          '.content-header h1',
          '.article-header h1',
          '.wi-article-title h1',
          '.article__headline',
          '.article__title',
          '.article_header h1',
          'h2.title'
        ];
        
        for (const selector of titleSelectors) {
          const titleElement = document.querySelector(selector);
          if (titleElement) {
            components.title = titleElement.textContent || titleElement.content || '';
            break;
          }
        }
        
        // Try to find article abstract
        const abstractSelectors = [
          '#ab005[lang="en"] p#sp0005',
          '#ab005[lang="id"] p#sp0005',
          '.abstract',
          '#abstract',
          'section.abstract p',
          'div.abstract-content',
          '#abstract-content',
          'meta[name="description"]',
          'meta[name="citation_abstract"]',
          // Added more selectors for better coverage
          '.journal-article-abstract',
          '.article-section__abstract',
          '.article-abstract',
          '.abstract-content',
          '.abstract-group',
          '.abstract-section',
          '.wi-abstract',
          '.article__abstract',
          'section[aria-labelledby="abstract"]',
          '.articleText',
          '.article-text > p:first-child'
        ];
        
        for (const selector of abstractSelectors) {
          const abstractElement = document.querySelector(selector);
          if (abstractElement) {
            components.abstract = abstractElement.innerHTML || abstractElement.content || '';
            break;
          }
        }
        
        return components;
      } catch (error) {
        console.error('Error getting article components:', error);
        return { title: "", abstract: "" };
      }
    }
    
    // Main function to display highlights on page
    function displayArticleHighlights() {
      try {
        // Get article title and abstract
        const { title, abstract } = getArticleComponents();
        
        if (!abstract) {
          console.error('Abstract not found.');
          return;
        }
        
        // Detect abstract language
        const language = detectLanguage(abstract);
        
        // Only generate highlights for supported languages (English and Indonesian)
        if (language !== 'en' && language !== 'id') {
          console.log(`Language (${language}) not supported. Highlights will not be displayed.`);
          return;
        }
        
        // Generate highlights
        const highlights = generateArticleHighlights(title, abstract);
        
        // If no highlights generated, exit
        if (highlights.length === 0) {
          console.log('No highlights generated.');
          return;
        }
        
        // Cari container highlight yang sudah ada
        const containerElement = document.getElementById('sp1310');
        
        if (!containerElement) {
          console.error('Highlight container (#sp1310) not found.');
          return;
        }
        
        // Cari atau buat elemen untuk daftar highlight
        let highlightsElement = containerElement.querySelector('ul.non-list');
        if (!highlightsElement) {
          // Jika elemen tidak ditemukan, buat elemen baru
          highlightsElement = document.createElement('ul');
          highlightsElement.className = 'non-list';
          containerElement.appendChild(highlightsElement);
        }
        
        // Buat HTML untuk highlight sesuai struktur yang sudah ada
        let html = '';
        
        for (let highlight of highlights) {
          html += `
            <li class="react-xocs-list-item">
              <span class="list-label">• </span>
              <span class="u-ml-16"><p>${highlight}</p></span>
            </li>`;
        }
        
        highlightsElement.innerHTML = html;
        
        // Tampilkan elemen highlight jika sebelumnya tersembunyi
        const highlightSection = document.getElementById('ab810');
        if (highlightSection && highlightSection.classList.contains('u-js-hide')) {
          highlightSection.classList.remove('u-js-hide');
        }
        
        // Atur tag kredit jika ada
        const creditElement = document.querySelector('.highlights.u-font-sans');
        if (creditElement) {
          creditElement.textContent = 'Generate NLP-AI by Wizdam. v2';
        }
        
        console.log(`Highlights in ${language === 'en' ? 'English' : 'Indonesian'} successfully displayed:`, highlights);
      } catch (error) {
        console.error('Error displaying highlights:', error);
      }
    }
    
    // Run function when DOM is fully loaded
    document.addEventListener('DOMContentLoaded', displayArticleHighlights);
    
    // For external use (e.g., with Node.js)
    if (typeof module !== 'undefined') {
      module.exports = {
        generateArticleHighlights,
        paraphraseHighlight,
        detectLanguage,
        getArticleComponents,
        enhanceHighlight,
        extractTopicModel
      };
    }
})();

// 
// Menampilkan naskah pdf
// hasil ekstrak ditampilkan pada halaman artikel
//
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';

async function getPdfText(url) {
    try {
        const pdf = await pdfjsLib.getDocument(url).promise;
        let text = '';
        let structuredText = [];
        
        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const textContent = await page.getTextContent();
            
            // Process text content by preserving paragraph structure
            let lastY = null;
            let paragraph = '';
            
            textContent.items.forEach(item => {
                // Detect paragraph breaks based on Y position changes beyond a threshold
                if (lastY !== null && Math.abs(item.transform[5] - lastY) > 5) {
                    if (paragraph.trim()) {
                        structuredText.push(paragraph.trim());
                        paragraph = '';
                    }
                }
                
                paragraph += item.str + ' ';
                lastY = item.transform[5];
            });
            
            // Add the last paragraph of the page
            if (paragraph.trim()) {
                structuredText.push(paragraph.trim());
            }
        }
        
        // Join paragraphs with proper line breaks
        text = structuredText.join('\n\n');
        
        // Apply text corrections for common OCR issues
        text = text.replace(/REFERENS\sI/gi, "REFERENSI")
                   .replace(/INTRODUC\sTION/gi, "INTRODUCTION")
                   .replace(/REFEREN\sSI/gi, "REFERENSI")
                   .replace(/P e n d a h u l u a n/gi, "Pendahuluan")
                   .replace(/A l a t d a n B a h a n/gi, "Alat dan Bahan")
                   .replace(/H a s i l d a n P e m b a h a s a n/gi, "Hasil dan Pembahasan")
                   .replace(/U c a p a n T e r i m a K a s i h/gi, "Ucapan Terima Kasih")
                   .replace(/R e f e r e n c e s/gi, "References")
                   .replace(/I I I/gi, "III")
                   .replace(/I I/gi, "II")
                   .replace(/M a t e r i a l s? a n d M e t h o d s?/gi, "Materials and Methods")
                   .replace(/R e s u l t s?/gi, "Results")
                   .replace(/D i s c u s s i o n/gi, "Discussion")
                   .replace(/C o n c l u s i o n s?/gi, "Conclusion");
        
        return { fullText: text, paragraphs: structuredText };
    } catch (error) {
        console.error(`Error extracting PDF text: ${error}`);
        return { fullText: '', paragraphs: [] };
    }
}

function formatSectionContent(content) {
    if (!content) return 'Not found in PDF for this article.';
    
    // Split content into paragraphs and remove very short lines
    const paragraphs = content.split('\n\n')
        .filter(p => p.trim().length > 10)
        .map(p => p.trim());
    
    // Format paragraphs with proper HTML
    return paragraphs.map(p => `<p>${p}</p>`).join('');
}

function extractSection(text, startPattern, endPattern) {
    try {
        const startRegex = new RegExp(startPattern, 'i');
        const endRegex = new RegExp(endPattern, 'i');
        
        const startMatch = text.match(startRegex);
        if (!startMatch) return '';
        
        const startIdx = startMatch.index + startMatch[0].length;
        
        const endMatch = text.substring(startIdx).match(endRegex);
        const endIdx = endMatch ? startIdx + endMatch.index : text.length;
        
        return text.substring(startIdx, endIdx).trim();
    } catch (error) {
        console.error(`Error extracting section with patterns ${startPattern} and ${endPattern}: ${error}`);
        return '';
    }
}

function processPaperSections(text) {
    // Identify document structure by checking various section patterns
    const isImradFormat = /(?:^|\s)(?:1\.?\s*(?:pendahuluan|introduction)|introduction|pendahuluan)/i.test(text);
    
    // Default empty sections
    const sections = {
        introduction: '',
        methods: '',
        results: '',
        discussion: '',
        resultsAndDiscussion: '',
        conclusion: '',
        acknowledgments: ''
    };

    if (!isImradFormat) {
        console.warn('No standard IMRAD format detected in the document');
        return sections;
    }

    // Extract sections with various possible headings
    sections.introduction = extractSection(
        text,
        '(?:^|\\n|\\s)(?:1\\.?\\s*)?(?:introduction|pendahuluan|pengantar)\\s*(?:\\n|$)',
        '(?:^|\\n|\\s)(?:2\\.?\\s*)?(?:materials? and methods?|methodology|bahan dan metod(?:e|ologi)|metode penelitian)\\s*(?:\\n|$)'
    );
    
    sections.methods = extractSection(
        text,
        '(?:^|\\n|\\s)(?:2\\.?\\s*)?(?:materials? and methods?|methodology|bahan dan metod(?:e|ologi)|metode penelitian)\\s*(?:\\n|$)',
        '(?:^|\\n|\\s)(?:3\\.?\\s*)?(?:results(?: and discussion)?|hasil(?: dan pembahasan)?)\\s*(?:\\n|$)'
    );
    
    // Check if results and discussion are separate or combined
    const hasResultsDiscussionCombined = /(?:^|\n|\s)(?:3\.?\s*)?(?:results and discussion|hasil dan pembahasan)\s*(?:\n|$)/i.test(text);
    
    if (hasResultsDiscussionCombined) {
        sections.resultsAndDiscussion = extractSection(
            text,
            '(?:^|\\n|\\s)(?:3\\.?\\s*)?(?:results and discussion|hasil dan pembahasan)\\s*(?:\\n|$)',
            '(?:^|\\n|\\s)(?:4\\.?\\s*)?(?:conclusion|conclusions|simpulan|kesimpulan)\\s*(?:\\n|$)'
        );
    } else {
        sections.results = extractSection(
            text,
            '(?:^|\\n|\\s)(?:3\\.?\\s*)?(?:results|hasil)\\s*(?:\\n|$)',
            '(?:^|\\n|\\s)(?:4\\.?\\s*)?(?:discussion|pembahasan|diskusi)\\s*(?:\\n|$)'
        );
        
        sections.discussion = extractSection(
            text,
            '(?:^|\\n|\\s)(?:4\\.?\\s*)?(?:discussion|pembahasan|diskusi)\\s*(?:\\n|$)',
            '(?:^|\\n|\\s)(?:5\\.?\\s*)?(?:conclusion|conclusions|simpulan|kesimpulan)\\s*(?:\\n|$)'
        );
    }
    
    // Extract conclusion section
    const conclusionStartPattern = '(?:^|\\n|\\s)(?:[4-6]\\.?\\s*)?(?:conclusion|conclusions|simpulan|kesimpulan)\\s*(?:\\n|$)';
    const ackStartPattern = '(?:^|\\n|\\s)(?:acknowledgements?|ucapan terima kasih)\\s*(?:\\n|$)';
    const refStartPattern = '(?:^|\\n|\\s)(?:references|daftar pustaka|referensi)\\s*(?:\\n|$)';
    
    sections.conclusion = extractSection(
        text,
        conclusionStartPattern,
        `(?:${ackStartPattern}|${refStartPattern})`
    );
    
    // Extract acknowledgments section if present
    sections.acknowledgments = extractSection(
        text,
        ackStartPattern,
        refStartPattern
    );
    
    return sections;
}

function toggleSectionVisibility(sectionId, hasContent) {
    const section = document.getElementById(sectionId);
    if (section) {
        if (hasContent) {
            section.classList.remove('u-js-hide');
        } else {
            section.classList.add('u-js-hide');
        }
    }
}

async function processPdf(url) {
    try {
        // Show loading indicator
        document.getElementById('pdf-content').classList.add('loading');
        
        // Extract text from PDF
        const { fullText, paragraphs } = await getPdfText(url);
        
        if (!fullText) {
            console.error('Failed to extract text from PDF');
            document.getElementById('pdf-content').classList.remove('loading');
            return;
        }
        
        // Process sections from extracted text
        const sections = processPaperSections(fullText);
        
        // Update HTML sections
        // Introduction
        const introSection = document.getElementById('sec1');
        if (introSection) {
            introSection.innerHTML = formatSectionContent(sections.introduction);
            toggleSectionVisibility('preview-section-introduction', sections.introduction);
        }
        
        // Materials and Methods
        const methodsSection = document.getElementById('p0070');
        if (methodsSection) {
            methodsSection.innerHTML = formatSectionContent(sections.methods);
        }
        
        // Results section
        const resultsSection = document.getElementById('sec3');
        const discussionSection = document.getElementById('sec4');
        
        if (sections.resultsAndDiscussion) {
            // Combined results and discussion
            if (resultsSection) {
                resultsSection.innerHTML = formatSectionContent(sections.resultsAndDiscussion);
            }
            if (discussionSection && discussionSection.querySelector('p')) {
                discussionSection.querySelector('p').innerHTML = 'See Results section for combined Results and Discussion.';
            }
        } else {
            // Separate results and discussion
            if (resultsSection) {
                resultsSection.innerHTML = formatSectionContent(sections.results);
            }
            if (discussionSection && discussionSection.querySelector('p')) {
                discussionSection.querySelector('p').innerHTML = formatSectionContent(sections.discussion);
            }
        }
        
        // Toggle visibility of snippets section
        toggleSectionVisibility('preview-section-snippets', 
            sections.methods || sections.results || sections.discussion || sections.resultsAndDiscussion);
        
        // Conclusion
        const conclusionSection = document.getElementById('p0317');
        if (conclusionSection) {
            conclusionSection.innerHTML = formatSectionContent(sections.conclusion);
            toggleSectionVisibility('con5', sections.conclusion);
        }
        
        // Acknowledgment
        const ackSection = document.getElementById('p0350');
        if (ackSection) {
            ackSection.innerHTML = formatSectionContent(sections.acknowledgments);
            toggleSectionVisibility('ack0010', sections.acknowledgments);
        }
        
        // Remove loading indicator
        document.getElementById('pdf-content').classList.remove('loading');
    } catch (error) {
        console.error(`Error processing PDF: ${error}`);
        document.getElementById('pdf-content').classList.remove('loading');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const pdfElement = document.querySelector('.pdf-file');
    let pdfUrl = pdfElement ? pdfElement.href : null;

    if (pdfUrl) {
        console.log(`Original PDF URL: ${pdfUrl}`);
        pdfUrl = pdfUrl.replace('/view/', '/viewFile/');
        console.log(`Converted PDF URL: ${pdfUrl}`);
        processPdf(pdfUrl);
    } else {
        console.error('PDF URL not found.');
    }
});

/**
 * Fungsi untuk mengelola tampilan elemen <p> di dalam bagian dengan kelas .bibliography-sec.
 * Jika jumlah elemen <p> melebihi 17, elemen-elemen tersebut akan dihapus,
 * menampilkan pemberitahuan, dan menggunakan tombol view-more yang sudah ada untuk mengatur visibilitas elemen.
 * Juga mengupdate jumlah total elemen <p> di dalam elemen .section-title pada span dengan class "count".
 */
document.addEventListener('DOMContentLoaded', function () {
    const bibliographySections = document.querySelectorAll('.ref-bibliography');
    let counter = 7;

    const removeExcessItems = (items) => items.map(item => {
        item.parentNode.removeChild(item);
        return item;
    });

    const toggleItemsVisibility = (section, viewMoreButton, notice, items) => {
        const isExpanded = viewMoreButton.classList.contains('view-less');
        if (isExpanded) {
            removeExcessItems(items);
            section.appendChild(notice);
            viewMoreButton.querySelector('.button-alternative-text').textContent = 'View more references';
            viewMoreButton.classList.remove('view-less');
            viewMoreButton.querySelector('svg').style.transform = 'rotate(0deg)';
        } else {
            items.forEach(item => {
                section.insertBefore(item, viewMoreButton);
            });
            section.removeChild(notice);
            viewMoreButton.querySelector('.button-alternative-text').textContent = 'View less references';
            viewMoreButton.classList.add('view-less');
            viewMoreButton.querySelector('svg').style.transform = 'rotate(180deg)';
        }
    };

    const splitParagraphs = (section) => {
        const paragraphs = section.querySelectorAll('p');
        paragraphs.forEach(paragraph => {
            if (paragraph.innerHTML.includes('<br>')) {
                const splitContent = paragraph.innerHTML.split('<br>');
                splitContent.forEach((content, index) => {
                    if (index === 0) {
                        paragraph.innerHTML = content.trim();
                    } else {
                        const newParagraph = document.createElement('p');
                        newParagraph.innerHTML = content.trim();
                        paragraph.parentNode.insertBefore(newParagraph, paragraph.nextSibling);
                    }
                });
            }
        });
    };

    bibliographySections.forEach(section => {
        splitParagraphs(section);
        let items = Array.from(section.querySelectorAll('p')).filter(item => item.textContent.trim() !== "");
        items.forEach(item => {
            if (!item.hasAttribute('id') && !item.hasAttribute('class')) {
                item.setAttribute('id', `ref-sangia${String(counter).padStart(3, '0')}`);
                item.setAttribute('class', 'reference');
                counter++;
            }
        });

        const totalItems = items.length;
        const sectionTitle = section.closest('.bibliography').querySelector('.section-title .count');
        if (sectionTitle) sectionTitle.textContent = `(${totalItems})`;

        const viewMoreButton = section.querySelector('.view-more');
        const notice = section.querySelector('.notice');

        if (totalItems > 17) {
            const removedItems = removeExcessItems(items.slice(17));
            section.appendChild(notice);
            section.appendChild(viewMoreButton);
            viewMoreButton.addEventListener('click', () => toggleItemsVisibility(section, viewMoreButton, notice, removedItems));
        } else {
            section.removeChild(notice);
            section.removeChild(viewMoreButton);
        }
    });
});

$(document).ready(function() {
    const toggleLoading = show => {
        const loadingSvg = `<div class="loading-indicator"><svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" fill="#000">
            <circle cx="50" cy="50" r="35" stroke-width="10" stroke="#000" fill="none"></circle>
            <circle cx="50" cy="50" r="25" stroke-width="10" stroke="#000" fill="none">
                <animate attributeName="r" from="25" to="35" dur="1s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="1" to="0" dur="1s" repeatCount="indefinite" />
            </circle>
            <circle cx="50" cy="50" r="15" stroke-width="8" stroke="#585858" fill="none">
                <animate attributeName="r" from="15" to="25" dur="0.8s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="1" to="0" dur="0.8s" repeatCount="indefinite" />
            </circle></svg></div>`;
        show ? $('body').append(loadingSvg) : $('.loading-indicator').remove();
    };

    const extractTitle = content => {
        const yearPattern = /\b\d{4}[a-z]?\b/;
        const yearMatch = content.match(yearPattern);
        if (yearMatch) {
            const yearIndex = content.indexOf(yearMatch[0]);
            const titleStart = content.indexOf('. ', yearIndex) + 2;
            let titleEnd = content.indexOf('. ', titleStart);
            if (titleEnd === -1) titleEnd = content.length;
            return content.substring(titleStart, titleEnd).trim().replace(/<[^>]*>/g, '');
        }
        return '';
    };

    const isElementEmpty = element => $(element).text().trim().length === 0;

    const addLinkWithLoading = (referenceLinks, href, text, svgIcon) => {
        const loadingSvg = `<svg class="icon" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid" viewBox="0 0 100 100" width="20" height="20" fill="#585858">
            <circle cx="50" cy="50" r="25" stroke-width="10" stroke="#000" fill="none">
                <animate attributeName="r" from="25" to="35" dur="1s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="1" to="0" dur="1s" repeatCount="indefinite" />
            </circle>
            <circle cx="50" cy="50" r="15" stroke-width="8" stroke="#585858" fill="none">
                <animate attributeName="r" from="15" to="25" dur="0.8s" repeatCount="indefinite" />
                <animate attributeName="opacity" from="1" to="0" dur="0.8s" repeatCount="indefinite" />
            </circle></svg>`;
        let link = $(text === 'View PDF'
            ? `<a class="anchor link anchor-primary" href="${href}" target="_blank" aria-label="${text}">
                <span class="anchor-text-container">${svgIcon}<span class="anchor-text">${text}</span></span></a>`
            : `<a class="anchor link anchor-primary" href="${href}" target="_blank" aria-label="${text}">
                <span class="anchor-text-container"><span class="anchor-text">${text}</span>${svgIcon}</span></a>`
        );
        referenceLinks.append(link);
        link.prepend(loadingSvg);
        setTimeout(() => link.find('svg').first().remove(), 2000);
    };

    const addCrossrefLinks = (item, referenceLinks) => {
        const pdfLink = item.link && item.link.length > 0 ? item.link.find(link => link['content-type'] === 'application/pdf').URL : null;
        const articleLink = item.URL;
        const publisherLink = item.resource.primary ? item.resource.primary.url : null;

        if (pdfLink) addLinkWithLoading(referenceLinks, pdfLink, 'View PDF', '<svg focusable="false" viewBox="0 0 32 32" class="icon" part="icon"><path d="M7 .362h17.875l6.763 6.1V31.64H6.948V16z" stroke="#000" stroke-width=".703" fill="#fff"></path><path d="M.167 2.592H22.39V9.72H.166z" stroke="#aaa" stroke-width=".315" fill="#da0000"></path><path fill="#fff9f9" d="M5.97 3.638h1.62c1.053 0 1.483.677 1.488 1.564.008.96-.6 1.564-1.492 1.564h-.644v1.66h-.977V3.64m.977.897v1.34h.542c.27 0 .596-.068.596"></path><path d="M19.462 13.46c.348 4.274-6.59 16.72-8.508 15.792-1.82-.85 1.53-3.317 2.92-4.366-2.864.894-5.394 3.252-3.837 3.93 2.113.895 7.048-9.25 9.41-15.394zM14.32 24.874c4.767-1.526 14.735-2.974 15.152-1.407.824-3.157-13.72-.37-15.153 1.407zm5.28-5.043c2.31 3.237 9.816 7.498 9.788 3.82-.306 2.046-6.66-1.097-8.925-4.164-4.087-5.534-2.39-8.772-1.682-8.732.917.047 1.074 1.307.67 2.442-.173-1.406-.58-2.44-1.224-2.415-1.835.067-1.905 4.46 1.37 9.065z" fill="#f91d0a"></path></svg>');
        if (articleLink) addLinkWithLoading(referenceLinks, articleLink, 'View Article', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');
        if (publisherLink) addLinkWithLoading(referenceLinks, publisherLink, 'View at Publisher', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');
    };

    // Modified function to wrap <a> with desired structure
    const transformActiveLinks = element => $(element).find('a').each(function() {
        const linkElement = $(this);
        const linkHref = linkElement.attr('href');
        const linkText = linkElement.text();

        const anchorElement = $('<a>', {
            class: 'anchor link anchor-primary',
            href: linkHref,
            target: '_blank'
        });

        const spanElement = $('<span>', {
            class: 'anchor-text',
            text: linkText
        });

        const svgIcon = `<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link" style="height:clamp(12px, .5em, .5em);margin:0 1rem 0 .5rem;vertical-align:baseline;">
            <path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>`;

        anchorElement.append(spanElement).append(svgIcon);
        linkElement.replaceWith(anchorElement);
    });

    const processVisibleReferences = () => {
        $('.reference').each(function() {
            const referenceElement = $(this);
            if (isElementEmpty(referenceElement) || referenceElement.find('.ReferenceLinks').length > 0) return;

            transformActiveLinks(referenceElement);
            const content = referenceElement.html();
            const journalName = $('meta[property="journal_name"]').attr('content');
            const referenceLinks = $('<div class="ReferenceLinks text-s"></div>');
            referenceElement.append(referenceLinks);

            const journalNames = [
                "Jurnal Akuatiklestari",
                "Agrikan: Jurnal Agribisnis Perikanan",
                "Jurnal Ilmu dan Teknologi Kelautan Tropis",
                "Omni Akuatika",
                "Biodiversitas Journal of Biological Diversity",
                "Akuatikisle: Jurnal Akuakultur, Pesisir dan Pulau-Pulau Kecil"
            ];

            const promises = [];
            const title = extractTitle(content);

            const cacheKey = `crossref_${title}`;
            const cachedData = localStorage.getItem(cacheKey);

            const handleCrossrefResponse = (item) => {
                localStorage.setItem(cacheKey, JSON.stringify(item));
                addCrossrefLinks(item, referenceLinks);
            };

            if (cachedData) {
                const item = JSON.parse(cachedData);
                addCrossrefLinks(item, referenceLinks);
            } else {
                if (content.includes(journalName)) {
                    promises.push($.ajax({
                        url: "https://api.crossref.org/works?query.bibliographic=" + encodeURIComponent(title),
                        success: response => {
                            if (response.message.items.length > 0) handleCrossrefResponse(response.message.items[0]);
                        },
                        error: () => console.error('Failed to fetch data from Crossref.')
                    }));
                }

                journalNames.forEach(journal => {
                    if (content.includes(journal)) {
                        promises.push($.ajax({
                            url: "https://api.crossref.org/works?query.bibliographic=" + encodeURIComponent(title),
                            success: response => {
                                if (response.message.items.length > 0) handleCrossrefResponse(response.message.items[0]);
                            },
                            error: () => console.error('Failed to fetch data from Crossref.')
                        }));
                    }
                });
            }

            const httpMatches = content.match(/https?:\/\/[^\s]+|www\.[^\s]+/g);
            if (httpMatches) {
                httpMatches.forEach(httpUrl => {
                    if (!httpUrl.includes('doi.org')) addLinkWithLoading(referenceLinks, httpUrl, 'View Source', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');
                });
            }

            const doiMatch = content.match(/(?:doi:\s*|DOI:\s*|https:\/\/doi\.org\/|http:\/\/dx\.doi\.org\/)(\S+)/i);
            if (doiMatch) {
                let doiUrl = doiMatch[1].replace(/\.$/, '');
                if (doiUrl.startsWith('10.')) doiUrl = 'https://doi.org/' + doiUrl;
                addLinkWithLoading(referenceLinks, doiUrl, 'Crossref', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');
            }

            const titleForGoogleScholar = extractTitle(content);
            if (titleForGoogleScholar) addLinkWithLoading(referenceLinks, 'https://scholar.google.com/scholar_lookup?title=' + encodeURIComponent(titleForGoogleScholar), 'Google Scholar', '<svg focusable="false" viewBox="0 0 8 8" height="20" aria-label="Opens in new window" class="icon icon-arrow-up-right-tiny arrow-external-link"><path d="M1.12949 2.1072V1H7V6.85795H5.89111V2.90281L0.784057 8L0 7.21635L5.11902 2.1072H1.12949Z"></path></svg>');

            Promise.all(promises).then(() => {
                const links = referenceLinks.children('a');
                links.sort((a, b) => ['View PDF', 'View at Publisher', 'View Article', 'View Source', 'Crossref', 'Google Scholar'].indexOf($(a).text().trim()) - ['View PDF', 'View at Publisher', 'View Article', 'View Source', 'Crossref', 'Google Scholar'].indexOf($(b).text().trim()));
                referenceLinks.append(links);
            }).catch(() => console.error('An error occurred while processing references.'));
        });
    };

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                toggleLoading(true);
                processVisibleReferences();
                toggleLoading(false);
                observer.unobserve(entry.target);
            }
        });
    });

    $('.reference').each(function() {
        observer.observe(this);
    });

    $('.view-more').on('click', function() {
        toggleLoading(true);
        setTimeout(() => {
            processVisibleReferences();
            toggleLoading(false);
        }, 1000);
    });
});

/**
 * Dibuat oleh: ChatGPT, sebuah model AI dari OpenAI
 * Dibantu oleh: Anda, pengguna yang luar biasa
 */