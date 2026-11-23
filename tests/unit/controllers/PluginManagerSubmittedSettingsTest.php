<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;
use PluginManagerController;

/**
 * Saving the settings of a plugin must not touch the settings the form did not show.
 * AuthLDAP hides the settings of the other LDAP mode, saving the form deleted their values.
 */
class PluginManagerSubmittedSettingsTest extends TestBaseClass
{
    /** @var string[] Names of all settings of the plugin */
    private const NAMES = ['ldapmode', 'userprefix', 'binddn', 'bindpwd', 'is_default'];

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.controllers.admin.PluginManagerController', true);
    }

    /**
     * Settings the form did not show are left out, so their stored value is kept.
     */
    public function testSettingsNotShownAreLeftOut(): void
    {
        $post = [
            PluginManagerController::SHOWN_SETTINGS_FIELD => json_encode(['ldapmode', 'userprefix', 'is_default']),
            'ldapmode' => 'simplebind',
            'userprefix' => 'uid=',
            'is_default' => '1',
        ];

        $this->assertSame(
            ['ldapmode' => 'simplebind', 'userprefix' => 'uid=', 'is_default' => '1'],
            PluginManagerController::getSubmittedSettings(self::NAMES, $post)
        );
    }

    /**
     * A setting the form showed without submitting a value, like an unchecked checkbox, is saved as null.
     */
    public function testShownSettingWithoutValueIsSavedAsNull(): void
    {
        $post = [
            PluginManagerController::SHOWN_SETTINGS_FIELD => json_encode(['ldapmode', 'is_default']),
            'ldapmode' => 'simplebind',
        ];

        $this->assertSame(
            ['ldapmode' => 'simplebind', 'is_default' => null],
            PluginManagerController::getSubmittedSettings(self::NAMES, $post)
        );
    }

    /**
     * A value submitted for a setting the form did not show is not saved.
     */
    public function testValueOfSettingNotShownIsIgnored(): void
    {
        $post = [
            PluginManagerController::SHOWN_SETTINGS_FIELD => json_encode(['ldapmode']),
            'ldapmode' => 'simplebind',
            'bindpwd' => 'other',
        ];

        $this->assertSame(
            ['ldapmode' => 'simplebind'],
            PluginManagerController::getSubmittedSettings(self::NAMES, $post)
        );
    }

    /**
     * Only settings of the plugin are returned, whatever the request contains.
     */
    public function testOnlySettingsOfThePluginAreReturned(): void
    {
        $post = [
            PluginManagerController::SHOWN_SETTINGS_FIELD => json_encode(['ldapmode', 'notASetting']),
            'ldapmode' => 'simplebind',
            'notASetting' => 'value',
            'YII_CSRF_TOKEN' => 'token',
        ];

        $this->assertSame(
            ['ldapmode' => 'simplebind'],
            PluginManagerController::getSubmittedSettings(self::NAMES, $post)
        );
    }

    /**
     * Requests that do not tell the shown settings save every setting, as it was before.
     *
     * @dataProvider provideUnusableListsOfShownSettings
     * @param mixed $shownSettings Submitted list of shown settings, null if the request has none
     * @return void
     */
    public function testAllSettingsAreSavedWithoutListOfShownSettings($shownSettings): void
    {
        $post = ['ldapmode' => 'simplebind'];
        if ($shownSettings !== null) {
            $post[PluginManagerController::SHOWN_SETTINGS_FIELD] = $shownSettings;
        }

        $this->assertSame(
            ['ldapmode' => 'simplebind', 'userprefix' => null, 'binddn' => null, 'bindpwd' => null, 'is_default' => null],
            PluginManagerController::getSubmittedSettings(self::NAMES, $post)
        );
    }

    /**
     * The data provider runs before the controller class is loaded, so it only returns the field values.
     *
     * @return array[] Missing or unusable lists of shown settings
     */
    public function provideUnusableListsOfShownSettings(): array
    {
        return [
            'no list' => [null],
            'no JSON' => ['ldapmode,userprefix'],
            'JSON without list' => ['"ldapmode"'],
            'array instead of JSON' => [['ldapmode']],
        ];
    }

    /**
     * Setting names made of digits are array keys of type integer, they must still match the list.
     */
    public function testNumericSettingNames(): void
    {
        $post = [
            PluginManagerController::SHOWN_SETTINGS_FIELD => json_encode([10, '20']),
            '10' => 'a',
            '20' => 'b',
            '30' => 'c',
        ];

        $this->assertSame(
            [10 => 'a', 20 => 'b'],
            PluginManagerController::getSubmittedSettings([10, 20, 30], $post)
        );
    }
}
