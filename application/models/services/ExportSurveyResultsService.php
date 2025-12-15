<?php

namespace LimeSurvey\Models\Services;

class ExportSurveyResultsService
{
    /**
     * Hold the available export types
     *
     * @var array
     */
    protected $_exports;

    /**
     * Root function for any export results action
     *
     * @param mixed $iSurveyId
     * @param mixed $sLanguageCode
     * @param string $sExportPlugin Type of export
     * @param FormattingOptions $oOptions
     * @param string $sFilter
     * @return
     * @throws Exception
     */
    function exportResponses($iSurveyId, $sLanguageCode, $sExportPlugin, FormattingOptions $oOptions, $sFilter = '')
    {
//Do some input validation.
        if (empty($iSurveyId)) {
            safeDie('A survey ID must be supplied.');
        }
        if (empty($sLanguageCode)) {
            safeDie('A language code must be supplied.');
        }
        if (empty($oOptions)) {
            safeDie('Formatting options must be supplied.');
        }
        if (empty($oOptions->selectedColumns)) {
            safeDie('At least one column must be selected for export.');
        }
//echo $oOptions->toString().PHP_EOL;
        $writer = null;

        $iSurveyId = sanitize_int($iSurveyId);
        if ($oOptions->output == 'display') {
            header("Cache-Control: must-revalidate, no-store, no-cache");
        }

        $exports = $this->getExports();

        if (array_key_exists($sExportPlugin, $exports) && !empty($exports[$sExportPlugin])) {
// This must be a plugin, now use plugin to load the right class
            $event = new PluginEvent('newExport');
            $event->set('type', $sExportPlugin);
            $oPluginManager = App()->getPluginManager();
            $oPluginManager->dispatchEvent($event, $exports[$sExportPlugin]);
            $writer = $event->get('writer');
        }

        if (!($writer instanceof IWriter)) {
            throw new Exception(sprintf('Writer for %s should implement IWriter', $sExportPlugin));
        }

        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($iSurveyId, $sLanguageCode, $oOptions);
        $writer->init($survey, $sLanguageCode, $oOptions);

        $countResponsesCommand = $surveyDao->loadSurveyResults($survey, $oOptions->responseMinRecord, $oOptions->responseMaxRecord, $sFilter, $oOptions->responseCompletionState, $oOptions->selectedColumns, $oOptions->aResponses);
        $countResponsesCommand->order = false;
        $countResponsesCommand->select('count(*)');
        $responseCount = $countResponsesCommand->queryScalar();
        $maxRows = 100;
        $maxPages = ceil($responseCount / $maxRows);
        for ($i = 0; $i < $maxPages; $i++) {
            $offset = $i * $maxRows;
            $responsesQuery = $surveyDao->loadSurveyResults($survey, $oOptions->responseMinRecord, $oOptions->responseMaxRecord, $sFilter, $oOptions->responseCompletionState, $oOptions->selectedColumns, $oOptions->aResponses);
            $responsesQuery->offset($offset);
            $responsesQuery->limit($maxRows);
            $survey->responses = $responsesQuery->query();
            $writer->write($survey, $sLanguageCode, $oOptions, true);
        }
        $result = $writer->close();

// Close resultset if needed
        if ($survey->responses instanceof CDbDataReader) {
            $survey->responses->close();
        }

        if ($oOptions->output == 'file') {
            return $writer->filename;
        } else {
            return $result;
        }
    }

    /**
     * Get an array of available export types
     *
     * @return array
     */
    public function getExports()
    {
        if (is_null($this->_exports)) {
            $event = new PluginEvent('listExportPlugins');
            $oPluginManager = App()->getPluginManager();
            $oPluginManager->dispatchEvent($event);

            $exports = $event->get('exportplugins', array());

            $this->_exports = $exports;
        }

        return $this->_exports;
    }
}
