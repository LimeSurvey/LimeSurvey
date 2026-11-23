<?php

namespace ls\tests;

use LimeSurvey\PluginManager\PasswordSettingMask;

/**
 * Stored plugin passwords must not be sent to the settings form (issue #9056).
 */
class PasswordSettingMaskTest extends TestBaseClass
{
    /**
     * A stored password is replaced by the placeholder, other settings are left alone.
     */
    public function testMaskReplacesStoredPasswordOnly(): void
    {
        $settings = [
            'server' => ['type' => 'string', 'current' => 'ldap.example.org'],
            'bindpwd' => ['type' => 'password', 'label' => 'Password', 'current' => 'stored secret'],
        ];

        $masked = PasswordSettingMask::mask($settings);

        $this->assertSame(PasswordSettingMask::PLACEHOLDER, $masked['bindpwd']['current']);
        $this->assertSame('Password', $masked['bindpwd']['label']);
        $this->assertSame($settings['server'], $masked['server']);
        $this->assertStringNotContainsString('stored secret', json_encode($masked));
    }

    /**
     * Without a stored password the field stays empty, so the form shows that none is set.
     */
    public function testMaskKeepsEmptyPasswordEmpty(): void
    {
        $settings = [
            'emptyString' => ['type' => 'password', 'current' => ''],
            'nullValue' => ['type' => 'password', 'current' => null],
            'noValue' => ['type' => 'password'],
        ];

        $this->assertSame($settings, PasswordSettingMask::mask($settings));
    }

    /**
     * The widget finds its render method without regard to case, so the type must be matched the same way.
     */
    public function testMaskIgnoresCaseOfType(): void
    {
        $masked = PasswordSettingMask::mask(['secret' => ['type' => 'Password', 'current' => 'stored secret']]);

        $this->assertSame(PasswordSettingMask::PLACEHOLDER, $masked['secret']['current']);
    }

    /**
     * A localized setting has one value per language, each stored one is replaced.
     */
    public function testMaskReplacesEachLanguageOfLocalizedPassword(): void
    {
        $masked = PasswordSettingMask::mask([
            'secret' => ['type' => 'password', 'localized' => true, 'current' => ['en' => 'one', 'de' => '']],
        ]);

        $this->assertSame(['en' => PasswordSettingMask::PLACEHOLDER, 'de' => ''], $masked['secret']['current']);
    }

    /**
     * Saving the form without touching the password keeps the stored one.
     */
    public function testRestorePutsStoredPasswordBack(): void
    {
        $settings = [
            'server' => ['type' => 'string', 'current' => 'ldap.example.org'],
            'bindpwd' => ['type' => 'password', 'current' => 'stored secret'],
        ];
        $submitted = ['server' => 'ldap2.example.org', 'bindpwd' => PasswordSettingMask::PLACEHOLDER];

        $this->assertSame(
            ['server' => 'ldap2.example.org', 'bindpwd' => 'stored secret'],
            PasswordSettingMask::restore($submitted, $settings)
        );
    }

    /**
     * A new password and an emptied field are saved as submitted.
     */
    public function testRestoreKeepsChangedAndClearedPassword(): void
    {
        $settings = ['bindpwd' => ['type' => 'password', 'current' => 'stored secret']];

        $this->assertSame(
            ['bindpwd' => 'new secret'],
            PasswordSettingMask::restore(['bindpwd' => 'new secret'], $settings)
        );
        $this->assertSame(['bindpwd' => ''], PasswordSettingMask::restore(['bindpwd' => ''], $settings));
        $this->assertSame(['bindpwd' => null], PasswordSettingMask::restore(['bindpwd' => null], $settings));
    }

    /**
     * The placeholder text in a setting known as another type is a normal value.
     */
    public function testRestoreIgnoresOtherSettingTypes(): void
    {
        $settings = ['server' => ['type' => 'string', 'current' => 'ldap.example.org']];
        $submitted = ['server' => PasswordSettingMask::PLACEHOLDER];

        $this->assertSame($submitted, PasswordSettingMask::restore($submitted, $settings));
    }

