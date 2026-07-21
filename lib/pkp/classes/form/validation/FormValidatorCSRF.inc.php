<?php
declare(strict_types=1);

/**
 * @file lib/pkp/classes/form/validation/FormValidatorCSRF.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 *
 * [WIZDAM EDITION]
 * @class FormValidatorCSRF
 * 
 * @brief Form validation check untuk CSRF Token yang memicu Notifikasi Global.
 */

import('lib.pkp.classes.form.validation.FormValidator');
import('lib.pkp.classes.validation.ValidatorCSRF');
import('classes.notification.NotificationManager');

class FormValidatorCSRF extends FormValidator {

    /**
     * Constructor.
     * 
     * @param Form $form Objek form yang sedang divalidasi
     */
    public function __construct($form) {
        parent::__construct($form, ValidatorCSRF::FIELD_NAME, 'required', '');
    }

    /**
     * Lakukan validasi eksekusi
     * @return bool
     */
    public function isValid(): bool {
        $request = Application::get()->getRequest();
        $clientToken = $request->getUserVar(ValidatorCSRF::FIELD_NAME);

        // Tentukan context secara aman
        $context = 'global';
        $router = $request->getRouter();
        if (is_a($router, 'PKPPageRouter') || is_a($router, 'PKPComponentRouter')) {
            $context = (string) $router->getRequestedOp($request);
        }

        // 1. Cek Validitas Token
        $isTokenValid = ValidatorCSRF::checkToken($clientToken, $context, [], false);

        // 2. Jika Tidak Valid, Kirim Notifikasi Global
        if (!$isTokenValid) {
            $notificationManager = new NotificationManager();
            $user = $request->getUser();
            $userId = $user ? $user->getId() : 0;

            $notificationManager->createTrivialNotification(
                $userId,
                NOTIFICATION_TYPE_ERROR,
                ['contents' => __('notification.csrfInvalid')]
            );
        }

        // 3. Kembalikan false agar form TIDAK di-execute (disimpan)
        return $isTokenValid;
    }
    
}
?>