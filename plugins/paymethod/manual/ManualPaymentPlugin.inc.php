<?php
declare(strict_types=1);

/**
 * @file plugins/paymethod/manual/ManualPaymentPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManualPaymentPlugin
 * @ingroup plugins_paymethod_manual
 *
 * @brief Manual payment plugin class
 * [WIZDAM EDITION] Fixed PHP 8 ArgumentCountError & Parameter Mismatch
 */

import('classes.plugins.PaymethodPlugin');

class ManualPaymentPlugin extends PaymethodPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ManualPaymentPlugin() {
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
     * Get the name of this plugin. The name must be unique within its category.
     * @see Plugin::getName
     * @return string Name of plugin
     */
    public function getName(): string {
        return 'Manual';
    }

    /**
     * Get the display name of this plugin.
     * @see Plugin::getDisplayName
     * @return string Display name of plugin
     */
    public function getDisplayName(): string {
        return __('plugins.paymethod.manual.displayName');
    }

    /**
     * Get a description of the plugin.
     * @see Plugin::getDescription
     * @return string Description of the plugin
     */
    public function getDescription(): string {
        return __('plugins.paymethod.manual.description');
    }

    /**
     * Register the plugin.
     * @see Plugin::register
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool True if plugin initialized successfully
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        if (parent::register($category, $path, $mainContextId)) {
            $this->addLocaleData();
            return true;
        }
        return false;
    }

    /**
     * Get the names of the settings form fields for this plugin.
     * @see PaymentPlugin::getSettingsFormFieldNames
     * @return array
     */
    public function getSettingsFormFieldNames(): array {
        return ['manualInstructions'];
    }

    /**
     * Get the template for the settings form.
     * @return string
     */
    public function displayPaymentSettingsForm($params, $smarty) {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
    
        if ($context) {
            $smarty->assign('manualInstructions', $this->getSetting($context->getId(), 'manualInstructions'));
        }
    
        // Cetak HTML langsung dari template
        $smarty->display($this->getTemplatePath() . 'settingsForm.tpl');
    }
    
    /**
     * Determine whether the plugin is configured.
     * @see PaymentPlugin::isConfigured
     * @return bool True iff the plugin is configured
     */
    public function isConfigured(): bool {
        // Selalu return true agar OJSPaymentManager tidak membunuh halaman
        return true; 
    }

    /**
     * Display the payment form.
     * @param int $queuedPaymentId
     * @param QueuedPayment $queuedPayment
     * @param Request $request
     * @return bool
     */
    public function displayPaymentForm($queuedPaymentId, $queuedPayment, $request) {
        $context = $request->getContext();
        $templateMgr = TemplateManager::getManager(); 
        
        AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);

        // --- 1. [FAILSAFE ANTI-WSOD] PENANGANAN INSTRUKSI ---
        $instructions = $this->getSetting($context->getId(), 'manualInstructions');
        $cleanInstructions = trim(strip_tags((string) $instructions));
        
        if (empty($cleanInstructions)) {
             $templateMgr->assign('manualInstructions', '<div class="pkp_form_error" style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 4px;"><strong>Peringatan:</strong> Instruksi pembayaran manual belum diatur. Transaksi belum bisa dilanjutkan.</div>');
        } else {
             $templateMgr->assign('manualInstructions', $instructions);
        }

        // --- 2. [CORE OJS] DATA ITEM DASAR ---
        $templateMgr->assign('itemName', $queuedPayment->getName());
        $templateMgr->assign('itemDescription', $queuedPayment->getDescription());
        
        if ($queuedPayment->getAmount() > 0) {
            $templateMgr->assign('itemAmount', $queuedPayment->getAmount());
            $templateMgr->assign('itemCurrencyCode', $queuedPayment->getCurrencyCode());
        }

        // --- 3. [WIZDAM UX] BACA DATA INVOICE DARI DATABASE ---
        $articleId = $queuedPayment->getAssocId();
        
