<?php

namespace ReactEditor;

class EditorMessages
{
    /**
     * Get the message about URL format configuration requirement
     *
     * @return string
     */
    public static function getUrlFormatRequirementHeader()
    {
        return gT(
            'Action needed'
        );
    }

    /**
     * Get the message about URL format configuration requirement
     *
     * @return string
     */
    public static function getUrlFormatRequirementMessage()
    {
        $manualUrl = 'https://www.limesurvey.org/manual/LimeSurvey_7_Question_Editor#Switching_URL_format_from_%22get%22_to_%22path%22_to_enable_the_new_editor_in_LimeSurvey_7';
        $manualLink = '<a class="link-info" href="' . $manualUrl . '" target="_blank" rel="noopener noreferrer">'
            . gT('LimeSurvey 7 Question Editor')
            . '</a>';
        return sprintf(
            gT('In order to activate the new editor you or your administrator need to set up the application to use "path" as the URL format. For more information, see %s.'),
            $manualLink
        );
    }
}
