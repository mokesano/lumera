<?php
declare(strict_types=1);

/**
 * @file plugins/generic/browse/pages/BrowseHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BrowseHandler
 * @ingroup plugins_generic_browse
 *
 * @brief Handle requests for additional browse functions.
 */

import('classes.handler.Handler');
import('lib.pkp.classes.core.VirtualArrayIterator');

class BrowseHandler extends Handler {

    /**
     * Show list of journal sections.
     * @param array $args
     * @param PKPRequest $request
     */
    public function sections($args = [], $request) {
        $this->setupTemplate($request, true);

        $journal = $request->getRouter()?->getContext($request);
        if (!$journal) {
            $request->redirect(null, 'index');
            return;
        }

        /** @var GenericPlugin $browsePlugin */
        $browsePlugin = PluginRegistry::getPlugin('generic', BROWSE_PLUGIN_NAME);
        if (!$browsePlugin) {
            $request->redirect(null, 'index');
            return;
        }

        $enableBrowseBySections = (bool) $browsePlugin->getSetting($journal->getId(), 'enableBrowseBySections');
        
        if ($enableBrowseBySections) {
            if (isset($args[0]) && $args[0] === 'view') {
                $sectionId = (int) $request->getUserVar('sectionId');

                /** @var SectionDAO $sectionDao */
                $sectionDao = DAORegistry::getDAO('SectionDAO');
                $section = $sectionDao->getSection($sectionId);
                if (!$section) {
                    $request->redirect(null, 'index');
                    return;
                }

                /** @var PublishedArticleDAO $publishedArticleDao */
                $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
                $publishedArticleIdsRaw = $publishedArticleDao->getPublishedArticleIdsBySection($sectionId);
                $publishedArticleIds = is_array($publishedArticleIdsRaw) ? $publishedArticleIdsRaw : [];

                $rangeInfo = Handler::getRangeInfo('search');
                $page = (int) ($rangeInfo?->getPage() ?? 1);
                $count = ($rangeInfo && $rangeInfo->getCount() > 0) ? (int) $rangeInfo->getCount() : 25;

                $totalResults = count($publishedArticleIds);
                $publishedArticleIds = array_slice($publishedArticleIds, $count * ($page - 1), $count);
                $results = new VirtualArrayIterator(ArticleSearch::formatResults($publishedArticleIds), $totalResults, $page, $count);

                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('results', $results);
                $templateMgr->assign('title', $section->getLocalizedTitle());
                $templateMgr->assign('sectionId', $sectionId);
                $templateMgr->assign('enableBrowseBySections', $enableBrowseBySections);
                $templateMgr->display($browsePlugin->getTemplatePath() . 'searchDetails.tpl');
            } else {
                $excludedSections = $browsePlugin->getSetting($journal->getId(), 'excludedSections');
                $excludedSectionsArray = is_array($excludedSections) ? $excludedSections : [];
                
                /** @var SectionDAO $sectionDao */
                $sectionDao = DAORegistry::getDAO('SectionDAO');
                $sectionsIterator = $sectionDao->getJournalSections($journal->getId());
                $sections = [];
                
                while ($sectionsIterator && ($section = $sectionsIterator->next())) {
                    if (!in_array($section->getId(), $excludedSectionsArray, true)) { 
                        $sections[$section->getLocalizedTitle()] = $section->getId();
                    }
                }
                ksort($sections);

                $rangeInfo = Handler::getRangeInfo('search');
                $page = (int) ($rangeInfo?->getPage() ?? 1);
                $count = ($rangeInfo && $rangeInfo->getCount() > 0) ? (int) $rangeInfo->getCount() : 25;

                $totalResults = count($sections);
                $sections = array_slice($sections, $count * ($page - 1), $count);
                $results = new VirtualArrayIterator($sections, $totalResults, $page, $count);

                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('results', $results);
                $templateMgr->assign('enableBrowseBySections', $enableBrowseBySections);
                $templateMgr->display($browsePlugin->getTemplatePath() . 'searchIndex.tpl');
            }
        } else {
            $request->redirect(null, 'index');
        }
    }

