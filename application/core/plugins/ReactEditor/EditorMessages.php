<?php

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
        return gT(
            'In order to activate the new editor you or your administrator need to set up the application to use "urlFormat" => "path" in the file application/config/config.php.'
        );
    }
}
