<?php

/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
* A Survey object may be loaded from the database via the SurveyDao
* (which follows the Data Access Object pattern).  Data access is broken
* into two separate functions: the first loads the survey structure from
* the database, and the second loads responses from the database.  The
* data loading is structured in this way to provide for speedy access in
* the event that a survey's response table contains a large number of records.
* The responses can be loaded a user-defined number at a time for output
* without having to load the entire set of responses from the database.
*
* The Survey object contains methods to conveniently access data that it
* contains in an attempt to encapsulate some of the complexity of its internal
* format.
*
* Data formatting operations that may be specific to the data export routines
* are relegated to the Writer class hierarcy and work with the Survey object
* and FormattingOptions objects to provide proper style/content when exporting
* survey information.
*
* Some guess work has been done when deciding what might be specific to exports
* and what is not.  In general, anything that requires altering of data fields
* (abbreviating, concatenating, etc...) has been moved into the writers and
* anything that is a direct access call with no formatting logic is a part of
* the Survey object.
*
* - elameno
*/

Yii::import('application.helpers.admin.export.*');
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
