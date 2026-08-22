<?php
declare(strict_types=1);

/**
 * @file pages/author/LegacySubmitRedirectHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Team
 * Distributed under the GNU GPL v3.
 *
 * @class LegacySubmitRedirectHandler
 * @ingroup pages_author
 *
 * @brief [WIZDAM] Kompatibilitas mundur -- alur submit artikel sudah
 * dipindahkan ke pages/submission/ (skema URL yang lebih rasional dan
 * agnostik terhadap peran). Handler ini HANYA meneruskan permintaan ke
 * lokasi barunya, TIDAK menduplikasi logika apa pun -- mencegah
 * link/bookmark lama (page="author" op="submit" dst) menjadi mati,
 * tanpa mempertahankan dua salinan alur submit yang bisa saling
 * menyimpang seiring waktu.
 */

import('lib.pkp.classes.handler.PKPHandler');

class LegacySubmitRedirectHandler extends PKPHandler {

    /**
     * [WIZDAM] Tangkap SEMUA nama op lama (submit, saveSubmit,
     * submitSuppFile, dst) secara generik lewat magic method __call().
     * Routing App memanggil method PERSIS sesuai nama $op
     * (call_user_func([$handler, $op], ...) -- lihat
     * PKPPageRouter::route()) -- __call() menangkap semuanya tanpa
     * perlu enam method nyaris identik satu per satu.
     *
     * Query string (GET params seperti articleId, yang SELURUH alur
     * wizard submit bergantung padanya) diteruskan EKSPLISIT lewat
     * getQueryArray() -- redirect() TIDAK membawa query string secara
     * otomatis kalau $params tidak diisi.
     * @param string $name Nama op yang dipanggil (jadi nama method di sini)
     * @param array $arguments [$args, $request] dari router
     */
    public function __call($name, $arguments) {
        [$args, $request] = $arguments + [null, null];
        if (!$request) $request = Application::get()->getRequest();

        $queryParams = $request->getQueryArray();
        $request->redirect(null, 'submission', $name, $args, $queryParams);
    }

}
?>