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
     * looking like rest/v1/survey-template/571271
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
        $language = $request->getData('language', $survey->language);
        $language = $language ?? 'en';
        $languageSettings = $this
            ->surveyLanguageSetting
            ->find('surveyls_survey_id = :sid and surveyls_language = :language', [
                ':sid'      => $surveyId,
                ':language' => $language
            ]);
        $response = [];
        if ($languageSettings) {
            $response['title'] = $languageSettings->surveyls_title;
            $response['subtitle'] = $languageSettings->surveyls_description;
        }
        $result = $this->getTemplateData($surveyId, $language);
        if ($result === false) {
            return $this->responseFactory->makeError(
                (new ResponseDataError(
                    'SURVEY_TEMPLATE_UNAVAILABLE',
                    'Survey template could not be retrieved'
                )
                )->toArray()
            );
        }
        return $this->responseFactory->makeSuccess(
            array_merge($response, ['template' => $result])
        );
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
     * Renders the survey preview by requesting it from this installation
     * over HTTP, using the session of the current user.
     *
     * @param int $surveyId
     * @param string $language
     * @return string|false The rendered survey page, or false on failure
     */
    private function getTemplateData($surveyId, $language)
    {
        // @todo This shouldn't require a HTTP request we should be able to
        // - render survey content internally. To handle this correctly
        // - we should refactor the survey view functionality to make it
        // - reusable (move it out of the controllers).

        // Never request a URL the client can choose (SSRF)
        $root = $this->getTrustedBaseUrl();
        if ($root === null) {
            return false;
        }

        $strCookie = $this->session->getSessionName()
        . '=' . $this->session->getSessionID() . '; path=/';
        $this->session->close();

        $ch = curl_init();
        curl_setopt(
            $ch,
            CURLOPT_URL,
            $root . '/' . $surveyId . '?' . http_build_query([
                'newtest' => 'Y',
                'lang' => $language,
                'popuppreview' => 'true',
            ])
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIE, $strCookie);
        $result = curl_exec($ch);
        if (curl_errno($ch) || !is_string($result)) {
            return false;
        }
        return $result;
    }

    /**
     * Get the base URL for the internal survey preview request
     *
     * The configured publicurl is used when it is set. Otherwise the URL of
     * the current request is used: its hostname was already checked against
     * the allowed hosts list, but its port comes from the client's Host
     * header, so only the default port of the scheme is accepted.
     *
     * @return string|null The base URL without trailing slash, or null if no trusted base URL is available
     */
    protected function getTrustedBaseUrl(): ?string
    {
        $defaultPorts = ['http' => 80, 'https' => 443];

        $publicUrl = trim((string) App()->getConfig('publicurl'));
        $parsedPublicUrl = parse_url($publicUrl);
        if (!empty($parsedPublicUrl['scheme']) && !empty($parsedPublicUrl['host'])) {
            if (!isset($defaultPorts[strtolower($parsedPublicUrl['scheme'])])) {
                return null;
            }
            return rtrim($publicUrl, '/');
        }

        $baseUrl = App()->getBaseUrl(true);
        $parsedBaseUrl = parse_url($baseUrl);
        if (empty($parsedBaseUrl['scheme']) || empty($parsedBaseUrl['host'])) {
            return null;
        }
        $scheme = strtolower($parsedBaseUrl['scheme']);
        if (!isset($defaultPorts[$scheme])) {
            return null;
        }
        if (isset($parsedBaseUrl['port']) && $parsedBaseUrl['port'] !== $defaultPorts[$scheme]) {
            return null;
        }
        return rtrim($baseUrl, '/');
    }
}
