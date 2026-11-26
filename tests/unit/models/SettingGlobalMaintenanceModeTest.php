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

    /**
     * Loads the console command and remembers the setting and its settings_global row.
     *
     * @return void
     */
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        require_once \Yii::app()->getBasePath() . '/commands/SetMaintenanceModeCommand.php';
        \Yii::import('application.helpers.update.updatedb_helper', true);
        $this->previousSetting = \Yii::app()->getConfig('maintenancemode', 'off');
        $this->previousRow = $this->readRow();
    }

    /**
     * Restores the setting and its settings_global row.
     *
     * @return void
     */
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
     * Reads the maintenancemode row from settings_global.
     *
     * @return string|false The stored value, false if there is no row
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
        $this->assertFalse(SettingGlobal::areParticipantsLocked());
        $this->assertFalse(SettingGlobal::isAdministrationLocked());

        \Yii::app()->setConfig('maintenancemode', 'soft');
        $this->assertFalse(SettingGlobal::areParticipantsLocked());
        $this->assertFalse(SettingGlobal::isAdministrationLocked());

        \Yii::app()->setConfig('maintenancemode', 'hard');
        $this->assertTrue(SettingGlobal::areParticipantsLocked());
        $this->assertFalse(SettingGlobal::isAdministrationLocked());

        \Yii::app()->setConfig('maintenancemode', 'hard-cli');
        $this->assertTrue(SettingGlobal::areParticipantsLocked());
        $this->assertTrue(SettingGlobal::isAdministrationLocked());

        // The mode of another installation can be checked without loading its config
        $this->assertTrue(SettingGlobal::areParticipantsLocked('hard-cli'));
        $this->assertTrue(SettingGlobal::areParticipantsLocked('hard'));
        $this->assertFalse(SettingGlobal::areParticipantsLocked('soft'));
        $this->assertFalse(SettingGlobal::areParticipantsLocked('off'));

        // Installations updated from older versions have no value at all, unknown values count as off
        \Yii::app()->setConfig('maintenancemode', '');
        $this->assertSame('off', SettingGlobal::getMaintenanceMode());
        $this->assertFalse(SettingGlobal::isAdministrationLocked());
        \Yii::app()->setConfig('maintenancemode', 'Hard');
        $this->assertSame('off', SettingGlobal::getMaintenanceMode());
        $this->assertFalse(SettingGlobal::areParticipantsLocked());
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
        $this->assertSame(1, $this->runCommand(['off', '/no/such/config.php']), 'A missing config.php is a usage error');
        $this->assertSame(1, $this->runCommand(['off', dirname(__DIR__, 3) . '/README.md']), 'A file that is not a config.php is a usage error');
        $this->assertSame('soft', $this->readRow(), 'A usage error changes nothing');

        // A PHP file with another name is rejected without running it
        $dir = sys_get_temp_dir() . '/ls-maintenance-' . uniqid();
        mkdir($dir);
        $marker = $dir . '/included.txt';
        $script = $dir . '/not-a-config.php';
        file_put_contents($script, '<?php file_put_contents(' . var_export($marker, true) . ', "1"); return ["components" => ["db" => ["connectionString" => "x"]]];');
        try {
            $this->assertSame(1, $this->runCommand(['off', $script]));
            $this->assertFileDoesNotExist($marker, 'The file must not be included before it is rejected');
            $this->assertSame('soft', $this->readRow());
        } finally {
            @unlink($marker);
            unlink($script);
            rmdir($dir);
        }
    }

    /**
     * The maintenance mode row is written without the model, as needed during a database update,
     * and a missing row before the update is removed again afterwards.
     *
     * @return void
     */
    public function testWriteRowForUpdate()
    {
        \Yii::app()->db->createCommand()->delete('{{settings_global}}', 'stg_name = :name', [':name' => 'maintenancemode']);

        writeMaintenanceModeRow('hard');
        $this->assertSame('hard', $this->readRow());
        $this->assertTrue(SettingGlobal::areParticipantsLocked());
        writeMaintenanceModeRow('soft');
        $this->assertSame('soft', $this->readRow(), 'An existing row is replaced');
        $this->assertSame('soft', SettingGlobal::getMaintenanceMode());

        writeMaintenanceModeRow(null);
        $this->assertFalse($this->readRow(), 'null removes the row');
        $this->assertSame('off', SettingGlobal::getMaintenanceMode());
        writeMaintenanceModeRow(null);
        $this->assertFalse($this->readRow(), 'Nothing to remove is not an error');
    }

    /**
     * A config.php without a table prefix gives an empty prefix, not null, so that the
     * {{table}} placeholders are still replaced.
     *
     * @return void
     */
    public function testCustomConfigWithoutTablePrefix()
    {
        $realConfig = \Yii::app()->getBasePath() . '/config/config.php';
        $dir = sys_get_temp_dir() . '/ls-maintenance-' . uniqid();
        mkdir($dir);
        $configPath = $dir . '/config.php';
        $code = '<?php $c = include ' . var_export($realConfig, true) . '; unset($c["components"]["db"]["tablePrefix"]); return $c;';
        file_put_contents($configPath, $code);
        $command = new class ('setmaintenancemode', null) extends \SetMaintenanceModeCommand {
            public function loadConfig(string $configPath): void
            {
                ob_start();
                try {
                    $this->useCustomConfig($configPath);
                } finally {
                    ob_end_clean();
                }
            }
        };
        try {
            $command->loadConfig($configPath);
            $this->assertSame('', \Yii::app()->db->tablePrefix);
            $this->assertSame('SELECT 1 FROM settings_global', \Yii::app()->db->createCommand('SELECT 1 FROM {{settings_global}}')->getText());
        } finally {
            // Back to the connection of the test installation
            $command->loadConfig($realConfig);
            unlink($configPath);
            rmdir($dir);
        }
    }

    /**
     * The participant pages that write data enable the lock filter, which lets the action run
     * while participants are not locked. While they are locked it ends every request, which
     * cannot be tested here.
     *
     * @return void
     */
    public function testParticipantsLockedFilter()
    {
        foreach (['RegisterController', 'OptinController', 'OptoutController'] as $controllerClass) {
            require_once \Yii::app()->getBasePath() . '/controllers/' . $controllerClass . '.php';
            $controller = new $controllerClass(strtolower(substr($controllerClass, 0, -10)));
            $this->assertContains('participantsLocked', $controller->filters(), $controllerClass);
        }

        $chain = new class {
            /** @var bool */
            public $ran = false;

            /**
             * Records that the action would run.
             *
             * @return void
             */
            public function run()
            {
                $this->ran = true;
            }
        };
        $controller = new \OptinController('optin');
        $previousGet = $_GET;
        try {
            $_GET = ['surveyid' => '999999999'];
            \Yii::app()->setConfig('maintenancemode', 'soft');
            $controller->filterParticipantsLocked($chain);
            $this->assertTrue($chain->ran, 'Not locked: the action runs');
        } finally {
            $_GET = $previousGet;
        }
    }
}
