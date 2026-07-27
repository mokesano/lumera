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
 * @brief PayPal paymethod plugin class.
 */

import('classes.plugins.PaymethodPlugin');

class PayPalPlugin extends PaymethodPlugin {

    /**
     * Constructor.
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
     * Plugin Description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.paymethod.paypal.description');
    }

    /**
     * Register the plugin.
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
     * Get the names of form fields for settings.
     * Settings yang disimpan di Database.
     * @return array
     */
    public function getSettingsFormFieldNames(): array {
        return ['clientId', 'testMode', 'paypalurl'];
    }

    /**
     * Cek apakah sudah dikonfigurasi.
     * Kita return TRUE agar PaymentManager tidak memblokir halaman (WSOD).
     * Validasi sesungguhnya kita lakukan di displayPaymentForm.
     * @return bool
     */
    public function isConfigured(): bool {
        return true; 
    }

    /**
     * TAMPILAN SETTINGS (BACKEND).
     * Menggunakan struktur <tr> agar pas dengan tabel OJS.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    public function displayPaymentSettingsForm($params, $smarty) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $journalId = $journal ? (int) $journal->getId() : 0;

        $smarty->assign([
            'clientId' => $this->getSetting($journalId, 'clientId'),
            'testMode' => $this->getSetting($journalId, 'testMode')
        ]);
        
        return $smarty->fetch($this->getTemplatePath() . 'settingsForm.tpl');
    }

    /**
     * TAMPILAN PEMBAYARAN (FRONTEND).
     * [MODERNISASI] Menggunakan API v2 + Anti-WSOD.
     * @param int $queuedPaymentId
     * @param OJSQueuedPayment $queuedPayment
     * @param PKPRequest $request
     * @return string
     */
    public function displayPaymentForm(int $queuedPaymentId, $queuedPayment, $request) {
        // [WIZDAM FIX] Explicitly declare type for linter to resolve getRequestUrl() and getType()
        /** @var OJSQueuedPayment $queuedPayment */
        
        $templateMgr = TemplateManager::getManager();
        $journal = $request->getJournal();
        
        AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
        
        // --- 1. REAL VALIDATION HAPPENS HERE ---
        $journalId = $journal ? (int) $journal->getId() : 0;
        $clientId = (string) $this->getSetting($journalId, 'clientId');

        if ($clientId === '') {
            $templateMgr->assign('paypalError', true);
            $templateMgr->assign('message', __('plugins.paymethod.paypal.error.missingClientId'));
            
            return $templateMgr->fetch($this->getTemplatePath() . 'settingsForm.tpl');
        }

        // --- 2. JIKA CONFIG AMAN ---
        $rawAmount = (float) $queuedPayment->getAmount();
        
        $locale = AppLocale::getLocale(); 
        $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
        $displayAmount = $formatter->format($rawAmount);

        $params = [
            'clientId'      => $clientId,
            'testMode'      => (bool) $this->getSetting($journalId, 'testMode'),
            'currency'      => (string) $queuedPayment->getCurrencyCode(),
            'amount'        => sprintf('%.2f', $rawAmount),
            'displayAmount' => $displayAmount,
            'returnUrl'     => (string) $queuedPayment->getRequestUrl(), // Linter now recognizes this
            'itemName'      => (string) $queuedPayment->getName(),
            'itemDesc'      => (string) $queuedPayment->getDescription()
        ];

        // --- 3. [WIZDAM UX] BACA DATA INVOICE DARI DATABASE (INTEGRASI MANAGER) ---
        $articleId = (int) $queuedPayment->getAssocId();
        
        if ($articleId > 0 && $journal) {
            /** @var ArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle($articleId);
            
            if ($article) {
                $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
                $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);

                $finalAmount = (float) $queuedPayment->getAmount();

                /** @var SiteDAO $siteDao */
                $siteDao = DAORegistry::getDAO('SiteDAO');
                $site = $siteDao->getSite();
                $siteTitle = $site ? (string) $site->getLocalizedTitle() : '';

                /** @var AuthorDAO $authorDao */
                $authorDao = DAORegistry::getDAO('AuthorDAO');
                $authors = $authorDao->getAuthorsBySubmissionId($articleId);
                
                $correspondingAuthor = null;
                if (is_array($authors)) {
                    foreach ($authors as $author) {
                        if ($author->getPrimaryContact()) {
                            $correspondingAuthor = $author;
                            break;
                        }
                    }
                }

                if (!$correspondingAuthor && is_array($authors) && !empty($authors)) {
                    $correspondingAuthor = $authors[0];
                }

                /** @var UserDAO $userDao */
                $userDao = DAORegistry::getDAO('UserDAO');
                $submitter = $userDao->getById((int) $queuedPayment->getUserId());

                if ($correspondingAuthor) {
                    $billedName = (string) $correspondingAuthor->getFullName();
                    $billedEmail = (string) $correspondingAuthor->getEmail();
                    $rawAffiliation = (string) $correspondingAuthor->getLocalizedAffiliation();
                    $countryCode = (string) $correspondingAuthor->getCountry();
                } elseif ($submitter) {
                    $billedName = (string) $submitter->getFullName();
                    $billedEmail = (string) $submitter->getEmail();
                    $rawAffiliation = (string) $submitter->getLocalizedAffiliation();
                    $countryCode = (string) $submitter->getCountry();
                } else {
                    $billedName = '';
                    $billedEmail = '';
                    $rawAffiliation = '';
                    $countryCode = '';
                }

                $affiliationList = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $rawAffiliation))));
                
                if ($countryCode !== '') {
                    /** @var CountryDAO $countryDao */
                    $countryDao = DAORegistry::getDAO('CountryDAO');
                    $countryObj = $countryDao->getCountry($countryCode);
                    if ($countryObj) {
                        $countryName = is_object($countryObj) ? (string) $countryObj->getCountry() : (string) $countryObj;
                        if (empty($affiliationList)) {
                            $affiliationList[] = $countryName;
                        } else {
                            $lastIndex = count($affiliationList) - 1;
                            $affiliationList[$lastIndex] .= ', ' . $countryName;
                        }
                    }
                }

                $dateBilled = date('d F Y');
                $dateDue = date('d F Y', strtotime('+7 days'));

                $settingTaxRate = (float) $journal->getSetting('paymentTax');
                $taxRate = $settingTaxRate > 0 ? ($settingTaxRate / 100) : 0.00;
                $isTaxInclusive = (bool) $journal->getSetting('paymentTaxInclusive');
                $discount = (float) $journal->getSetting('paymentDiscount');
                
                if ($isTaxInclusive) {
                    $amountAfterDiscount = $finalAmount;
                    $baseForVat = $amountAfterDiscount / (1 + $taxRate);
                    $tax = $amountAfterDiscount - $baseForVat;
                } else {
                    $amountAfterDiscount = $finalAmount / (1 + $taxRate);
                    $tax = $amountAfterDiscount * $taxRate;
                }
                
                $originalFee = $amountAfterDiscount + $discount;
                $subtotal = $amountAfterDiscount;
                
                $feeSubmission  = 0.00;
                $feeFastTrack   = 0.00;
                $feePublication = 0.00;
                
                // Linter now recognizes getType() because of the @var annotation above
                $paymentType = $queuedPayment->getType();
                
                if ((int) $paymentType === 5 || $paymentType === 'PAYMENT_TYPE_SUBMISSION') {
                    $feeSubmission = $originalFee;
                } elseif ((int) $paymentType === 6 || $paymentType === 'PAYMENT_TYPE_FASTTRACK') {
                    $feeFastTrack = $originalFee;
                } elseif ((int) $paymentType === 7 || $paymentType === 'PAYMENT_TYPE_PUBLICATION') {
                    $feePublication = $originalFee;
                }

                $invoiceNumber = '';
                $invoiceCode   = '';
                
                $result = $articleDao->retrieve(
                    "SELECT setting_name, setting_value FROM article_settings WHERE article_id = ? AND setting_name IN ('wizdam_invoice_number', 'wizdam_invoice_code')", 
                    [$articleId]
                );
                
                if ($result) {
                    while (!$result->EOF) {
                        $row = $result->GetRowAssoc(false);
                        if ($row['setting_name'] === 'wizdam_invoice_number') {
                            $invoiceNumber = (string) $row['setting_value'];
                        }
                        if ($row['setting_name'] === 'wizdam_invoice_code') {
                            $invoiceCode = (string) $row['setting_value'];
                        }
                        $result->MoveNext();
                    }
                    $result->Close();
                }

                if ($invoiceNumber === '') {
                    $invoiceNumber = 'INVOICE-PENDING-' . $articleId;
                }
                if ($invoiceCode === '') {
                    $invoiceCode = 'Manuscript#' . str_pad((string) $articleId, 7, '0', STR_PAD_LEFT);
                }

                $templateMgr->assign('invoiceSiteTitle', $siteTitle);
                $templateMgr->assign('invoiceBilledName', $billedName);
                $templateMgr->assign('invoiceBilledAffiliations', $affiliationList);
                $templateMgr->assign('invoiceBilledEmail', $billedEmail);
                $templateMgr->assign('invoiceDateBilled', $dateBilled);
                $templateMgr->assign('invoiceDateDue', $dateDue);

                $templateMgr->assign('invoiceArticleId', $articleId);
                $templateMgr->assign('invoiceArticleTitle', (string) $article->getLocalizedTitle());
                $templateMgr->assign('invoiceAuthors', (string) $article->getAuthorString());
                $templateMgr->assign('invoiceNumber', $invoiceNumber);
                $templateMgr->assign('invoiceCode', $invoiceCode);
                
                $templateMgr->assign('feeSubmission', $formatter->format($feeSubmission));
                $templateMgr->assign('feeFastTrack', $formatter->format($feeFastTrack));
                $templateMgr->assign('feePublication', $formatter->format($feePublication));
                $templateMgr->assign('subtotal', $formatter->format($subtotal));
                $templateMgr->assign('discount', $formatter->format($discount));
                
                $templateMgr->assign('taxRateLabel', (string) $settingTaxRate);
                $templateMgr->assign('isTaxInclusive', $isTaxInclusive);
                $templateMgr->assign('tax', $formatter->format($tax));
                $templateMgr->assign('finalAmount', $formatter->format($finalAmount));
            }
        }

        $templateMgr->assign('paypalParams', $params);
        $templateMgr->assign('paypalError', false);
        
        return $templateMgr->display($this->getTemplatePath() . 'paymentForm.tpl');
    }

    /**
     * Handle requests.
     * @see PKPPlugin::handle()
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function handle($args, $request) {
        $templateMgr = TemplateManager::getManager();
        $journal = $request->getJournal();
        
        if (!$journal) {
            return parent::handle($args, $request);
        }

        import('classes.mail.MailTemplate');
        
        $contactName = (string) ($journal->getSetting('supportName') ?: $journal->getSetting('contactName'));
        $contactEmail = (string) ($journal->getSetting('supportEmail') ?: $journal->getSetting('contactEmail'));

        $mail = new MailTemplate('PAYPAL_INVESTIGATE_PAYMENT');
        $mail->setFrom($contactEmail, $contactName);
        $mail->addRecipient($contactEmail, $contactName);

        $paymentStatus = (string) $request->getUserVar('payment_status');

        switch (array_shift($args)) {
            case 'ipn':
                $req = 'cmd=_notify-validate';
                foreach ($_POST as $key => $value) {
                    $req .= '&' . urlencode((string) $key) . '=' . urlencode((string) $value);    
                }
                
                $ch = curl_init();
                $httpProxyHost = Config::getVar('proxy', 'http_host');
                if ($httpProxyHost) {
                    curl_setopt($ch, CURLOPT_PROXY, $httpProxyHost);
                    curl_setopt($ch, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
                    $username = Config::getVar('proxy', 'username');
                    if ($username) {
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $username . ':' . Config::getVar('proxy', 'password'));
                    }
                }
                
                $paypalUrl = (string) $this->getSetting((int) $journal->getId(), 'paypalurl');
                if ($paypalUrl === '') {
                    $paypalUrl = 'https://www.paypal.com/cgi-bin/webscr';
                }

                curl_setopt($ch, CURLOPT_URL, $paypalUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: PKP PayPal Service', 'Content-Type: application/x-www-form-urlencoded', 'Content-Length: ' . strlen($req)]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
                $ret = curl_exec($ch);
                $curlError = curl_error($ch);
                curl_close($ch);

                if (is_string($ret) && strcmp($ret, 'VERIFIED') === 0) {
                    switch ($paymentStatus) {
                        case 'Completed':
                            /** @var PayPalDAO $payPalDao */
                            $payPalDao = DAORegistry::getDAO('PayPalDAO');
                            $transactionId = (string) $request->getUserVar('txn_id');
                            
                            if ($payPalDao->transactionExists($transactionId)) {
                                $this->sendInvestigateMail($mail, $journal, $_POST, "Duplicate transaction ID: $transactionId");
                                exit();
                            } 
                            
                            $payPalDao->insertTransaction(
                                $transactionId,
                                (string) $request->getUserVar('txn_type'),
                                PKPString::strtolower((string) $request->getUserVar('payer_email')),
                                PKPString::strtolower((string) $request->getUserVar('receiver_email')),
                                (string) $request->getUserVar('item_number'),
                                (string) $request->getUserVar('payment_date'),
                                (string) $request->getUserVar('payer_id'),
                                (string) $request->getUserVar('receiver_id')
                            );

                            $queuedPaymentId = (int) $request->getUserVar('custom');
                            import('classes.payment.ojs.OJSPaymentManager');
                            $ojsPaymentManager = new OJSPaymentManager($request);
                            $queuedPayment = $ojsPaymentManager->getQueuedPayment($queuedPaymentId);

                            if (!$queuedPayment) {
                                $this->sendInvestigateMail($mail, $journal, $_POST, "Missing queued payment ID: $queuedPaymentId");
                                exit();
                            }

                            $grantedAmount = (float) $request->getUserVar('mc_gross');
                            $queuedAmount = (float) $queuedPayment->getAmount();
                            $grantedCurrency = (string) $request->getUserVar('mc_currency');
                            $grantedEmail = PKPString::strtolower((string) $request->getUserVar('receiver_email'));
                            $queuedEmail = PKPString::strtolower((string) $this->getSetting((int) $journal->getId(), 'selleraccount'));

                            if ($queuedAmount === 0.0 && $grantedAmount > 0.0) {
                                /** @var QueuedPaymentDAO $queuedPaymentDao */
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
                AppLocale::requireComponents(LOCALE_COMPONENT_CORE_COMMON, LOCALE_COMPONENT_CORE_USER, LOCALE_COMPONENT_APPLICATION_COMMON);
                $templateMgr->assign([
                    'currentUrl' => $request->url(null, 'index'),
                    'pageTitle' => 'plugins.paymethod.paypal.purchase.cancelled.title',
                    'message' => 'plugins.paymethod.paypal.purchase.cancelled',
                    'backLink' => (string) $request->getUserVar('ojsReturnUrl'),
                    'backLinkLabel' => 'common.continue'
                ]);
                $templateMgr->display('common/message.tpl');
                exit();
        }
        parent::handle($args, $request);
    }

    /**
     * Send Investigate Mail.
     * @param MailTemplate $mail
     * @param Journal $journal
     * @param array $postData
     * @param string $info
     */
    private function sendInvestigateMail($mail, $journal, $postData, $info) {
        $mail->assignParams([
            'journalName' => (string) $journal->getLocalizedTitle(),
            'postInfo' => print_r($postData, true),
            'additionalInfo' => $info,
            'serverVars' => print_r($_SERVER, true)
        ]);
        $mail->send();
    }

    /**
     * Get Plugin Path, relative to the plugins directory.
     * @return string|null
     */
    public function getInstallSchemaFile(): ?string {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'schema.xml';
    }

    /**
     * Install Email Templates File.
     * @return string|null
     */
    public function getInstallEmailTemplatesFile(): ?string {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'emailTemplates.xml';
    }

    /**
     * Install Locale Email Template Data File.
     * @return string|null
     */
    public function getInstallEmailTemplateDataFile(): ?string {
        return $this->getPluginPath() . '/locale/{$installedLocale}/emailTemplates.xml';
    }
    
}
?>