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
     * @param int $surveyGroupId
     *
     * @return array
     * @throws PermissionDeniedException
     */
    public function update($surveyId, $surveyGroupId = null): array
    {
        if (
            // Did we really need hasGlobalPermission template ? We are inside survey : hasSurveyPermission only seem better
            !Permission::model()->hasGlobalPermission('templates', 'update')
            && !Permission::model()->hasSurveyPermission($surveyId, 'surveysettings', 'update')
        ) {
            throw new PermissionDeniedException(gT("You do not have permission to access this page."), 403);
        }
        // turn ajaxmode off as default behavior and return the theme configuration
        $model = $this->turnAjaxmodeOffAsDefault($surveyId);
        $model->save();

        if (isset($_POST['TemplateConfiguration'])) {
            $model->attributes = $_POST['TemplateConfiguration'];
            if ($model->save()) {
                App()->user->setFlash('success', gT('Theme options saved.'));
            }
        }
        $this->updateCommon($model, $surveyId, $surveyGroupId);

        return $model;
    }

    /**
     * This method turns ajaxmode off as default.
     * @param int $surveyId survey ID of the survey
     *
     * @return TemplateConfiguration
     */
    protected function turnAjaxmodeOffAsDefault(int $surveyId): TemplateConfiguration
    {
        $templateConfiguration = TemplateConfiguration::getInstance(null, null, $surveyId);
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
        $oSimpleInheritance = Template::getInstance(
            $oModelWithInheritReplacement->sTemplateName,
            $sid,
            $gsid,
            null,
            true
        );
        $oSimpleInheritance->options = 'inherit';
        $oSimpleInheritanceTemplate = $oSimpleInheritance->prepareTemplateRendering(
            $oModelWithInheritReplacement->sTemplateName
        );
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
}
