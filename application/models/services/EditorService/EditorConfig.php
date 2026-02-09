<?php

namespace LimeSurvey\Models\Services\EditorService;

class EditorConfig
{
    private bool $isQuestionEditorEnabled;

    /**
     * @param boolean $isQuestionEditorEnabled
     */
    public function __construct(bool $isQuestionEditorEnabled = true)
    {
        $this->isQuestionEditorEnabled = $isQuestionEditorEnabled;
    }

    /**
     * Init app config
     */
    public function initAppConfig()
    {
        App()->setConfig('editorEnabled', $this->getIsEditorEnabled());
    }

    /**
     * Get editor enabled state
     *
     * @return boolean
     */
    public function getIsEditorEnabled()
    {
        $result = false;
        if ($this->isBackendAccess()) {
            if ($this->isQuestionEditorEnabled) {
                $surveyId = EditorRequestHelper::findSurveyId();
                if (!$surveyId) {
                    $result = true;
                } else {
                    $survey = \Survey::model()->findByPk($surveyId);
                    if (
                        $survey
                        && $survey->getTemplateEffectiveName() === 'fruity_twentythree'
                    ) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * If user is a logged-in user we can assume, that backend is accessed right now.
     */
    private function isBackendAccess(): bool
    {
        return !App()->user->isGuest;
    }
}
