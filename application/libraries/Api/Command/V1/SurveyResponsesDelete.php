<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use Survey;
use LimeSurvey\Api\Command\{CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
    ResponseData\ResponseDataError};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class SurveyResponsesDelete implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param Survey $survey
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        Survey $survey,
        ResponseFactory $responseFactory
    ) {
        $this->survey = $survey;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run session key release command.
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $data = [];
        $sid = (string) $request->getData('_id');
        $rids = (array) $request->getData('rid', []);

        if (!is_numeric($sid)) {
            return $this->responseFactory->makeError(
                (new ResponseDataError('INVALID_SURVEY_ID','Invalid survey ID'))
                ->toArray());
        }

        try {
            $responseModel = \Response::model($sid);
        } catch (\Exception $e) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError('SURVEY_NOT_FOUND','Survey not found'))
                    ->toArray()
            );
        }

        if ($rids) {
            foreach ($rids as $rid) {
                $response = $responseModel->findByPk($rid);

                $data[$rid]['responseId'] = $rid;
                if (!$response) {
                    $data[$rid]['deleted'] = false;
                    $data[$rid]['error'] = (new ResponseDataError('RESPONSE_NOT_FOUND','Response not found'))
                        ->toArray()['error'];
                } else {
                    $isDeleted = $response->delete(true);
                    if (!$isDeleted) {
                        $data[$rid]['error'] = (new ResponseDataError('RESPONSE_NOT_DELETED','Response could not be deleted'))
                            ->toArray()['error'];
                    }
                    $data[$rid]['deleted'] = $isDeleted;
                }
            }
        }

        return $this->responseFactory->makeSuccess(['responses' => [
            'surveyId' => $sid,
            'data' => $data
        ]]);
    }
}
