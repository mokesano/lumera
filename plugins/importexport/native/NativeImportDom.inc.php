<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/native/NativeImportDom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NativeImportDom
 * @ingroup plugins_importexport_native
 *
 * @brief Native import/export plugin DOM functions for import
 * [WIZDAM EDITION] Refactored for PHP 8.0+ (Static Methods, Strict Types, Reference Cleanup)
 */

import('lib.pkp.classes.xml.XMLCustomWriter');

class NativeImportDom {
    
    /**
     * Import articles.
     * @param Journal $journal
     * @param array $nodes
     * @param Issue $issue
     * @param Section $section
     * @param array $articles Output accumulator
     * @param array $errors Output accumulator
     * @param User $user
     * @param bool $isCommandLine
     * @return bool
     */
    public static function importArticles($journal, $nodes, $issue, $section, &$articles, &$errors, $user, $isCommandLine) {
        $articles = [];
        $dependentItems = [];
        $hasErrors = false;
        
        foreach ($nodes as $node) {
            $article = null;
            $publishedArticle = null;
            $articleErrors = [];
            
            $result = self::handleArticleNode($journal, $node, $issue, $section, $article, $publishedArticle, $articleErrors, $user, $isCommandLine, $dependentItems);
            
            if ($result) {
                $articles[] = $article;
            } else {
                $errors = array_merge($errors, $articleErrors);
                $hasErrors = true;
            }
        }
        
        if ($hasErrors) {
            self::cleanupFailure($dependentItems);
            return false;
        }
        return true;
    }

    /**
     * Import a single article.
     *
     * @param Journal $journal
     * @param XMLNode $node
     * @param Issue $issue
     * @param Section $section
     * @param Article &$article Output article
     * @param array &$errors Output errors
     * @param User $user
     * @param bool $isCommandLine
     *
     * @return bool
     */
    public static function importArticle($journal, $node, $issue, $section, &$article, &$errors, $user, $isCommandLine) {
        $dependentItems = [];
        $publishedArticle = null;
        
        $result = self::handleArticleNode($journal, $node, $issue, $section, $article, $publishedArticle, $errors, $user, $isCommandLine, $dependentItems);
        
        if (!$result) {
            self::cleanupFailure($dependentItems);
        }
        return $result;
    }

    /**
     * Import multiple issues.
     *
     * @param Journal $journal
     * @param array $issueNodes
     * @param array &$issues Output issues
     * @param array &$errors Output errors
     * @param User $user
     * @param bool $isCommandLine
     *
     * @return bool
     */
    public static function importIssues($journal, $issueNodes, &$issues, &$errors, $user, $isCommandLine) {
        $dependentItems = [];
        $errors = [];
        $issues = [];
        $hasErrors = false;
        
        foreach ($issueNodes as $issueNode) {
            $issue = null;
            $issueErrors = [];
            
            $result = self::importIssue($journal, $issueNode, $issue, $issueErrors, $user, $isCommandLine, $dependentItems, false);
            
            if ($result) {
                // Success. Add this issue to the list.
                $issues[] = $issue;
            } else {
                // Failure. Record the errors.
                $errors = array_merge($errors, $issueErrors);
                $hasErrors = true;
            }
        }
        
        if ($hasErrors) {
            self::cleanupFailure($dependentItems);
            return false;
        }
        return true;
    }

