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
     * @param Request $request The REST request, with the survey ID in "_id" and an optional "language"
     * @return Response The template, title and subtitle, or an error response
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
     * This runs before the survey lookup on purpose: An unknown survey id
     * gives a 403 as well, so users cannot probe which survey ids exist.
     *
     * @param int $surveyId
     * @return Response|false Forbidden response, or false if access is allowed
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

        return false;
    }

    /**
     * Get template data
     *
     * Renders the survey preview by requesting it from this installation
     * over HTTP, using the session of the current user.
     *
     * @param int $surveyId The survey ID
     * @param string $language The language code to render the survey in
     * @return string|false The rendered survey page, or false on failure
     */
    protected function getTemplateData($surveyId, $language)
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
        if (
            curl_errno($ch)
            || !is_string($result)
            || curl_getinfo($ch, CURLINFO_RESPONSE_CODE) !== 200
        ) {
            return false;
        }
        return $result;
    }

    /**
     * Get the base URL for the internal survey preview request
     *
     * The configured publicurl is used when it is set. Otherwise the URL of
     * the current request is used, which comes from the client's Host
     * header. Its hostname is only checked when an allowed hosts list
     * exists, and its port is never checked, so it is only accepted when an
     * allowed hosts list exists and the port is the default of the scheme.
     *
     * With "Force SSL" on, an http URL is changed to https, because the
     * preview request would only be redirected to https.
     *
     * The URL is rebuilt from its parsed parts, so curl gets exactly the
     * scheme, host, port and path that were checked here.
     *
     * @return string|null The base URL without trailing slash, or null if no trusted base URL is available
     */
    protected function getTrustedBaseUrl(): ?string
    {
        $defaultPorts = ['http' => 80, 'https' => 443];

        $baseUrl = App()->getPublicBaseUrl(true);
        $isPublicUrl = $baseUrl === App()->getConfig('publicurl');

        $parts = parse_url((string) $baseUrl);
        if (
            empty($parts['scheme'])
            || empty($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])
        ) {
            return null;
        }
        $scheme = strtolower($parts['scheme']);
        if (!isset($defaultPorts[$scheme])) {
            return null;
        }
        // Only plain host names, IPv4 addresses and bracketed IPv6 addresses
        if (!preg_match('/^(?:[a-z0-9.-]+|\[[0-9a-f:.]+\])$/i', $parts['host'])) {
            return null;
        }

        if (!$isPublicUrl) {
            if (empty(App()->loadAllowedHosts())) {
                return null;
            }
            if (isset($parts['port']) && $parts['port'] !== $defaultPorts[$scheme]) {
                return null;
            }
        }

        if (
            $scheme === 'http'
            && App()->getConfig('ssl_emergency_override') !== true
            && strtolower((string) App()->getConfig('force_ssl')) === 'on'
        ) {
            $scheme = 'https';
            if (isset($parts['port']) && $parts['port'] === $defaultPorts['http']) {
                unset($parts['port']);
            }
        }

        return $scheme . '://' . $parts['host']
            . (isset($parts['port']) ? ':' . $parts['port'] : '')
            . rtrim($parts['path'] ?? '', '/');
    }
}
