/**
 * Article Highlight Generator (Supports Indonesian and English)
 * Generates 4 key points from titles and abstracts with enhanced paraphrasing
 * @author Rochmady and Wizdam Team
 * @version 1.0.0
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