        if ($articleId) {
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle($articleId);
            $journal = Request::getJournal(); 
            
            if ($article && $journal) {
                // Instansiasi Formatter
                $locale = AppLocale::getLocale();
                $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
                $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
                $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);

                // Ambil nilai TOTAL AKHIR dari antrean OJS
                $finalAmount = $queuedPayment->getAmount();

                // --- KOP SURAT (SITE SETTINGS) ---
                $siteDao = DAORegistry::getDAO('SiteDAO');
                $site = $siteDao->getSite();
                $siteTitle = $site->getLocalizedTitle();

                // --- PROFIL DITAGIHKAN KEPADA (CORRESPONDING AUTHOR) ---
                $authorDao = DAORegistry::getDAO('AuthorDAO');
                $authors = $authorDao->getAuthorsBySubmissionId($articleId);
                
                $correspondingAuthor = null;
                foreach ($authors as $author) {
                    if ($author->getPrimaryContact()) { 
                        $correspondingAuthor = $author;
                        break;
                    }
                }

                if (!$correspondingAuthor && !empty($authors)) {
                    $correspondingAuthor = $authors[0];
                }

                $userDao = DAORegistry::getDAO('UserDAO');
                $submitter = $userDao->getById($queuedPayment->getUserId());

                if ($correspondingAuthor) {
                    $billedName = $correspondingAuthor->getFullName();
                    $billedEmail = $correspondingAuthor->getEmail();
                    $rawAffiliation = $correspondingAuthor->getLocalizedAffiliation();
                    $countryCode = $correspondingAuthor->getCountry();
                } else {
                    $billedName = $submitter->getFullName();
                    $billedEmail = $submitter->getEmail();
                    $rawAffiliation = $submitter->getLocalizedAffiliation();
                    $countryCode = $submitter->getCountry();
                }

                // Proses Multi-Afiliasi dan Negara
                $affiliationList = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$rawAffiliation))));
                
                if (!empty($countryCode)) {
                    $countryDao = DAORegistry::getDAO('CountryDAO');
                    $countryName = $countryDao->getCountry($countryCode);
                    if ($countryName) {
                        if (empty($affiliationList)) {
                            $affiliationList[] = $countryName;
                        } else {
                            $lastIndex = count($affiliationList) - 1;
                            $affiliationList[$lastIndex] .= ', ' . $countryName;
                        }
                    }
                }

                // --- TANGGAL (JATUH TEMPO +7 HARI) ---
                $dateBilled = date('d F Y');
                $dateDue = date('d F Y', strtotime('+7 days'));

                // --- REVERSE CALCULATION (PAJAK & DISKON) ---
                $settingTaxRate = (float) $journal->getSetting('paymentTax');
                $taxRate = $settingTaxRate > 0 ? ($settingTaxRate / 100) : 0.00;
                $isTaxInclusive = (bool) $journal->getSetting('paymentTaxInclusive');
                $discount = (float) $journal->getSetting('paymentDiscount');
                
                // Final amount dari queue (sudah diproses createQueuedPayment)
                $finalAmount = $queuedPayment->getAmount();
                
                // Hitung amount setelah discount (sebelum komponen VAT)
                if ($isTaxInclusive) {
                    // VAT Inclusive: finalAmount sudah merupakan amount setelah discount
                    $amountAfterDiscount = $finalAmount;
                    // Ekstrak komponen VAT untuk display (hanya untuk transparansi)
                    $baseForVat = $amountAfterDiscount / (1 + $taxRate);
                    $tax = $amountAfterDiscount - $baseForVat;
                } else {
                    // VAT Exclusive: finalAmount = amountAfterDiscount + VAT
                    $amountAfterDiscount = $finalAmount / (1 + $taxRate);
                    $tax = $amountAfterDiscount * $taxRate;
                }
                
                // Hitung fee asli (sebelum discount) - ini yang ditampilkan di baris Fee
                $originalFee = $amountAfterDiscount + $discount;
                
                // Subtotal untuk display (amount setelah discount)
                $subtotal = $amountAfterDiscount;
                
                // Assign fee berdasarkan tipe pembayaran
                $feeSubmission  = 0.00;
                $feeFastTrack   = 0.00;
                $feePublication = 0.00;
                
                $paymentType = $queuedPayment->getType();
                
                if ($paymentType == 5 || $paymentType == 'PAYMENT_TYPE_SUBMISSION') {
                    $feeSubmission = $originalFee;
                } elseif ($paymentType == 6 || $paymentType == 'PAYMENT_TYPE_FASTTRACK') {
                    $feeFastTrack = $originalFee;
                } elseif ($paymentType == 7 || $paymentType == 'PAYMENT_TYPE_PUBLICATION') {
                    $feePublication = $originalFee;
                }

                // --- BACA IDENTITAS FAKTUR DARI DATABASE ---
                $invoiceNumber = '';
                $invoiceCode   = '';
                
                $result = $articleDao->retrieve(
                    "SELECT setting_name, setting_value FROM article_settings WHERE article_id = ? AND setting_name IN ('wizdam_invoice_number', 'wizdam_invoice_code')", 
                    array((int)$articleId)
                );
                
                while (!$result->EOF) {
                    $row = $result->GetRowAssoc(false);
                    if ($row['setting_name'] == 'wizdam_invoice_number') $invoiceNumber = $row['setting_value'];
                    if ($row['setting_name'] == 'wizdam_invoice_code')   $invoiceCode   = $row['setting_value'];
                    $result->MoveNext();
                }
                $result->Close();

                if (empty($invoiceNumber)) $invoiceNumber = 'INVOICE-PENDING-' . $articleId;
                if (empty($invoiceCode))   $invoiceCode   = 'Manuscript#' . str_pad((string)$articleId, 7, '0', STR_PAD_LEFT);

                // --- ASSIGN KE SMARTY ---
                $templateMgr->assign('invoiceSiteTitle', $siteTitle);
                $templateMgr->assign('invoiceBilledName', $billedName);
                $templateMgr->assign('invoiceBilledAffiliations', $affiliationList); 
                $templateMgr->assign('invoiceBilledEmail', $billedEmail);
                $templateMgr->assign('invoiceDateBilled', $dateBilled);
                $templateMgr->assign('invoiceDateDue', $dateDue);

                $templateMgr->assign('invoiceArticleId', $articleId);
                $templateMgr->assign('invoiceArticleTitle', $article->getLocalizedTitle());
                $templateMgr->assign('invoiceAuthors', $article->getAuthorString());
                $templateMgr->assign('invoiceNumber', $invoiceNumber);
                $templateMgr->assign('invoiceCode', $invoiceCode);
                
                $templateMgr->assign('feeSubmission', $formatter->format($feeSubmission));
                $templateMgr->assign('feeFastTrack', $formatter->format($feeFastTrack));
                $templateMgr->assign('feePublication', $formatter->format($feePublication));
                $templateMgr->assign('subtotal', $formatter->format($subtotal));
                $templateMgr->assign('discount', $formatter->format($discount));
                
                $templateMgr->assign('taxRateLabel', (string)$settingTaxRate);
                $templateMgr->assign('isTaxInclusive', $isTaxInclusive);
                $templateMgr->assign('tax', $formatter->format($tax));
                $templateMgr->assign('finalAmount', $formatter->format($finalAmount));
            }
        }

        $templateMgr->assign('queuedPaymentId', $queuedPaymentId);
        $templateMgr->display($this->getTemplatePath() . 'paymentForm.tpl');
        
        return true; 
    }

    /**
     * Handle incoming requests/notifications.
     * @param array $args
     * @param Request $request
     */
    public function handle($args, $request) {
        $context = $request->getContext();
        $templateMgr = TemplateManager::getManager(); // Disesuaikan
        $user = $request->getUser();
        
        $op = isset($args[0]) ? $args[0] : null;
        $queuedPaymentId = isset($args[1]) ? ((int) $args[1]) : 0;

        // Bypass Application kernel, direct instantiation
        import('classes.payment.ojs.OJSPaymentManager');
        if (!class_exists('OJSPaymentManager')) {
            error_log("ManualPaymentPlugin: OJSPaymentManager class not found.");
            $request->redirect(null, 'index');
        }
        
        $ojsPaymentManager = new \OJSPaymentManager($request);
        $queuedPayment = $ojsPaymentManager->getQueuedPayment($queuedPaymentId);
        
        if (!$queuedPayment) $request->redirect(null, 'index');

        switch ($op) {
            case 'notify':
                import('classes.mail.MailTemplate');
                AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
                
                $contactName = $context->getSetting('contactName');
                $contactEmail = $context->getSetting('contactEmail');
                
                $mail = new MailTemplate('MANUAL_PAYMENT_NOTIFICATION');
                $mail->setFrom($contactEmail, $contactName);
                $mail->addRecipient($contactEmail, $contactName);
                
                $mail->assignParams([
                    'journalName' => $context->getLocalizedTitle(),
                    'userFullName' => $user ? $user->getFullName() : ('(' . __('common.none') . ')'),
                    'userName' => $user ? $user->getUsername() : ('(' . __('common.none') . ')'),
                    'itemName' => $queuedPayment->getName(),
                    'itemCost' => $queuedPayment->getAmount(),
                    'itemCurrencyCode' => $queuedPayment->getCurrencyCode()
                ]);
                $mail->send();

                $templateMgr->assign([
                    'currentUrl' => $request->url(null, null, 'payment', 'plugin', ['notify', $queuedPaymentId]),
                    'pageTitle' => 'plugins.paymethod.manual.paymentNotification',
                    'message' => 'plugins.paymethod.manual.notificationSent',
                    'backLink' => $queuedPayment->getRequestUrl(),
                    'backLinkLabel' => 'common.continue'
                ]);
                $templateMgr->display('common/message.tpl');
                exit();
        }
        
        // Memanggil parent yang valid secara arsitektur
        parent::handle($args, $request); 
    }

    /**
     * Get the email templates installation file.
     * @see Plugin::getInstallEmailTemplatesFile
     * @return string
     */
    public function getInstallEmailTemplatesFile(): ?string {
        return ($this->getPluginPath() . DIRECTORY_SEPARATOR . 'emailTemplates.xml');
    }

    /**
     * Get the email template data installation file.
     * @see Plugin::getInstallEmailTemplateDataFile
     * @return string
     */
    public function getInstallEmailTemplateDataFile(): ?string {
        return ($this->getPluginPath() . '/locale/{$installedLocale}/emailTemplates.xml');
    }
}
?>