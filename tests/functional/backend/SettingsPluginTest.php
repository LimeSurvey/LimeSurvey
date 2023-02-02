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

        self::$plugin = App()->getPluginManager()->loadPlugin('SettingsPlugin', $plugin->id);

        //Import survey
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_854771.lss');

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

    /**
     * Testing for settings at a suvey level.
     */
    public function testGetAndSetSurveySetting()
    {

        foreach (self::$settings as $key => $value) {
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

    public function testGetAndSetSettingEncripted()
    {
        self::$plugin->setEncriptedSettings(array_keys(self::$settings));
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
                $this->assertEquals($setting->value, json_encode(LSActiveRecord::encryptSingle(json_encode($value))));
            }

            $settingValue = self::$plugin->getSetting($key);
            $this->assertEquals($settingValue, json_decode(json_encode($value), true));
        }
    }

    /**
     * Testing for encrypted settings at a survey level
     */
    public function testGetAndSetSurveySettingEncripted()
    {
        self::$plugin->setEncriptedSettings(array_keys(self::$settings));
        foreach (self::$settings as $key => $value) {
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
                    'The setting value obtained with the PluginSetting model should be the encripted value previously set'
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
