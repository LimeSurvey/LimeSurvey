<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

class SurveyThemeHelperTest extends TestBaseClass
{
    /** @var string[] Temporary config files to remove after each test */
    private $tmpFiles = [];

    /**
     * Removes the temporary config files.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        foreach ($this->tmpFiles as $tmpFile) {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
        $this->tmpFiles = [];
        parent::tearDown();
    }

    /**
     * A theme without a 'cssframework' theme option (like Vanilla, which only declares the CSS framework
     * in 'engine') must not log a warning and must not be modified.
     *
     * @return void
     */
    public function testConfigWithEngineCssFrameworkOnlyIsLeftAlone()
    {
        $configFile = $this->createTmpConfig(file_get_contents(\Yii::app()->getConfig('rootdir') . '/themes/survey/vanilla/config.xml'));
        $originalContent = file_get_contents($configFile);

        $warnings = $this->getWarningsLoggedBy(function () use ($configFile) {
            \SurveyThemeHelper::checkConfigFiles($configFile);
        });

        $this->assertSame([], $warnings, 'No warning must be logged for a config without cssframework theme option.');
        $this->assertSame($originalContent, file_get_contents($configFile), 'The config file must not be modified.');
    }

    /**
     * The 'cssframework' theme option options get wrapped in an 'optgroup' and a default option is set,
     * even if the 'engine' 'cssframework' node comes first in the document.
     *
     * @return void
     */
    public function testCssFrameworkOptionIsNormalizedWhenEngineComesFirst()
    {
        $configFile = $this->createTmpConfig(
            '<?xml version="1.0" encoding="UTF-8"?>
            <config>
                <engine>
                    <cssframework>
                        <name>bootstrap</name>
                    </cssframework>
                </engine>
                <options>
                    <cssframework type="dropdown" title="Variations">
                        <dropdownoptions>
                            <option value="css/variations/a.css">A</option>
                            <option value="css/variations/b.css">B</option>
                        </dropdownoptions>
                    </cssframework>
                </options>
            </config>'
        );

        $warnings = $this->getWarningsLoggedBy(function () use ($configFile) {
            \SurveyThemeHelper::checkConfigFiles($configFile);
        });

        $this->assertSame([], $warnings, 'No warning must be logged for a valid config.');
        $xPath = new \DOMXPath($this->loadDomDocument($configFile));
        $this->assertSame(2, $xPath->query('/config/options/cssframework/dropdownoptions/optgroup/option')->length, 'Options must be wrapped in an optgroup.');
        $this->assertSame('A', trim($xPath->query('/config/options/cssframework/text()')->item(0)->nodeValue), 'The first option must be set as default.');
        $this->assertSame(0, $xPath->query('/config/engine/cssframework/dropdownoptions')->length, 'The engine cssframework must not be modified.');
    }

    /**
     * A 'cssframework' theme option without dropdown options (e.g. 'inherit') is valid and must not log a warning.
     *
     * @return void
     */
    public function testCssFrameworkOptionWithoutDropdownOptionsIsLeftAlone()
    {
        $configFile = $this->createTmpConfig(
            '<?xml version="1.0" encoding="UTF-8"?>
            <config>
                <options>
                    <cssframework>inherit</cssframework>
                </options>
            </config>'
        );
        $originalContent = file_get_contents($configFile);

        $warnings = $this->getWarningsLoggedBy(function () use ($configFile) {
            \SurveyThemeHelper::checkConfigFiles($configFile);
        });

        $this->assertSame([], $warnings, 'No warning must be logged for a cssframework option without dropdown options.');
        $this->assertSame($originalContent, file_get_contents($configFile), 'The config file must not be modified.');
    }

    /**
     * Writes the given XML to a temporary config file.
     *
     * @param string $xml The config XML
     * @return string The path of the temporary config file
     */
    private function createTmpConfig($xml)
    {
        $configFile = tempnam(sys_get_temp_dir(), 'lsconfig');
        file_put_contents($configFile, $xml);
        $this->tmpFiles[] = $configFile;
        return $configFile;
    }

    /**
     * Loads a config file into a DOMDocument.
     *
     * @param string $configFile The path of the config file
     * @return \DOMDocument
     */
    private function loadDomDocument($configFile)
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML(file_get_contents($configFile));
        return $domDocument;
    }

    /**
     * Runs the callback and returns the 'application' warnings it logged.
     *
     * @param callable $callback The code to run
     * @return array The logged warnings
     */
    private function getWarningsLoggedBy(callable $callback)
    {
        $logger = \Yii::getLogger();
        $countBefore = count($logger->getLogs(\CLogger::LEVEL_WARNING, 'application'));
        $callback();
        return array_slice($logger->getLogs(\CLogger::LEVEL_WARNING, 'application'), $countBefore);
    }
}
