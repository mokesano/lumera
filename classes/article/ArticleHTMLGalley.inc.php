<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleHTMLGalley.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleHTMLGalley
 * @ingroup article
 *
 * @brief An HTML galley may include an optional stylesheet and set of images.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor, Regex Callback, Ref removal)
 * - Null Safety for Image/File Iteration
 */

import('classes.article.ArticleGalley');

class ArticleHTMLGalley extends ArticleGalley {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleHTMLGalley() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            // [CCTV] Untuk menangkap identitas Class Anak (jika ada)
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ArticleHTMLGalley(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Check if galley is an HTML galley.
     * @return boolean
     */
    public function isHTMLGalley() {
        return true;
    }

    /**
     * Return string containing the contents of the HTML file.
     * This function performs any necessary filtering, like image URL replacement.
     * @return string
     */
    public function getHTMLContents() {
        import('classes.file.ArticleFileManager');
        $fileManager = new ArticleFileManager($this->getArticleId());
        $contents = $fileManager->readFile($this->getFileId());
        $journal = Request::getJournal();

        // Replace media file references
        // PHP 8 Safety: Ensure images is array
        $images = $this->getImageFiles();
        if ($images === null) $images = array();

        foreach ($images as $image) {
            $imageUrl = Request::url(null, 'article', 'viewFile', array($this->getArticleId(), $this->getBestGalleyId($journal), $image->getFileId()));
            $pattern = preg_quote(rawurlencode($image->getOriginalFileName()));

            $contents = preg_replace(
                '/([Ss][Rr][Cc]|[Hh][Rr][Ee][Ff]|[Dd][Aa][Tt][Aa])\s*=\s*"([^"]*' . $pattern . ')"/',
                '\1="' . $imageUrl . '"',
                $contents
            );

            // Replacement for Flowplayer
            $contents = preg_replace(
                '/[Uu][Rr][Ll]\s*\:\s*\'(' . $pattern . ')\'/',
                'url:\'' . $imageUrl . '\'',
                $contents
            );

            // Replacement for other players (tested with odeo; yahoo and google player won't work w/ OJS URLs, might work for others)
            $contents = preg_replace(
                '/[Uu][Rr][Ll]=([^"]*' . $pattern . ')/',
                'url=' . $imageUrl ,
                $contents
            );
        }

        // Perform replacement for ojs://... URLs
        // Guideline #5: Removed & from $this for callback
        $contents = preg_replace_callback(
            '/(<[^<>]*")[Oo][Jj][Ss]:\/\/([^"]+)("[^<>]*>)/',
            array($this, '_handleOjsUrl'),
            (string) $contents
        );

        // Perform variable replacement for journal, issue, site info
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $issueDao->getIssueByArticleId($this->getArticleId());

        $journal = Request::getJournal();
        $site = Request::getSite();

        $paramArray = array(
            'issueTitle' => $issue ? $issue->getIssueIdentification() : __('editor.article.scheduleForPublication.toBeAssigned'),
            'journalTitle' => $journal ? $journal->getLocalizedTitle() : '',
            'siteTitle' => $site ? $site->getLocalizedTitle() : '',
            'currentUrl' => Request::getRequestUrl()
        );

        foreach ($paramArray as $key => $value) {
            $contents = str_replace('{$' . $key . '}', (string) $value, $contents);
        }

        return $contents;
    }

    /**
     * Regex callback to handle OJS URLs
     * @param array $matchArray
     * @return string
     */
    public function _handleOjsUrl($matchArray) {
        $url = $matchArray[2];
        $anchor = null;
        if (($i = strpos($url, '#')) !== false) {
            $anchor = substr($url, $i+1);
            $url = substr($url, 0, $i);
        }
        $urlParts = explode('/', $url);
        
        if (isset($urlParts[0])) {
            switch(strtolower_codesafe($urlParts[0])) {
                case 'journal':
                    $url = Request::url(
                        isset($urlParts[1]) ?
                            $urlParts[1] :
                            Request::getRequestedJournalPath(),
                        null,
                        null,
                        null,
                        null,
                        $anchor
                    );
                    break;
                case 'article':
                    if (isset($urlParts[1])) {
                        $url = Request::url(
                            null,
                            'article',
                            'view',
                            $urlParts[1],
                            null,
                            $anchor
                        );
                    }
                    break;
                case 'issue':
                    if (isset($urlParts[1])) {
                        $url = Request::url(
                            null,
                            'issue',
                            'view',
                            $urlParts[1],
                            null,
                            $anchor
                        );
                    } else {
                        $url = Request::url(
                            null,
                            'issue',
                            'current',
                            null,
                            null,
                            $anchor
                        );
                    }
                    break;
                case 'suppfile':
                    if (isset($urlParts[1]) && isset($urlParts[2])) {
                        $url = Request::url(
                            null,
                            'article',
                            'downloadSuppFile',
                            array($urlParts[1], $urlParts[2]),
                            null,
                            $anchor
                        );
                    }
                    break;
                case 'sitepublic':
                    array_shift($urlParts);
                    import ('classes.file.PublicFileManager');
                    $publicFileManager = new PublicFileManager();
                    $url = Request::getBaseUrl() . '/' . $publicFileManager->getSiteFilesPath() . '/' . implode('/', $urlParts) . ($anchor?'#' . $anchor:'');
                    break;
                case 'public':
                    array_shift($urlParts);
                    $journal = Request::getJournal();
                    import ('classes.file.PublicFileManager');
                    $publicFileManager = new PublicFileManager();
                    if ($journal) {
                        $url = Request::getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journal->getId()) . '/' . implode('/', $urlParts) . ($anchor?'#' . $anchor:'');
                    }
                    break;
            }
        }
        return $matchArray[1] . $url . $matchArray[3];
    }

    /**
     * Check if the specified file is a dependent file.
     * @param int $fileId
     * @return boolean
     */
    public function isDependentFile($fileId) {
        if ($this->getStyleFileId() == $fileId) return true;
        
        $images = $this->getImageFiles();
        if (is_array($images)) {
            foreach ($images as $image) {
                if ($image->getFileId() == $fileId) return true;
            }
        }
        return false;
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of associated stylesheet file, if applicable.
     * @return int
     */
    public function getStyleFileId() {
        return $this->getData('styleFileId');
    }

    /**
     * Set ID of associated stylesheet file.
     * @param int $styleFileId
     */
    public function setStyleFileId($styleFileId) {
        return $this->setData('styleFileId', $styleFileId);
    }

    /**
     * Return the stylesheet file associated with this HTML galley, if applicable.
     * @return ArticleFile|null
     */
    public function getStyleFile() {
        return $this->getData('styleFile');
    }

    /**
     * Set the stylesheet file for this HTML galley.
     * @param ArticleFile $styleFile (No & needed)
     */
    public function setStyleFile($styleFile) {
        $this->setData('styleFile', $styleFile);
    }

    /**
     * Return array of image files for this HTML galley.
     * @return array
     */
    public function getImageFiles() {
        $images = $this->getData('images');
        return is_array($images) ? $images : array();
    }

    /**
     * Set array of image files for this HTML galley.
     * @param array $images (No & needed)
     */
    public function setImageFiles($images) {
        return $this->setData('images', $images);
    }
}

?>