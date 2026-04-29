<?php
declare(strict_types=1);

/**
 * @file classes/article/PublishedArticle.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublishedArticle
 * @ingroup article
 * @see PublishedArticleDAO
 *
 * @brief Published article class.
 * [WIZDAM EDITION] PHP 7.4+ Compatible, Strict Types & Fatal Error Fix
 */

import('classes.article.Article');

class PublishedArticle extends Article {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PublishedArticle() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::PublishedArticle(). Please refactor to parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get ID of published article.
     * @return int
     */
    public function getPublishedArticleId() {
        return $this->getData('publishedArticleId');
    }

    /**
     * Set ID of published article.
     * @param int $publishedArticleId
     */
    public function setPublishedArticleId($publishedArticleId) {
        return $this->setData('publishedArticleId', $publishedArticleId);
    }

    /**
     * Get ID of the issue this article is in.
     * @return int
     */
    public function getIssueId() {
        return $this->getData('issueId');
    }

    /**
     * Set ID of the issue this article is in.
     * @param int $issueId
     */
    public function setIssueId($issueId) {
        return $this->setData('issueId', $issueId);
    }

    /**
     * Get section ID of the issue this article is in.
     * @return int
     */
    public function getSectionId() {
        return $this->getData('sectionId');
    }

    /**
     * Set section ID of the issue this article is in.
     * @param int $sectionId
     */
    public function setSectionId($sectionId) {
        return $this->setData('sectionId', $sectionId);
    }

    /**
     * Get date published.
     * @return string
     */
    public function getDatePublished() {
        return $this->getData('datePublished');
    }

    /**
     * Set date published.
     * @param string $datePublished
     */
    public function setDatePublished($datePublished) {
        return $this->setData('datePublished', $datePublished);
    }

    /**
     * Get sequence of article in table of contents.
     * @return float
     */
    public function getSeq() {
        return $this->getData('seq');
    }

    /**
     * Set sequence of article in table of contents.
     * @param float $seq
     */
    public function setSeq($seq) {
        return $this->setData('seq', $seq);
    }

    /**
     * Get views of the published article.
     * @return int
     */
    public function getViews() {
        $application = PKPApplication::getApplication();
        // Casting (int) added here to fix the Fatal Error
        return $application->getPrimaryMetricByAssoc(ASSOC_TYPE_ARTICLE, (int) $this->getId());
    }

    /**
     * Get the localized license text for the article.
     * @param string $locale (optional)
     * @return string
     */
    public function getLicense($locale = null) {
        // Jika locale spesifik diminta, ambil data untuk locale tersebut.
        if ($locale !== null) {
            $licenseText = $this->getData('license', $locale);
        } else {
            // Jika tidak, App otomatis mendeteksi locale aktif (context-aware),
            // dengan fallback ke bahasa utama (primary locale) jurnal jika terjemahan kosong.
            $licenseText = $this->getLocalizedData('license');
        }

        // Return string murni dari database (atau string kosong jika belum diisi) untuk mencegah Fatal Error
        return $licenseText ?? '';
    }
    
    /**
     * Get profile images of all authors mapped by Author ID
     * @return array
     */
    public function getAuthorProfileImages() {
        $authors = $this->getAuthors();
        $images = [];
        $userDao = DAORegistry::getDAO('UserDAO');
        
        foreach ($authors as $author) {
            $authorId = $author->getId();
            // Struktur baja: jamin kunci 'uploadName' selalu ada meski nilainya null
            $images[$authorId] = ['uploadName' => null]; 
            
            // PERBAIKAN: Gunakan fungsi bawaan OJS yang benar -> getUserByEmail
            $user = $userDao->getUserByEmail($author->getEmail());
            if ($user) {
                $profileImage = $user->getData('profileImage');
                if (is_array($profileImage) && isset($profileImage['uploadName'])) {
                    $images[$authorId]['uploadName'] = $profileImage['uploadName'];
                }
            }
        }
        return $images;
    }

    /**
     * Get user data (like gender) of all authors mapped by Author ID
     * @return array
     */
    public function getAuthorUserDataMap() {
        $authors = $this->getAuthors();
        $map = [];
        $userDao = DAORegistry::getDAO('UserDAO');
        
        foreach ($authors as $author) {
            $authorId = $author->getId();
            // Struktur baja: jamin kunci 'gender' selalu ada
            $map[$authorId] = ['gender' => null]; 
            
            // PERBAIKAN: Gunakan fungsi bawaan OJS yang benar -> getUserByEmail
            $user = $userDao->getUserByEmail($author->getEmail());
            if ($user) {
                $map[$authorId]['gender'] = $user->getData('gender');
            }
        }
        return $map;
    }
    
    /**
     * Get access status (ARTICLE_ACCESS_...)
     * @return int
     */
    public function getAccessStatus() {
        return $this->getData('accessStatus');
    }

    /**
     * Set access status (ARTICLE_ACCESS_...)
     * @param int $accessStatus
     */
    public function setAccessStatus($accessStatus) {
        return $this->setData('accessStatus', $accessStatus);
    }

    /**
     * Get the galleys for an article.
     * @return array ArticleGalley
     */
    public function getGalleys() {
        return $this->getData('galleys');
    }

    /**
     * Get the localized galleys for an article.
     * @return array ArticleGalley
     */
    public function getLocalizedGalleys() {
        $allGalleys = $this->getData('galleys');
        $galleys = [];

        if (!is_array($allGalleys)) return $galleys;

        foreach ([AppLocale::getLocale(), AppLocale::getPrimaryLocale()] as $tryLocale) {
            foreach ($allGalleys as $galley) {
                if ($galley->getLocale() == $tryLocale) {
                    $galleys[] = $galley;
                }
            }
            if (!empty($galleys)) {
                // [FIX] $articleId was undefined in original code
                $articleId = $this->getId();
                HookRegistry::dispatch('ArticleGalleyDAO::getLocalizedGalleysByArticle', [&$galleys, $articleId]);
                return $galleys;
            }
        }

        return $galleys;
    }

    /**
     * Set the galleys for an article.
     * @param array $galleys ArticleGalley
     */
    public function setGalleys($galleys) {
        return $this->setData('galleys', $galleys);
    }

    /**
     * Get supplementary files for this article.
     * @return array SuppFiles
     */
    public function getSuppFiles() {
        return $this->getData('suppFiles');
    }

    /**
     * Set supplementary file for this article.
     * @param array $suppFiles SuppFiles
     */
    public function setSuppFiles($suppFiles) {
        return $this->setData('suppFiles', $suppFiles);
    }
}

?>