<?php

namespace ls\tests;

use LimeSurvey\Helpers\Update\Update_722;

/**
 * DB update 722: The subquestionsactiveedit setting is added to the stored question theme settings.
 */
class Update722Test extends TestBaseClass
{
    /**
     * Returns the decoded settings of a question theme
     * @param string $name
     * @param string $questionType
     * @return array
     */
    private function getSettings(string $name, string $questionType): array
    {
        $settings = \Yii::app()->db->createCommand()
            ->select('settings')
            ->from('{{question_themes}}')
            ->where('name = :name AND question_type = :type', [':name' => $name, ':type' => $questionType])
            ->queryScalar();
        return json_decode((string) $settings, true);
    }

    /**
     * Stores question theme settings without the subquestionsactiveedit setting, like before the update
     * @param string $name
     * @param string $questionType
     * @return void
     */
    private function removeSetting(string $name, string $questionType)
    {
        $settings = $this->getSettings($name, $questionType);
        unset($settings['subquestionsactiveedit']);
        \Yii::app()->db->createCommand()->update(
            '{{question_themes}}',
            ['settings' => json_encode($settings)],
            'name = :name AND question_type = :type',
            [':name' => $name, ':type' => $questionType]
        );
    }

    /**
     * @testdox The bundled ranking themes get subquestionsactiveedit 1, other themes 0
     */
    public function testUpdateAddsSetting()
    {
        $themes = [
            ['ranking', 'R', '1'],
            ['ranking_advanced', 'R', '1'],
            ['multiplechoice', 'M', '0'],
            ['listradio', 'L', '0'],
        ];
        foreach ($themes as [$name, $questionType]) {
            $this->removeSetting($name, $questionType);
        }

        (new Update_722(\Yii::app()->db, []))->up();

        foreach ($themes as [$name, $questionType, $expected]) {
            $settings = $this->getSettings($name, $questionType);
            $this->assertSame($expected, $settings['subquestionsactiveedit'], $name);
            // Inserted after answerscales, like the XML loader does
            $keys = array_keys($settings);
            $this->assertSame('subquestionsactiveedit', $keys[array_search('answerscales', $keys) + 1], $name);
        }
    }

    /**
     * @testdox The bundled question themes get the new versions of their XML files
     */
    public function testUpdateSetsBundledThemeVersions()
    {
        $db = \Yii::app()->db;
        $db->createCommand()->update('{{question_themes}}', ['version' => '1.0'], "name = 'ranking' AND question_type = 'R'");
        $db->createCommand()->update('{{question_themes}}', ['version' => '1.1'], "name = 'bootstrap_buttons_multi' AND question_type = 'M'");

        (new Update_722($db, []))->up();

        $getVersion = function (string $name, string $questionType) use ($db) {
            return $db->createCommand()
                ->select('version')
                ->from('{{question_themes}}')
                ->where('name = :name AND question_type = :type', [':name' => $name, ':type' => $questionType])
                ->queryScalar();
        };
        $this->assertSame('1.1.0', $getVersion('ranking', 'R'));
        $this->assertSame('1.2.0', $getVersion('bootstrap_buttons_multi', 'M'));
    }

    /**
     * @testdox An existing subquestionsactiveedit setting is kept
     */
    public function testUpdateKeepsExistingSetting()
    {
        $settings = $this->getSettings('multiplechoice', 'M');
        $settings['subquestionsactiveedit'] = '1';
        \Yii::app()->db->createCommand()->update(
            '{{question_themes}}',
            ['settings' => json_encode($settings)],
            'name = :name AND question_type = :type',
            [':name' => 'multiplechoice', ':type' => 'M']
        );
        try {
            (new Update_722(\Yii::app()->db, []))->up();
            $this->assertSame('1', $this->getSettings('multiplechoice', 'M')['subquestionsactiveedit']);
        } finally {
            $settings['subquestionsactiveedit'] = '0';
            \Yii::app()->db->createCommand()->update(
                '{{question_themes}}',
                ['settings' => json_encode($settings)],
                'name = :name AND question_type = :type',
                [':name' => 'multiplechoice', ':type' => 'M']
            );
        }
    }
}
