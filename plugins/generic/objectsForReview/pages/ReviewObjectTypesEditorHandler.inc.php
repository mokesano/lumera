<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/pages/ReviewObjectTypesEditorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewObjectTypesEditorHandler
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Handle requests for editor objects for review functions.
 */

import('classes.handler.Handler');

class ReviewObjectTypesEditorHandler extends Handler {

	/**
	 * Display objects for review listing pages.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function reviewObjectTypes($args, $request) {
		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		$rangeInfo = $this->getRangeInfo('reviewObjectTypes');
		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		$types = $reviewObjectTypeDao->getTypeIdsAlphabetizedByContext($journalId);

		$totalResults = count($types);
		$types = array_slice($types, $rangeInfo->getCount() * ($rangeInfo->getPage() - 1), $rangeInfo->getCount());
		
		import('lib.pkp.classes.core.VirtualArrayIterator');
		$results = new VirtualArrayIterator($types, $totalResults, $rangeInfo->getPage(), $rangeInfo->getCount());

		$this->setupTemplate($request);
		$templateMgr = TemplateManager::getManager($request);
		
		$plugin = $this->_getObjectsForReviewPlugin();
		
		// [WIZDAM] Modernization: Micro-payload for template assignment
		$templateMgr->assign([
			'results' => $results,
			'pluginLocales' => $this->_getPluginLocales(),
			'missingReviewObjects' => $this->_getMissingDefaultReviewObjectsKeys($journalId),
		]);

		$templateMgr->display($plugin->getTemplatePath() . 'editor/reviewObjectTypes.tpl');
	}

	/**
	 * Create a new review object type.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function createReviewObjectType($args, $request) {
		$this->editReviewObjectType($args, $request);
	}

	/**
	 * Create/edit a review object type.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function editReviewObjectType($args, $request) {
		$typeId = array_shift($args);
		$typeId = $typeId ? (int) $typeId : null;

		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		$reviewObjectType = $reviewObjectTypeDao->getById($typeId, $journalId);
		
		if ($typeId && !$reviewObjectType) {
			$request->redirect(null, 'editor', 'reviewObjectTypes');
		}

		$this->setupTemplate($request, true, $reviewObjectType);
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pageTitle', $typeId ? 'plugins.generic.objectsForReview.editor.objectType.edit' : 'plugins.generic.objectsForReview.editor.objectType.create');

		$plugin = $this->_getObjectsForReviewPlugin();
		$plugin->import('classes.form.ReviewObjectTypeForm');
		$reviewObjectTypeForm = new ReviewObjectTypeForm(OBJECTS_FOR_REVIEW_PLUGIN_NAME, $typeId);
		
		if ($reviewObjectTypeForm->isLocaleResubmit()) {
			$reviewObjectTypeForm->readInputData();
		} else {
			$reviewObjectTypeForm->initData();
		}
		$reviewObjectTypeForm->display($request);
	}

	/**
	 * Update a review object type.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function updateReviewObjectType($args, $request) {
		$typeId = (int) trim((string) $request->getUserVar('typeId'));

		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		$reviewObjectType = $reviewObjectTypeDao->getById($typeId, $journalId);
		
		if ($typeId && !$reviewObjectType) {
			$request->redirect(null, 'editor', 'reviewObjectTypes');
		}

		$plugin = $this->_getObjectsForReviewPlugin();
		$plugin->import('classes.form.ReviewObjectTypeForm');
		$reviewObjectTypeForm = new ReviewObjectTypeForm(OBJECTS_FOR_REVIEW_PLUGIN_NAME, $typeId);
		$reviewObjectTypeForm->readInputData();
		
		if (!$typeId) {
			$formLocale = $reviewObjectTypeForm->getFormLocale();
			$options = $reviewObjectTypeForm->getData('possibleOptions');
			
			if (isset($options[$formLocale]) && is_array($options[$formLocale])) {
				// [WIZDAM] Modernization: Replace deprecated create_function with arrow function (PHP 7.4+)
				usort($options[$formLocale], fn($a, $b) => $a['order'] <=> $b['order']);
			}
			$reviewObjectTypeForm->setData('possibleOptions', $options);

			$addOptionFlag = (int) trim((string) $request->getUserVar('addOption'));
            
			if ($addOptionFlag) {
				$editData = true;
				$options = $reviewObjectTypeForm->getData('possibleOptions');
				if (!isset($options[$formLocale]) || !is_array($options[$formLocale])) {
					$options[$formLocale] = [];
					$lastOrder = 0;
				} else {
					$lastOrder = $options[$formLocale][count($options[$formLocale]) - 1]['order'];
				}
				// [WIZDAM] Modernization: Short array syntax
				$options[$formLocale][] = ['order' => $lastOrder + 1];
				$reviewObjectTypeForm->setData('possibleOptions', $options);

			} else {
				$delOptionInput = $request->getUserVar('delOption'); 

				if (!empty($delOptionInput) && is_array($delOptionInput) && count($delOptionInput) === 1) {
					$editData = true;
					// [WIZDAM] Modernization: Array destructuring
					[$delOptionKey] = array_keys($delOptionInput);
					$delOption = (int) trim((string) $delOptionKey); 
					
					$options = $reviewObjectTypeForm->getData('possibleOptions');
					if (!isset($options[$formLocale])) {
						$options[$formLocale] = [];
					}
					array_splice($options[$formLocale], $delOption, 1);
					$reviewObjectTypeForm->setData('possibleOptions', $options);
				}
			}
		}

		if (!isset($editData) && $reviewObjectTypeForm->validate()) {
			$reviewObjectTypeForm->execute();
			$notificationType = $typeId ? NOTIFICATION_TYPE_OFR_OT_UPDATED : NOTIFICATION_TYPE_OFR_OT_CREATED;
			$this->_createTrivialNotification($notificationType, $request);
			$request->redirect(null, 'editor', 'reviewObjectTypes');
		} else {
			$this->setupTemplate($request, true, $reviewObjectType);
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('pageTitle', $typeId ? 'plugins.generic.objectsForReview.editor.objectType.edit' : 'plugins.generic.objectsForReview.editor.objectType.create');
			$reviewObjectTypeForm->display($request);
		}
	}

	/**
	 * Preview a review object type.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function previewReviewObjectType($args, $request) {
		$typeId = array_shift($args);

		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		$reviewObjectType = $reviewObjectTypeDao->getById($typeId, $journalId);
		
		if (!$reviewObjectType) {
			$request->redirect(null, 'editor', 'reviewObjectTypes');
		}
		
		/** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
		$reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
		$reviewObjectMetadata = $reviewObjectMetadataDao->getArrayByReviewObjectTypeId($typeId);

		$this->setupTemplate($request, true, $reviewObjectType);
		$templateMgr = TemplateManager::getManager($request);

		$templateMgr->assign('pageTitle', 'plugins.generic.objectsForReview.editor.objectType.preview');
		$templateMgr->assign('reviewObjectType', $reviewObjectType);
		$templateMgr->assign('reviewObjectMetadata', $reviewObjectMetadata);

		/** @var LanguageDAO $languageDao */
		$languageDao = DAORegistry::getDAO('LanguageDAO');
		$languages = $languageDao->getLanguages();
		
