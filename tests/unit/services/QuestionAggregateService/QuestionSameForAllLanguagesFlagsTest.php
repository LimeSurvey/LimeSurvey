<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use LimeSurvey\DI;
use LimeSurvey\Models\Services\QuestionAggregateService\QuestionService;
use ls\tests\TestBaseClass;
use Question;

/**
 * An update of a question saves the flags "same script" and "same default values"
 * for all languages as they are sent and keeps the stored value of a flag that isn't sent.
 *
 * @group services
 */
class QuestionSameForAllLanguagesFlagsTest extends TestBaseClass
{
    /**
     * Imports the survey.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_922301_typeChangeOtherConditions.lss');
    }

    /**
     * Uses a new DI container: Other tests replace models like Question or QuestionL10n by mocks in the shared one.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $container = new \ReflectionProperty(DI::class, 'container');
        $container->setValue(null, null);
    }

    /**
     * The flags and their stored values
     *
     * @return array
     */
    public function flagProvider()
    {
        return [
            'same_script on' => ['same_script', 1],
            'same_script off' => ['same_script', 0],
            'same_default on' => ['same_default', 1],
            'same_default off' => ['same_default', 0],
        ];
    }

    /**
     * @testdox An update that doesn't send the flag keeps its stored value
     * @dataProvider flagProvider
     *
     * @param string $flag The flag, same_script or same_default
     * @param int $stored The stored value of the flag
     * @return void
     */
    public function testUpdateKeepsFlagThatIsNotSent($flag, $stored)
    {
        $qid = $this->storeFlag($flag, $stored);

        $this->saveQuestion($qid, ['relevance' => '1']);
        $this->assertSame($stored, (int) $this->getQuestion($qid)->$flag);

        $this->saveQuestion($qid, ['relevance' => '1', $flag => null]);
        $this->assertSame($stored, (int) $this->getQuestion($qid)->$flag, 'A flag sent as null keeps its stored value');
    }

    /**
     * @testdox An update that sends the flag saves the sent value
     * @dataProvider flagProvider
     *
     * @param string $flag The flag, same_script or same_default
     * @param int $stored The stored value of the flag
     * @return void
     */
    public function testUpdateSavesSentFlag($flag, $stored)
    {
        $qid = $this->storeFlag($flag, $stored);
        $sent = $stored ? 0 : 1;

        $this->saveQuestion($qid, [$flag => $sent]);
        $this->assertSame($sent, (int) $this->getQuestion($qid)->$flag);

        $this->saveQuestion($qid, [$flag => $sent]);
        $this->assertSame($sent, (int) $this->getQuestion($qid)->$flag, 'Sending the same value again keeps it');
    }

    /**
     * Stores the value of a flag for question Q1.
     *
     * @param string $flag The flag, same_script or same_default
     * @param int $value The value to store
     * @return int The ID of the question
     */
    private function storeFlag($flag, $value)
    {
        $qid = (int) Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'Q1'])->qid;
        Question::model()->updateByPk($qid, [$flag => $value]);
        $this->assertSame($value, (int) $this->getQuestion($qid)->$flag);
        return $qid;
    }

    /**
     * Updates the question with the question service.
     *
     * @param int $qid The ID of the question
     * @param array $data The question data to send
     * @return void
     */
    private function saveQuestion($qid, array $data)
    {
        DI::getContainer()->get(QuestionService::class)->save([
            'question' => array_merge(['sid' => self::$surveyId, 'qid' => $qid], $data),
        ]);
    }

    /**
     * Loads the question from the database.
     *
     * @param int $qid The ID of the question
     * @return Question The question as stored
     */
    private function getQuestion($qid)
    {
        return Question::model()->findByPk($qid);
    }
}
