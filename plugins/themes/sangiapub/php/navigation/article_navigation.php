<?php
/**
 * Article Navigation System
 * File: plugins/themes/[theme-name]/php/navigation/article_navigation.php
 * Sistem navigasi artikel (previous/next) dengan OJS integration
 * @author Rochmady and Wizdam Team approach - OJS Integrated Navigation
 * @version 2.0 - Powerful & Secure Navigation
 * Last Update: 2025-05-26
 */

// Konfigurasi - sama seperti pola Editorial Timeline
$action = isset($_GET['action']) ? $_GET['action'] : 'template';
$articleId = isset($_GET['articleId']) ? (int) $_GET['articleId'] : null;

// Jika tidak ada articleId dari parameter, ambil dari template vars
if (!$articleId) {
    $article = $this->get_template_vars('article');
    if ($article) {
        $articleId = $article->getId();
    }
}

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

// Ambil journal dari template vars - sama seperti pola Editorial
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
 * Fungsi untuk mendapatkan issue ID dari artikel
 * @param int $articleId ID artikel
 * @return int Issue ID
 */
function getIssueIdFromArticleOJS($articleId) {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    
    try {
        $result = $articleDao->retrieve(
            "SELECT pa.issue_id 
             FROM published_articles pa 
             WHERE pa.article_id = ?",
            array($articleId)
        );
        
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $issueId = $row['issue_id'];
            $result->Close();
            return intval($issueId);
        }
    } catch (Exception $e) {
        error_log("Error getting issue ID from article: " . $e->getMessage());
    }
    
    return 0;
}

/**
 * Fungsi untuk mendapatkan semua artikel dalam issue yang sama
 * @param int $issueId ID issue
 * @param int $journalId ID journal
 * @return array Array artikel IDs terurut
 */
function getArticlesInIssue($issueId, $journalId) {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    $articles = array();
    
    try {
        $result = $articleDao->retrieve(
            "SELECT pa.article_id 
             FROM published_articles pa 
             JOIN issues i ON pa.issue_id = i.issue_id 
             WHERE i.issue_id = ? 
             AND i.journal_id = ?
             AND i.published = 1
             ORDER BY pa.article_id ASC",
            array($issueId, $journalId)
        );
        
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $articles[] = intval($row['article_id']);
                $result->MoveNext();
            }
            $result->Close();
        }
    } catch (Exception $e) {
        error_log("Error getting articles in issue: " . $e->getMessage());
    }
    
    return $articles;
}

/**
 * Fungsi untuk mendapatkan artikel dari issue sebelumnya
 * @param int $currentIssueId ID issue saat ini
 * @param int $journalId ID journal
 * @return int|null Article ID dari issue sebelumnya
 */
function getPreviousIssueLastArticle($currentIssueId, $journalId) {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    
    try {
        $result = $articleDao->retrieve(
            "SELECT pa.article_id 
             FROM published_articles pa 
             JOIN issues i ON pa.issue_id = i.issue_id 
             WHERE i.journal_id = ? 
             AND i.issue_id < ?
             AND i.published = 1
             ORDER BY i.issue_id DESC, pa.article_id DESC 
             LIMIT 1",
            array($journalId, $currentIssueId)
        );
        
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $articleId = intval($row['article_id']);
            $result->Close();
            return $articleId;
        }
    } catch (Exception $e) {
        error_log("Error getting previous issue article: " . $e->getMessage());
    }
    
    return null;
}

/**
 * Fungsi untuk mendapatkan artikel dari issue selanjutnya
 * @param int $currentIssueId ID issue saat ini
 * @param int $journalId ID journal
 * @return int|null Article ID dari issue selanjutnya
 */
function getNextIssueFirstArticle($currentIssueId, $journalId) {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    
    try {
        $result = $articleDao->retrieve(
            "SELECT pa.article_id 
             FROM published_articles pa 
             JOIN issues i ON pa.issue_id = i.issue_id 
             WHERE i.journal_id = ? 
             AND i.issue_id > ?
             AND i.published = 1
             ORDER BY i.issue_id ASC, pa.article_id ASC 
             LIMIT 1",
            array($journalId, $currentIssueId)
        );
        
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $articleId = intval($row['article_id']);
            $result->Close();
            return $articleId;
        }
    } catch (Exception $e) {
        error_log("Error getting next issue article: " . $e->getMessage());
    }
    
    return null;
}

/**
 * Fungsi untuk mendapatkan informasi artikel (title, authors)
 * @param int $articleId ID artikel
 * @return array|null Article info
 */
