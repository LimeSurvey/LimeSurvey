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
use LimeSurvey\Models\Services\embeds\BaseEmbed;

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
    protected int $surveyId = -1;
    protected bool $isPreview = true;
    protected bool $js = false;
    protected string $language = "en";
    protected bool $fillToken = false;
    protected string $token = "";
    const ENDPOINT = "/index.php/rest/v1/survey-template/";
    /**
     * @psalm-suppress UndefinedClass
     * @psalm-suppress PropertyNotSetInConstructor
    */
    protected BaseEmbed $embed;

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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @psalm-suppress UndefinedClass
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $this->surveyId = (int)$request->getData('_id');
        $this->isPreview = $this->isPreview && (\Yii::app()->request->getParam('popuppreview', 'true') === 'true');
        $this->js = $this->js || (\Yii::app()->request->getParam('js', 'false') === 'true');
        $this->fillToken = (\Yii::app()->request->getParam('filltoken', 'false') === 'true');
        $this->token = \Yii::app()->request->getParam('LSEMBED-token', '');
        $target = \Yii::app()->request->getParam('target', 'marketing');
        $embedType = \Yii::app()->request->getParam('embed', BaseEmbed::EMBED_STRUCTURE_STANDARD);
        $embedOptions = \Yii::app()->request->getParam('embedOptions', []);
        $renderOnlyEmbedTypes = [BaseEmbed::EMBED_STRUCTURE_EMAIL, BaseEmbed::EMBED_STRUCTURE_BUTTON];

        if (in_array($embedType, $renderOnlyEmbedTypes)) {
            $embedOptions['surveyId'] = $this->surveyId;
        }

        $this->embed = BaseEmbed::instantiate($embedType)
                        ->setEmbedOptions($embedOptions);

        if ($response = $this->ensurePermissions()) {
            return $response;
        }

        $survey = $this->survey->findByPk($this->surveyId);
        if (!$survey) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    'SURVEY_NOT_FOUND',
                    'Survey not found'
                )
                )->toArray()
            );
        }
        $this->language = ((\Yii::app()->request->getParam('lang') ?? $survey->language) ?? 'en');
        $languageSettings = $this
            ->surveyLanguageSetting
            ->find('surveyls_survey_id = :sid and surveyls_language = :language', [
                ':sid'      => $this->surveyId,
                ':language' => $this->language
            ]);
        $response = [];
        if ($languageSettings) {
            $response['title'] = $languageSettings->surveyls_title;
            $response['subtitle'] = $languageSettings->surveyls_description;
        }
        if (!in_array($embedType, $renderOnlyEmbedTypes)) {
            $structure = '';
            if ($this->js) {
                $structure = $this->getJavascript($embedType, $this->isPreview);
                if (!$this->isPreview) {
                    $this->embed->setStructure($structure);
                }
            }
            if ($this->isPreview) {
                $result = $this->getTemplateData();
                if (is_string($result)) {
                    $structure .= " {$result}";
                }
                $this->embed->displayWrapper($target !== 'marketing')->setStructure($structure);
            } elseif (!$this->js) {
                $surveyResult = $this->getSurveyResult();
                $this->embed->displayWrapper(false)->setStructure($surveyResult['form']);
                $response['hiddenInputs'] = $surveyResult['hiddenInputs'];
                $response['head'] = $surveyResult['head'];
                $response['beginScripts'] = $surveyResult['beginScripts'];
                $response['bottomScripts'] = $surveyResult['bottomScripts'];
            }
        }

        return $this->responseFactory->makeSuccess(
            array_merge($response, ['template' => $this->embed->render()])
        );
    }

    /**
     * Ensure Permissions
     *
     * @param string $authToken
     * @return Response|false
     */
    private function ensurePermissions()
    {
        if (
            !$this->hasSurveyPermission(
                $this->surveyId,
                'surveycontent',
                'read'
            )
        ) {
            return $this->responseFactory
                ->makeErrorForbidden();
        }

        if (!$this->surveyId) {
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
     * @return Response|bool|string
     */
    private function getTemplateData()
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
            $root . "/{$this->surveyId}?newtest=Y&lang={$this->language}&popuppreview=true"
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

    /**
     * Returns the root URL
     * @return string
     */
    private function getRootUrl()
    {
        return (
            !empty($_SERVER['HTTPS'])
            ? 'https'
            : 'http'
        ) . '://' . ($_SERVER['HTTP_HOST'] ?? '');
    }

    /**
     * Gets the source by survey id and language
     * @return string
     */
    private function getSrc()
    {
        $root = $this->getRootUrl();
        $token = ($this->token ? "&token=" . $this->token : "");
        return $root . "/index.php/{$this->surveyId}?lang={$this->language}" . ($this->fillToken ? "&filltoken=true" : "") . $token;
    }

    /**
     * Returns the survey results
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @psalm-suppress PossiblyInvalidCast
     * @return array{beginScripts: string, bottomScripts: string, form: bool|string, head: string, hiddenInputs: string|array{beginScripts: string, bottomScripts: string, form: string, head: string, hiddenInputs: string}}
     */
    private function getSurveyResult()
    {
        $LEMPostKey = \Yii::app()->request->getPost('LSEMBED-LEMpostKey', false);
        $form = "";
        if (!$LEMPostKey) {
            $curl = "curl -Ss -D - '{$this->getSrc()}' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7' -H 'Accept-Language: en-US,en;q=0.9' -H 'Connection: keep-alive' -H 'Sec-Fetch-Dest: document' -H 'Sec-Fetch-Mode: navigate' -H 'Sec-Fetch-Site: none' -H 'Sec-Fetch-User: ?1' -H 'Upgrade-Insecure-Requests: 1' -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36' -H 'sec-ch-ua: \"Not)A;Brand\";v=\"8\", \"Chromium\";v=\"138\"' -H 'sec-ch-ua-mobile: ?0' -H 'sec-ch-ua-platform: \"Linux\"' --insecure";
            exec($curl, $output, $result_code);
            $result = implode("\n", $output);
            $headerEnd = strpos($result, "<!DOCTYPE");
            $headerCookies = explode(';', substr($result, 0, (int)$headerEnd));
            $cookies = [];
            foreach ($headerCookies as $hc) {
                $prefix = "Set-Cookie: ";
                $prefixPosition = strpos($hc, $prefix);
                if ($prefixPosition !== false) {
                    $cookie = substr($hc, $prefixPosition + strlen($prefix));
                    list($key, $val) = explode("=", $cookie);
                    $cookies[] = "<input type='hidden' name='LSSESSION-{$key}' value='{$val}'>";
                }
            }
            $hiddenInputs = implode(" ", $cookies);
        } else {
            $sessionCookies = [];
            $parameters = [];
            $cookies = [];
            foreach ($_POST as $key => $value) {
                $k = (string) $key;
                $text = (string) $value;
                if (strpos($k, "LSSESSION-") === 0) {
                    $sessionCookies[] = substr($k, strlen("LSSESSION-")) . "=" . $text;
                    $cookies[] = "<input type='hidden' name='{$k}' value='{$text}'>";
                } elseif (strpos($k, "LSEMBED-") === 0) {
                    $parameters[] = substr($k, strlen("LSEMBED-")) . "=" . $text;
                }
            }
            $hiddenInputs = implode(" ", $cookies);
            $sc = implode("; ", $sessionCookies);
            $p = implode("&", $parameters);
            $curl = str_replace("==", "%3D%3D", "curl '{$this->getSrc()}' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7' -H 'Accept-Language: en-US,en;q=0.9' -H 'Cache-Control: max-age=0' -H 'Connection: keep-alive' -H 'Content-Type: application/x-www-form-urlencoded' -b '{$sc}' -H 'Origin: {$this->getRootUrl()}' -H 'Referer: {$this->getSrc()}' -H 'Sec-Fetch-Dest: document' -H 'Sec-Fetch-Mode: navigate' -H 'Sec-Fetch-Site: same-origin' -H 'Sec-Fetch-User: ?1' -H 'Upgrade-Insecure-Requests: 1' -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36' -H 'sec-ch-ua: \"Not)A;Brand\";v=\"8\", \"Chromium\";v=\"138\"' -H 'sec-ch-ua-mobile: ?0' -H 'sec-ch-ua-platform: \"Linux\"' --data-raw '{$p}' --insecure");
            exec($curl, $output, $result_code);
            $result = implode("\n", $output);
        }
        $dom = new \DOMDocument();
        $headerPart = substr($result, $headerEnd ?? 0);
        $nonEmpty = $headerPart . ' ';
        @$dom->loadHTML($nonEmpty);
        $xpath = new \DOMXPath($dom);
        $forms = $xpath->query("//article");
        $form = substr($result, $headerEnd ?? 0);
        foreach ($forms as $f) {
            $form = $dom->saveHTML($f);
        }
        $h = [];
        $heads = $xpath->query("//head/*");
        foreach ($heads as $head) {
            $h[] = $dom->saveHTML($head);
        }
        $h = implode("SEPARATOR", $h);
        $bes = [];
        $beginScripts = $xpath->query("//div[@id='beginScripts']/*");
        foreach ($beginScripts as $beginScript) {
            $bes[] = $dom->saveHTML($beginScript);
        }
        $bes = implode("SEPARATOR", $bes);
        $bos = [];
        $bottomScripts = $xpath->query("//div[@id='bottomScripts']/*");
        foreach ($bottomScripts as $bottomScript) {
            $bos[] = $dom->saveHTML($bottomScript);
        }
        $bos = implode("SEPARATOR", $bos);
        $registration = $xpath->query("//*[@id='register_firstname']");
        $isRegistration = false;
        foreach ($registration as $r) {
            $isRegistration = true;
        }
        return [
            'form' => $isRegistration ?  "<form id='limesurvey' class='register'><a class='register' target='_blank'></a></form>" : $form,
            'hiddenInputs' => $hiddenInputs,
            'head' => $h,
            'beginScripts' => $bes,
            'bottomScripts' => $bos,
        ];
    }

    /**
     * Gets the javascript that does the goodies
     * @param string $embedType
     * @param bool $isPreview
     * @return string
     */
    private function getJavascript(string $embedType, bool $isPreview = false)
    {
        $containerId = '1';
        $lang = $this->language;
        $surveyId = $this->surveyId;
        $rootUrl = $this->getRootUrl();
        $embedScriptUrl = $rootUrl . '/assets/scripts/survey-embed.js';

        $allowedTypes = [BaseEmbed::EMBED_STRUCTURE_POPUP, BaseEmbed::EMBED_STRUCTURE_WIDGET];
        $optionalAttributes = '';
        if ($isPreview) {
            $optionalAttributes .= ' data-is-preview="true"';
        }
        if (in_array($embedType, $allowedTypes, true)) {
            $optionalAttributes .= ' data-embed-type="' . $embedType . '"';
        }

        return <<<HTML
                <script
                    src="{$embedScriptUrl}"
                    data-survey-id="{$surveyId}"
                    data-lang="{$lang}"
                    data-container-id="{$containerId}"
                    data-root-url="{$rootUrl}" {$optionalAttributes}>
                </script>
            HTML;
    }
}
