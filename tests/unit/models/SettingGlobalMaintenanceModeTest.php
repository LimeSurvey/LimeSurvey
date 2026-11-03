<?php

namespace ls\tests\unit\models;

use ls\tests\TestBaseClass;
use SettingGlobal;

/**
 * Tests the maintenance mode helpers, including the mode "hard-cli" that locks the
 * administration, and the console command that sets the mode.
 */
class SettingGlobalMaintenanceModeTest extends TestBaseClass
{
    /** @var mixed The setting as it was before the test */
    private $previousSetting;

    /** @var string|false The settings_global row as it was before the test, false if there was none */
    private $previousRow;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        require_once \Yii::app()->getBasePath() . '/commands/SetMaintenanceModeCommand.php';
        $this->previousSetting = \Yii::app()->getConfig('maintenancemode', 'off');
        $this->previousRow = $this->readRow();
    }

    #[\Override]
    public function tearDown(): void
    {
        \Yii::app()->setConfig('maintenancemode', $this->previousSetting);
        $db = \Yii::app()->db;
        $db->createCommand()->delete('{{settings_global}}', 'stg_name = :name', [':name' => 'maintenancemode']);
        if ($this->previousRow !== false) {
            $db->createCommand()->insert('{{settings_global}}', ['stg_name' => 'maintenancemode', 'stg_value' => $this->previousRow]);
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
            ->where('stg_name = :name', [':name' => 'maintenancemode'])
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
        $command = new \SetMaintenanceModeCommand('setmaintenancemode', null);
        ob_start();
        try {
            return (int) $command->run($args);
        } finally {
            ob_end_clean();
        }
    }

    /**
     * Only "hard" and "hard-cli" keep participants from taking surveys, only "hard-cli" locks the administration.
     *
     * @return void
     */
    public function testHelpersPerMode()
    {
        \Yii::app()->setConfig('maintenancemode', 'off');
        $this->assertSame('off', SettingGlobal::getMaintenanceMode());
        $this->assertFalse(SettingGlobal::isMaintenanceModeHard());
        $this->assertFalse(SettingGlobal::isAdministrationLocked());

        \Yii::app()->setConfig('maintenancemode', 'soft');
        $this->assertFalse(SettingGlobal::isMaintenanceModeHard());
        $this->assertFalse(SettingGlobal::isAdministrationLocked());

        \Yii::app()->setConfig('maintenancemode', 'hard');
        $this->assertTrue(SettingGlobal::isMaintenanceModeHard());
        $this->assertFalse(SettingGlobal::isAdministrationLocked());

        \Yii::app()->setConfig('maintenancemode', 'hard-cli');
        $this->assertTrue(SettingGlobal::isMaintenanceModeHard());
        $this->assertTrue(SettingGlobal::isAdministrationLocked());

        // Installations updated from older versions have no value at all
        \Yii::app()->setConfig('maintenancemode', '');
        $this->assertSame('off', SettingGlobal::getMaintenanceMode());
        $this->assertFalse(SettingGlobal::isAdministrationLocked());
    }

    /**
     * The console command writes the mode to settings_global and the running configuration.
     *
     * @return void
     */
    public function testCommandWritesTheMode()
    {
        \Yii::app()->db->createCommand()->delete('{{settings_global}}', 'stg_name = :name', [':name' => 'maintenancemode']);

        $this->assertSame(0, $this->runCommand(['hard-cli']));
        $this->assertSame('hard-cli', $this->readRow());
        $this->assertTrue(SettingGlobal::isAdministrationLocked());
        $this->assertSame(0, $this->runCommand(['OFF']), 'Case does not matter');
        $this->assertSame('off', $this->readRow());
        $this->assertFalse(SettingGlobal::isAdministrationLocked());
        $this->assertSame(0, $this->runCommand(['soft']));
        $this->assertSame('soft', $this->readRow());

        $this->assertSame(1, $this->runCommand([]), 'Missing mode is a usage error');
        $this->assertSame(1, $this->runCommand(['hardcli']));
        $this->assertSame('soft', $this->readRow(), 'A usage error changes nothing');
    }
}
