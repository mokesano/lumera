<?php
declare(strict_types=1);

/**
 * @file plugins/generic/usageStats/UsageStatsSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsSettingsForm
 * @ingroup plugins_generic_usageStats
 *
 * @brief Form for journal managers to modify usage statistics plugin settings.
 */

import('lib.pkp.classes.form.Form');

class UsageStatsSettingsForm extends Form {

    /** @var UsageStatsPlugin */
    protected $plugin;

    /**
     * Constructor
     * @param UsageStatsPlugin $plugin
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'usageStatsSettingsForm.tpl');

        $this->addCheck(new FormValidatorCustom(
            $this, 'dataPrivacyOption', FORM_VALIDATOR_OPTIONAL_VALUE, 
            'plugins.generic.usageStats.settings.dataPrivacyOption.requiresSalt', 
            [$this, '_dependentFormFieldIsSet'], 
            [$this, 'saltFilepath']
        ));
        $this->addCheck(new FormValidatorCustom(
            $this, 'dataPrivacyOption', FORM_VALIDATOR_OPTIONAL_VALUE, 
            'plugins.generic.usageStats.settings.dataPrivacyOption.excludesRegion', 
            [$this, '_dependentFormFieldIsSet'], 
            [$this, 'selectedOptionalColumns', STATISTICS_DIMENSION_REGION], 
            true
        ));
        $this->addCheck(new FormValidatorCustom(
            $this, 'dataPrivacyOption', FORM_VALIDATOR_OPTIONAL_VALUE, 
            'plugins.generic.usageStats.settings.dataPrivacyOption.excludesCity', 
            [$this, '_dependentFormFieldIsSet'], 
            [$this, 'selectedOptionalColumns', STATISTICS_DIMENSION_CITY], 
            true
        ));
        
        $this->addCheck(new FormValidatorCustom(
            $this, 'saltFilepath', FORM_VALIDATOR_OPTIONAL_VALUE, 
            'plugins.generic.usageStats.settings.dataPrivacyOption.saltFilepath.invalid', 
            [$plugin, 'validateSaltpath']
        ));
        
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * Initialize form data.
     * @see Form::initData()
     */
    public function initData() {
        $plugin = $this->plugin;

        $this->setData('createLogFiles', $plugin->getSetting(CONTEXT_ID_NONE, 'createLogFiles'));
        $this->setData('accessLogFileParseRegex', $plugin->getSetting(CONTEXT_ID_NONE, 'accessLogFileParseRegex'));
        $this->setData('dataPrivacyOption', $plugin->getSetting(CONTEXT_ID_NONE, 'dataPrivacyOption'));
        $this->setData('saltFilepath', $plugin->getSaltPath());
        $this->setData('selectedOptionalColumns', $plugin->getSetting(CONTEXT_ID_NONE, 'optionalColumns'));
        $this->setData('compressArchives', $plugin->getSetting(CONTEXT_ID_NONE, 'compressArchives'));
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     */
    public function readInputData() {
        $this->readUserVars([
            'createLogFiles', 
            'accessLogFileParseRegex', 
            'dataPrivacyOption', 
            'optionalColumns', 
            'compressArchives', 
            'saltFilepath'
        ]);
        $this->setData('selectedOptionalColumns', $this->getData('optionalColumns'));
    }

    /**
     * Display the form.
     * @see Form::fetch()
     * @param PKPRequest|null $request
     * @param string|null $template
     * @return string
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->plugin->getName());
        
        $saltFilepath = Config::getVar('usageStats', 'salt_filepath');

        $isSaltValid = $saltFilepath && file_exists((string) $saltFilepath) && is_writable((string) $saltFilepath);
        $templateMgr->assign('saltFilepath', $isSaltValid);
        
        $templateMgr->assign('optionalColumnsOptions', $this->getOptionalColumnsList());
        
        if (!$this->getData('selectedOptionalColumns')) {
            $this->setData('selectedOptionalColumns', []);
        }
        
        parent::display($request, $template);
    }

    /**
     * Save settings.
     * @see Form::execute()
     * @param mixed $object (optional) Unused.
     */
    public function execute($object = null) {
        $plugin = $this->plugin;

        $plugin->updateSetting(CONTEXT_ID_NONE, 'createLogFiles', $this->getData('createLogFiles'));
        $plugin->updateSetting(CONTEXT_ID_NONE, 'accessLogFileParseRegex', $this->getData('accessLogFileParseRegex'));
        $plugin->updateSetting(CONTEXT_ID_NONE, 'dataPrivacyOption', $this->getData('dataPrivacyOption'));
        $plugin->updateSetting(CONTEXT_ID_NONE, 'compressArchives', $this->getData('compressArchives'));
        $plugin->updateSetting(CONTEXT_ID_NONE, 'saltFilepath', $this->getData('saltFilepath'));

        $optionalColumns = $this->getData('optionalColumns') ?? [];
        
        // Make sure optional columns data makes sense.
        if (in_array(STATISTICS_DIMENSION_CITY, $optionalColumns, true) && !in_array(STATISTICS_DIMENSION_REGION, $optionalColumns, true)) {
            $request = Application::get()->getRequest();
            $user = $request->getUser();
            
            if ($user) {
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification(
                    $user->getId(), 
                    NOTIFICATION_TYPE_WARNING, 
                    ['contents' => __('plugins.generic.usageStats.settings.optionalColumns.cityRequiresRegion')]
                );
            }
            $optionalColumns[] = STATISTICS_DIMENSION_REGION;
        }
        
        $plugin->updateSetting(CONTEXT_ID_NONE, 'optionalColumns', $optionalColumns);
    }

    /**
     * Get optional columns list.
     * @return array
     */
    public function getOptionalColumnsList() {
        $plugin = $this->plugin;
        $reportPlugin = $plugin->getReportPlugin();
        $optionalColumns = $reportPlugin->getOptionalColumns(OJS_METRIC_TYPE_COUNTER);
        $columnsList = [];
        
        foreach ($optionalColumns as $column) {
            $columnsList[$column] = StatisticsHelper::getColumnNames($column);
        }
        
        return $columnsList;
    }

    /**
     * Check for the presence of dependent fields if a field value is set.
     * The Complement call will enforce a dependent value as unset if a field value is set.
     * @param mixed $fieldValue
     * @param Form $form
     * @param string $dependentFieldName
     * @param mixed $expectedValue
     * @return bool
     */
    public function _dependentFormFieldIsSet($fieldValue, $form, $dependentFieldName, $expectedValue = null) {
        if ($fieldValue) {
            $dependentValue = $form->getData($dependentFieldName);
            if ($dependentValue) {
                if ($expectedValue !== null) {
                    // Check the expected value against the dependent value
                    if (is_array($dependentValue)) {
                        return in_array($expectedValue, $dependentValue, true);
                    } else {
                        return $dependentValue === $expectedValue;
                    }
                } else {
                    // Field was set and any dependent value is allowed
                    return true;
                }
            } else {
                // Field was set but no dependent value was set
                return false;
            }
        } else {
            // This is false so the complement call will be true when checking a negative dependency
            // e.g., if $fieldValue, $dependentFieldName can't contain $expectedValue
            return false;
        }
    }
    
}
?>