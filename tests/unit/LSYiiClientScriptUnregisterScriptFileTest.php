<?php

namespace ls\tests;

use LSYii_ClientScript;
use Yii;

/**
 * Tests for LSYii_ClientScript::unregisterScriptFile().
 */
class LSYiiClientScriptUnregisterScriptFileTest extends TestBaseClass
{
    /** @var mixed */
    private $useAssetManager;

    /**
     * Keep the asset manager setting, the tests change it.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->useAssetManager = Yii::app()->getConfig('use_asset_manager');
    }

    /**
     * Restore the asset manager setting.
     */
    public function tearDown(): void
    {
        Yii::app()->setConfig('use_asset_manager', $this->useAssetManager);
        parent::tearDown();
    }

    /**
     * Script files registered at other positions than the head are removed.
     */
    public function testUnregisterAllPositions()
    {
        Yii::app()->setConfig('use_asset_manager', false);
        $clientScript = $this->getClientScriptWithScriptFiles();

        $clientScript->unregisterScriptFile($this->getScriptUrl('multiplechoice_withcomments.js'));
        $clientScript->unregisterScriptFile($this->getScriptUrl('date.js'));

        $this->assertSame([], $this->getRegisteredUrls($clientScript));
    }

    /**
     * Script files published through the asset manager are removed with their original URL.
     */
    public function testUnregisterPublishedScriptFile()
    {
        Yii::app()->setConfig('use_asset_manager', true);
        $clientScript = $this->getClientScriptWithScriptFiles();
        $this->assertNotContains(
            $this->getScriptUrl('date.js'),
            $this->getRegisteredUrls($clientScript),
            'The script file was not published.'
        );

        $clientScript->unregisterScriptFile($this->getScriptUrl('multiplechoice_withcomments.js'));
        $clientScript->unregisterScriptFile($this->getScriptUrl('date.js'));

        $this->assertSame([], $this->getRegisteredUrls($clientScript));
    }

    /**
     * Other script files stay registered.
     */
    public function testUnregisterKeepsOtherScriptFiles()
    {
        Yii::app()->setConfig('use_asset_manager', true);
        $clientScript = $this->getClientScriptWithScriptFiles();
        $dateUrls = $this->getRegisteredUrls($clientScript);

        $clientScript->unregisterScriptFile($this->getScriptUrl('multiplechoice_withcomments.js'));

        $this->assertCount(1, $this->getRegisteredUrls($clientScript));
        $this->assertContains(end($dateUrls), $this->getRegisteredUrls($clientScript));
    }

    /**
     * Return a client script with two script files registered as the question renderers do.
     *
     * @return LSYii_ClientScript
     */
    private function getClientScriptWithScriptFiles()
    {
        $clientScript = new LSYii_ClientScript();
        $clientScript->registerScriptFile($this->getScriptUrl('multiplechoice_withcomments.js'), LSYii_ClientScript::POS_BEGIN);
        $clientScript->registerScriptFile($this->getScriptUrl('date.js'), LSYii_ClientScript::POS_END);
        $this->assertCount(2, $this->getRegisteredUrls($clientScript));
        return $clientScript;
    }

    /**
     * Return the URL of a general script.
     *
     * @param string $fileName
     * @return string
     */
    private function getScriptUrl($fileName)
    {
        return Yii::app()->getConfig('generalscripts') . $fileName;
    }

    /**
     * Return the URLs of all registered script files, whatever their position.
     *
     * @param LSYii_ClientScript $clientScript
     * @return string[]
     */
    private function getRegisteredUrls(LSYii_ClientScript $clientScript)
    {
        $urls = [];
        foreach ($clientScript->getScriptFiles() as $scriptFiles) {
            $urls = array_merge($urls, array_keys($scriptFiles));
        }
        return $urls;
    }
}
