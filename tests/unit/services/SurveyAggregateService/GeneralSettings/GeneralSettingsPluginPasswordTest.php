<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use ls\tests\TestBaseClass;
use LimeSurvey\PluginManager\PasswordSettingMask;
use LimeSurvey\PluginManager\PluginManager;
use Mockery;

/**
 * The survey settings form shows a placeholder instead of a stored plugin password (issue #9056).
 *
 * @group services
 */
class GeneralSettingsPluginPasswordTest extends TestBaseClass
{
    /** @var string Name of the plugin the tested settings belong to */
    private const PLUGIN = 'PasswordTestPlugin';

    /** @var array[] Events dispatched during the update: Name, target and received settings */
    private $dispatched = [];

    /**
     * An unchanged password field comes back as the placeholder: The plugin gets the stored password.
     */
    public function testPlaceholderIsReplacedByStoredPassword(): void
    {
        $this->updateWithPluginSettings(
            [self::PLUGIN => ['apiUrl' => 'https://example.org', 'apiKey' => PasswordSettingMask::PLACEHOLDER]]
        );

        $this->assertSame(
            ['apiUrl' => 'https://example.org', 'apiKey' => 'stored secret'],
            $this->getSavedSettings()
        );
        // The stored values are asked from the plugin the settings belong to only
        $this->assertSame(self::PLUGIN, $this->getDispatched('beforeSurveySettings')['target']);
    }

    /**
     * A changed password is handed over as submitted, the stored values are not needed for that.
     */
    public function testChangedPasswordIsSavedAsSubmitted(): void
    {
        $this->updateWithPluginSettings(
            [self::PLUGIN => ['apiUrl' => 'https://example.org', 'apiKey' => 'new secret']]
        );

        $this->assertSame(
            ['apiUrl' => 'https://example.org', 'apiKey' => 'new secret'],
            $this->getSavedSettings()
        );
        $this->assertNull($this->getDispatched('beforeSurveySettings'));
    }

    /**
     * An emptied password field removes the password.
     */
    public function testClearedPasswordIsSavedEmpty(): void
    {
        $this->updateWithPluginSettings([self::PLUGIN => ['apiUrl' => 'https://example.org', 'apiKey' => '']]);

        $this->assertSame(['apiUrl' => 'https://example.org', 'apiKey' => ''], $this->getSavedSettings());
    }

    /**
     * The placeholder text in a setting that is no password is a normal value.
     */
    public function testPlaceholderTextInOtherSettingIsKept(): void
    {
        $this->updateWithPluginSettings(
            [self::PLUGIN => ['apiUrl' => PasswordSettingMask::PLACEHOLDER, 'apiKey' => 'new secret']]
        );

        $this->assertSame(
            ['apiUrl' => PasswordSettingMask::PLACEHOLDER, 'apiKey' => 'new secret'],
            $this->getSavedSettings()
        );
    }

    /**
     * If the plugin gives no stored password when the settings are saved, the placeholder
     * is not handed over as the new password: The setting is left out.
     */
    public function testPlaceholderIsRemovedIfPluginReturnsNoStoredPassword(): void
    {
        $this->updateWithPluginSettings(
            [self::PLUGIN => ['apiUrl' => 'https://example.org', 'apiKey' => PasswordSettingMask::PLACEHOLDER]],
            false
        );

        $this->assertSame(['apiUrl' => 'https://example.org'], $this->getSavedSettings());
    }

    /**
     * Run the survey settings update with a plugin manager that plays a plugin
     * having a stored password in its survey settings.
     *
     * @param array $pluginSettings Submitted plugin settings, indexed by plugin name
     * @param boolean $pluginReturnsSettings Whether the plugin returns its settings panel when asked during the save
     * @return void
     */
    private function updateWithPluginSettings(array $pluginSettings, bool $pluginReturnsSettings = true): void
    {
        $this->dispatched = [];

        $mockSet = (new GeneralSettingsMockSetFactory())->make();
        $mockSet->pluginManager = Mockery::mock(PluginManager::class)
            ->makePartial();
        $mockSet->pluginManager->shouldReceive('dispatchEvent')
            ->andReturnUsing(function ($event, $target = []) use ($pluginReturnsSettings) {
                if ($event->getEventName() == 'beforeSurveySettings') {
                    $event->set('surveysettings.7', [
                        'name' => 'OtherPlugin',
                        'settings' => [
                            'apiKey' => ['type' => 'password', 'current' => 'secret of another plugin'],
                        ],
                    ]);
                    $event->set('surveysettings.8', [
                        'name' => $pluginReturnsSettings ? self::PLUGIN : 'YetAnotherPlugin',
                        'settings' => [
                            'apiUrl' => ['type' => 'string', 'current' => 'https://example.com'],
                            'apiKey' => ['type' => 'password', 'current' => 'stored secret'],
                        ],
                    ]);
                }
                $this->dispatched[] = [
                    'name' => $event->getEventName(),
                    'target' => $target,
                    'settings' => $event->get('settings'),
                ];
                return $event;
            });

        (new GeneralSettingsFactory())->make($mockSet)->update(1, ['plugin' => $pluginSettings]);
    }

    /**
     * Returns the first dispatched event with the given name.
     *
     * @param string $eventName
     * @return array|null Name, target and received settings of the event, null if it was not dispatched
     */
    private function getDispatched(string $eventName): ?array
    {
        foreach ($this->dispatched as $event) {
            if ($event['name'] == $eventName) {
                return $event;
            }
        }
        return null;
    }

    /**
     * Returns the settings the plugin received for saving.
     *
     * @return mixed
     */
    private function getSavedSettings()
    {
        $event = $this->getDispatched('newSurveySettings');
        $this->assertNotNull($event, 'The newSurveySettings event was not dispatched');
        $this->assertSame(self::PLUGIN, $event['target']);
        return $event['settings'];
    }
}
