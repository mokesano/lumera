<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/checkout/services/InvoiceService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION]
 * @class InvoiceService
 * @brief Jantung pengelola tagihan. Menangani bisnis proses pembayaran.
 */

import('lib.wizdam.classes.checkout.Invoice');
import('lib.wizdam.classes.checkout.InvoiceDAO');

class InvoiceService {
    
    private InvoiceDAO $invoiceDao;

    /**
     * Constructor
     */
    public function __construct() {
        $this->invoiceDao = DAORegistry::getDAO('InvoiceDAO');
    }

    /**
     * Generate nomor dan kode invoice yang semantik dan permanen.
     * Format artikel : [4 digit awal ISSN]-[articleId]-[4 digit akhir ISSN]
     * Format non-artikel: [PREFIX]-[userId]-[yymmdd]
     */
    public function generateInvoiceNumber( string $feeType, int $journalId, int $userId, ?int $articleId = null ): array {
        $articleFeeTypes = [
            'PUBLICATION', 'FAST_TRACK', 'SUBMISSION',
            Invoice::FEE_TYPE_PUBLICATION,
            Invoice::FEE_TYPE_FAST_TRACK,
            Invoice::FEE_TYPE_SUBMISSION,
        ];

        if (in_array(strtoupper($feeType), $articleFeeTypes, true) && $articleId > 0) {
            // ── Format berbasis ISSN untuk biaya yang terkait artikel ──
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal    = $journalDao->getById($journalId);

            $issnRaw   = '';
            if ($journal) {
                $issnRaw = $journal->getSetting('onlineIssn')
                    ?: ($journal->getSetting('printIssn') ?: '');
            }
            $issnClean = str_pad(str_replace('-', '', $issnRaw), 8, '0', STR_PAD_RIGHT);

            $invoiceNumber = substr($issnClean, 0, 4) . '-' . $articleId . '-' . substr($issnClean, -4);
            $invoiceCode = 'Manuscript#' . str_pad((string) $articleId, 7, '0', STR_PAD_LEFT);

        } else {
            // ── Format berbasis prefix untuk biaya non-artikel ──
            $prefixMap = [
                'MEMBERSHIP'            => 'MBR',
                'RENEW_SUBSCRIPTION'    => 'SUB',
                'PURCHASE_ARTICLE'      => 'ART',
                'DONATION'              => 'DON',
                'PURCHASE_SUBSCRIPTION' => 'PSB',
                'PURCHASE_ISSUE'        => 'ISS',
                'GIFT'                  => 'GFT',
                'BUNDLE_PAYMENT'        => 'BDL',
            ];

            $prefix        = $prefixMap[strtoupper($feeType)] ?? 'INV';
            $datePart      = date('ymd');
            $invoiceNumber = $prefix . '-' . $userId . '-' . $datePart;
            $invoiceCode   = $prefix . '#' . str_pad((string) $userId, 7, '0', STR_PAD_LEFT);
        }

        return [
            'invoiceNumber' => $invoiceNumber,
            'invoiceCode'   => $invoiceCode,
        ];
    }

    /**
     * Membuat tagihan baru (Disinkronkan dengan struktur tabel baru Wizdam)
     * @param int $journalId ID jurnal terkait
     * @param int $userId ID pengguna yang ditagih
     * @param int|null $submissionId ID pengajuan terkait (jika ada)
     * @param string $feeType Jenis biaya (misal: 'SUBMISSION', 'PUBLICATION', 'ACCESS')
     * @param float $amount Nominal tagihan (harus positif)
     * @param string $currencyCode Kode mata uang (default: 'IDR')
     * @return Invoice Objek tagihan yang baru dibuat
     */
    public function generateInvoice(int $journalId, int $userId, ?int $submissionId, string $feeType, float $amount, string $currencyCode = 'IDR'): Invoice {
        if ($amount < 0) {
            throw new \InvalidArgumentException("WIZDAM: Nominal tagihan tidak boleh negatif.");
        }

        $invoice = new Invoice();
        $invoice->setData('journalId', $journalId);
        $invoice->setUserId($userId);
        $invoice->setData('submissionId', $submissionId);
        $invoice->setData('feeType', $feeType);
        $invoice->setData('amount', $amount);
        $invoice->setData('currencyCode', $currencyCode);
        $invoice->setData('status', 'UNPAID');

        $this->invoiceDao->insertObject($invoice);
        return $invoice;
    }

