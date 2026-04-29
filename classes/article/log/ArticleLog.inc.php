<?php
declare(strict_types=1);

/**
 * @defgroup article_log
 */

/**
 * @file classes/article/log/ArticleLog.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleLog
 * @ingroup article_log
 *
 * @brief Static class for adding / accessing article log entries.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.article.log.ArticleEventLogEntry');
import('classes.article.log.ArticleEmailLogEntry');

class ArticleLog {
    
    /**
     * Add a new event log entry with the specified parameters
     * @param PKPRequest $request
     * @param Article $article
     * @param int $eventType
     * @param string $messageKey
     * @param array $params optional
     * @return ArticleEventLogEntry|null
     */
    public static function logEvent(PKPRequest $request, Article $article, int $eventType, string $messageKey, array $params = []): ?ArticleEventLogEntry {
        $journal = $request->getJournal();
        
        // [FIX WIZDAM] Kembalikan ke logika standar OJS: Argumen ke-2 adalah Article ID, bukan User ID.
        // Ditambah (int) casting untuk keamanan tipe data PHP 8.
        return self::logEventHeadless(
            $journal, 
            (int) $article->getId(), // <--- PERBAIKAN DISINI (Ganti $userId jadi $article->getId())
            $article, 
            $eventType, 
            $messageKey, 
            $params
        );
    }

    /**
     * Add a new event log entry with the specified parameters
     * @param Journal $journal
     * @param int $userId
     * @param Article $article
     * @param int $eventType
     * @param string $messageKey
     * @param array $params optional
     * @return ArticleEventLogEntry|null
     */
    public static function logEventHeadless(Journal $journal, int $userId, Article $article, int $eventType, string $messageKey, array $params = []): ?ArticleEventLogEntry {

        // Create a new entry object
        $articleEventLogDao = DAORegistry::getDAO('ArticleEventLogDAO');
        $entry = $articleEventLogDao->newDataObject();

        // Set implicit parts of the log entry
        $entry->setDateLogged(Core::getCurrentDate());
        
        // [WIZDAM] Singleton Fallback for Remote Addr
        $request = Application::get()->getRequest();
        $entry->setIPAddress($request->getRemoteAddr());
        
        $entry->setUserId($userId);
        $entry->setAssocType(ASSOC_TYPE_ARTICLE);
        $entry->setAssocId($article->getId());

        // Set explicit parts of the log entry
        $entry->setEventType($eventType);
        $entry->setMessage($messageKey);
        $entry->setParams($params);
        $entry->setIsTranslated(0);
        
        // [WIZDAM FIX] Removed duplicate setParams call
        // $entry->setParams($params); 

        // Insert the resulting object
        $articleEventLogDao->insertObject($entry);
        return $entry;
    }

    /**
     * Add an email log entry to this article.
     * @param int $articleId
     * @param ArticleEmailLogEntry $entry
     * @param PKPRequest|null $request
     * @return int|false ID of inserted object or false
     */
    public static function logEmail(int $articleId, ArticleEmailLogEntry $entry, ?PKPRequest $request = null) {
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $journalId = $articleDao->getArticleJournalId($articleId);

        if (!$journalId) {
            // Invalid article
            return false;
        }

        // Add the entry
        $entry->setAssocType(ASSOC_TYPE_ARTICLE);
        $entry->setAssocId($articleId);

        if ($request) {
            $user = $request->getUser();
            $entry->setSenderId($user == null ? 0 : $user->getId());
            $entry->setIPAddress($request->getRemoteAddr());
        } else {
            $entry->setSenderId(0);
        }

        $entry->setDateSent(Core::getCurrentDate());

        $logDao = DAORegistry::getDAO('ArticleEmailLogDAO');
        return $logDao->insertObject($entry);
    }
}
?>