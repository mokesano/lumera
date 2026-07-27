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
 * @brief Manual payment plugin class.
 */

import('classes.plugins.PaymethodPlugin');

class ManualPaymentPlugin extends PaymethodPlugin {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
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
     * @return string
     */
    public function getName(): string {
        return 'Manual';
    }

    /**
     * Get the display name of this plugin.
     * @see Plugin::getDisplayName
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.paymethod.manual.displayName');
    }

    /**
     * Get a description of the plugin.
     * @see Plugin::getDescription
     * @return string
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
     * @return bool
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
     * @param array $params
     * @param object $smarty
     * @return void
     */
    public function displayPaymentSettingsForm($params, $smarty) {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
    
        if ($context) {
            $smarty->assign('manualInstructions', $this->getSetting((int) $context->getId(), 'manualInstructions'));
        }
    
        $smarty->display($this->getTemplatePath() . 'settingsForm.tpl');
    }
    
    /**
     * Determine whether the plugin is configured.
     * @see PaymentPlugin::isConfigured
     * @return bool
     */
    public function isConfigured(): bool {
        return true; 
    }

    /**
     * Display the payment form.
     * @param int $queuedPaymentId
     * @param mixed $queuedPayment
     * @param mixed $request
     * @return bool
     */
    public function displayPaymentForm($queuedPaymentId, $queuedPayment, $request) {
        // [WIZDAM FIX] Explicitly declare type for linter to resolve getType() and other methods
        /** @var OJSQueuedPayment $queuedPayment */
        
        $context = $request->getContext();
        $templateMgr = TemplateManager::getManager($request); 
        
        AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);

        $contextId = $context ? (int) $context->getId() : 0;

        // --- 1. [FAILSAFE ANTI-WSOD] PENANGANAN INSTRUKSI ---
        $instructions = $this->getSetting($contextId, 'manualInstructions');
        $cleanInstructions = trim(strip_tags((string) $instructions));
        
        if ($cleanInstructions === '') {
             $templateMgr->assign('manualInstructions', '<div class="pkp_form_error" style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 4px;"><strong>Peringatan:</strong> Instruksi pembayaran manual belum diatur. Transaksi belum bisa dilanjutkan.</div>');
        } else {
             $templateMgr->assign('manualInstructions', (string) $instructions);
        }

        // --- 2. [CORE OJS] DATA ITEM DASAR ---
        $templateMgr->assign('itemName', (string) $queuedPayment->getName());
        $templateMgr->assign('itemDescription', (string) $queuedPayment->getDescription());
        
        if ((float) $queuedPayment->getAmount() > 0) {
            $templateMgr->assign('itemAmount', (float) $queuedPayment->getAmount());
            $templateMgr->assign('itemCurrencyCode', (string) $queuedPayment->getCurrencyCode());
        }

        // --- 3. [WIZDAM UX] BACA DATA INVOICE DARI DATABASE ---
        $articleId = (int) $queuedPayment->getAssocId();
        $journal = $request->getJournal();
        