    /**
     * Import a single issue.
     *
     * @param Journal $journal
     * @param XMLNode $issueNode
     * @param Issue &$issue Output issue
     * @param array &$errors Output errors
     * @param User $user
     * @param bool $isCommandLine
     * @param array &$dependentItems Output dependent items
     * @param bool $cleanupErrors Whether to clean up on error
     *
     * @return bool
     */
    public static function importIssue($journal, $issueNode, &$issue, &$errors, $user, $isCommandLine, &$dependentItems, $cleanupErrors = true) {
        $errors = [];
        $issue = null;
        $hasErrors = false;

        $issueDao = DAORegistry::getDAO('IssueDAO'); /** @var IssueDAO $issueDao */
        $issue = new Issue();
        $issue->setJournalId($journal->getId());

        $journalSupportedLocales = array_keys($journal->getSupportedLocaleNames());
        $journalPrimaryLocale = $journal->getPrimaryLocale();

        /* --- Set IDs --- */
        // We pass null for article because we are in issue context
        $dummyArticle = null;
        if (!self::handlePubIds($issueNode, $issue, $journal, $issue, $dummyArticle, $errors)) {
            $hasErrors = true;
        }

        /* --- Set title, description, volume, number, and year --- */

        $titleExists = false;
        for ($index=0; ($node = $issueNode->getChildByName('title', $index)); $index++) {
            $locale = $node->getAttribute('locale');
            if ($locale == '') {
                $locale = $journalPrimaryLocale;
            } elseif (!in_array($locale, $journalSupportedLocales)) {
                $errors[] = ['plugins.importexport.native.import.error.issueTitleLocaleUnsupported', ['issueTitle' => $node->getValue(), 'locale' => $locale]];
                $hasErrors = true;
                continue;
            }
            $issue->setTitle($node->getValue(), $locale);
            $titleExists = true;
        }

        for ($index=0; ($node = $issueNode->getChildByName('description', $index)); $index++) {
            $locale = $node->getAttribute('locale');
            if ($locale == '') {
                $locale = $journalPrimaryLocale;
            } elseif (!in_array($locale, $journalSupportedLocales)) {
                $errors[] = ['plugins.importexport.native.import.error.issueDescriptionLocaleUnsupported', ['issueTitle' => $issue->getLocalizedTitle(), 'locale' => $locale]];
                $hasErrors = true;
                continue;
            }
            $issue->setDescription($node->getValue(), $locale);
        }

        if (($node = $issueNode->getChildByName('volume'))) $issue->setVolume($node->getValue());
        if (($node = $issueNode->getChildByName('number'))) $issue->setNumber($node->getValue());
        if (($node = $issueNode->getChildByName('year'))) $issue->setYear($node->getValue());

        /* --- Set date published --- */

        if (($node = $issueNode->getChildByName('date_published'))) {
            $publishedDate = strtotime($node->getValue());
            if ($publishedDate === false || $publishedDate === -1) {
                $errors[] = ['plugins.importexport.native.import.error.invalidDate', ['value' => $node->getValue()]];
                if ($cleanupErrors) {
                    self::cleanupFailure($dependentItems);
                }
                return false;
            } else {
                $issue->setDatePublished($publishedDate);
            }
        }

        /* --- Set attributes --- */

        switch(($value = $issueNode->getAttribute('identification'))) {
            case 'num_vol_year_title':
                $issue->setShowVolume(1); $issue->setShowNumber(1); $issue->setShowYear(1); $issue->setShowTitle(1);
                break;
            case 'num_vol_year':
                $issue->setShowVolume(1); $issue->setShowNumber(1); $issue->setShowYear(1); $issue->setShowTitle(0);
                break;
            case 'vol_year':
                $issue->setShowVolume(1); $issue->setShowNumber(0); $issue->setShowYear(1); $issue->setShowTitle(0);
                break;
            case 'num_year_title':
                $issue->setShowVolume(0); $issue->setShowNumber(1); $issue->setShowYear(1); $issue->setShowTitle(1);
                break;
            case 'vol':
                $issue->setShowVolume(1); $issue->setShowNumber(0); $issue->setShowYear(0); $issue->setShowTitle(0);
                break;
            case 'year':
                $issue->setShowVolume(0); $issue->setShowNumber(0); $issue->setShowYear(1); $issue->setShowTitle(0);
                break;
            case 'title':
            case '':
            case null:
                $issue->setShowVolume(0); $issue->setShowNumber(0); $issue->setShowYear(0); $issue->setShowTitle(1);
                break;
            default:
                $errors[] = ['plugins.importexport.native.import.error.unknownIdentificationType', ['identificationType' => $value, 'issueTitle' => $issue->getLocalizedTitle()]];
                $hasErrors = true;
                break;
        }

        if (($issueNode->getAttribute('identification') == 'title' || $issueNode->getAttribute('identification') == '') && (!$titleExists)) {
            $errors[] = ['plugins.importexport.native.import.error.titleMissing', []];
            $issue->setTitle(__('plugins.importexport.native.import.error.defaultTitle'), $journalPrimaryLocale);
            $hasErrors = true;
        }

        switch(($value = $issueNode->getAttribute('published'))) {
            case 'true': $issue->setPublished(1); break;
            case 'false':
            case '':
            case null: $issue->setPublished(0); break;
            default:
                $errors[] = ['plugins.importexport.native.import.error.invalidBooleanValue', ['value' => $value]];
                $hasErrors = true;
                break;
        }

        switch(($value = $issueNode->getAttribute('current'))) {
            case 'true': $issue->setCurrent(1); break;
            case 'false':
            case '':
            case null: $issue->setCurrent(0); break;
            default:
                $errors[] = ['plugins.importexport.native.import.error.invalidBooleanValue', ['value' => $value]];
                $hasErrors = true;
                break;
        }

        if (($value = $issueNode->getAttribute('public_id')) != '') {
            $anotherIssue = $issueDao->getIssueByPubId('publisher-id', $value, $journal->getId());
            if ($anotherIssue) {
                $errors[] = ['plugins.importexport.native.import.error.duplicatePublicIssueId', ['issueTitle' => $issue->getIssueIdentification(), 'otherIssueTitle' => $anotherIssue->getIssueIdentification()]];
                $hasErrors = true;
            } else {
                $issue->setStoredPubId('publisher-id', $value);
            }
        }

        /* --- Access Status --- */
        $node = $issueNode->getChildByName('open_access');
        $issue->setAccessStatus($node ? ISSUE_ACCESS_OPEN : ISSUE_ACCESS_SUBSCRIPTION);

        if (($node = $issueNode->getChildByName('access_date'))) {
            $accessDate = strtotime($node->getValue());
            if ($accessDate === false || $accessDate === -1) {
                $errors[] = ['plugins.importexport.native.import.error.invalidDate', ['value' => $node->getValue()]];
                $hasErrors = true;
            } else {
                $issue->setOpenAccessDate($accessDate);
            }
        }

        if ($hasErrors) {
            $issue = null;
            if ($cleanupErrors) {
                self::cleanupFailure($dependentItems);
            }
            return false;
        } else {
            if ($issue->getCurrent()) {
                $issueDao->updateCurrentIssue($journal->getId());
            }
            $issue->setId($issueDao->insertIssue($issue));
            $dependentItems[] = ['issue', $issue];
        }

        /* --- Handle cover --- */
        $coverErrors = [];
        for ($index = 0; ($node = $issueNode->getChildByName('cover', $index)); $index++) {
            if (!self::handleCoverNode($journal, $node, $issue, $coverErrors, $isCommandLine)) {
                $errors = array_merge($errors, $coverErrors);
                $hasErrors = true;
            }
        }

        /* --- Handle sections --- */
        $sectionErrors = [];
        for ($index = 0; ($node = $issueNode->getChildByName('section', $index)); $index++) {
            if (!self::handleSectionNode($journal, $node, $issue, $sectionErrors, $user, $isCommandLine, $dependentItems, $index)) {
                $errors = array_merge($errors, $sectionErrors);
                $hasErrors = true;
            }
        }

        if ($hasErrors) {
            $issue = null;
            if ($cleanupErrors) {
                self::cleanupFailure($dependentItems);
            }
            return false;
        }

        $issueDao->updateIssue($issue);
        return true;
    }

