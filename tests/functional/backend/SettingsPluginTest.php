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
    protected static $settingsValue = [];
    /* @var array[] : date time settings of plugin  */
    protected static $dateTimePluginSettings = [
        'date_time_type1' => [
            'type' => 'date',
            'saveformat' => 'd.m.Y',
        ],
        'date_time_type5' => [
            'type' => 'date',
            'saveformat' => 'd/m/Y',
        ],
        'date_time_time' => [
            'type' => 'date',
            'saveformat' => 'H:i',
        ],
        'date_time_year' => [
            'type' => 'date',
            'saveformat' => 'Y',
        ],
        'date_time_false' => [
            'type' => 'date',
            'saveformat' => false, // session date format.
        ],
        //No format specified, save using the session date format.
        'date_time_null' => [
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
        require_once __DIR__ . "/../../data/plugins/SettingsPlugin.php";
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

        \Yii::app()->session['dateformat'] = 6;

        self::$plugin = App()->getPluginManager()->loadPlugin('SettingsPlugin', $plugin->id);

        //Import survey
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_854771.lss');

        $obj = new \stdClass();
        $obj->customProperty = 'abc';

        self::$settingsValue = [
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

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        //Clear up database.
        \PluginSetting::model()->deleteAllByAttributes(
            array('plugin_id' => self::$plugin->getId())
        );
    }

    public function testGetAndSetSetting()
    {
        foreach (self::$settingsValue as $key => $value) {
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

    /**
     * Testing for settings at a suvey level.
     */
    public function testGetAndSetSurveySetting()
    {
        foreach (self::$settingsValue as $key => $value) {
            self::$plugin->setSurveySetting($key, $value, self::$surveyId);

            $setting = \PluginSetting::model()->findByAttributes([
                'plugin_id' => self::$plugin->getId(),
                'model' => 'Survey',
                'model_id' => self::$surveyId,
                'key' => $key
            ]);

            $this->assertNotEmpty($setting->id, 'Setting id should not be empty.');
            $this->assertEquals(
                $setting->value,
                json_encode($value),
                'The setting value obtained with the PluginSetting model should be the value previously set'
            );

            $settingValue = self::$plugin->getSurveySetting($key, self::$surveyId);
            $this->assertEquals(
                $settingValue,
                json_decode(json_encode($value), true),
                'The setting value obtained with the get function should be the value previously set'
            );
        }
    }

    public function testGetAndSetSettingEncrypted()
    {
        self::$plugin->setEncryptedSettings(array_keys(self::$settingsValue));
        foreach (self::$settingsValue as $key => $value) {
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
        $sessionFormatDate = getDateFormatData(App()->session['dateformat']);
        /* format sent by widget */
        $sendformat = $sessionFormatDate['phpdate'] . ' H:i';
        /* Check with each settings with current datetime */
        foreach (self::$dateTimePluginSettings as $pluginSetting => $settingDatas) {
            /* Set the value to current date */
            $value = date_create()->format($sendformat);
            self::$plugin->setSetting($pluginSetting, $value);

            $setting = \PluginSetting::model()->findByAttributes([
                'plugin_id' => self::$plugin->getId(),
                'key' => $pluginSetting
            ]);

            $format = !empty($settingDatas['saveformat']) ? $settingDatas['saveformat'] : $sessionFormatDate['phpdate'] . ' H:i';
            $date = \LimeSurvey\PluginManager\LimesurveyApi::getFormattedDateTime($value, $format);

            $this->assertNotEmpty(
                $setting->id,
                'The setting id is empty, there must be a problem while saving ' . $value
            );
            $this->assertEquals(
                $setting->value,
                json_encode($date),
                'The value returned in the PluginSetting object after saving is not correct for ' . $pluginSetting . '.'
            );

            $settingValue = self::$plugin->getSetting($pluginSetting);
            $this->assertEquals(
                $settingValue,
                $date,
                'The value returned by the get function is not correct for ' . $pluginSetting . '.'
            );
        }
    }

    public function testSaveDateTimePluginSettingWithoutASpecificFormat()
    {
        $myDateSettings = array(
            'my_default_date' => [
                'type' => 'date',
            ],
            'my_default_date_also' => [
                'type' => 'date',
                'saveformat' => false, // session date format.
            ]
        );

        self::$plugin->setSettings($myDateSettings);

        //Set a date time value.
        self::$plugin->setSetting('my_default_date', '2023-03-17 12:35:42');
        self::$plugin->setSetting('my_default_date_also', '2023-03-17 13:36:45');

        //Get the value previously set.
        $savedDefaultDate = self::$plugin->getSetting('my_default_date');
        $savedDefaultDateAlso = self::$plugin->getSetting('my_default_date_also');

        //No format specified, save using the session date format.
        $this->assertSame('2023-03-17 12:35:42', $savedDefaultDate, 'The date time setting was not properly saved.');
        $this->assertSame('2023-03-17 13:36:45', $savedDefaultDateAlso, 'The date time setting was not properly saved.');
    }

    public function testSaveDateTimePluginSettingWithASpecificFormat()
    {
        $myDateSettings = array(
            'my_year' => [
                'type' => 'date',
                'saveformat' => 'Y',
            ],
            'my_time' => [
                'type' => 'date',
                'saveformat' => 'H:i',
            ],
            'my_formatted_date' => [
                'type' => 'date',
                'saveformat' => 'd.m.Y',
            ],
        );

        self::$plugin->setSettings($myDateSettings);

        //Set a date time value.
        self::$plugin->setSetting('my_formatted_date', '2023-03-17 12:35:42');
        self::$plugin->setSetting('my_year', '2023-03-17 12:35:42');
        self::$plugin->setSetting('my_time', '2023-03-17 12:35:42');

        //Get the value previously set.
        $savedFormattedDate = self::$plugin->getSetting('my_formatted_date');
        $savedYear = self::$plugin->getSetting('my_year');
        $savedTime = self::$plugin->getSetting('my_time');

        $this->assertSame('17.03.2023', $savedFormattedDate, 'The date time setting was not saved in the specified format.');
        $this->assertSame('2023', $savedYear, 'The date time setting was not saved in the specified format.');
        $this->assertSame('12:35', $savedTime, 'The date time setting was not saved in the specified format.');
    }

    public function testExpectedDateTimeSettingSaved()
    {
        $myDateSettings = array(
            'my_new_formatted_date' => [
                'type' => 'date',
                'saveformat' => 'd/m/Y',
            ],
        );

        self::$plugin->setSettings($myDateSettings);

        //Set a date time value.
        self::$plugin->setSetting('my_new_formatted_date', '2023-03-20 18:42:30');

        //Get setting from database.
        $pluginSetting = \PluginSetting::model()->findByAttributes([
            'plugin_id' => self::$plugin->getId(),
            'key' => 'my_new_formatted_date'
        ]);

        $settingInDb = $pluginSetting->value;

        $this->assertSame(json_encode('20/03/2023'), $settingInDb);
    }

     /**
     * Testing for encrypted settings at a survey level
     */
    public function testGetAndSetSurveySettingEncrypted()
    {
        self::$plugin->setEncryptedSettings(array_keys(self::$settingsValue));
        foreach (self::$settingsValue as $key => $value) {
            self::$plugin->setSurveySetting($key, $value, self::$surveyId);

            $setting = \PluginSetting::model()->findByAttributes([
                'plugin_id' => self::$plugin->getId(),
                'model' => 'Survey',
                'model_id' => self::$surveyId,
                'key' => $key
            ]);

            $this->assertNotEmpty($setting->id, 'Setting id should not be empty.');
            if (empty($value)) {
                $this->assertEquals(
                    $setting->value,
                    json_encode($value),
                    'The setting value obtained with the PluginSetting model should be the empty value previously set'
                );
            } else {
                $this->assertEquals(
                    $setting->value,
                    json_encode(LSActiveRecord::encryptSingle(json_encode($value))),
                    'The setting value obtained with the PluginSetting model should be the Encrypted value previously set'
                );
            }

            $settingValue = self::$plugin->getSurveySetting($key, self::$surveyId);
            $this->assertEquals(
                $settingValue,
                json_decode(json_encode($value), true),
                'The setting value obtained with the get function should be the value previously set'
            );
        }
    }
}
