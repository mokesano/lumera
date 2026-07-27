<?php
declare(strict_types=1);

/**
 * @file tools/moveMigrateInvoices.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * 
 * @brief CLI tool untuk MEMINDAHKAN Queued & Completed Payments ke Invoices.
 * DESTRUKTIF: baris sumber DIHAPUS setelah berhasil dipindah. WAJIB
 * dijalankan dalam mode maintenance.
 */

require(__DIR__ . '/bootstrap.inc.php');

class MoveMigrateInvoices extends CommandLineTool {

    public function usage(): void {
        echo "Script to MOVE legacy payments to Lumera Invoices (DESTRUKTIF -- jalankan HANYA dalam mode maintenance)\n"
           . "Usage: {$this->scriptName}\n";
    }

    public function execute(): void {
        import('lib.wizdam.classes.invoice.InvoiceLegacyMigrationDAO');
        $migrationDao = new InvoiceLegacyMigrationDAO();
        $chunkSize = 100;
        
        echo "====================================================\n";
        echo "LUMERA INVOICES MIGRATOR: DUAL-STAGE CLI WORKER (MODE: MOVE)\n";
        echo "PERINGATAN: baris sumber akan DIHAPUS. Pastikan situs dalam\n";
        echo "mode maintenance sebelum melanjutkan.\n";
        echo "====================================================\n\n";

        echo "[STAGE 1] Memindahkan Queued Payments (Tertunda)...\n";
        $cursor = 0;
        $totalQueued = 0;
        do {
            $result = $migrationDao->moveLegacyQueuedPayments($chunkSize, $cursor);
            $this->printLogs($result['logs']);
            $totalQueued += $result['processed'];
            $cursor = $result['next_cursor'];
            if (!$result['is_done']) usleep(100000);
        } while (!$result['is_done']);
        echo ">> STAGE 1 SELESAI. Total dipindahkan: {$totalQueued} baris.\n\n";

        echo "[STAGE 2] Memindahkan Completed Payments (Lunas/PAID)...\n";
        $cursor = 0;
        $totalCompleted = 0;
        do {
            $result = $migrationDao->moveLegacyCompletedPayments($chunkSize, $cursor);
            $this->printLogs($result['logs']);
            $totalCompleted += $result['processed'];
            $cursor = $result['next_cursor'];
            if (!$result['is_done']) usleep(100000);
        } while (!$result['is_done']);
        echo ">> STAGE 2 SELESAI. Total dipindahkan: {$totalCompleted} baris.\n\n";

        $grandTotal = $totalQueued + $totalCompleted;
        echo "====================================================\n";
        echo "MIGRASI SELESAI TOTAL! {$grandTotal} data berhasil DIPINDAHKAN.\n";
        echo "Baris yang dilewati karena data korup/tidak valid TETAP ADA di tabel\n";
        echo "sumber -- periksa manual, tool ini tidak menghapusnya sepihak.\n";
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

$tool = new MoveMigrateInvoices($argv ?? []);
$tool->execute();