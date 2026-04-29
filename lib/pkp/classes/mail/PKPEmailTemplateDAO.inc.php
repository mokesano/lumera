<?php
declare(strict_types=1);

/**
 * @file classes/mail/PKPEmailTemplateDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPEmailTemplateDAO
 * @ingroup mail
 * @see PKPEmailTemplate
 *
 * @brief Operations for retrieving and modifying Email Template objects.
 */

class PKPEmailTemplateDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPEmailTemplateDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPEmailTemplateDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Retrieve a base email template by key.
     * @param $emailKey string
     * @param $assocType int
     * @param $assocId int
     * @return BaseEmailTemplate|null
     */
    public function getBaseEmailTemplate($emailKey, $assocType, $assocId) {
        $result = $this->retrieve(
            'SELECT    d.email_key,
                d.can_edit,
                d.can_disable,
                COALESCE(e.enabled, 1) AS enabled,
                e.email_id,
                e.assoc_type,
                e.assoc_id,
                d.from_role_id,
                d.to_role_id
            FROM    email_templates_default d
                LEFT JOIN email_templates e ON (d.email_key = e.email_key AND e.assoc_type = ? AND e.assoc_id = ?)
            WHERE    d.email_key = ?',
            array((int) $assocType, (int) $assocId, $emailKey)
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnBaseEmailTemplateFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Retrieve localized email template by key.
     * @param $emailKey string
     * @param $assocType int
     * @param $assocId int
     * @return LocaleEmailTemplate|null
     */
    public function getLocaleEmailTemplate($emailKey, $assocType, $assocId) {
        $result = $this->retrieve(
            'SELECT    d.email_key,
                d.can_edit,
                d.can_disable,
                COALESCE(e.enabled, 1) AS enabled,
                e.email_id,
                e.assoc_type,
                e.assoc_id,
                d.from_role_id,
                d.to_role_id
            FROM    email_templates_default d
                LEFT JOIN email_templates e ON (d.email_key = e.email_key AND e.assoc_type = ? AND e.assoc_id = ?)
            WHERE    d.email_key = ?',
            array((int) $assocType, (int) $assocId, $emailKey)
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnLocaleEmailTemplateFromRow($result->GetRowAssoc(false));
        } else {
            $result->Close();
            unset($result);

            // Check to see if there's a custom email template. This is done in PHP to avoid
            // having to do a full outer join or union in SQL.
            $result = $this->retrieve(
                'SELECT    e.email_key,
                    1 AS can_edit,
                    1 AS can_disable,
                    e.enabled,
                    e.email_id,
                    e.assoc_type,
                    e.assoc_id,
                    NULL AS from_role_id,
                    NULL AS to_role_id
                FROM    email_templates e
                    LEFT JOIN email_templates_default d ON (e.email_key = d.email_key)
                WHERE    d.email_key IS NULL AND
                    e.assoc_type = ? AND
                    e.assoc_id = ? AND
                    e.email_key = ?',
                array((int) $assocType, (int) $assocId, $emailKey)
            );
            if ($result->RecordCount() != 0) {
                $returner = $this->_returnLocaleEmailTemplateFromRow($result->GetRowAssoc(false));
            }
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Retrieve an email template by key.
     * @param $emailKey string
     * @param $locale string
     * @param $assocType int
     * @param $assocId int
     * @return EmailTemplate|null
     */
    public function getEmailTemplate($emailKey, $locale, $assocType, $assocId) {
        $primaryLocale = AppLocale::getPrimaryLocale();

        $result = $this->retrieve(
            'SELECT    COALESCE(edl.subject, ddl.subject, edpl.subject, ddpl.subject) AS subject,
                COALESCE(edl.body, ddl.body, edpl.body, ddpl.body) AS body,
                COALESCE(e.enabled, 1) AS enabled,
                d.email_key, d.can_edit, d.can_disable,
                e.assoc_type, e.assoc_id, e.email_id,
                COALESCE(ddl.locale, ddpl.locale) AS locale,
                d.from_role_id, d.to_role_id
            FROM    email_templates_default d
                LEFT JOIN email_templates_default_data ddpl ON (ddpl.email_key = d.email_key AND ddpl.locale = ?)
                LEFT JOIN email_templates_default_data ddl ON (ddl.email_key = d.email_key AND ddl.locale = ?)
                LEFT JOIN email_templates e ON (d.email_key = e.email_key AND e.assoc_type = ? AND e.assoc_id = ?)
                LEFT JOIN email_templates_data edpl ON (edpl.email_key = e.email_key AND edpl.assoc_type = e.assoc_type AND edpl.assoc_id = e.assoc_id AND edpl.locale = ?)
                LEFT JOIN email_templates_data edl ON (edl.email_key = e.email_key AND edl.assoc_type = e.assoc_type AND edl.assoc_id = e.assoc_id AND edl.locale = ?)
            WHERE    d.email_key = ?',
            array($primaryLocale, $locale, (int) $assocType, (int) $assocId, $primaryLocale, $locale, $emailKey)
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnEmailTemplateFromRow($result->GetRowAssoc(false));
            $returner->setCustomTemplate(false);
        } else {
            $result->Close();
            unset($result);

            // Check to see if there's a custom email template. This is done in PHP to avoid
            // having to do a full outer join or union in SQL.
            $result = $this->retrieve(
                'SELECT    ed.subject,
                    ed.body,
                    1 AS enabled,
                    e.email_key,
                    1 AS can_edit,
                    1 AS can_disable,
                    e.assoc_type,
                    e.assoc_id,
                    e.email_id,
                    ed.locale,
                    NULL AS from_role_id,
                    NULL AS to_role_id
                FROM    email_templates e
                    LEFT JOIN email_templates_data ed ON (ed.email_key = e.email_key AND ed.assoc_type = e.assoc_type AND ed.assoc_id = e.assoc_id)
                    LEFT JOIN email_templates_default d ON (e.email_key = d.email_key)
                WHERE    d.email_key IS NULL AND
                    e.assoc_type = ? AND
                    e.assoc_id = ? AND
                    e.email_key = ? AND
                    ed.locale = ?',
                array((int) $assocType, (int) $assocId, $emailKey, $locale)
            );
            if ($result->RecordCount() != 0) {
                $returner = $this->_returnEmailTemplateFromRow($result->GetRowAssoc(false));
                $returner->setCustomTemplate(true);
            }
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Internal function to return an email template object from a row.
     * @param $row array
     * @return BaseEmailTemplate
     */
    public function _returnBaseEmailTemplateFromRow(&$row) {
        $emailTemplate = new BaseEmailTemplate();
        $emailTemplate->setEmailId($row['email_id']);
        $emailTemplate->setAssocType($row['assoc_type']);
        $emailTemplate->setAssocId($row['assoc_id']);
        $emailTemplate->setEmailKey($row['email_key']);
        $emailTemplate->setEnabled($row['enabled'] == null ? 1 : $row['enabled']);
        $emailTemplate->setCanDisable($row['can_disable']);
        $emailTemplate->setFromRoleId($row['from_role_id']);
        $emailTemplate->setToRoleId($row['to_role_id']);

        HookRegistry::dispatch('EmailTemplateDAO::_returnBaseEmailTemplateFromRow', array(&$emailTemplate, &$row));

        return $emailTemplate;
    }

    /**
     * Internal function to return an email template object from a row.
     * @param $row array
     * @return LocaleEmailTemplate
     */
    public function _returnLocaleEmailTemplateFromRow(&$row) {
        $emailTemplate = new LocaleEmailTemplate();
        $emailTemplate->setEmailId($row['email_id']);
        $emailTemplate->setAssocType($row['assoc_type']);
        $emailTemplate->setAssocId($row['assoc_id']);
        $emailTemplate->setEmailKey($row['email_key']);
        $emailTemplate->setEnabled($row['enabled'] == null ? 1 : $row['enabled']);
        $emailTemplate->setCanDisable($row['can_disable']);
        $emailTemplate->setFromRoleId($row['from_role_id']);
        $emailTemplate->setToRoleId($row['to_role_id']);

        $emailTemplate->setCustomTemplate(false);

        if (!HookRegistry::dispatch('EmailTemplateDAO::_returnLocaleEmailTemplateFromRow', array(&$emailTemplate, &$row))) {
            $result = $this->retrieve(
                'SELECT    dd.locale,
                    dd.description,
                    COALESCE(ed.subject, dd.subject) AS subject,
                    COALESCE(ed.body, dd.body) AS body
                FROM    email_templates_default_data dd
                    LEFT JOIN email_templates_data ed ON (dd.email_key = ed.email_key AND dd.locale = ed.locale AND ed.assoc_type = ? AND ed.assoc_id = ?)
                WHERE    dd.email_key = ?',
                array((int) $row['assoc_type'], (int) $row['assoc_id'], $row['email_key'])
            );

            while (!$result->EOF) {
                $dataRow = $result->GetRowAssoc(false);
                $emailTemplate->addLocale($dataRow['locale']);
                $emailTemplate->setSubject($dataRow['locale'], $dataRow['subject']);
                $emailTemplate->setBody($dataRow['locale'], $dataRow['body']);
                $emailTemplate->setDescription($dataRow['locale'], $dataRow['description']);
                $result->MoveNext();
            }
            $result->Close();
            unset($result);

            // Retrieve custom email contents as well; this is done in PHP to avoid
            // using a SQL outer join or union.
            $result = $this->retrieve(
                'SELECT    ed.locale,
                    ed.subject,
                    ed.body
                FROM    email_templates_data ed
                    LEFT JOIN email_templates_default_data dd ON (ed.email_key = dd.email_key AND dd.locale = ed.locale)
                WHERE    ed.assoc_type = ? AND
                    ed.assoc_id = ? AND
                    ed.email_key = ? AND
                    dd.email_key IS NULL',
                array((int) $row['assoc_type'], (int) $row['assoc_id'], $row['email_key'])
            );

            while (!$result->EOF) {
                $dataRow = $result->GetRowAssoc(false);
                $emailTemplate->addLocale($dataRow['locale']);
                $emailTemplate->setSubject($dataRow['locale'], $dataRow['subject']);
                $emailTemplate->setBody($dataRow['locale'], $dataRow['body']);
                $result->MoveNext();

                $emailTemplate->setCustomTemplate(true);
            }

            $result->Close();
            unset($result);
        }

        return $emailTemplate;
    }

    /**
     * Internal function to return an email template object from a row.
     * @param $row array
     * @return EmailTemplate
     */
    public function _returnEmailTemplateFromRow(&$row, $isCustomTemplate=null) {
        $emailTemplate = new EmailTemplate();
        $emailTemplate->setEmailId($row['email_id']);
        $emailTemplate->setAssocType($row['assoc_type']);
        $emailTemplate->setAssocId($row['assoc_id']);
        $emailTemplate->setEmailKey($row['email_key']);
        $emailTemplate->setLocale($row['locale']);
        $emailTemplate->setSubject($row['subject']);
        $emailTemplate->setBody($row['body']);
        $emailTemplate->setEnabled($row['enabled'] == null ? 1 : $row['enabled']);
        $emailTemplate->setCanDisable($row['can_disable']);
        $emailTemplate->setFromRoleId($row['from_role_id']);
        $emailTemplate->setToRoleId($row['to_role_id']);

        if ($isCustomTemplate !== null) {
            $emailTemplate->setCustomTemplate($isCustomTemplate);
        }

        HookRegistry::dispatch('EmailTemplateDAO::_returnEmailTemplateFromRow', array(&$emailTemplate, &$row));

        return $emailTemplate;
    }

    /**
     * Insert a new base email template.
     * @param $emailTemplate BaseEmailTemplate
     */
    public function insertBaseEmailTemplate($emailTemplate) {
        $this->update(
            'INSERT INTO email_templates
                (assoc_type, assoc_id, email_key, enabled)
                VALUES
                (?, ?, ?, ?)',
            array(
                (int) $emailTemplate->getAssocType(),
                (int) $emailTemplate->getAssocId(),
                $emailTemplate->getEmailKey(),
                $emailTemplate->getEnabled() == null ? 0 : 1
            )
        );
        $emailTemplate->setEmailId($this->getInsertEmailId());
        return $emailTemplate->getEmailId();
    }

    /**
     * Update an existing base email template.
     * @param $emailTemplate BaseEmailTemplate
     */
    public function updateBaseEmailTemplate($emailTemplate) {
        return $this->update(
            'UPDATE    email_templates
            SET    enabled = ?
            WHERE    email_id = ?',
            array(
                $emailTemplate->getEnabled() == null ? 0 : 1,
                (int) $emailTemplate->getEmailId()
            )
        );
    }

    /**
     * Insert a new localized email template.
     * @param $emailTemplate LocaleEmailTemplate
     */
    public function insertLocaleEmailTemplate($emailTemplate) {
        $this->insertBaseEmailTemplate($emailTemplate);
        return $this->updateLocaleEmailTemplateData($emailTemplate);
    }

    /**
     * Update an existing localized email template.
     * @param $emailTemplate LocaleEmailTemplate
     */
    public function updateLocaleEmailTemplate($emailTemplate) {
        $this->updateBaseEmailTemplate($emailTemplate);
        return $this->updateLocaleEmailTemplateData($emailTemplate);
    }

    /**
     * Insert/update locale-specific email template data.
     * @param $emailTemplate LocaleEmailTemplate
     */
    public function updateLocaleEmailTemplateData($emailTemplate) {
        foreach ($emailTemplate->getLocales() as $locale) {
            $result = $this->retrieve(
                'SELECT    COUNT(*)
                FROM    email_templates_data
                WHERE    email_key = ? AND
                    locale = ? AND
                    assoc_type = ? AND
                    assoc_id = ?',
                array(
                    $emailTemplate->getEmailKey(),
                    $locale,
                    (int) $emailTemplate->getAssocType(),
                    (int) $emailTemplate->getAssocId()
                )
            );

            if ($result->fields[0] == 0) {
                $this->update(
                    'INSERT INTO email_templates_data
                    (email_key, locale, assoc_type, assoc_id, subject, body)
                    VALUES
                    (?, ?, ?, ?, ?, ?)',
                    array(
                        $emailTemplate->getEmailKey(),
                        $locale,
                        (int) $emailTemplate->getAssocType(),
                        (int) $emailTemplate->getAssocId(),
                        $emailTemplate->getSubject($locale),
                        $emailTemplate->getBody($locale)
                    )
                );

            } else {
                $this->update(
                    'UPDATE    email_templates_data
                    SET    subject = ?,
                        body = ?
                    WHERE    email_key = ? AND
                        locale = ? AND
                        assoc_type = ? AND
                        assoc_id = ?',
                    array(
                        $emailTemplate->getSubject($locale),
                        $emailTemplate->getBody($locale),
                        $emailTemplate->getEmailKey(),
                        $locale,
                        (int) $emailTemplate->getAssocType(),
                        (int) $emailTemplate->getAssocId()
                    )
                );
            }

            $result->Close();
            unset($result);
        }
    }

    /**
     * Delete an email template by key.
     * @param $emailKey string
     * @param $assocType int
     * @param $assocId int
     */
    public function deleteEmailTemplateByKey($emailKey, $assocType, $assocId) {
        $this->update(
            'DELETE FROM email_templates_data WHERE email_key = ? AND assoc_type = ? AND assoc_id = ?',
            array($emailKey, (int) $assocType, (int) $assocId)
        );
        return $this->update(
            'DELETE FROM email_templates WHERE email_key = ? AND assoc_type = ? AND assoc_id = ?',
            array($emailKey, (int) $assocType, (int) $assocId)
        );
    }

    /**
     * Retrieve all email templates.
     * @param $locale string
     * @param $assocType int
     * @param $assocId int
     * @param $rangeInfo object optional
     * @return array Email templates
     */
    public function getEmailTemplates($locale, $assocType, $assocId, $rangeInfo = null) {
        $emailTemplates = array();

        $result = $this->retrieveRange(
            'SELECT    COALESCE(ed.subject, dd.subject) AS subject,
                COALESCE(ed.body, dd.body) AS body,
                COALESCE(e.enabled, 1) AS enabled,
                d.email_key, d.can_edit, d.can_disable,
                e.assoc_type, e.assoc_id, e.email_id,
                dd.locale,
                d.from_role_id, d.to_role_id
            FROM    email_templates_default d
                LEFT JOIN email_templates_default_data dd ON (dd.email_key = d.email_key)
                LEFT JOIN email_templates e ON (d.email_key = e.email_key AND e.assoc_type = ? AND e.assoc_id = ?)
                LEFT JOIN email_templates_data ed ON (ed.email_key = e.email_key AND ed.assoc_type = e.assoc_type AND ed.assoc_id = e.assoc_id AND ed.locale = dd.locale)
            WHERE    dd.locale = ?',
            array((int) $assocType, (int) $assocId, $locale),
            $rangeInfo
        );

        while (!$result->EOF) {
            // Wizdam Fix: Manually fetch row association
            $row = $result->GetRowAssoc(false);
            $emailTemplates[] = $this->_returnEmailTemplateFromRow($row, false);
            $result->MoveNext();
        }
        $result->Close();
        unset($result);

        // Fetch custom email templates as well; this is done in PHP
        // to avoid a union or full outer join call in SQL.
        $result = $this->retrieve(
            'SELECT    ed.subject,
                ed.body,
                e.enabled,
                e.email_key,
                1 AS can_edit,
                1 AS can_disable,
                e.assoc_type,
                e.assoc_id,
                e.email_id,
                ed.locale,
                NULL AS from_role_id,
                NULL AS to_role_id
            FROM    email_templates e
                LEFT JOIN email_templates_data ed ON (e.email_key = ed.email_key AND ed.assoc_type = e.assoc_type AND ed.assoc_id = e.assoc_id AND ed.locale = ?)
                LEFT JOIN email_templates_default d ON (e.email_key = d.email_key)
            WHERE    e.assoc_type = ? AND
                e.assoc_id = ? AND
                d.email_key IS NULL',
            array($locale, (int) $assocType, (int) $assocId)
        );

        while (!$result->EOF) {
            // Wizdam Fix: Manually fetch row association
            $row = $result->GetRowAssoc(false);
            $emailTemplates[] = $this->_returnEmailTemplateFromRow($row, true);
            $result->MoveNext();
        }
        $result->Close();
        unset($result);

        // Sort all templates by email key.
        // Wizdam Fix: Replaced create_function with anonymous function
        usort($emailTemplates, function($t1, $t2) {
            return strcmp($t1->getEmailKey(), $t2->getEmailKey());
        });

        return $emailTemplates;
    }

    /**
     * Get the ID of the last inserted email template.
     * @return int
     */
    public function getInsertEmailId() {
        return $this->getInsertId('email_templates', 'emailId');
    }

    /**
     * Delete all email templates for a specific journal/conference/...
     * @param $assocType int
     * @param $assocId int
     */
    public function deleteEmailTemplatesByAssoc($assocType, $assocId) {
        $this->update(
            'DELETE FROM email_templates_data WHERE assoc_type = ? AND assoc_id = ?', array((int) $assocType, (int) $assocId)
        );
        return $this->update(
            'DELETE FROM email_templates WHERE assoc_type = ? AND assoc_id = ?', array((int) $assocType, (int) $assocId)
        );
    }

    /**
     * Delete all email templates for a specific locale.
     * @param $locale string
     */
    public function deleteEmailTemplatesByLocale($locale) {
        $this->update(
            'DELETE FROM email_templates_data WHERE locale = ?', $locale
        );
    }

    /**
     * Delete all default email templates for a specific locale.
     * @param $locale string
     */
    public function deleteDefaultEmailTemplatesByLocale($locale) {
        $this->update(
            'DELETE FROM email_templates_default_data WHERE locale = ?', $locale
        );
    }

    /**
     * Check if a template exists with the given email key for a journal/
     * conference/...
     * @param $emailKey string
     * @param $assocType int optional
     * @param $assocId int optional
     * @return boolean
     */
    public function templateExistsByKey($emailKey, $assocType = null, $assocId = null) {
        if ($assocType !== null) {
            $result = $this->retrieve(
                'SELECT    COUNT(*)
                FROM    email_templates
                WHERE    email_key = ? AND
                    assoc_type = ? AND
                    assoc_id = ?',
                array(
                    $emailKey,
                    (int) $assocType,
                    (int) $assocId
                )
            );
            if (isset($result->fields[0]) && $result->fields[0] != 0) {
                $result->Close();
                unset($result);
                return true;
            }

            $result->Close();
            unset($result);
        }

        $result = $this->retrieve(
            'SELECT COUNT(*)
                FROM email_templates_default
                WHERE email_key = ?',
            $emailKey
        );
        if (isset($result->fields[0]) && $result->fields[0] != 0) {
            $returner = true;
        } else {
            $returner = false;
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Check if a custom template exists with the given email key for a
     * journal/conference/...
     * @param $emailKey string
     * @param $assocType int
     * @param $assocId int
     * @return boolean
     */
    public function customTemplateExistsByKey($emailKey, $assocType, $assocId) {
        $result = $this->retrieve(
            'SELECT    COUNT(*)
            FROM    email_templates e
                LEFT JOIN email_templates_default d ON (e.email_key = d.email_key)
            WHERE    e.email_key = ? AND
                d.email_key IS NULL AND
                e.assoc_type = ? AND
                e.assoc_id = ?',
            array(
                $emailKey,
                (int) $assocType,
                (int) $assocId
            )
        );
        $returner = (isset($result->fields[0]) && $result->fields[0] != 0);

        $result->Close();
        unset($result);

        return $returner;
    }

    public function getMainEmailTemplatesFilename() {
        return 'registry/emailTemplates.xml';
    }

    public function getMainEmailTemplateDataFilename($locale = null) {
        if ($locale !== null && !PKPLocale::isLocaleValid($locale)) return null;
        if ($locale === null) $locale = '{$installedLocale}';
        return "locale/$locale/emailTemplates.xml";
    }

    /**
     * Install email templates from an XML file.
     * NOTE: Uses qstr instead of ? bindings so that SQL can be fetched
     * rather than executed.
     * @param $templatesFile string Filename to install
     * @param $returnSql boolean Whether or not to return SQL rather than
     * executing it
     * @param $emailKey string Optional name of single email key to install,
     * skipping others
     * @param $skipExisting boolean If true, do not install email templates
     * that already exist in the database
     * @return array|bool
     */
    public function installEmailTemplates($templatesFile, $returnSql = false, $emailKey = null, $skipExisting = false) {
        $xmlDao = new XMLDAO();
        $sql = array();
        $data = $xmlDao->parseStruct($templatesFile, array('email'));
        if (!isset($data['email'])) return false;
        foreach ($data['email'] as $entry) {
            $attrs = $entry['attributes'];
            if ($emailKey && $emailKey != $attrs['key']) continue;
            if ($skipExisting && $this->templateExistsByKey($attrs['key'])) continue;
            $dataSource = $this->getDataSource();
            $sql[] = 'INSERT INTO email_templates_default
                (email_key, can_disable, can_edit, from_role_id, to_role_id)
                VALUES
                (' .
                $dataSource->qstr($attrs['key']) . ', ' .
                ($attrs['can_disable']?1:0) . ', ' .
                ($attrs['can_edit']?1:0) . ', ' .
                (isset($attrs['from_role_id'])?((int) $attrs['from_role_id']):'null') . ', ' .
                (isset($attrs['to_role_id'])?((int) $attrs['to_role_id']):'null') .
                ")";
            if (!$returnSql) {
                $this->update(array_shift($sql));
            }
        }
        if ($returnSql) return $sql;
        return true;
    }

    /**
     * Install email template localized data from an XML file.
     * NOTE: Uses qstr instead of ? bindings so that SQL can be fetched
     * rather than executed.
     * @param $templateDataFile string Filename to install
     * @param $returnSql boolean Whether or not to return SQL rather than
     * executing it
     * @param $emailKey string If specified, the key of the single template
     * to install (otherwise all are installed)
     * @return array|bool
     */
    public function installEmailTemplateData($templateDataFile, $returnSql = false, $emailKey = null) {
        $xmlDao = new XMLDAO();
        $sql = array();
        $data = $xmlDao->parse($templateDataFile, array('email_texts', 'email_text', 'subject', 'body', 'description'));
        if (!$data) return false;
        $locale = $data->getAttribute('locale');

        foreach ($data->getChildren() as $emailNode) {
            if ($emailKey && $emailKey != $emailNode->getAttribute('key')) continue;
            $dataSource = $this->getDataSource();
            $sql[] = 'DELETE FROM email_templates_default_data WHERE email_key = ' . $dataSource->qstr($emailNode->getAttribute('key')) . ' AND locale = ' . $dataSource->qstr($locale);
            if (!$returnSql) {
                $this->update(array_shift($sql));
            }

            $sql[] = 'INSERT INTO email_templates_default_data
                (email_key, locale, subject, body, description)
                VALUES
                (' .
                $dataSource->qstr($emailNode->getAttribute('key')) . ', ' .
                $dataSource->qstr($locale) . ', ' .
                $dataSource->qstr($emailNode->getChildValue('subject')) . ', ' .
                $dataSource->qstr($emailNode->getChildValue('body')) . ', ' .
                $dataSource->qstr($emailNode->getChildValue('description')) .
                ")";
            if (!$returnSql) {
                $this->update(array_shift($sql));
            }
        }
        if ($returnSql) return $sql;
        return true;
    }
}

?>