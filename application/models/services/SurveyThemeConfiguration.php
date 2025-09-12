<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use Permission;
use Template;
use TemplateConfiguration;
use TemplateManifest;

class SurveyThemeConfiguration
{
    private Permission $permission;

    public function __construct(
        Permission $permission
    ) {
        $this->permission = $permission;
    }

    /**
     * @param int $surveyId
     * @param array $props properties (in options json string)
     *
     * @return void
     * @throws PermissionDeniedException
     */
    public function updateThemeOption($surveyId, $props): void
    {
        if (
            !Permission::model()->hasGlobalPermission('templates', 'update')
            && !Permission::model()->hasSurveyPermission($surveyId, 'surveysettings', 'update')
        ) {
            throw new PermissionDeniedException(gT("You do not have permission to access this page."), 403);
        }

        $model = $this->turnAjaxmodeOffAsDefault($surveyId, $props['templateName']);
        $model->save();
        $model->bUseMagicInherit = true;

        $model->setOptions();
        $attributes = $model->attributes;
        $oPreviousOptions = $model->oOptions;

        foreach ($props as $key => $value) {
            if ($key === 'templateName') {
                continue;
            }

            // Process replacement for cssframework and font files references
            if ($key === 'cssframework' || $key === 'font') {
                $oldFileValue = $this->getOptionAttributeDataValue($surveyId, $props['templateName'], $oPreviousOptions->$key, $key);
                $newFileValue = $this->getOptionAttributeDataValue($surveyId, $props['templateName'], $value, $key);

                $attributeKey = ($key === 'cssframework') ? 'files_css' : 'packages_to_load';
                $attributeInherited = $model->__get($attributeKey);
                $attributes[$attributeKey] = str_replace($oldFileValue, $newFileValue, $attributeInherited);
            }

            $oPreviousOptions->$key = $value;
        }

        $sNewOptions = json_encode($oPreviousOptions);
        $attributes['options'] = $sNewOptions;
        $model->attributes = $attributes;
        $model->save();
    }

    /**
     * This method turns ajaxmode off as default.
     * @param int $surveyId survey ID of the survey
     * @param string $sTemplateName
     *
     * @return TemplateConfiguration
     */
    protected function turnAjaxmodeOffAsDefault(int $surveyId, $sTemplateName = null): TemplateConfiguration
    {
        $templateConfiguration = TemplateConfiguration::getInstance($sTemplateName, null, $surveyId);
        $attributes = $templateConfiguration->getAttributes();
        $hasOptions = isset($attributes['options']);
        if ($hasOptions) {
            $options = $attributes['options'] ?? '';
            $optionsJSON = json_decode($options, true);

            if ($options !== 'inherit' && $optionsJSON !== null) {
                $ajaxModeOn = (!empty($optionsJSON['ajaxmode']) && $optionsJSON['ajaxmode'] == 'on');
                if ($ajaxModeOn) {
                    $optionsJSON['ajaxmode'] = 'off';
                    $options = json_encode($optionsJSON);
                    $templateConfiguration->setAttribute('options', $options);
                }
            }
        }
        return $templateConfiguration;
    }

