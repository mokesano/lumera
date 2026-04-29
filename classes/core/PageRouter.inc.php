<?php
declare(strict_types=1);

/**
 * @file classes/core/PageRouter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PageRouter
 * @ingroup core
 *
 * @brief Class providing OJS-specific page routing.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Ref removal, Visibility)
 * - Safe User/Journal Context Checks
 * - Native Routing Support
 * - [v2] Degradasi Routing Bertingkat: Issue → Volume → Year → Archive
 */

import('lib.pkp.classes.core.PKPPageRouter');

class PageRouter extends PKPPageRouter {

    /**
     * get the cacheable pages
     * @return array
     */
    public function getCacheablePages() {
        return array('about', 'announcement', 'help', 'index', 'information', 'rt', 'issue', '');
    }

    /**
     * Override _getRequestedUrlParts to handle "native" routing.
     *
     * ROUTING TABLE (diproses berurutan, paling spesifik dahulu):
     *
     *  Rule 1  : /volumes/{vol}/issue/{slug}[/showToc]  → IssueHandler::view()
     *            Fallback jika slug tidak resolve ke issue → VolumesHandler::view()
     *  Rule 1b : /volumes/{vol}/issue/                  → VolumesHandler::view()
     *            (URL malformed: slug kosong, hasil dari issue null)
     *  Rule 2  : /volumes/{vol}                         → VolumesHandler::view()
     *  Rule 3  : /volumes/                              → VolumesHandler::displayArchive()
     *  Rule 4  : /year/{year}                           → VolumesHandler::year()
     *            (Untuk Level 3 degradasi: volume null)
     *
     * @param array $callback
     * @param PKPRequest $request
     * @return mixed
     */
    public function _getRequestedUrlParts($callback, $request) {

        $url = null;
        if ($request->isPathInfoEnabled()) {
            if (isset($_SERVER['PATH_INFO'])) {
                $url = $_SERVER['PATH_INFO'];
            }
        } else {
            $url = $request->getCompleteUrl();
        }

        $page  = null;
        $op    = null;
        $args  = array();
        $found = false;

        // =====================================================================
        // Rule 1: /volumes/{vol}/issue/{slug}[/showToc]
        // Paling spesifik, harus dicek pertama.
        // =====================================================================
        if (preg_match('#/volumes/([0-9]+)/issue/([^/]+)(/showToc)?/?$#i', (string)$url, $matches)) {

            $volumeNumber = $matches[1];
            $urlSlug      = $matches[2];

            // Pastikan slug tidak benar-benar kosong setelah capture
            // (pattern [^/]+ seharusnya tidak match string kosong, tapi perlindungan ekstra)
            if ($urlSlug !== '') {
                // --- Terjemahan Slug → Issue ID ---
                $issueId = null;
                $journal = $request->getJournal();

                if ($journal) {
                    $issueDao = DAORegistry::getDAO('IssueDAO');
                    $issuesIterator = $issueDao->getPublishedIssuesByVolume($journal->getId(), $volumeNumber);

                    while ($issue = $issuesIterator->next()) {
                        $issueNumberStr = (string) $issue->getNumber();

                        if ($issueNumberStr === '') {
                            // Issue number kosong di DB → gunakan ID sebagai identifier
                            // Tapi ini seharusnya tidak pernah masuk Rule 1 dari URL
                            // (URL issue bernilai null seharusnya sudah didegradasi ke volume)
                            $expectedSlug = (string) $issue->getId();
                        } else {
                            $dbSlug = PKPString::slugify($issueNumberStr);
                            $expectedSlug = ($dbSlug !== '') ? $dbSlug : (string) $issue->getId();
                        }

                        if ($expectedSlug === $urlSlug) {
                            $issueId = $issue->getId();
                            break;
                        }
                    }
                }
                // --- Akhir Terjemahan ---

                if ($issueId) {
                    // *** Issue DITEMUKAN: Route normal ke IssueHandler::view() ***
                    $page = 'issue';
                    $op   = 'view';
                    $args = array($issueId);
                    if (isset($matches[3]) && $matches[3] === '/showToc') {
                        $args[] = 'showToc';
                    }
                    $found = true;
                } else {
                    // *** Issue TIDAK DITEMUKAN untuk slug ini ***
                    // Degradasi Level 2: Arahkan ke detail volume.
                    // VolumesHandler::view() akan menangani 301 redirect permanen
                    // karena mendeteksi bahwa request berasal dari URL /issue/{slug}.
                    $page  = 'volumes';
                    $op    = 'view';
                    $args  = array($volumeNumber);
                    $found = true;
                }
            }
            // Jika slug kosong (seharusnya tidak terjadi dengan pattern ini),
            // biarkan jatuh ke Rule 1b di bawah → tidak ada 'else' di sini
        }

        // =====================================================================
        // Rule 1b: /volumes/{vol}/issue/  (URL malformed: slug kosong)
        //
        // Terjadi ketika issue->getNumber() kosong DAN kode lama/tempel
        // menghasilkan URL akhiran "/issue/" tanpa slug.
        // Degradasi langsung ke VolumesHandler::view() dengan sinyal 301.
        // =====================================================================
        elseif (preg_match('#/volumes/([0-9]+)/issue/?$#i', (string)$url, $matches)) {
            $page  = 'volumes';
            $op    = 'view';
            $args  = array($matches[1]);
            $found = true;
            // Flag khusus agar VolumesHandler tahu ini degradasi dari issue null
            // dan bisa melakukan 301 redirect permanen ke URL canonical volume.
            // Kita set di $_SERVER agar tidak perlu mengubah signature handler.
            $_SERVER['WIZDAM_DEGRADED_FROM_ISSUE'] = '1';
        }

        // =====================================================================
        // Rule 2: /volumes/{vol}
        // =====================================================================
        elseif (preg_match('#/volumes/([0-9]+)/?$#i', (string)$url, $matches)) {
            $page  = 'volumes';
            $op    = 'view';
            $args  = array($matches[1]);
            $found = true;
        }

        // =====================================================================
        // Rule 3: /volumes/  (Halaman Arsip Utama)
        // =====================================================================
        elseif (preg_match('#/volumes/?$#i', (string)$url, $matches)) {
            $page  = 'volumes';
            $op    = 'displayArchive';
            $args  = array();
            $found = true;
        }

        // =====================================================================
        // Rule 4: /year/{year}  (Degradasi Level 3: Volume null)
        //
        // Digunakan ketika issue->getVolume() kosong/null.
        // Ditangani oleh VolumesHandler::year().
        // =====================================================================
        elseif (preg_match('#/year/([0-9]{4})/?$#i', (string)$url, $matches)) {
            $page  = 'volumes';
            $op    = 'year';
            $args  = array($matches[1]);
            $found = true;
        }

        // =====================================================================
        // Dispatch
        // =====================================================================
        if ($found) {
            if ($callback[1] === 'getPage') return $page;
            if ($callback[1] === 'getOp')   return $op;
            if ($callback[1] === 'getArgs') return $args;
        } else {
            return parent::_getRequestedUrlParts($callback, $request);
        }
    }

