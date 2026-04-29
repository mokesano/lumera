<?php
declare(strict_types=1);

/**
 * @file pages/policies/PoliciesHandler.inc.php
 *
 * Copyright (c) 2025 Wizdam Team
 * Copyright (c) 2025 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PoliciesHandler
 * @ingroup pages_policies
 *
 * @brief Handle requests for policy pages.
 * @version Wizdam-Fixed-Compatibility & Optimized
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance, 
 * DRY Principle, and N+1 Query Optimization.
 */

import('classes.handler.Handler');
import('lib.pkp.classes.core.PKPString');

class PoliciesHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PoliciesHandler($request = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    // --------------------------------------------------------------
    // 0. CORE HELPERS (OPTIMASI DRY)
    // --------------------------------------------------------------

    /**
     * Helper: Mendapatkan object request yang valid (Mencegah repeat kode).
     * @param object|null $request PKPRequest
     * @return object
     */
    private function _getRequest($request = null) {
        return $request instanceof PKPRequest ? $request : Application::get()->getRequest();
    }

    // --------------------------------------------------------------
    // 1. FUNGSI INDEX (FIXED COMPATIBILITY)
    // --------------------------------------------------------------

    /**
     * Halaman Utama Kebijakan (Index).
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function index($args = [], $request = null) {
        $request = $this->_getRequest($request);

        $this->validate();
        $this->setupTemplate();
        $this->_showPolicyIndex($request);
    }

    // --------------------------------------------------------------
    // 2. FUNGSI VIEW (PENANGAN SLUG DINAMIS)
    // --------------------------------------------------------------

    /**
     * Penangan Kebijakan Dinamis (View).
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function view($args = [], $request = null) {
        $request = $this->_getRequest($request);

        $slug = isset($args[0]) ? $args[0] : null;
        
        if (!$slug) {
            $request->redirect(null, null, 'index'); 
            return;
        }

        $this->validate();
        $this->setupTemplate();
        
        // Coba cari slug di custom items
        if (!$this->_showCustomPolicy($request, $slug)) {
            // Jika tidak ketemu, lempar ke Index
            $request->redirect(null, null, 'index'); 
        }
    }

    // --------------------------------------------------------------
    // 3. FUNGSI HANDLER SPESIFIK
    // --------------------------------------------------------------

    /**
     * Menampilkan Privacy Statement.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function privacyStatement($args = [], $request = null) {
        $this->_commonPolicyHandler('privacyStatement', 'about.privacyStatement', $this->_getRequest($request));
    }

    /**
     * Menampilkan Peer Review Process.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function peerReview($args = [], $request = null) {
        $this->_commonPolicyHandler('reviewPolicy', 'about.peerReviewProcess', $this->_getRequest($request));
    }

    /**
     * Menampilkan Publication Ethics.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function ethics($args = [], $request = null) {
        $this->_commonPolicyHandler('publicationEthics', 'about.publicationEthics', $this->_getRequest($request));
    }

    // -----------------------------------------------------------------
    // OPEN ACCESS vs DELAYED OA (Strict Logic)
    // -----------------------------------------------------------------

    /**
     * Menampilkan Open Access Policy dengan Logika Cerdas.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function openAccess($args = [], $request = null) {
        $request = $this->_getRequest($request);

        $this->validate();
        $this->setupTemplate();
        
        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager();
        
        $publishingMode = $journal->getSetting('publishingMode');
        $isSubscription = ($publishingMode == PUBLISHING_MODE_SUBSCRIPTION);
        
        $content = '';
        $pageTitleKey = 'about.openAccessPolicy';

        if ($isSubscription && $journal->getSetting('enableDelayedOpenAccess')) {
            $pageTitleKey = 'about.delayedOpenAccess';
            $rawContent = $journal->getLocalizedSetting('delayedOpenAccessPolicy');
            $duration = $journal->getSetting('delayedOpenAccessDuration');
            $embargoText = AppLocale::Translate('about.delayedOpenAccessDescription1') . ' ' . $duration . ' ' . AppLocale::Translate('about.delayedOpenAccessDescription2');
            
            $content = !empty($rawContent) ? "<p><em>" . $embargoText . "</em></p>" . $rawContent : $embargoText;
        } else {
            $content = $journal->getLocalizedSetting('openAccessPolicy');
            if (empty($content)) {
                $siteDao = DAORegistry::getDAO('SiteDAO');
                $site = $siteDao->getSite();
                $content = $site->getSetting('publisherOpenAccess');
            }
        }

        if (!empty($content)) $content = PKPString::stripUnsafeHtml($content);

        $translatedTitle = AppLocale::Translate($pageTitleKey);
        $templateMgr->assign('pageTitle', $translatedTitle);
        $templateMgr->assign('pageTitleTranslated', $translatedTitle);
        $templateMgr->assign('content', $content);
        
        $templateMgr->display('policies/generic.tpl');
    }

    // -----------------------------------------------------------------
    // ARCHIVING (LOCKSS + Self Archiving Contextual)
    // -----------------------------------------------------------------

    /**
     * Menampilkan Kebijakan Pengarsipan (Archiving).
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function archiving($args = [], $request = null) {
        $request = $this->_getRequest($request);

        $this->validate();
        $this->setupTemplate();
        
        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager();
        
        $publishingMode = $journal->getSetting('publishingMode');
        $isSubscription = ($publishingMode == PUBLISHING_MODE_SUBSCRIPTION);

        $content = '';
        if ($journal->getSetting('enableLockss')) {
            $content = $journal->getLocalizedSetting('lockssLicense');
        }
        if (empty($content)) $content = $journal->getLocalizedSetting('archivingInfo');
        if (empty($content)) $content = $journal->getLocalizedSetting('archivingPolicy');

        if ($isSubscription && $journal->getSetting('enableAuthorSelfArchive')) {
            $selfArchiving = $journal->getLocalizedSetting('authorSelfArchivePolicy');
            if (!empty($selfArchiving)) {
                if (!empty($content)) $content .= "<hr class='policy-separator' />";
                $subTitle = AppLocale::Translate('about.authorSelfArchive');
                $content .= "<h3>" . $subTitle . "</h3>" . $selfArchiving;
            }
        }

        if (!empty($content)) $content = PKPString::stripUnsafeHtml($content);

        $translatedTitle = AppLocale::Translate('about.archiving');
        $templateMgr->assign('pageTitle', $translatedTitle);
        $templateMgr->assign('pageTitleTranslated', $translatedTitle);
        $templateMgr->assign('content', $content);
        
        $templateMgr->display('policies/generic.tpl');
    }

    /**
     * Menampilkan Copyright Notice.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function copyright($args = [], $request = null) {
        $this->_commonPolicyHandler('copyrightNotice', 'about.copyrightNotice', $this->_getRequest($request));
    }

    /**
     * Menampilkan Publication Frequency.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function publicationFrequency($args = [], $request = null) {
        $this->_commonPolicyHandler('pubFreqPolicy', 'about.publicationFrequency', $this->_getRequest($request));
    }

    /**
     * Menampilkan Kebijakan Bagian (Section Policies) dengan Optimasi Performa
     * dan Penarikan Data (Afiliasi/Interests) yang Akurat.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function sectionPolicies($args = [], $request = null) {
        $request = $this->_getRequest($request);
        
        $this->validate();
        $this->setupTemplate();

        $journal = $request->getJournal();
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO');
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $userDao = DAORegistry::getDAO('UserDAO');
        
        $templateMgr = TemplateManager::getManager();

        $primaryLocale = $journal->getPrimaryLocale();
        if (empty($primaryLocale)) {
            $primaryLocale = AppLocale::getLocale();
        }

        $sectionsIterator = $sectionDao->getJournalSections($journal->getId());
        $sections = $sectionsIterator->toArray();
        
        $templateMgr->assign('sections', $sections);

        $sectionEditorEntriesBySection = [];
        
        // [OPTIMASI N+1] Array untuk menyimpan cache user yang sudah di-query
        $userCache = []; 

        foreach ($sections as $section) {
            $sectionEditorEntriesArray = $sectionEditorsDao->getEditorsBySectionId($journal->getId(), $section->getId());
            $richEditorData = []; 

            foreach ($sectionEditorEntriesArray as $entryArray) {
                if (!isset($entryArray['user']) || !is_object($entryArray['user'])) continue;
                
                $sectionEditorObject = $entryArray['user'];
                $userId = $sectionEditorObject->getId();
                
                // Panggil dari cache jika ada, jika tidak, baru query DB
                if (!isset($userCache[$userId])) {
                    $userCache[$userId] = $userDao->getById($userId);
                }
                $user = $userCache[$userId];
                
                if (!$user) continue; 

                // 1. Tarik & Pecah Afiliasi
                $rawAffiliation = (string) $user->getAffiliation($primaryLocale);
                if (empty($rawAffiliation)) {
                    $rawAffiliation = (string) $user->getLocalizedAffiliation();
                }
                $affiliationsArray = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $rawAffiliation)));

                // 2. Tarik Data Interests dengan aman
                $interests = '';
                if (method_exists($user, 'getInterestString')) {
                    $interests = $user->getInterestString();
                } elseif (method_exists($user, 'getInterestsString')) {
                    $interests = $user->getInterestsString();
                } else {
                    $interests = (string) $user->getData('interests');
                }
                
                if (empty(trim($interests))) {
                    $interestDao = DAORegistry::getDAO('InterestDAO');
                    if ($interestDao && method_exists($interestDao, 'getInterestsString')) {
                        $interests = $interestDao->getInterestsString($userId);
                    }
                }

                // 3. Tarik Negara
                $countryCode = $user->getCountry();
                $countryName = '';
                if (!empty($countryCode)) {
                    $countryName = $countryDao->getCountry($countryCode, $primaryLocale);
                    if (empty($countryName)) {
                        $countryName = $countryDao->getCountry($countryCode, 'en_US');
                    }
                }

                // 4. Masukkan ke Array
                $richEditorData[] = [
                    'user'          => $sectionEditorObject,
                    'affiliations'  => $affiliationsArray,
                    'interests'     => $interests,
                    'countryString' => $countryName
                ];
            }
            $sectionEditorEntriesBySection[$section->getId()] = $richEditorData;
        }

        $templateMgr->assign('sectionEditorEntriesBySection', $sectionEditorEntriesBySection);
        $templateMgr->assign('pageTitle', 'about.sectionPolicies');

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $templateMgr->assign('paymentConfigured', $paymentManager->isConfigured());

        $templateMgr->display('policies/sectionPolicies.tpl');
    }

    // --------------------------------------------------------------
    // 4. INTERNAL HELPERS
    // --------------------------------------------------------------

    /**
     * Helper Umum untuk Menampilkan Halaman Kebijakan Standar.
     * @param string $settingName
     * @param string $titleKey
     * @param object $request PKPRequest
     */
    public function _commonPolicyHandler($settingName, $titleKey, $request) {
        $this->validate();
        $this->setupTemplate();
        
        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager();
        
        $content = $journal->getLocalizedSetting($settingName);
        
        if (empty($content)) {
            $siteDao = DAORegistry::getDAO('SiteDAO');
            $site = $siteDao->getSite();
            
            $globalMap = [
                'privacyStatement' => 'publisherPrivacy',
                'openAccessPolicy' => 'publisherOpenAccess'
            ];
            
            if (isset($globalMap[$settingName])) {
                $content = $site->getSetting($globalMap[$settingName]);
            }
        }
        
        $translatedTitle = AppLocale::Translate($titleKey);
        
        $templateMgr->assign('pageTitle', $translatedTitle);
        $templateMgr->assign('content', $content);
        $templateMgr->display('policies/generic.tpl');
    }

    /**
     * Helper untuk Menampilkan Kebijakan Kustom (Custom Items).
     * @param object $request PKPRequest
     * @param string $slug
     * @return boolean
     */
    public function _showCustomPolicy($request, $slug) {
        $journal = $request->getJournal();
        $customItems = $journal->getLocalizedSetting('customAboutItems');
        
        if (is_array($customItems)) {
            foreach ($customItems as $item) {
                $generatedSlug = $this->_slugify($item['title']);
                if ($generatedSlug == $slug) {
                    $templateMgr = TemplateManager::getManager();
                    $templateMgr->assign('pageTitle', $item['title']); 
                    $templateMgr->assign('content', $item['content']);
                    $templateMgr->display('policies/generic.tpl');
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Helper untuk Menampilkan Daftar Index Kebijakan.
     * @param object $request PKPRequest
     */
    public function _showPolicyIndex($request) {
        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager();
          
        $customItems = $journal->getLocalizedSetting('customAboutItems');
        $processedCustomItems = [];
        if (is_array($customItems)) {
            foreach ($customItems as $item) {
                $item['slug'] = $this->_slugify($item['title']);
                $processedCustomItems[] = $item;
            }
        }
          
        $templateMgr->assign('customPolicies', $processedCustomItems);
        $templateMgr->assign('pageTitle', 'manager.setup.policies'); 
        $templateMgr->display('policies/index.tpl');
    }
    
    /**
     * [WIZDAM MAGIC] Magic Method: __call
     * Menangkap request URL yang tidak cocok dengan method eksplisit di atas.
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args) {
        $request = Application::get()->getRequest();
        
        $this->validate();
        $this->setupTemplate();
        
        // 1. Ubah CamelCase kembali ke Slug
        $requestSlug = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $method));

        // 2. Cari di Custom Items berdasarkan slug
        if ($this->_showCustomPolicy($request, $requestSlug)) {
            return; 
        }

        // 3. Fallback metode asli
        if ($this->_showCustomPolicy($request, $method)) {
            return;
        }

        // 4. Jika tetap tidak ada, arahkan kembali ke index
        $request->redirect(null, null, 'index');
    }

    /**
     * Helper: Mengubah camelCase menjadi kebab-case
     * @param string $input
     * @return string
     */
    public function _camelToKebab($input) {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $input));
    }

    /**
     * Utilitas: Pembuat Slug (Slugify).
     * @param string $text
     * @return string
     */
    public function _slugify($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        if (function_exists('iconv')) $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        return empty($text) ? 'n-a' : $text;
    }

    /**
     * Setup Common Template Data.
     * @param object $request PKPRequest
     */
    public function setupTemplate($request = NULL) {
        parent::setupTemplate();
        
        $request = Application::get()->getRequest();
        $journal = $request->getJournal(); 
        $templateMgr = TemplateManager::getManager();
        
        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_MANAGER, 
            LOCALE_COMPONENT_APP_MANAGER
        );

        $customItems = $journal->getLocalizedSetting('customAboutItems');
        $processedCustomItems = [];
        if (is_array($customItems)) {
            foreach ($customItems as $item) {
                $item['slug'] = $this->_slugify($item['title']);
                $processedCustomItems[] = $item;
            }
        }
        $templateMgr->assign('customPolicies', $processedCustomItems);

        $router = $request->getRouter();
        if ($router instanceof PKPPageRouter) {
            $requestedOp = $router->getRequestedOp($request);

            if ($requestedOp !== 'index') {
                $policiesUrl = Request::url(null, 'policies');
                $policiesLabel = AppLocale::Translate('manager.setup.policies');
                if (empty($policiesLabel)) $policiesLabel = "Policies";

                $hierarchyArray = [
                    'url'  => $policiesUrl,
                    'name' => $policiesLabel
                ];

                $templateMgr->assign('pageHierarchy', [$hierarchyArray]);
            }
        }

        $templateMgr->assign('helpTopicId', 'pages.policies');
    }
}
?>