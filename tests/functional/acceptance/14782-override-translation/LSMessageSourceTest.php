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
        parent::setUpBeforeClass();
        self::$testHelper->resetCache();
    }

    public function testUpdatedString()
    {
        $sourcemessage = "sourcemessage" . App()->securityManager->generateRandomString(42);
        $translatedmessage = "translatedmessage" . App()->securityManager->generateRandomString(42);
        $translatedYes = "Yes" . App()->securityManager->generateRandomString(42);

        /* Prefill fr cache and check po only*/
        $yesPoFr = gT('Yes', 'unescaped', 'fr');
        $this->assertNotEquals('Yes', $yesPoFr, 'Translation from po file seems broken');

        /* Add the string to bet tested in translation DB */
        $randomId = $this->addTranslation($sourcemessage, $translatedmessage);
        $this->assertNotEmpty($randomId, 'Unable to create source_message row in DB');
        $yesId = $this->addTranslation('Yes', $translatedYes);
        $this->assertNotEmpty($randomId, 'Unable to create Yes source_message row in DB');

        /* Check with not existing string */
        if (!empty($randomId)) {
            /* get current */
            $translatedDeGt = gT($sourcemessage, 'unescaped', 'de');
            $translatedFrGt = gT($sourcemessage, 'unescaped', 'fr');
            /* Delete added translation */
            $this->deleteTranslation($randomId);
            /* result */
            $this->assertEquals($translatedmessage, $translatedDeGt, 'Message translation by DB is broken');
            $this->assertEquals($sourcemessage, $translatedFrGt, 'Yii Message caching seems broken');
        }

        /* Check with Yes : replace string */
        if (!empty($yesId)) {
            $translatedDeYesGt = gT('Yes', 'unescaped', 'de');
            $translatedFrYesGt = gT('Yes', 'unescaped', 'fr');
            /* Delete added translation */
            $this->deleteTranslation($yesId);
            /* result */
            $this->assertEquals($translatedYes, $translatedDeYesGt, 'Message translation by DB is broken');
            $this->assertEquals($yesPoFr, $translatedFrYesGt, 'Yii Message caching seems broken');
        }
    }

    /**
     * Create de and fr translation in DB and return id of source message
     * @param $source
     * @param $translated
     * @return integer|null
     */
    private function addTranslation($source, $translated)
    {
        App()->db->createCommand()->insert('{{source_message}}', array(
            'category' => '',
            'message' => $source,
        ));
        $result = App()->db->createCommand()
            ->select("id")
            ->from("{{source_message}}")
            ->where(
                'message=:message',
                array(':message' => $source)
            )->queryRow();
        if (empty($result['id'])) {
            return null;
        }
        App()->db->createCommand()->insert('{{message}}', array(
            'id' => intval($result['id']),
            'language' => 'de',
            'translation' => $translated,
        ));
        App()->db->createCommand()->insert('{{message}}', array(
            'id' => intval($result['id']),
            'language' => 'fr',
            'translation' => $translated,
        ));
        return $result['id'];
    }

    /**
     * Delete existing trabnsklation by source id
     * @param $integer
     */
    private function deleteTranslation($sourceId)
    {
        App()->db->createCommand()->delete(
            '{{source_message}}',
            'id = :id',
            array(":id" => $sourceId)
        );
        App()->db->createCommand()->delete(
            '{{message}}',
            'id = :id',
            array(":id" => $sourceId)
        );
    }
}
