<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use SurveyLanguageSetting;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
    ResponseData\ResponseDataError
};

class SurveyTemplate implements CommandInterface
{
    protected ResponseFactory $responseFactory;

    protected Survey $survey;
    protected SurveyLanguageSetting $surveyLanguageSetting;

    /**
     * Constructor
     *
     * @param ResponseFactory $responseFactory
     * @param Survey $survey
     * @param SurveyLanguageSetting $surveyLanguageSetting
     */
    public function __construct(
        ResponseFactory $responseFactory,
        Survey $survey,
        SurveyLanguageSetting $surveyLanguageSetting
    ) {
        $this->responseFactory = $responseFactory;
        $this->survey = $survey;
        $this->surveyLanguageSetting = $surveyLanguageSetting;
    }
    /**
     * Run survey template command
     *
     * Supports GET and POST, with the sid at the end of the endpoint,
     * lookin like rest/v1/survey-template/571271
     *
     * If it's a GET request, then language is not specified, so it is inferred from the survey's default language and falling back to en if not found
     *
     * If it's a POST, language can be specified like this:
     * {
     *     "language": "en",
     * }
     *
     * Responds with an object, like this:
     * {
     *     "template": "<some HTML>"
     *     "title": "Lunch"
     *     "subtitle": "What should we eat for lunch?"
     * }
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $surveyId = intval($request->getData('_id'));
        if (!$surveyId) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'SURVEY_NOT_FOUND',
                    'Survey not found'
                )
                )->toArray()
            );
        }
        $survey = $this->survey->findByPk($surveyId);
        if (!$survey) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'SURVEY_NOT_FOUND',
                    'Survey not found'
                )
                )->toArray()
            );
        }
        $language = (($request->getData('language') ?? $survey->language) ?? 'en');
        $languageSettings = $this
            ->surveyLanguageSetting
            ->find('surveyls_survey_id = :sid and surveyls_language = :language', [
            ':sid' => $surveyId,
            ':language' => $language
        ]);
        $response = [];
        if ($languageSettings) {
            $response['title'] = $languageSettings->surveyls_title;
            $response['subtitle'] = $languageSettings->surveyls_description;
        }
        $ch = curl_init();
        $root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
        curl_setopt($ch, CURLOPT_URL, $root . "/{$surveyId}?newtest=Y&lang={$language}&popuppreview=true");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'SURVEY_NOT_FOUND',
                    'Survey not found'
                )
                )->toArray()
            );
        }
        curl_close(($ch));
        return $this->responseFactory->makeSuccess(array_merge($response, ['template' => $result]));
    }
}
