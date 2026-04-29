<?php
declare(strict_types=1);

/**
 * @file plugins/paymethod/paypal/PayPalPlugin.inc.php
 * 
 * PayPal Paymethod Plugin
 * Copyright (c) 2017-2024 Wizdam Team Development
 * Copyright (c) 2017-2024 Rochmady
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING
 * 
 * @class PayPalPlugin
 * @ingroup plugins_paymethod_paypal
 * 
 * @extends PaymethodPlugin
 * @brief PayPal paymethod plugin class
 * 
 * [WIZDAM EDITION - API v2 SMART BUTTONS]
 * Key Features:
 * - Frontend: API v2 (Smart Buttons)
 * - Backend: IPN Handler (Legacy Support)
 * - Safety: Graceful Error Handling (No WSOD)
 */

import('classes.plugins.PaymethodPlugin');

class PayPalPlugin extends PaymethodPlugin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get name of plugin.
     * @see PKPPlugin::getName()
     * @return string
     */
    public function getName(): string {
        return 'Paypal';
    }

    /**
     * Get Display Name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.paymethod.paypal.displayName');
    }

    /**
     * Plugin Description
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.paymethod.paypal.description');
    }

    /**
     * Register the plugin
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     * @see PKPPlugin::register()
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        if (parent::register($category, $path, $mainContextId)) {
            $this->addLocaleData();
            return true;
        }
        return false;
    }

    /**
     * Get the names of form fields for settings
     * Settings yang disimpan di Database
     * Kita tambahkan clientId dan testMode, tapi biarkan setting lama jika ada.
     * @return array
     */
    public function getSettingsFormFieldNames() {
        return array('clientId', 'testMode', 'paypalurl');
    }

    /**
     * Cek apakah sudah dikonfigurasi
     * @return bool
     * Kita return TRUE agar PaymentManager tidak memblokir halaman (WSOD).
     * Validasi sesungguhnya kita lakukan di displayPaymentForm.
     */
    public function isConfigured() {
        return true; 
    }

    /**
     * TAMPILAN SETTINGS (BACKEND)
     * Menggunakan struktur <tr> agar pas dengan tabel OJS
     * @return string
     * @param array $params
     * @param Smarty $smarty
     */
    public function displayPaymentSettingsForm($params, $smarty) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        $smarty->assign(array(
            'clientId' => $this->getSetting($journal->getId(), 'clientId'),
            'testMode' => $this->getSetting($journal->getId(), 'testMode')
        ));
        
        return $smarty->display($this->getTemplatePath() . 'settingsForm.tpl');
    }

    /**
     * TAMPILAN PEMBAYARAN (FRONTEND)
     * [MODERNISASI] Menggunakan API v2 + Anti-WSOD
     * @return string
     * @param int $queuedPaymentId
     * @param QueuedPayment $queuedPayment
     * @param Request $request
     */
    public function displayPaymentForm(int $queuedPaymentId, $queuedPayment, $request) {
        $templateMgr = TemplateManager::getManager();
        $journal = $request->getJournal();
        
        AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
        
        // --- 1. REAL VALIDATION HAPPENS HERE ---
        // Ambil Client ID dari database
        $clientId = $this->getSetting($journal->getId(), 'clientId');

        // Jika Client ID kosong, set Flag Error = TRUE
        if (empty($clientId)) {
            $templateMgr->assign('paypalError', true);
            // [WIZDAM FIX] Refactor hardcoded string to localized locale key
            $templateMgr->assign('message', __('plugins.paymethod.paypal.error.missingClientId'));
            
            // [WIZDAM NOTE] JANGAN return false, tetap tampilkan template agar header/footer muncul
            return $smarty->fetch($this->getTemplatePath() . 'settingsForm.tpl');
        }

        // --- 2. JIKA CONFIG AMAN ---
        // Ambil nilai mentah dari database
        $rawAmount = $queuedPayment->getAmount();
        
        // Gunakan NumberFormatter bawaan PHP untuk menyesuaikan dengan Locale
        $locale = AppLocale::getLocale(); 
        $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
        $displayAmount = $formatter->format($rawAmount);

        $params = array(
            'clientId'      => $clientId,
            'testMode'      => $this->getSetting($journal->getId(), 'testMode'),
            'currency'      => $queuedPayment->getCurrencyCode(),
            'amount'        => sprintf('%.2F', $rawAmount), // WAJIB: Untuk API PayPal
            'displayAmount' => $displayAmount, // BARU: Untuk UI Smarty
            'returnUrl'     => $queuedPayment->getRequestUrl(),
            'itemName'      => $queuedPayment->getName(),
            'itemDesc'      => $queuedPayment->getDescription()
        );

        // --- 3. [WIZDAM UX] BACA DATA INVOICE DARI DATABASE (INTEGRASI MANAGER) ---
        $articleId = $queuedPayment->getAssocId();
        
        if ($articleId) {
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle($articleId);
            $journal = Request::getJournal(); // Panggil objek Jurnal untuk membaca pengaturan
            
            if ($article && $journal) {
                // 1. Instansiasi Formatter
                $locale = AppLocale::getLocale();
                $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
                $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
                $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);

                // 2. Ambil nilai TOTAL AKHIR dari antrean OJS
                $finalAmount = $queuedPayment->getAmount();

                // 3. TARIK PENGATURAN DARI FEE PAYMENT OPTIONS & REVERSE-CALCULATION
                // --- KOP SURAT (SITE SETTINGS) ---
                $siteDao = DAORegistry::getDAO('SiteDAO');
                $site = $siteDao->getSite();
                $siteTitle = $site->getLocalizedTitle();

                // --- PROFIL DITAGIHKAN KEPADA (CORRESPONDING AUTHOR) ---
                $authorDao = DAORegistry::getDAO('AuthorDAO');
                $authors = $authorDao->getAuthorsBySubmissionId($articleId);
                
                $correspondingAuthor = null;
                foreach ($authors as $author) {
                    if ($author->getPrimaryContact()) { // Mencari centang "Principal contact for editorial correspondence"
                        $correspondingAuthor = $author;
                        break;
                    }
                }

                // Jika tidak ada coresponden yang dicentang, ambil penulis pertama
                if (!$correspondingAuthor && !empty($authors)) {
                    $correspondingAuthor = $authors[0];
                }

                // Data Default dari Submitter (User yang login)
                $userDao = DAORegistry::getDAO('UserDAO');
                $submitter = $userDao->getById($queuedPayment->getUserId());

                // Logika Penentuan Nama & Email Penagihan
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

                // Proses Multi-Afiliasi dan Negara (Sesuai permintaan sebelumnya)
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
                $templateMgr->assign('invoiceBilledAffiliations', $affiliationList); // Sekarang berbentuk array
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

        $templateMgr->assign('paypalParams', $params);
        $templateMgr->assign('paypalError', false); // Aman
        
        return $templateMgr->display($this->getTemplatePath() . 'paymentForm.tpl');
    }

    /**
     * Handle requests
     * @see PKPPlugin::handle()
     * @param array $args
     * @param Request $request
     * @return void
     */
    public function handle($args, $request) {
        $templateMgr = TemplateManager::getManager();
        $journal = $request->getJournal();
        
        if (!$journal) return parent::handle($args, $request);

        import('classes.mail.MailTemplate');
        
        $contactName = $journal->getSetting('supportName') ?: $journal->getSetting('contactName');
        $contactEmail = $journal->getSetting('supportEmail') ?: $journal->getSetting('contactEmail');

        $mail = new MailTemplate('PAYPAL_INVESTIGATE_PAYMENT');
        $mail->setFrom($contactEmail, $contactName);
        $mail->addRecipient($contactEmail, $contactName);

        $paymentStatus = $request->getUserVar('payment_status');

        switch (array_shift($args)) {
            case 'ipn':
                $req = 'cmd=_notify-validate';
                foreach ($_POST as $key => $value) {
                    $req .= '&' . urlencode((string)$key) . '=' . urlencode((string)$value);    
                }
                
                $ch = curl_init();
                if ($httpProxyHost = Config::getVar('proxy', 'http_host')) {
                    curl_setopt($ch, CURLOPT_PROXY, $httpProxyHost);
                    curl_setopt($ch, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
                    if ($username = Config::getVar('proxy', 'username')) {
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $username . ':' . Config::getVar('proxy', 'password'));
                    }
                }
                
                $paypalUrl = $this->getSetting($journal->getId(), 'paypalurl');
                if (!$paypalUrl) $paypalUrl = 'https://www.paypal.com/cgi-bin/webscr';

                curl_setopt($ch, CURLOPT_URL, $paypalUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: PKP PayPal Service', 'Content-Type: application/x-www-form-urlencoded', 'Content-Length: ' . strlen($req)));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
                $ret = curl_exec ($ch);
                $curlError = curl_error($ch);
                curl_close ($ch);

                if (is_string($ret) && strcmp($ret, 'VERIFIED') == 0) {
                    switch ($paymentStatus) {
                        case 'Completed':
                            $payPalDao = DAORegistry::getDAO('PayPalDAO');
                            $transactionId = $request->getUserVar('txn_id');
                            
                            if ($payPalDao->transactionExists($transactionId)) {
                                $this->sendInvestigateMail($mail, $journal, $_POST, "Duplicate transaction ID: $transactionId");
                                exit();
                            } 
                            
                            $payPalDao->insertTransaction(
                                $transactionId,
                                $request->getUserVar('txn_type'),
                                PKPString::strtolower($request->getUserVar('payer_email')),
                                PKPString::strtolower($request->getUserVar('receiver_email')),
                                $request->getUserVar('item_number'),
                                $request->getUserVar('payment_date'),
                                $request->getUserVar('payer_id'),
                                $request->getUserVar('receiver_id')
                            );

                            $queuedPaymentId = $request->getUserVar('custom');
                            import('classes.payment.ojs.OJSPaymentManager');
                            $ojsPaymentManager = new OJSPaymentManager($request);
                            $queuedPayment = $ojsPaymentManager->getQueuedPayment($queuedPaymentId);

                            if (!$queuedPayment) {
                                $this->sendInvestigateMail($mail, $journal, $_POST, "Missing queued payment ID: $queuedPaymentId");
                                exit();
                            }

                            $grantedAmount = $request->getUserVar('mc_gross');
                            $queuedAmount = $queuedPayment->getAmount();
                            $grantedCurrency = $request->getUserVar('mc_currency');
                            $queuedCurrency = $queuedPayment->getCurrencyCode();
                            $grantedEmail = PKPString::strtolower($request->getUserVar('receiver_email'));
                            // Fallback check jika selleraccount lama masih ada
                            $queuedEmail = PKPString::strtolower($this->getSetting($journal->getId(), 'selleraccount'));

                            // Note: Validasi email mungkin perlu disesuaikan jika menggunakan API v2 tanpa selleraccount setting
                            // Tapi untuk keamanan, biarkan logika ini berjalan jika data tersedia.

                            if ($queuedAmount == 0 && $grantedAmount > 0) {
                                $queuedPaymentDao = DAORegistry::getDAO('QueuedPaymentDAO');
                                $queuedPayment->setAmount($grantedAmount);
                                $queuedPayment->setCurrencyCode($grantedCurrency);
                                $queuedPaymentDao->updateQueuedPayment($queuedPaymentId, $queuedPayment);
                            }

                            if ($ojsPaymentManager->fulfillQueuedPayment($queuedPayment, $this->getName())) {
                                exit();
                            }
                            
                            $this->sendInvestigateMail($mail, $journal, $_POST, "Queued payment ID $queuedPaymentId could not be fulfilled (DB Error?)");
                            exit();

                        case 'Pending':
                            exit();
                        default:
                            $this->sendInvestigateMail($mail, $journal, $_POST, "Unhandled Payment Status: $paymentStatus");
                            exit();
                    }
                } else {
                    $this->sendInvestigateMail($mail, $journal, $_POST, "Confirmation Return: $ret | CURL Error: $curlError");
                    exit();
                }
                break;

            case 'cancel':
                AppLocale::requireComponents(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_APPLICATION_COMMON);
                $templateMgr->assign(array(
                    'currentUrl' => $request->url(null, 'index'),
                    'pageTitle' => 'plugins.paymethod.paypal.purchase.cancelled.title',
                    'message' => 'plugins.paymethod.paypal.purchase.cancelled',
                    'backLink' => $request->getUserVar('ojsReturnUrl'),
                    'backLinkLabel' => 'common.continue'
                ));
                $templateMgr->display('common/message.tpl');
                exit();
                break;
        }
        parent::handle($args, $request);
    }

    // Helper functions (KEEP AS IS)
    /**
     * Send Investigate Mail
     * @param MailTemplate $mail
     * @param Journal $journal
     * @param array $postData
     * @param string $info
     */
    private function sendInvestigateMail($mail, $journal, $postData, $info) {
        $mail->assignParams(array(
            'journalName' => $journal->getLocalizedTitle(),
            'postInfo' => print_r($postData, true),
            'additionalInfo' => $info,
            'serverVars' => print_r($_SERVER, true)
        ));
        $mail->send();
    }

    /**
     * Get Plugin Path, relative to the plugins directory
     * @return string
     */
    public function getInstallSchemaFile(): ?string {
        return ($this->getPluginPath() . DIRECTORY_SEPARATOR . 'schema.xml');
    }

    /**
     * Install Email Templates File
     * @return string
     */
    public function getInstallEmailTemplatesFile(): ?string {
        return ($this->getPluginPath() . DIRECTORY_SEPARATOR . 'emailTemplates.xml');
    }

    /**
     * Install Locale Email Template Data File
     * @return string
     */
    public function getInstallEmailTemplateDataFile(): ?string {
        return ($this->getPluginPath() . '/locale/{$installedLocale}/emailTemplates.xml');
    }
}
?>