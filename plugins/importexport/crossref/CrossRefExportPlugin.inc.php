<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/crossref/CrossRefExportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CrossRefExportPlugin
 * @ingroup plugins_importexport_crossref
 *
 * @brief CrossRef export/registration plugin.
 */

if (!class_exists('CrossrefDoiExportPlugin')) {
    import('plugins.importexport.crossref.classes.CrossrefDoiExportPlugin');
}

define('CROSSREF_STATUS_SUBMITTED', 'submitted');
define('CROSSREF_STATUS_COMPLETED', 'completed');
define('CROSSREF_STATUS_FAILED', 'failed');
define('CROSSREF_STATUS_REGISTERED', 'found');
define('CROSSREF_STATUS_MARKEDREGISTERED', 'markedRegistered');
define('CROSSREF_STATUS_NOT_DEPOSITED', 'notDeposited');

// FIX: status antara yang sebelumnya tidak pernah direpresentasikan di DB/UI.
// Crossref memproses deposit secara ASINKRON: queued -> in_process -> completed
// (atau failed). Sebelumnya kode ini hanya mengenal biner "completed" vs "belum",
// sehingga status riil (masih di antrean/sedang diproses) tidak pernah terlihat
// di aplikasi, dan artikel yang sebenarnya SUDAH disubmit tapi belum completed
// terus dianggap "belum diregister" -> berisiko disubmit ulang (double deposit).
define('CROSSREF_STATUS_QUEUED', 'queued');
define('CROSSREF_STATUS_IN_PROCESS', 'in_process');
// Status mentah dari Crossref yang belum dikenali/dipetakan oleh aplikasi ini.
// Tetap disimpan APA ADANYA (bukan divonis completed/failed) supaya tidak ada
// informasi yang hilang/salah tafsir saat Crossref menambah status baru di masa depan.
define('CROSSREF_STATUS_UNKNOWN_REMOTE', 'unknownRemoteStatus');

// FIX: deposit MANUAL — admin men-download XML (tombol "Export") lalu meng-
// upload sendiri ke portal Crossref di luar aplikasi ini. Sebelumnya alur ini
// TIDAK PERNAH tercatat di DB sama sekali. Status ini ditulis SEGERA setelah
// file XML berhasil digenerate (lihat exportObjects()), sebagai penanda "sudah
// diekspor, menunggu konfirmasi apakah benar-benar diupload ke Crossref".
// Statusnya SENGAJA dipisah dari CROSSREF_STATUS_SUBMITTED (yang berarti
// APLIKASI INI sendiri yang mem-POST ke Crossref) karena beda tingkat
// kepastian: "submitted" = kita tahu pasti terkirim; "exported" = kita cuma
// tahu filenya dibuat, belum tentu benar-benar diupload oleh admin.
define('CROSSREF_STATUS_EXPORTED', 'exported');

// Status lokal yang membuat objek DIKECUALIKAN dari daftar "unregistered" /
// SEMENTARA tidak ditawarkan lagi untuk export/register ulang selama masih
// dalam masa tunggu (cooldown) -- mencakup deposit OTOMATIS maupun MANUAL,
// supaya keduanya sama-sama tidak "hilang" dari radar sebelum status finalnya
// jelas.
if (!defined('CROSSREF_IN_FLIGHT_STATUSES')) {
    define('CROSSREF_IN_FLIGHT_STATUSES', [
        CROSSREF_STATUS_EXPORTED,
        CROSSREF_STATUS_SUBMITTED,
        CROSSREF_STATUS_QUEUED,
        CROSSREF_STATUS_IN_PROCESS,
    ]);
}

// FIX: subset dari status di atas yang boleh memicu PENGIRIMAN ULANG OTOMATIS
// (registerDoi()) ke Crossref setelah cooldown kedaluwarsa. SENGAJA TIDAK
// menyertakan CROSSREF_STATUS_EXPORTED -- deposit manual adalah keputusan
// admin sepenuhnya; aplikasi ini TIDAK BOLEH mem-POST ulang secara otomatis
// atas nama objek yang admin pilih untuk deposit manual. Untuk status EXPORTED
// yang basi (cooldown lewat, tidak ada histori di Crossref), objek hanya akan
// muncul lagi di daftar "belum diregister" untuk PERHATIAN admin -- tidak ada
// aksi otomatis yang dipicu.
if (!defined('CROSSREF_AUTO_RESUBMIT_STATUSES')) {
    define('CROSSREF_AUTO_RESUBMIT_STATUSES', [
        CROSSREF_STATUS_SUBMITTED,
        CROSSREF_STATUS_QUEUED,
        CROSSREF_STATUS_IN_PROCESS,
    ]);
}

