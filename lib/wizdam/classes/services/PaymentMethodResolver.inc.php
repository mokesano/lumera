<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/PaymentMethodResolver.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * 
 * @class PaymentMethodResolver
 * 
 * @brief Menentukan daftar SEMUA metode pembayaran yang berlaku untuk sebuah
 * Invoice, ditandai mana yang terkonfigurasi (bisa diklik) dan mana yang
 * direkomendasikan Journal Manager (paymentMethodPluginName). Rekomendasi
 * TIDAK pernah menutup opsi lain -- opsi yang belum terkonfigurasi tetap
 * tampil, hanya dalam mode nonaktif.
 */

import('lib.wizdam.classes.services.PaymentSettingsService');
import('lib.wizdam.classes.invoice.Invoice');

class PaymentMethodResolver {

    /**
     * @param Invoice $invoice
     * @return array daftar SEMUA metode, tiap elemen:
     *   ['id', 'label', 'is_configured' => bool, 'is_recommended' => bool, 'sub_options'(optional)]
     */
    public function getAvailableMethods(Invoice $invoice): array {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById((int) $invoice->getData('journalId'));
        $settings = PaymentSettingsService::resolveForJournal($journal);

        $recommendedId = $this->_resolveRecommendedId($journal);

        $methods = [
            [
                'id' => 'manual',
                'label' => __('payment.method.manual'),
                'is_configured' => $settings->isManualEnabled(),
            ],
            [
                'id' => 'paypal',
                'label' => __('payment.method.paypal'),
                'is_configured' => $settings->isPayPalEnabled() && $settings->getPayPalSellerEmail() !== '',
            ],
            [
                'id' => 'midtrans',
                'label' => 'Virtual Account / QRIS (Midtrans)',
                'is_configured' => in_array('midtrans', $settings->getEnabledGateways(), true) && $settings->getMidtransServerKey() !== '',
                'sub_options' => ['qris', 'bank_transfer', 'all'],
            ],
            [
                'id' => 'xendit',
                'label' => 'Virtual Account / QRIS (Xendit)',
                'is_configured' => in_array('xendit', $settings->getEnabledGateways(), true) && $settings->getXenditApiKey() !== '',
                'sub_options' => ['qris', 'bank_transfer', 'all'],
            ],
            // Stripe: tinggal tambah 1 blok serupa begitu kredensialnya tersedia.
        ];

        foreach ($methods as &$method) {
            $method['is_recommended'] = ($method['id'] === $recommendedId);
        }
        unset($method);

        return $methods;
    }

    /**
     * Menerjemahkan preferensi lama journal->getSetting('paymentMethodPluginName')
     * ('Manual'/'Paypal', dari PaymethodPlugin::getName()) menjadi id metode
     * baru yang seragam. Kalau tidak diset/tidak dikenali, tidak ada
     * rekomendasi eksplisit (semua metode tampil setara).
     * @param object|null $journal
     * @return string|null
     */
    private function _resolveRecommendedId($journal): ?string {
        if (!$journal) return null;

        $pluginName = (string) $journal->getSetting('paymentMethodPluginName');
        $map = [
            'Manual' => 'manual',
            'Paypal' => 'paypal',
            'Midtrans' => 'midtrans',
            'Xendit' => 'xendit',
        ];

        return $map[$pluginName] ?? null;
    }

}
?>