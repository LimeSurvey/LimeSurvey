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
     * Updates Common.
     *
     * @param TemplateConfiguration $model Template Configuration
     * @param int|null $sid Survey ID
     * @param int|null $gsid Survey Group ID
     *
     * @return array
     * @throws NotFoundException
     */
    public function updateCommon(TemplateConfiguration $model, int $sid = null, int $gsid = null)
    {
        /* init the template to current one if option use some twig function (imageSrc for example) mantis #14363 */
        $oTemplate = Template::model()->getInstance($model->template_name, $sid, $gsid);
        $oModelWithInheritReplacement = TemplateConfiguration::model()->findByPk($model->id);
        if ($oModelWithInheritReplacement === null) {
            throw new NotFoundException(gT("Survey theme {$model->template_name} not found."));
        }
        $aOptionAttributes = TemplateManifest::getOptionAttributes($oTemplate->path);
        $oTemplate = $oModelWithInheritReplacement->prepareTemplateRendering($oModelWithInheritReplacement->template->name); // Fix empty file lists
        $aTemplateConfiguration = $oTemplate->getOptionPageAttributes();
        if ($aOptionAttributes['optionsPage'] === 'core') {
            App()->clientScript->registerPackage('themeoptions-core');
            $templateOptionPage = '';
        } else {
            $templateOptionPage = $oModelWithInheritReplacement->getOptionPage();
        }
        $inheritName = $oModelWithInheritReplacement->sTemplateName;
        $oSimpleInheritance = Template::getInstance($inheritName, $sid, $gsid, null, true);
        $oSimpleInheritance->options = 'inherit';
        $oSimpleInheritanceTemplate = $oSimpleInheritance->prepareTemplateRendering($inheritName);
        $oParentOptions = (array)$oSimpleInheritanceTemplate->oOptions;
        $aData = [
            'model'                  => $model,
            'templateOptionPage'     => $templateOptionPage,
            'optionInheritedValues'  => $oModelWithInheritReplacement->oOptions,
            'optionCssFiles'         => $oModelWithInheritReplacement->files_css,
            'optionCssFramework'     => $oModelWithInheritReplacement->cssframework_css,
            'aTemplateConfiguration' => $aTemplateConfiguration,
            'aOptionAttributes'      => $aOptionAttributes,
            'oParentOptions'         => $oParentOptions,
            'sPackagesToLoad'        => $oModelWithInheritReplacement->packages_to_load,
            'sid'                    => $sid,
            'gsid'                   => $gsid
        ];
        if ($sid !== null) {
            $aData['surveyid'] = $sid;
        }
        $actionBaseUrl = 'themeOptions/update/';
        $actionUrlArray = ['id' => $model->id];
        if ($model->sid) {
            $actionBaseUrl = 'themeOptions/updateSurvey/';
            unset($actionUrlArray['id']);
            $actionUrlArray['surveyid'] = $model->sid;
            $actionUrlArray['gsid'] = $model->gsid ? $model->gsid : $gsid;
        }
        if ($model->gsid) {
            $actionBaseUrl = 'themeOptions/updateSurveyGroup/';
            unset($actionUrlArray['id']);
            $actionUrlArray['gsid'] = $model->gsid;
            $actionUrlArray['id'] = $model->id;
        }
        $aData['actionUrl'] = App()->getController()->createUrl($actionBaseUrl, $actionUrlArray);
        return $aData;
    }

     /**
     * Returns a Theme options
     *
     * @param integer $iSurveyId
     * @param string $sTemplateName
     * @return array
     */
    public function getSurveyThemeOptions($iSurveyId = 0, $sTemplateName = null): array
    {
        $oParentMainConfiguration = Template::getTemplateConfiguration($sTemplateName, null, null);
        $oCurrentConfiguration = TemplateConfiguration::model()->getInstanceFromSurveyId($iSurveyId, $sTemplateName);
        $oCurrentConfiguration->bUseMagicInherit = true;

        $oCurrentConfiguration->oParentTemplate = $oParentMainConfiguration;
        $oCurrentConfiguration->oParentTemplate->bUseMagicInherit = true;
        $oCurrentConfiguration->setOptions();
        $themeOptions = (array)$oCurrentConfiguration->oOptions;

        if (empty($themeOptions)) {
            return [];
        }

        return $themeOptions;
    }

     /**
     * Returns a Theme options attributes (eg: fonts, variations ...)
     *
     * @param integer $iSurveyId
     * @param string $sTemplateName
     * @return array
     */
    public function getSurveyThemeOptionsAttributes($iSurveyId = 0, $sTemplateName = null): array
    {
        $attributes = [];
        $oTemplate = Template::model()->getInstance($sTemplateName, $iSurveyId);

        $aTemplateAttribute = $oTemplate->getOptionPageAttributes();
        $attributes['imageFileList'] = $aTemplateAttribute['imageFileList'];

        $aOptionAttributes = TemplateManifest::getOptionAttributes($oTemplate->path);
        $fontsDropdownString = $aOptionAttributes['optionAttributes']['font']['dropdownoptions'];
        $cssframeworkDropdownString = $aOptionAttributes['optionAttributes']['cssframework']['dropdownoptions'] ?? '';

        $attributes['fonts'] = $this->extractDropdownOptions($fontsDropdownString);
        $attributes['cssframework'] = $this->extractDropdownOptions($cssframeworkDropdownString);

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
