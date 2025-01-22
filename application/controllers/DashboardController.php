<?php

/**
 * Class IndexController
 */
class DashboardController extends LSBaseController
{
    /**
     * responses constructor.
     * @param $controller
     * @param $id
     */
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        App()->loadHelper('surveytranslator');
    }

    /**
     * Set filters for all actions
     * @return string[]
     */
    public function filters()
    {
        return [];
    }

    /**
     * this is part of _renderWrappedTemplate implement in old responses.php
     *
     * @param string $view
     * @return bool
     */
    public function beforeRender($view)
    {
        $this->layout = 'with_sidebar';

        return parent::beforeRender($view);
    }

    /**
     * View the dashboard index/index
     */
    public function actionView(): void
    {
        $aData = $this->getData();
        $this->render('welcome', $aData);
    }

    /**
     * Used to get responses data for browse etc
     *
     * @param int|null $surveyId
     * @param int|null $responseId
     * @param string|null $language
     * @return array
     */
    private function getData(): array
    {
        $aData = [];
        $aData['issuperadmin'] = false;
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $aData['issuperadmin'] = true;
        }
        // display old dashboard interface
        $aData['oldDashboard'] = App()->getConfig('display_old_dashboard') === '1';
        $setting_entry = 'last_survey_' . App()->user->getId();
        $lastsurvey = App()->getConfig($setting_entry);
        if ($lastsurvey) {
            try {
                $survey = Survey::model()->findByPk($lastsurvey);
                if ($survey) {
                    $aData['showLastSurvey'] = true;
                    $iSurveyID = $lastsurvey;
                    $aData['surveyTitle'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
                    $aData['surveyUrl'] = $this->createUrl("surveyAdministration/view/surveyid/{$iSurveyID}");
                } else {
                    $aData['showLastSurvey'] = false;
                }
            } catch (Exception $e) {
                $aData['showLastSurvey'] = false;
            }
        } else {
            $aData['showLastSurvey'] = false;
        }

        // We get the last question visited by user
        $setting_entry = 'last_question_' . App()->user->getId();
        $lastquestion = App()->getConfig($setting_entry);

        // the question group of this question
        $setting_entry = 'last_question_gid_' . App()->user->getId();
        $lastquestiongroup = App()->getConfig($setting_entry);

        // the sid of this question : last_question_sid_1
        $setting_entry = 'last_question_sid_' . App()->user->getId();
        $lastquestionsid = App()->getConfig($setting_entry);

        if ($lastquestion && $lastquestiongroup && $lastquestionsid) {
            $survey = Survey::model()->findByPk($lastquestionsid);
            if ($survey) {
                $baselang = $survey->language;
                $aData['showLastQuestion'] = true;
                $qid = $lastquestion;
                $gid = $lastquestiongroup;
                $sid = $lastquestionsid;
                $qrrow = Question::model()->findByAttributes(['qid' => $qid, 'gid' => $gid, 'sid' => $sid]);
                if ($qrrow) {
                    $aData['last_question_name'] = $qrrow['title'];
                    if (!empty($qrrow->questionl10ns[$baselang]['question'])) {
                        $aData['last_question_name'] .= ' : ' . $qrrow->questionl10ns[$baselang]['question'];
                    }
                    $aData['last_question_link'] = $this->createUrl("questionAdministration/view/surveyid/$sid/gid/$gid/qid/$qid");
                } else {
                    $aData['showLastQuestion'] = false;
                }
            } else {
                $aData['showLastQuestion'] = false;
            }
        } else {
            $aData['showLastQuestion'] = false;
        }

        $aData['countSurveyList'] = Survey::model()->count();

        //show banner after welcome logo
        $event = new PluginEvent('beforeWelcomePageRender');
        App()->getPluginManager()->dispatchEvent($event);
        $belowLogoHtml = $event->get('html');

        // We get the home page display setting
        $aData['bShowSurveyList'] = (App()->getConfig('show_survey_list') == "show");
        $aData['bShowSurveyListSearch'] = (App()->getConfig('show_survey_list_search') == "show");
        $aData['bShowLogo'] = (App()->getConfig('show_logo') == "show");
        $aData['oSurveySearch'] = new Survey('search');
        $aData['bShowLastSurveyAndQuestion'] = (App()->getConfig('show_last_survey_and_question') == "show");
        $aData['iBoxesByRow'] = (int)App()->getConfig('boxes_by_row');
        $aData['sBoxesOffSet'] = (int)App()->getConfig('boxes_offset');
        $aData['bBoxesInContainer'] = (App()->getConfig('boxes_in_container') == 'yes');
        $aData['belowLogoHtml'] = $belowLogoHtml;

        return $aData;
    }


    public function getSurveyCounts()
    {
        // Active Surveys
        $activeSurveysCount = Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('{{surveys}}')
            ->where('active = :active', [':active' => 'Y'])
            ->queryScalar();

        // Draft Surveys
        $draftSurveysCount = Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('{{surveys}}')
            ->where('active = :active AND expires IS NULL', [':active' => 'N'])
            ->queryScalar();

        // Closed Surveys
        $closedSurveysCount = Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('{{surveys}}')
            ->where('active = :active AND expires IS NOT NULL', [':active' => 'N'])
            ->queryScalar();

        // All surveys
        $allSurveysCount = Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('{{surveys}}')

            ->queryScalar();

        return [
            'active' => $activeSurveysCount,
            'draft' => $draftSurveysCount,
            'closed' => $closedSurveysCount,
            'all' => $allSurveysCount
        ];
    }


    public function getActiveSurveys()
    {
        // Query to get all active surveys with their titles
        $surveys = Yii::app()->db->createCommand()
            ->select(['s.sid', 'sl.surveyls_title'])  // Select survey id and title
            ->from('{{surveys}} s')
            ->join('{{surveys_languagesettings}} sl', 's.sid = sl.surveyls_survey_id') // Join with the surveys_languagesettings table
            ->where('s.active = :active AND sl.surveyls_language = :language', [':active' => 'Y', ':language' => 'en'])  // Only active surveys in English
            ->queryAll();

        return $surveys;
    }

    public function getFirstFiveActiveSurveysWithResponses()
    {
        // Query to get the first 5 active surveys
        $activeSurveys = Yii::app()->db->createCommand()
            ->select(['sid', 'datecreated'])
            ->from('{{surveys}}')
            ->where('active = :active', [':active' => 'Y'])
            ->limit(5)
            ->queryAll();

        $results = [];
        foreach ($activeSurveys as $survey) {
            // Query to get the survey title from the survey_languagesettings table
            $surveyTitle = Yii::app()->db->createCommand()
                ->select('surveyls_title')
                ->from('{{surveys_languagesettings}}')
                ->where('surveyls_survey_id = :sid AND surveyls_language = :language', [':sid' => $survey['sid'], ':language' => 'en'])  // Assuming English ('en') language
                ->queryScalar();

            // Construct the response table name for each survey
            $responseTable = '{{survey_' . $survey['sid'] . '}}';

            try {
                // Try to get the response count for the survey from its specific response table
                $responseCount = Yii::app()->db->createCommand()
                    ->select('COUNT(*)')
                    ->from($responseTable)
                    ->queryScalar();
            } catch (Exception $e) {
                // In case the table doesn't exist or any error occurs, set response count to 0
                $responseCount = 0;
            }

            // Add the survey details to the result
            $results[] = [
                'survey_id' => $survey['sid'],
                'survey_title' => $surveyTitle,
                'response_count' => $responseCount,
                'date_created' => $survey['datecreated'],
            ];
        }

        return $results;
    }


    // public function countAllSurveys()
    // {
    //     $surveysCount = Yii::app()->db->createCommand()
    //         ->select('COUNT(*)')
    //         ->from('{{surveys}}')
    //         ->queryScalar();

    //     return  $surveysCount;
    // }

    public function getSurveyList()
    {
        // Query to get all active surveys with their titles
        $surveys = Yii::app()->db->createCommand()
            ->select(['s.sid', 'sl.surveyls_title'])  // Select survey id and title
            ->from('{{surveys}} s')
            ->join('{{surveys_languagesettings}} sl', 's.sid = sl.surveyls_survey_id') // Join with the surveys_languagesettings table
            ->where('sl.surveyls_language = :language', [':language' => 'en'])  // Only active surveys in English
            ->queryAll();

        return $surveys;
    }


    public function getSurveyResponseTrends($sid)
    {

        // Construct the table name dynamically
        $responseTable = '{{survey_' . (int)$sid . '}}';

        try {
            // Query to get response trends
            $data = Yii::app()->db->createCommand()
                ->select([
                    "DATE_FORMAT(submitdate, '%Y-%m-%d') AS response_date",
                    "COUNT(*) AS response_count"
                ])
                ->from($responseTable)
                ->where('submitdate IS NOT NULL') // Filter only submitted responses
                ->group('response_date')
                ->order('response_date ASC')
                ->queryAll();

            // Log the result for debugging
            Yii::log('Response trends data: ' . print_r($data, true), 'info');

            return $data;
        } catch (Exception $e) {
            // Log the error for debugging
            Yii::log('Error fetching response trends: ' . $e->getMessage(), 'error');
            return [];
        }
    }


    public function actionGetSurveyResponseTrends()
    {

        $rawPost = file_get_contents('php://input');
        Yii::log('Raw POST data: ' . $rawPost, 'info'); // Debugging

        $decodedPost = json_decode($rawPost, true);
        Yii::log('Decoded POST data: ' . print_r($decodedPost, true), 'info');

        $surveyId = isset($decodedPost['surveyid']) ? $decodedPost['surveyid'] : null;
        Yii::log('Survey ID: ' . $surveyId, 'info');

        // Fetch the specific POST parameter 'surveyid'
        $surveyId = Yii::app()->request->getPost('surveyid', null);
        Yii::log('Survey ID in widget: ' . $surveyId, 'info'); // Debugging

        if ($surveyId) {
            $data = $this->getSurveyResponseTrends($surveyId);
            echo json_encode($data);
        } else {
            Yii::log('No survey ID received', 'error'); // Error Logging
            echo json_encode(['error' => 'No survey ID provided']);
        }
        Yii::app()->end();
    }


    public function getRecentActivitySummary()
    {
        $recentActivities = [];

        // Fetch active surveys and their recent responses
        $surveyIds = Yii::app()->db->createCommand("
            SELECT sid, surveyls_title
            FROM surveys
            JOIN surveys_languagesettings 
              ON surveys.sid = surveys_languagesettings.surveyls_survey_id
            WHERE active = 'Y'
        ")->queryAll();

        foreach ($surveyIds as $survey) {
            $surveyId = $survey['sid'];
            $surveyTitle = $survey['surveyls_title'];

            // Count new responses
            $responseCount = Yii::app()->db->createCommand("
                SELECT COUNT(*) AS response_count 
                FROM {{survey_$surveyId}}
                WHERE submitdate IS NOT NULL
            ")->queryScalar();

            $recentActivities[] = [
                'type' => 'survey_response',
                'message' => "Survey \"$surveyTitle\" received $responseCount new responses.",
            ];
        }

        // Fetch recently edited drafts
        $editedDrafts = Yii::app()->db->createCommand("
            SELECT surveyls_title
            FROM surveys
            JOIN surveys_languagesettings 
              ON surveys.sid = surveys_languagesettings.surveyls_survey_id
            WHERE active = 'N'
           
            LIMIT 5
        ")->queryAll();

        foreach ($editedDrafts as $draft) {
            $recentActivities[] = [
                'type' => 'draft_edited',
                'message' => "survey \"{$draft['surveyls_title']}\" was edited.",
            ];
        }

        // Fetch new user creation actions
        $newUsers = Yii::app()->db->createCommand("
            SELECT users_name, created
            FROM users
            ORDER BY created DESC
            LIMIT 5
        ")->queryAll();

        foreach ($newUsers as $user) {
            $recentActivities[] = [
                'type' => 'user_creation',
                'message' => "new user \"{$user['users_name']}\" created on {$user['created']}.",
            ];
        }

        return $recentActivities;
    }
}
