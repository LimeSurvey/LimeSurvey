<?php

/**
 * SearchBoxWidget is a custom Yii widget used to render a search box with filtering capabilities.
 * It supports different view types and can switch between them based on user preferences or query parameters.
 */
class SearchBoxWidget extends CWidget
{
    /**
     * @var string $formUrl The URL to which the form will be submitted. Defaults to 'dashboard/view'.
     */
    public string $formUrl = 'dashboard/view';

    /**
     * @var CActiveRecord $model The model associated with the search form.
     */
    public CActiveRecord $model;

    /**
     * @var bool $onlyfilter If true, only the filter section of the widget is rendered.
     */
    public bool $onlyfilter = false;

    /**
     * @var string|null $viewtype The type of view widget (list-widget or box-widget) to render.
     * Can be set via query parameter, user settings, or defaults to 'list-widget'.
     */
    public ?string $viewtype = '';

    /**
     * @var bool $switch If true, the view type selection is saved to user settings.
     */
    public bool $switch = false;

    public $pageSize;

    /**
     * Runs the widget, rendering the appropriate view based on the viewtype and switch properties.
     * It determines the viewtype from the query parameters, user settings, or defaults.
     *
     * @throws CException If an error occurs during rendering.
     */
    public function run()
    {
        $this->formUrl = $this->getFormUrl();
        if (App()->request->getQuery('viewtype')) {
            $this->viewtype = App()->request->getQuery('viewtype');
        } elseif (SettingsUser::getUserSettingValue('welcome_page_widget')) {
            $this->viewtype = SettingsUser::getUserSettingValue('welcome_page_widget');
        } else {
            $this->viewtype = 'list-widget';
        }

        if (!empty($this->viewtype) && $this->switch) {
            SettingsUser::setUserSetting('welcome_page_widget', $this->viewtype);
        }

        $this->render('searchBox');
    }

    /**
     * Initializes the widget by registering necessary client scripts.
     */
    public function init(): void
    {
        $this->registerClientScript();
    }

    /**
     * Registers the necessary JavaScript files for the widget.
     */
    public function registerClientScript()
    {
        App()->getClientScript()->registerScriptFile(
            App()->getConfig("extensionsurl") . 'admin/SearchBoxWidget/assets/filters.js',
            CClientScript::POS_END
        );
    }

    /**
     * Generates and returns the form URL, handling URL formatting and GET parameters.
     *
     * @return string The generated form URL.
     * @throws CException
     */
    public function getFormUrl(): string
    {
        $url = App()->createAbsoluteUrl(App()->request->getPathInfo());
        if (Yii::app()->getUrlManager()->getUrlFormat() == CUrlManager::GET_FORMAT) {
            // Ignore all GET params (searchbox filters) except the 'r' param.
            return $url . '?' . http_build_query(['r' => App()->request->getParam('r')]);
        }
        return $url;
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
                'message' => "Draft \"{$draft['surveyls_title']}\" was edited.",
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
