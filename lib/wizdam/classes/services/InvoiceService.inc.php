<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/InvoiceService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance & DDD
 * @class InvoiceService
 * @brief Jantung pengelola tagihan. Menangani bisnis proses pembayaran, 
 * bersih dari raw SQL query, mendukung i18n, dan berada di direktori services terpusat.
 */

// Model & DAO tetap di domain checkout (atau sesuaikan jika ikut dipindah)
import('lib.wizdam.classes.invoice.Invoice');
import('lib.wizdam.classes.invoice.InvoiceDAO');

// Import service yang relevan
import('lib.wizdam.classes.services.CartService');

class InvoiceService {
    
    /** @var InvoiceDAO */
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
     * Membuat tagihan baru
     * @param int $journalId
     * @param int $userId
     * @param int|null $submissionId
     * @param string $feeType
     * @param float $amount
     * @param string $currencyCode
     * @return Invoice
     */
    public function generateInvoice( int $journalId, int $userId, ?int $submissionId, string $feeType, float $amount, string $currencyCode = 'IDR', ?string $invoiceNumber = null, ?string $invoiceCode = null ): Invoice {
        
        if ($amount < 0) {
            // [WIZDAM i18n FIX] Menggunakan locale key untuk exception
            throw new \InvalidArgumentException(__('billing.error.negativeAmount'));
        }
        
        // Jika invoiceNumber tidak dikirim dari OJSPaymentManager, generate
        if (empty($invoiceNumber)) {
            $generated     = $this->generateInvoiceNumber($feeType, $journalId, $userId, $submissionId);
            $invoiceNumber = $generated['invoiceNumber'];
            $invoiceCode   = $generated['invoiceCode'];
        }

        $invoice = new Invoice();
        $invoice->setData('journalId', $journalId);
        $invoice->setUserId($userId);
        $invoice->setData('submissionId', $submissionId);
        $invoice->setData('invoiceNumber', $invoiceNumber);
        $invoice->setData('invoiceCode', $invoiceCode);
        $invoice->setData('feeType', $feeType);
        $invoice->setData('amount', $amount);
        $invoice->setData('currencyCode', $currencyCode);
        $invoice->setData('status', 'UNPAID');

        $this->invoiceDao->insertObject($invoice);
        return $invoice;
    }

    /**
     * [WIZDAM] Membuat tagihan dari sesi Keranjang Belanja (B2C -> B2B)
     * Fungsi ini dipanggil oleh OrderHandler saat mengeksekusi Checkout.
     * @param object $user Objek pengguna yang melakukan checkout
     * @param array $cartItems Array item dalam keranjang
     * @param int item_reference_id (ID artikel/manuskrip)
     * @param string item_type
     * @return Invoice Tagihan yang baru dibuat berdasarkan isi keranjang
     */
    public function createInvoiceFromCart(object $user, array $cartItems): Invoice {
        if (empty($cartItems)) {
            // [WIZDAM i18n FIX] Menggunakan locale key (sudah disiapkan di OrderHandler)
            throw new \Exception(__('order.error.emptyCart'));
        }

        // Ambil item pertama sebagai referensi jurnal/artikel utama
        $firstItem = $cartItems[0];
        $journalId = (int) Config::getVar('general', 'journal_id', 1);
        $articleId = (int) $firstItem['item_reference_id'];
        
        // Panggil CartService untuk menghitung total tagihan dengan akurat
        $cartService = new CartService();
        $summary = $cartService->calculateSummary($cartItems, $journalId);

        // Anggap feeType sebagai kumpulan (Bundle) jika lebih dari 1 item
        $feeType = count($cartItems) > 1 ? 'BUNDLE_PAYMENT' : $firstItem['item_type'];

        // Buat Invoice baru menggunakan nilai Total Akhir (setelah pajak/diskon)
        return $this->generateInvoice(
            $journalId,
            (int) $user->getId(),
            $articleId,
            strtoupper($feeType),
            (float) $summary['total'],
            $summary['currency']
        );
    }

    /**
     * Mengambil tagihan berdasarkan ID
     * @param int $invoiceId
     * @return Invoice|null
     */
    public function getInvoiceById(int $invoiceId): ?Invoice {
        return $this->invoiceDao->getById($invoiceId);
    }

