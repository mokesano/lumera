<?php
/**
 * Article Navigation
 * @file navigation.php
 * @brief Function mengaktifkan navigasi pada halaman artikel OJS 2.4.8.2 
 * @author Rochmady and Wizdam Team
 * @version v1.0.0 - Menggunakan Aksess Database
 * Last update: 23-05-2025
 */
// Path db_config.php untuk mengakses lingkungan OJS
include '/home/sangiaor/php_sangia/db_config.php';

function getArticleIdFromUrl() {
    $url = $_SERVER['REQUEST_URI'];
    preg_match('/\/article\/view\/(\d+)/', $url, $matches);
    if (isset($matches[1])) {
        return intval($matches[1]);
    }
    return 0;
}

function getJournalPathFromUrl() {
    $url = $_SERVER['REQUEST_URI'];
    preg_match('/\/([^\/]+)\/article\/view\/(\d+)/', $url, $matches);
    if (isset($matches[1])) {
        return $matches[1];
    }
    return '';
}

function getIssueIdFromArticle($mysqli, $articleId) {
    $query = "SELECT issue_id FROM published_articles WHERE article_id = $articleId";
    $result = $mysqli->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['issue_id'];
    }
    return 0;
}

function getArticleNavigation($currentArticleId, $currentJournalPath) {
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $navigation = array(
        'previous' => null,
        'next' => null,
    );

    $currentArticleId = intval($currentArticleId);
    $currentJournalPath = $mysqli->real_escape_string($currentJournalPath);

    $currentIssueId = getIssueIdFromArticle($mysqli, $currentArticleId);

    $allArticlesQuery = "
        SELECT pa.article_id 
        FROM published_articles pa 
        JOIN issues i ON pa.issue_id = i.issue_id 
        JOIN journals j ON i.journal_id = j.journal_id 
        WHERE j.path = '$currentJournalPath' 
        AND i.issue_id = $currentIssueId
        ORDER BY pa.article_id ASC";
    
    $allArticlesResult = $mysqli->query($allArticlesQuery);
    
    if ($allArticlesResult && $allArticlesResult->num_rows > 0) {
        $articles = [];
        while ($row = $allArticlesResult->fetch_assoc()) {
            $articles[] = $row;
        }
        
        $currentIndex = array_search($currentArticleId, array_column($articles, 'article_id'));
        
        if ($currentIndex !== false) {
            if (isset($articles[$currentIndex - 1])) {
                $navigation['previous'] = $articles[$currentIndex - 1];
            } else {
                $previousIssueQuery = "
                    SELECT pa.article_id 
                    FROM published_articles pa 
                    JOIN issues i ON pa.issue_id = i.issue_id 
                    JOIN journals j ON i.journal_id = j.journal_id 
                    WHERE j.path = '$currentJournalPath' 
                    AND i.issue_id < $currentIssueId
                    ORDER BY i.issue_id DESC, pa.article_id DESC LIMIT 1";
                
                $previousIssueResult = $mysqli->query($previousIssueQuery);
                if ($previousIssueResult && $row = $previousIssueResult->fetch_assoc()) {
                    $navigation['previous'] = $row;
                }
            }
            if (isset($articles[$currentIndex + 1])) {
                $navigation['next'] = $articles[$currentIndex + 1];
            } else {
                $nextIssueQuery = "
                    SELECT pa.article_id 
                    FROM published_articles pa 
                    JOIN issues i ON pa.issue_id = i.issue_id 
                    JOIN journals j ON i.journal_id = j.journal_id 
                    WHERE j.path = '$currentJournalPath' 
                    AND i.issue_id > $currentIssueId
                    ORDER BY i.issue_id ASC, pa.article_id ASC LIMIT 1";
                
                $nextIssueResult = $mysqli->query($nextIssueQuery);
                if ($nextIssueResult && $row = $nextIssueResult->fetch_assoc()) {
                    $navigation['next'] = $row;
                }
            }
        }
    }

    $mysqli->close();
    return $navigation;
}
?>
