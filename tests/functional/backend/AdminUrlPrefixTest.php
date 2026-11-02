<?php

namespace ls\tests;

use Yii;
use CUrlManager;

/**
 * Administration controllers are only reachable under the "admin" URL prefix.
 *
 * Talks to the web server directly (no browser), so the HTTP status codes can be checked.
 * Needs the DOMAIN environment variable like the browser tests, e.g. DOMAIN=localhost/limesurvey.
 */
class AdminUrlPrefixTest extends TestBaseClass
{
    /** @var string Base URL of the installation, up to and including index.php */
    private static $baseUrl;

    /** @var string Cookie jar file, keeps the session and CSRF cookies between requests */
    private static $cookieJar;

    /**
     * @inheritdoc
     * Build the base URL from the DOMAIN environment variable
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $domain = getenv('DOMAIN');
        if (empty($domain)) {
            echo 'Must specify DOMAIN environment variable to run this test, like "DOMAIN=localhost/limesurvey" or "DOMAIN=limesurvey.localhost".';
            exit(12);
        }
        self::$baseUrl = 'http://' . $domain . '/index.php';
        self::$cookieJar = tempnam(sys_get_temp_dir(), 'adminurlprefix');
    }

    /**
     * @inheritdoc
     * Remove the cookie jar
     */
    public static function tearDownAfterClass(): void
    {
        if (self::$cookieJar && file_exists(self::$cookieJar)) {
            unlink(self::$cookieJar);
        }
        parent::tearDownAfterClass();
    }

