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
 * @brief Form validation check untuk CSRF Token. 
 * Memanggil ValidatorCSRF dari core validation.
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorCSRF extends FormValidator {

    /**
     * Constructor.
     * @param Form $form Objek form yang sedang divalidasi
     * @param string $message Kunci locale untuk pesan error (atau pesan statis)
     */
    public function __construct(&$form, $message = 'Terdapat kendala keamanan (CSRF Token tidak valid). Silakan muat ulang form.') {
        // Kita set field yang dicek adalah 'csrfToken', sifatnya 'required'
        parent::__construct($form, 'csrfToken', 'required', $message);
    }

    /**
     * Lakukan validasi eksekusi
     * @return bool
     */
    public function isValid(): bool {
        import('lib.pkp.classes.validation.ValidatorCSRF');
        
        $request = Application::get()->getRequest();
        $clientToken = $request->getUserVar('csrfToken');

        // Panggil Core Engine yang sudah Anda buat
        $op = Application::get()->getRequest()->getRouter()
          ->getRequestedOp(Application::get()->getRequest());
        return ValidatorCSRF::checkToken($clientToken, (string) $op, [], false);
    }
}
?>