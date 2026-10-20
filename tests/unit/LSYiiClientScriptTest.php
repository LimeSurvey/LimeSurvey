<?php

namespace ls\tests;

use Yii;

/**
 * Test LSYii_ClientScript class.
 */
class LSYiiClientScriptTest extends TestBaseClass
{
    /**
     * Name of the survey theme package used by the tests.
     * @var string
     */
    private const PACKAGE_NAME = 'survey-template-lsyiiclientscripttest';

    /**
     * @inheritdoc
     * Remove the test package and everything registered by the test.
     */
    protected function tearDown(): void
    {
        $clientScript = Yii::app()->clientScript;
        $clientScript->reset();
        $packages = $clientScript->packages;
        unset($packages[self::PACKAGE_NAME]);
        $clientScript->packages = $packages;
        parent::tearDown();
    }

    /**
     * The custom.css of a survey theme must be rendered at the end of the head section,
     * after the theme options inline styles, while the other theme css files stay before the title.
     */
    public function testThemeCustomCssIsRenderedAtEndOfHead()
    {
        $clientScript = Yii::app()->clientScript;
        $clientScript->addPackage(self::PACKAGE_NAME, array(
            'baseUrl' => '/themes/survey/lsyiiclientscripttest',
            'css'     => array('css/base.css', 'css/custom.css'),
        ));
        $clientScript->registerPackage(self::PACKAGE_NAME);

        $output = '<html><head><title>Test</title><style>body { color: red; }</style></head><body></body></html>';
        $clientScript->render($output);

        $basePosition = strpos($output, 'lsyiiclientscripttest/css/base.css');
        $customPosition = strpos($output, 'lsyiiclientscripttest/css/custom.css');
        $this->assertNotFalse($basePosition, 'base.css is not rendered.');
        $this->assertNotFalse($customPosition, 'custom.css is not rendered.');
        $this->assertSame(1, substr_count($output, 'lsyiiclientscripttest/css/custom.css'), 'custom.css must be rendered once.');
        $this->assertLessThan(strpos($output, '<title>'), $basePosition, 'base.css must be rendered before the title.');
        $this->assertGreaterThan(strpos($output, '</style>'), $customPosition, 'custom.css must be rendered after the theme options inline styles.');
        $this->assertLessThan(strpos($output, '</head>'), $customPosition, 'custom.css must be rendered inside the head section.');
    }

    /**
     * A custom.css file outside of a survey theme package keeps its position.
     */
    public function testOtherCustomCssKeepsItsPosition()
    {
        $clientScript = Yii::app()->clientScript;
        $clientScript->registerCssFile('/plugins/lsyiiclientscripttest/css/custom.css');

        $output = '<html><head><title>Test</title><style>body { color: red; }</style></head><body></body></html>';
        $clientScript->render($output);

        $customPosition = strpos($output, 'lsyiiclientscripttest/css/custom.css');
        $this->assertNotFalse($customPosition, 'custom.css is not rendered.');
        $this->assertLessThan(strpos($output, '<title>'), $customPosition, 'custom.css must be rendered before the title.');
    }
}
