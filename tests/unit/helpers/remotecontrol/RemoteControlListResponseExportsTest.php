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
