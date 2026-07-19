<?php
declare(strict_types=1);

/**
 * @file tools/generateIdentifiers.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class GenerateIdentifiers
 * @ingroup tools
 *
 * @brief CLI tool to generate eLocator and PII sequentially for existing articles.
 */

require(__DIR__ . '/bootstrap.inc.php');

class GenerateIdentifiers extends CommandLineTool {

    /**
     * Constructor
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);
    }

    /**
     * Generate eLocator for article.
     */
    public function usage(): void {
        echo "Script to generate eLocator and PII for published articles sequentially\n"
           . "Usage: {$this->scriptName}\n";
    }

    /**
     * Execute to generate eLocator for article.
     */
    public function execute(): void {
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');

        echo "Memulai migrasi eLocator dan PII secara sekuensial...\n";

        // Hanya mengambil ID Artikel (Sangat hemat memori)
        $result = $articleDao->retrieve("SELECT article_id FROM articles WHERE status = 3");
        
        $count = 0;
        $chunkSize = 100;

        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $articleId = (int) $row['article_id'];

            // Eksekusi Pemanggilan Sentral
            $articleDao->getArticleIdentifiers($articleId);

            $count++;
            if ($count % $chunkSize === 0) {
                echo "Telah memproses {$count} artikel...\n";
                usleep(100000); 
            }
            $result->MoveNext();
        }
        $result->Close();

        echo "Selesai! Total {$count} artikel lama berhasil diverifikasi/diperbarui.\n";
    }
}

$tool = new GenerateIdentifiers($argv ?? []);
$tool->execute();
?>