    /**
     * Mengambil semua tagihan untuk pengguna tertentu, dengan filter opsional.
     * @param int $userId
     * @param string $filter 'active' = UNPAID/PENDING, 'history' = PAID/CANCELLED/EXPIRED, '' = semua
     * @return Invoice[]
     */
    public function getUserInvoices(int $userId, string $filter = ''): array {
        $result = $this->invoiceDao->getByUserId($userId);
    
        // Normalisasi hasil dari DAO
        if ($result instanceof Invoice) {
            $allInvoices = [$result];
        } elseif (is_array($result)) {
            $allInvoices = $result;
        } elseif (is_object($result) && method_exists($result, 'toArray')) {
            $allInvoices = $result->toArray();
        } else {
            $allInvoices = [];
        }
    
        // Terapkan filter berdasarkan argumen
        switch ($filter) {
            case 'active':
                // Tagihan aktif: belum dibayar dan belum dibatalkan
                return array_values(array_filter($allInvoices, function (Invoice $invoice) {
                    $status = $invoice->getStatus();
                    return !in_array($status, [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED])
                        && empty($invoice->getData('datePaid'));
                }));
    
            case 'history':
                // Riwayat: sudah selesai (lunas, dibatalkan, atau expired)
                return array_values(array_filter($allInvoices, function (Invoice $invoice) {
                    $status = $invoice->getStatus();
                    return in_array($status, [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED])
                        || !empty($invoice->getData('datePaid'));
                }));
    
            default:
                // Tanpa filter: kembalikan semua
                return $allInvoices;
        }
    }

    /**
     * Menandai tagihan sebagai dibayar
     * @param int $invoiceId
     * @param string $paymentMethod
     * @return bool
     */
    public function markAsPaid(int $invoiceId, string $paymentMethod): bool {
        $invoice = $this->getInvoiceById($invoiceId);
        if (!$invoice || $invoice->isLegacy() || $invoice->getStatus() === Invoice::STATUS_PAID) {
            return false;
        }
        $invoice->setData('status',        Invoice::STATUS_PAID);
        $invoice->setData('paymentMethod', $paymentMethod);
        $invoice->setData('datePaid',      Core::getCurrentDate());
        $this->invoiceDao->updateObject($invoice);
        HookRegistry::call('Wizdam::InvoicePaid', [$invoice]);
        return true;
    }
    
    /**
     * Mengambil tagihan yang belum dibayar untuk pengguna tertentu
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
     * Mengambil tagihan yang sudah dibayar untuk pengguna tertentu
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
     * Mengambil nama biaya yang telah diterjemahkan berdasarkan tipe biaya
     * @param string $feeType
     * @return string
     */
    public function getLocalizedFeeName(string $feeType): string {
        $typeStr = strtoupper($feeType);
        switch ($typeStr) {
            case Invoice::FEE_TYPE_MEMBERSHIP:
            case '1':  return __('manager.payment.options.membershipFee');
            case Invoice::FEE_TYPE_RENEW_SUBSCRIPTION:
            case '2':  return __('manager.payment.options.renewSubscriptionFee');
            case Invoice::FEE_TYPE_PURCHASE_ARTICLE:
            case '3':  return __('manager.payment.options.purchaseArticleFee');
            case Invoice::FEE_TYPE_DONATION:
            case '4':  return __('manager.payment.options.donationFee');
            case Invoice::FEE_TYPE_SUBMISSION:
            case '5':  return __('manager.payment.options.submissionFee');
            case Invoice::FEE_TYPE_FAST_TRACK:
            case '6':  return __('manager.payment.options.fastTrackFee');
            case Invoice::FEE_TYPE_PUBLICATION:
            case '7':  return __('manager.payment.options.publicationFee');
            case Invoice::FEE_TYPE_PURCHASE_SUBSCRIPTION:
            case '8':  return __('manager.payment.options.purchaseSubscriptionFee');
            case Invoice::FEE_TYPE_PURCHASE_ISSUE:
            case '9':  return __('manager.payment.options.purchaseIssueFee');
            case Invoice::FEE_TYPE_GIFT:
            case '10': return __('manager.payment.options.giftFee');
            case 'BUNDLE_PAYMENT': return __('billing.feeType.bundlePayment');
            default:   return __('manager.payment.options.otherFee');
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
     * Membatalkan tagihan (Digunakan oleh Webhook atau pembatalan manual).
     * Tagihan tidak dihapus dari database demi menjaga jejak audit finansial.
     * @param int $invoiceId
     * @return bool
     */
    public function markAsCancelled(int $invoiceId): bool {
        $invoice = $this->getInvoiceById($invoiceId);
        
        // Hanya tagihan UNPAID yang bisa dibatalkan
        if (!$invoice || $invoice->isLegacy() || $invoice->getStatus() !== Invoice::STATUS_UNPAID) {
            return false; 
        }

        // Ubah status menjadi CANCELLED
        $invoice->setData('status', Invoice::STATUS_CANCELLED);
        
        // Kosongkan metode pembayaran jika ada sisa data
        $invoice->setData('paymentMethod', ''); 

        // Gunakan updateObject standar milik DAO
        $this->invoiceDao->updateObject($invoice);
        
        // Opsional: Panggil hook jika ada aksi lanjutan saat dibatalkan
        HookRegistry::call('Wizdam::InvoiceCancelled', [$invoice]);

        return true;
    }

    /**
     * Deletes an invoice.
     * @param Invoice $invoice The invoice to delete.
     * @return bool True if the invoice was deleted, false otherwise.
     */
    public function deleteInvoice(Invoice $invoice): bool {
        return $this->invoiceDao->deleteInvoiceById((int) $invoice->getInvoiceId());
    }
}
?>