    /**
     * Mengambil satu invoice berdasarkan ID (Termasuk auto-resolve Legacy ID)
     * @param int $invoiceId ID tagihan yang ingin diambil
     * @return Invoice|null Objek Invoice jika ada, atau null jika tidak ada
     */
    public function getInvoiceById(int $invoiceId): ?Invoice {
        return $this->invoiceDao->getById($invoiceId);
    }

    /**
     * Mengambil semua histori tagihan user
     * @param int $userId ID pengguna yang ingin diambil histori tagihannya
     * @return Invoice[] Array objek Invoice terkait dengan pengguna tersebut
     */
    public function getUserInvoices(int $userId): array {
        $result = $this->invoiceDao->getByUserId($userId);
        
        // [WIZDAM FIX] Menjamin kembalian selalu berupa Strict Array
        // Mengantisipasi ketidakpastian tipe data dari DAO bawaan OJS
        if ($result instanceof Invoice) {
            // Jika DAO mengembalikan objek tunggal, bungkus ke dalam array
            return [$result];
        } elseif (is_array($result)) {
            // Jika sudah berupa array, kembalikan langsung
            return $result;
        } elseif (is_object($result) && method_exists($result, 'toArray')) {
            // Jika berupa DAOResultFactory khas OJS 2.x
            return $result->toArray();
        }
        
        // Fallback jika kosong atau null
        return [];
    }

    /**
     * Mengubah status invoice menjadi PAID (Dipanggil Webhook/Payment Gateway)
     * @param int $invoiceId ID tagihan yang akan diubah statusnya
     * @param string $paymentMethod Metode pembayaran yang digunakan
     */
    public function markAsPaid(int $invoiceId, string $paymentMethod): bool {
        $invoice = $this->getInvoiceById($invoiceId);
        
        if (!$invoice || $invoice->isLegacy() || $invoice->getStatus() === 'PAID') {
            return false; 
        }

        $invoice->setData('status', 'PAID');
        $invoice->setData('paymentMethod', $paymentMethod);
        $invoice->setData('datePaid', Core::getCurrentDate());

        $this->invoiceDao->updateObject($invoice);
        
        // [WIZDAM HOOK] Pemicu agar sistem tahu ada pembayaran sukses
        HookRegistry::call('Wizdam::InvoicePaid', [$invoice]);

        return true;
    }
    
    /**
     * Mengambil HANYA tagihan yang BELUM LUNAS (UNPAID)
     * @param int $userId
     * @return Invoice[]
     */
    public function getUnpaidInvoices(int $userId): array {
        $allInvoices = $this->getUserInvoices($userId);
        return array_filter($allInvoices, function($invoice) {
            return $invoice->getData('status') != 1 && $invoice->getData('status') !== 'PAID' && $invoice->getData('datePaid') == '';
        });
    }

    /**
     * Mengambil HANYA tagihan yang SUDAH LUNAS (PAID) - Untuk Receipts/History
     * @param int $userId
     * @return Invoice[]
     */
    public function getPaidInvoices(int $userId): array {
        $allInvoices = $this->getUserInvoices($userId);
        return array_filter($allInvoices, function($invoice) {
            return $invoice->getData('status') == 1 || $invoice->getData('status') === 'PAID' || $invoice->getData('datePaid') != '';
        });
    }

