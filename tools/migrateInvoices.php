<?php
declare(strict_types=1);

/**
 * @file tools/migrateInvoices.php
 * @brief CLI tool untuk migrasi Queued & Completed Payments secara sekuensial.
 */

require(__DIR__ . '/bootstrap.inc.php');

class MigrateInvoices extends CommandLineTool {

    public function usage(): void {
        echo "Script to migrate legacy payments to Frontedge Invoices\n"
           . "Usage: {$this->scriptName}\n";
    }

    public function execute(): void {
        $invoiceDao = DAORegistry::getDAO('InvoiceDAO');
        $chunkSize = 100;
        
        echo "====================================================\n";
        echo "WIZDAM INVOICES MIGRATOR: DUAL-STAGE CLI WORKER\n";
        echo "====================================================\n\n";

        // --- TAHAP 1: QUEUED PAYMENTS ---
        echo "[TAHAP 1] Mengekstrak Queued Payments (Tertunda)...\n";
        $offset = 0;
        $totalQueued = 0;
        do {
            $result = $invoiceDao->migrateLegacyQueuedPayments($chunkSize, $offset);
            $this->printLogs($result['logs']);
            $totalQueued += $result['processed'];
            $offset += $chunkSize;
            if (!$result['is_done']) usleep(100000); // 0.1 detik
        } while (!$result['is_done']);
        echo ">> TAHAP 1 SELESAI. Total diproses: {$totalQueued} baris.\n\n";

        // --- TAHAP 2: COMPLETED PAYMENTS ---
        echo "[TAHAP 2] Mengekstrak Completed Payments (Lunas/PAID)...\n";
        $offset = 0;
        $totalCompleted = 0;
        do {
            $result = $invoiceDao->migrateLegacyCompletedPayments($chunkSize, $offset);
            $this->printLogs($result['logs']);
            $totalCompleted += $result['processed'];
            $offset += $chunkSize;
            if (!$result['is_done']) usleep(100000); // 0.1 detik
        } while (!$result['is_done']);
        echo ">> TAHAP 2 SELESAI. Total diproses: {$totalCompleted} baris.\n\n";

        $grandTotal = $totalQueued + $totalCompleted;
        echo "====================================================\n";
        echo "MIGRASI SELESAI TOTAL! {$grandTotal} data berhasil diamankan.\n";
        echo "====================================================\n";
    }

    private function printLogs(array $logs): void {
        foreach ($logs as $log) {
            if ($log['type'] === 'success') echo "[OK] " . $log['msg'] . "\n";
            elseif ($log['type'] === 'skip') echo "[SKIP] " . $log['msg'] . "\n";
            elseif ($log['type'] === 'error') echo "[FAIL] " . $log['msg'] . "\n";
        }
    }
}

$tool = new MigrateInvoices($argv ?? []);
$tool->execute();
?>