<?php

namespace LimeSurvey\Api\Command\V1;

use CHttpSession;
use Survey;
use SurveyLanguageSetting;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
    ResponseData\ResponseDataError
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

/**
 * Survey Template
 *
 * Used by cloud / account to retrieve templates.
 */
class SurveyTemplate implements CommandInterface
{
    use AuthPermissionTrait;

    protected CHttpSession $session;
    protected ResponseFactory $responseFactory;

    protected Survey $survey;
    protected SurveyLanguageSetting $surveyLanguageSetting;
    protected string $language = "en";

    /**
     * Constructor
     *
     * @param ResponseFactory $responseFactory
     * @param Survey $survey
     * @param SurveyLanguageSetting $surveyLanguageSetting
     */
    public function __construct(
        ResponseFactory $responseFactory,
        CHttpSession $session,
        Survey $survey,
        SurveyLanguageSetting $surveyLanguageSetting
    ) {
        $this->responseFactory = $responseFactory;
        $this->session = $session;
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
        $surveyId = (int)$request->getData('_id');

        if ($response = $this->ensurePermissions($surveyId)) {
            return $response;
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

        $response = $this->buildLanguageSettings($survey);
        $result = $this->getTemplateData($surveyId, $this->language);

        return $this->responseFactory->makeSuccess(
            array_merge($response, ['template' => $result])
        );
    }

    private function buildLanguageSettings(Survey $survey): array
    {
        $this->language = ((\Yii::app()->request->getParam('lang') ?? $survey->language) ?? 'en');
        $languageSettings = $this
            ->surveyLanguageSetting
            ->find('surveyls_survey_id = :sid and surveyls_language = :language', [
                ':sid'      => $survey->sid,
                ':language' => $this->language
            ]);
        $response = [];
        if ($languageSettings) {
            $response['title'] = $languageSettings->surveyls_title;
            $response['subtitle'] = $languageSettings->surveyls_description;
        }
        return $response;
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

    /**
     * Get template data
     *
     * @param int $surveyId
     * @param string $language
     * @return Response|bool|string
     */
    private function getTemplateData($surveyId, $language)
    {
        // @todo This shouldnt require a HTTP request we should be able to
        // - render survey content internally. To handle this correctly
        // - we should refactor the survey view functionality to make it
        // - reusable (move it out of the controllers).

        $strCookie = $this->session->getSessionName()
        . '=' . $this->session->getSessionID() . '; path=/';
        $this->session->close();

        $ch = curl_init();
        $root = (
            !empty($_SERVER['HTTPS'])
            ? 'https'
            : 'http'
        ) . '://' . ($_SERVER['HTTP_HOST'] ?? '');
        curl_setopt(
            $ch,
            CURLOPT_URL,
            $root . "/{$surveyId}?newtest=Y&lang={$language}&popuppreview=true"
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIE, $strCookie);
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
        return $result;
    }
}
