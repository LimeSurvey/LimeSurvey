<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Tests the setting that keeps database updates from being started from the web
 * interface while they are run from the command line, and the console command
 * that switches it.
 */
class WebDatabaseUpdateAllowedTest extends TestBaseClass
{
    /** @var mixed The setting as it was before the test */
    private $previousSetting;

    /** @var string|false The settings_global row as it was before the test, false if there was none */
    private $previousRow;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        \Yii::app()->loadHelper('update.updatedb');
        require_once \Yii::app()->getBasePath() . '/commands/AllowWebDatabaseUpdateCommand.php';
        $this->previousSetting = \Yii::app()->getConfig('allowwebdatabaseupdate', true);
        $this->previousRow = $this->readRow();
    }

    #[\Override]
    public function tearDown(): void
    {
        \Yii::app()->setConfig('allowwebdatabaseupdate', $this->previousSetting);
        $db = \Yii::app()->db;
        $db->createCommand()->delete('{{settings_global}}', 'stg_name = :name', [':name' => 'allowwebdatabaseupdate']);
        if ($this->previousRow !== false) {
            $db->createCommand()->insert('{{settings_global}}', ['stg_name' => 'allowwebdatabaseupdate', 'stg_value' => $this->previousRow]);
        }
        parent::tearDown();
    }

    /**
     * @return string|false the stored value, false if there is no row
     */
    private function readRow()
    {
        return \Yii::app()->db->createCommand()
            ->select('stg_value')
            ->from('{{settings_global}}')
            ->where('stg_name = :name', [':name' => 'allowwebdatabaseupdate'])
            ->queryScalar();
    }

    /**
     * Runs the console command and returns its exit code, swallowing its output.
     *
     * @param array $args
     * @return int
     */
    private function runCommand(array $args): int
    {
        $command = new \AllowWebDatabaseUpdateCommand('allowwebdatabaseupdate', null);
        ob_start();
        try {
            return (int) $command->run($args);
        } finally {
            ob_end_clean();
        }
    }

    /**
     * The update is allowed by default and refused only when the setting is switched off.
     *
     * @return void
     */
    public function testSettingControlsWebUpdates()
    {
        \Yii::app()->setConfig('allowwebdatabaseupdate', true);
        $this->assertTrue(isWebDatabaseUpdateAllowed());

        \Yii::app()->setConfig('allowwebdatabaseupdate', false);
        $this->assertFalse(isWebDatabaseUpdateAllowed());
        $this->assertNotEmpty(getWebDatabaseUpdateDisabledMessage());

        // settings_global values are strings
        \Yii::app()->setConfig('allowwebdatabaseupdate', '0');
        $this->assertFalse(isWebDatabaseUpdateAllowed());
        \Yii::app()->setConfig('allowwebdatabaseupdate', '1');
        $this->assertTrue(isWebDatabaseUpdateAllowed());
    }

    /**
     * The console command writes the setting to settings_global, creating or updating the row.
     *
     * @return void
     */
    public function testCommandWritesTheSetting()
    {
        \Yii::app()->db->createCommand()->delete('{{settings_global}}', 'stg_name = :name', [':name' => 'allowwebdatabaseupdate']);

        $this->assertSame(0, $this->runCommand(['off']));
        $this->assertSame('0', $this->readRow());
        $this->assertSame(0, $this->runCommand(['OFF']), 'Case does not matter');
        $this->assertSame('0', $this->readRow());
        $this->assertSame(0, $this->runCommand(['on']));
        $this->assertSame('1', $this->readRow());

        $this->assertSame(1, $this->runCommand([]), 'Missing mode is a usage error');
        $this->assertSame(1, $this->runCommand(['maybe']));
        $this->assertSame('1', $this->readRow(), 'A usage error changes nothing');
    }
}
