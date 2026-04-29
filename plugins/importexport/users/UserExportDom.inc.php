<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/users/UserExportDom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserExportDom
 * @ingroup plugins_importexport_users
 *
 * @brief User plugin DOM functions for export
 */

import('lib.pkp.classes.xml.XMLCustomWriter');

define('USERS_DTD_URL', 'http://pkp.sfu.ca/ojs/dtds/users.dtd');
define('USERS_DTD_ID', '-//PKP/OJS Users XML//EN');

class UserExportDom {

    /**
     * Constructor
     */
    public function __construct() {
        // No parent constructor to call
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserExportDom() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
        return true; // Maintain legacy return behavior (though constructor return values are ignored in 'new')
    }

    /**
     * Export users to XML DOM object.
     *
     * @param object $journal Journal object
     * @param array $users Array of User objects
     * @param array|null $allowedRoles Array of allowed role paths
     * @return object DOMDocument
     */
    public function exportUsers($journal, array $users, ?array $allowedRoles = null) {
        $roleDao = DAORegistry::getDAO('RoleDAO');

        $doc = XMLCustomWriter::createDocument('users', USERS_DTD_ID, USERS_DTD_URL);
        $root = XMLCustomWriter::createElement($doc, 'users');

        foreach ($users as $user) {
            $userNode = XMLCustomWriter::createElement($doc, 'user');

            XMLCustomWriter::createChildWithText($doc, $userNode, 'username', $user->getUserName(), false);
            $passwordNode = XMLCustomWriter::createChildWithText($doc, $userNode, 'password', $user->getPassword());
            XMLCustomWriter::setAttribute($passwordNode, 'encrypted', (string) Config::getVar('security', 'encryption'));
            XMLCustomWriter::createChildWithText($doc, $userNode, 'salutation', $user->getSalutation(), false);
            XMLCustomWriter::createChildWithText($doc, $userNode, 'first_name', $user->getFirstName());
            XMLCustomWriter::createChildWithText($doc, $userNode, 'middle_name', $user->getMiddleName(), false);
            XMLCustomWriter::createChildWithText($doc, $userNode, 'last_name', $user->getLastName());
            XMLCustomWriter::createChildWithText($doc, $userNode, 'initials', $user->getInitials(), false);
            XMLCustomWriter::createChildWithText($doc, $userNode, 'gender', $user->getGender(), false);
            XMLCustomWriter::createChildWithText($doc, $userNode, 'email', $user->getEmail());
            XMLCustomWriter::createChildWithText($doc, $userNode, 'url', $user->getUrl(), false);
            XMLCustomWriter::createChildWithText($doc, $userNode, 'phone', $user->getPhone(), false);
            XMLCustomWriter::createChildWithText($doc, $userNode, 'fax', $user->getFax(), false);
            XMLCustomWriter::createChildWithText($doc, $userNode, 'mailing_address', $user->getMailingAddress(), false);
            XMLCustomWriter::createChildWithText($doc, $userNode, 'country', $user->getCountry(), false);

            $affiliations = $user->getAffiliation(null);
            if (is_array($affiliations)) {
                foreach ($affiliations as $locale => $value) {
                    $affiliationNode = XMLCustomWriter::createChildWithText($doc, $userNode, 'affiliation', $value, false);
                    if ($affiliationNode) {
                        XMLCustomWriter::setAttribute($affiliationNode, 'locale', $locale);
                    }
                    unset($affiliationNode);
                }
            }

            $signatures = $user->getSignature(null);
            if (is_array($signatures)) {
                foreach ($signatures as $locale => $value) {
                    $signatureNode = XMLCustomWriter::createChildWithText($doc, $userNode, 'signature', $value, false);
                    if ($signatureNode) {
                        XMLCustomWriter::setAttribute($signatureNode, 'locale', $locale);
                    }
                    unset($signatureNode);
                }
            }

            import('lib.pkp.classes.user.InterestManager');
            $interestManager = new InterestManager();
            $interests = $interestManager->getInterestsForUser($user);
            if (is_array($interests)) {
                foreach ($interests as $interest) {
                    XMLCustomWriter::createChildWithText($doc, $userNode, 'interests', $interest, false);
                }
            }

            $gossips = $user->getGossip(null);
            if (is_array($gossips)) {
                foreach ($gossips as $locale => $value) {
                    $gossipNode = XMLCustomWriter::createChildWithText($doc, $userNode, 'gossip', $value, false);
                    if ($gossipNode) {
                        XMLCustomWriter::setAttribute($gossipNode, 'locale', $locale);
                    }
                    unset($gossipNode);
                }
            }

            $biographies = $user->getBiography(null);
            if (is_array($biographies)) {
                foreach ($biographies as $locale => $value) {
                    $biographyNode = XMLCustomWriter::createChildWithText($doc, $userNode, 'biography', $value, false);
                    if ($biographyNode) {
                        XMLCustomWriter::setAttribute($biographyNode, 'locale', $locale);
                    }
                    unset($biographyNode);
                }
            }

            XMLCustomWriter::createChildWithText($doc, $userNode, 'locales', implode(':', $user->getLocales()), false);
            
            $roles = $roleDao->getRolesByUserId($user->getId(), $journal->getId());
            foreach ($roles as $role) {
                $rolePath = $role->getRolePath();
                if ($allowedRoles !== null && !in_array($rolePath, $allowedRoles)) {
                    continue;
                }
                $roleNode = XMLCustomWriter::createElement($doc, 'role');
                XMLCustomWriter::setAttribute($roleNode, 'type', $rolePath);
                XMLCustomWriter::appendChild($userNode, $roleNode);
                unset($roleNode);
            }

            XMLCustomWriter::appendChild($root, $userNode);
        }

        XMLCustomWriter::appendChild($doc, $root);

        return $doc;
    }
}

?>