<?php

namespace ls\tests;

use LSActiveRecord;

/**
 * @group api
 */
class SettingsPluginTest extends TestBaseClass
{
    /* @var LimeSurvey\PluginManager\iPlugin */
    protected static $plugin;

    /* @var array : settings with value to set */
    protected static $settings = [];
    /* @var array[] : date time settings of plugin  */
    protected static $dateTimePluginSettings = [
        'date_time_1' => [
            'type' => 'date',
            'saveformat' => 'd.m.Y',
        ],
        'date_time_2' => [
            'type' => 'date',
            'saveformat' => 'd/m/Y',
        ],
        'date_time_3' => [
            'type' => 'date',
            'saveformat' => 'H:i',
        ],
        'date_time_4' => [
            'type' => 'date',
            'saveformat' => false, // session date format.
        ],
        //No format specified, save using the session date format.
        'date_time_5' => [
            'type' => 'date',
        ],
    ];

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

    /**
     * Test that date time settings are saved using a
     * format specified in datetimesaveformat or if
     * not set, they are saved using
     * the session date format.
     */
    public function testGetAndSetDateTimeSettings(): void
    {
        self::$plugin->setSettings(self::$dateTimePluginSettings);
        \Yii::app()->session['dateformat'] = 6;
        $sessionFormatDate = getDateFormatData(App()->session['dateformat']);
        /* format sent by widget */
        $sendformat = $sessionFormatDate['phpdate'] . ' H:i';
        /* Check with each settings with current datetime */
        foreach (self::$dateTimePluginSettings as $setting => $settingDatas) {
            /* Set the value to current date */
            $value = date_create()->format($sendformat);
            self::$plugin->setSetting($setting, $value);

            $DBsetting = \PluginSetting::model()->findByAttributes([
                'plugin_id' => self::$plugin->getId(),
                'key' => $setting
            ]);

            $format = !empty($settingDatas['saveformat']) ? $settingDatas['saveformat'] : $sessionFormatDate['phpdate'] . ' H:i';
            $date = \LimeSurvey\PluginManager\LimesurveyApi::getFormattedDateTime($value, $format);

            $this->assertNotEmpty($DBsetting->id, 'The setting id is empty, there must be a problem while saving ' . $value);
            $this->assertEquals($DBsetting->value, json_encode($date), 'The value returned in the PluginSetting object after saving is not correct for ' . $setting . '.');

            $settingValue = self::$plugin->getSetting($setting);
            $this->assertEquals($settingValue, $date, 'The value returned by the get function is not correct ' . $setting . '.');
        }
    }
}