// FIX: berapa lama (detik) artikel yang sudah disubmit tapi belum completed/failed
// akan DIKECUALIKAN dari seleksi ulang oleh _getUnregisteredArticles(). Bisa
// dioverride lewat config.inc.php: [crossref] resubmit_cooldown_hours = 6
if (!defined('CROSSREF_RESUBMIT_COOLDOWN_SECONDS')) {
    define('CROSSREF_RESUBMIT_COOLDOWN_SECONDS', ((int) (Config::getVar('crossref', 'resubmit_cooldown_hours') ?: 6)) * 3600);
}

// DataCite API
define('CROSSREF_API_DEPOSIT_OK', 303);
define('CROSSREF_API_RESPONSE_OK', 200);
define('CROSSREF_API_URL', 'https://api.crossref.org/deposits');

// TESTING
// define('CROSSREF_API_URL', 'https://api.crossref.org/deposits?test=true');

define('CROSSREF_SEARCH_API', 'http://search.crossref.org/dois');
define('CROSSREF_WORKS_API', 'http://api.crossref.org/works/');

// The name of the settings used to save the registered DOI and the URL with the deposit status.
define('CROSSREF_DEPOSIT_STATUS', 'depositStatus');
// FIX: nama setting baru untuk mencatat KAPAN submission terakhir dilakukan
// secara lokal — dipakai untuk cooldown anti-double-deposit, independen dari
// apakah Crossref API sudah sempat mengonfirmasi status terbarunya atau belum.
define('CROSSREF_DEPOSIT_SUBMITTED_AT', 'depositSubmittedAt');

