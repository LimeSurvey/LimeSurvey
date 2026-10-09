<?php

namespace ls\tests;

use LimeSurvey\PluginManager\PluginEvent;

/**
 * Tests that the model plugin events expose the underlying Yii event as 'modelEvent'
 */
class PluginEventBehaviorTest extends TestBaseClass
{
    /** @var string Name of the global setting used by the tests */
    private const SETTING_NAME = 'PluginEventBehaviorTestSetting';

    /** @var \PluginBase|null */
    private $plugin;

    /**
     * Remove the test plugin subscriptions and the test setting.
     * @return void
     */
    public function tearDown(): void
    {
        if ($this->plugin) {
            $pluginManager = \Yii::app()->getPluginManager();
            $pluginManager->unsubscribe($this->plugin, 'beforeSettingGlobalSave');
            $pluginManager->unsubscribe($this->plugin, 'afterModelSave');
            $this->plugin = null;
        }
        \SettingGlobal::model()->deleteByPk(self::SETTING_NAME);
        parent::tearDown();
    }

    /**
     * A plugin can cancel a save by invalidating the modelEvent in a before*Save event.
     * @return void
     */
    public function testBeforeSaveCanBeCancelledByPlugin()
    {
        $this->plugin = $this->createTestPlugin(function (PluginEvent $event) {
            $modelEvent = $event->get('modelEvent');
            $this->assertInstanceOf(\CModelEvent::class, $modelEvent);
            $modelEvent->isValid = false;
        });
        \Yii::app()->getPluginManager()->subscribe($this->plugin, 'beforeSettingGlobalSave', 'handleEvent');

        $setting = new \SettingGlobal();
        $setting->stg_name = self::SETTING_NAME;
        $setting->stg_value = '1';

        $this->assertFalse($setting->save());
        $this->assertNull(\SettingGlobal::model()->findByPk(self::SETTING_NAME));
    }

    /**
     * Saving still works when plugins do not touch the modelEvent, and after*Save events receive it too.
     * @return void
     */
    public function testAfterSaveReceivesModelEvent()
    {
        $receivedEvents = [];
        $this->plugin = $this->createTestPlugin(function (PluginEvent $event) use (&$receivedEvents) {
            $receivedEvents[] = $event->get('modelEvent');
        });
        \Yii::app()->getPluginManager()->subscribe($this->plugin, 'afterModelSave', 'handleEvent');

        $setting = new \SettingGlobal();
        $setting->stg_name = self::SETTING_NAME;
        $setting->stg_value = '1';

        $this->assertTrue($setting->save());
        $this->assertNotNull(\SettingGlobal::model()->findByPk(self::SETTING_NAME));
        $this->assertCount(1, $receivedEvents);
        $this->assertInstanceOf(\CEvent::class, $receivedEvents[0]);
        $this->assertSame($setting, $receivedEvents[0]->sender);
    }

    /**
     * Create a minimal plugin that forwards its current event to the given callback.
     * @param callable $callback called with the dispatched PluginEvent
     * @return \PluginBase
     */
    private function createTestPlugin(callable $callback)
    {
        $plugin = new class (\Yii::app()->getPluginManager(), null) extends \PluginBase {
            /** @var callable */
            private $callback;

            /**
             * @param callable $callback
             * @return void
             */
            public function setCallback(callable $callback)
            {
                $this->callback = $callback;
            }

            /**
             * @return void
             */
            public function init()
            {
            }

            /**
             * Forward the current plugin event to the callback.
             * @return void
             */
            public function handleEvent()
            {
                call_user_func($this->callback, $this->getEvent());
            }
        };
        $plugin->setCallback($callback);
        return $plugin;
    }
}
