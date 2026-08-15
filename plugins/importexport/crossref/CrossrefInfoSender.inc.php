<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/crossref/CrossrefInfoSender.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CrossrefInfoSender
 * @ingroup plugins_importexport_crossref
 *
 * @brief Scheduled task to send article information to the ALM server.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');

class CrossrefInfoSender extends ScheduledTask {

    /** @var CrossRefExportPlugin|null */
    protected ?CrossRefExportPlugin $_plugin = null;

    /**
     * Constructor.
     * @param array $args
     */
    public function __construct($args) {
        PluginRegistry::loadCategory('importexport');
        /** @var CrossRefExportPlugin|null $plugin */
        $plugin = PluginRegistry::getPlugin('importexport', 'CrossRefExportPlugin');
        $this->_plugin = $plugin;

        if ($plugin instanceof CrossRefExportPlugin) {
            $plugin->addLocaleData();
        }

        parent::__construct($args);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param array $args
     */
    public function CrossrefInfoSender($args) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get the name of this scheduled task.
     * @see ScheduledTask::getName()
     * @return string
     */
    public function getName(): string {
        return __('plugins.importexport.crossref.senderTask.name');
    }

    /**
     * Execute the actions of this scheduled task.
     * @see ScheduledTask::executeActions()
     * @return bool
     */
    public function executeActions(): bool {
        if (!$this->_plugin) {
            return false;
        }

        $plugin = $this->_plugin;
        $journals = $this->_getJournals();
        $request = Application::get()->getRequest();
        $errors = [];

        // FIX: satu jurnal yang bermasalah (termasuk error koneksi DB yang tidak
        // bisa dipulihkan) tidak lagi menggagalkan seluruh task untuk jurnal
        // lainnya. Sebelumnya, exception apapun di tengah loop ini akan bocor
        // sampai ke AcronPlugin dan membatalkan proses untuk SEMUA jurnal yang
        // belum diproses.
        foreach ($journals as $journal) {
            try {
                $this->_processJournal($journal, $plugin, $request, $errors);
            } catch (Throwable $e) {
                error_log(
                    'CrossrefInfoSender: gagal memproses jurnal "' . $journal->getPath() .
                    '": ' . $e->getMessage() . '. Melanjutkan ke jurnal berikutnya.'
                );
                $this->addExecutionLogEntry(
                    'Journal "' . $journal->getPath() . '" skipped due to error: ' . $e->getMessage(),
                    SCHEDULED_TASK_MESSAGE_TYPE_WARNING
                );
                continue;
            }
        }
        return true;
    }

    /**
     * FIX: Diekstrak dari executeActions() agar bisa dibungkus try/catch per-jurnal
     * (isolasi kegagalan) tanpa mengubah alur logika bisnis aslinya.
     *
     * @param Journal $journal
     * @param CrossRefExportPlugin $plugin
     * @param PKPRequest $request
     * @param array $errors
     */
    protected function _processJournal($journal, CrossRefExportPlugin $plugin, $request, array &$errors): void {
        // FIX: pastikan koneksi DB hidup sebelum mulai memproses jurnal ini —
        // jurnal sebelumnya mungkin baru saja menghabiskan waktu lama di loop
        // artikel + HTTP call, sehingga koneksi bisa idle cukup lama.
        DBConnection::ensureConnection();

        // FIX: bypassCooldown=true di sini SENGAJA -- status-check (updateDepositStatus
        // di bawah) adalah operasi read-only ke Crossref, tidak berisiko double-
        // submission, dan justru PALING dibutuhkan selama artikel masih dalam
        // masa cooldown (supaya begitu Crossref selesai memproses, statusnya
        // cepat terdeteksi, bukan menunggu cooldown habis dulu). Keputusan
        // "boleh disubmit ulang atau tidak" tetap menghormati cooldown, tapi
        // dicek terpisah di bawah lewat isEligibleForAutoResubmit().
        $unregisteredArticles = $plugin->_getUnregisteredArticles($journal, true);
        $unregisteredArticlesIds = [];

        foreach ($unregisteredArticles as $articleData) {
            $article = $articleData['article'] ?? null;
            if ($article instanceof PublishedArticle && $plugin->canBeExported($article, $errors)) {
                $unregisteredArticlesIds[(int) $article->getId()] = $article;
            }
        }

        $toBeDepositedIds = [];
        $notify = false;
        $processedCount = 0;
        foreach ($unregisteredArticlesIds as $id => $article) {
            // FIX: cek koneksi setiap beberapa artikel. Ini pengaman tambahan di
            // luar retry di dalam updateDepositStatus() itu sendiri — setiap
            // artikel melibatkan HTTP request blocking ke Crossref API yang bisa
            // membuat koneksi DB idle cukup lama untuk melewati wait_timeout.
            if ($processedCount > 0 && $processedCount % 10 === 0) {
                DBConnection::ensureConnection();
            }
            $processedCount++;

            $currentStatus = $article->getData($plugin->getDepositStatusSettingName());
            $plugin->updateDepositStatus($request, $journal, $article);
            $newStatus = $article->getData($plugin->getDepositStatusSettingName());

            // FIX: sebelumnya hanya status KOSONG yang dianggap "perlu deposit".
            // Sekarang artikel dengan status in-flight (submitted/queued/
            // in_process) yang cooldown-nya sudah kedaluwarsa juga harus
            // dianggap kandidat submit ulang. Kelayakan ini (status + cooldown)
            // sekarang dicek lewat isEligibleForAutoResubmit() di plugin --
            // status CROSSREF_STATUS_EXPORTED (deposit manual) SENGAJA tidak
            // pernah dianggap layak resubmit otomatis di sana; artikel exported
            // yang basi hanya akan tampak lagi di daftar "belum diregister"
            // untuk PERHATIAN admin, bukan disubmit ulang tanpa sepengetahuannya.
            if (!$newStatus || $plugin->isEligibleForAutoResubmit($article, (string) $newStatus)) {
                $toBeDepositedIds[] = $id;
            }

            if (!$notify && $newStatus === CROSSREF_STATUS_FAILED && $currentStatus !== CROSSREF_STATUS_FAILED) {
                $notify = true;
            }
        }

        if ($notify) {
            /** @var RoleDAO $roleDao */
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $journalManagers = $roleDao->getUsersByRoleId(ROLE_ID_JOURNAL_MANAGER, (int) $journal->getId());
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();

            while ($journalManager = $journalManagers->next()) {
                $notificationManager->createTrivialNotification(
                    (int) $journalManager->getId(),
                    NOTIFICATION_TYPE_ERROR,
                    ['contents' => __('plugins.importexport.crossref.notification.failed')]
                );
            }
        }

        $autoRegistrationActive = $plugin->getSetting((int) $journal->getId(), 'automaticRegistration');
        import('lib.wizdam.classes.services.JournalOwnershipService');
        if (!$autoRegistrationActive && JournalOwnershipService::isOwnership($journal)) {
            import('lib.wizdam.classes.services.DoiCredentialService');
            $autoRegistrationActive = DoiCredentialService::resolveForJournal($journal)->isConfigured();
        }

        if (!empty($toBeDepositedIds) && $autoRegistrationActive) {
            // FIX: koneksi bisa sudah idle lama setelah loop update-status di atas
            // (banyak HTTP call). Pastikan hidup sebelum masuk registerObjects(),
            // yang sendiri juga melakukan lebih banyak HTTP call + DB write.
            DBConnection::ensureConnection();

            $exportSpec = [DOI_EXPORT_ARTICLES => $toBeDepositedIds];
            $result = $plugin->registerObjects($request, $exportSpec, $journal);

            if ($result !== true) {
                if (is_array($result)) {
                    foreach ($result as $error) {
                        if (is_array($error) && !empty($error)) {
                            $this->addExecutionLogEntry(
                                __($error[0], ['param' => $error[1] ?? null]),
                                SCHEDULED_TASK_MESSAGE_TYPE_WARNING
                            );
                        }
                    }
                }
            }
        }

        // FIX (pemantauan deposit manual + otomatis untuk ISSUE): sebelumnya
        // scheduled task ini SAMA SEKALI tidak menyentuh issue -- hanya artikel.
        // Issue yang di-deposit lewat tombol "Register" (otomatis, admin-
        // triggered) ATAUPUN "Export XML" (manual, lihat exportObjects() /
        // _markObjectsAsExported()) tidak pernah mendapat refresh status
        // berkala. Polling ini HANYA mengecek status riil ke Crossref API dan
        // menyinkronkan DB (queued/in_process/completed/failed) -- TIDAK PERNAH
        // memicu registerObjects()/registerDoi() untuk issue; submit/resubmit
        // issue tetap sepenuhnya keputusan manual admin lewat UI.
        $this->_pollIssueDepositStatuses($journal, $plugin, $request);
    }

    /**
     * FIX: Perbarui status deposit (queued/in_process/completed/failed) untuk
     * semua issue yang statusnya masih "in-flight" (baru diekspor manual atau
     * disubmit otomatis, belum completed/failed). Method ini HANYA melakukan
     * status-check (GET) ke Crossref API lewat updateDepositStatus() -- sama
     * persis mekanisme yang dipakai untuk artikel -- dan TIDAK PERNAH men-
     * submit/resubmit deposit issue apapun. Ini yang membuat status deposit
     * manual (export XML lalu upload sendiri ke Crossref) akhirnya ikut
     * terlihat di panel, tersinkron otomatis sesuai kondisi riil di Crossref,
     * alih-alih hanya bisa "dipilih" manual oleh admin lewat tombol Mark
     * Registered.
     *
     * @param Journal $journal
     * @param CrossRefExportPlugin $plugin
     * @param PKPRequest $request
     */
    protected function _pollIssueDepositStatuses($journal, CrossRefExportPlugin $plugin, $request): void {
        if (!method_exists($plugin, '_getUnregisteredIssues') || !method_exists($plugin, 'updateDepositStatus')) {
            return;
        }

        // FIX: bypassCooldown=true -- sama seperti artikel, status-check untuk
        // issue harus tetap jalan meski masih dalam masa cooldown (read-only,
        // tidak berisiko double-submission; lihat catatan di _getUnregisteredArticles()).
        $unregisteredIssues = $plugin->_getUnregisteredIssues($journal, true);
        if (empty($unregisteredIssues)) {
            return;
        }

        $processedCount = 0;
        foreach ($unregisteredIssues as $issue) {
            if (!($issue instanceof Issue)) {
                continue;
            }

            // FIX: hanya poll issue yang MEMANG punya jejak deposit (exported/
            // submitted/queued/in_process) -- issue yang belum pernah
            // diekspor/disubmit sama sekali tidak perlu dicek ke Crossref, tidak
            // ada apapun untuk ditemukan di sana.
            $status = method_exists($plugin, 'getDepositStatusSettingName')
                ? $issue->getData($plugin->getDepositStatusSettingName())
                : null;
            if (!$status || !defined('CROSSREF_IN_FLIGHT_STATUSES') || !in_array($status, CROSSREF_IN_FLIGHT_STATUSES, true)) {
                continue;
            }

            if ($processedCount > 0 && $processedCount % 10 === 0) {
                DBConnection::ensureConnection();
            }
            $processedCount++;

            $plugin->updateDepositStatus($request, $journal, $issue);
        }
    }

    /**
     * Get all journals that meet the requirements to have
     * their articles DOIs sent to Crossref.
     * @see CrossrefExportPlugin::registerObjects()
     * @return array
     */
    public function _getJournals(): array {
        $plugin = $this->_plugin;
        if (!$plugin) {
            return [];
        }

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journalFactory = $journalDao->getJournals(true);

        $journals = [];
        while ($journal = $journalFactory->next()) {
            $journalId = (int) $journal->getId();

            $hasOwnCredentials = $plugin->getSetting($journalId, 'username')
                && $plugin->getSetting($journalId, 'password')
                && $plugin->getSetting($journalId, 'automaticRegistration');

            if (!$hasOwnCredentials) {
                import('lib.wizdam.classes.services.JournalOwnershipService');
                if (JournalOwnershipService::isPartnership($journal)) {
                    continue;
                }
                import('lib.wizdam.classes.services.DoiCredentialService');
                $doiCredentials = DoiCredentialService::resolveForJournal($journal);
                if (!$doiCredentials->isConfigured()) {
                    continue;
                }
            }

            $doiPrefix = null;
            $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $journalId);
            if (is_array($pubIdPlugins) && isset($pubIdPlugins['DOIPubIdPlugin'])) {
                $doiPubIdPlugin = $pubIdPlugins['DOIPubIdPlugin'];
                if (!$doiPubIdPlugin->getSetting($journalId, 'enabled')) {
                    continue;
                }
                $doiPrefix = $doiPubIdPlugin->getSetting($journalId, 'doiPrefix');
            }

            if (!empty($doiPrefix)) {
                $journals[] = $journal;
            } else {
                $this->addExecutionLogEntry(
                    __('plugins.importexport.crossref.senderTask.warning.noDOIprefix', ['path' => $journal->getPath()]),
                    SCHEDULED_TASK_MESSAGE_TYPE_WARNING
                );
            }
        }

        return $journals;
    }
    
}
?>