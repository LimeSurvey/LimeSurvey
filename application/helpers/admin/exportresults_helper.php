<?php

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
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
* are relegated to the Writer class hierarchy and work with the Survey object
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
        $oOptions->selectedColumns = $this->expandRankingColumns(
            $surveyDao,
            $survey,
            $oOptions->selectedColumns,
            $sLanguageCode,
            $sFilter,
            $oOptions->responseCompletionState,
            $oOptions->responseMinRecord,
            $oOptions->responseMaxRecord,
            $oOptions->aResponses
        );
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
     * Replaces a ranking question's single combined column ("Q{qid}", the only
     * ranking field the export column picker offers, storing a JSON array of
     * ranked subquestion codes) with a dynamically sized, ordered list of
     * per-rank-position columns ("Q{qid}_rank{n}"), one per rank actually used
     * in the exported data, so the output has one column per rank instead of a
     * single JSON-array column. The synthetic columns are registered into
     * $survey->fieldMap so the rest of the export machinery (headers, value
     * extraction, answer formatting) handles them like any other field.
     *
     * The number of columns is taken from the data being exported rather than
     * from the question's subquestion count: a respondent never ranks more
     * items than exist, but may rank fewer, and which is which isn't knowable
     * from the survey definition alone.
     *
     * $selectedColumns may also still list a ranking question's old, static
     * per-subquestion fieldmap entries (fieldname suffix "_S{sqid}") alongside
     * its base field — e.g. the remote-control API's export_responses()
     * defaults $aFields to every createFieldMap() key when none is given.
     * Those entries have no DB column of their own (only the base "Q{qid}"
     * JSON column exists) and are superseded by the dynamic expansion below,
     * so they are dropped here rather than kept, to avoid duplicate output
     * columns for the same ranking question.
     *
     * Non-ranking columns are left untouched.
     *
     * @param SurveyDao $surveyDao
     * @param SurveyObj $survey
     * @param string[] $selectedColumns
     * @param string $sLanguageCode
     * @param string|array $sFilter
     * @param string $completionState
     * @param int $minRecord
     * @param int $maxRecord
     * @param string|null $responsesId
     * @return string[]
     */
    private function expandRankingColumns(
        SurveyDao $surveyDao,
        SurveyObj $survey,
        array $selectedColumns,
        $sLanguageCode,
        $sFilter,
        $completionState,
        $minRecord,
        $maxRecord,
        $responsesId
    ) {
        $expanded = [];
        foreach ($selectedColumns as $column) {
            $field = $survey->fieldMap[$column] ?? null;
            if ($field === null || $field['type'] !== Question::QT_R_RANKING) {
                $expanded[] = $column;
                continue;
            }
            if ($field['suffix'] !== '') {
                // Superseded static per-subquestion entry; skip (see docblock).
                continue;
            }

            $qid = $field['qid'];
            // Upper bound: a response can never rank more items than the question defines.
            $itemCount = count(getSubQuestions($survey->id, $qid, $sLanguageCode));
            $usedRankCount = $this->getMaxRankedItemCount($surveyDao, $survey, $qid, $sFilter, $completionState, $minRecord, $maxRecord, $responsesId);
            $columnCount = $usedRankCount > 0 ? min($usedRankCount, $itemCount) : $itemCount;

            for ($position = 1; $position <= $columnCount; $position++) {
                $rankFieldName = "Q{$qid}_rank{$position}";
                $survey->fieldMap[$rankFieldName] = [
                    'fieldname' => $rankFieldName,
                    'type' => Question::QT_R_RANKING,
                    'sid' => $survey->id,
                    'gid' => $field['gid'],
                    'qid' => $qid,
                    'aid' => $position,
                    'suffix' => "_rank{$position}",
                    'title' => $field['title'] ?? '',
                    'question' => $field['question'] ?? '',
                    'subquestion' => sprintf(gT('Rank %s'), $position),
                    'group_name' => $field['group_name'] ?? '',
                    'mandatory' => $field['mandatory'] ?? 'N',
                    'encrypted' => $field['encrypted'] ?? 'N',
                ];
                $expanded[] = $rankFieldName;
            }
        }
        return $expanded;
    }

    /**
     * Finds the highest number of ranked items actually present across the
     * responses being exported, by decoding the ranking question's raw JSON
     * column for every matching row. Used to size the exported rank-position
     * columns to what the data actually contains.
     *
     * @param SurveyDao $surveyDao
     * @param SurveyObj $survey
     * @param int $qid
     * @param string|array $sFilter
     * @param string $completionState
     * @param int $minRecord
     * @param int $maxRecord
     * @param string|null $responsesId
     * @return int
     */
    private function getMaxRankedItemCount(
        SurveyDao $surveyDao,
        SurveyObj $survey,
        $qid,
        $sFilter,
        $completionState,
        $minRecord,
        $maxRecord,
        $responsesId
    ) {
        $baseField = "Q{$qid}";
        $command = $surveyDao->loadSurveyResults($survey, $minRecord, $maxRecord, $sFilter, $completionState, [$baseField], $responsesId);
        $command->order = false;
        $maxCount = 0;
        foreach ($command->queryColumn() as $rawValue) {
            $rankedCodes = $this->decodeRankedCodes($rawValue);
            if ($rankedCodes !== null) {
                $maxCount = max($maxCount, count($rankedCodes));
            }
        }
        return $maxCount;
    }

    /**
     * Decodes a ranking question's raw JSON-array column value into a plain
     * list of subquestion codes, or null if it isn't one.
     *
     * Decodes without the "assoc" flag so a JSON object (e.g. "{...}") always
     * comes back as a stdClass and fails the is_array() check below, rather
     * than risking being coerced into something that looks like a sequential
     * array. Only a non-empty array whose every element is a string is
     * accepted; anything else (a non-string/empty raw value, invalid JSON, an
     * object, an empty array, or an array containing a non-string element,
     * including nested arrays/objects) returns null so it can never reach
     * ranking or question-title resolution downstream.
     *
     * @param mixed $rawValue
     * @return string[]|null
     */
    private function decodeRankedCodes($rawValue)
    {
        if (!is_string($rawValue) || $rawValue === '') {
            return null;
        }
        $decoded = json_decode($rawValue);
        if (!is_array($decoded) || $decoded === []) {
            return null;
        }
        foreach ($decoded as $code) {
            if (!is_string($code)) {
                return null;
            }
        }
        return $decoded;
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
