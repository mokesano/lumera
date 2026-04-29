<?php
declare(strict_types=1);

/**
 * @file plugins/generic/lucene/LucenePlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LucenePlugin
 * @ingroup plugins_generic_lucene
 *
 * @brief Lucene plugin class
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.plugins.GenericPlugin');
import('plugins.generic.lucene.classes.SolrWebService');

define('LUCENE_PLUGIN_DEFAULT_RANKING_BOOST', 1.0); // Default: No boost (=weight factor one).

class LucenePlugin extends GenericPlugin {

	/** @var SolrWebService */
	protected $_solrWebService;

	/** @var array */
	protected $_mailTemplates = [];

	/** @var string */
	protected $_spellingSuggestion;

	/** @var string */
	protected $_spellingSuggestionField;

	/** @var array */
	protected $_highlightedArticles;

	/** @var array */
	protected $_enabledFacetCategories;

	/** @var array */
	protected $_facets;


	/**
	 * Constructor
	 */
	public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LucenePlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::LucenePlugin(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }


	//
	// Getters and Setters
	//
	/**
	 * Get the solr web service.
	 * @return SolrWebService
	 */
	public function getSolrWebService() {
		return $this->_solrWebService;
	}

	/**
	 * Facets corresponding to a recent search (if any).
	 * @return boolean
	 */
	public function getFacets() {
		return $this->_facets;
	}

	/**
	 * Set an alternative article mailer implementation.
	 * NB: Required to override the mailer implementation for testing.
	 * @param string $emailKey
	 * @param MailTemplate $mailTemplate
	 */
	public function setMailTemplate($emailKey, $mailTemplate) {
		$this->_mailTemplates[$emailKey] = $mailTemplate;
	}

	/**
	 * Instantiate a MailTemplate object for the given email key.
	 * @param string $emailKey
	 * @param Journal $journal
	 */
	public function getMailTemplate($emailKey, $journal = null) {
		if (!isset($this->_mailTemplates[$emailKey])) {
			import('classes.mail.MailTemplate');
			$mailTemplate = new MailTemplate($emailKey, null, null, $journal, true, true);
			$this->_mailTemplates[$emailKey] = $mailTemplate;
		}
		return $this->_mailTemplates[$emailKey];
	}


	//
	// Implement template methods from PKPPlugin.
	//
	/**
     * Register the plugin
	 * @see PKPPlugin::register()
     * @param string $category
     * @param string $path
     * @return boolean
	 */
	public function register(string $category, string $path): bool {
		$success = parent::register($category, $path);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return $success;

		if ($success && $this->getEnabled()) {
			// Register callbacks (application-level).
			HookRegistry::register('PluginRegistry::loadCategory', [$this, 'callbackLoadCategory']);
			HookRegistry::register('LoadHandler', [$this, 'callbackLoadHandler']);

			// Register callbacks (data-access level).
			HookRegistry::register('articledao::getAdditionalFieldNames', [$this, 'callbackArticleDaoAdditionalFieldNames']);
			$customRanking = (boolean)$this->getSetting(0, 'customRanking');
			if ($customRanking) {
				HookRegistry::register('sectiondao::getAdditionalFieldNames', [$this, 'callbackSectionDaoAdditionalFieldNames']);
			}

			// Register callbacks (controller-level).
			HookRegistry::register('ArticleSearch::retrieveResults', [$this, 'callbackRetrieveResults']);
			HookRegistry::register('ArticleSearchIndex::articleMetadataChanged', [$this, 'callbackArticleMetadataChanged']);
			HookRegistry::register('ArticleSearchIndex::articleFileChanged', [$this, 'callbackArticleFileChanged']);
			HookRegistry::register('ArticleSearchIndex::articleFileDeleted', [$this, 'callbackArticleFileDeleted']);
			HookRegistry::register('ArticleSearchIndex::articleFilesChanged', [$this, 'callbackArticleFilesChanged']);
			HookRegistry::register('ArticleSearchIndex::suppFileMetadataChanged', [$this, 'callbackSuppFileMetadataChanged']);
			HookRegistry::register('ArticleSearchIndex::articleDeleted', [$this, 'callbackArticleDeleted']);
			HookRegistry::register('ArticleSearchIndex::articleChangesFinished', [$this, 'callbackArticleChangesFinished']);
			HookRegistry::register('ArticleSearchIndex::rebuildIndex', [$this, 'callbackRebuildIndex']);

			// Register callbacks (forms).
			if ($customRanking) {
				HookRegistry::register('sectionform::Constructor', [$this, 'callbackSectionFormConstructor']);
				HookRegistry::register('sectionform::initdata', [$this, 'callbackSectionFormInitData']);
				HookRegistry::register('sectionform::readuservars', [$this, 'callbackSectionFormReadUserVars']);
				HookRegistry::register('sectionform::execute', [$this, 'callbackSectionFormExecute']);
			}

			// Register callbacks (view-level).
			HookRegistry::register('TemplateManager::display', [$this, 'callbackTemplateDisplay']);
			if ($this->getSetting(0, 'autosuggest')) {
				HookRegistry::register('Templates::Search::SearchResults::FilterInput', [$this, 'callbackTemplateFilterInput']);
			}
			if ($customRanking) {
				HookRegistry::register('Templates::Manager::Sections::SectionForm::AdditionalMetadata', [$this, 'callbackTemplateSectionFormAdditionalMetadata']);
			}
			HookRegistry::register('Templates::Search::SearchResults::PreResults', [$this, 'callbackTemplatePreResults']);
			HookRegistry::register('Templates::Search::SearchResults::AdditionalArticleLinks', [$this, 'callbackTemplateAdditionalArticleLinks']);
			HookRegistry::register('Templates::Search::SearchResults::AdditionalArticleInfo', [$this, 'callbackTemplateAdditionalArticleInfo']);
			HookRegistry::register('Templates::Search::SearchResults::SyntaxInstructions', [$this, 'callbackTemplateSyntaxInstructions']);

			// Instantiate the web service.
			$searchHandler = $this->getSetting(0, 'searchEndpoint');
			$username = $this->getSetting(0, 'username');
			$password = $this->getSetting(0, 'password');
			$instId = $this->getSetting(0, 'instId');
			$useProxySettings = $this->getSetting(0, 'useProxySettings');
			if (!$useProxySettings) $useProxySettings = false;

			$this->_solrWebService = new SolrWebService($searchHandler, $username, $password, $instId, $useProxySettings);
		}
		return $success;
	}

	/**
     * Display name of plugin
	 * @see PKPPlugin::getDisplayName()
	 */
	public function getDisplayName(): string {
		return __('plugins.generic.lucene.displayName');
	}

	/**
     * Description of plugin.
	 * @see PKPPlugin::getDescription()
	 */
	public function getDescription(): string {
		return __('plugins.generic.lucene.description');
	}

	/**
     * Path to the plugin's settings file.
	 * @see PKPPlugin::getInstallSitePluginSettingsFile()
	 */
	public function getInstallSitePluginSettingsFile(): ?string {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
     * Path to the plugin's email templates file.
	 * @see PKPPlugin::getInstallEmailTemplatesFile()
	 */
	public function getInstallEmailTemplatesFile(): string {
		return ($this->getPluginPath() . '/emailTemplates.xml');
	}

	/**
     * Path to the plugin's email template data file.
	 * @see PKPPlugin::getInstallEmailTemplateDataFile()
	 */
	public function getInstallEmailTemplateDataFile(): string {
		return ($this->getPluginPath() . '/locale/{$installedLocale}/emailTemplates.xml');
	}

	/**
     * Indicates whether this plugin is a site plugin 
     * (i.e. a plugin that is not specific to a journal).
	 * @see PKPPlugin::isSitePlugin()
	 */
	public function isSitePlugin(): bool {
		return true;
	}

	/**
     * Path to the plugin's templates directory.
	 * @see PKPPlugin::getTemplatePath()
	 */
	public function getTemplatePath(): string {
		return parent::getTemplatePath() . 'templates/';
	}


	//
	// Implement template methods from GenericPlugin.
	//
	
	/**
     * Get the management verbs for this plugin.
	 * @see GenericPlugin::getManagementVerbs()
	 */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
       $verbs = parent::getManagementVerbs($verbs, $request);
       if ($this->getEnabled($request)) {
           $verbs[] = ['settings', __('plugins.generic.lucene.settings')];
       }
       return $verbs;
    }

	/**
     * Handle management actions for this plugin.
	 * @see GenericPlugin::manage()
	 */
	public function manage(string $verb, array $args, string $message, array $messageParams, $request = NULL): bool {
		if (!parent::manage($verb, $args, $message, $messageParams)) return false;

		switch ($verb) {
			case 'settings':
				$templateMgr = TemplateManager::getManager();
				$templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
				$this->import('classes.form.LuceneSettingsForm');
				$form = new LuceneSettingsForm($this);
				if (Request::getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						$request->redirect(null, 'manager', 'plugins', $this->getCategory());
						return false;
					} else {
						$this->_setBreadCrumbs();
						$form->display();
					}
				} else {
					$this->_setBreadCrumbs();
					$form->initData();
					$form->display();
				}
				return true;

			default:
				// Unknown management verb
				assert(false);
				return false;
		}
	}


	//
	// Application level hook implementations.
	//
	/**
     * Callback to load the plugin category.
	 * @see PluginRegistry::loadCategory()
     * @param $hookName
     * @param $args array
	 */
	public function callbackLoadCategory($hookName, $args) {
		// We only contribute to the block plug-in category.
		$category = $args[0];
		if ($category != 'blocks') return false;

		// We only contribute a plug-in if at least one
		// faceting category is enabled.
		$enabledFacetCategories = $this->_getEnabledFacetCategories();
		if (empty($enabledFacetCategories)) return false;

		// Instantiate the block plug-in for facets.
		$this->import('LuceneFacetsBlockPlugin');
		$luceneFacetsBlockPlugin = new LuceneFacetsBlockPlugin($this->getName());

		// Add the plug-in to the registry.
		$plugins =& $args[1];
		$seq = $luceneFacetsBlockPlugin->getSeq();
		if (!isset($plugins[$seq])) $plugins[$seq] = [];
		$plugins[$seq][$luceneFacetsBlockPlugin->getPluginPath()] = $luceneFacetsBlockPlugin;

		return false;
	}

	/**
     * Callback to load the plugin's handler.
	 * @see PKPPageRouter::route()
     * @param $hookName
     * @param $args array
	 */
	public function callbackLoadHandler($hookName, $args) {
		// Check the page.
		$page = $args[0];
		if ($page !== 'lucene') return;

		// Check the operation.
		$op = $args[1];
		$publicOps = [
			'queryAutocomplete',
			'pullChangedArticles',
			'similarDocuments'
		];
		if (!in_array($op, $publicOps)) return;

		// Looks as if our handler had been requested.
		define('HANDLER_CLASS', 'LuceneHandler');
		define('LUCENE_PLUGIN_NAME', $this->getName());
		$handlerFile =& $args[2];
		$handlerFile = $this->getPluginPath() . '/' . 'LuceneHandler.inc.php';
	}


	//
	// Data-access level hook implementations.
	//
	/**
     * Callback to add additional field names to the ArticleDAO.
	 * @see DAO::getAdditionalFieldNames()
     * @param $hookName
     * @param $args array
	 */
	public function callbackArticleDaoAdditionalFieldNames($hookName, $args) {
		// Add the indexing state setting to the field names.
		$returner =& $args[1];
		$returner[] = 'indexingState';
	}

	/**
	 * Callback to add additional field names to the SectionDAO.
	 * @see DAO::getAdditionalFieldNames()
     * @param $hookName
     * @param $args array
	 */
	public function callbackSectionDaoAdditionalFieldNames($hookName, $args) {
		// Add the custom ranking setting to the field names.
		$returner =& $args[1];
		$returner[] = 'rankingBoost';
	}

	//
	// Controller level hook implementations.
	//
	/**
     * Callback to retrieve search results from the solr web service.
	 * @see ArticleSearch::retrieveResults()
     * @param $hookName
     * @param $params array
	 */
	public function callbackRetrieveResults($hookName, $params) {
		assert($hookName == 'ArticleSearch::retrieveResults');

		// Unpack the parameters.
		list($journal, $keywords, $fromDate, $toDate, $page, $itemsPerPage, $dummy) = $params;
		$totalResults =& $params[6]; // need to use reference
		$error =& $params[7]; // need to use reference

		// Instantiate a search request.
		$searchRequest = new SolrSearchRequest();
		$searchRequest->setJournal($journal);
		$searchRequest->setFromDate($fromDate);
		$searchRequest->setToDate($toDate);
		$searchRequest->setPage($page);
		$searchRequest->setItemsPerPage($itemsPerPage);
		$searchRequest->addQueryFromKeywords($keywords);

		// Get the ordering criteria.
		list($orderBy, $orderDir) = $this->_getResultSetOrdering($journal);
		$searchRequest->setOrderBy($orderBy);
		$searchRequest->setOrderDir($orderDir == 'asc' ? true : false);

		// Configure alternative spelling suggestions.
		$spellcheck = (boolean)$this->getSetting(0, 'spellcheck');
		$searchRequest->setSpellcheck($spellcheck);

		// Configure highlighting.
		$highlighting = (boolean)$this->getSetting(0, 'highlighting');
		$searchRequest->setHighlighting($highlighting);

		// Configure faceting.
		// 1) Faceting will be disabled for filtered search categories.
		$activeFilters = array_keys($searchRequest->getQuery());
		if ($journal instanceof Journal) $activeFilters[] = 'journalTitle';
		if (!empty($fromDate) || !empty($toDate)) $activeFilters[] = 'publicationDate';
		// 2) Switch faceting on for enabled categories that have no
		// active filters.
		$facetCategories = array_values(array_diff($this->_getEnabledFacetCategories(), $activeFilters));
		$searchRequest->setFacetCategories($facetCategories);

		// Configure custom ranking.
		$customRanking = (boolean)$this->getSetting(0, 'customRanking');
		if ($customRanking) {
			$sectionDao = DAORegistry::getDAO('SectionDAO'); /* @var $sectionDao SectionDAO */
			if ($journal instanceof Journal) {
				$sections = $sectionDao->getJournalSections($journal->getId());
			} else {
				$sections = $sectionDao->getSections();
			}
			while (!$sections->eof()) { /* @var $sections DAOResultFactory */
				$section = $sections->next();
				$rankingBoost = $section->getData('rankingBoost');
				if (isset($rankingBoost)) {
					$sectionBoost = (float)$rankingBoost;
				} else {
					$sectionBoost = LUCENE_PLUGIN_DEFAULT_RANKING_BOOST;
				}
				if ($sectionBoost != LUCENE_PLUGIN_DEFAULT_RANKING_BOOST) {
					$searchRequest->addBoostFactor(
						'section_id', $section->getId(), $sectionBoost
					);
				}
				unset($section);
			}
			unset($sections);
		}

		// Call the solr web service.
		$solrWebService = $this->getSolrWebService();
		$result = $solrWebService->retrieveResults($searchRequest, $totalResults);
		if (is_null($result)) {
			$error = $solrWebService->getServiceMessage();
			$this->_informTechAdmin($error, $journal, true);
			$error .=  ' ' . __('plugins.generic.lucene.message.techAdminInformed');
			return [];
		} else {
			// Store spelling suggestion, highlighting and faceting info internally.
			if ($spellcheck && isset($result['spellingSuggestion'])) {
				$this->_spellingSuggestion = $result['spellingSuggestion'];

				// Identify the field for which we got the suggestion.
				foreach($keywords as $bitmap => $searchPhrase) {
					if (!empty($searchPhrase)) {
						switch ($bitmap) {
							case null:
								$queryField = 'query';
								break;

							case ARTICLE_SEARCH_INDEX_TERMS:
								$queryField = 'indexTerms';
								break;

							default:
								$indexFieldMap = ArticleSearch::getIndexFieldMap();
								assert(isset($indexFieldMap[$bitmap]));
								$queryField = $indexFieldMap[$bitmap];
						}
					}
				}
				$this->_spellingSuggestionField = $queryField;
			}
			if ($highlighting && isset($result['highlightedArticles'])) {
				$this->_highlightedArticles = $result['highlightedArticles'];
			}
			if (!empty($facetCategories) && isset($result['facets'])) {
				$this->_facets = $result['facets'];
			}

			// Return the scored results.
			if (isset($result['scoredResults']) && !empty($result['scoredResults'])) {
				return $result['scoredResults'];
			} else {
				return [];
			}
		}
	}

	/**
     * Callback to mark an article as changed when its metadata has been modified.
	 * @see ArticleSearchIndex::articleMetadataChanged()
     * @param $hookName
     * @param $params array
	 */
	public function callbackArticleMetadataChanged($hookName, $params) {
		assert($hookName == 'ArticleSearchIndex::articleMetadataChanged');
		list($article) = $params; /* @var $article Article */
		$this->_solrWebService->markArticleChanged($article->getId());
		return true;
	}

	/**
     * Callback to mark an article as changed when its files have been modified.
	 * @see ArticleSearchIndex::articleFilesChanged()
     * @param $hookName
     * @param $params array
	 */
	public function callbackArticleFilesChanged($hookName, $params) {
		assert($hookName == 'ArticleSearchIndex::articleFilesChanged');
		list($article) = $params; /* @var $article Article */
		$this->_solrWebService->markArticleChanged($article->getId());
		return true;
	}

	/**
     * Callback to mark an article as changed when one of its files has been modified.
	 * @see ArticleSearchIndex::articleFileChanged()
     * @param $hookName
     * @param $params array
	 */
	public function callbackArticleFileChanged($hookName, $params) {
		assert($hookName == 'ArticleSearchIndex::articleFileChanged');
		list($articleId, $type, $fileId) = $params;
		$this->_solrWebService->markArticleChanged($articleId);
		return true;
	}

	/**
     * Callback to mark an article as changed when one of its files has been deleted.
	 * @see ArticleSearchIndex::articleFileDeleted()
     * @param $hookName
     * @param $params array
	 */
	public function callbackArticleFileDeleted($hookName, $params) {
		assert($hookName == 'ArticleSearchIndex::articleFileDeleted');
		list($articleId, $type, $assocId) = $params;
		$this->_solrWebService->markArticleChanged($articleId);
		return true;
	}

	/**
     * Callback to mark an article as changed when the metadata of one of its supplementary files has been modified.
	 * @see ArticleSearchIndex::suppFileMetadataChanged()
     * @param $hookName
     * @param $params array
	 */
	public function callbackSuppFileMetadataChanged($hookName, $params) {
		assert($hookName == 'ArticleSearchIndex::suppFileMetadataChanged');
		list($suppFile) = $params; /* @var $suppFile SuppFile */
		if (!($suppFile instanceof SuppFile)) return true;
		$this->_solrWebService->markArticleChanged($suppFile->getArticleId());
		return true;
	}

	/**
     * Callback to delete an article from the index when it has been deleted.
	 * @see ArticleSearchIndex::articleDeleted()
     * @param $hookName
     * @param $params array
	 */
	public function callbackArticleDeleted($hookName, $params) {
		assert($hookName == 'ArticleSearchIndex::articleDeleted');
		list($articleId) = $params;
		// Deleting an article must always be done synchronously.
		$this->_solrWebService->deleteArticleFromIndex($articleId);
		return true;
	}

	/**
     * Callback to push changed articles to the index when a batch of article changes has been completed.
	 * @see ArticleSearchIndex::articleChangesFinished()
     * @param $hookName
     * @param $params array
	 */
	public function callbackArticleChangesFinished($hookName, $params) {
		// In the case of pull-indexing we ignore this call.
		if ($this->getSetting(0, 'pullIndexing')) return true;

		$solrWebService = $this->getSolrWebService();
		$result = $solrWebService->pushChangedArticles(5);
		if (is_null($result)) {
			$this->_informTechAdmin($solrWebService->getServiceMessage());
		}
		return true;
	}

	/**
     * Callback to re-build the index.
	 * @see ArticleSearchIndex::rebuildIndex()
     * @param $hookName
     * @param $params array
	 */
	public function callbackRebuildIndex($hookName, $params) {
		assert($hookName == 'ArticleSearchIndex::rebuildIndex');
		$solrWebService = $this->getSolrWebService();

		// Unpack the parameters.
		list($log, $journal) = $params;

		// If we got a journal instance then only re-index
		// articles from that journal.
		$journalIdOrNull = ($journal instanceof Journal ? $journal->getId() : null);

		// Clear index
		if ($log) echo 'LucenePlugin: ' . __('search.cli.rebuildIndex.clearingIndex') . ' ... ';
		$solrWebService->deleteArticlesFromIndex($journalIdOrNull);
		if ($log) echo __('search.cli.rebuildIndex.done') . "\n";

		// Re-build index
		if ($journal instanceof Journal) {
			$journals = [$journal];
			unset($journal);
		} else {
			$journalDao = DAORegistry::getDAO('JournalDAO'); /* @var $journalDao JournalDAO */
			$journalIterator = $journalDao->getJournals();
			$journals = $journalIterator->toArray();
		}

		// We re-index journal by journal
		foreach($journals as $journal) {
			if ($log) echo __('search.cli.rebuildIndex.indexing', ['journalName' => $journal->getLocalizedTitle()]) . ' ';

			// Mark all articles in the journal for re-indexing.
			$numMarked = $this->_solrWebService->markJournalChanged($journal->getId());

			// Pull or push?
			if ($this->getSetting(0, 'pullIndexing')) {
				if ($log) echo '... ' . __('plugins.generic.lucene.rebuildIndex.pullResult', ['numMarked' => $numMarked]) . "\n";
			} else {
				// In case of push indexing we immediately update the index.
				$numIndexed = 0;
				do {
					// We update the index in batches
					$articlesInBatch = $solrWebService->pushChangedArticles(SOLR_INDEXING_MAX_BATCHSIZE, $journal->getId());
					if (is_null($articlesInBatch)) {
						$error = $solrWebService->getServiceMessage();
						if ($log) {
							echo ' ' . __('search.cli.rebuildIndex.error') . (empty($error) ? '' : ": $error") . "\n";
						} else {
							$this->_informTechAdmin($error, $journal);
						}
						return true;
					}
					if ($log) echo '.';
					$numIndexed += $articlesInBatch;
				} while ($articlesInBatch == SOLR_INDEXING_MAX_BATCHSIZE);
				if ($log) echo ' ' . __('search.cli.rebuildIndex.result', ['numIndexed' => $numIndexed]) . "\n";
			}
		}
		return true;
	}

	//
	// Form hook implementations.
	//
	/**
     * Callback to add a ranking boost option to the section form.
	 * @see Form::Form()
     * @param $hookName
     * @param $params array
	 */
	public function callbackSectionFormConstructor($hookName, $params) {
		// Check whether we got a valid ranking boost option.
		$acceptedValues = array_keys($this->_getRankingBoostOptions());
		$form = $params[0];
		$form->addCheck(
			new FormValidatorInSet(
				$form, 'rankingBoostOption', FORM_VALIDATOR_REQUIRED_VALUE,
				'plugins.generic.lucene.sectionForm.rankingBoostInvalid',
				$acceptedValues
			)
		);
		return false;
	}

	/**
     * Callback to initialize the ranking boost field in the section form.
	 * @see Form::initData()
     * @param $hookName
     * @param $params array
	 */
	public function callbackSectionFormInitData($hookName, $params) {
		$form = $params[0]; /* @var $form SectionForm */

		// Read the section's ranking boost.
		$rankingBoost = LUCENE_PLUGIN_DEFAULT_RANKING_BOOST;
		$section = $form->section;
		if ($section instanceof Section) {
			$rankingBoostSetting = $section->getData('rankingBoost');
			if (is_numeric($rankingBoostSetting)) $rankingBoost = (float)$rankingBoostSetting;
		}

		$rankingBoostOption = (int)($rankingBoost * 2);
		$rankingBoostOptions = $this->_getRankingBoostOptions();
		if (!in_array($rankingBoostOption, array_keys($rankingBoostOptions))) {
			$rankingBoostOption = (int)(LUCENE_PLUGIN_DEFAULT_RANKING_BOOST * 2);
		}
		$form->setData('rankingBoostOption', $rankingBoostOption);
		return false;
	}

	/**
     * Callback to read the ranking boost field from the section form.
	 * @see Form::readUserVars()
     * @param $hookName
     * @param $params array
	 */
	public function callbackSectionFormReadUserVars($hookName, $params) {
		// Reference needed to modify the array in place
		$userVars =& $params[1];
		$userVars[] = 'rankingBoostOption';
		return false;
	}

	/**
     * Callback to save the ranking boost field from the section form.
	 * @see Form::execute()
     * @param $hookName
     * @param $params array
	 */
	public function callbackSectionFormExecute($hookName, $params) {
		$form = $params[0]; /* @var $form SectionForm */
		$rankingBoostOption = $form->getData('rankingBoostOption');
		$rankingBoostOptions = $this->_getRankingBoostOptions();
		if (in_array($rankingBoostOption, array_keys($rankingBoostOptions))) {
			$rankingBoost = ((float)$rankingBoostOption)/2;
		} else {
			$rankingBoost = LUCENE_PLUGIN_DEFAULT_RANKING_BOOST;
		}

		// Update the ranking boost of the section.
		$section = $params[1]; /* @var $section Section */
		$section->setData('rankingBoost', $rankingBoost);
		return false;
	}


	//
	// View level hook implementations.
	//
	/**
     * Callback to add stylesheets and result set ordering options to the search results template.
	 * @see TemplateManager::display()
     * @param $hookName
     * @param $params array
	 */
	public function callbackTemplateDisplay($hookName, $params) {
		// We only plug into the search results list.
		$template = $params[1];
		if ($template != 'search/search.tpl') return false;

		// Get request and context.
		$request = PKPApplication::getRequest();
		$journal = $request->getContext();

		// Assign our private stylesheet.
		$templateMgr = $params[0];
		$templateMgr->addStylesheet($request->getBaseUrl() . '/' . $this->getPluginPath() . '/templates/lucene.css');

		// Result set ordering options.
		$orderByOptions = $this->_getResultSetOrderingOptions($journal);
		$templateMgr->assign('luceneOrderByOptions', $orderByOptions);
		$orderDirOptions = $this->_getResultSetOrderingDirectionOptions();
		$templateMgr->assign('luceneOrderDirOptions', $orderDirOptions);

		// Result set ordering selection.
		list($orderBy, $orderDir) = $this->_getResultSetOrdering($journal);
		$templateMgr->assign('orderBy', $orderBy);
		$templateMgr->assign('orderDir', $orderDir);

		return false;
	}

	/**
     * Callback to add an autosuggest input to the search results template.
	 * @see templates/search/searchResults.tpl
     * @param $hookName
     * @param $params array
	 */
	public function callbackTemplateFilterInput($hookName, $params) {
		$smarty = $params[1];
		$output =& $params[2]; // Reference required for string concatenation
		$smarty->assign($params[0]);
		$output .= $smarty->fetch($this->getTemplatePath() . 'filterInput.tpl');
		return false;
	}

	/**
     * Callback to add a spelling suggestion to the search results template.
	 * @see templates/search/searchResults.tpl
     * @param $hookName
     * @param $params array
	 */
	public function callbackTemplatePreResults($hookName, $params) {
		$smarty = $params[1];
		$output =& $params[2]; // Reference required for string concatenation
		$smarty->assign('spellingSuggestion', $this->_spellingSuggestion);
		$smarty->assign(
			'spellingSuggestionUrlParams',
			[$this->_spellingSuggestionField => $this->_spellingSuggestion]
		);
		$output .= $smarty->fetch($this->getTemplatePath() . 'preResults.tpl');
		return false;
	}

	/**
     * Callback to add a "similar documents" link to each search result.
	 * @see templates/search/searchResults.tpl
     * @param $hookName
     * @param $params array
	 */
	public function callbackTemplateAdditionalArticleLinks($hookName, $params) {
		if (!$this->getSetting(0, 'simdocs')) return false;

		$hookParams = $params[0];
		if (!(isset($hookParams['articleId']) && is_numeric($hookParams['articleId']))) {
			return false;
		}
		$urlParams = [
			'articleId' => $hookParams['articleId']
		];

		// Create a URL that links to "similar documents".
		$request = PKPApplication::getRequest();
		$router = $request->getRouter();
		$simdocsUrl = $router->url(
			$request, null, 'lucene', 'similarDocuments', null, $urlParams
		);

		$output =& $params[2]; // Reference required for string concatenation
		$output .= '&nbsp;<a href="' . $simdocsUrl . '" class="file">'
			. __('plugins.generic.lucene.results.similarDocuments')
			. '</a>';
		return false;
	}

	/**
     * Callback to add a "similar documents" link to each search result.
	 * @see templates/search/searchResults.tpl
     * @param $hookName
     * @param $params array
	 */
	public function callbackTemplateAdditionalArticleInfo($hookName, $params) {
		if (!$this->getSetting(0, 'highlighting')) return false;

		$hookParams = $params[0];
		if (!(isset($hookParams['articleId']) && is_numeric($hookParams['articleId'])
			&& isset($hookParams['numCols']))) {
			return false;
		}
		$articleId = $hookParams['articleId'];

		if (!isset($this->_highlightedArticles[$articleId])) return false;

		$output =& $params[2]; // Reference required for string concatenation
		$output .= '<tr class="plugins_generic_lucene_highlighting"><td colspan=' . $hookParams['numCols'] . '>"...&nbsp;'
			. trim($this->_highlightedArticles[$articleId]) . '&nbsp;..."</td></tr>';
		return false;
	}

	/**
     * Callback to add syntax instructions to the search results template.
	 * @see templates/search/searchResults.tpl
     * @param $hookName
     * @param $params array
	 */
	public function callbackTemplateSyntaxInstructions($hookName, $params) {
		$output =& $params[2]; // Reference required for string concatenation
		$output .= __('plugins.generic.lucene.results.syntaxInstructions');
		return false;
	}

	/**
     * Callback to add custom ranking options to the section form template.
	 * @see templates/manager/sections/sectionForm.tpl
     * @param $hookName
     * @param $params array
	 */
	public function callbackTemplateSectionFormAdditionalMetadata($hookName, $params) {
		$smarty = $params[1];
		$smarty->assign('rankingBoostOptions', $this->_getRankingBoostOptions());

		$output =& $params[2]; // Reference required for string concatenation
		$output .= $smarty->fetch($this->getTemplatePath() . 'additionalSectionMetadata.tpl');
		return false;
	}


	//
	// Private helper methods
	//
	/**
	 * Set the page's breadcrumbs
	 */
	protected function _setBreadcrumbs() {
		$templateMgr = TemplateManager::getManager();
		$pageCrumbs = [
			[
				Request::url(null, 'user'),
				'navigation.user'
			],
			[
				Request::url('index', 'admin'),
				'user.role.siteAdmin'
			],
			[
				Request::url(null, 'manager', 'plugins'),
				'manager.plugins'
			]
		];
		$templateMgr->assign('pageHierarchy', $pageCrumbs);
	}

	/**
	 * Return the available options for result set ordering.
	 * @param Journal $journal
	 * @return array
	 */
	protected function _getResultSetOrderingOptions($journal) {
		$resultSetOrderingOptions = [
			'score' => __('plugins.generic.lucene.results.orderBy.relevance'),
			'authors' => __('plugins.generic.lucene.results.orderBy.author'),
			'issuePublicationDate' => __('plugins.generic.lucene.results.orderBy.issue'),
			'publicationDate' => __('plugins.generic.lucene.results.orderBy.date'),
			'title' => __('plugins.generic.lucene.results.orderBy.article')
		];

		// Only show the "journal title" option if we have several journals.
		if (!($journal instanceof Journal)) {
			$resultSetOrderingOptions['journalTitle'] = __('plugins.generic.lucene.results.orderBy.journal');
		}

		return $resultSetOrderingOptions;
	}

	/**
	 * Return the available options for the result set ordering direction.
	 * @return array
	 */
	protected function _getResultSetOrderingDirectionOptions() {
		return [
			'asc' => __('plugins.generic.lucene.results.orderDir.asc'),
			'desc' => __('plugins.generic.lucene.results.orderDir.desc')
		];
	}

	/**
	 * Return the currently selected result set ordering option.
	 * @param Journal $journal
	 * @return array
	 */
	protected function _getResultSetOrdering($journal) {
		// Retrieve the request.
		$request = Application::getRequest();

		// Order field.
		$orderBy = $request->getUserVar('orderBy');
		$orderByOptions = $this->_getResultSetOrderingOptions($journal);
		if (is_null($orderBy) || !in_array($orderBy, array_keys($orderByOptions))) {
			$orderBy = 'score';
		}

		// Ordering direction.
		$orderDir = $request->getUserVar('orderDir');
		$orderDirOptions = $this->_getResultSetOrderingDirectionOptions();
		if (is_null($orderDir) || !in_array($orderDir, array_keys($orderDirOptions))) {
			if (in_array($orderBy, ['score', 'publicationDate', 'issuePublicationDate'])) {
				$orderDir = 'desc';
			} else {
				$orderDir = 'asc';
			}
		}

		return [$orderBy, $orderDir];
	}

	/**
	 * Get all currently enabled facet categories.
	 * @return array
	 */
	protected function _getEnabledFacetCategories() {
		if (!is_array($this->_enabledFacetCategories)) {
			$this->_enabledFacetCategories = [];
			$availableFacetCategories = [
				'discipline', 'subject', 'type', 'coverage',
				'journalTitle', 'authors', 'publicationDate'
			];
			foreach($availableFacetCategories as $facetCategory) {
				if ($this->getSetting(0, 'facetCategory' . ucfirst($facetCategory))) {
					$this->_enabledFacetCategories[] = $facetCategory;
				}
			}
		}
		return $this->_enabledFacetCategories;
	}

	/**
	 * Checks whether a minimum amount of time has passed since
	 * the last email message went out.
	 * @return boolean
	 */
	protected function _spamCheck() {
		// Avoid spam.
		$lastEmailTimstamp = (int)$this->getSetting(0, 'lastEmailTimestamp');
		$threeHours = 60 * 60 * 3;
		$now = time();
		if ($now - $lastEmailTimstamp < $threeHours) return false;
		$this->updateSetting(0, 'lastEmailTimestamp', $now);
		return true;
	}

	/**
	 * Send an email to the site's tech admin
	 * warning that an indexing error has occured.
	 * @param array $error
	 * @param Journal $journal
	 * @param boolean $isSearchProblem
	 */
	protected function _informTechAdmin($error, $journal = null, $isSearchProblem = false) {
		if (!$this->_spamCheck()) return;

		// Is this a search or an indexing problem?
		if ($isSearchProblem) {
			$mail = $this->getMailTemplate('LUCENE_SEARCH_SERVICE_ERROR_NOTIFICATION', $journal);
		} else {
			// Check whether this is journal or article index update problem.
			if ($journal instanceof Journal) {
				$mail = $this->getMailTemplate('LUCENE_JOURNAL_INDEXING_ERROR_NOTIFICATION', $journal);
			} else {
				$mail = $this->getMailTemplate('LUCENE_ARTICLE_INDEXING_ERROR_NOTIFICATION');
			}
		}

		// Assign parameters.
		$request = PKPApplication::getRequest();
		$site = $request->getSite();
		$mail->assignParams(
			['siteName' => $site->getLocalizedTitle(), 'error' => $error]
		);

		// Send to the site's tech contact.
		$mail->addRecipient($site->getLocalizedContactEmail(), $site->getLocalizedContactName());

		// Send the mail.
		$mail->send($request);
	}

	/**
	 * Return the available ranking boost options.
	 * @return array
	 */
	protected function _getRankingBoostOptions() {
		return [
			0 => __('plugins.generic.lucene.sectionForm.ranking.never'),
			1 => __('plugins.generic.lucene.sectionForm.ranking.low'),
			2 => __('plugins.generic.lucene.sectionForm.ranking.normal'),
			4 => __('plugins.generic.lucene.sectionForm.ranking.high')
		];
	}
}
?>