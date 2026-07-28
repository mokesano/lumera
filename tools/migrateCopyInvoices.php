<?php
declare(strict_types=1);

/**
 * @file tools/migrateCopyInvoices.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * 
 * @class MigrateCopyInvoices
 * @ingroup tools
 *
 * @brief CLI tool untuk MENYALIN Queued & Completed Payments ke Invoices.
 * TIDAK PERNAH menghapus baris sumber. Aman dijalankan berkali-kali.
 */

require(__DIR__ . '/bootstrap.inc.php');

class MigrateCopyInvoices extends CommandLineTool {

    /**
     * Usage tool
     */
    public function usage(): void {
        echo "Script to COPY legacy payments to Lumera Invoices (aman diulang, tidak menghapus sumber)\n"
           . "Usage: {$this->scriptName}\n";
    }

    /**
     * Execute migration invoices
     */
    public function execute(): void {
        import('lib.wizdam.classes.invoice.InvoiceLegacyMigrationDAO');
        $migrationDao = new InvoiceLegacyMigrationDAO();
        $chunkSize = 100;

        try {
            echo "====================================================\n";
            echo "LUMERA INVOICES MIGRATOR: DUAL-STAGE CLI WORKER (MODE: COPY)\n";
            echo "====================================================\n\n";

            echo "[STAGE 1] Menyalin Queued Payments (Tertunda)...\n";
            $cursor = 0;
            $totalQueued = 0;
            do {
                $result = $migrationDao->copyLegacyQueuedPayments($chunkSize, $cursor);
                $this->printLogs($result['logs']);
                $totalQueued += $result['processed'];
                $cursor = $result['next_cursor'];
                if (!$result['is_done']) usleep(100000);
            } while (!$result['is_done']);
            echo ">> STAGE 1 SELESAI. Total disalin: {$totalQueued} baris.\n\n";

            echo "[STAGE 2] Menyalin Completed Payments (Lunas/PAID)...\n";
            $cursor = 0;
            $totalCompleted = 0;
            do {
                $result = $migrationDao->copyLegacyCompletedPayments($chunkSize, $cursor);
                $this->printLogs($result['logs']);
                $totalCompleted += $result['processed'];
                $cursor = $result['next_cursor'];
                if (!$result['is_done']) usleep(100000);
            } while (!$result['is_done']);
            echo ">> STAGE 2 SELESAI. Total disalin: {$totalCompleted} baris.\n\n";

            $grandTotal = $totalQueued + $totalCompleted;
            echo "====================================================\n";
            echo "MIGRASI SELESAI TOTAL! {$grandTotal} data berhasil DISALIN.\n";
            echo "Baris sumber di queued_payments/completed_payments TETAP UTUH.\n";
            echo "====================================================\n";
        } catch (\Throwable $e) {
            echo "\n[GAGAL] " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Print log migrate
     * @param array $logs
     */
    private function printLogs(array $logs): void {
        foreach ($logs as $log) {
            if ($log['type'] === 'success') echo "[OK] " . $log['msg'] . "\n";
            elseif ($log['type'] === 'skip') echo "[SKIP] " . $log['msg'] . "\n";
            elseif ($log['type'] === 'error') echo "[FAIL] " . $log['msg'] . "\n";
        }
    }
}

$tool = new MigrateCopyInvoices($argv ?? []);
$tool->execute();