class CrossRefExportPlugin extends CrossrefDoiExportPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CrossRefExportPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Implement template methods from ImportExportPlugin
    //
    /**
     * Get the name of this plugin.
     * @see ImportExportPlugin::getName()
     * @return string
     */
    public function getName(): string {
        return 'CrossRefExportPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @see ImportExportPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.importexport.crossref.displayName');
    }

    /**
     * Get the description of this plugin.
     * @see ImportExportPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.importexport.crossref.description');
    }

    /**
     * Register the plugin.
     * @see LazyLoadPlugin::register()
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path);
        if (!Config::getVar('general', 'installed')) {
            return false;
        }

        if ($success) {
            HookRegistry::register('AcronPlugin::parseCronTab', [$this, 'callbackParseCronTab']);
        }
        return $success;
    }

    //
    // Implement template methods from DOIExportPlugin
    //
    /**
     * Get the plugin ID.
     * @see DOIExportPlugin::getPluginId()
     * @return string
     */
    public function getPluginId(): string {
        return 'crossref';
    }

    /**
     * Get the class name of the settings form.
     * @see DOIExportPlugin::getSettingsFormClassName()
     * @return string
     */
    public function getSettingsFormClassName(): string {
        return 'CrossRefSettingsForm';
    }

    /**
     * Get all object types that can be exported/registered via this plugin.
     * @see DOIExportPlugin::getAllObjectTypes()
     * @return array
     */
    public function getAllObjectTypes(): array {
        return [
            'issue'   => DOI_EXPORT_ISSUES,
            'article' => DOI_EXPORT_ARTICLES
        ];
    }

    /**
     * Process a DOI activity request.
     * @see DOIExportPlugin::process()
     * @param Request $request
     * @param Journal $journal
     */
    public function process($request, $journal): void {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        if ($request->getUserVar('checkStatus')) {
            $articleIds = (array) $request->getUserVar('articleId');
            $errors = [];
            $articles = $this->_getObjectsFromIds(DOI_EXPORT_ARTICLES, $articleIds, (int) $journal->getId(), $errors); // Undefined method '_getObjectsFromIds'.
            
            foreach ($articles as $article) {
                $this->updateDepositStatus($request, $journal, $article);
            }
            $request->redirect(
                null, null, null,
                ['plugin', $this->getName(), 'articles'],
                null
            );
        } else {
            parent::process($request, $journal);
        }
    }

    /**
     * Display a list of issues for export.
     * @see DOIExportPlugin::displayIssueList()
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     */
    public function displayIssueList($templateMgr, $journal): void {
        $this->setBreadcrumbs([], true);

        AppLocale::requireComponents([LOCALE_COMPONENT_APP_EDITOR]);
        
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $this->registerDaoHook('IssueDAO');
        $issueIterator = $issueDao->getPublishedIssues((int) $journal->getId(), Handler::getRangeInfo('issues'));

        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

        $excludes = [];
        $allExcluded = true;
        $numArticles = [];
        $allArticlesRegistered = [];

        while ($issue = $issueIterator->next()) {
            $issueId = (int) $issue->getId();
            $excludes[$issueId] = true;
            $issueArticles = $publishedArticleDao->getPublishedArticles($issueId);
            $issueArticlesNo = 0;
            $allArticlesRegistered[$issueId] = true;
            
            foreach ($issueArticles as $issueArticle) {
                $articleRegistered = $issueArticle->getData($this->getPluginId() . '::registeredDoi');
                $errors = [];
                
                if ($this->canBeExported($issueArticle, $errors)) {
                    $excludes[$issueId] = false;
                    $allExcluded = false;
                    $issueArticlesNo++;
                }
                if ($allArticlesRegistered[$issueId] && !isset($articleRegistered)) {
                    $allArticlesRegistered[$issueId] = false;
                }
            }
            $numArticles[$issueId] = $issueArticlesNo;
        }

        $issueIterator = $issueDao->getPublishedIssues((int) $journal->getId(), Handler::getRangeInfo('issues'));

        // FIX (pemantauan deposit manual + otomatis di panel, issue-level):
        // sebelumnya variabel-variabel ini TIDAK PERNAH di-assign ke issues.tpl
        // sama sekali -- template tidak punya cara menampilkan status deposit
        // issue apapun, meskipun datanya sekarang sudah tercatat (lihat
        // registerDoi()/exportObjects()/_markObjectsAsExported()). Sekarang
        // disamakan dengan yang sudah tersedia untuk articles.tpl.
        $templateMgr->assign([
            'issues'                       => $issueIterator,
            'allExcluded'                  => $allExcluded,
            'excludes'                     => $excludes,
            'numArticles'                  => $numArticles,
            'allArticlesRegistered'        => $allArticlesRegistered,
            'depositStatusSettingName'     => $this->getDepositStatusSettingName(),
            'depositStatusUrlSettingName'  => $this->getDepositStatusUrlSettingName(),
            'statusMapping'                => $this->getStatusMapping(),
        ]);

        $templateMgr->display($this->getTemplatePath() . 'issues.tpl');
    }

    /**
     * Display a list of articles for export.
     * @see DOIExportPlugin::displayArticleList
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     */
    public function displayArticleList($templateMgr, $journal): void {
        $this->setBreadcrumbs([], true);

        $filter = $templateMgr->get_template_vars('filter');
        
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articles = [];
        
        if ($filter) {
            if ($filter === CROSSREF_STATUS_NOT_DEPOSITED) {
                $allArticles = $publishedArticleDao->getBySetting($this->getDepositStatusSettingName(), null, (int) $journal->getId());
            } else {
                $allArticles = $publishedArticleDao->getBySetting($this->getDepositStatusSettingName(), $filter, (int) $journal->getId());
            }
        } else {
            $allArticles = $this->getAllPublishedArticles($journal);
        }

        $articleData = [];
        $errors = [];
        foreach ($allArticles as $article) {
            if ($this->canBeExported($article, $errors)) {
                $preparedArticle = $this->_prepareArticleData($article, $journal); // Undefined method '_prepareArticleData'.
                if (is_array($preparedArticle)) {
                    $articleData[] = $preparedArticle;
                    $articles[] = $article;
                }
            }
        }

        $totalArticles = count($articleData);
        $rangeInfo = Handler::getRangeInfo('articles');
        if ($rangeInfo && $rangeInfo->isValid()) {
            $articleData = array_slice($articleData, $rangeInfo->getCount() * ($rangeInfo->getPage() - 1), $rangeInfo->getCount());
        }
        
        import('lib.pkp.classes.core.VirtualArrayIterator');
        $iterator = new VirtualArrayIterator($articleData, $totalArticles, $rangeInfo->getPage(), $rangeInfo->getCount());

        $templateMgr->assign([
            'articles'                  => $iterator,
            'depositStatusSettingName'  => $this->getDepositStatusSettingName(),
            'depositStatusUrlSettingName'=> $this->getDepositStatusUrlSettingName(),
            'statusMapping'             => $this->getStatusMapping(),
            'isEditor'                  => Validation::isEditor((int) $journal->getId())
        ]);

        $templateMgr->display($this->getTemplatePath() . 'articles.tpl');
    }

    /**
     * The selected issue can be exported if it contains an article that has a DOI.
     * The selected article can be exported if it has a DOI.
     * @see DOIExportPlugin::displayIssueList() 
     * @see DOIExportPlugin::displayArticleList()
     * @param Issue|PublishedArticle $foundObject
     * @param array $errors
     * @return bool
     */
    public function canBeExported($foundObject, &$errors): bool {
        if ($foundObject instanceof Issue) {
            /** @var PublishedArticleDAO $publishedArticleDao */
            $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $issueArticles = $publishedArticleDao->getPublishedArticles((int) $foundObject->getId());
            foreach ($issueArticles as $issueArticle) {
                if (parent::canBeExported($issueArticle, $errors)) {
                    return true;
                }
            }
        }
        if ($foundObject instanceof PublishedArticle) {
            return parent::canBeExported($foundObject, $errors);
        }
        return false;
    }

    /**
     * Prepare article data for display in the article list template.
     * @see DOIExportPlugin::generateExportFiles()
     * @param Request $request
     * @param int $exportType
     * @param array $objects
     * @param string $targetPath
     * @param Journal $journal
     * @param array $errors
     * @return array|bool
     */
    public function generateExportFiles($request, $exportType, $objects, $targetPath, $journal, &$errors) {
        AppLocale::requireComponents([LOCALE_COMPONENT_APP_EDITOR]);

        $this->import('classes.CrossRefExportDom');
        $dom = new CrossRefExportDom($request, $this, $journal, $this->getCache());
        $doc = $dom->generate($objects);
        
        if ($doc === false) {
            $errors = $dom->getErrors();
            return false;
        }

        $exportFileName = $this->getTargetFileName($targetPath, $exportType);
        file_put_contents($exportFileName, XMLCustomWriter::getXML($doc));

        return [$exportFileName => $objects];
    }

    /**
     * Mark the DOI as registered in the system, so that it is not exported/registered again and update the status of the deposit.
     * @see DOIExportPlugin::processMarkRegistered()
     * @param Request $request
     * @param int $exportType
     * @param array $objects
     * @param Journal $journal
     */
    public function processMarkRegistered($request, $exportType, $objects, $journal): void {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $this->import('classes.CrossRefExportDom');
        $dom = new CrossRefExportDom($request, $this, $journal, $this->getCache());
        
        // NOTE: $statusUpdatePossible declare but not use
        // $statusUpdatePossible = $this->getSetting($journal->getId(), 'username') && $this->getSetting($journal->getId(), 'password');

        foreach ($objects as $object) {
            if ($object instanceof Issue) {
                $articlesByIssue = $dom->retrieveArticlesByIssue($object);
                foreach ($articlesByIssue as $article) {
                    if ($article->getPubId('doi')) {
                        $articleDao->updateSetting((int) $article->getId(), $this->getDepositStatusSettingName(), CROSSREF_STATUS_MARKEDREGISTERED, 'string');
                        $this->markRegistered($request, $article);
                    }
                }
            } else {
                if ($object->getPubId('doi')) {
                    $articleDao->updateSetting((int) $object->getId(), $this->getDepositStatusSettingName(), CROSSREF_STATUS_MARKEDREGISTERED, 'string');
                    $this->markRegistered($request, $object);
                }
            }
        }
    }

    /**
     * Register DOIs with the DOI registration agency.
     * @param mixed $request
     * @param Journal $journal
     * @param array $objects
     * @param string $filename
     * @return bool|array
     */
    public function registerDoi($request, $journal, $objects, $filename) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $curlCh = curl_init();
        $proxyHost = Config::getVar('proxy', 'http_host');
        if ($proxyHost) {
            curl_setopt($curlCh, CURLOPT_PROXY, $proxyHost);
            curl_setopt($curlCh, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
            $proxyUsername = Config::getVar('proxy', 'username');
            if ($proxyUsername) {
                $proxyPassword = Config::getVar('proxy', 'password');
                curl_setopt($curlCh, CURLOPT_PROXYUSERPWD, $proxyUsername . ':' . $proxyPassword);
            }
        }
        
        curl_setopt($curlCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlCh, CURLOPT_POST, true);
        curl_setopt($curlCh, CURLOPT_HEADER, 1);
        curl_setopt($curlCh, CURLOPT_BINARYTRANSFER, true);

        import('lib.wizdam.classes.services.DoiCredentialService');
        $doiCredentials = DoiCredentialService::resolveForJournal($journal);
        $crossrefUsername = $doiCredentials->getCrossrefUsername();
        $crossrefPassword = $doiCredentials->getCrossrefPassword();

        curl_setopt($curlCh, CURLOPT_URL, CROSSREF_API_URL);
        curl_setopt($curlCh, CURLOPT_USERPWD, "$crossrefUsername:$crossrefPassword");

        if (!is_readable($filename)) {
            return [['plugins.importexport.crossref.register.error.mdsError', 'File is not readable.']];
        }
        
        $fh = fopen($filename, 'rb');
        $fileSize = (int) filesize($filename);

        $httpheaders = [
            'Content-Type: application/vnd.crossref.deposit+xml',
            'Content-Length: ' . $fileSize
        ];

        curl_setopt($curlCh, CURLOPT_HTTPHEADER, $httpheaders);
        curl_setopt($curlCh, CURLOPT_INFILE, $fh);
        curl_setopt($curlCh, CURLOPT_INFILESIZE, $fileSize);

        $response = curl_exec($curlCh);
        $status = curl_getinfo($curlCh, CURLINFO_HTTP_CODE);
        
        if (is_resource($fh)) {
            fclose($fh);
        }
        
        curl_close($curlCh);

        if ($response === false) {
            return [['plugins.importexport.crossref.register.error.mdsError', 'No response from server.']];
        } elseif ($status !== CROSSREF_API_DEPOSIT_OK) {
            return [['plugins.importexport.crossref.register.error.mdsError', "$status - $response"]];
        }

        // FIX: HTTP POST ke Crossref BERHASIL DITERIMA di sini — tapi Crossref
        // memproses deposit secara asinkron (queued -> in_process -> completed),
        // butuh waktu menit sampai jam. Cek status di bawah (updateDepositStatus)
        // dilakukan detik ini juga, sehingga HAMPIR PASTI belum mencerminkan hasil
        // akhir. Tanpa penanda lokal ini, objek yang baru disubmit akan tetap
        // tampak "belum pernah disubmit" di DB sampai status COMPLETED benar-benar
        // tercatat — sehingga run/klik berikutnya akan menyeleksinya lagi sebagai
        // kandidat unregistered dan mengirim ulang deposit yang SAMA (double
        // submission), padahal submission pertama mungkin masih diproses.
        //
        // FIX (issue-level): sebelumnya blok ini (dan updateDepositStatus() di
        // bawah) HANYA berlaku untuk objek `instanceof Article` — deposit ISSUE
        // yang berhasil di-POST ke Crossref TIDAK PERNAH tercatat statusnya sama
        // sekali (bukan cuma soal timing seperti artikel, tapi permanen: issue
        // akan selamanya tampak "belum registered" dan disubmit ulang setiap kali
        // admin klik "Register"). Sekarang mendukung Article DAN Issue.
        $depositStatusSettingName = $this->getDepositStatusSettingName();
        $depositSubmittedAtSettingName = $this->getDepositSubmittedAtSettingName();
        $submittedAt = time();

        foreach ($objects as $depositedObject) {
            if ($this->_supportsDepositStatusTracking($depositedObject)) {
                $depositedObject->setData($depositStatusSettingName, CROSSREF_STATUS_SUBMITTED);
                $depositedObject->setData($depositSubmittedAtSettingName, $submittedAt);
                $this->_persistDepositStatusSettings($depositedObject, [
                    $depositStatusSettingName      => ['value' => CROSSREF_STATUS_SUBMITTED, 'type' => 'string'],
                    $depositSubmittedAtSettingName => ['value' => $submittedAt, 'type' => 'int'],
                ]);
            }
        }

        foreach ($objects as $depositedObject) {
            if ($this->_supportsDepositStatusTracking($depositedObject)) {
                $this->updateDepositStatus($request, $journal, $depositedObject);
            }
        }
        
        return true;
    }

    /**
     * FIX: Tipe objek yang saat ini didukung untuk pelacakan status deposit
     * granular (submitted/queued/in_process/completed/failed). Perluas array ini
     * kalau dukungan Galley/SuppFile ditambahkan di masa depan — lihat
     * _persistDepositStatusSettings() yang perlu ditambah case DAO-nya juga.
     * @param mixed $object
     * @return bool
     */
    protected function _supportsDepositStatusTracking($object): bool {
        return ($object instanceof Article) || ($object instanceof Issue);
    }

    /**
     * FIX: Tulis satu atau lebih setting status deposit untuk objek Article ATAU
     * Issue, mengikuti mekanisme penyimpanan yang berbeda di tiap DAO:
     * - ArticleDAO punya updateSetting($id, $name, $value, $type) langsung ke
     *   tabel article_settings, tidak perlu pre-registrasi field.
     * - IssueDAO TIDAK punya updateSetting() setara. Setting hanya persisten
     *   lewat updateIssue($issue), yang memanggil updateLocaleFields() ->
     *   updateDataObjectSettings() — dan itu HANYA menyimpan field yang
     *   terdaftar lewat hook getAdditionalFieldNames() (lihat method itu di
     *   bawah, sudah ditambah field-field deposit status).
     *
     * Dibungkus DBConnection::executeWithRetry() untuk konsisten dengan
     * penanganan koneksi transient di seluruh plugin ini.
     *
     * @param Article|Issue $object
     * @param array<string,array{value:mixed,type:string}> $settings Peta nama setting -> ['value'=>..,'type'=>..]
     */
    protected function _persistDepositStatusSettings($object, array $settings): void {
        if ($object instanceof Article) {
            /** @var ArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            DBConnection::executeWithRetry(function () use ($articleDao, $object, $settings) {
                $result = true;
                foreach ($settings as $name => $spec) {
                    $result = $articleDao->updateSetting((int) $object->getId(), $name, $spec['value'], $spec['type']) ?? $result;
                }
                return $result;
            });
            return;
        }

        if ($object instanceof Issue) {
            /** @var IssueDAO $issueDao */
            $issueDao = DAORegistry::getDAO('IssueDAO');
            // FIX: wajib dipanggil SEBELUM updateIssue(), supaya hook
            // getAdditionalFieldNames() terdaftar dan field-field status deposit
            // (yang baru ditambahkan di getAdditionalFieldNames()) benar-benar
            // ikut dipersist oleh updateDataObjectSettings() -- tanpa ini,
            // penulisan status akan diam-diam tidak tersimpan.
            $this->registerDaoHook('IssueDAO');
            DBConnection::executeWithRetry(function () use ($issueDao, $object) {
                $issueDao->updateIssue($object);
                return true;
            });
            return;
        }

        error_log('CrossRefExportPlugin: tipe objek tidak didukung untuk penulisan status deposit: ' . get_class($object));
    }

    /**
     * This method checks the CrossRef APIs, if deposits and registration have been successful.
     * @param Request $request
     * @param Journal $journal
     * @param Article $article
     * @return bool
     */
    public function updateDepositStatus($request, $journal, $article): bool {
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        // FIX (issue-level): sebelumnya method ini hard-code ke ArticleDAO.
        // Kalau dipanggil dengan objek Issue (yang sebelumnya tidak pernah
        // terjadi karena registerDoi() memfilter instanceof Article), baris ini
        // akan salah menulis ke tabel article_settings dengan ID issue. Sekarang
        // penulisan setting didelegasikan ke _persistDepositStatusSettings() yang
        // memilih DAO yang benar berdasarkan tipe objek.
        import('lib.pkp.classes.core.JSONManager');
        $jsonManager = new JSONManager();

        $curlCh = curl_init();
        $proxyHost = Config::getVar('proxy', 'http_host');
        if ($proxyHost) {
            curl_setopt($curlCh, CURLOPT_PROXY, $proxyHost);
            curl_setopt($curlCh, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
            
            $proxyUsername = Config::getVar('proxy', 'username');
            if ($proxyUsername) {
                $proxyPassword = Config::getVar('proxy', 'password');
                curl_setopt($curlCh, CURLOPT_PROXYUSERPWD, $proxyUsername . ':' . $proxyPassword);
            }
        }
        curl_setopt($curlCh, CURLOPT_RETURNTRANSFER, true);

        import('lib.wizdam.classes.services.DoiCredentialService');
        $doiCredentials = DoiCredentialService::resolveForJournal($journal);
        $crossrefUsername = $doiCredentials->getCrossrefUsername();
        $crossrefPassword = $doiCredentials->getCrossrefPassword();
        curl_setopt($curlCh, CURLOPT_USERPWD, "$crossrefUsername:$crossrefPassword");

        $doi = urlencode((string) $article->getPubId('doi'));
        $params = 'filter=doi:' . $doi;
        curl_setopt(
            $curlCh,
            CURLOPT_URL,
            CROSSREF_API_URL . (strpos(CROSSREF_API_URL, '?') === false ? '?' : '&') . $params
        );

        $response = curl_exec($curlCh);

        // FIX: HTTP request di atas bersifat blocking dan bisa memakan waktu
        // beberapa detik. Koneksi DB yang dibuka sebelum curl_exec() (mis. untuk
        // ArticleDAO di atas) bisa jadi sudah idle cukup lama saat kita tiba di
        // sini. Pastikan/pulihkan koneksi SEBELUM melakukan write pertama, supaya
        // "MySQL server has gone away" tertangani di sini, bukan meledak sebagai
        // exception tak tertangani yang membunuh seluruh scheduled task.
        DBConnection::ensureConnection();

        if ($response && curl_getinfo($curlCh, CURLINFO_HTTP_CODE) === CROSSREF_API_RESPONSE_OK) {
            $decodedResponse = $jsonManager->decode($response);
            $pastDeposits = [];
            
            if (isset($decodedResponse->message->items) && is_array($decodedResponse->message->items)) {
                foreach ($decodedResponse->message->items as $item) {
                    $submittedAt = strtotime($item->{'submitted-at'} ?? 'now');
                    $pastDeposits[$submittedAt] = [
                        'status'   => $item->status ?? '',
                        'batch-id' => $item->{'batch-id'} ?? ''
                    ];
                }
            }

            if (count($pastDeposits) > 0) {
                $lastDeposit = $pastDeposits[max(array_keys($pastDeposits))];
                // FIX: normalisasi status mentah Crossref (queued/in_process/
                // completed/failed/dst.) ke konstanta internal aplikasi. Sebelum
                // ini, string mentah dari Crossref ditulis apa adanya ke DB tapi
                // TIDAK dikenali oleh getStatusMapping() (yang cuma memetakan
                // status internal aplikasi) — sehingga status queued/in_process
                // secara efektif tidak terlihat/tidak berlabel di UI meskipun
                // datanya tersimpan. Setelah normalisasi, status apapun yang
                // dikembalikan Crossref akan selalu punya label yang jelas.
                $lastStatus = $this->normalizeCrossrefStatus((string) $lastDeposit['status']);
                $lastBatchId = $lastDeposit['batch-id'];
                
                $statusUrlSettingName = $this->getDepositStatusUrlSettingName();
                $depositStatusSettingName = $this->getDepositStatusSettingName();
                
                if ($article->getData($statusUrlSettingName) !== '/deposits/' . $lastBatchId) {
                    // FIX: bungkus write dengan retry otomatis — jaring pengaman
                    // terakhir jika koneksi mati tepat di antara ensureConnection()
                    // di atas dan write ini. Sekarang lewat helper generik agar
                    // benar untuk Article maupun Issue.
                    $this->_persistDepositStatusSettings($article, [
                        $statusUrlSettingName => ['value' => '/deposits/' . $lastBatchId, 'type' => 'string'],
                    ]);
                }
                
                if ($lastStatus === CROSSREF_STATUS_COMPLETED) {
                    curl_setopt($curlCh, CURLOPT_URL, CROSSREF_WORKS_API . $doi);
                    $worksResponse = curl_exec($curlCh);

                    if ($worksResponse && curl_getinfo($curlCh, CURLINFO_HTTP_CODE) === CROSSREF_API_RESPONSE_OK) {
                        // FIX: curl_exec kedua di atas juga blocking — cek ulang.
                        DBConnection::ensureConnection();
                        $article->setData($depositStatusSettingName, CROSSREF_STATUS_REGISTERED);
                        $this->_persistDepositStatusSettings($article, [
                            $depositStatusSettingName => ['value' => CROSSREF_STATUS_REGISTERED, 'type' => 'string'],
                        ]);
                        $this->markRegistered($request, $article);
                        curl_close($curlCh);
                        return true;
                    }
                }
                
                if ($article->getData($depositStatusSettingName) !== $lastStatus) {
                    $article->setData($depositStatusSettingName, $lastStatus);
                    $this->_persistDepositStatusSettings($article, [
                        $depositStatusSettingName => ['value' => $lastStatus, 'type' => 'string'],
                    ]);
                }
                
                if ($article->getData($this->getPluginId() . '::' . DOI_EXPORT_REGDOI)) {
                    $regDoiSettingName = $this->getPluginId() . '::' . DOI_EXPORT_REGDOI;
                    $this->_persistDepositStatusSettings($article, [
                        $regDoiSettingName => ['value' => null, 'type' => 'string'],
                    ]);
                }
            }
        }

        curl_close($curlCh);
        return false;
    }

    /**
     * Add scheduled tasks to the system cron tab.
     * @see AcronPlugin::parseCronTab()
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackParseCronTab($hookName, $args): bool {
        $taskFilesPath =& $args[0];
        $taskFilesPath[] = $this->getPluginPath() . DIRECTORY_SEPARATOR . 'scheduledTasks.xml';
        return false;
    }

    /**
     * Get the name of the setting used to save the deposit status.
     * @return string
     */
    public function getDepositStatusSettingName(): string {
        return $this->getPluginId() . '::' . CROSSREF_DEPOSIT_STATUS;
    }

    /**
     * Get the name of the setting used to save the URL with the deposit status.
     * @return string
     */
    public function getDepositStatusUrlSettingName(): string {
        return $this->getPluginId() . '::' . CROSSREF_DEPOSIT_STATUS . 'Url';
    }

    /**
     * FIX: Get the name of the setting used to record WHEN a deposit was last
     * submitted locally. Used for the resubmit-cooldown check in
     * _getUnregisteredArticles() (see CrossrefDoiExportPlugin) — independent of
     * whether Crossref's own status API has caught up yet, so a still-processing
     * deposit is never mistaken for "never submitted" and re-sent.
     * @return string
     */
    public function getDepositSubmittedAtSettingName(): string {
        return $this->getPluginId() . '::' . CROSSREF_DEPOSIT_SUBMITTED_AT;
    }

    /**
     * FIX: Normalize a raw status string returned by Crossref's deposit-history
     * API (GET .../deposits?filter=doi:...) into one of this plugin's known
     * status constants. Crossref processes deposits asynchronously
     * (queued -> in_process -> completed, or failed) and this codebase
     * previously only recognized "completed" — every other real Crossref state
     * was invisible to the application. Unknown/future status strings are kept
     * verbatim (never silently reinterpreted as completed or failed) so nothing
     * is lost or misrepresented if Crossref's API vocabulary changes.
     *
     * @param string $rawStatus Status string as returned by Crossref (e.g. 'queued', 'in_process', 'completed', 'failed').
     * @return string One of the CROSSREF_STATUS_* constants.
     */
    public function normalizeCrossrefStatus(string $rawStatus): string {
        $normalized = strtolower(trim($rawStatus));

        $knownStatuses = [
            'queued'      => CROSSREF_STATUS_QUEUED,
            'pending'     => CROSSREF_STATUS_QUEUED,
            'in_process'  => CROSSREF_STATUS_IN_PROCESS,
            'in-process'  => CROSSREF_STATUS_IN_PROCESS,
            'processing'  => CROSSREF_STATUS_IN_PROCESS,
            'completed'   => CROSSREF_STATUS_COMPLETED,
            'success'     => CROSSREF_STATUS_COMPLETED,
            'failed'      => CROSSREF_STATUS_FAILED,
            'failure'     => CROSSREF_STATUS_FAILED,
            'error'       => CROSSREF_STATUS_FAILED,
        ];

        if (isset($knownStatuses[$normalized])) {
            return $knownStatuses[$normalized];
        }

        // FIX: status yang tidak dikenali TIDAK divonis completed/failed secara
        // serampangan. Dicatat sebagai "unknown remote status" plus log, supaya
        // admin sadar ada status baru dari Crossref yang belum dipetakan aplikasi
        // ini, alih-alih diam-diam salah tafsir.
        error_log('CrossRefExportPlugin: status Crossref tidak dikenali: "' . $rawStatus . '". Disimpan apa adanya sebagai unknownRemoteStatus.');
        return CROSSREF_STATUS_UNKNOWN_REMOTE;
    }

    /**
     * Get status mapping for the status display.
     * @return array
     */
    public function getStatusMapping(): array {
        return [
            // FIX (deposit manual): status untuk objek yang di-export XML lalu
            // menunggu konfirmasi apakah admin benar-benar mengupload ke Crossref.
            CROSSREF_STATUS_EXPORTED         => __('plugins.importexport.crossref.status.exported'),
            CROSSREF_STATUS_SUBMITTED        => __('plugins.importexport.crossref.status.submitted'),
            // FIX: status antara yang sebelumnya tidak pernah ditampilkan.
            CROSSREF_STATUS_QUEUED           => __('plugins.importexport.crossref.status.queued'),
            CROSSREF_STATUS_IN_PROCESS       => __('plugins.importexport.crossref.status.inProcess'),
            CROSSREF_STATUS_COMPLETED        => __('plugins.importexport.crossref.status.completed'),
            CROSSREF_STATUS_FAILED           => __('plugins.importexport.crossref.status.failed'),
            CROSSREF_STATUS_REGISTERED       => __('plugins.importexport.crossref.status.registered'),
            CROSSREF_STATUS_MARKEDREGISTERED => __('plugins.importexport.crossref.status.markedRegistered'),
            // FIX: fallback tampilan untuk status mentah Crossref yang belum
            // dikenali aplikasi ini — tetap terlihat di UI, tidak hilang.
            CROSSREF_STATUS_UNKNOWN_REMOTE   => __('plugins.importexport.crossref.status.unknownRemote'),
        ];
    }

}
?>