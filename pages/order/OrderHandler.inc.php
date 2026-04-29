<?php
declare(strict_types=1);

/**
 * @file pages/order/OrderHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance, DDD, and i18n
 * @class OrderHandler
 * @ingroup pages_order
 *
 * @brief Menangani domain B2C publik. Tempat pengguna melihat keranjang (Cart) 
 * dan melakukan Checkout untuk men-generate Invoice resmi ke domain Billing.
 */

import('classes.handler.Handler');

// Mengimpor Service Layer Wizdam Frontedge
import('lib.wizdam.classes.checkout.services.CartService');
import('lib.wizdam.classes.checkout.services.InvoiceService');
import('lib.wizdam.classes.security.SecurityHashService');

class OrderHandler extends Handler {
    
    private CartService $cartService;
    private InvoiceService $invoiceService;
    private SecurityHashService $securityHashService;

    /**
     * Constructor OrderHandler
     */
    public function __construct() {
        parent::__construct();
        
        // Memastikan hanya pengguna yang login yang bisa memiliki keranjang/checkout
        $this->addCheck(new HandlerValidatorCustom($this, true, null, null, function() {
            return Validation::isLoggedIn();
        }));

        $this->cartService = new CartService();
        $this->invoiceService = new InvoiceService();
        $this->securityHashService = new SecurityHashService();
    }

    /**
     * Memuat dependensi antarmuka dan Locale
     */
    public function setupTemplate($request = null): void {
        parent::setupTemplate($request);
        // Komponen locale spesifik order (bisa disesuaikan)
        AppLocale::requireComponents(
            array(
                LOCALE_COMPONENT_CORE_COMMON, 
                LOCALE_COMPONENT_CORE_USER, 
                LOCALE_COMPONENT_APPLICATION_COMMON
            )
        );
    }

    /**
     * Menampilkan Antarmuka Keranjang Belanja.
     * Rute: GET /order/cart
     */
    public function cart(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        
        $this->setupTemplate($request);
        $user = $request->getUser();

        // Mengambil isi keranjang milik user (bisa dari session atau tabel temp DB)
        $cartItems = $this->cartService->getUserCart((int) $user->getId());
        $cartSummary = $this->cartService->calculateSummary($cartItems);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'cartItems' => $cartItems,
            'cartSummary' => $cartSummary, // Array berisi subtotal, pajak, total akhir
            'pageTitle' => 'order.shoppingCart',
            'pageHierarchy' => [
                [$request->url(null, 'user'), 'navigation.user']
            ]
        ]);

        $templateMgr->display('order/cart.tpl');
    }

    /**
     * Memproses keranjang menjadi Invoice resmi (Biasanya via form POST).
     * Rute: POST /order/checkout
     */
    public function checkout(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        
        // 1. Validasi Metode dan Keamanan
        if (!$request->isPost()) {
            $this->_redirectWithError($request, 'order.error.invalidMethod', 'cart');
        }

        import('lib.pkp.classes.validation.ValidatorCSRF');
        if (!ValidatorCSRF::checkToken($request->getUserVar('csrfToken'))) {
            $this->_redirectWithError($request, 'order.error.csrfInvalid', 'cart');
        }

        $user = $request->getUser();
        $cartItems = $this->cartService->getUserCart((int) $user->getId());

        // 2. Cegah Checkout jika keranjang kosong
        if (empty($cartItems)) {
            $this->_redirectWithError($request, 'order.error.emptyCart', 'cart');
        }

        try {
            // 3. Delegasikan pembuatan Invoice ke Service
            // Service ini akan melakukan INSERT ke tabel invoices dan invoice_items
            $invoice = $this->invoiceService->createInvoiceFromCart($user, $cartItems);
            
            // 4. Bersihkan keranjang setelah berhasil dikonversi
            $this->cartService->clearCart((int) $user->getId());

            // 5. TRANSAKSI LINTAS DOMAIN: 
            // Generate Security Hash untuk masuk ke domain Billing
            $hash = $this->securityHashService->generateHash('invoice', (int) $invoice->getId());

            // 6. Buat Notifikasi Sukses
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $notificationManager->createTrivialNotification(
                $user->getId(), 
                NOTIFICATION_TYPE_SUCCESS, 
                ['contents' => __('order.success.checkoutComplete')]
            );

            // 7. Lempar (Redirect) pengguna secara estafet ke Smart Router di BillingHandler
            $request->redirect(null, 'billing', 'invoice', ["{$hash}-{$invoice->getId()}"]);

        } catch (\Throwable $e) {
            // Tangkap kegagalan database atau logika service tanpa membunuh sistem
            error_log('Checkout Error: ' . $e->getMessage()); // Catat untuk debugging admin
            $this->_redirectWithError($request, 'order.error.checkoutFailed', 'cart');
        }
    }

    /**
     * HELPER: Mengalihkan pengguna kembali ke halaman keranjang dengan Notifikasi Error.
     * Menggantikan penggunaan die() demi UX yang sempurna.
     */
    private function _redirectWithError($request, string $localeKey, string $targetOp = 'cart'): void {
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();
        $user = $request->getUser();
        
        if ($user) {
            $notificationManager->createTrivialNotification(
                $user->getId(),
                NOTIFICATION_TYPE_ERROR,
                ['contents' => __($localeKey)]
            );
        }
        
        $request->redirect(null, 'order', $targetOp);
        exit;
    }
}
?>