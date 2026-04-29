<?php
declare(strict_types=1);

/**
 * @file lib/pkp/pages/help/HelpHandler.inc.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University Library
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HelpHandler
 * @ingroup pages_help
 *
 * @brief [WIZDAM CORE] Handle requests for viewing help pages + Chatbox Logic.
 * Refactored for Wizdam Fork v3.2 Protocol (PHP 8.1+ Strict).
 */

declare(strict_types=1);

// Define Defaults
if (!defined('HELP_DEFAULT_TOPIC')) define('HELP_DEFAULT_TOPIC', 'index/topic/000000');
if (!defined('HELP_DEFAULT_TOC')) define('HELP_DEFAULT_TOC', 'index/toc/000000');

// Imports (Wizdam Core Pathing)
import('lib.pkp.classes.help.HelpToc');
import('lib.pkp.classes.help.HelpTocDAO');
import('lib.pkp.classes.help.HelpTopic');
import('lib.pkp.classes.help.HelpTopicDAO');
import('lib.pkp.classes.help.HelpTopicSection');
import('classes.handler.Handler'); // Akan otomatis mencari Handler terdekat (Core/App)

class HelpHandler extends Handler {
    
    /**
     * Construct
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HelpHandler() {
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    // --- STANDARD HELP METHODS (MODERNIZED) ---

    /**
     * Show the help index page.
     * @param array $args
     * @param PKPRequest|null $request
     * @return void
     */
    public function index($args = [], $request = null) {
        $this->view(['index', 'topic', '000000'], $request);
    }

    /**
     * Show the help table of contents.
     * @param array $args
     * @param PKPRequest|null $request
     * @return void
     */
    public function toc($args, $request) {
        $this->validate();
        $this->setupTemplate();
        $templateMgr = TemplateManager::getManager();
        
        import('classes.help.Help');
        $help = Help::getHelp(); 

        $templateMgr->assign('helpToc', $help->getTableOfContents());
        $templateMgr->display('help/helpToc.tpl');
    }

    /**
     * View a help topic.
     * @param array $args
     * @param PKPRequest|null $request
     * @return void
     */
    public function view($args, $request) {
        $this->validate();
        $this->setupTemplate();
        $request = $request instanceof PKPRequest ? $request : PKPApplication::getRequest();

        $topicId = implode("/", $args ?? []);
        $rawKeyword = (string) $request->getUserVar('keyword');
        $keyword = trim(PKPString::regexp_replace('/[^\w\s\.\-]/', '', strip_tags($rawKeyword)));
        $result = (int) $request->getUserVar('result');

        $topicDao = DAORegistry::getDAO('HelpTopicDAO');
        $topic = $topicDao->getTopic($topicId);

        if ($topic === false) {
            $topicId = HELP_DEFAULT_TOPIC;
            $topic = $topicDao->getTopic($topicId);
        }

        $tocDao = DAORegistry::getDAO('HelpTocDAO');
        $toc = $tocDao->getToc($topic->getTocId());
        if ($toc === false) $toc = $tocDao->getToc(HELP_DEFAULT_TOC);

        $subToc = ($topic->getSubTocId() != null) ? $tocDao->getToc($topic->getSubTocId()) : null;
        $relatedTopics = $topic->getRelatedTopics();
        
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('currentTopicId', $topic->getId());
        $templateMgr->assign('topic', $topic);
        $templateMgr->assign('toc', $toc);
        $templateMgr->assign('subToc', $subToc);
        $templateMgr->assign('relatedTopics', $relatedTopics);
        $templateMgr->assign('locale', AppLocale::getLocale());
        $templateMgr->assign('breadcrumbs', $toc->getBreadcrumbs());
        
        if (!empty($keyword)) $templateMgr->assign('helpSearchKeyword', $keyword);
        if (!empty($result)) $templateMgr->assign('helpSearchResult', $result);
        
        $templateMgr->display('help/view.tpl');
    }

