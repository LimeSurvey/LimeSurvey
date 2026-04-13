<?php

namespace ls\tests;

class FixMovedQuestionConditions extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();

        \Yii::import('application.helpers.common_helper', true);

        // Import default survey
        $filename = self::$surveysFolder . '/survey_fixMovedQuestionConditionsDefault.lss';
        self::importSurvey($filename);

        \Yii::app()->setConfig('sid', self::$surveyId);
    }

    /**
     * We want to be sure that the cfieldname has the correct
     * group id before and after executing fixMovedQuestionConditions.
     *
     * Testing just one condition.
     *
     * This test uses the survey previously imported in setupBeforeClass.
     */
    public function testFixOneCondition()
    {
        $groups = self::$testSurvey->groups;
        $questions = self::$testSurvey->allQuestions;

        $conditions = \Condition::model()
                            ->getSomeConditions(
                                array('cfieldname'),
                                'cqid = ' . $questions[0]->qid,
                                null,
                                null
                            );

        $firstQuestionConditions = $conditions->readAll();

        $this->assertSame(
            $firstQuestionConditions[0]['cfieldname'],
            'Q' . $questions[0]->qid,
            'The cfieldname field is not correct.'
        );

        // Fix conditions, informing question has been moved from group one to group two.
        fixMovedQuestionConditions($questions[0]->qid, $groups[0]->gid, $groups[1]->gid);

        $fixedConditions = \Condition::model()
                            ->getSomeConditions(
                                array('cfieldname'),
                                'cqid = ' . $questions[0]->qid,
                                null,
                                null
                            );

        $firstQuestionFixedConditions = $fixedConditions->readAll();

        $this->assertSame(
            $firstQuestionFixedConditions[0]['cfieldname'],
            'Q' . $questions[0]->qid,
            'The cfieldname field is not correct : bad group after moving question'
        );
    }

    /**
     * We want to be sure that the cfieldname has the correct
     * group id before and after executing fixMovedQuestionConditions.
     *
     * Testing multiple conditions.
     *
     * This test uses the survey previously imported in setupBeforeClass.
     */
    public function testFixMultipleConditions()
    {
        $groups = self::$testSurvey->groups;
        $questions = self::$testSurvey->allQuestions;

        $conditions = \Condition::model()
                            ->getSomeConditions(
                                array('cfieldname'),
                                'cqid = ' . $questions[2]->qid,
                                null,
                                null
                            );

        $questionConditions = $conditions->readAll();

        $expectedCfieldname = 'Q' . $questions[2]->qid;

        foreach ($questionConditions as $condition) {
            $this->assertSame(
                $expectedCfieldname,
                $condition['cfieldname'],
                'The cfieldname field is not correct.'
            );
        }

        // Fix conditions, informing question has been moved from group three to group four.
        fixMovedQuestionConditions($questions[2]->qid, $groups[2]->gid, $groups[3]->gid);

        $fixedConditions = \Condition::model()
                            ->getSomeConditions(
                                array('cfieldname'),
                                'cqid = ' . $questions[2]->qid,
                                null,
                                null
                            );

        $questionFixedConditions = $fixedConditions->readAll();

        $expectedFixedCfieldname = 'Q' . $questions[2]->qid;

        foreach ($questionFixedConditions as $condition) {
            $this->assertSame(
                $expectedFixedCfieldname,
                $condition['cfieldname'],
                'The cfieldname field is not correct : bad group after moving question'
            );
        }
    }

    /**
     * We want to be sure that the cfieldname has the correct
     * group id before and after executing fixMovedQuestionConditions.
     *
     * Testing just one condition.
     *
     * This test uses a new survey.
     */
    public function testFixOneConditionInNewSurvey()
    {
        // Import new survey
        $filename = self::$surveysFolder . '/survey_fixMovedQuestionConditionsNew.lss';
        self::importSurvey($filename);

        $groups = self::$testSurvey->groups;
        $questions = self::$testSurvey->allQuestions;

        $conditions = \Condition::model()
                            ->getSomeConditions(
                                array('cfieldname'),
                                'cqid = ' . $questions[3]->qid,
                                null,
                                null
                            );

        $firstQuestionConditions = $conditions->readAll();

        $this->assertSame(
            $firstQuestionConditions[0]['cfieldname'],
           'Q' . $questions[3]->qid,
            'The cfieldname field is not correct.'
        );

        // Fix conditions, informing question has been moved from group two to group one.
        fixMovedQuestionConditions($questions[3]->qid, $groups[1]->gid, $groups[0]->gid, self::$surveyId);

        $fixedConditions = \Condition::model()
                            ->getSomeConditions(
                                array('cfieldname'),
                                'cqid = ' . $questions[3]->qid,
                                null,
                                null
                            );

        $firstQuestionFixedConditions = $fixedConditions->readAll();

        $this->assertSame(
            $firstQuestionFixedConditions[0]['cfieldname'],
            'Q' . $questions[3]->qid,
            'The cfieldname field is not correct : bad group after moving question'
        );
    }

    /**
     * We want to be sure that the cfieldname has the correct
     * group id before and after executing fixMovedQuestionConditions.
     *
     * Testing multiple conditions.
     *
     * This test uses the survey imported in the previous test.
     */
    public function testFixMultitpleConditionsInNewSurvey()
    {
        $groups = self::$testSurvey->groups;
        $questions = self::$testSurvey->allQuestions;

        $conditions = \Condition::model()
                            ->getSomeConditions(
                                array('cfieldname'),
                                'cqid = ' . $questions[0]->qid,
                                null,
                                null
                            );

        $questionConditions = $conditions->readAll();

        $expectedCfieldname = 'Q' . $questions[0]->qid;

        foreach ($questionConditions as $condition) {
            $this->assertSame(
                $expectedCfieldname,
                $condition['cfieldname'],
                'The cfieldname field is not correct.'
            );
        }

        // Fix conditions, informing question has been moved from group one to group two.
        fixMovedQuestionConditions($questions[0]->qid, $groups[0]->gid, $groups[1]->gid, self::$surveyId);

        $fixedConditions = \Condition::model()
                            ->getSomeConditions(
                                array('cfieldname'),
                                'cqid = ' . $questions[0]->qid,
                                null,
                                null
                            );

        $questionFixedConditions = $fixedConditions->readAll();

        $expectedFixedCfieldname = 'Q' . $questions[0]->qid;

        foreach ($questionFixedConditions as $condition) {
            $this->assertSame(
                $expectedFixedCfieldname,
                $condition['cfieldname'],
                'The cfieldname field is not correct : bad group after moving question'
            );
        }
    }
}