    /**
     * The placeholder is never saved as a password: With nothing stored the field stays empty.
     */
    public function testRestoreNeverSavesPlaceholderWithoutStoredPassword(): void
    {
        $submitted = ['bindpwd' => PasswordSettingMask::PLACEHOLDER];

        $this->assertSame(
            ['bindpwd' => ''],
            PasswordSettingMask::restore($submitted, ['bindpwd' => ['type' => 'password', 'current' => '']])
        );
        $this->assertSame(
            ['bindpwd' => null],
            PasswordSettingMask::restore($submitted, ['bindpwd' => ['type' => 'password', 'current' => null]])
        );
    }

    /**
     * If the stored password is not known, the setting is left out so that saving does not touch it.
     */
    public function testRestoreRemovesPlaceholderWithUnknownStoredPassword(): void
    {
        $submitted = ['server' => 'ldap.example.org', 'bindpwd' => PasswordSettingMask::PLACEHOLDER];

        // A password setting without current value
        $this->assertSame(
            ['server' => 'ldap.example.org'],
            PasswordSettingMask::restore($submitted, ['bindpwd' => ['type' => 'password']])
        );
        // A setting without definition could be a password
        $this->assertSame(['server' => 'ldap.example.org'], PasswordSettingMask::restore($submitted, []));
        // Other values of settings without definition are kept
        $this->assertSame(
            ['server' => 'ldap.example.org', 'bindpwd' => 'new secret'],
            PasswordSettingMask::restore(['server' => 'ldap.example.org', 'bindpwd' => 'new secret'], [])
        );
    }

    /**
     * A plugin can hide settings when it returns the current values, like AuthLDAP does for the
     * settings of the other LDAP mode. The password of such a setting must survive a save.
     */
    public function testRestoreWithSettingHiddenByThePlugin(): void
    {
        $definitions = [
            'ldapmode' => ['type' => 'select'],
            'bindpwd' => ['type' => 'password'],
        ];
        $currentSettings = [
            'ldapmode' => ['type' => 'select', 'current' => 'simplebind'],
        ];
        $submitted = ['ldapmode' => 'simplebind', 'bindpwd' => PasswordSettingMask::PLACEHOLDER];

        $this->assertSame(
            ['ldapmode' => 'simplebind'],
            PasswordSettingMask::restore($submitted, array_replace($definitions, $currentSettings))
        );
    }

    /**
     * Each language of a localized password is restored from its own stored value.
     */
    public function testRestoreHandlesLocalizedPassword(): void
    {
        $settings = [
            'secret' => ['type' => 'password', 'localized' => true, 'current' => ['en' => 'one', 'de' => 'two']],
        ];
        $submitted = [
            'secret' => [
                'en' => PasswordSettingMask::PLACEHOLDER,
                'de' => 'changed',
                'fr' => PasswordSettingMask::PLACEHOLDER,
            ],
        ];

        // French has no stored value: Its placeholder is not saved
        $this->assertSame(
            ['secret' => ['en' => 'one', 'de' => 'changed']],
            PasswordSettingMask::restore($submitted, $settings)
        );
    }

    /**
     * What mask() hides, restore() brings back unchanged.
     */
    public function testMaskedValuesSurviveRoundTrip(): void
    {
        $settings = [
            'server' => ['type' => 'string', 'current' => 'ldap.example.org'],
            'bindpwd' => ['type' => 'password', 'current' => 'Test <b>"quoted"</b> & more'],
            'unset' => ['type' => 'password', 'current' => ''],
        ];
        $submitted = array_map(
            function ($setting) {
                return $setting['current'];
            },
            PasswordSettingMask::mask($settings)
        );

        $this->assertSame(
            ['server' => 'ldap.example.org', 'bindpwd' => 'Test <b>"quoted"</b> & more', 'unset' => ''],
            PasswordSettingMask::restore($submitted, $settings)
        );
    }

    /**
     * The placeholder is found on every level of the submitted values.
     */
    public function testContainsPlaceholder(): void
    {
        $this->assertFalse(PasswordSettingMask::containsPlaceholder([]));
        $this->assertFalse(PasswordSettingMask::containsPlaceholder(['a' => 'value', 'b' => ['en' => '']]));
        $this->assertTrue(PasswordSettingMask::containsPlaceholder(['a' => PasswordSettingMask::PLACEHOLDER]));
        $this->assertTrue(
            PasswordSettingMask::containsPlaceholder(['a' => ['en' => PasswordSettingMask::PLACEHOLDER]])
        );
    }
}
