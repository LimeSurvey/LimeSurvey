<?php

namespace LimeSurvey\Models\Services;

use CException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LSYii_Application;
use Permission;
use Survey;
use SurveyActivator;

class SurveyActivate
{
    private Survey $survey;
    private Permission $permission;
    private SurveyActivator $surveyActivator;
    private LSYii_Application $app;

    public function __construct(
        Survey $survey,
        Permission $permission,
        SurveyActivator $surveyActivator,
        LSYii_Application $app
    ) {
        $this->survey = $survey;
        $this->permission = $permission;
        $this->surveyActivator = $surveyActivator;
        $this->app = $app;
    }

    /**
     * @param int $surveyId
     * @param array $params
     * @param bool $force
     * @return array
     * @throws PermissionDeniedException
     * @throws CException
     */
    public function activate(int $surveyId, array $params = [], bool $force = false): array
    {
        if ((!$force) && (!$this->permission->hasSurveyPermission($surveyId, 'surveyactivation', 'update'))) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }

        if (!is_array($params)) {
            $params = [];
        }

        $survey = $this->survey->findByPk($surveyId);
        $aData['oSurvey'] = $survey;
        $aData['sidemenu']['state'] = false;
        $aData['aSurveysettings'] = getSurveyInfo($surveyId);
        $aData['surveyid'] = $surveyId;

        if (!is_null($survey)) {
            $fields = [
                'anonymized',
                'datestamp',
                'ipaddr',
                'ipanonymize',
                'refurl',
                'savetimings'
            ];
            $notNullable = [
                'anonymized',
                'savetimings',
                'datestamp',
                'ipaddr',
                'ipanonymize',
                'refurl'
            ];
            foreach ($fields as $field) {
                if ((!in_array($field, $notNullable)) || ($fielvalue !== null)) {
                    $survey->{$field} = $this->app->request->getPost($field, $params[$field] ?? null);
                }
            }
            $survey->save();

            // Make sure the saved values will be picked up
            $this->survey->resetCache();
            $survey->setOptions();
        }

        $result = $this->surveyActivator->setSurvey($survey)->activate();
        if ($params['restore'] ?? false) {
            $result['restored'] = $this->restoreData($surveyId);
        }
        return $result;
    }

    /**
     * Restores all archived data tables
     *
     * @param int $surveyId
     * @param int|null $timestamp
     * @param bool $preserveIDs
     * @return bool
     * @throws CException
     */
    public function restoreData(int $surveyId, $timestamp = null, $preserveIDs = false): bool
    {
        require_once "application/helpers/admin/import_helper.php";
        $deactivatedArchives = getDeactivatedArchives($surveyId);
        $archives = [];
        foreach ($deactivatedArchives as $key => $deactivatedArchive) {
            $candidates = explode(",", $deactivatedArchive);
            sort($candidates);
            $found = false;
            if ($timestamp) {
                foreach ($candidates as $candidate) {
                    if (!$found) {
                        $exploded = explode("_", $candidate);
                        if ($exploded[count($exploded) - 1] == $timestamp) {
                            $found = true;
                            $archives[$key] = $candidate;
                        }
                    }
                }
            }
            if (!$found) {
                $archives[$key] = $candidates[count($candidates) - 1];
            }
        }
        if (is_array($archives) && isset($archives['survey']) && isset($archives['questions'])) {
            //Recover survey
            $qParts = explode("_", $archives['questions']);
            $qTimestamp = $qParts[count($qParts) - 1];
            $sParts = explode("_", $archives['survey']);
            $sTimestamp = $sParts[count($sParts) - 1];
            $dynamicColumns = getUnchangedColumns($surveyId, $sTimestamp, $qTimestamp);
            $this->app->recoverSurveyResponses($surveyId, $archives["survey"], $preserveIDs, $dynamicColumns);
            if (isset($archives["tokens"])) {
                $tokenTable = $this->app->db->tablePrefix . "tokens_" . $surveyId;
                createTableFromPattern($tokenTable, $archives["tokens"]);
                $this->app->copyFromOneTableToTheOther($archives["tokens"], $tokenTable);
            }
            if (isset($archives["timings"])) {
                $timingsTable = $this->app->db->tablePrefix . "survey_" . $surveyId . "_timings";
                $this->app->copyFromOneTableToTheOther($archives["timings"], $timingsTable);
            }
            return true;
        } else {
            return false; //not recoverable
        }
    }
}
