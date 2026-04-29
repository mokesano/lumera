<?php
declare(strict_types=1);

/**
 * @file classes/article/AuthorDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorDAO
 * @ingroup article
 * @see Author
 *
 * @brief Operations for retrieving and modifying Author objects.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor, Ref removal)
 * - Strict Integer/String Casting
 * - Optimized Profile Mapping Logic
 */

import('classes.article.Author');
import('classes.article.Article');
import('lib.pkp.classes.submission.PKPAuthorDAO');

class AuthorDAO extends PKPAuthorDAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthorDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class AuthorDAO uses deprecated constructor. Please refactor to __construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Retrieve all published submissions associated with authors with
     * the given first name, middle name, last name, affiliation, and country.
     * @param int|null $journalId (null if no restriction desired)
     * @param string $firstName
     * @param string $middleName
     * @param string $lastName
     * @param string $affiliation
     * @param string $country
     * @return array PublishedArticles
     */
    public function getPublishedArticlesForAuthor($journalId, $firstName, $middleName, $lastName, $affiliation, $country) {
        $publishedArticles = array();
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $params = array(
            'affiliation',
            $firstName, 
            $middleName, 
            $lastName,
            $affiliation, 
            $country
        );
        if ($journalId !== null) $params[] = (int) $journalId;

        $result = $this->retrieve(
            'SELECT DISTINCT
                aa.submission_id
            FROM authors aa
                LEFT JOIN articles a ON (aa.submission_id = a.article_id)
                LEFT JOIN author_settings asl ON (asl.author_id = aa.author_id AND asl.setting_name = ?)
            WHERE aa.first_name = ?
                AND a.status = ' . STATUS_PUBLISHED . '
                AND (aa.middle_name = ?' . (empty($middleName)?' OR aa.middle_name IS NULL':'') . ')
                AND aa.last_name = ?
                AND (asl.setting_value = ?' . (empty($affiliation)?' OR asl.setting_value IS NULL':'') . ')
                AND (aa.country = ?' . (empty($country)?' OR aa.country IS NULL':'') . ') ' .
                ($journalId!==null?(' AND a.journal_id = ?'):''),
            $params
        );

        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($row['submission_id']);
            if ($publishedArticle) {
                $publishedArticles[] = $publishedArticle;
            }
            $result->moveNext();
            unset($publishedArticle);
        }

        $result->Close();
        unset($result);

        return $publishedArticles;
    }

    /**
     * Retrieve all published authors for a journal in an associative array by
     * the first letter of the last name.
     * @param int|null $journalId Optional journal ID to restrict results to
     * @param string|null $initial An initial the last names must begin with
     * @param DBResultRange|null $rangeInfo Range information
     * @param boolean $includeEmail Whether or not to include the email in the select distinct
     * @param boolean $disallowRepeatedEmail Whether or not to include duplicated emails in the array
     * @return DAOResultFactory Authors ordered by sequence
     */
    public function getAuthorsAlphabetizedByJournal($journalId = null, $initial = null, $rangeInfo = null, $includeEmail = false, $disallowRepeatedEmail = false) {
        $params = array(
            'affiliation', AppLocale::getPrimaryLocale(),
            'affiliation', AppLocale::getLocale()
        );

        if (isset($journalId)) $params[] = (int) $journalId;
        $params[] = AUTHOR_TOC_DEFAULT;
        $params[] = AUTHOR_TOC_SHOW;
        
        $initialSql = '';
        if (isset($initial)) {
            $params[] = PKPString::strtolower($initial) . '%';
            $initialSql = ' AND LOWER(aa.last_name) LIKE LOWER(?)';
        }

        $result = $this->retrieveRange(
            'SELECT DISTINCT
                CAST(\'\' AS CHAR) AS url,
                0 AS author_id,
                0 AS submission_id,
                ' . ($includeEmail ? 'aa.email AS email,' : 'CAST(\'\' AS CHAR) AS email,') . '
                0 AS primary_contact,
                0 AS seq,
                aa.first_name,
                aa.middle_name,
                aa.last_name,
                CASE WHEN asl.setting_value = \'\' THEN NULL ELSE SUBSTRING(asl.setting_value FROM 1 FOR 255) END AS affiliation_l,
                CASE WHEN asl.setting_value = \'\' THEN NULL ELSE asl.locale END AS locale,
                CASE WHEN aspl.setting_value = \'\' THEN NULL ELSE SUBSTRING(aspl.setting_value FROM 1 FOR 255) END AS affiliation_pl,
                CASE WHEN aspl.setting_value = \'\' THEN NULL ELSE aspl.locale END AS primary_locale,
                CASE WHEN aa.country = \'\' THEN NULL ELSE aa.country END AS country
            FROM authors aa
                LEFT JOIN author_settings aspl ON (aa.author_id = aspl.author_id AND aspl.setting_name = ? AND aspl.locale = ?)
                LEFT JOIN author_settings asl ON (aa.author_id = asl.author_id AND asl.setting_name = ? AND asl.locale = ?)
                '.($disallowRepeatedEmail ? " LEFT JOIN authors aa2 ON (aa.email=aa2.email AND aa.author_id < aa2.author_id) " : "").'
                JOIN articles a ON (a.article_id = aa.submission_id AND a.status = ' . STATUS_PUBLISHED . ')
                JOIN published_articles pa ON (pa.article_id = a.article_id)
                JOIN issues i ON (pa.issue_id = i.issue_id AND i.published = 1)
                JOIN sections s ON (a.section_id = s.section_id)
                JOIN journals j ON (a.journal_id = j.journal_id)
            WHERE ' . (isset($journalId) ? 'a.journal_id = ?' : 'j.enabled = 1') . '
                AND (aa.last_name IS NOT NULL AND aa.last_name <> \'\')
                AND ((s.hide_author = 0 AND a.hide_author = ?) OR a.hide_author = ?)
                ' . ($disallowRepeatedEmail ? ' AND aa2.email IS NULL ' : '')
                . $initialSql . '
            ORDER BY aa.last_name, aa.first_name',
            $params,
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnSimpleAuthorFromRow');
        return $returner;
    }

    /**
     * Get a new data object
     * @return Author
     */
    public function newDataObject() {
        return new Author();
    }

    /**
     * Insert a new Author.
     * @param Author $author (No & needed)
     * @return int
     */
    public function insertAuthor($author) {
        $this->update(
            'INSERT INTO authors
                (submission_id, first_name, middle_name, last_name, country, email, url, primary_contact, seq)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            array(
                (int) $author->getSubmissionId(),
                $author->getFirstName(),
                $author->getMiddleName() . '', // make non-null string
                $author->getLastName(),
                $author->getCountry(),
                $author->getEmail(),
                $author->getUrl(),
                (int) $author->getPrimaryContact(),
                (float) $author->getSequence()
            )
        );

        $author->setId($this->getInsertAuthorId());
        $this->updateLocaleFields($author);

        return $author->getId();
    }

    /**
     * Update an existing Author.
     * @param Author $author (No & needed)
     */
    public function updateAuthor($author) {
        $returner = $this->update(
            'UPDATE authors
            SET first_name = ?,
                middle_name = ?,
                last_name = ?,
                country = ?,
                email = ?,
                url = ?,
                primary_contact = ?,
                seq = ?
            WHERE author_id = ?',
            array(
                $author->getFirstName(),
                $author->getMiddleName() . '', // make non-null
                $author->getLastName(),
                $author->getCountry(),
                $author->getEmail(),
                $author->getUrl(),
                (int) $author->getPrimaryContact(),
                (float) $author->getSequence(),
                (int) $author->getId()
            )
        );
        $this->updateLocaleFields($author);
        return $returner;
    }

    /**
     * Delete authors by submission.
     * @param int $submissionId
     */
    public function deleteAuthorsByArticle($submissionId) {
        $authors = $this->getAuthorsBySubmissionId($submissionId);
        foreach ($authors as $author) {
            $this->deleteAuthor($author);
        }
    }

    /**
     * Get additional fields.
     * @return array
     */
    public function getAdditionalFieldNames() {
        $additionalFields = parent::getAdditionalFieldNames();
        $additionalFields[] = 'orcid';
        return $additionalFields;
    }
    
    /**
     * [MOD FORK v7.4] Mengambil data profil gabungan (User, OJS, Gravatar)
     * untuk array objek Penulis (Author) yang diberikan.
     * @param array $authors (Array dari objek Author)
     * @return array (Berisi 3 peta: profileImages, gravatars, userData)
     */
    public function getAuthorProfileDataMaps($authors) {
        $userDao = DAORegistry::getDAO('UserDAO');
        
        $authorProfileImageMap = array();
        $authorGravatarMap = array();
        $authorUserDataMap = array();
        
        // PHP 8 Safety: ensure input is iterable
        if (!is_array($authors)) return array('profileImages' => [], 'gravatars' => [], 'userData' => []);

        foreach ($authors as $author) {
            $authorId = $author->getId();
            $authorEmail = $author->getEmail(); // Email dari data naskah (fallback)
            $authorOrcid = $author->getData('orcid');
            $authorFirstName = $author->getFirstName();
            $authorLastName = $author->getLastName();

            $matchingUserId = null;
            $emailToUseForGravatar = $authorEmail; // Default: gunakan email naskah

            // 1. Normalisasi ORCID
            $normalizedOrcid = null;
            if ($authorOrcid) {
                $normalizedOrcid = preg_replace('/^https?:\/\/(www\.)?orcid\.org\//', '', $authorOrcid);
            }

            // 2. Cari berdasarkan ORCID
            if ($normalizedOrcid) {
                $userId = $userDao->getUserIdByNormalizedOrcid($normalizedOrcid);
                if ($userId) $matchingUserId = $userId;
            }

            // 3. Fallback ke email
            if (!$matchingUserId && !empty($authorEmail)) {
                $user = $userDao->getUserByEmail($authorEmail);
                if ($user) $matchingUserId = $user->getId();
            }
            
            // 4. Fallback ke Nama (menggunakan query SQL langsung)
            if (!$matchingUserId && !empty($authorFirstName) && !empty($authorLastName)) {
                
                // [FIX] PERBAIKAN FATAL ERROR: Use Execute() array binding
                // $conn = $this->getDataSource(); // dead code
                $result = $this->retrieve(
                    'SELECT user_id FROM users WHERE first_name = ? AND last_name = ?',
                    array($authorFirstName, $authorLastName)
                );
                
                if (!$result->EOF) {
                    $matchingUserId = $result->fields['user_id'];
                }
                $result->Close();
            }

            // --- Jika Pengguna Ditemukan, Ambil Datanya ---
            if ($matchingUserId) {
                $user = $userDao->getById($matchingUserId);
                if ($user) {
                    // PETA 1: Simpan data gambar profil OJS
                    $profileImage = $user->getData('profileImage');
                    if ($profileImage && !empty($profileImage['uploadName'])) {
                        $authorProfileImageMap[$authorId] = $profileImage;
                    }
                    
                    // Prioritaskan email User yang cocok untuk Gravatar
                    $emailToUseForGravatar = $user->getEmail();
                    
                    // PETA 3: Simpan data pengguna lainnya
                    $authorUserDataMap[$authorId] = array(
                        'id' => $user->getId(),
                        'gender' => $user->getGender(),
                        'url' => $user->getUrl(),
                        'phone' => $user->getPhone(),
                        'fax' => $user->getFax(),
                        'biography' => $user->getBiography(null)
                    );
                }
            }

            // --- PETA 2: Buat Fallback Gravatar ---
            if (!empty($emailToUseForGravatar)) {
                $hash = md5(strtolower(trim($emailToUseForGravatar)));
                $authorGravatarMap[$authorId] = "https://www.gravatar.com/avatar/" . $hash . "?s=150&d=mm";
            }
        }
        
        // Kembalikan semua peta sebagai satu array
        return array(
            'profileImages' => $authorProfileImageMap,
            'gravatars' => $authorGravatarMap,
            'userData' => $authorUserDataMap
        );
    }
    
    /**
     * Retrieve author ID by first and last name.
     * @param $firstName string
     * @param $lastName string
     * @return int|null
     */
    public function getAuthorIdByName($firstName, $lastName) {
        $result = $this->retrieve(
            'SELECT author_id FROM authors WHERE first_name = ? AND last_name = ? LIMIT 1',
            array($firstName, $lastName)
        );

        if ($result->EOF) {
            return null;
        }

        $row = $result->GetRowAssoc(false);
        return $row['author_id'];
    }
    
    /**
     * Get extended author data (Email, URL, ORCID)
     * @param $authorId int
     * @return array
     */
    public function getAuthorAdditionalData($authorId) {
        $data = array('email' => null, 'url' => null, 'orcid' => null);
        
        // Get Email & URL
        $result = $this->retrieve(
            'SELECT email, url FROM authors WHERE author_id = ?',
            array((int) $authorId)
        );

        if (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $data['email'] = $row['email'];
            $data['url'] = $row['url'];
        }

        // Get ORCID from settings
        $result = $this->retrieve(
            "SELECT setting_value FROM author_settings WHERE author_id = ? AND setting_name = 'orcid'",
            array((int) $authorId)
        );

        if (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            // Clean ORCID Logic inline
            $s = $row['setting_value'];
            $s = preg_replace('/(https?:\/\/)?(orcid\.org\/)?/', '', $s);
            if (preg_match('/^\d{16}$/', $s)) {
                $s = substr($s,0,4).'-'.substr($s,4,4).'-'.substr($s,8,4).'-'.substr($s,12,4);
            }
            $data['orcid'] = $s;
        }

        return $data;
    }
}

?>