    /**
     * Override url() to generate "native" URLs untuk volumes dan issues.
     * LOGIKA PEMBANGUNAN URL BERTINGKAT:
     * $page == 'volumes', $op == 'year'  → /{context}/year/{year}
     * $page == 'volumes', $op == 'view'  → /{context}/volumes/{vol}
     * $page == 'volumes', $op == 'view', $path is array [vol,'issue',slug]
     *                                   → /{context}/volumes/{vol}/issue/{slug}
     * $page == 'volumes' (default)       → /{context}/volumes/
     *
     * Degradasi otomatis terjadi di sisi template/handler yang memanggil url():
     * Jika slug kosong, jangan gunakan format array — cukup kirim $path = $volumeId.
     *
     * @see PKPRouter::url()
     * @param PKPRequest $request
     * @param string|null $newContext
     * @param string|null $page
     * @param string|null $op
     * @param mixed|null $path
     * @param array|null $params
     * @param string|null $anchor
     * @param bool $escape
     * @return string
     */
    public function url($request, $newContext = null, $page = null, $op = null, $path = null, $params = null, $anchor = null, $escape = false) {

        if ($page === 'volumes') {
            $newContext = $this->_urlCanonicalizeNewContext($newContext);
            $baseUrlAndContext = $this->_urlGetBaseAndContext($request, $newContext);
            $baseUrl = array_shift($baseUrlAndContext);
            $context = $baseUrlAndContext;

            $pathInfoArray = $context;

            // --- Skenario Y: Year URL (Level 3 Degradasi) ---
            // url(page='volumes', op='year', path='2023')
            if ($op === 'year' && $path && !is_array($path)) {
                $pathInfoArray[] = 'year';
                $pathInfoArray[] = $path;
            }

            // --- Skenario A: Detail Volume saja ---
            // url(page='volumes', op='view', path='{vol}')
            elseif ($op === 'view' && $path && !is_array($path)) {
                $pathInfoArray[] = 'volumes';
                $pathInfoArray[] = $path;
            }

            // --- Skenario B: Detail Issue (ada slug) ---
            // url(page='volumes', path=[vol, 'issue', slug])
            // PENTING: Hanya bangun URL /issue/{slug} jika slug TIDAK kosong.
            elseif (is_array($path)) {
                $vol  = isset($path[0]) ? (string) $path[0] : '';
                $slug = isset($path[2]) ? (string) $path[2] : '';

                if ($slug !== '' && $vol !== '') {
                    // Level 1 (Normal): Ada slug DAN volume
                    $pathInfoArray[] = 'volumes';
                    $pathInfoArray[] = $vol;
                    $pathInfoArray[] = $path[1]; // 'issue'
                    $pathInfoArray[] = $slug;
                } elseif ($vol !== '') {
                    // Level 2 (Degradasi): Slug kosong, tapi volume ada
                    // Hasilkan /volumes/{vol} tanpa /issue/
                    $pathInfoArray[] = 'volumes';
                    $pathInfoArray[] = $vol;
                } else {
                    // Level 3 (Degradasi Penuh): Volume juga kosong → arsip utama
                    $pathInfoArray[] = 'volumes';
                }
            }

            // --- Skenario C: Halaman Arsip Utama ---
            // url(page='volumes') atau url(page='volumes', op='displayArchive')
            else {
                $pathInfoArray[] = 'volumes';
            }

            $queryParametersArray = $this->_urlGetAdditionalParameters($request, $params, $escape);
            $anchor = (empty($anchor) ? '' : '#'.rawurlencode((string)$anchor));

            return $this->_urlFromParts($baseUrl, $pathInfoArray, $queryParametersArray, $anchor, $escape);
        }

        return parent::url($request, $newContext, $page, $op, $path, $params, $anchor, $escape);
    }