        if ($articleId > 0 && $journal) {
            /** @var ArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle($articleId);
            
            if ($article) {
                $locale = AppLocale::getLocale();
                $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
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
                
                if ($result && !$result->EOF) {
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

        $templateMgr->assign('queuedPaymentId', (int) $queuedPaymentId);
        $templateMgr->display($this->getTemplatePath() . 'paymentForm.tpl');
        
        return true; 
    }

    /**
     * Handle incoming requests/notifications.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function handle($args, $request) {
        $context = $request->getContext();
        $templateMgr = TemplateManager::getManager($request);
        $user = $request->getUser();
        
        $op = $args[0] ?? null;
        $queuedPaymentId = (int) ($args[1] ?? 0);

        import('classes.payment.ojs.OJSPaymentManager');
        if (!class_exists('OJSPaymentManager')) {
            error_log("ManualPaymentPlugin: OJSPaymentManager class not found.");
            $request->redirect(null, 'index');
            return;
        }
        
        $ojsPaymentManager = new \OJSPaymentManager($request);
        $queuedPayment = $ojsPaymentManager->getQueuedPayment($queuedPaymentId);
        
        if (!$queuedPayment || !$user) {
            $request->redirect(null, 'index');
            return;
        }

        switch ($op) {
            case 'notify':
                import('classes.mail.MailTemplate');
                AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
                
                $journalName = $context ? (string) $context->getLocalizedName() : '';
                $contactName = $context ? (string) $context->getSetting('contactName') : '';
                $contactEmail = $context ? (string) $context->getSetting('contactEmail') : '';
                $supportEmail = $context ? ((string) $context->getSetting('supportEmail') ?: $contactEmail) : '';

                $locale = AppLocale::getLocale();
                $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
                $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
                $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
                $formattedAmount = $formatter->format((float) $queuedPayment->getAmount());

                import('lib.wizdam.classes.services.InvoiceService');
                import('lib.wizdam.classes.security.SecurityHashService');
                $invoiceService = new InvoiceService();
                $hashService = new SecurityHashService();

                $submissionId = (int) $queuedPayment->getAssocId();
                $invoices = $invoiceService->getInvoicesBySubmissionId($submissionId);
                if (is_array($invoices) && !empty($invoices)) {
                    $invoice = $invoices[0];
                    $invoiceId = (int) $invoice->getInvoiceId();
                    $displayPaymentId = (string) $invoice->getInvoiceNumber();
                    $authHash = $hashService->generateHash('invoice', $invoiceId);
                    $paymentStatusUrl = $request->url(null, 'billing', 'invoice', ["{$authHash}-{$invoiceId}"]);
                } else {
                    $displayPaymentId = (string) $queuedPaymentId;
                    $paymentStatusUrl = $request->url(null, 'billing');
                }

                $mailUser = new MailTemplate('USER_INVOICE_GENERATED'); 
                $mailUser->setFrom($contactEmail, $contactName);
                $mailUser->setReplyTo($supportEmail, $contactName);
                $mailUser->addRecipient((string) $user->getEmail(), (string) $user->getFullName());
                
                $mailUser->assignParams([
                    'userFullName'     => (string) $user->getFullName(),
                    'journalName'      => $journalName,
                    'paymentId'        => $displayPaymentId, 
                    'paymentReason'    => (string) $queuedPayment->getDescription() ?: __('common.none'),
                    'totalAmount'      => $formattedAmount,
                    'itemCurrencyCode' => (string) $queuedPayment->getCurrencyCode(),
                    'paymentStatusUrl' => $paymentStatusUrl,
                    'supportEmail'     => $supportEmail
                ]);
                $mailUser->send();

                $mailAdmin = new MailTemplate('MANUAL_PAYMENT_NOTIFICATION'); 
                $mailAdmin->setFrom($contactEmail, $contactName);
                $mailAdmin->addRecipient($contactEmail, $contactName);

                $mailAdmin->assignParams([
                    'paymentId'             => $displayPaymentId,
                    'priorityLevel'         => 'High',
                    'paymentRequestDate'    => date('Y-m-d H:i:s'),
                    'itemName'              => (string) $queuedPayment->getName() ?: __('common.none'),
                    'totalAmount'           => $formattedAmount,
                    'itemCurrencyCode'      => (string) $queuedPayment->getCurrencyCode(),
                    'journalName'           => $journalName,
                    'submissionId'          => (string) $submissionId,
                    'userFullName'          => (string) $user->getFullName(),
                    'userEmail'             => (string) $user->getEmail(),
                    'paymentStatusUrl'      => $paymentStatusUrl,
                    'notificationTimestamp' => date('Y-m-d H:i:s'),
                    'instanceUrl'           => (string) $request->getBaseUrl()
                ]);
                $mailAdmin->send();

                $templateMgr->assign([
                    'currentUrl'    => $request->url(null, null, 'payment', 'plugin', ['notify', $queuedPaymentId]),
                    'pageTitle'     => 'plugins.paymethod.manual.paymentNotification',
                    'message'       => 'plugins.paymethod.manual.notificationSent',
                    'backLink'      => $paymentStatusUrl, 
                    'backLinkLabel' => 'common.continue'
                ]);
                $templateMgr->display('common/message.tpl');
                exit();
        }
        
        parent::handle($args, $request); 
    }

    /**
     * Get the email templates installation file.
     * @see Plugin::getInstallEmailTemplatesFile
     * @return string|null
     */
    public function getInstallEmailTemplatesFile(): ?string {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'emailTemplates.xml';
    }

    /**
     * Get the email template data installation file.
     * @see Plugin::getInstallEmailTemplateDataFile
     * @return string|null
     */
    public function getInstallEmailTemplateDataFile(): ?string {
        return $this->getPluginPath() . '/locale/{$installedLocale}/emailTemplates.xml';
    }

}
?>