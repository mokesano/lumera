<?php
/**
 * Editorial Timeline API - Simplified
 * File: plugins/themes/[theme-name]/php/editorial/editorial_time.php
 * Sistem untuk mengambil revisionDate dan acceptedDate saja
 * @author Rochmady and Wizdam Team approach - Simplified Editorial Data
 * @version 2.0 - Focused on Timeline Data Only
 * Last Update: 2025-05-26
 */

// Konfigurasi - sama seperti kode Hero
$action = isset($_GET['action']) ? $_GET['action'] : 'template';
$articleId = isset($_GET['articleId']) ? (int) $_GET['articleId'] : null;

// Validasi parameter
if (!$articleId) {
    if ($action == 'json' || $action == 'api') {
        header('Content-Type: application/json');
        echo json_encode([
            "error" => "No article ID provided",
            "code" => "MISSING_ARTICLE_ID"
        ]);
        exit;
    }
    return; // Exit untuk template
}

// Ambil journal ID dari template vars - sama seperti kode Hero
$journal = $this->get_template_vars('currentJournal');
if (!$journal) {
    if ($action == 'json' || $action == 'api') {
        header('Content-Type: application/json');
        echo json_encode([
            "error" => "Journal context not found", 
            "code" => "NO_JOURNAL_CONTEXT"
        ]);
        exit;
    }
    return; // Exit untuk template
}

$journalId = $journal->getId();

/**
 * Fungsi untuk mendapatkan editorial decisions - HANYA revisionDate dan acceptedDate
 * @param int $articleId ID artikel
 * @return array Array dengan revisionDate dan acceptedDate
 */
function getEditorialTimeline($articleId) {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    
    $timeline = array(
        'revisionDate' => null,
        'acceptedDate' => null
    );
    
    try {
        // Query langsung ke tabel edit_decisions untuk mendapatkan timeline
        $result = $articleDao->retrieve(
            "SELECT decision, date_decided 
             FROM edit_decisions 
             WHERE article_id = ?
             ORDER BY date_decided ASC",
            array($articleId)
        );
        
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $decision = $row['decision'];
                $dateDecided = $row['date_decided'];
                
                // SUBMISSION_EDITOR_DECISION_REVISIONS_REQUIRED = 2
                if ($decision == 2) {
                    $timeline['revisionDate'] = $dateDecided;
                }
                // SUBMISSION_EDITOR_DECISION_ACCEPT = 1  
                elseif ($decision == 1) {
                    $timeline['acceptedDate'] = $dateDecided;
                }
                
                $result->MoveNext();
            }
            $result->Close();
        }
    } catch (Exception $e) {
        error_log("Error getting editorial timeline: " . $e->getMessage());
    }
    
    return $timeline;
}

// === MAIN EXECUTION ===

// Validasi artikel exists dan belongs to journal
$articleDao = &DAORegistry::getDAO('ArticleDAO');
$article = $articleDao->getArticle($articleId);

if (!$article || $article->getJournalId() != $journalId) {
    if ($action == 'json' || $action == 'api') {
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: application/json');
        echo json_encode([
            "error" => "Article not found or access denied",
            "code" => "ARTICLE_NOT_FOUND",
            "article_id" => $articleId
        ]);
        exit;
    }
    return;
}

// Ambil timeline data
$timeline = getEditorialTimeline($articleId);

// Prepare response - SIMPLIFIED hanya article timeline
$response = array(
    'success' => true,
    'article_id' => $articleId,
    'journal_id' => $journalId,
    'article' => array(
        'revisionDate' => $timeline['revisionDate'],
        'acceptedDate' => $timeline['acceptedDate']
    ),
    'meta' => array(
        'last_update' => date('Y-m-d H:i:s'),
        'version' => '1.0-timeline-only',
        'security' => 'ojs-integrated'
    )
);

// Handle different actions - sama seperti kode Hero
if ($action == 'json' || $action == 'api') {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Article-ID: ' . $articleId);
    header('X-Journal-ID: ' . $journalId);
    header('X-Data-Type: timeline-only');
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
} else {
    // Template assignment - SIMPLIFIED
    $this->assign('revisionDate', $timeline['revisionDate']);
    $this->assign('acceptedDate', $timeline['acceptedDate']);
    $this->assign('editorialTimeline', $timeline);
}
?>