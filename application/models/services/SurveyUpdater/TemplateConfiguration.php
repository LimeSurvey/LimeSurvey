<?php

namespace LimeSurvey\Models\Services\SurveyUpdater;

/**
 * Survey Updater Service template configuration
 *
 */
class TemplateConfiguration
{
    /**
     * Update
     *
     * @param int $surveyId
     * @return void
     */
    public function update($surveyId)
    {
        // This will force the generation of the entry for survey group
        \TemplateConfiguration::checkAndcreateSurveyConfig(
            $surveyId
        );
    }

    /**
     * Update Variant
     *
     * @param int $surveyId
     * @param array $input
     * @return void
     */
    public function updateVariant($surveyId, $input)
    {
        $variant = $input['variant'];
        $variant_css = $input['variant_css'];
        $oSurvey = \Survey::model()->findByPk($surveyId);
        $sTemplateName = $oSurvey->template;

        $oSurveyConfig = \TemplateConfiguration::getInstance($sTemplateName, null, $surveyId);
        if ($oSurveyConfig->options === 'inherit') {
            $oSurveyConfig->setOptionKeysToInherit();
        }
        if ($variant) {
            $oSurveyConfig->setOption('cssframework', $variant);
        }
        if ($variant_css) {
            $oSurveyConfig->files_css = "{\"add\":[\"$variant_css\"]}";
        }
        $oSurveyConfig->save();
    }
}
