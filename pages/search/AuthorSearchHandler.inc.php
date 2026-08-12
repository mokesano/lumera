<?php
declare(strict_types=1);

/**
 * @file pages/search/AuthorSearchHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSearchHandler
 * @ingroup pages_search
 *
 * @brief Handle requests for browsing/searching published articles by author.
 *
 * [WIZDAM] Dipecah dari SearchHandler.inc.php (sebelumnya satu class
 * menangani search+authors+titles+categories+category sekaligus) menjadi
 * 4 class terpisah: SearchHandler (base + Search itu sendiri),
 * AuthorSearchHandler (file ini), TitleSearchHandler, CategorySearchHandler.
 * Isi method authors() di bawah ini SALINAN PERSIS dari SearchHandler.inc.php
 * asli -- termasuk seluruh perbaikan N+1 (memoization getAuthorUserMatch,
 * batch DAO lookups) yang sudah dikerjakan sebelumnya -- tidak ada logika
 * yang diubah, cuma dipindah lokasi filenya.
 */

import('pages.search.SearchHandler');

class AuthorSearchHandler extends SearchHandler {

    /**
     * Show index of published articles by author.
     * 
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function authors($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $this->validate();
        $this->setupTemplate($request, true);

        $journal = $request->getJournal();
        
        /** @var AuthorDAO $authorDao */
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        if (isset($args[0]) && $args[0] === 'view') {
            $firstName = trim((string) $request->getUserVar('firstName'));
            $middleName = trim((string) $request->getUserVar('middleName'));
            $lastName = trim((string) $request->getUserVar('lastName'));
            $affiliation = trim((string) $request->getUserVar('affiliation'));
            $country = trim((string) $request->getUserVar('country'));
            
            // 1. Get Author ID
            $authorId = $request->getUserVar('authorId');
            if (!$authorId && $firstName && $lastName && $authorDao) {
                $authorId = $authorDao->getAuthorIdByName($firstName, $lastName);
            }

            // 2. Get Author Basic Data
            $authorData = ['email' => null, 'url' => null, 'orcid' => null];
            if ($authorId && $authorDao) {
                $fetchedData = $authorDao->getAuthorAdditionalData($authorId);
                if (is_array($fetchedData)) {
                    $authorData = array_merge($authorData, $fetchedData);
                }
            }

            // 3. User Matching & Profile Data
            $matchData = [];
            if ($userDao) {
                $matchData = $userDao->getAuthorUserMatch(
                    $firstName,
                    $lastName,
                    $authorData['email'] ?? null,
                    $authorData['orcid'] ?? null
                ) ?: [];
            }

            // 4. Process Affiliations
            $affiliationsArray = !empty($affiliation) 
                ? array_filter(explode("\n", $affiliation), 'trim') 
                : [];

            // 5. Assign to Template
            $templateMgr = TemplateManager::getManager($request);
            
            // [WIZDAM] Micro-payloads
            $templateMgr->assign([
                'authorId' => $authorId,
                'authorEmail' => $authorData['email'] ?? null,
                'authorUrl' => $authorData['url'] ?? null,
                'authorOrcid' => $authorData['orcid'] ?? null,
                'matchedUserId' => $matchData['userId'] ?? null,
                'hasProfileImage' => !empty($matchData['hasImage']),
                'profileImageUrl' => $matchData['imgUrl'] ?? '',
                'user' => $matchData['user'] ?? new User(),
                'affiliations' => $affiliationsArray
            ]);

            $publishedArticles = $authorDao ? $authorDao->getPublishedArticlesForAuthor(
                $journal ? $journal->getId() : null, 
                $firstName, 
                $middleName, 
                $lastName, 
                $affiliation, 
                $country
            ) : [];

            // Inject User Match Data
            foreach ($publishedArticles as $article) {
                $authors = $article->getAuthors();
                foreach ($authors as $author) {
                    $authorMatchData = $userDao ? $userDao->getAuthorUserMatch(
                        $author->getFirstName(), 
                        $author->getLastName(), 
                        $author->getEmail(), 
                        $author->getData('orcid')
                    ) : [];

                    if (!empty($authorMatchData['found'])) {
                        $author->setData('id', $authorMatchData['userId'] ?? null);
                        $author->setData('isVerifiedAuthor', true);
                        $author->setData('userGender', $authorMatchData['gender'] ?? '');
                        $author->setData('hasProfileImage', !empty($authorMatchData['hasImage']));
                        $author->setData('profileImageUrl', $authorMatchData['imgUrl'] ?? '');
                        $author->setData('userInterests', $authorMatchData['interests'] ?? '');
                        $author->setData('userSalutation', $authorMatchData['salutation'] ?? '');
                        $author->setData('userPhone', $authorMatchData['phone'] ?? '');
                        $author->setData('userFax', $authorMatchData['fax'] ?? '');
                    } else {
                        $author->setData('isVerifiedAuthor', false);
                    }
                }
            }

            $journals = [];
            $issues = [];
            $sections = [];
            $issuesUnavailable = [];

            /** @var IssueDAO $issueDao */
            $issueDao = DAORegistry::getDAO('IssueDAO');
            /** @var SectionDAO $sectionDao */
            $sectionDao = DAORegistry::getDAO('SectionDAO');
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');

            foreach ($publishedArticles as $article) {
                $articleId = $article->getId();
                $issueId = $article->getIssueId();
                $sectionId = $article->getSectionId();
                $journalId = $article->getJournalId();

                if (!isset($issues[$issueId]) && $issueDao) {
                    import('classes.issue.IssueAction');
                    $issue = $issueDao->getIssueById($issueId);
                    $issues[$issueId] = $issue;
                    $issuesUnavailable[$issueId] = $issue && IssueAction::subscriptionRequired($issue) 
                        && (!IssueAction::subscribedUser($journal, $issueId, $articleId) 
                        && !IssueAction::subscribedDomain($journal, $issueId, $articleId));
                }
                if (!isset($journals[$journalId]) && $journalDao) {
                    $journals[$journalId] = $journalDao->getById($journalId);
                }
                if (!isset($sections[$sectionId]) && $sectionDao) {
                    $sections[$sectionId] = $sectionDao->getSection($sectionId, $journalId, true);
                }
            }

            if (empty($publishedArticles)) {
                $request->redirect(null, $request->getRequestedPage());
            }

            // [WIZDAM] Micro-payloads
            $templateMgr->assign([
                'publishedArticles' => $publishedArticles,
                'issues' => $issues,
                'issuesUnavailable' => $issuesUnavailable,
                'sections' => $sections,
                'journals' => $journals,
                'firstName' => $firstName,
                'middleName' => $middleName,
                'lastName' => $lastName,
                'affiliation' => $affiliation
            ]);

            /** @var CountryDAO $countryDao */
            $countryDao = DAORegistry::getDAO('CountryDAO');
            $countryObj = $countryDao ? $countryDao->getCountry($country) : null;
            $templateMgr->assign('country', $countryObj);

            $templateMgr->display('search/authorDetails.tpl');

        } else {
            $searchInitial = trim((string) $request->getUserVar('searchInitial'));
            $searchInitial = preg_match('/^[A-Z]$/i', $searchInitial) ? strtoupper($searchInitial) : '';
            
            $rangeInfo = $this->getRangeInfo('authors');

            $authorsFactory = $authorDao ? $authorDao->getAuthorsAlphabetizedByJournal(
                $journal ? $journal->getId() : null,
                $searchInitial,
                $rangeInfo,
                true
            ) : null;
            
            $authors = $authorsFactory ? $authorsFactory->toArray() : [];

            foreach ($authors as $key => $author) {
                // 1. Fix Missing ID
                if (empty($author->getId()) && $authorDao) {
                    $recoveredId = $authorDao->getAuthorIdByName(
                        $author->getFirstName(), 
                        $author->getLastName()
                    );
                    if ($recoveredId) {
                        $author->setData('id', $recoveredId);
                    }
                }

                // 2. Check User Match
                $matchData = $userDao ? $userDao->getAuthorUserMatch(
                    $author->getFirstName(),
                    $author->getLastName(),
                    $author->getEmail(),
                    $author->getData('orcid')
                ) : [];

                if (!empty($matchData['found'])) {
                    $author->setData('id', $matchData['userId'] ?? null);
                    $author->setData('isVerifiedAuthor', true);
                    $author->setData('userGender', $matchData['gender'] ?? '');
                    $author->setData('hasProfileImage', !empty($matchData['hasImage']));
                    $author->setData('profileImageUrl', $matchData['imgUrl'] ?? '');
                    $author->setData('userInterests', $matchData['interests'] ?? '');
                    $author->setData('userSalutation', $matchData['salutation'] ?? '');
                    $author->setData('userPhone', $matchData['phone'] ?? '');
                    $author->setData('userFax', $matchData['fax'] ?? '');
                } else {
                    $author->setData('isVerifiedAuthor', false);
                }
                
                $authors[$key] = $author;
            }

            import('lib.pkp.classes.core.VirtualArrayIterator');
            $totalCount = $authorsFactory ? $authorsFactory->getCount() : count($authors);
            $currentPage = $authorsFactory ? $authorsFactory->getPage() : 1;
            $itemsPerPage = ($rangeInfo && $rangeInfo->isValid()) ? $rangeInfo->getCount() : max(1, count($authors));
            $authorsIterator = new VirtualArrayIterator($authors, $totalCount, $currentPage, $itemsPerPage);

            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign([
                'searchInitial' => $request->getUserVar('searchInitial'),
                'alphaList' => explode(' ', __('common.alphaList')),
                'authors' => $authorsIterator
            ]);
            
            $templateMgr->display('search/authorIndex.tpl');
        }
    }

}
?>