    /**
     * Show list of journal sections identify types.
     * @param array $args
     * @param PKPRequest $request
     */
    public function identifyTypes($args = [], $request) {
        $this->setupTemplate($request, true);

        $journal = $request->getRouter()?->getContext($request);
        if (!$journal) {
            $request->redirect(null, 'index');
            return;
        }

        /** @var GenericPlugin $browsePlugin */
        $browsePlugin = PluginRegistry::getPlugin('generic', BROWSE_PLUGIN_NAME);
        if (!$browsePlugin) {
            $request->redirect(null, 'index');
            return;
        }

        $enableBrowseByIdentifyTypes = (bool) $browsePlugin->getSetting($journal->getId(), 'enableBrowseByIdentifyTypes');
        
        if ($enableBrowseByIdentifyTypes) {
            if (isset($args[0]) && $args[0] === 'view') {
                $identifyType = trim((string) ($request->getUserVar('identifyType') ?? ''));
                
                /** @var SectionDAO $sectionDao */
                $sectionDao = DAORegistry::getDAO('SectionDAO');
                $sectionsIterator = $sectionDao->getJournalSections($journal->getId());
                $sections = [];
                
                while ($sectionsIterator && ($section = $sectionsIterator->next())) {
                    if ($section->getLocalizedIdentifyType() === $identifyType) {
                        $sections[] = $section;
                    }
                }
                
                /** @var PublishedArticleDAO $publishedArticleDao */
                $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
                $publishedArticleIds = [];
                foreach ($sections as $section) {
                    $publishedArticleIdsRaw = $publishedArticleDao->getPublishedArticleIdsBySection($section->getId());
                    $publishedArticleIdsBySection = is_array($publishedArticleIdsRaw) ? $publishedArticleIdsRaw : [];
                    $publishedArticleIds = array_merge($publishedArticleIds, $publishedArticleIdsBySection);
                }

                $rangeInfo = Handler::getRangeInfo('search');
                $page = (int) ($rangeInfo?->getPage() ?? 1);
                $count = ($rangeInfo && $rangeInfo->getCount() > 0) ? (int) $rangeInfo->getCount() : 25;

                $totalResults = count($publishedArticleIds);
                $publishedArticleIds = array_slice($publishedArticleIds, $count * ($page - 1), $count);
                $results = new VirtualArrayIterator(ArticleSearch::formatResults($publishedArticleIds), $totalResults, $page, $count);

                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('results', $results);
                $templateMgr->assign('title', $identifyType);
                $templateMgr->assign('enableBrowseByIdentifyTypes', $enableBrowseByIdentifyTypes);
                $templateMgr->display($browsePlugin->getTemplatePath() . 'searchDetails.tpl');
            } else {
                $excludedIdentifyTypes = $browsePlugin->getSetting($journal->getId(), 'excludedIdentifyTypes');
                $excludedIdentifyTypesArray = is_array($excludedIdentifyTypes) ? $excludedIdentifyTypes : [];
                
                /** @var SectionDAO $sectionDao */
                $sectionDao = DAORegistry::getDAO('SectionDAO');
                $sectionsIterator = $sectionDao->getJournalSections($journal->getId());
                $sectionidentifyTypes = [];
                
                while ($sectionsIterator && ($section = $sectionsIterator->next())) {
                    $type = $section->getLocalizedIdentifyType();
                    if ($type && !in_array($section->getId(), $excludedIdentifyTypesArray, true) && !in_array($type, $sectionidentifyTypes, true)) {
                        $sectionidentifyTypes[] = $type;
                    }
                }
                sort($sectionidentifyTypes);

                $rangeInfo = Handler::getRangeInfo('search');
                $page = (int) ($rangeInfo?->getPage() ?? 1);
                $count = ($rangeInfo && $rangeInfo->getCount() > 0) ? (int) $rangeInfo->getCount() : 25;

                $totalResults = count($sectionidentifyTypes);
                $sectionidentifyTypes = array_slice($sectionidentifyTypes, $count * ($page - 1), $count);
                $results = new VirtualArrayIterator($sectionidentifyTypes, $totalResults, $page, $count);

                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('results', $results);
                $templateMgr->assign('enableBrowseByIdentifyTypes', $enableBrowseByIdentifyTypes);
                $templateMgr->display($browsePlugin->getTemplatePath() . 'searchIndex.tpl');
            }
        } else {
            $request->redirect(null, 'index');
        }
    }

    /**
     * Ensure that we have a journal and the plugin is enabled.
     * @param PKPRequest $request
     * @param array $args
     * @param array $roleAssignments
     * @return bool
     */
    public function authorize($request, $args, $roleAssignments) {
        $journal = $request->getRouter()?->getContext($request);
        if (!$journal) {
            return false;
        }
        
        /** @var GenericPlugin $browsePlugin */
        $browsePlugin = PluginRegistry::getPlugin('generic', BROWSE_PLUGIN_NAME);
        if (!$browsePlugin || !$browsePlugin->getEnabled()) {
            return false;
        }
        
        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * Setup common template variables.
     * @param PKPRequest|null $request
     * @param bool $subclass
     * @param string $op
     * @return void
     */
    public function setupTemplate($request = null, $subclass = false, $op = 'index') {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('helpTopicId', 'user.searchAndBrowse');

        $opMap = [
            'index' => 'navigation.search',
            'categories' => 'navigation.categories'
        ];

        if ($request) {
            $templateMgr->assign('pageHierarchy',
                $subclass ? [[$request->url(null, 'search', $op), $opMap[$op] ?? 'navigation.search']] : []
            );

            $journal = $request->getRouter()?->getContext($request);
            if (!$journal || !$journal->getSetting('restrictSiteAccess')) {
                $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
            }
        }
    }
    
}
?>