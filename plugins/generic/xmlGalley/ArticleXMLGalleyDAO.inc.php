<?php
declare(strict_types=1);

/**
 * @file plugins/generic/xmlGalley/ArticleXMLGalleyDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleXMLGalleyDAO
 * @ingroup plugins_generic_xmlGalley
 *
 * @brief Extended DAO methods for XML-derived galleys
 * * MODERNIZED & BUG #5152 RESOLVED FOR WIZDAM FORK
 */

import('classes.article.ArticleGalleyDAO');

class ArticleXMLGalleyDAO extends ArticleGalleyDAO {
    
    /** @var string Name of parent plugin */
    public string $parentPluginName = '';

    /**
     * Constructor
     * @param string $parentPluginName
     */
    public function __construct(string $parentPluginName) {
        $this->parentPluginName = $parentPluginName;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     * @param string $parentPluginName
     */
    public function ArticleXMLGalleyDAO(string $parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Internal function to return an ArticleXMLGalley object from an XML galley Id
     * @param int $xmlGalleyId The unique ID of the derived galley
     * @param int|null $articleId
     * @return object|null
     */
    public function _getXMLGalleyFromId(int $xmlGalleyId, ?int $articleId = null): ?object {
        $params = [$xmlGalleyId];
        if ($articleId !== null) $params[] = $articleId;

        // WIZDAM FIX: Query using x.xml_galley_id (Primary Key) instead of x.galley_id
        $result = $this->retrieve(
            'SELECT    x.*,
                x.galley_type AS file_type,
                g.file_id,
                g.html_galley,
                g.style_file_id,
                g.seq,
                g.locale,
                g.remote_url,
                a.file_name,
                a.original_file_name,
                a.file_stage,
                a.file_type,
                a.file_size,
                a.date_uploaded,
                a.date_modified
            FROM    article_xml_galleys x
                LEFT JOIN article_galleys g ON (x.galley_id = g.galley_id)
                LEFT JOIN article_files a ON (g.file_id = a.file_id)
            WHERE    x.xml_galley_id = ?
                ' . ($articleId !== null ? ' AND x.article_id = ?' : ''),
            $params
        );

        $xmlGalley = null;

        if ($result) {
            if ($result->RecordCount() != 0) {
                $articleGalley = $this->_returnGalleyFromRow($result->GetRowAssoc(false));

                $xmlGalleyPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
                $xmlGalley = $xmlGalleyPlugin->_returnXMLGalleyFromArticleGalley($articleGalley);
            }
            $result->Close();
        }
        unset($result);

        return $xmlGalley;
    }

    /**
     * Append XML-derived galleys (eg. PDF) to the list of galleys for an article
     * @param string $hookName
     * @param array $args
     * @return boolean
     */
    public function appendXMLGalleys(string $hookName, array $args): bool {
        $galleys =& $args[0]; 
        $articleId = $args[1];

        $xmlGalleyPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $journal = Request::getJournal();

        foreach ($galleys as $key => $galley) {
            
            // if the galley is an XML galley, append XML-derived galleys
            if ($galley->getFileType() == "text/xml" || $galley->getFileType() == "application/xml") {

                // WIZDAM FIX: Retrieve xml_galley_id as well to allow multiple formats
                $result = $this->retrieve(
                    'SELECT    xml_galley_id
                    FROM    article_xml_galleys x
                    WHERE    x.galley_id = ? AND
                        x.article_id = ?
                    ORDER BY xml_galley_id',
                    [(int) $galley->getId(), (int) $articleId]
                );

                if ($result) {
                    while (!$result->EOF) {
                        $row = $result->GetRowAssoc(false);
                        
                        // Load the virtual galley using its unique xml_galley_id
                        $xmlGalley = $this->_getXMLGalleyFromId((int) $row['xml_galley_id'], (int) $articleId);

                        // WIZDAM FIX: Bug #5152 Resolved. Safe to assign unique ID.
                        if ($xmlGalley) {
                            $xmlGalley->setId((int) $row['xml_galley_id']);

                            // Append PDF galleys if the correct plugin settings are set
                            if ( ($xmlGalleyPlugin->getSetting($journal->getId(), 'nlmPDF') == 1 
                                    && $xmlGalley->isPdfGalley()) || $xmlGalley->isHTMLGalley()) {
                                array_push($galleys, $xmlGalley);
                            }
                        }
                        
                        $result->moveNext();
                    }
                    $result->Close();
                }
                unset($result);

                // Optional: hide source XML galley
                // if (isset($xmlGalley)) unset($galleys[$key]);
            }
        }

        return true;
    }

    /**
     * Insert XML-derived galleys into article_xml_galleys
     * when an XML galley is created
     * @param string $hookName
     * @param array $args
     * @return boolean
     */
    public function insertXMLGalleys(string $hookName, array $args): bool {
        $galley = $args[0];
        $galleyId = $args[1];

        // If the galley is an XML file, then insert rows in the article_xml_galleys table
        if ($galley->getLabel() == "XML") {

            // 1. Create an XHTML galley
            $this->update(
                'INSERT INTO article_xml_galleys
                    (galley_id, article_id, label, galley_type)
                    VALUES
                    (?, ?, ?, ?)',
                [
                    (int) $galleyId,
                    (int) $galley->getArticleId(),
                    'XHTML',
                    'application/xhtml+xml'
                ]
            );

            // WIZDAM FIX: Bug #5152 Resolved. We can now safely generate PDF entries
            // because they will get their own auto-incremented xml_galley_id in the DB.
            $journal = Request::getJournal();
            $xmlGalleyPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);

            if ($xmlGalleyPlugin->getSetting($journal->getId(), 'nlmPDF') == 1 && 
                $xmlGalleyPlugin->getSetting($journal->getId(), 'XSLstylesheet') == 'NLM' ) {

                // 2. Create a PDF galley
                $this->update(
                    'INSERT INTO article_xml_galleys
                        (galley_id, article_id, label, galley_type)
                        VALUES
                        (?, ?, ?, ?)',
                    [
                        (int) $galleyId,
                        (int) $galley->getArticleId(),
                        'PDF',
                        'application/pdf'
                    ]
                );
            }
            return true;
        }
        return false;
    }

    /**
     * Delete XML-derived galleys from article_xml_galleys 
     * when the XML galley is deleted
     * @param string $hookName
     * @param array $args
     */
    public function deleteXMLGalleys(string $hookName, array $args): void {
        $galleyId = $args[0]; // This is the Source XML Galley ID
        $articleId = isset($args[1]) ? $args[1] : null;

        // Cascade delete all derived formats attached to this source galley_id
        if ($articleId !== null) {
            $this->update(
                'DELETE FROM article_xml_galleys WHERE galley_id = ? AND article_id = ?',
                [(int) $galleyId, (int) $articleId]
            );
        } else {
            $this->update(
                'DELETE FROM article_xml_galleys WHERE galley_id = ?', 
                (int) $galleyId
            );
        }
    }

    /**
     * Increment views on XML-derived galleys
     * @param string $hookName
     * @param array $args
     * @return boolean
     */
    public function incrementXMLViews(string $hookName, array $args): bool {
        $xmlGalleyId = $args[0];

        // WIZDAM FIX: Increment based on the unique derived ID
        return (bool) $this->update(
            'UPDATE article_xml_galleys SET views = views + 1 WHERE xml_galley_id = ?',
            (int) $xmlGalleyId
        );
    }
}
?>