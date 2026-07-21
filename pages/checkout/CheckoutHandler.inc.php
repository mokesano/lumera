<?php
declare(strict_types=1);

/**
 * @file pages/checkout/CheckoutHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * 
 * @class CheckoutHandler
 * @brief Controller antarmuka untuk 3-Tahap Checkout (Cart -> Billing -> Payment/Finalize).
 */

import('classes.handler.Handler');
import('lib.wizdam.classes.services.CheckoutService');
import('lib.pkp.classes.validation.ValidatorCSRF'); // Pindahkan ke atas untuk scope global class

class CheckoutHandler extends Handler {
    
    /** @var CheckoutService */
    private CheckoutService $checkoutService;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->addCheck(new HandlerValidatorCustom($this, true, null, null, function() {
            return Validation::isLoggedIn();
        }));
        $this->checkoutService = new CheckoutService();
    }

    /**
     * Setup template dan load komponen bahasa yang diperlukan untuk semua langkah checkout.
     * @param Request|null $request
     */
    public function setupTemplate($request = null): void {
        parent::setupTemplate($request);
        AppLocale::requireComponents([
            LOCALE_COMPONENT_CORE_COMMON,
            LOCALE_COMPONENT_CORE_USER,
            LOCALE_COMPONENT_APPLICATION_COMMON,
            LOCALE_COMPONENT_APP_PAYMENT
        ]);
    }

    // =========================================================================
    // TAHAP 1: CART & OPTIONS
    // =========================================================================
    
    /**
     * Tampilkan halaman cart dan opsi checkout.
     * @param array $args
     * @param Request|null $request
     */
    public function cart(array $args = [], $request = null): void {
        $this->validate();
        $this->setupTemplate($request);
        if (!$request) $request = Application::get()->getRequest();

        $articleId = isset($args[0]) ? (int) $args[0] : 0;
        $feeType = $request->getUserVar('feeType') ?: 'PUBLICATION'; 
        $baseAmount = (float) ($request->getUserVar('amount') ?: 0); 
        
        $user = $request->getUser();
        if (!$user) {
            $request->redirect(null, 'login');
            return; 
        }

        $context = $request->getContext(); 
        $contextId = $context ? (int) $context->getId() : 0; 
        
        $defaultCurrency = Config::getVar('general', 'default_currency', 'USD');
        $currency = $context ? ($context->getSetting('currency') ?: $defaultCurrency) : $defaultCurrency;

        $optionalFeeConfigs = [
            'FAST_TRACK' => [
                'setting_key' => 'fastTrackFee',
                'locale_name' => 'manager.payment.options.fastTrackFee',
                'locale_desc' => 'manager.payment.options.fastTrackFeeDescription'
            ],
            'COLOR_FIGURE' => [
                'setting_key' => 'colorFigureFee',
                'locale_name' => 'manager.payment.options.colorFigureFee',
                'locale_desc' => 'manager.payment.options.colorFigureFeeDescription'
            ],
            'PAGE_CHARGE' => [
                'setting_key' => 'pageChargeFee',
                'locale_name' => 'manager.payment.options.pageChargeFee',
                'locale_desc' => 'manager.payment.options.pageChargeFeeDescription'
            ]
        ];

        $optionalFees = [];
        if ($context) {
            foreach ($optionalFeeConfigs as $type => $config) {
                $feeAmount = (float) $context->getSetting($config['setting_key']);
                if ($feeAmount > 0) {
                    $optionalFees[$type] = [
                        'amount' => $feeAmount,
                        'label'  => __($config['locale_name']),
                        'desc'   => __($config['locale_desc'])
                    ];
                }
            }
        }

        $queuedPaymentId = $this->checkoutService->initCart(
            $contextId, (int) $user->getId(), $articleId, $feeType, $baseAmount, $currency
        );

        $summary = $this->checkoutService->calculateCartSummary($queuedPaymentId);
        $fastTrackPrice = $optionalFees['FAST_TRACK']['amount'] ?? 0;

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'queuedPaymentId' => $queuedPaymentId,
            'articleId'       => $articleId,
            'summary'         => $summary,
            'optionalFees'    => $optionalFees,
            'fastTrackPrice'  => $fastTrackPrice,
            'pageTitle'       => 'checkout.cart.title',
            'currentStep'     => 1
        ]);

        $templateMgr->display('checkout/cart.tpl');
    }

    /**
     * Endpoint AJAX untuk mengupdate keranjang (Centang Fast-Track, Kupon, dll)
     * Rute: POST /checkout/updateCartAjax
     * @param array $args
     * @param Request|null $request
     */
    public function updateCartAjax(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        
        // 1. Validasi Mutlak: Lempar ke login jika belum ada sesi
        if (!Validation::isLoggedIn()) {
            Validation::redirectLogin();
            return;
        }

        $queuedPaymentId = (int) $request->getUserVar('queuedPaymentId');
        $additionalItemsRaw = $request->getUserVar('additionalItems'); 
        $promoCode = trim((string) $request->getUserVar('promoCode'));
        
        $additionalItems = is_array($additionalItemsRaw) ? $additionalItemsRaw : json_decode($additionalItemsRaw ?: '[]', true);

        // [CRITICAL FIX] Jangan pernah percaya discountAmount dari parameter frontend!
        $discountAmount = 0.0;
        if (!empty($promoCode)) {
            // TODO: Integrasi validasi DB riil (Sama seperti logika di checkout() atas)
            if (strtoupper($promoCode) === 'SANGIA50') {
                $discountAmount = 50000.0;
            } else {
                $promoCode = null; // Batalkan promo jika tidak valid di DB
            }
        }

        $success = $this->checkoutService->updateCartItems($queuedPaymentId, $additionalItems, $promoCode, $discountAmount);
        
        header('Content-Type: application/json');
        if ($success) {
            $newSummary = $this->checkoutService->calculateCartSummary($queuedPaymentId);
            echo json_encode(['status' => 'success', 'summary' => $newSummary]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Cart not found']);
        }
        exit;
    }

    /**
     * Proses submit checkout.
     * @param array $args
     * @param Request|null $request
     * @return void
     */
    public function checkoutSubmit(array $args = [], $request = null): void {
        if (!$request) $request = Application::get()->getRequest();
        $articleId = isset($args[0]) ? (int) $args[0] : 0;
        
        if (!$request->isPost()) {
            if ($articleId) $request->redirect(null, 'checkout', 'cart', [$articleId]);
            else $request->redirect(null, 'index');
            return;
        }

        $submitArgs = $articleId > 0 ? [$articleId] : [];

        if (!Validation::isLoggedIn()) {
            Validation::redirectLogin();
            return;
        }

        $user = $request->getUser();
        if (!$articleId) {
            $request->redirect(null, 'index');
            return;
        }
        
        $journal = $request->getJournal(); 
        $journalId = $journal ? (int) $journal->getId() : 0;
        
        $currency = $journal ? $journal->getSetting('currency') : null;
        if (!$currency) {
            $currency = Config::getVar('general', 'default_currency', 'IDR'); 
        }

        $feeType = (string) ($request->getUserVar('feeType') ?: 'PUBLICATION');
        $amount = (float) $request->getUserVar('amount');
        $promoCode = trim((string) $request->getUserVar('promoCode'));
        
        $queuedPaymentId = $this->checkoutService->initCart(
            $journalId, (int) $user->getId(), $articleId, $feeType, $amount, $currency
        );

        $isFastTrack = (int) $request->getUserVar('fastTrack');
        $validatedItems = [];
        
        if ($isFastTrack === 1 && $journal) {
            $fastTrackFee = (float) $journal->getSetting('fastTrackFee');
            if ($fastTrackFee > 0) {
                $validatedItems[] = ['type' => 'FAST_TRACK', 'amount' => $fastTrackFee];
            }
        }

        $this->checkoutService->updateCartItems($queuedPaymentId, $validatedItems, $promoCode, 0.0);

        $request->redirect(null, 'checkout', 'billing', [$queuedPaymentId]);
    }

    // =========================================================================
    // TAHAP 2: BILLING ADDRESS
    // =========================================================================
    
    /**
     * Tampilkan form billing address dan proses submit-nya.
     * @param array $args
     * @param Request|null $request
     */
    public function billing(array $args = [], $request = null): void {
        $this->validate();
        $this->setupTemplate($request);
        if (!$request) $request = Application::get()->getRequest();
        
        if (!Validation::isLoggedIn()) {
            Validation::redirectLogin();
            return;
        }
        
        $user = $request->getUser();
        $queuedPaymentId = isset($args[0]) ? (int) $args[0] : 0;
        if ($queuedPaymentId === 0) {
            $request->redirect(null, 'index');
            return;
        }

        // ↓ FIX: Tangani POST dari billing.tpl
        if ($request->isPost()) {
            $billingData = [
                'name'        => $user->getFullName(),
                'institution' => $user->getAffiliation(AppLocale::getLocale()) ?: '',
                'country'     => $request->getUserVar('billingCountry'),
                'city'        => $request->getUserVar('billingCity'),
                'postal_code' => $request->getUserVar('billingPostalCode'),
                'address'     => $request->getUserVar('billingStreetAddress'),
            ];
            $this->checkoutService->updateBillingAddress($queuedPaymentId, $billingData);
            
            // PRG: redirect ke payment() via GET
            $request->redirect(null, 'checkout', 'payment', [$queuedPaymentId]);
            return;
        }

        // GET: Pure view
        $summary = $this->checkoutService->calculateCartSummary($queuedPaymentId);
        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries() ?: [];

        $defaultAddress = [
            'country' => $user->getCountry() ?: '',
            'city'    => '', 
            'postal_code' => '',
            'address' => $user->getMailingAddress() ?: ''
        ];

        // Jika user kembali dari Step berikutnya, gunakan data yang sudah diketik
        $existingBilling = $summary['billing_address'] ?? [];
        if (!empty($existingBilling['country'])) {
            $defaultAddress = array_merge($defaultAddress, $existingBilling);
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'currentStep'     => 2,
            'pageTitle'       => 'checkout.billing.title',
            'queuedPaymentId' => $queuedPaymentId,
            'summary'         => $summary,
            'userEmail'       => (string) $user->getEmail(),
            'userFullName'    => (string) $user->getFullName(),
            'defaultAddress'  => $defaultAddress,
            'countries'       => $countries
        ]);

        $templateMgr->display('checkout/billing.tpl');
    }

    // =========================================================================
    // TAHAP 3: PAYMENT REVIEW & FINALIZE
    // =========================================================================
    
    /**
     * Tampilkan ringkasan pembayaran dan tombol finalize.
     * @param array $args
     * @param Request|null $request
     */
    public function payment(array $args = [], $request = null): void {
        $this->validate();
        $this->setupTemplate($request);
        if (!$request) $request = Application::get()->getRequest();

        if (!Validation::isLoggedIn()) {
            Validation::redirectLogin();
            return;
        }

        $queuedPaymentId = isset($args[0]) ? (int) $args[0] : 0;
        if ($queuedPaymentId === 0) {
            error_log("[WIZDAM DEBUG] GAGAL MASUK STEP 3: queuedPaymentId adalah 0. queuedPaymentId Artinya ID tidak terkirim dari form billing.tpl!");
            $request->redirect(null, 'checkout'); // Mental ke awal
            return;
        }

        if ($request->isPost()) {
            $this->finalize($args, $request);
            return;
        }

        // Render Antarmuka Payment (Step 3)
        $summary = $this->checkoutService->calculateCartSummary($queuedPaymentId);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'queuedPaymentId' => $queuedPaymentId,
            'summary'         => $summary,
            'pageTitle'       => 'checkout.payment.title',
            'currentStep'     => 3
        ]);

        $templateMgr->display('checkout/payment.tpl');
    }

    /**
     * Proses finalize checkout, buat invoice, dan redirect ke halaman invoice.
     * @param array $args
     * @param Request|null $request
     */
    public function finalize(array $args = [], $request = null): void {
        // $this->validate(); // TIDAK dipanggil di sini karena sudah dijalankan oleh payment()
        if (!$request) $request = Application::get()->getRequest();

        $queuedPaymentId = isset($args[0]) ? (int) $args[0] : 0;

        // [WIZDAM] Kunci Pintu Terakhir!
        if (!$request->isPost()) {
            $request->redirect(null, 'checkout', 'payment', [$queuedPaymentId]);
            return;
        }

        try {
            $invoice = $this->checkoutService->finalizeCheckout($queuedPaymentId);
            
            import('lib.wizdam.classes.security.SecurityHashService');
            $hashService = new SecurityHashService();
            
            $invoiceId = $invoice->getInvoiceId();
            $authHash = $hashService->generateHash('invoice', $invoiceId);
            
            $request->redirect(null, 'billing', 'invoice', ["{$authHash}-{$invoiceId}"]);

        } catch (\Throwable $e) {
            $request->redirect(null, 'checkout', 'cart');
        }
    }
    
}
?>