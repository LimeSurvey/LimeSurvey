<?php

namespace ls\tests;

use LSActiveRecord;

/**
 * @group api
 */
class SettingsPluginTest extends TestBaseClass
{
    protected static $plugin;

    protected static $settings = [];
    protected static $dateTimeSettings = [];

    /**
     * @inheritdoc
     * Activate needed plugins
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once __DIR__ . '/../../data/plugins/SettingsPlugin.php';
        $plugin = \Plugin::model()->findByAttributes(array('name' => 'SettingsPlugin'));
        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = 'SettingsPlugin';
            $plugin->active = 1;
            $plugin->save();
        } else {
            $plugin->active = 1;
            $plugin->save();
        }

        self::$plugin = App()->getPluginManager()->loadPlugin('SettingsPlugin', $plugin->id);

        $obj = new \stdClass();
        $obj->customProperty = 'abc';

        self::$settings = [
            'empty_1' => 0,
            'empty_2' => null,
            'empty_3' => false,
            'empty_4' => "",
            'empty_5' => [],
            'empty_6' => (object) [],
            'empty_7' => new \stdClass(),
            'number' => rand(100, 999),
            'decimal' => rand(100, 999) * 0.5,
            'string' => 'abc',
            'string_2' => 'ABC',
            'especial_character_1' => 'ñ',
            'especial_character_2' => 'á',
            'especial_character_3' => 'é',
            'especial_character_4' => 'í',
            'especial_character_5' => 'ó',
            'especial_character_6' => 'ú',
            'especial_character_7' => 'Ñ',
            'especial_character_8' => 'Á',
            'especial_character_9' => 'É',
            'especial_character_10' => 'Í',
            'especial_character_11' => 'Ó',
            'especial_character_12' => 'Ú',
            'array_1' => ['a', 'b', 'c'],
            'array_2' => [1,2,3],
            'object_1' => (object) ['property' => 'Here we go'],
            'object_2' => $obj,
        ];

        self::$dateTimeSettings = array(
            'date_time_1' => [
                'datetime' => date_create()->format('Y-m-d H:i:s'),
            ],
            'date_time_2' => [
                'datetime' => date_create()->format('Y-m-d H:i:s'),
                'datetimesaveformat' => 'd/m/Y'
            ],
        );
    }

    public function testGetAndSetSetting()
    {
        foreach (self::$settings as $key => $value) {
            self::$plugin->setSetting($key, $value);

            $setting = \PluginSetting::model()->findByAttributes([
                'plugin_id' => self::$plugin->getId(),
                'key' => $key
            ]);

            $this->assertNotEmpty($setting->id);
            $this->assertEquals($setting->value, json_encode($value));

            $settingValue = self::$plugin->getSetting($key);
            $this->assertEquals($settingValue, json_decode(json_encode($value), true));
        }
    }

    public function testGetAndSetSettingEncrypted()
    {
        self::$plugin->setEncryptedSettings(array_keys(self::$settings));
        foreach (self::$settings as $key => $value) {
            self::$plugin->setSetting($key, $value);

            $setting = \PluginSetting::model()->findByAttributes([
                'plugin_id' => self::$plugin->getId(),
                'key' => $key
            ]);

            $this->assertNotEmpty($setting->id);
            if (empty($value)) {
                $this->assertEquals($setting->value, json_encode($value));
            } else {
                $this->assertEquals(
                    $setting->value,
                    json_encode(LSActiveRecord::encryptSingle(json_encode($value)))
                );
            }

            $settingValue = self::$plugin->getSetting($key);
            $this->assertEquals($settingValue, json_decode(json_encode($value), true));
        }
    }

    public function testGetAndSetDateTimeSettings(): void
    {

        foreach (self::$dateTimeSettings as $key => $value) {
            self::$plugin->setSetting($key, $value);

            $setting = \PluginSetting::model()->findByAttributes([
                'plugin_id' => self::$plugin->getId(),
                'key' => $key
            ]);

            $settingValue = self::$plugin->getSetting($key);

            if (is_array($settingValue) && isset($settingValue['datetime'])) {
                //Default date time format
                $this->assertSame($settingValue['datetime'], $value['datetime']);
            } else {
                //Custom date time format
                $date = \LimeSurvey\PluginManager\LimesurveyApi::getFormattedDateTime($value['datetime'], $value['datetimesaveformat']);
                $this->assertSame($date, $settingValue);
            }
        }
    }
}
