<?php

namespace ls\tests\unit\helpers\remotecontrol;

/**
 * Tests for the list_response_exports RemoteControl API call.
 */
class RemoteControlListResponseExportsTest extends BaseTest
{
    /**
     * Make sure the Authdb plugin, which provides the core export formats, is active and loaded.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $plugin = \Plugin::model()->findByAttributes(array('name' => 'Authdb'));
        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = 'Authdb';
        }
        $plugin->active = 1;
        $plugin->save();
        App()->getPluginManager()->loadPlugin('Authdb', $plugin->id);
        // Clear login attempts.
        \Yii::app()->getDb()->createCommand('DELETE FROM {{failed_login_attempts}}')->execute();
    }

    /**
     * An invalid session key must be rejected.
     */
    public function testInvalidSessionKey()
    {
        $result = $this->handler->list_response_exports('invalid-session-key');
        $this->assertSame(array('status' => 'Invalid session key'), $result);
    }

    /**
     * The export formats are listed sorted by type, with plain-text labels and only the API relevant keys.
     */
    public function testListResponseExports()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $this->assertIsString($sessionKey);

        $result = $this->handler->list_response_exports($sessionKey);
        $this->assertArrayNotHasKey('status', $result);
        $this->assertNotEmpty($result);

        $types = array();
        $defaultTypes = array();
        foreach ($result as $exportFormat) {
            $this->assertSame(array('type', 'label', 'tooltip', 'isDefault'), array_keys($exportFormat));
            $this->assertIsString($exportFormat['label']);
            $this->assertStringNotContainsString('<', $exportFormat['label']);
            if ($exportFormat['tooltip'] !== null) {
                $this->assertStringNotContainsString('<', $exportFormat['tooltip']);
            }
            $types[] = $exportFormat['type'];
            if ($exportFormat['isDefault']) {
                $defaultTypes[] = $exportFormat['type'];
            }
        }

        $sortedTypes = $types;
        sort($sortedTypes, SORT_STRING);
        $this->assertSame($sortedTypes, $types, 'Export formats should be sorted by type.');
        $this->assertContains('json', $types);
        $this->assertSame(array('csv'), $defaultTypes, 'CSV should be the only default export format.');
    }

    /**
     * Export labels stay in English without changing the application's current language.
     */
    public function testListResponseExportsUsesEnglish()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $this->assertIsString($sessionKey);

        $app = App();
        $originalLanguage = $app->getLanguage();
        $originalMessages = $app->getMessages();
        // Keep the test independent of installed translation files.
        $messages = $this->getMockBuilder(\CMessageSource::class)
            ->onlyMethods(array('loadMessages'))
            ->getMock();
        $messages->language = 'en';
        $messages->method('loadMessages')->willReturn(array('CSV' => 'CSV auf Deutsch'));

        try {
            $app->setComponent('messages', $messages);
            $app->setLanguage('de');
            $this->assertSame('CSV auf Deutsch', gT('CSV'));

            $result = $this->handler->list_response_exports($sessionKey);
            $formats = array_column($result, null, 'type');
            $this->assertSame('CSV', $formats['csv']['label']);
            $this->assertSame('de', $app->getLanguage());
        } finally {
            $app->setLanguage($originalLanguage);
            $app->setComponent('messages', $originalMessages);
        }
    }

    /**
     * A failing export plugin must not leave the application language set to English.
     */
    public function testListResponseExportsRestoresLanguageOnException()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $this->assertIsString($sessionKey);

        $app = App();
        $originalLanguage = $app->getLanguage();
        $originalPluginManager = $app->getPluginManager();
        $pluginManager = $this->createMock(\LimeSurvey\PluginManager\PluginManager::class);
        $pluginManager->expects($this->once())->method('dispatchEvent')->willReturnCallback(function () {
            $this->assertSame('en', App()->getLanguage());
            throw new \RuntimeException('Export discovery failed');
        });

        try {
            $app->setComponent('pluginManager', $pluginManager);
            $app->setLanguage('de');
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Export discovery failed');
            try {
                $this->handler->list_response_exports($sessionKey);
            } finally {
                $this->assertSame('de', $app->getLanguage());
            }
        } finally {
            $app->setLanguage($originalLanguage);
            $app->setComponent('pluginManager', $originalPluginManager);
        }
    }

    /**
     * HTML meant for the admin GUI is converted to plain text without gluing words together.
     */
    public function testHtmlToPlainText()
    {
        $method = new \ReflectionMethod($this->handler, 'htmlToPlainText');
        $method->setAccessible(true);

        $this->assertSame(
            'Microsoft Excel (Iconv Library not installed)',
            $method->invoke($this->handler, 'Microsoft Excel<font class="warningtitle">(Iconv Library not installed)</font>')
        );
        $this->assertSame(
            'First step. Second step. Fish & "chips"',
            $method->invoke($this->handler, "<ol><li>First step.</li>\n<li>Second step.</li></ol><br>Fish &amp; &quot;chips&quot;")
        );
        $this->assertNull($method->invoke($this->handler, null));
        $this->assertNull($method->invoke($this->handler, ''));
    }
}
