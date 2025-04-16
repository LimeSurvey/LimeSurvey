<?php

namespace LimeSurvey\Api\Command\V1;

use CHttpSession;
use Survey;
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
     * Processes the request
     * @param \LimeSurvey\Api\Command\Request\Request $request
     */
    public function run(Request $request)
    {
        $surveyId = (int)$request->getData('_id');
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
        $rawData = getTableArchivesAndTimestamps($surveyId);
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
            $data[] = $newData;
        }
        return $this->responseFactory->makeSuccess($data);
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
