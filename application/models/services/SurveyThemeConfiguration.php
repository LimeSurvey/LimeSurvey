<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use Permission;
use Template;
use TemplateConfiguration;
use TemplateManifest;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SurveyThemeConfiguration
{
    private const DEFAULT_CORNER_RADIUS = '2';

    private Permission $permission;

    public function __construct(
        Permission $permission
    ) {
        $this->permission = $permission;
    }

    /**
     * Returns the theme-related view data (themeConf, inheritedThemeName, aTemplateList)
     * needed by _generaloptions_panel.php.
     *
     * Centralizes logic previously duplicated across multiple controllers.
     *
     * @param string|null $surveyTemplate  The survey's current template value (e.g. 'inherit' or a theme name).
     * @param object|null $surveyOptions   The resolved survey options object (must have ->template property).
     * @return array{themeConf: TemplateConfiguration, inheritedThemeName: string, aTemplateList: array}
     */
    public function getThemeViewData(?string $surveyTemplate, ?object $surveyOptions = null): array
    {
        $defaultTheme = \App()->getConfig('defaulttheme');
        $inheritedThemeName = $surveyOptions->template ?? $defaultTheme;

        $templateName = ($surveyTemplate === 'inherit')
            ? ($surveyOptions->template ?? $defaultTheme)
            : ($surveyTemplate ?? $defaultTheme);

        $themeConf = TemplateConfiguration::getInstanceFromTemplateName($templateName);

        $aTemplateList = $this->getAvailableTemplates(null, $templateName);

        return [
            'themeConf'          => $themeConf,
            'inheritedThemeName' => $inheritedThemeName,
            'aTemplateList'      => $aTemplateList,
        ];
    }

    /**
     * Returns the list of available templates for the current user,
     * optionally filtered to only those that are (or extend from) a given base template.
     *
     * Each entry contains 'name' and 'folder'.
     * Permission checks mirror what _generaloptions_panel.php does:
     * - global 'templates' read permission, OR
     * - specific template permission, OR
     * - the template is the survey's currently assigned template.
     *
     * @param string|null $baseTemplateName    If provided, only templates that ARE this template
     *                                         or inherit from it (directly or transitively) are returned.
     * @param string|null $currentTemplateName The survey's currently assigned template name.
     *                                         Always included regardless of permissions.
     * @return array<int, array{name: string, folder: string}>
     */
    public function getAvailableTemplates(?string $baseTemplateName = null, ?string $currentTemplateName = null): array
    {
        $allTemplates = Template::getTemplateList();
        $result = [];

        foreach ($allTemplates as $templateName => $folder) {
            // Permission check: global permission, template-specific permission, or current survey template
            $hasPermission = $this->permission->hasGlobalPermission('templates', 'read')
                || $this->permission->hasTemplatePermission($templateName)
                || ($currentTemplateName !== null && $templateName === $currentTemplateName);

            if (!$hasPermission) {
                continue;
            }

            // If a base template filter is given, check inheritance
            if ($baseTemplateName !== null && !$this->isBasedOn($templateName, $baseTemplateName)) {
                continue;
            }

            $result[] = [
                'name'   => $templateName,
                'folder' => $folder,
            ];
        }

        return $result;
    }

    /**
     * Checks whether a given template is the specified base template
     * or extends from it (walks up the full inheritance chain).
     *
     * Delegates to Template::isBasedOn().
     *
     * @param string $templateName      The template to check.
     * @param string $baseTemplateName  The base template name to look for.
     * @return bool
     */
    protected function isBasedOn(string $templateName, string $baseTemplateName): bool
    {
        return Template::isBasedOn($templateName, $baseTemplateName);
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

        $model = TemplateConfiguration::getInstance($props['templateName'] ?? null, null, $surveyId);
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
     * Returns all attributes and options needed to display the themeoptions including inheritance.
     *
     * @param TemplateConfiguration $themeConfiguration Template Configuration
     * @param ?int $sid Survey ID
     * @param ?int $gsid Survey Group ID
     *
     * @return array
     * @throws NotFoundException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function updateCommon(TemplateConfiguration $themeConfiguration, ?int $sid = null, ?int $gsid = null)
    {
        /* init the template to current one if option use some twig function (imageSrc for example) mantis #14363 */
        // Template::getInstance will call prepareTemplateRendering which will populate array needed for inheritance display
        $preparedThemeConfigurationModel = Template::model()->getInstance($themeConfiguration->template_name, $sid, $gsid);
        if ($preparedThemeConfigurationModel === null) {
            throw new NotFoundException(gT("Survey theme {$themeConfiguration->template_name} not found."));
        }
        $themeCategoriesAndOptions = TemplateManifest::getOptionAttributes($preparedThemeConfigurationModel->path);
        $themeConfigurationAttributesAndFiles = $preparedThemeConfigurationModel->getOptionPageAttributes();

        // Resolve option image file paths by walking up the inheritance chain
        if (!empty($themeCategoriesAndOptions['optionAttributes'])) {
            foreach ($themeCategoriesAndOptions['optionAttributes'] as $key => &$attribute) {
                if (!empty($attribute['optionimages'])) {
                    $imageNames = explode('|', $attribute['optionimages']);
                    $resolvedPaths = [];
                    foreach ($imageNames as $imageName) {
                        $resolvedPaths[$imageName] = $preparedThemeConfigurationModel->resolveOptionImagePath($imageName);
                    }
                    $attribute['resolvedOptionImagePaths'] = $resolvedPaths;
                }
            }
            unset($attribute);
        }

        if ($themeCategoriesAndOptions['optionsPage'] === 'core') {
            App()->clientScript->registerPackage('themeoptions-core');
            $customThemeOptionsPage = '';
        } else {
            $customThemeOptionsPage = $preparedThemeConfigurationModel->getOptionPage();
        }
        $parentOptions = [];
        if ($preparedThemeConfigurationModel->oParentTemplate !== null) {
            $parentTheme = $preparedThemeConfigurationModel->oParentTemplate;
            $preparedParentTheme = $parentTheme->prepareTemplateRendering();
            $parentOptions = (array)($preparedParentTheme->oOptions ?? []);
            // Some themes (e.g. bootswatch) only set their default variation as css file, not as option
            if (empty($parentOptions['cssframework'])) {
                $parentVariation = $this->getVariationFromCssFiles(
                    $preparedParentTheme,
                    $themeCategoriesAndOptions['optionAttributes']['cssframework']['dropdownoptions'] ?? ''
                );
                if ($parentVariation !== '') {
                    $parentOptions['cssframework'] = $parentVariation;
                }
            }
            $this->addInheritedValueLabels($themeCategoriesAndOptions, $parentOptions);
        }
        /** TODO: most of the options in this array should be renamed to better reflect what they actually contain,
         *  TODO: but it would break backwards compatibility with custom themes, unless we modify strings inside twig through a query
         */
        $aData = [
            // $themeConfiguration needs to be a model with $bUseMagicInherit turned off since the advanced settings use an active form,
            // which would use the magic getter to resolve 'inherit' based values
            'model'                  => $themeConfiguration,
            'templateOptionPage'     => $customThemeOptionsPage,
            'oParentOptions'         => $parentOptions,
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
     * @param array $themeData
     * @return array
     */
    public function getSurveyThemeOptionsAttributes($themeData): array
    {
        $attributesReact = $themeData['aOptionAttributes']['optionsOrderReact'] ?? [];
        $attributesCore = $themeData['aOptionAttributes']['optionAttributes'] ?? [];
        $currentThemeOptions = $themeData['aTemplateConfiguration']['options'] ?? [];
        $parentThemeOptions = $themeData['oParentOptions'] ?? [];
        $attributesCompleteData = [];
        $imageDefaultDropDown = [];
        $iterator = 0;
        $imageFileList = $themeData['aTemplateConfiguration']['imageFileList'] ?? [];
        foreach ($imageFileList as $imageData) {
            $imageDefaultDropDown[$iterator]['value'] = $imageData['filepath'];
            $imageDefaultDropDown[$iterator]['label'] = $imageData['filename'];
            $imageDefaultDropDown[$iterator]['group'] = $imageData['group'];
            $imageDefaultDropDown[$iterator]['imagePreviewPath'] = $imageData['preview'];
            $imageDefaultDropDown[$iterator]['imageOriginPath'] = $imageData['filepathOptions'];
            $iterator++;
        }
        foreach ($attributesReact as $key => $optionAttribute) {
            if (!isset($attributesCore[$key])) {
                continue;
            }

            $attributesCompleteData[$key] = $attributesCore[$key];
            $attributesCompleteData[$key]['category'] = $optionAttribute['category'];
            $attributesCompleteData[$key]['currentValue'] = $currentThemeOptions->$key ?? 'inherit';
            $attributesCompleteData[$key]['parentValue'] = $parentThemeOptions[$key] ?? '';
            if ($key === 'cornerradius' && empty($attributesCompleteData[$key]['parentValue'])) {
                $attributesCompleteData[$key]['parentValue'] = self::DEFAULT_CORNER_RADIUS;
            }
            if (
                $attributesCompleteData[$key]['type'] === 'dropdown'
                // "checkicon" is of type "icon" but has dropdown options
                || !empty($attributesCompleteData[$key]['dropdownoptions'])
            ) {
                $attributesCompleteData[$key]['dropdownoptions'] = $this->extractDropdownOptions($attributesCompleteData[$key]['dropdownoptions']);
                if (empty($attributesCompleteData[$key]['dropdownoptions'])) {
                    $attributesCompleteData[$key]['dropdownoptions'] = $imageDefaultDropDown;
                }
            }

            // TODO: incorporate this option type into the theme properly
            if (in_array($key, ['brandlogofile', 'backgroundimagefile'])) {
                $attributesCompleteData[$key]['hasFileUpload'] = true;
            }

            // TODO: AT-1896 - force temporary theme color to be of icon type for the API
            if ($attributesCompleteData[$key]['title'] === 'Theme color') {
                $attributesCompleteData[$key]['type'] = 'icon';
            }
        }

        return $attributesCompleteData;
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

        foreach ($this->getDropdownOptionElements($optionsHtml) as $option) {
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
     * Parses an HTML dropdown options string into option elements
     *
     * @param string $optionsHtml string containing option tags
     * @return \DOMNodeList<\DOMElement> the option elements
     */
    private function getDropdownOptionElements(string $optionsHtml): \DOMNodeList
    {
        $dom = new \DOMDocument();
        $html = '<select>' . $optionsHtml . '</select>';

        // Suppress errors for invalid HTML
        libxml_use_internal_errors(true);
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        return $dom->getElementsByTagName('option');
    }

    /**
     * Finds the variation (value of the "cssframework" option) a theme uses by looking at its css files.
     * Needed for themes that set their default variation only as css file, e.g. bootswatch through cssframework_css.
     *
     * @param TemplateConfiguration $theme the prepared theme configuration
     * @param string $optionsHtml dropdown options of the "cssframework" option
     * @return string the option value of the variation, or an empty string if none is found
     */
    private function getVariationFromCssFiles(TemplateConfiguration $theme, string $optionsHtml): string
    {
        if (empty($optionsHtml)) {
            return '';
        }
        // Map of css file => option value. The css file is either the option value or its data-value attribute.
        $variations = [];
        foreach ($this->getDropdownOptionElements($optionsHtml) as $option) {
            foreach ([$option->getAttribute('value'), $option->getAttribute('data-value')] as $cssFile) {
                if ($cssFile !== '') {
                    $variations[$cssFile] = $option->getAttribute('value');
                }
            }
        }

        // Framework files are loaded first, so a variation in files_css wins
        $cssFiles = [];
        $frameworkCss = json_decode((string)$theme->cssframework_css, true);
        if (is_array($frameworkCss) && !empty($frameworkCss['replace'])) {
            foreach ((array)$frameworkCss['replace'] as $replacement) {
                // Replacements are pairs of [original file, replacement file]
                $cssFiles[] = is_array($replacement) ? end($replacement) : $replacement;
            }
        }
        $filesCss = json_decode((string)$theme->files_css, true);
        if (is_array($filesCss)) {
            foreach (['add', 'replace'] as $action) {
                $cssFiles = array_merge($cssFiles, (array)($filesCss[$action] ?? []));
            }
        }

        $variation = '';
        foreach ($cssFiles as $cssFile) {
            if (is_string($cssFile) && isset($variations[$cssFile])) {
                $variation = $variations[$cssFile];
            }
        }
        return $variation;
    }

    /**
     * Adds the label of the inherited value to the dropdown option attributes as "inheritedvaluelabel",
     * so the inherited value can be shown with the same text as in the dropdown (e.g. "Flatly" instead of a css file)
     *
     * @param array $themeCategoriesAndOptions option attributes as returned by TemplateManifest::getOptionAttributes()
     * @param array $parentOptions the option values of the parent theme configuration
     * @return void
     */
    private function addInheritedValueLabels(array &$themeCategoriesAndOptions, array $parentOptions): void
    {
        foreach ($themeCategoriesAndOptions['optionAttributes'] ?? [] as $key => $attribute) {
            if (
                ($attribute['type'] ?? '') !== 'dropdown'
                || empty($attribute['dropdownoptions'])
                || !isset($parentOptions[$key])
                || !is_string($parentOptions[$key])
            ) {
                continue;
            }
            foreach ($this->extractDropdownOptions($attribute['dropdownoptions']) as $option) {
                // Option values are not always stored with the same case, see getOptionAttributeDataValue()
                if (strtolower($option['value']) === strtolower($parentOptions[$key])) {
                    $themeCategoriesAndOptions['optionAttributes'][$key]['inheritedvaluelabel'] = $option['label'];
                    break;
                }
            }
        }
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
