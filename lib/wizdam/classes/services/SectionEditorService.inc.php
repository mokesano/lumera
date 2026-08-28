<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/SectionEditorService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class SectionEditorService
 * @ingroup wizdam_services
 *
 * @brief [WIZDAM N+1 FIX] Satu-satunya sumber kebenaran untuk membangun
 * data section editors (nama, foto -- lewat objek User, afiliasi, negara)
 * per section sebuah jurnal, dipakai BERSAMA oleh
 * AboutJournalHandler::editorialPolicies() dan
 * PoliciesHandler::sectionPolicies() -- sebelumnya kedua handler ini
 * punya SALINAN LOGIC YANG NYARIS IDENTIK, dan KEDUANYA (dengan tingkat
 * keparahan berbeda) mengalami N+1:
 *
 * - AboutJournalHandler::editorialPolicies(): TIDAK ADA caching sama
 *   sekali -- $userDao->getById() dipanggil ULANG untuk SETIAP entry
 *   editor di SETIAP section, walau user yang sama muncul di section
 *   lain. getEditorsBySectionId() sendiri juga 1 query TERPISAH per
 *   section (N section = N query, di luar query per-editor).
 *
 * - PoliciesHandler::sectionPolicies(): SUDAH ada $userCache parsial
 *   (menghindari getById() berulang untuk user YANG SAMA), tapi masih
 *   panggil getEditorsBySectionId() per section (N+1 di level section),
 *   dan getById()-nya sendiri di baliknya masih 2 query terpisah
 *   (users + user_settings) untuk SETIAP user unik.
 *
 * Service ini eliminasi KEDUA lapis N+1 sekaligus:
 * 1. SectionEditorsDAO::getEditorsGroupedByJournalId() -- SATU query
 *    untuk SEMUA section jurnal sekaligus (bukan N query per section).
 * 2. SectionEditorsDAO::enrichUsersWithSettings() -- SATU query
 *    user_settings untuk SEMUA user unik sekaligus (bukan getById()
 *    terpisah per user, yang masing-masing 2 query).
 * 3. Afiliasi/negara dihitung SEKALI per user unik, hasilnya dipakai
 *    ulang untuk SEMUA section tempat user itu jadi editor (bukan
 *    dihitung ulang tiap kemunculan).
 *
 * Untuk jurnal dengan, misalnya, 5 section dan 15 editor unik (beberapa
 * merangkap di >1 section): SEBELUMNYA bisa 5 (section) + hingga ~30-45
 * (getById + settings per kemunculan editor, termasuk yang merangkap
 * dihitung berkali-kali) = 35-50 query. SEKARANG: 1 (semua section) + 1
 * (semua user_settings) = 2 query, TERLEPAS dari jumlah section/editor.
 */

import('classes.journal.SectionEditorsDAO');

class SectionEditorService {

    /**
     * Bangun data section editors siap-template untuk SEMUA section
     * sebuah jurnal, dikelompokkan per section_id -- bentuk hasil PERSIS
     * sama dengan yang sudah dikonsumsi about/editorialPolicies.tpl DAN
     * policies/sectionPolicies.tpl ($sectionEditorEntry.user /
     * .affiliationString / .countryString), jadi kedua handler tinggal
     * assign hasilnya langsung tanpa perlu ubah template sama sekali.
     *
     * @param Journal $journal
     * @return array<int, array> [$sectionId => [['user' => User, 'affiliationString' => string, 'affiliations' => array, 'interests' => string, 'countryString' => string], ...]]
     */
    public static function getSectionEditorEntriesGroupedBySection($journal): array {
        $journalId = (int) $journal->getId();

        /** @var SectionEditorsDAO $sectionEditorsDao */
        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO');

        // Langkah 1: SATU query untuk seluruh editor di seluruh section jurnal.
        $rawGroupedBySection = $sectionEditorsDao->getEditorsGroupedByJournalId($journalId);
        if (empty($rawGroupedBySection)) {
            return [];
        }

        // Langkah 2: Kumpulkan objek User UNIK (dedupe by userId) -- editor
        // yang merangkap di beberapa section cuma dihitung SEKALI di sini.
        $uniqueUsersById = [];
        foreach ($rawGroupedBySection as $entries) {
            foreach ($entries as $entry) {
                $user = $entry['user'];
                $uniqueUsersById[(int) $user->getId()] = $user;
            }
        }

        // Langkah 3: SATU query user_settings untuk SEMUA user unik --
        // melengkapi objek User dasar (dari JOIN section_editors, belum
        // ada affiliation/interests) langsung di tempat (object handle).
        $sectionEditorsDao->enrichUsersWithSettings($uniqueUsersById);

        // Langkah 4: Hitung data kaya (afiliasi, negara) SEKALI per user
        // unik -- bukan per kemunculan section.
        $primaryLocale = $journal->getPrimaryLocale();
        if (empty($primaryLocale)) {
            $primaryLocale = AppLocale::getLocale();
        }
        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');

        $enrichedDataByUserId = [];
        foreach ($uniqueUsersById as $userId => $user) {
            $enrichedDataByUserId[$userId] = self::_buildEnrichedEditorData($user, $primaryLocale, $countryDao);
        }

        // Langkah 5: Susun ulang per section, pakai data kaya yang SUDAH
        // dihitung (bukan hitung ulang) untuk tiap kemunculan user.
        $result = [];
        foreach ($rawGroupedBySection as $sectionId => $entries) {
            $richEntries = [];
            foreach ($entries as $entry) {
                $userId = (int) $entry['user']->getId();
                $richEntries[] = $enrichedDataByUserId[$userId];
            }
            $result[$sectionId] = $richEntries;
        }

        return $result;
    }

    /**
     * @param User $user
     * @param string $primaryLocale
     * @param CountryDAO $countryDao
     * @return array
     */
    private static function _buildEnrichedEditorData($user, string $primaryLocale, $countryDao): array {
        $rawAffiliation = (string) $user->getAffiliation($primaryLocale);
        if ($rawAffiliation === '') {
            $rawAffiliation = (string) $user->getLocalizedAffiliation();
        }
        $affiliationsArray = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $rawAffiliation))));

        $interests = '';
        if (method_exists($user, 'getInterestString')) {
            $interests = $user->getInterestString();
        } elseif (method_exists($user, 'getInterestsString')) {
            $interests = $user->getInterestsString();
        } else {
            $interests = (string) $user->getData('interests');
        }
        if (trim($interests) === '') {
            $interestDao = DAORegistry::getDAO('InterestDAO');
            if ($interestDao && method_exists($interestDao, 'getInterestsString')) {
                $interests = $interestDao->getInterestsString((int) $user->getId());
            }
        }

        $countryCode = $user->getCountry();
        $countryName = '';
        if (!empty($countryCode)) {
            $countryName = $countryDao->getCountry($countryCode, $primaryLocale);
            if (empty($countryName)) {
                $countryName = $countryDao->getCountry($countryCode, 'en_US');
            }
        }

        return [
            'user' => $user,
            'affiliationString' => $rawAffiliation,
            'affiliations' => $affiliationsArray,
            'interests' => $interests,
            'countryString' => $countryName,
        ];
    }

}
?>