<?php

namespace LimeSurvey\Api\Command\V1;

use CHttpSession;
use Survey;
use Token;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
    ResponseData\ResponseDataError
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class SurveyArchive implements CommandInterface
{
    use AuthPermissionTrait;

    protected CHttpSession $session;

    protected ResponseFactory $responseFactory;

    protected Survey $survey;

    /**
     * Constructor
     * @param \CHttpSession $session
     * @param \LimeSurvey\Api\Command\Response\ResponseFactory $responseFactory
     * @param \Survey $survey
     */
    public function __construct(
        CHttpSession $session,
        ResponseFactory $responseFactory,
        Survey $survey
    ) {
        $this->session = $session;
        $this->responseFactory = $responseFactory;
        $this->survey = $survey;
    }

    /**
     * Processes data and returns aggregate summary of the archives
     * @param \Survey $survey
     * @param array $rawData
     * @return array
     */
    protected function processData(Survey $survey, array $rawData): array
    {
        $hasTokens = false;
        try {
            $hasTokens = ($survey->isActive && (Token::model($survey->sid)->find('1=1') !== null));
        } catch (\Exception $ex) {
            //Tokens table exists, proceed
        }
        $data = [];
        for ($index = 0; $index < count($rawData); $index++) {
            $newData = ['newformat' => false];
            $split = explode(",", $rawData[$index]['tables']);
            $types = [];
            foreach ($split as $tbl) {
                if (strpos($tbl, 'questions') !== false) {
                    $types[] = 'questions';
                    $newData['newformat'] = true;
                } elseif (strpos($tbl, 'timings') !== false) {
                    $types[] = 'timings';
                } elseif (strpos($tbl, 'tokens') !== false) {
                    $types[] = 'tokens';
                } elseif (strpos($tbl, 'survey') !== false) {
                    $types[] = 'survey';
                }
            }
            $newData['types'] = $types;
            $newData['count'] = $rawData[$index]['cnt'];
            $newData['timestamp'] = $rawData[$index]['timestamp'];
            $newData['hastokens'] = $hasTokens;
            $data[] = $newData;
        }
        if ($survey->isActive) {
            $data[] = [
                'timestamp' => 0,
                'count' => 0,
                'types' => [],
                'hastokens' => $hasTokens
            ];
        }
        return $data;
    }

    /**
     * Processes the request
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @SuppressWarnings(PHPMD.PossiblyInvalidOperand)
     * @SuppressWarnings(PHPMD.InvalidArgument)
     */
    public function run(Request $request)
    {
        $surveyId = (int)$request->getData('_id');
        if (!$surveyId) {
            $surveyId = intval($_GET['id']);
        }
        $rawBaseTable = ($_GET['basetable'] ?? 'survey') . "";
        if (!in_array($rawBaseTable, ['survey', 'tokens'])) {
            throw new \Exception("Incorrect base table");
        }
        $baseTable = "old_{$rawBaseTable}";
        if ($response = $this->ensurePermissions($surveyId)) {
            return $response;
        }
        $survey = Survey::model()->findByPk($surveyId);
        if (!$survey) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'SURVEY_NOT_FOUND',
                    'Survey not found'
                )
                )->toArray()
            );
        }
        require_once "application/helpers/admin/import_helper.php";
        return $this->responseFactory->makeSuccess($this->processData($survey, getTableArchivesAndTimestamps($surveyId, $baseTable)));
    }

    /**
     * Ensure Permissions
     *
     * @param string $authToken
     * @param int $surveyId
     * @return Response|false
     */
    private function ensurePermissions($surveyId)
    {
        if (
            !$this->hasSurveyPermission(
                $surveyId,
                'surveycontent',
                'read'
            )
        ) {
            return $this->responseFactory
                ->makeErrorForbidden();
        }

        if (!$surveyId) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'SURVEY_NOT_FOUND',
                    'Survey not found'
                )
                )->toArray()
            );
        }

        return false;
    }
}
