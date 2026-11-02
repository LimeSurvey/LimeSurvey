<?php

namespace ls\tests;

use Yii;
use CUrlManager;
use LSAdminUrlRule;

/**
 * URLs of administration controllers are created and parsed with the "admin" prefix.
 */
class LSAdminUrlRuleTest extends TestBaseClass
{
    /**
     * @inheritdoc
     * Save the URL settings, they are changed by every test
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$testHelper::saveUrlSettings();
    }

    /**
     * @inheritdoc
     * Put the URL settings back
     */
    public static function tearDownAfterClass(): void
    {
        self::$testHelper::resetUrlSettings();
        parent::tearDownAfterClass();
    }

    /**
     * Use the shipped routes in path format with a fixed base URL
     *
     * @param string $urlFormat
     * @return void
     */
    private function useUrlFormat($urlFormat = CUrlManager::PATH_FORMAT)
    {
        self::$testHelper::setUrlToExpectedDefault($urlFormat);
        Yii::app()->getUrlManager()->setBaseUrl('/index.php');
    }

    /**
     * An administration controller route gets the prefix, parameters stay in the query string
     */
    public function testAdminControllerUrlGetsPrefix()
    {
        $this->useUrlFormat();
        $this->assertSame(
            '/index.php/admin/surveyAdministration/view?surveyid=123',
            Yii::app()->createUrl('surveyAdministration/view', ['surveyid' => 123])
        );
        $this->assertSame(
            '/index.php/admin/userManagement/index',
            Yii::app()->createUrl('/userManagement/index')
        );
    }

    /**
     * Path style parameters inside the route are kept
     */
    public function testRouteWithPathParamsKeepsThem()
    {
        $this->useUrlFormat();
        $this->assertSame(
            '/index.php/admin/surveyAdministration/view/surveyid/123',
            Yii::app()->createUrl('surveyAdministration/view/surveyid/123')
        );
    }

    /**
     * The editor page and the editor link live under the prefix too
     */
    public function testEditorUrls()
    {
        $this->useUrlFormat();
        $this->assertSame('/index.php/admin/editor', Yii::app()->createUrl('editor'));
        $this->assertSame(
            '/index.php/admin/editorLink/index?route=survey%2F1',
            Yii::app()->createUrl('editorLink/index', ['route' => 'survey/1'])
        );
    }

    /**
     * The legacy AdminController keeps its own URLs
     */
    public function testLegacyAdminControllerUrlsUnchanged()
    {
        $this->useUrlFormat();
        $this->assertSame(
            '/index.php/admin/survey/sa/view/surveyid/1',
            Yii::app()->createUrl('admin/survey/sa/view', ['surveyid' => 1])
        );
        $this->assertSame(
            '/index.php/admin/authentication/sa/login',
            Yii::app()->createUrl('admin/authentication/sa/login')
        );
        $this->assertSame('/index.php/admin/index', Yii::app()->createUrl('admin/index'));
    }

    /**
     * Public controllers are not prefixed
     */
    public function testPublicControllerUrlsUnchanged()
    {
        $this->useUrlFormat();
        $this->assertSame('/index.php/1', Yii::app()->createUrl('survey/index', ['sid' => 1]));
        $this->assertSame('/index.php/1?newtest=Y', Yii::app()->createUrl('survey/index', ['sid' => 1, 'newtest' => 'Y']));
        $this->assertSame('/index.php/optout/tokens/1', Yii::app()->createUrl('optout/tokens', ['surveyid' => 1]));
        $this->assertSame('/index.php/rest/v1/survey', Yii::app()->createUrl('rest', ['_api_version' => 'v1', '_entity' => 'survey']));
    }

    /**
     * Nothing changes in GET URL format
     */
    public function testGetFormatUnchanged()
    {
        $this->useUrlFormat(CUrlManager::GET_FORMAT);
        $this->assertSame(
            '/index.php?r=surveyAdministration/view&surveyid=1',
            Yii::app()->createUrl('surveyAdministration/view', ['surveyid' => 1])
        );
    }

    /**
     * Prefixed request paths of administration controllers are parsed, everything else is left to the other rules
     */
    public function testParseUrl()
    {
        $this->useUrlFormat();
        $rule = new LSAdminUrlRule();

        $this->assertSame('surveyAdministration/view', $this->parse($rule, 'admin/surveyAdministration/view'));
        $this->assertSame('editor', $this->parse($rule, 'admin/editor'));
        $this->assertSame('editorLink/index', $this->parse($rule, 'admin/editorLink/index'));

        unset($_GET['surveyid']);
        $this->assertSame('surveyAdministration/view', $this->parse($rule, 'admin/surveyAdministration/view/surveyid/123'));
        $this->assertSame('123', $_GET['surveyid']);
        unset($_GET['surveyid']);

        $this->assertFalse($this->parse($rule, 'admin/survey/sa/view/surveyid/1'));
        $this->assertFalse($this->parse($rule, 'admin/authentication/sa/login'));
        $this->assertFalse($this->parse($rule, 'admin/index'));
        $this->assertFalse($this->parse($rule, 'admin'));
        $this->assertFalse($this->parse($rule, 'admin/doesNotExist/index'));
        $this->assertFalse($this->parse($rule, 'surveyAdministration/view'));
        $this->assertFalse($this->parse($rule, 'survey/index/sid/1'));
    }

    /**
     * Parse a path with the rule
     *
     * @param LSAdminUrlRule $rule
     * @param string $pathInfo
     * @return string|false
     */
    private function parse(LSAdminUrlRule $rule, $pathInfo)
    {
        return $rule->parseUrl(Yii::app()->getUrlManager(), Yii::app()->getRequest(), $pathInfo, $pathInfo);
    }

    /**
     * Only controllers implementing AdminControllerInterface count, "admin" itself never does
     */
    public function testIsAdminController()
    {
        foreach (['surveyAdministration', 'userManagement', 'editor', 'editorLink', 'dashboard'] as $id) {
            $this->assertTrue(LSAdminUrlRule::isAdminController($id), "$id should be an admin controller");
        }
        foreach (['admin', 'survey', 'optout', 'rest', 'doesNotExist', '../config', ''] as $id) {
            $this->assertFalse(LSAdminUrlRule::isAdminController($id), "'$id' should not be an admin controller");
        }
    }

    /**
     * Prefix detection on request paths
     */
    public function testHasPrefix()
    {
        $this->assertTrue(LSAdminUrlRule::hasPrefix('admin'));
        $this->assertTrue(LSAdminUrlRule::hasPrefix('admin/surveyAdministration/view'));
        $this->assertFalse(LSAdminUrlRule::hasPrefix('administration/view'));
        $this->assertFalse(LSAdminUrlRule::hasPrefix('surveyAdministration/view'));
        $this->assertFalse(LSAdminUrlRule::hasPrefix(''));
    }
}
