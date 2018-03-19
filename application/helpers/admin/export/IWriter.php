<?php
interface IWriter
{
    /**
     * Writes the survey and all the responses it contains to the output
     * using the options specified in FormattingOptions.
     *
     * See Survey and SurveyDao objects for information on loading a survey
     * and results from the database.
     *
     * @param SurveyObj $survey
     * @param string $sLanguageCode
     * @param FormattingOptions $oOptions
     */
    public function write(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions);
    public function close();
}