    /**
     * [WIZDAM UX] Mengubah tipe tagihan menjadi teks lokalisasi
     * Mengakomodasi format string Wizdam maupun fallback integer Legacy OJS
     * @param string|int $feeType Tipe tagihan
     * @return string Teks terjemahan yang human-readable
     */
    public function getLocalizedFeeName($feeType): string {
        $typeStr = strtoupper((string) $feeType);
        
        switch ($typeStr) {
            case Invoice::FEE_TYPE_MEMBERSHIP:
            case '1':
                return __('manager.payment.options.membershipFee');
                
            case Invoice::FEE_TYPE_RENEW_SUBSCRIPTION:
            case '2':
                return __('manager.payment.options.renewSubscriptionFee');
                
            case Invoice::FEE_TYPE_PURCHASE_ARTICLE:
            case '3':
                return __('manager.payment.options.purchaseArticleFee');
                
            case Invoice::FEE_TYPE_DONATION:
            case '4':
                return __('manager.payment.options.donationFee');
                
            case Invoice::FEE_TYPE_SUBMISSION:
            case '5':
                return __('manager.payment.options.submissionFee');
                
            case Invoice::FEE_TYPE_FAST_TRACK:
            case '6':
                return __('manager.payment.options.fastTrackFee');
                
            case Invoice::FEE_TYPE_PUBLICATION:
            case '7':
                return __('manager.payment.options.publicationFee');
                
            case Invoice::FEE_TYPE_PURCHASE_SUBSCRIPTION:
            case '8':
                return __('manager.payment.options.purchaseSubscriptionFee');
                
            case Invoice::FEE_TYPE_PURCHASE_ISSUE:
            case '9':
                return __('manager.payment.options.purchaseIssueFee');
                
            case Invoice::FEE_TYPE_GIFT:
            case '10':
                return __('manager.payment.options.giftFee');
                
            default:
                return __('manager.payment.options.otherFee', 'Biaya Lainnya (Layanan Tambahan)');
        }
    }
    