    /**
     * Redirect to user home page.
     * @param PKPRequest $request
     */
    public function redirectHome($request) {
        $user = $request->getUser();

        if (!$user) {
            $request->redirect('index', 'user');
            return;
        }

        $roleDao = DAORegistry::getDAO('RoleDAO');
        $userId  = $user->getId();
        $journal = $this->getContext($request, 1);

        if ($journal instanceof Journal) {
            $roles = $roleDao->getRolesByUserId($userId, $journal->getId());

            if (count($roles) == 1) {
                $role = array_shift($roles);
                if ($role->getRoleId() == ROLE_ID_READER) {
                    $request->redirect(null, 'user');
                } else {
                    $request->redirect(null, $role->getRolePath());
                }
            } else {
                $request->redirect(null, 'user');
            }
        } else {
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $roles      = $roleDao->getRolesByUserId($userId);

            if (count($roles) == 1) {
                $role    = array_shift($roles);
                $journal = $journalDao->getById($role->getJournalId());

                if (!$journal) {
                    $request->redirect('index', 'user');
                    return;
                }

                if ($role->getRoleId() == ROLE_ID_READER) {
                    $request->redirect('index', 'user');
                } else {
                    $request->redirect($journal->getPath(), $role->getRolePath());
                }
            } else {
                $request->redirect('index', 'user');
            }
        }
    }
}

?>