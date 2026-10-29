<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use Question;
use Yii;

/**
 * Tests that subquestion codes may only contain letters and digits, also on survey import.
 */
class ImportSubquestionCodeTest extends TestBaseClass
{
    /**
     * Imports the survey whose subquestion codes contain underscores.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.admin.import_helper', true);

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_19401_subquestion_codes.lss');
    }

    /**
     * Subquestion codes with underscores are cleaned up on import.
     */
    public function testInvalidSubquestionCodesAreRenamedOnImport()
    {
        $parent = Question::model()->find('sid = :sid AND parent_qid = 0', [':sid' => self::$surveyId]);
        $titles = array_map(
            function ($subquestion) {
                return $subquestion->title;
            },
            Question::model()->findAll(
                ['condition' => 'parent_qid = :qid', 'order' => 'question_order', 'params' => [':qid' => $parent->qid]]
            )
        );

        $this->assertSame(['AA', 'sq123'], $titles);
    }

    /**
     * Provides subquestion codes and whether they are valid.
     *
     * @return array
     */
    public function subquestionCodeProvider()
    {
        return [
            'letters and digits' => ['AB12', true],
            'digits only'        => ['123', true],
            'underscore'         => ['A_B', false],
            'square brackets'    => ['A[1]', false],
            'backslash'          => ['A\\B', false],
            'caret'              => ['A^B', false],
            'backtick'           => ['A`B', false],
        ];
    }

    /**
     * New subquestion codes may only contain letters and digits.
     *
     * @dataProvider subquestionCodeProvider
     * @param string $code
     * @param bool $valid
     */
    public function testSubquestionCodeValidation($code, $valid)
    {
        $parent = Question::model()->find('sid = :sid AND parent_qid = 0', [':sid' => self::$surveyId]);
        $subquestion = new Question();
        $subquestion->sid = self::$surveyId;
        $subquestion->gid = $parent->gid;
        $subquestion->parent_qid = $parent->qid;
        $subquestion->scale_id = 0;
        $subquestion->title = $code;

        $this->assertSame($valid, $subquestion->validate(['title']), json_encode($subquestion->getErrors()));
    }
}
