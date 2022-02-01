<?php

namespace ls\tests;
use App;

/**
 * Test core : LSMessageSource
 */
class LSMessageSourceTest extends TestBaseClass
{
    public static function setupBeforeClass(): void
    {
        \Yii::import('application.helpers.common_helper', true);
    }

    public function testUpdatedString()
    {
        $sourcemessage = "sourcemessage" . App()->securityManager->generateRandomString(42);
        $translatedmessage = "translatedmessage" . App()->securityManager->generateRandomString(42);
        App()->db->createCommand()->insert('{{source_message}}', array(
            'category' => '',
            'message' => $sourcemessage,
        ));
        $result = App()->db->createCommand()
            ->select("id")
            ->from("{{source_message}}")
            ->where(
                'message=:message',
                array(':message' => $sourcemessage)
            )->queryRow();
        $this->assertCount(1, $result, 'Unable to create source_message row in DB');
        return;
        App()->db->createCommand()->insert('{{message}}', array(
            'id' => intval($result['id']),
            'language' => 'de',
            'translation' => $translatedmessage,
        ));
        $translatedGt = gT($sourcemessage, 'unescaped', 'de');
        /* Delete added translation */
        if(!empty($result['id'])) {
            App()->db->createCommand()->delete(
                '{{source_message}}',
                'id = :id',
                array(":id" => $result['id'])
            );
            App()->db->createCommand()->delete(
                '{{message}}',
                'id = :id',
                array(":id" => $result['id'])
            );
        }
        $this->assertEquals($translatedmessage, $translatedGt, 'Message translation by DB is broken');
     }
}