function getArticleInfo($articleId) {
    if (!$articleId) return null;
    
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    $article = $articleDao->getArticle($articleId);
    
    if (!$article) return null;
    
    // Get authors dengan logika nama Indonesia
    $authorDao = &DAORegistry::getDAO('AuthorDAO');
    $authors = $authorDao->getAuthorsBySubmissionId($articleId);
    $authorNames = array();
    $authorsData = array();
    
    if ($authors) {
        foreach ($authors as $author) {
            $firstName = trim($author->getFirstName());
            $middleName = trim($author->getMiddleName());
            $lastName = trim($author->getLastName());
            
            // Logika nama Indonesia: jika firstName sama dengan lastName, tampilkan hanya lastName
            $displayName = '';
            
            if ($firstName !== $lastName && !empty($firstName)) {
                $displayName .= $firstName;
            }
            
            if (!empty($middleName)) {
                $displayName .= (!empty($displayName) ? ' ' : '') . $middleName;
            }
            
            if (!empty($lastName)) {
                $displayName .= (!empty($displayName) ? ' ' : '') . $lastName;
            }
            
            // Fallback jika display name kosong
            if (empty($displayName)) {
                $displayName = !empty($lastName) ? $lastName : (!empty($firstName) ? $firstName : 'Unknown Author');
            }
            
            if (!empty($displayName)) {
                $authorNames[] = $displayName;
                
                // Simpan data lengkap untuk keperluan lain
                $authorsData[] = array(
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'display_name' => $displayName,
                    'is_single_name' => ($firstName === $lastName) // flag untuk nama tunggal
                );
            }
        }
    }
    
    return array(
        'article_id' => $articleId,
        'title' => $article->getLocalizedTitle(),
        'authors' => $authorNames,
        'authors_data' => $authorsData, // Data lengkap authors
        'author_string' => implode(', ', $authorNames),
        'url' => Request::url(null, 'article', 'view', $articleId)
    );
}

/**
 * Fungsi utama untuk mendapatkan navigasi artikel
 * @param int $currentArticleId ID artikel saat ini
 * @param int $journalId ID journal
 * @return array Navigation data
 */
function getArticleNavigationOJS($currentArticleId, $journalId) {
    $navigation = array(
        'previous' => null,
        'next' => null,
        'current_position' => 0,
        'total_in_issue' => 0
    );
    
    // Step 1: Get issue ID dari artikel saat ini
    $currentIssueId = getIssueIdFromArticleOJS($currentArticleId);
    
    if (!$currentIssueId) {
        return $navigation; // Tidak bisa dapat issue, return empty
    }
    
    // Step 2: Get semua artikel dalam issue yang sama
    $articlesInIssue = getArticlesInIssue($currentIssueId, $journalId);
    
    if (empty($articlesInIssue)) {
        return $navigation;
    }
    
    $navigation['total_in_issue'] = count($articlesInIssue);
    
    // Step 3: Find current position
    $currentIndex = array_search($currentArticleId, $articlesInIssue);
    
    if ($currentIndex === false) {
        return $navigation; // Article tidak ditemukan dalam issue
    }
    
    $navigation['current_position'] = $currentIndex + 1;
    
    // Step 4: Determine previous article
    if ($currentIndex > 0) {
        // Ada artikel sebelumnya dalam issue yang sama
        $previousArticleId = $articlesInIssue[$currentIndex - 1];
        $navigation['previous'] = getArticleInfo($previousArticleId);
    } else {
        // Artikel pertama dalam issue, cari dari issue sebelumnya
        $previousArticleId = getPreviousIssueLastArticle($currentIssueId, $journalId);
        if ($previousArticleId) {
            $navigation['previous'] = getArticleInfo($previousArticleId);
            $navigation['previous']['cross_issue'] = true;
        }
    }
    
    // Step 5: Determine next article
    if ($currentIndex < (count($articlesInIssue) - 1)) {
        // Ada artikel selanjutnya dalam issue yang sama
        $nextArticleId = $articlesInIssue[$currentIndex + 1];
        $navigation['next'] = getArticleInfo($nextArticleId);
    } else {
        // Artikel terakhir dalam issue, cari dari issue selanjutnya
        $nextArticleId = getNextIssueFirstArticle($currentIssueId, $journalId);
        if ($nextArticleId) {
            $navigation['next'] = getArticleInfo($nextArticleId);
            $navigation['next']['cross_issue'] = true;
        }
    }
    
    return $navigation;
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

// Ambil navigation data
$navigationData = getArticleNavigationOJS($articleId, $journalId);

// Prepare response
$response = array(
    'success' => true,
    'article_id' => $articleId,
    'journal_id' => $journalId,
    'navigation' => $navigationData,
    'meta' => array(
        'last_update' => date('Y-m-d H:i:s'),
        'version' => '1.0-navigation-ojs',
        'security' => 'ojs-integrated'
    )
);

// Handle different actions - sama seperti pola Editorial
if ($action == 'json' || $action == 'api') {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Article-ID: ' . $articleId);
    header('X-Journal-ID: ' . $journalId);
    header('X-Data-Type: navigation');
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
} else {
    // Template assignment - FOCUSED on what's needed
    $this->assign('articleNavigation', $navigationData);
    $this->assign('previousArticle', $navigationData['previous']);
    $this->assign('nextArticle', $navigationData['next']);
    $this->assign('navigationPosition', $navigationData['current_position']);
    $this->assign('totalArticlesInIssue', $navigationData['total_in_issue']);
}
?>