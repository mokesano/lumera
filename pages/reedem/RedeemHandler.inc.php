<?php
declare(strict_types=1);

/**
 * @file pages/redeem/RedeemHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance, DDD, and i18n
 * @class RedeemHandler
 * @ingroup pages_redeem
 *
 * @brief Menangani domain Loyalti (Dompet Virtual). Tempat Editor/Reviewer 
 * melihat saldo "Recognition Points" dan riwayat mutasinya.
 */

import('classes.handler.Handler');
import('lib.wizdam.classes.checkout.services.RedeemService');

class RedeemHandler extends Handler {
    
    private RedeemService $redeemService;

    public function __construct() {
        parent::__construct();
        
        $this->addCheck(new HandlerValidatorCustom($this, true, null, null, function() {
            return Validation::isLoggedIn();
        }));

        $this->redeemService = new RedeemService();
    }

    public function setupTemplate($request = null): void {
        parent::setupTemplate($request);
        AppLocale::requireComponents(
            LOCALE_COMPONENT_APP_COMMON, 
            LOCALE_COMPONENT_CORE_USER
        );
    }

    /**
     * Menampilkan Dasbor Dompet Virtual (Saldo & Riwayat Mutasi).
     * Rute: GET /redeem
     */
    public function index(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();
        
        $this->setupTemplate($request);
        $user = $request->getUser();

        // Mengambil data dari layanan loyalti
        $balance = $this->redeemService->getUserBalance((int) $user->getId());
        $history = $this->redeemService->getUserHistory((int) $user->getId());

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'pointBalance' => $balance,
            'pointHistory' => $history,
            'pageTitle' => 'redeem.walletDashboard',
            'pageHierarchy' => [
                [$request->url(null, 'user'), 'navigation.user']
            ]
        ]);

        $templateMgr->display('redeem/index.tpl');
    }

    /**
     * Memproses permintaan penukaran poin (POST).
     * Rute: POST /redeem/exchange
     */
    public function exchange(array $args = [], $request = null): void {
        $this->validate();
        if (!$request) $request = Application::get()->getRequest();

        if (!$request->isPost()) {
            $this->_redirectWithError($request, 'redeem.error.invalidMethod');
        }

        import('lib.pkp.classes.validation.ValidatorCSRF');
        if (!ValidatorCSRF::checkToken($request->getUserVar('csrfToken'))) {
            $this->_redirectWithError($request, 'redeem.error.csrfInvalid');
        }

        $user = $request->getUser();
        $pointsToRedeem = (int) $request->getUserVar('points_to_redeem');

        if ($pointsToRedeem <= 0) {
            $this->_redirectWithError($request, 'redeem.error.invalidPointAmount');
        }

        try {
            // Eksekusi penukaran di Service
            $this->redeemService->exchangePoints((int) $user->getId(), $pointsToRedeem);

            // Jika berhasil, beri notifikasi trivial sukses
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $notificationManager->createTrivialNotification(
                $user->getId(), 
                NOTIFICATION_TYPE_SUCCESS, 
                ['contents' => __('redeem.success.pointsExchanged')]
            );

            $request->redirect(null, 'redeem', 'index');

        } catch (\Exception $e) {
            // Menangkap exception dari service (misal: "Insufficient balance")
            // Tanpa mengekspos pesan teknis langsung ke user, gunakan locale key
            if ($e->getMessage() === 'Insufficient balance.') {
                $this->_redirectWithError($request, 'redeem.error.insufficientPoints');
            } else {
                $this->_redirectWithError($request, 'redeem.error.exchangeFailed');
            }
        }
    }

    /**
     * HELPER: Mengalihkan pengguna kembali ke dasbor dompet dengan Notifikasi Error.
     */
    private function _redirectWithError($request, string $localeKey): void {
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
        
        $request->redirect(null, 'redeem', 'index');
        exit;
    }
}
?>