    /**
     * Search help topics.
     * @param array $args
     * @param PKPRequest|null $request
     * @return void
     */
    public function search($args, $request) {
        $this->validate();
        $this->setupTemplate();
        $request = $request instanceof PKPRequest ? $request : PKPApplication::getRequest();
        
        $searchResults = [];
        $rawKeyword = (string) $request->getUserVar('keyword');
        $keyword = trim(PKPString::regexp_replace('/[^\w\s\.\-]/', '', strip_tags($rawKeyword)));

        if (!empty($keyword)) {
            $topicDao = DAORegistry::getDAO('HelpTopicDAO');
            $topics = $topicDao->getTopicsByKeyword($keyword);
            $tocDao = DAORegistry::getDAO('HelpTocDAO');
            foreach ($topics as $topic) {
                $searchResults[] = ['topic' => $topic, 'toc' => $tocDao->getToc($topic->getTocId())];
            }
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('showSearch', true);
        $templateMgr->assign('pageTitle', __('help.searchResults'));
        $templateMgr->assign('helpSearchKeyword', $keyword);
        $templateMgr->assign('searchResults', $searchResults);
        $templateMgr->display('help/searchResults.tpl');
    }

    /**
     * Setup the template.
     * @return void
     */
    public function setupTemplate($request = NULL) {
        parent::setupTemplate();
        $templateMgr = TemplateManager::getManager();
        $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
    }

    // --- WIZDAM CHATBOX MODULE (INJECTED DIRECTLY INTO CORE) ---

    /**
     * Handle Chatbox AJAX Request
     * @param array $args
     * @return void
     */
    public function chat($args = []): void {
        $request = PKPApplication::getRequest();
        if (!$request->isPost()) {
            http_response_code(403);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $query = trim((string) $request->getUserVar('q'));
        $context = trim((string) $request->getUserVar('context'));
        $currentLocale = AppLocale::getLocale();

        $reply = $this->_getBotResponse($query, $context, $currentLocale);

        header('Content-Type: application/json');
        echo json_encode(['reply' => $reply]);
        exit;
    }

    /**
     * Generate Bot Response
     * @param string $query
     * @param string $contextUrl
     * @param string $locale
     * @return string
     */
    private function _getBotResponse(string $query, string $contextUrl, string $locale): string {
        $topicDao = DAORegistry::getDAO('HelpTopicDAO');

        // 1. User Search
        if (!empty($query)) {
            $keyword = trim(PKPString::regexp_replace('/[^\w\s\.\-]/', '', strip_tags($query)));
            $topics = $topicDao->getTopicsByKeyword($keyword);
            
            if (empty($topics)) return $this->_getLocalizedMsg('search_fail', $locale, $query);
            return $this->_formatHelpOutput($topics[0], $this->_getLocalizedMsg('search_success', $locale), $locale);
        }

        // 2. Context Aware
        $topicId = $this->_mapUrlToTopic($contextUrl);
        if ($topicId) {
            $topic = $topicDao->getTopic($topicId);
            if ($topic) return $this->_formatHelpOutput($topic, $this->_getLocalizedMsg('context_found', $locale), $locale);
        }

        return $this->_getLocalizedMsg('greeting', $locale);
    }

    /**
     * Format Help Output for Chatbox
     * @param HelpTopic $topic
     * @param string $introText
     * @param string $locale
     * @return string
     */
    private function _formatHelpOutput($topic, string $introText, string $locale): string {
        $title = $topic->getTitle();
        $content = strip_tags($topic->getContents(), '<p><br><b><i><ul><li>');
        if (strlen($content) > 300) {
            $cutoff = strpos($content, ' ', 300);
            $content = ($cutoff !== false ? substr($content, 0, $cutoff) : substr($content, 0, 300)) . "...";
        }
        $link = Request::url(null, 'help', 'view', explode('/', $topic->getId()));
        $readMoreText = $this->_getLocalizedMsg('read_more', $locale);

        return "<div style='margin-bottom:5px;'><i>{$introText}</i></div>
                <h4 style='margin:0 0 5px 0; color:#004e82;'>{$title}</h4>
                <div style='font-size:12px; line-height:1.4;'>{$content}</div>
                <div style='margin-top:8px;'><a href='{$link}' target='_blank' style='color:#004e82; font-weight:bold;'>📖 {$readMoreText}</a></div>";
    }

    /**
     * Get Localized Message
     * @param string $key
     * @param string $locale
     * @param string $extraArg
     * @return string
     */
    private function _getLocalizedMsg(string $key, string $locale, string $extraArg = ''): string {
        $isIndo = ($locale === 'id_ID');
        switch ($key) {
            case 'search_fail': return $isIndo ? "Maaf, tidak ada panduan ditemukan untuk '<b>" . htmlspecialchars($extraArg) . "</b>'." : "Sorry, no help topics found for '<b>" . htmlspecialchars($extraArg) . "</b>'.";
            case 'search_success': return $isIndo ? "Hasil pencarian teratas:" : "Top search result:";
            case 'context_found': return $isIndo ? "Panduan halaman ini:" : "Guide for this page:";
            case 'greeting': return $isIndo ? "Halo! Saya Asisten Wizdam. Ketik sesuatu untuk mencari panduan." : "Hello! I am Wizdam Assistant. Ask me anything about the system.";
            case 'read_more': return $isIndo ? "Baca Selengkapnya" : "Read Full Article";
            default: return "...";
        }
    }

    /**
     * Map URL to Help Topic ID
     * @param string $url
     * @return string|null
     */
    private function _mapUrlToTopic(string $url): ?string {
        if (strpos($url, '/manager') !== false) return 'journal/topic/000003';
        if (strpos($url, '/editor') !== false) return 'journal/topic/000002';
        if (strpos($url, '/author') !== false) return 'journal/topic/000001';
        if (strpos($url, '/reviewer') !== false) return 'journal/topic/000004';
        if (strpos($url, '/register') !== false) return 'site/topic/000002';
        return null;
    }
}

?>