		$validLanguages = ['' => __('plugins.generic.objectsForReview.editor.objectForReview.chooseLanguage')];
		// [WIZDAM] Modernization: Replace deprecated each() with foreach
		foreach ($languages as $language) {
			$validLanguages[$language->getCode()] = $language->getName();
		}
		$templateMgr->assign('validLanguages', $validLanguages);
		
		$plugin = $this->_getObjectsForReviewPlugin();
		$templateMgr->display($plugin->getTemplatePath() . 'editor/previewReviewObjectType.tpl');
	}

	/**
	 * Delete a review object type.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function deleteReviewObjectType($args, $request) {
		$typeId = array_shift($args);

		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		if ($reviewObjectTypeDao->reviewObjectTypeExists($typeId, $journalId)) {
			$reviewObjectTypeDao->deleteById($typeId, $journalId);
		}

		$this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_OT_DELETED, $request);
		$request->redirect(null, 'editor', 'reviewObjectTypes');
	}

	/**
	 * Activate a review object type to be used.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function activateReviewObjectType($args, $request) {
		$typeId = array_shift($args);

		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		$reviewObjectType = $reviewObjectTypeDao->getById($typeId, $journalId);
		
		if ($reviewObjectType && !$reviewObjectType->getActive()) {
			$reviewObjectType->setActive(1);
			$reviewObjectTypeDao->updateObject($reviewObjectType);
		}

		$this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_OT_ACTIVATED, $request);
		$request->redirect(null, 'editor', 'reviewObjectTypes');
	}

	/**
	 * Deactivate a review object type.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function deactivateReviewObjectType($args, $request) {
		$typeId = array_shift($args);

		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		$reviewObjectType = $reviewObjectTypeDao->getById($typeId, $journalId);
		
		if ($reviewObjectType && $reviewObjectType->getActive()) {
			$reviewObjectType->setActive(0);
			$reviewObjectTypeDao->updateObject($reviewObjectType);
		}

		$this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_OT_DEACTIVATED, $request);
		$request->redirect(null, 'editor', 'reviewObjectTypes');
	}

	/**
	 * Update review object locale data.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function updateOrInstallReviewObjectTypes($args, $request) {
		$journal = $request->getJournal();
		$plugin = $this->_getObjectsForReviewPlugin();

		$updateLocaleDataFlag = (int) trim((string) $request->getUserVar('updateLocaleData'));

		if ($updateLocaleDataFlag) {
			$reviewObjectTypes = (array) $request->getUserVar('update'); 
			$locales = (array) $request->getUserVar('updateLocales'); 
			$this->_updateOrInstallReviewObjectTypes($journal, $reviewObjectTypes, $locales, 'update');
			$notificationType = NOTIFICATION_TYPE_OFR_OT_UPDATED;

		} elseif ((int) trim((string) $request->getUserVar('installReviewObjects'))) { 
			$reviewObjectTypes = (array) $request->getUserVar('reviewObjects');
			$locales = (array) $request->getUserVar('installLocales'); 
			$this->_updateOrInstallReviewObjectTypes($journal, $reviewObjectTypes, $locales, 'install');
			$notificationType = NOTIFICATION_TYPE_OFR_OT_INSTALLED;
		}
		
		$this->_createTrivialNotification($notificationType, $request);
		$request->redirect(null, 'editor', 'reviewObjectTypes');
	}

	/**
	 * Display a list of the metadata within a review object type.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function reviewObjectMetadata($args, $request) {
		$typeId = array_shift($args);

		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		$reviewObjectType = $reviewObjectTypeDao->getById($typeId, $journalId);
		
		if (!$reviewObjectType) {
			$request->redirect(null, 'editor', 'reviewObjectTypes');
		}

		/** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
		$reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
		$reviewObjectMetadata = $reviewObjectMetadataDao->getByReviewObjectTypeId($typeId);

		$allTypes = $reviewObjectTypeDao->getTypeIdsAlphabetizedByContext($journalId);
		$typeOptions = [];
		foreach ($allTypes as $type) {
			$typeOptions[$type['typeId']] = $type['typeName'];
		}
		
		$this->setupTemplate($request, true, $reviewObjectType);
		$templateMgr = TemplateManager::getManager($request);

		$templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
		$templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');

		$templateMgr->assign([
			'reviewObjectMetadata' => $reviewObjectMetadata,
			'typeOptions' => $typeOptions,
			'typeId' => $typeId,
		]);
		
		$plugin = $this->_getObjectsForReviewPlugin();
		$templateMgr->display($plugin->getTemplatePath() . 'editor/reviewObjectMetadata.tpl');
	}

	/**
	 * Create a new review object metadata.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function createReviewObjectMetadata($args, $request) {
		$this->editReviewObjectMetadata($args, $request);
	}

	/**
	 * Create/edit a review object metadata.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function editReviewObjectMetadata($args, $request) {
		$typeId = array_shift($args);
		$metadataId = array_shift($args);
		$metadataId = $metadataId ? (int) $metadataId : null;

		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		$reviewObjectType = $reviewObjectTypeDao->getById($typeId, $journalId);

		/** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
		$reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
		
		if (!$reviewObjectType || ($metadataId && !$reviewObjectMetadataDao->reviewObjectMetadataExists($metadataId, $typeId))) {
			$request->redirect(null, 'editor', 'reviewObjectMetadata', [$typeId]);
		}

		$this->setupTemplate($request, true, $reviewObjectType);
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pageTitle', $metadataId ? 'plugins.generic.objectsForReview.editor.objectMetadata.edit' : 'plugins.generic.objectsForReview.editor.objectMetadata.create');

		$plugin = $this->_getObjectsForReviewPlugin();
		$plugin->import('classes.form.ReviewObjectMetadataForm');
		$reviewObjectMetadataForm = new ReviewObjectMetadataForm(OBJECTS_FOR_REVIEW_PLUGIN_NAME, $typeId, $metadataId);
		
		if ($reviewObjectMetadataForm->isLocaleResubmit()) {
			$reviewObjectMetadataForm->readInputData();
		} else {
			$reviewObjectMetadataForm->initData();
		}
		$reviewObjectMetadataForm->display($request);
	}

	/**
	 * Update a review object metadata.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function updateReviewObjectMetadata($args, $request) {
		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		$typeId = (int) trim((string) $request->getUserVar('reviewObjectTypeId'));
		$metadataId = (int) trim((string) $request->getUserVar('metadataId'));

		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		$reviewObjectType = $reviewObjectTypeDao->getById($typeId, $journalId);

		/** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
		$reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
		
		if (!$reviewObjectType || ($metadataId && !$reviewObjectMetadataDao->reviewObjectMetadataExists($metadataId, $typeId))) {
			$request->redirect(null, 'editor', 'reviewObjectMetadata', [$typeId]);
		}

		$plugin = $this->_getObjectsForReviewPlugin();
		$plugin->import('classes.form.ReviewObjectMetadataForm');
		$reviewObjectMetadataForm = new ReviewObjectMetadataForm(OBJECTS_FOR_REVIEW_PLUGIN_NAME, $typeId, $metadataId);
		$reviewObjectMetadataForm->readInputData();
		
		$formLocale = $reviewObjectMetadataForm->getFormLocale();
		$options = $reviewObjectMetadataForm->getData('possibleOptions');
		
		if (isset($options[$formLocale]) && is_array($options[$formLocale])) {
			// [WIZDAM] Modernization: Replace deprecated create_function with arrow function
			usort($options[$formLocale], fn($a, $b) => $a['order'] <=> $b['order']);
		}
		$reviewObjectMetadataForm->setData('possibleOptions', $options);

		$addOptionFlag = (int) trim((string) $request->getUserVar('addOption'));
        
		if ($addOptionFlag) {
			$editData = true;
			$options = $reviewObjectMetadataForm->getData('possibleOptions');
			if (!isset($options[$formLocale]) || !is_array($options[$formLocale])) {
				$options[$formLocale] = [];
				$lastOrder = 0;
			} else {
				$lastOrder = $options[$formLocale][count($options[$formLocale]) - 1]['order'];
			}
			$options[$formLocale][] = ['order' => $lastOrder + 1];
			$reviewObjectMetadataForm->setData('possibleOptions', $options);

		} else {
			$delOptionInput = $request->getUserVar('delOption');
            
			if (!empty($delOptionInput) && is_array($delOptionInput) && count($delOptionInput) === 1) {
				$editData = true;
				[$delOptionKey] = array_keys($delOptionInput);
				$delOption = (int) trim((string) $delOptionKey); 
				
				$options = $reviewObjectMetadataForm->getData('possibleOptions');
				if (!isset($options[$formLocale])) {
					$options[$formLocale] = [];
				}
				array_splice($options[$formLocale], $delOption, 1);
				$reviewObjectMetadataForm->setData('possibleOptions', $options);
			}
		}

		if (!isset($editData) && $reviewObjectMetadataForm->validate()) {
			$reviewObjectMetadataForm->execute();
			$this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_OT_UPDATED, $request);
			$request->redirect(null, 'editor', 'reviewObjectMetadata', [$typeId]);
		} else {
			$this->setupTemplate($request, true, $reviewObjectType);
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('pageTitle', $metadataId ? 'plugins.generic.objectsForReview.editor.objectMetadata.edit' : 'plugins.generic.objectsForReview.editor.objectMetadata.create');
			$reviewObjectMetadataForm->display($request);
		}
	}

	/**
	 * Delete a review object metadata.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function deleteReviewObjectMetadata($args, $request) {
		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		$typeId = array_shift($args);
		$metadataId = array_shift($args);

		/** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
		$reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
		$reviewObjectMetadataDao->deleteById($metadataId, $typeId);

		$this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_OT_UPDATED, $request);
		$request->redirect(null, 'editor', 'reviewObjectMetadata', [$typeId]);
	}

	/**
	 * Change the sequence of a review object metadata.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function moveReviewObjectMetadata($args, $request) {
		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		/** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
		$reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
		
		$metadataId = (int) trim((string) $request->getUserVar('id'));
		$reviewObjectMetadata = $reviewObjectMetadataDao->getById($metadataId);

		if (!$reviewObjectMetadata) {
			$request->redirect(null, 'editor', 'reviewObjectTypes');
		}

		$direction = trim((string) $request->getUserVar('d'));

		if ($direction !== '') {
			$reviewObjectMetadata->setSequence($reviewObjectMetadata->getSequence() + ($direction === 'u' ? -1.5 : 1.5));
		} else {
			$prevId = (int) trim((string) $request->getUserVar('prevId'));
			if ($prevId === 0) {
				$prevSeq = 0;
			} else {
				$prevReviewObjectMetadata = $reviewObjectMetadataDao->getById($prevId);
				$prevSeq = $prevReviewObjectMetadata ? $prevReviewObjectMetadata->getSequence() : 0;
			}
			$reviewObjectMetadata->setSequence($prevSeq + 0.5);
		}

		$reviewObjectMetadataDao->updateObject($reviewObjectMetadata);
		$reviewObjectMetadataDao->resequence($reviewObjectMetadata->getReviewObjectTypeId());

		if ($direction !== '') {
			$request->redirect(null, 'editor', 'reviewObjectMetadata', [$reviewObjectMetadata->getReviewObjectTypeId()]);
		}
	}

	/**
	 * Copy review object metadata to another review object.
	 * @param array $args
	 * @param PKPRequest $request
	 */
	public function copyOrUpdateReviewObjectMetadata($args, $request) {
		$typeId = array_shift($args);

		$journal = $request->getJournal();
		$journalId = (int) $journal->getId();

		$copy = (int) trim((string) $request->getUserVar('copy'));

		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		$reviewObjectType = $reviewObjectTypeDao->getById($typeId, $journalId);
		$reallyBigNumber = defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 999999;

		if ($reviewObjectType) {
			/** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
			$reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');
			
			if ((int) trim((string) $request->getUserVar('save'))) { 
				$requiredMetadata = (array) $request->getUserVar('required');
				$displayMetadata = (array) $request->getUserVar('display');
				$allReviewObjectMetadata = $reviewObjectMetadataDao->getArrayByReviewObjectTypeId($typeId); 
				
				foreach ($allReviewObjectMetadata as $metadata) {
					if ($metadata->getKey() !== REVIEW_OBJECT_METADATA_KEY_TITLE) {
						$metadata->setRequired(in_array($metadata->getId(), $requiredMetadata, true) ? 1 : 0);
						$metadata->setDisplay(in_array($metadata->getId(), $displayMetadata, true) ? 1 : 0);
						$reviewObjectMetadataDao->updateObject($metadata);
					}
				}
			} else {
				$copyInput = $request->getUserVar('copy');
				if (is_array($copyInput)) {
					$copy = (array) $copyInput;
					$targetTypeId = (int) trim((string) $request->getUserVar('targetReviewObjectTypeId'));
					
					foreach ($copy as $metadataId) {
						$metadataId = (int) trim((string) $metadataId); 
						$reviewObjectMetadata = $reviewObjectMetadataDao->getById($metadataId, $typeId);
						
						if ($reviewObjectMetadata && $reviewObjectMetadata->getKey() === null && $reviewObjectTypeDao->reviewObjectTypeExists($targetTypeId, $journalId)) {
							$reviewObjectMetadata->setReviewObjectTypeId($targetTypeId);
							$reviewObjectMetadata->setSequence($reallyBigNumber);
							$reviewObjectMetadataDao->insertObject($reviewObjectMetadata);
							$reviewObjectMetadataDao->resequence($targetTypeId);
						}
					}
					$request->redirect(null, 'editor', 'reviewObjectMetadata', [$targetTypeId]);
				}
			}
		}
		$this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_OT_UPDATED, $request);
		$request->redirect(null, 'editor', 'reviewObjectMetadata', [$typeId]);
	}

	/**
	 * Ensure that we have a journal, plugin is enabled, and user is editor.
	 * @see PKPHandler::authorize()
	 */
	public function authorize($request, $args, $roleAssignments) {
		$journal = $request->getJournal();
		if (!$journal) {
			return false;
		}

		$plugin = $this->_getObjectsForReviewPlugin();
		if (!$plugin || !$plugin->getEnabled()) {
			return false;
		}

		if (!Validation::isEditor($journal->getId())) {
			Validation::redirectLogin();
		}

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Setup common template variables.
	 * @param PKPRequest|null $request
	 * @param bool $subclass
	 * @param ReviewObjectType|null $reviewObjectType
	 */
	public function setupTemplate($request = null, $subclass = false, $reviewObjectType = null) {
		$templateMgr = TemplateManager::getManager($request);
		$pageCrumbs = [
			[$request->url(null, 'user'), 'navigation.user'],
			[$request->url(null, 'editor'), 'user.role.editor']
		];

		if ($subclass) {
			$pageCrumbs[] = [
				$request->url(null, 'editor', 'reviewObjectTypes'),
				AppLocale::translate('plugins.generic.objectsForReview.editor.objectTypes'),
				true
			];
		}
		if ($reviewObjectType) {
			$pageCrumbs[] = [
				$request->url(null, 'editor', 'editReviewObjectType', $reviewObjectType->getId()),
				$reviewObjectType->getLocalizedName(),
				true
			];
		}

		$templateMgr->assign('pageHierarchy', $pageCrumbs);
		$plugin = $this->_getObjectsForReviewPlugin();
		$templateMgr->addStyleSheet($request->getBaseUrl() . '/' . $plugin->getStyleSheet());
	}

	//
	// Private helper methods
	//
	/**
	 * Get the objectForReview plugin object
	 * @return ObjectsForReviewPlugin|null
	 */
	private function &_getObjectsForReviewPlugin() {
		$plugin = PluginRegistry::getPlugin('generic', OBJECTS_FOR_REVIEW_PLUGIN_NAME);
		return $plugin;
	}

	/**
	 * Get plugin locales i.e. the languages the plug-in is translated into
	 * @return array
	 */
	private function _getPluginLocales(): array {
		$plugin = $this->_getObjectsForReviewPlugin();
		$pluginLocales = [];
		$allLocales = AppLocale::getAllLocales();
		
		foreach ($allLocales as $locale => $localeName) {
			$localeFilename = $plugin->getPluginPath() . "/locale/$locale/locale.xml";
			if (file_exists($localeFilename)) {
				$pluginLocales[$locale] = $localeName;
			}
		}
		return $pluginLocales;
	}

	/**
	 * Get the missing/not installed review objects/keys
	 * @param int $journalId
	 * @return array
	 */
	private function _getMissingDefaultReviewObjectsKeys(int $journalId): array {
		$plugin = $this->_getObjectsForReviewPlugin();
		$missingReviewObjectKeys = [];

		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		$installedReviewObjectKeys = $reviewObjectTypeDao->getTypeKeys($journalId);
		
		$files = glob($plugin->getPluginPath() . '/xml/reviewObjects/*.xml');
		if ($files) {
			foreach ($files as $filePath) {
				$objectKey = basename($filePath, '.xml');
				if (!in_array($objectKey, $installedReviewObjectKeys, true)) {
					$missingReviewObjectKeys[$objectKey] = $objectKey;
				}
			}
		}
		return $missingReviewObjectKeys;
	}

	/**
	 * Update or install review objects
	 * @param Journal $journal
	 * @param array $reviewObjects
	 * @param array $locales
	 * @param string $action
	 * @return bool
	 */
	private function _updateOrInstallReviewObjectTypes($journal, array $reviewObjects, array $locales, string $action): bool {
		$plugin = $this->_getObjectsForReviewPlugin();
		if (!$journal || empty($reviewObjects) || empty($locales) || empty($action)) {
			return false;
		}
		$journalId = (int) $journal->getId();
		
		$plugin->import('classes.ReviewObjectType');
		$plugin->import('classes.ReviewObjectMetadata');

		/** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
		$reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
		/** @var ReviewObjectMetadataDAO $reviewObjectMetadataDao */
		$reviewObjectMetadataDao = DAORegistry::getDAO('ReviewObjectMetadataDAO');

		$metadataHelper = new ReviewObjectMetadata();
		$multipleOptionsTypes = $metadataHelper->getMultipleOptionsTypes();
		$dtdTypes = $metadataHelper->getMetadataDTDTypes();
		$reallyBigNumber = defined('REALLY_BIG_NUMBER') ? REALLY_BIG_NUMBER : 999999;

		foreach ($reviewObjects as $keyOrId) {
			$reviewObjectType = null;

			if ($action === 'install') {
				$reviewObjectType = $reviewObjectTypeDao->newDataObject();
				$reviewObjectType->setContextId($journalId);
				$reviewObjectType->setActive(0);
				$reviewObjectType->setKey($keyOrId);
			} elseif ($action === 'update') {
				$reviewObjectType = $reviewObjectTypeDao->getById($keyOrId, $journalId);
				if (!$reviewObjectType) {
					return false;
				}
			}

			if (!$reviewObjectType) {
				continue;
			}

			$onlyCommonMetadata = ($action === 'update' && $reviewObjectType->getKey() === null);
			$reviewObjectMetadataArray = [];
			
			foreach ($locales as $locale) {
				$localePath = $plugin->getPluginPath() . '/locale/'. $locale . '/locale.xml';
				AppLocale::registerLocaleFile($locale, $localePath, true);

				$xmlDao = new XMLDAO();
				$commonDataPath = $plugin->getPluginPath() . '/xml/commonMetadata.xml';
				$commonData = $xmlDao->parse($commonDataPath);
				
				if (!$commonData) {
					continue; // Null safety
				}

				$commonMetadata = $commonData->getChildByName('objectMetadata');
				$allMetadataChildren = $commonMetadata ? $commonMetadata->getChildren() : [];

				if (!$onlyCommonMetadata) {
					$itemPath = $plugin->getPluginPath() . '/xml/reviewObjects/'. $reviewObjectType->getKey() . '.xml';
					$data = $xmlDao->parse($itemPath);
					if (!$data) {
						return false;
					}

					$itemTypeName = __($data->getChildValue('objectType'), [], $locale);
					$reviewObjectType->setName($itemTypeName, $locale);

					$roleSelectionOptions = $data->getChildByName('roleSelectionOptions');
					$itemMetadata = $data->getChildByName('objectMetadata');
					
					if ($itemMetadata) {
						$allMetadataChildren = array_merge($allMetadataChildren, $itemMetadata->getChildren());
					}
				}

				foreach ($allMetadataChildren as $metadataNode) {
					$key = $metadataNode->getAttribute('key');
					$reviewObjectMetadata = null;

					if (array_key_exists($key, $reviewObjectMetadataArray)) {
						$reviewObjectMetadata = $reviewObjectMetadataArray[$key];
					} else {
						if ($action === 'update') {
							$reviewObjectMetadata = $reviewObjectMetadataDao->getByKey($key, $reviewObjectType->getId());
						}
						if ($action === 'install' || !$reviewObjectMetadata) {
							$reviewObjectMetadata = $reviewObjectMetadataDao->newDataObject();
							$reviewObjectMetadata->setSequence($reallyBigNumber);
							
							$typeAttr = $metadataNode->getAttribute('type');
							$metadataType = $dtdTypes[$typeAttr] ?? null;
							$reviewObjectMetadata->setMetadataType($metadataType);
							
							$required = $metadataNode->getAttribute('required');
							$reviewObjectMetadata->setRequired($required === 'true' ? 1 : 0);
							
							$display = $metadataNode->getAttribute('display');
							$reviewObjectMetadata->setDisplay($display === 'true' ? 1 : 0);
						}
					}

					if (!$reviewObjectMetadata) {
						continue;
					}
					
					$name = __($metadataNode->getChildValue('name'), [], $locale);
					$reviewObjectMetadata->setName($name, $locale);
					
					if ($key === REVIEW_OBJECT_METADATA_KEY_ROLE) {
						if (!$onlyCommonMetadata && isset($roleSelectionOptions)) {
							$possibleOptions = [];
							$index = 1;
							foreach ($roleSelectionOptions->getChildren() as $selectionOptionNode) {
								$possibleOptions[] = ['order' => $index, 'content' => __($selectionOptionNode->getValue(), [], $locale)];
								$index++;
			}
							$reviewObjectMetadata->setPossibleOptions($possibleOptions, $locale);
						}
					} else {
						if (in_array($reviewObjectMetadata->getMetadataType(), $multipleOptionsTypes, true)) {
							$selectionOptions = $metadataNode->getChildByName('selectionOptions');
							$possibleOptions = [];
							$index = 1;
							if ($selectionOptions) {
								foreach ($selectionOptions->getChildren() as $selectionOptionNode) {
									$possibleOptions[] = ['order' => $index, 'content' => __($selectionOptionNode->getValue(), [], $locale)];
									$index++;
								}
							}
							$reviewObjectMetadata->setPossibleOptions($possibleOptions, $locale);
						} else {
							$reviewObjectMetadata->setPossibleOptions(null, null);
						}
					}
					$reviewObjectMetadataArray[$key] = $reviewObjectMetadata;
				}
			}

			if ($action === 'install') {
				$reviewObjectTypeDao->insertObject($reviewObjectType);
			} elseif ($action === 'update') {
				$reviewObjectTypeDao->updateObject($reviewObjectType);
			}
			
			foreach ($reviewObjectMetadataArray as $key => $reviewObjectMetadata) {
				if ($reviewObjectMetadata->getKey() === '') {
					$reviewObjectMetadata->setKey($key);
					$reviewObjectMetadata->setReviewObjectTypeId($reviewObjectType->getId());
					$reviewObjectMetadataDao->insertObject($reviewObjectMetadata);
					$reviewObjectMetadataDao->resequence($reviewObjectType->getId());
				} else {
					$reviewObjectMetadataDao->updateObject($reviewObjectMetadata);
				}
			}
		}
		return true;
	}

	/**
	 * Create trivial notification
	 * @param int $notificationType
	 * @param PKPRequest $request
	 */
	private function _createTrivialNotification(int $notificationType, $request): void {
		$user = $request->getUser();
		import('classes.notification.NotificationManager');
		$notificationManager = new NotificationManager();
		$notificationManager->createTrivialNotification($user->getId(), $notificationType);
	}

}
?>