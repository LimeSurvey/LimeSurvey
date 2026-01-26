<?php


class EditorRedirector
{
    /**
     * Routing map
     *
     * Map of CE URLs to editor routes
     *
     * Editor routes can contain variable:
     * - $surveyId
     */
    private $routingMap = [
        'surveyAdministration/view' => 'survey/$surveyId',
        'surveyAdministration/rendersidemenulink/subaction/generalsettings/surveyid/' => 'survey/$surveyId/settings/generalsettings',
        'questionAdministration/view' => 'survey/$surveyId/?question=$questionId',
        'questionAdministration/edit' => 'survey/$surveyId/?question=$questionId',
        'questionAdministration/create' => 'survey/$surveyId/structure',
        'questionGroupsAdministration/add' => 'survey/$surveyId/structure',
        'questionGroupsAdministration/view' => 'survey/$surveyId?group=$questionGroupId',
        'questionGroupsAdministration/edit' => 'survey/$surveyId?group=$questionGroupId',
    ];

    /**
     * Handler editor redirect
     *
     * If editor is enabled and we are attemppting to access a
     * page which has been moved to the new editor, redirect to
     * the page in the new editor.
     */
    public function handleRedirect()
    {
        $isEditorEnabled = SettingsUser::getUserSettingValue('editorEnabled');
        if ($isEditorEnabled) {
            $path = Yii::app()->request->getPathInfo();
            foreach ($this->routingMap as $source => $destination) {
                if ($path == $source || strpos($path, $source) === 0) {
                    // Check if we have the required surveyId for this destination
                    $surveyId = EditorRequestHelper::findSurveyId();
                    if ($surveyId !== null || strpos($destination, '$surveyId') === false) {
                        $this->redirectToEditorRoute($destination);
                    }
                    break;
                }
            }
        }
    }

    /**
     * Redirect to editor route
     *
     * @param string $route
     */
    private function redirectToEditorRoute($route)
    {
        $editorUrl = App()->createUrl(
            'editorLink/index',
            ['route' => $this->replaceVariables($route)]
        );
        Yii::app()->request->redirect($editorUrl);
    }

    /**
     * Replace variables in a URI with their actual values
     *
     * Takes a URI string that may contain placeholders like $surveyId and replaces
     * them with actual values retrieved from the request. This allows for dynamic
     * route generation based on the current context.
     *
     * @param string $uri The URI string containing variables to be replaced
     * @return string The URI with all recognized variables replaced with their values
     */
    private function replaceVariables($uri)
    {
        $replacements = [
            '$surveyId' => EditorRequestHelper::findSurveyId(),
            '$questionId' => EditorRequestHelper::findQuestionId(),
            '$questionGroupId' => EditorRequestHelper::findQuestionGroupId()
        ];

        $parts = explode('/', $uri);
        $finalParts = array_map(
            function ($part) use ($replacements) {
                $replacedPart = $part;
                foreach ($replacements as $variable => $value) {
                    if (
                        strpos($replacedPart, $variable) !== false
                        && $value !== null
                    ) {
                        $replacedPart = str_replace(
                            $variable,
                            $value,
                            $replacedPart
                        );
                    }
                }
                return $replacedPart;
            },
            $parts
        );
        return implode('/', $finalParts);
    }
}