    /**
     * Returns all attributes and options needed to display the themeoptions inlcuding inheritance.
     *
     * @param TemplateConfiguration $themeConfiguration Template Configuration
     * @param int|null $sid Survey ID
     * @param int|null $gsid Survey Group ID
     *
     * @return array
     * @throws NotFoundException
     */
    public function updateCommon(TemplateConfiguration $themeConfiguration, int $sid = null, int $gsid = null)
    {
        /* init the template to current one if option use some twig function (imageSrc for example) mantis #14363 */
        // Template::getInstance will call prepareTemplateRendering which will populate array needed for inheritance display
        $preparedThemeConfigurationModel = Template::model()->getInstance(
            $themeConfiguration->template_name,
            $sid,
            $gsid
        );
        if ($preparedThemeConfigurationModel === null) {
            throw new NotFoundException(gT("Survey theme {$themeConfiguration->template_name} not found."));
        }
        $themeCategoriesAndOptions = TemplateManifest::getOptionAttributes($preparedThemeConfigurationModel->path);
        $themeConfigurationAttributesAndFiles = $preparedThemeConfigurationModel->getOptionPageAttributes();
        if ($themeCategoriesAndOptions['optionsPage'] === 'core') {
            App()->clientScript->registerPackage('themeoptions-core');
            $customThemeOptionsPage = '';
        } else {
            $customThemeOptionsPage = $preparedThemeConfigurationModel->getOptionPage();
        }
        if ($preparedThemeConfigurationModel->oParentTemplate !== null) {
            $parentTheme = $preparedThemeConfigurationModel->oParentTemplate;
            $preparedParentTheme = $parentTheme->prepareTemplateRendering();
        }
        /** TODO: most of the options in this array should be renamed to better reflect what they actually contain,
         *  TODO: but it would break backwards compatibility with custom themes, unless we modify strings inside twig through a query
         */
        $aData = [
            // $themeConfiguration needs to be a model with $bUseMagicInherit turned off since the advanced settings use an active form,
            // which would use the magic getter to resolve 'inherit' based values
            'model'                  => $themeConfiguration,
            'templateOptionPage'     => $customThemeOptionsPage,
            'oParentOptions'         => (array)($preparedParentTheme->oOptions ?? []),
            'optionCssFiles'         => $preparedThemeConfigurationModel->files_css,
            'optionCssFramework'     => $preparedThemeConfigurationModel->cssframework_css,
            'aTemplateConfiguration' => $themeConfigurationAttributesAndFiles,
            'aOptionAttributes'      => $themeCategoriesAndOptions,
            'sPackagesToLoad'        => $preparedThemeConfigurationModel->packages_to_load,
            'sid'                    => $sid,
            'gsid'                   => $gsid,
        ];
        if ($sid !== null) {
            $aData['surveyid'] = $sid;
        }
        $actionBaseUrl = 'themeOptions/update/';
        $actionUrlArray = ['id' => $themeConfiguration->id];
        if ($themeConfiguration->sid) {
            $actionBaseUrl = 'themeOptions/updateSurvey/';
            unset($actionUrlArray['id']);
            $actionUrlArray['surveyid'] = $themeConfiguration->sid;
            $actionUrlArray['gsid'] = $themeConfiguration->gsid ? $themeConfiguration->gsid : $gsid;
        }
        if ($themeConfiguration->gsid) {
            $actionBaseUrl = 'themeOptions/updateSurveyGroup/';
            unset($actionUrlArray['id']);
            $actionUrlArray['gsid'] = $themeConfiguration->gsid;
            $actionUrlArray['id'] = $themeConfiguration->id;
        }
        $aData['actionUrl'] = App()->getController()->createUrl($actionBaseUrl, $actionUrlArray);
        return $aData;
    }

     /**
     * Returns the theme option attributes with custom format for react
     *
     * @param array $optionAttributes
     * @return array
     */
    public function getSurveyThemeOptionsAttributes($optionAttributes): array
    {
        $attributes = $optionAttributes;
        foreach ($optionAttributes as $key => $optionAttribute) {
            if ($optionAttribute['type'] === 'dropdown') {
                $attributes[$key]['dropdownoptions'] = $this->extractDropdownOptions($optionAttribute['dropdownoptions']);
            }
        }

        return $attributes;
    }

     /**
     * Extracts option values and labels from HTML dropdown options string
     *
     * @param string $optionsHtml string containing option tags
     * @return array values and labels
     */
    protected function extractDropdownOptions($optionsHtml): array
    {
        $options = [];

        if (empty($optionsHtml)) {
            return $options;
        }

        $dom = new \DOMDocument();
        $html = '<select>' . $optionsHtml . '</select>';

        // Suppress errors for invalid HTML
        libxml_use_internal_errors(true);
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $optionElements = $dom->getElementsByTagName('option');

        foreach ($optionElements as $option) {
            $value = $option->getAttribute('value');

            $label = $option->textContent;
            $label = preg_replace('/\s+/', ' ', $label);
            $label = trim($label);

            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

     /**
     * Gets the specific data value for an option based on its value
     *
     * @param integer $iSurveyId The survey ID
     * @param string $sTemplateName The template name
     * @param string $optionValue The value of the option to find
     * @param string $optionType The type of option ('font' || 'cssframework')
     * @return string
     */
    protected function getOptionAttributeDataValue($iSurveyId = 0, $sTemplateName = null, $optionValue = '', $optionType = ''): string
    {
        $lowercasedValue = strtolower($optionValue);
        $oTemplate = Template::model()->getInstance($sTemplateName, $iSurveyId);

        $aOptionAttributes = TemplateManifest::getOptionAttributes($oTemplate->path);

        if (!isset($aOptionAttributes['optionAttributes'][$optionType]['dropdownoptions'])) {
            return '';
        }

        $optionsHtml = $aOptionAttributes['optionAttributes'][$optionType]['dropdownoptions'];

        $attributeName = ($optionType === 'font') ? 'data-font-package' : 'data-value';

        $dom = new \DOMDocument();
        @$dom->loadHTML('<select>' . $optionsHtml . '</select>');

        $options = $dom->getElementsByTagName('option');

        foreach ($options as $option) {
            $lowercasedAttributeValue = strtolower($option->getAttribute('value'));
            if ($lowercasedAttributeValue === $lowercasedValue) {
                return $option->getAttribute($attributeName);
            }
        }

        return '';
    }
}