    /**
     * Send one request without following redirects
     *
     * @param string $method GET, HEAD or POST
     * @param string $path Path after index.php, including the query string
     * @param array $postData Form fields for a POST request
     * @return array{status: int, location: string, body: string} Status code, Location header (empty when none) and body
     */
    private function request($method, $path, array $postData = [])
    {
        $curl = curl_init(self::$baseUrl . $path);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => $method === 'HEAD',
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR => self::$cookieJar,
            CURLOPT_COOKIEFILE => self::$cookieJar,
        ]);
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
        }
        $response = curl_exec($curl);
        $this->assertNotFalse($response, 'Request failed: ' . curl_error($curl));
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        curl_close($curl);
        $location = '';
        if (preg_match('/^Location:\s*(.+?)\s*$/mi', substr($response, 0, $headerSize), $matches)) {
            $location = $matches[1];
        }
        return ['status' => $status, 'location' => $location, 'body' => substr($response, $headerSize)];
    }

    /**
     * Get a CSRF token (and its cookie in the jar) from the login form, so a POST passes the CSRF check
     *
     * @return string
     */
    private function getCsrfToken()
    {
        $response = $this->request('GET', '/admin/authentication/sa/login');
        $this->assertSame(200, $response['status']);
        $found = preg_match('/name="YII_CSRF_TOKEN"[^>]*value="([^"]+)"|value="([^"]+)"[^>]*name="YII_CSRF_TOKEN"/', $response['body'], $matches);
        $this->assertSame(1, $found, 'No CSRF token in the login form');
        return $matches[1] !== '' ? $matches[1] : $matches[2];
    }

    /**
     * Whether the installation uses the path URL format, the prefix is only enforced there
     *
     * @return bool
     */
    private function usesPathFormat()
    {
        return Yii::app()->getUrlManager()->getUrlFormat() === CUrlManager::PATH_FORMAT;
    }

    /**
     * A GET request without the prefix is permanently redirected, query string included
     */
    public function testGetWithoutPrefixIsRedirectedPermanently()
    {
        $response = $this->request('GET', '/surveyAdministration/listsurveys?active=Y');
        if (!$this->usesPathFormat()) {
            $this->assertNotSame(301, $response['status'], 'Nothing is enforced in GET URL format');
            return;
        }
        $this->assertSame(301, $response['status']);
        $this->assertStringEndsWith('/index.php/admin/surveyAdministration/listsurveys?active=Y', $response['location']);
    }

    /**
     * Path style parameters of the old URL end up in the redirect target
     */
    public function testPathParamsAreKeptInRedirect()
    {
        if (!$this->usesPathFormat()) {
            $this->assertTrue(true);
            return;
        }
        $response = $this->request('GET', '/surveyAdministration/view/surveyid/123');
        $this->assertSame(301, $response['status']);
        $this->assertStringEndsWith('/index.php/admin/surveyAdministration/view?surveyid=123', $response['location']);
    }

    /**
     * A POST request without the prefix is refused instead of redirected
     */
    public function testPostWithoutPrefixIsRefused()
    {
        if (!$this->usesPathFormat()) {
            $this->assertTrue(true);
            return;
        }
        $token = $this->getCsrfToken();
        $response = $this->request('POST', '/surveyAdministration/listsurveys', ['YII_CSRF_TOKEN' => $token]);
        $this->assertSame(404, $response['status']);
        // The same POST with the prefix passes the prefix check (as guest it is sent to the login page)
        $response = $this->request('POST', '/admin/surveyAdministration/listsurveys', ['YII_CSRF_TOKEN' => $token]);
        $this->assertSame(302, $response['status']);
    }

    /**
     * The prefixed URL is served: as guest this is the redirect to the login page
     */
    public function testPrefixedUrlIsServed()
    {
        $response = $this->request('GET', '/admin/surveyAdministration/listsurveys');
        $this->assertSame(302, $response['status']);
        $this->assertStringContainsString('/admin/authentication/sa/login', $response['location']);
    }

    /**
     * The editor page and the editor link are under the prefix as well
     */
    public function testEditorIsUnderPrefix()
    {
        $response = $this->request('GET', '/admin/editor');
        $this->assertSame(302, $response['status'], 'Guest is sent to the login page');
        $this->assertStringContainsString('/admin/authentication/sa/login', $response['location']);

        if ($this->usesPathFormat()) {
            $response = $this->request('GET', '/editorLink?route=survey%2F1');
            $this->assertSame(301, $response['status']);
            $this->assertStringEndsWith('/index.php/admin/editorLink?route=survey%2F1', $response['location']);
        }
    }

    /**
     * With URL rewriting the old static editor entry point is redirected to the prefixed editor page
     *
     * Checked only when the web server rewrites URLs without the script name.
     */
    public function testRewrittenEditorUrls()
    {
        $rootUrl = substr(self::$baseUrl, 0, -strlen('/index.php'));
        $curl = curl_init($rootUrl . '/admin/authentication/sa/login');
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true]);
        curl_exec($curl);
        $rewriting = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE) === 200;
        curl_close($curl);
        if (!$rewriting || !$this->usesPathFormat()) {
            $this->assertTrue(true, 'No URL rewriting or GET URL format');
            return;
        }
        $saveBaseUrl = self::$baseUrl;
        self::$baseUrl = $rootUrl;
        try {
            $response = $this->request('GET', '/editor/');
            $this->assertSame(301, $response['status']);
            $this->assertStringEndsWith('/index.php/admin/editor', $response['location']);

            foreach (['/admin/editor', '/admin/editor/'] as $path) {
                $response = $this->request('GET', $path);
                $this->assertSame(302, $response['status'], $path . ' is served, the guest is sent to the login page');
                $this->assertStringContainsString('/admin/authentication/sa/login', $response['location']);
            }
        } finally {
            self::$baseUrl = $saveBaseUrl;
        }
    }

    /**
     * A survey alias named like an administration controller is served at the unprefixed URL
     */
    public function testSurveyAliasWinsOverUnprefixedAdminController()
    {
        if (!$this->usesPathFormat()) {
            $this->assertTrue(true);
            return;
        }
        $alias = 'quotas';
        $this->assertNull(
            \SurveyLanguageSetting::model()->findByAttributes(['surveyls_alias' => $alias]),
            'Another survey already uses the alias'
        );
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_718593_list_with_comment.lss');
        $languageSetting = self::$testSurvey->languagesettings[self::$testSurvey->language];
        $languageSetting->surveyls_alias = $alias;
        $this->assertTrue($languageSetting->save(false), 'Could not set the alias');
        try {
            $response = $this->request('GET', '/' . $alias);
            $this->assertSame(200, $response['status'], 'The survey is served instead of the quotas controller');
            $this->assertStringContainsString($languageSetting->surveyls_title, $response['body']);

            $response = $this->request('GET', '/admin/' . $alias);
            $this->assertSame(302, $response['status'], 'The prefixed URL still reaches the controller');
            $this->assertStringContainsString('/admin/authentication/sa/login', $response['location']);
        } finally {
            \Yii::app()->session['loginID'] = 1;
            self::$testSurvey->delete();
            self::$testSurvey = null;
        }
    }

    /**
     * The legacy AdminController and the public survey controller are not affected
     */
    public function testOtherControllersAreNotTouched()
    {
        $response = $this->request('GET', '/admin/authentication/sa/login');
        $this->assertSame(200, $response['status']);

        $response = $this->request('GET', '/surveys/publicList');
        $this->assertSame(200, $response['status']);
    }
}