    /**
     * Mengambil ringkasan tagihan
     * @param Invoice $invoice
     * @return array
     */
    public function getInvoiceSummary(Invoice $invoice): array {
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $userDao    = DAORegistry::getDAO('UserDAO');
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $authorDao  = DAORegistry::getDAO('AuthorDAO');
        $countryDao = DAORegistry::getDAO('CountryDAO');

        $journalId = (int) $invoice->getData('journalId');
        $journal   = $journalDao->getById($journalId);
        $userId    = (int) $invoice->getUserId();
        $submitter = $userDao->getById($userId);
        $articleId = (int) ($invoice->getData('submissionId') ?: $invoice->getAssocId());
        $feeType   = (string) ($invoice->getData('feeType') ?: $invoice->getFeeType());

        $localizedFeeName = $this->getLocalizedFeeName($feeType);

        // ── Invoice Number: 3 lapis prioritas, tidak ada fallback status ──
        // Prioritas 1: dari kolom invoice_number di tabel 'invoices' mutlak
        $invoiceNumber = (string) $invoice->getData('invoiceNumber');
        $invoiceCode   = (string) $invoice->getData('invoiceCode');

        // Prioritas 2: dari article_settings (untuk data lama sebelum migrasi skema selesai)
        $article = null;
        if ($articleId > 0 && $journal) {
            $article = $articleDao->getArticle($articleId, $journal->getId());
            if ($article) {
                if (empty($invoiceNumber)) {
                    $invoiceNumber = (string) $article->getData('wizdam_invoice_number');
                    $invoiceCode   = (string) $article->getData('wizdam_invoice_code');
                }
            }
        }

        // Prioritas 3: generate ulang on-the-fly (legacy invoice tanpa nomor sama sekali)
        if (empty($invoiceNumber)) {
            $generated     = $this->generateInvoiceNumber($feeType, $journalId, $userId, $articleId ?: null);
            $invoiceNumber = $generated['invoiceNumber'];
            $invoiceCode   = $generated['invoiceCode'];
        }

        // ── Data penulis/biller ──
        $articleTitle   = $localizedFeeName;
        $billedName     = $submitter ? $submitter->getFullName() : '';
        $billedEmail    = $submitter ? $submitter->getEmail() : '';
        $rawAffiliation = $submitter ? $submitter->getLocalizedAffiliation() : '';
        $countryCode    = $submitter ? $submitter->getCountry() : '';

        $articleFeeTypes = [
            'PUBLICATION', 'FAST_TRACK', 'SUBMISSION',
            'PAYMENT_TYPE_SUBMISSION', 'PAYMENT_TYPE_FASTTRACK', 'PAYMENT_TYPE_PUBLICATION',
            '5', '6', '7',
            Invoice::FEE_TYPE_PUBLICATION,
            Invoice::FEE_TYPE_FAST_TRACK,
            Invoice::FEE_TYPE_SUBMISSION,
        ];

        if ($article && in_array(strtoupper($feeType), $articleFeeTypes, true)) {
            $rawTitle = $article->getLocalizedTitle();
            if (!empty($rawTitle)) $articleTitle = $rawTitle;

            $authors             = $authorDao->getAuthorsBySubmissionId($articleId);
            $correspondingAuthor = null;
            foreach ($authors as $author) {
                if ($author->getPrimaryContact()) { $correspondingAuthor = $author; break; }
            }
            if (!$correspondingAuthor && !empty($authors)) $correspondingAuthor = $authors[0];

            if ($correspondingAuthor) {
                $billedName     = $correspondingAuthor->getFullName();
                $billedEmail    = $correspondingAuthor->getEmail();
                $rawAffiliation = $correspondingAuthor->getLocalizedAffiliation();
                $countryCode    = $correspondingAuthor->getCountry();
            }
        }

        // ── Afiliasi + Negara ──
        $affiliationList = array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', (string) $rawAffiliation))
        ));
        if (!empty($countryCode)) {
            $countryName = $countryDao->getCountry($countryCode);
            if ($countryName) {
                if (empty($affiliationList)) $affiliationList[] = $countryName;
                else $affiliationList[count($affiliationList) - 1] .= ', ' . $countryName;
            }
        }

        // ── Kalkulasi pajak ──
        $settingTaxRate  = $journal ? (float) $journal->getSetting('paymentTax')          : 0;
        $isTaxInclusive  = $journal ? (bool)  $journal->getSetting('paymentTaxInclusive') : false;
        $discount        = $journal ? (float) $journal->getSetting('paymentDiscount')      : 0;
        $taxRate         = $settingTaxRate > 0 ? ($settingTaxRate / 100) : 0.00;
        $finalAmount     = (float) $invoice->getData('amount');

        if ($isTaxInclusive) {
            $baseForVat = $finalAmount / (1 + $taxRate);
            $tax        = $finalAmount - $baseForVat;
        } else {
            $baseForVat = $finalAmount / (1 + $taxRate);
            $tax        = $baseForVat * $taxRate;
        }
        $originalFee  = $baseForVat + $discount;
        $subtotal     = $baseForVat;
        $feeBase      = 0.0;
        $feeFastTrack = 0.0;

        if (in_array(strtoupper($feeType), ['FAST_TRACK', 'PAYMENT_TYPE_FASTTRACK', '6'], true)) {
            $feeFastTrack = $originalFee;
        } else {
            $feeBase = $originalFee;
        }

        return [
            'invoice'              => $invoice,
            'journal'              => $journal,
            'user'                 => $submitter,
            'article'              => $article,
            'localizedFeeName'     => $localizedFeeName,
            'articleTitle'         => $articleTitle,
            'wizdamInvoiceNumber'  => $invoiceNumber,
            'wizdamInvoiceCode'    => $invoiceCode,
            'authorName'           => $billedName,
            'authorAffiliation'    => implode("\n", $affiliationList),
            'authorEmail'          => $billedEmail,
            'currencyCode'         => $invoice->getData('currencyCode'),
            'dateBilled'           => $invoice->getData('dateBilled'),
            'datePaid'             => $invoice->getData('datePaid'),
            'isPaid'               => ($invoice->getData('status') === Invoice::STATUS_PAID),
            'paymentMethod'        => $invoice->getData('paymentMethod'),
            'taxPercentage'        => $settingTaxRate,
            'formattedBaseFee'     => number_format($feeBase, 2),
            'formattedFastTrackFee'=> number_format($feeFastTrack, 2),
            'formattedDiscount'    => number_format($discount, 2),
            'formattedSubtotal'    => number_format($subtotal, 2),
            'formattedTax'         => number_format($tax, 2),
            'formattedAmount'      => number_format($finalAmount, 2),
            'isTaxInclusive'       => $isTaxInclusive,
        ];
    }
    
    /**
     * Mendelegasikan penghapusan tagihan ke DAO.
     * Service Layer murni (Tidak ada raw SQL).
     * @param Invoice $invoice
     * @return bool
     */
    public function deleteInvoice(Invoice $invoice): bool {
        // Asumsi $this->invoiceDao sudah di-assign di konstruktor
        return $this->invoiceDao->deleteInvoiceById((int) $invoice->getInvoiceId());
    }
}
?>