    /**
     * Handle a cover node.
     *
     * @param Journal $journal
     * @param XMLNode $coverNode
     * @param Issue $issue
     * @param array &$errors Output errors
     * @param bool $isCommandLine
     *
     * @return bool
     */
    public static function handleCoverNode($journal, $coverNode, $issue, &$errors, $isCommandLine) {
        $errors = [];
        $hasErrors = false;

        $journalSupportedLocales = array_keys($journal->getSupportedLocaleNames());
        $journalPrimaryLocale = $journal->getPrimaryLocale();

        $locale = $coverNode->getAttribute('locale');
        if ($locale == '') {
            $locale = $journalPrimaryLocale;
        } elseif (!in_array($locale, $journalSupportedLocales)) {
            $errors[] = ['plugins.importexport.native.import.error.coverLocaleUnsupported', ['issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
            return false;
        }

        $issue->setShowCoverPage(1, $locale);

        if (($node = $coverNode->getChildByName('caption'))) $issue->setCoverPageDescription($node->getValue(), $locale);

        if (($node = $coverNode->getChildByName('image'))) {
            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();
            $newName = 'cover_issue_' . $issue->getId()."_{$locale}"  . '.';

            if (($href = $node->getChildByName('href'))) {
                $url = $href->getAttribute('src');
                if ($isCommandLine || self::isAllowedMethod($url)) {
                    if ($isCommandLine && self::isRelativePath($url)) {
                        $url = PWD . '/' . $url;
                    }

                    $originalName = basename($url);
                    $newName .= $publicFileManager->getExtension($originalName);
                    if (!$publicFileManager->copyJournalFile($journal->getId(), $url, $newName)) {
                        $errors[] = ['plugins.importexport.native.import.error.couldNotCopy', ['url' => $url]];
                        $hasErrors = true;
                    } else {
                        $issue->setFileName($newName, $locale);
                        $issue->setOriginalFileName($publicFileManager->truncateFileName($originalName, 127), $locale);
                    }
                }
            }
            if (($embed = $node->getChildByName('embed'))) {
                if (($type = $embed->getAttribute('encoding')) !== 'base64') {
                    $errors[] = ['plugins.importexport.native.import.error.unknownEncoding', ['type' => $type]];
                    $hasErrors = true;
                } else {
                    $originalName = $embed->getAttribute('filename');
                    $newName .= $publicFileManager->getExtension($originalName);
                    $issue->setFileName($newName, $locale);
                    $issue->setOriginalFileName($publicFileManager->truncateFileName($originalName, 127), $locale);
                    if ($publicFileManager->writeJournalFile($journal->getId(), $newName, base64_decode($embed->getValue()))===false) {
                        $errors[] = ['plugins.importexport.native.import.error.couldNotWriteFile', ['originalName' => $originalName]];
                        $hasErrors = true;
                    }
                }
            }
            // Store the image dimensions.
            if (!$hasErrors) {
                list($width, $height) = getimagesize($publicFileManager->getJournalFilesPath($journal->getId()) . '/' . $newName);
                $issue->setWidth($width, $locale);
                $issue->setHeight($height, $locale);
            }
        }

        if ($hasErrors) {
            return false;
        }
        return true;
    }

    /**
     * Handle an article cover node.
     *
     * @param Journal $journal
     * @param XMLNode $coverNode
     * @param Article $article
     * @param array &$errors Output errors
     * @param bool $isCommandLine
     *
     * @return bool
     */
    public static function handleArticleCoverNode($journal, $coverNode, $article, &$errors, $isCommandLine) {
        $errors = [];
        $hasErrors = false;

        $journalSupportedLocales = array_keys($journal->getSupportedLocaleNames());

        $locale = $coverNode->getAttribute('locale');
        if ($locale == '') {
            $locale = $article->getLocale();
        } elseif (!in_array($locale, $journalSupportedLocales)) {
            $errors[] = ['plugins.importexport.native.import.error.coverLocaleUnsupported', ['issueTitle' => '', 'locale' => $locale]];
            return false;
        }

        $article->setShowCoverPage(1, $locale);

        if (($node = $coverNode->getChildByName('altText'))) $article->setCoverPageAltText($node->getValue(), $locale);

        if (($node = $coverNode->getChildByName('image'))) {
            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();
            $newName = 'cover_article_' . $article->getId()."_{$locale}"  . '.';

            if (($href = $node->getChildByName('href'))) {
                $url = $href->getAttribute('src');
                if ($isCommandLine || self::isAllowedMethod($url)) {
                    if ($isCommandLine && self::isRelativePath($url)) {
                        $url = PWD . '/' . $url;
                    }

                    $originalName = basename($url);
                    $newName .= $publicFileManager->getExtension($originalName);
                    if (!$publicFileManager->copyJournalFile($journal->getId(), $url, $newName)) {
                        $errors[] = ['plugins.importexport.native.import.error.couldNotCopy', ['url' => $url]];
                        $hasErrors = true;
                    } else {
                        $article->setFileName($newName, $locale);
                        $article->setOriginalFileName($publicFileManager->truncateFileName($originalName, 127), $locale);
                    }
                }
            }
            if (($embed = $node->getChildByName('embed'))) {
                if (($type = $embed->getAttribute('encoding')) !== 'base64') {
                    $errors[] = ['plugins.importexport.native.import.error.unknownEncoding', ['type' => $type]];
                    $hasErrors = true;
                } else {
                    $originalName = $embed->getAttribute('filename');
                    $newName .= $publicFileManager->getExtension($originalName);
                    $article->setFileName($newName, $locale);
                    $article->setOriginalFileName($publicFileManager->truncateFileName($originalName, 127), $locale);
                    if ($publicFileManager->writeJournalFile($journal->getId(), $newName, base64_decode($embed->getValue()))===false) {
                        $errors[] = ['plugins.importexport.native.import.error.couldNotWriteFile', ['originalName' => $originalName]];
                        $hasErrors = true;
                    }
                }
            }
            // Store the image dimensions.
            if (!$hasErrors) {
                list($width, $height) = getimagesize($publicFileManager->getJournalFilesPath($journal->getId()) . '/' . $newName);
                $article->setWidth($width, $locale);
                $article->setHeight($height, $locale);
            }
        }

        if ($hasErrors) {
            return false;
        }
        return true;
    }

    /**
     * Check if the URL is a relative path.
     *
     * @param string $url
     * @return bool
     */
    public static function isRelativePath($url) {
        if (self::isAllowedMethod($url)) return false;
        if (isset($url[0]) && $url[0] == '/') return false;
        return true;
    }

    /**
     * Check if the URL uses an allowed method (http, https, ftp, ftps).
     *
     * @param string $url
     * @return bool
     */
    public static function isAllowedMethod($url) {
        $allowedPrefixes = [
            'http://',
            'ftp://',
            'https://',
            'ftps://'
        ];
        foreach ($allowedPrefixes as $prefix) {
            if (substr($url, 0, strlen($prefix)) === $prefix) return true;
        }
        return false;
    }

    /**
     * Handle a section node.
     *
     * @param Journal $journal
     * @param XMLNode $sectionNode
     * @param Issue $issue
     * @param array &$errors Output errors
     * @param User $user
     * @param bool $isCommandLine
     * @param array &$dependentItems Output dependent items
     * @param int|null $sectionIndex
     *
     * @return bool
     */
    public static function handleSectionNode($journal, $sectionNode, $issue, &$errors, $user, $isCommandLine, &$dependentItems, $sectionIndex = null) {
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $errors = [];

        $journalSupportedLocales = array_keys($journal->getSupportedLocaleNames());
        $journalPrimaryLocale = $journal->getPrimaryLocale();

        $titles = [];
        for ($index=0; ($node = $sectionNode->getChildByName('title', $index)); $index++) {
            $locale = $node->getAttribute('locale');
            if ($locale == '') {
                $locale = $journalPrimaryLocale;
            } elseif (!in_array($locale, $journalSupportedLocales)) {
                $errors[] = ['plugins.importexport.native.import.error.sectionTitleLocaleUnsupported', ['sectionTitle' => $node->getValue(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
                return false;
            }
            $titles[$locale] = $node->getValue();
        }
        if (empty($titles)) {
            $errors[] = ['plugins.importexport.native.import.error.sectionTitleMissing', ['issueTitle' => $issue->getIssueIdentification()]];
            return false;
        }

        $abbrevs = [];
        for ($index=0; ($node = $sectionNode->getChildByName('abbrev', $index)); $index++) {
            $locale = $node->getAttribute('locale');
            if ($locale == '') {
                $locale = $journalPrimaryLocale;
            } elseif (!in_array($locale, $journalSupportedLocales)) {
                $errors[] = ['plugins.importexport.native.import.error.sectionAbbrevLocaleUnsupported', ['sectionAbbrev' => $node->getValue(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
                return false;
            }
            $abbrevs[$locale] = $node->getValue();
        }

        $identifyTypes = [];
        for ($index=0; ($node = $sectionNode->getChildByName('identify_type', $index)); $index++) {
            $locale = $node->getAttribute('locale');
            if ($locale == '') {
                $locale = $journalPrimaryLocale;
            } elseif (!in_array($locale, $journalSupportedLocales)) {
                $errors[] = ['plugins.importexport.native.import.error.sectionIdentifyTypeLocaleUnsupported', ['sectionIdentifyType' => $node->getValue(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
                return false;
            }
            $identifyTypes[$locale] = $node->getValue();
        }

        $policies = [];
        for ($index=0; ($node = $sectionNode->getChildByName('policy', $index)); $index++) {
            $locale = $node->getAttribute('locale');
            if ($locale == '') {
                $locale = $journalPrimaryLocale;
            } elseif (!in_array($locale, $journalSupportedLocales)) {
                $errors[] = ['plugins.importexport.native.import.error.sectionPolicyLocaleUnsupported', ['sectionPolicy' => $node->getValue(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
                return false;
            }
            $policies[$locale] = $node->getValue();
        }

        $section = null;
        $foundSectionId = $foundSectionTitle = null;
        $index = 0;
        foreach($titles as $locale => $title) {
            $section = $sectionDao->getSectionByTitle($title, $journal->getId());
            if ($section) {
                $sectionId = $section->getId();
                if ($foundSectionId) {
                    if ($foundSectionId != $sectionId) {
                        $errors[] = ['plugins.importexport.native.import.error.sectionTitleMismatch', ['section1Title' => $title, 'section2Title' => $foundSectionTitle, 'issueTitle' => $issue->getIssueIdentification()]];
                        return false;
                    }
                } else if ($index > 0) {
                        $errors[] = ['plugins.importexport.native.import.error.sectionTitleMatch', ['sectionTitle' => $title, 'issueTitle' => $issue->getIssueIdentification()]];
                        return false;
                }
                $foundSectionId = $sectionId;
                $foundSectionTitle = $title;
            } else {
                if ($foundSectionId) {
                    $errors[] = ['plugins.importexport.native.import.error.sectionTitleMatch', ['sectionTitle' => $foundSectionTitle, 'issueTitle' => $issue->getIssueIdentification()]];
                    return false;
                }
            }
            $index++;
        }

        $abbrevSection = null;
        $foundSectionId = $foundSectionAbbrev = null;
        $index = 0;
        foreach($abbrevs as $locale => $abbrev) {
            $abbrevSection = $sectionDao->getSectionByAbbrev($abbrev, $journal->getId());
            if ($abbrevSection) {
                $sectionId = $abbrevSection->getId();
                if ($foundSectionId) {
                    if ($foundSectionId != $sectionId) {
                        $errors[] = ['plugins.importexport.native.import.error.sectionAbbrevMismatch', ['section1Abbrev' => $abbrev, 'section2Abbrev' => $foundSectionAbbrev, 'issueTitle' => $issue->getIssueIdentification()]];
                        return false;
                    }
                } else if ($index > 0) {
                    $errors[] = ['plugins.importexport.native.import.error.sectionAbbrevMatch', ['sectionAbbrev' => $abbrev, 'issueTitle' => $issue->getIssueIdentification()]];
                    return false;
                }
                $foundSectionId = $sectionId;
                $foundSectionAbbrev = $abbrev;
            } else {
                if ($foundSectionId) {
                    $errors[] = ['plugins.importexport.native.import.error.sectionAbbrevMatch', ['sectionAbbrev' => $foundSectionAbbrev, 'issueTitle' => $issue->getIssueIdentification()]];
                    return false;
                }
            }
            $index++;
        }

        if (!$section && !$abbrevSection) {
            unset($section);
            $section = new Section();
            $section->setTitle($titles, null);
            $section->setAbbrev($abbrevs, null);
            $section->setIdentifyType($identifyTypes, null);
            $section->setPolicy($policies, null);
            $section->setJournalId($journal->getId());
            $section->setSequence(REALLY_BIG_NUMBER);
            $section->setMetaIndexed(1);
            $section->setEditorRestricted(1);
            $section->setId($sectionDao->insertSection($section));
            $sectionDao->resequenceSections($journal->getId());
        }

        if (!$section && $abbrevSection) {
            unset($section);
            $section = $abbrevSection;
        }

        if ($sectionIndex !== null) {
            $sectionDao->insertCustomSectionOrder($issue->getId(), $section->getId(), $sectionIndex);
        }

        $hasErrors = false;
        $article = null;
        $publishedArticle = null;
        $articleErrors = [];

        for ($index = 0; ($node = $sectionNode->getChildByName('article', $index)); $index++) {
            if (!self::handleArticleNode($journal, $node, $issue, $section, $article, $publishedArticle, $articleErrors, $user, $isCommandLine, $dependentItems)) {
                $errors = array_merge($errors, $articleErrors);
                $hasErrors = true;
            }
        }
        if ($hasErrors) return false;

        return true;
    }

    /**
     * Handle an article node.
     *
     * @param Journal $journal
     * @param XMLNode $articleNode
     * @param Issue $issue
     * @param Section $section
     * @param Article &$article Output article
     * @param PublishedArticle &$publishedArticle Output published article
     * @param array &$errors Output errors
     * @param User $user
     * @param bool $isCommandLine
     * @param array &$dependentItems Output dependent items
     *
     * @return bool
     */
    public static function handleArticleNode($journal, $articleNode, $issue, $section, &$article, &$publishedArticle, &$errors, $user, $isCommandLine, &$dependentItems) {
        $errors = [];

        $journalSupportedLocales = array_keys($journal->getSupportedLocaleNames());
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articleDao = DAORegistry::getDAO('ArticleDAO');

        $article = new Article();
        if ($locale = $articleNode->getAttribute('locale')) {
            $article->setLocale($locale);
        } else {
            $article->setLocale($journal->getPrimaryLocale());
        }
        if (($value = $articleNode->getAttribute('public_id')) != '') {
            $anotherArticle = $publishedArticleDao->getPublishedArticleByPubId('publisher-id', $value, $journal->getId());
            if ($anotherArticle) {
                $errors[] = ['plugins.importexport.native.import.error.duplicatePublicArticleId', ['articleTitle' => $article->getLocalizedTitle(), 'otherArticleTitle' => $anotherArticle->getLocalizedTitle()]];
                return false;
            } else {
                $article->setStoredPubId('publisher-id', $value);
            }
        }

        $article->setJournalId($journal->getId());
        $article->setUserId($user->getId());
        $article->setSectionId($section->getId());
        $article->setStatus(STATUS_PUBLISHED);
        $article->setSubmissionProgress(0);
        $article->setDateSubmitted(Core::getCurrentDate());
        $article->stampStatusModified();

        $titleExists = false;
        for ($index=0; ($node = $articleNode->getChildByName('title', $index)); $index++) {
            $locale = $node->getAttribute('locale');
            if ($locale == '') {
                $locale = $article->getLocale();
            } elseif (!in_array($locale, $journalSupportedLocales)) {
                $errors[] = ['plugins.importexport.native.import.error.articleTitleLocaleUnsupported', ['articleTitle' => $node->getValue(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
                return false;
            }
            $article->setTitle($node->getValue(), $locale);
            $titleExists = true;
        }
        if (!$titleExists || $article->getTitle($article->getLocale()) == '') {
            $errors[] = ['plugins.importexport.native.import.error.articleTitleMissing', ['issueTitle' => $issue->getIssueIdentification(), 'sectionTitle' => $section->getLocalizedTitle()]];
            return false;
        }

        for ($index=0; ($node = $articleNode->getChildByName('abstract', $index)); $index++) {
            $locale = $node->getAttribute('locale');
            if ($locale == '') {
                $locale = $article->getLocale();
            } elseif (!in_array($locale, $journalSupportedLocales)) {
                $errors[] = ['plugins.importexport.native.import.error.articleAbstractLocaleUnsupported', ['articleTitle' => $article->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
                return false;
            }
            $article->setAbstract($node->getValue(), $locale);
        }

        if (($indexingNode = $articleNode->getChildByName('indexing'))) {
            // Simplified repetition for indexing fields
            $fields = ['discipline', 'type', 'subject', 'subject_class'];
            foreach($fields as $field) {
                for ($index=0; ($node = $indexingNode->getChildByName($field, $index)); $index++) {
                    $locale = $node->getAttribute('locale');
                    if ($locale == '') $locale = $article->getLocale();
                    if (!in_array($locale, $journalSupportedLocales)) {
                        $errors[] = ['plugins.importexport.native.import.error.article'.ucfirst($field).'LocaleUnsupported', ['articleTitle' => $article->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
                        return false;
                    }
                    $setter = 'set' . str_replace('_', '', $field);
                    if (method_exists($article, $setter)) $article->$setter($node->getValue(), $locale); // Loose call but standard in OJS
                    elseif ($field == 'subject_class') $article->setSubjectClass($node->getValue(), $locale);
                }
            }

            if (($coverageNode = $indexingNode->getChildByName('coverage'))) {
                $covFields = ['geographical' => 'CoverageGeo', 'chronological' => 'CoverageChron', 'sample' => 'CoverageSample'];
                foreach($covFields as $field => $setterSuffix) {
                    for ($index=0; ($node = $coverageNode->getChildByName($field, $index)); $index++) {
                        $locale = $node->getAttribute('locale');
                        if ($locale == '') $locale = $article->getLocale();
                        if (!in_array($locale, $journalSupportedLocales)) {
                            $errors[] = ['plugins.importexport.native.import.error.articleCoverage'.$setterSuffix.'LocaleUnsupported', ['articleTitle' => $article->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
                            return false;
                        }
                        $method = 'set' . $setterSuffix;
                        $article->$method($node->getValue(), $locale);
                    }
                }
            }
        }

        for ($index=0; ($node = $articleNode->getChildByName('sponsor', $index)); $index++) {
            $locale = $node->getAttribute('locale');
            if ($locale == '') $locale = $article->getLocale();
            elseif (!in_array($locale, $journalSupportedLocales)) {
                $errors[] = ['plugins.importexport.native.import.error.articleSponsorLocaleUnsupported', ['articleTitle' => $article->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
                return false;
            }
            $article->setSponsor($node->getValue(), $locale);
        }

        if (($node = $articleNode->getChildByName('pages'))) $article->setPages($node->getValue());
        if (($language = $articleNode->getAttribute('language'))) $article->setLanguage($language);

        /* --- Set IDs --- */
        if (!self::handlePubIds($articleNode, $article, $journal, $issue, $article, $errors)) return false;

        $articleDao->insertArticle($article);

        /* --- Handle covers --- */
        $coverErrors = [];
        for ($index = 0; ($node = $articleNode->getChildByName('cover', $index)); $index++) {
            if (!self::handleArticleCoverNode($journal, $node, $article, $coverErrors, $isCommandLine)) {
                $errors = array_merge($errors, $coverErrors);
                return false;
            }
        }

        $dependentItems[] = ['article', $article];

        /* --- Handle authors --- */
        $authorErrors = [];
        for ($index = 0; ($node = $articleNode->getChildByName('author', $index)); $index++) {
            if (!self::handleAuthorNode($journal, $node, $issue, $section, $article, $authorErrors, $index)) {
                $errors = array_merge($errors, $authorErrors);
                return false;
            }
        }
        
        // Create submission mangement records
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $signoffs = [
            'SIGNOFF_COPYEDITING_INITIAL', 'SIGNOFF_COPYEDITING_AUTHOR', 'SIGNOFF_COPYEDITING_FINAL',
            'SIGNOFF_LAYOUT', 'SIGNOFF_PROOFREADING_AUTHOR', 'SIGNOFF_PROOFREADING_PROOFREADER', 'SIGNOFF_PROOFREADING_LAYOUT'
        ];
        foreach($signoffs as $symbol) {
             $signoff = $signoffDao->build($symbol, ASSOC_TYPE_ARTICLE, $article->getId());
             $signoff->setUserId(0);
             $signoffDao->updateObject($signoff);
        }

        // Log the import in the article event log.
        import('classes.article.log.ArticleLog');
        ArticleLog::logEventHeadless(
            $journal, $user->getId(), $article,
            ARTICLE_LOG_ARTICLE_IMPORT,
            'log.imported',
            ['userName' => $user->getFullName(), 'articleId' => $article->getId()]
        );

        // Insert published article entry.
        $publishedArticle = new PublishedArticle();
        $publishedArticle->setId($article->getId());
        $publishedArticle->setIssueId($issue->getId());

        if (($node = $articleNode->getChildByName('date_published'))) {
            $publishedDate = strtotime($node->getValue());
            if ($publishedDate === false || $publishedDate === -1) {
                $errors[] = ['plugins.importexport.native.import.error.invalidDate', ['value' => $node->getValue()]];
                return false;
            } else {
                $publishedArticle->setDatePublished($publishedDate);
            }
        }
        $node = $articleNode->getChildByName('open_access');
        $publishedArticle->setAccessStatus($node?ARTICLE_ACCESS_OPEN:ARTICLE_ACCESS_ISSUE_DEFAULT);
        $publishedArticle->setSeq(REALLY_BIG_NUMBER);

        $publishedArticle->setPublishedArticleId($publishedArticleDao->insertPublishedArticle($publishedArticle));
        $publishedArticleDao->resequencePublishedArticles($section->getId(), $issue->getId());

        // Setup default copyright/license metadata
        $article->initializePermissions();

        if ($permissionsNode = $articleNode->getChildByName('permissions')) {
            if ($node = $permissionsNode->getChildByName('copyright_year')) {
                $article->setCopyrightYear($node->getValue());
            }
            for ($index=0; ($node = $permissionsNode->getChildByName('copyright_holder', $index)); $index++) {
                $locale = $node->getAttribute('locale');
                if ($locale == '') $locale = $article->getLocale();
                $article->setCopyrightHolder($node->getValue(), $locale);
            }
            if ($node = $permissionsNode->getChildByName('license_url')) {
                $article->setLicenseURL($node->getValue());
            }
        }
        $articleDao->updateLocaleFields($article);

        /* --- Galleys --- */
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($article->getId());

        $hasErrors = false;
        $galleyCount = 0;
        $galleyErrors = [];
        // Direct access to children array in DOMElement
        foreach ($articleNode->children as $node) {
            $isHtml = ($node->getName() == 'htmlgalley');
            if (!$isHtml && $node->getName() != 'galley') continue;

            if (!self::handleGalleyNode($journal, $node, $issue, $section, $article, $galleyErrors, $isCommandLine, $isHtml, $galleyCount, $articleFileManager)) {
                $errors = array_merge($errors, $galleyErrors);
                $hasErrors = true;
            }
            $galleyCount++;
        }
        if ($hasErrors) return false;

        /* --- Handle supplemental files --- */
        $suppFileErrors = [];
        for ($index = 0; ($node = $articleNode->getChildByName('supplemental_file', $index)); $index++) {
            if (!self::handleSuppFileNode($journal, $node, $issue, $section, $article, $suppFileErrors, $isCommandLine, $articleFileManager)) {
                $errors = array_merge($errors, $suppFileErrors);
                $hasErrors = true;
            }
        }
        if ($hasErrors) return false;

        // Index the inserted article.
        import('classes.search.ArticleSearchIndex');
        $articleSearchIndex = new ArticleSearchIndex();
        $articleSearchIndex->articleMetadataChanged($article);
        $articleSearchIndex->articleFilesChanged($article);
        $articleSearchIndex->articleChangesFinished();

        return true;
    }

	/**
	 * Handle an author node (i.e. convert an author from DOM to DAO).
	 * @param $journal Journal
	 * @param $authorNode DOMElement
	 * @param $issue Issue
	 * @param $section Section
	 * @param $article Article
	 * @param $errors array
	 * @param $authorIndex int 0 for first author, 1 for second, ...
	 */
    public static function handleAuthorNode($journal, $authorNode, $issue, $section, $article, &$errors, $authorIndex) {
        $errors = [];
        $journalSupportedLocales = array_keys($journal->getSupportedLocaleNames());
        
        $author = new Author();
        if (($node = $authorNode->getChildByName('firstname'))) $author->setFirstName((string)$node->getValue());
        if (($node = $authorNode->getChildByName('middlename'))) $author->setMiddleName($node->getValue());
        if (($node = $authorNode->getChildByName('lastname'))) $author->setLastName((string)$node->getValue());
        $author->setSequence($authorIndex+1);
        
        for ($index=0; ($node = $authorNode->getChildByName('affiliation', $index)); $index++) {
            $locale = $node->getAttribute('locale');
            if ($locale == '') $locale = $article->getLocale();
            elseif (!in_array($locale, $journalSupportedLocales)) {
                $errors[] = ['plugins.importexport.native.import.error.articleAuthorAffiliationLocaleUnsupported', ['authorFullName' => $author->getFullName(), 'articleTitle' => $article->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
                return false;
            }
            $author->setAffiliation($node->getValue(), $locale);
        }
        if (($node = $authorNode->getChildByName('country'))) $author->setCountry($node->getValue());
        if (($node = $authorNode->getChildByName('email'))) $author->setEmail((string)$node->getValue());
        if (($node = $authorNode->getChildByName('url'))) $author->setUrl($node->getValue());
        
        $fields = ['competing_interests' => 'CompetingInterests', 'biography' => 'Biography'];
        foreach($fields as $field => $setter) {
            for ($index=0; ($node = $authorNode->getChildByName($field, $index)); $index++) {
                $locale = $node->getAttribute('locale');
                if ($locale == '') $locale = $article->getLocale();
                elseif (!in_array($locale, $journalSupportedLocales)) {
                     $errors[] = ['plugins.importexport.native.import.error.articleAuthor'.$setter.'LocaleUnsupported', ['authorFullName' => $author->getFullName(), 'articleTitle' => $article->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
                     return false;
                }
                $method = 'set'.$setter;
                $author->$method($node->getValue(), $locale);
            }
        }

        $author->setSubmissionId($article->getId());
        $author->setPrimaryContact($authorNode->getAttribute('primary_contact')==='true'?1:0);
        $authorDao = DAORegistry::getDAO('AuthorDAO'); /* @var $authorDao AuthorDAO */
        $authorDao->insertAuthor($author);

        return true;
    }

    /**
     * Handle a galley node.
     */
    public static function handleGalleyNode($journal, $galleyNode, $issue, $section, $article, &$errors, $isCommandLine, $isHtml, $galleyCount, $articleFileManager) {
        $errors = [];
        $journalSupportedLocales = array_keys($journal->getSupportedLocaleNames());
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');

        if ($isHtml) $galley = new ArticleHtmlGalley();
        else $galley = new ArticleGalley();

        if (($value = $galleyNode->getAttribute('public_id')) != '') {
            $anotherGalley = $galleyDao->getGalleyByPubId('publisher-id', $value, $article->getId());
            if ($anotherGalley) {
                $errors[] = ['plugins.importexport.native.import.error.duplicatePublicGalleyId', ['publicId' => $value, 'articleTitle' => $article->getLocalizedTitle()]];
                return false;
            } else {
                $galley->setStoredPubId('publisher-id', $value);
            }
        }

        $galley->setArticleId($article->getId());
        $galley->setSequence($galleyCount);

        /* --- Set IDs --- */
        // We pass $issue here, though handlePubIds signature might expect $article as second param for duplicate check.
        // Wait, handlePubIds signature: ($node, &$pubObject, $journal, $issue, $article, &$errors)
        if (!self::handlePubIds($galleyNode, $galley, $journal, $issue, $article, $errors)) return false;

        $locale = $galleyNode->getAttribute('locale');
        if ($locale == '') {
            $locale = $article->getLocale();
        } elseif (!in_array($locale, $journalSupportedLocales)) {
            $errors[] = ['plugins.importexport.native.import.error.galleyLocaleUnsupported', ['articleTitle' => $article->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
            return false;
        }
        $galley->setLocale($locale);

        /* --- Galley Label --- */
        if (!($node = $galleyNode->getChildByName('label'))) {
            $errors[] = ['plugins.importexport.native.import.error.galleyLabelMissing', ['articleTitle' => $article->getLocalizedTitle(), 'sectionTitle' => $section->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification()]];
            return false;
        }
        $galley->setLabel($node->getValue());

        /* --- Galley File --- */
        if (!($node = $galleyNode->getChildByName('file'))) {
            $errors[] = ['plugins.importexport.native.import.error.galleyFileMissing', ['articleTitle' => $article->getLocalizedTitle(), 'sectionTitle' => $section->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification()]];
            return false;
        }

        $fileId = null;
        if (($href = $node->getChildByName('href'))) {
            $url = $href->getAttribute('src');
            if ($isCommandLine || self::isAllowedMethod($url)) {
                if ($isCommandLine && self::isRelativePath($url)) {
                    $url = PWD . '/' . $url;
                }
                if (($fileId = $articleFileManager->copyPublicFile($url, $href->getAttribute('mime_type')))===false) {
                    $errors[] = ['plugins.importexport.native.import.error.couldNotCopy', ['url' => $url]];
                    return false;
                }
            }
        }
        if (($embed = $node->getChildByName('embed'))) {
            if (($type = $embed->getAttribute('encoding')) !== 'base64') {
                $errors[] = ['plugins.importexport.native.import.error.unknownEncoding', ['type' => $type]];
                return false;
            }
            $originalName = $embed->getAttribute('filename');
            if (($fileId = $articleFileManager->writePublicFile($originalName, base64_decode($embed->getValue()), $embed->getAttribute('mime_type')))===false) {
                $errors[] = ['plugins.importexport.native.import.error.couldNotWriteFile', ['originalName' => $originalName]];
                return false;
            }
        }
        if (($remote = $node->getChildByName('remote'))) {
            $url = $remote->getAttribute('src');
            $galley->setRemoteURL($url);
            $fileId = 0;
        }
        if (!isset($fileId)) {
            $errors[] = ['plugins.importexport.native.import.error.galleyFileMissing', ['articleTitle' => $article->getLocalizedTitle(), 'sectionTitle' => $section->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification()]];
            return false;
        }
        $galley->setFileId($fileId);
        $galleyDao->insertGalley($galley);

        if ($isHtml) {
            $result = self::handleHtmlGalleyNodes($galleyNode, $articleFileManager, $galley, $errors, $isCommandLine);
            if (!$result) return false;
        }

        return true;
    }

    /**
     * Handle HTML galley nodes (stylesheets, images).
	 */
    public static function handleHtmlGalleyNodes($galleyNode, $articleFileManager, $galley, &$errors, $isCommandLine) {
        $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');

        foreach ($galleyNode->children as $node) {
            $isStylesheet = ($node->getName() == 'stylesheet');
            $isImage = ($node->getName() == 'image');
            if (!$isStylesheet && !$isImage) continue;

            $fileId = null;
            if (($href = $node->getChildByName('href'))) {
                $url = $href->getAttribute('src');
                if ($isCommandLine || self::isAllowedMethod($url)) {
                    if ($isCommandLine && self::isRelativePath($url)) {
                        $url = PWD . '/' . $url;
                    }
                    if (($fileId = $articleFileManager->copyPublicFile($url, $href->getAttribute('mime_type')))===false) {
                        $errors[] = ['plugins.importexport.native.import.error.couldNotCopy', ['url' => $url]];
                        return false;
                    }
                }
            }
            if (($embed = $node->getChildByName('embed'))) {
                if (($type = $embed->getAttribute('encoding')) !== 'base64') {
                    $errors[] = ['plugins.importexport.native.import.error.unknownEncoding', ['type' => $type]];
                    return false;
                }
                $originalName = $embed->getAttribute('filename');
                if (($fileId = $articleFileManager->writePublicFile($originalName, base64_decode($embed->getValue()), $embed->getAttribute('mime_type')))===false) {
                    $errors[] = ['plugins.importexport.native.import.error.couldNotWriteFile', ['originalName' => $originalName]];
                    return false;
                }
            }

            if (!isset($fileId)) continue;

            if ($isStylesheet) {
                $galley->setStyleFileId($fileId);
                $articleGalleyDao->updateGalley($galley);
            } else {
                $articleGalleyDao->insertGalleyImage($galley->getId(), $fileId);
            }
        }
        return true;
    }

    /**
	 * Import a public ID from the XML node to the given publication object.
	 * @param $node DOMNode
	 * @param $pubObject object
	 * @param $journal Journal
	 * @param $issue Issue
	 * @param $article Article
	 * @param $errors array
	 */
    public static function handlePubIds($node, $pubObject, $journal, $issue, $article, &$errors) {
        for ($index=0; ($idNode = $node->getChildByName('id', $index)); $index++) {
            $pubIdType = $idNode->getAttribute('type');

            if (is_null($pubIdType)) continue;

            $errorParams = ['pubIdType' => $pubIdType];

            $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $journal->getId());
            $pubIdPluginFound = false;
            if (is_array($pubIdPlugins)) foreach ($pubIdPlugins as $pubIdPlugin) {
                if ($pubIdPlugin->getPubIdType() == $pubIdType) {
                    $pubId = $idNode->getValue();
                    $errorParams['pubId'] = $pubId;
                    if (!$pubIdPlugin->validatePubId($pubId)) {
                        $errors[] = ['plugins.importexport.native.import.error.invalidPubId', $errorParams];
                        return false;
                    }
                    if (!$pubIdPlugin->checkDuplicate($pubId, $pubObject, $journal->getId())) {
                        $errors[] = ['plugins.importexport.native.import.error.duplicatePubId', $errorParams];
                        return false;
                    }
                    $pubObject->setStoredPubId($pubIdType, $pubId);
                    $pubIdPluginFound = true;
                    break;
                }
            }
            if (!$pubIdPluginFound) {
                $errors[] = ['plugins.importexport.native.import.error.unknownPubId', $errorParams];
                return false;
            }
        }
        return true;
    }

    /**
     * Handle a supplemental file node.
     */
    public static function handleSuppFileNode($journal, $suppNode, $issue, $section, $article, &$errors, $isCommandLine, $articleFileManager) {
        $errors = [];
        $journalSupportedLocales = array_keys($journal->getSupportedLocaleNames());
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');

        $suppFile = new SuppFile();
        $suppFile->setArticleId($article->getId());

        /* --- Set IDs --- */
        if (!self::handlePubIds($suppNode, $suppFile, $journal, $issue, $article, $errors)) return false;

        $fields = [
            'title' => 'Title', 'creator' => 'Creator', 'subject' => 'Subject',
            'type_other' => 'TypeOther', 'description' => 'Description',
            'publisher' => 'Publisher', 'sponsor' => 'Sponsor', 'source' => 'Source'
        ];
        foreach($fields as $xmlTag => $setter) {
            for ($index=0; ($node = $suppNode->getChildByName($xmlTag, $index)); $index++) {
                $locale = $node->getAttribute('locale');
                if ($locale == '') $locale = $article->getLocale();
                elseif (!in_array($locale, $journalSupportedLocales)) {
                     $errors[] = ['plugins.importexport.native.import.error.articleSuppFile'.$setter.'LocaleUnsupported', ['suppFileTitle' => $suppFile->getLocalizedTitle(), 'articleTitle' => $article->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification(), 'locale' => $locale]];
                     return false;
                }
                $method = 'set'.$setter;
                $suppFile->$method($node->getValue(), $locale);
            }
        }

        if (($node = $suppNode->getChildByName('date_created'))) {
            $createdDate = strtotime($node->getValue());
            if ($createdDate !== -1 && $createdDate !== false) $suppFile->setDateCreated($createdDate);
        }

        switch (($suppType = $suppNode->getAttribute('type'))) {
            case 'research_instrument': $suppFile->setType(__('author.submit.suppFile.researchInstrument')); break;
            case 'research_materials': $suppFile->setType(__('author.submit.suppFile.researchMaterials')); break;
            case 'research_results': $suppFile->setType(__('author.submit.suppFile.researchResults')); break;
            case 'transcripts': $suppFile->setType(__('author.submit.suppFile.transcripts')); break;
            case 'data_analysis': $suppFile->setType(__('author.submit.suppFile.dataAnalysis')); break;
            case 'data_set': $suppFile->setType(__('author.submit.suppFile.dataSet')); break;
            case 'source_text': $suppFile->setType(__('author.submit.suppFile.sourceText')); break;
            case 'other': $suppFile->setType(''); break;
            default:
                $errors[] = ['plugins.importexport.native.import.error.unknownSuppFileType', ['suppFileType' => $suppType]];
                return false;
        }

        $suppFile->setShowReviewers($suppNode->getAttribute('show_reviewers')=='true');
        $suppFile->setLanguage($suppNode->getAttribute('language'));

        if (($value = $suppNode->getAttribute('public_id')) != '') {
            $anotherSuppFile = $suppFileDao->getSuppFileByPubId('publisher-id', $value, $article->getId());
            if ($anotherSuppFile) {
                $errors[] = ['plugins.importexport.native.import.error.duplicatePublicSuppFileId', ['suppFileTitle' => $suppFile->getLocalizedTitle(), 'otherSuppFileTitle' => $anotherSuppFile->getLocalizedTitle()]];
                return false;
            } else {
                $suppFile->setStoredPubId('publisher-id', $value);
            }
        }

        if (!($fileNode = $suppNode->getChildByName('file'))) {
            $errors[] = ['plugins.importexport.native.import.error.suppFileMissing', ['articleTitle' => $article->getLocalizedTitle(), 'sectionTitle' => $section->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification()]];
            return false;
        }

        $fileId = null;
        if (($href = $fileNode->getChildByName('href'))) {
            $url = $href->getAttribute('src');
            if ($isCommandLine || self::isAllowedMethod($url)) {
                if ($isCommandLine && self::isRelativePath($url)) {
                    $url = PWD . '/' . $url;
                }
                if (($fileId = $articleFileManager->copySuppFile($url, $href->getAttribute('mime_type')))===false) {
                    $errors[] = ['plugins.importexport.native.import.error.couldNotCopy', ['url' => $url]];
                    return false;
                }
            }
        }
        if (($embed = $fileNode->getChildByName('embed'))) {
            if (($type = $embed->getAttribute('encoding')) !== 'base64') {
                $errors[] = ['plugins.importexport.native.import.error.unknownEncoding', ['type' => $type]];
                return false;
            }
            $originalName = $embed->getAttribute('filename');
            if (($fileId = $articleFileManager->writeSuppFile($originalName, base64_decode($embed->getValue()), $embed->getAttribute('mime_type')))===false) {
                $errors[] = ['plugins.importexport.native.import.error.couldNotWriteFile', ['originalName' => $originalName]];
                return false;
            }
        }
        if (($remote = $fileNode->getChildByName('remote'))) {
            $url = $remote->getAttribute('src');
            $suppFile->setRemoteURL($url);
            $fileId = 0;
        }

        if (!isset($fileId)) {
            $errors[] = ['plugins.importexport.native.import.error.suppFileMissing', ['articleTitle' => $article->getLocalizedTitle(), 'sectionTitle' => $section->getLocalizedTitle(), 'issueTitle' => $issue->getIssueIdentification()]];
            return false;
        }

        $suppFile->setFileId($fileId);
        $suppFileDao->insertSuppFile($suppFile);

        return true;
    }

    /**
     * Cleanup dependent items in case of failure.
     * @param array $dependentItems
     */
    public static function cleanupFailure (&$dependentItems) {
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $articleDao = DAORegistry::getDAO('ArticleDAO');

        foreach ($dependentItems as $dependentItem) {
            $type = array_shift($dependentItem);
            $object = array_shift($dependentItem);

            switch ($type) {
                case 'issue':
                    $issueDao->deleteIssue($object);
                    break;
                case 'article':
                    $articleDao->deleteArticle($object);
                    break;
                default:
                    fatalError ('cleanupFailure: Unimplemented type');
            }
        }
    }
}
?>