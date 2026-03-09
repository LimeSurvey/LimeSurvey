<?php

namespace ls\tests;

/**
 * Check filterxsshtml_forcedall and relatyed settings
 * @since 6.17.0
 * @group settings
 */
class XssAndScriptForSuperadminCheckTest extends TestBaseClass
{
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();
        /* be sure to be at default */
        App()->setConfig('forcedsuperadmin', [1]);
        App()->setConfig('filterxsshtml', true);
        App()->setConfig('disablescriptwithxss', true);
        App()->setConfig('filterxsshtml_forcedall', false);
        App()->setConfig('filterxsshtml_allowforcedsuperadmin', false);
        App()->setConfig('filterxsshtml_enablescript', '');
    }

    /**
     * @return void
     */
    public function testXssOnAndScriptOffSuperadmin()
    {
        App()->setConfig('filterxsshtml_forcedall', true);
        App()->setConfig('disablescriptwithxss', true);
        $questionl10n = $this->importSurveyAndgetQuestionI10n();
        $this->assertEquals('Question:', $questionl10n->question);
        $this->assertEquals('Help:', $questionl10n->help);
        $this->assertEquals('', $questionl10n->script);
        self::$testSurvey->delete();
        /* reset to default */
        App()->setConfig('filterxsshtml_forcedall', false);
        App()->setConfig('filterxsshtml_allowforcedsuperadmin', false);
    }

    /**
     * @return void
     */
    public function testXssOnAndScriptOnSuperadmin()
    {
        App()->setConfig('filterxsshtml_forcedall', true);
        App()->setConfig('filterxsshtml_enablescript', 'superadmin');
        $questionl10n = $this->importSurveyAndgetQuestionI10n();
        $this->assertEquals('Question:', $questionl10n->question);
        $this->assertEquals('Help:', $questionl10n->help);
        $this->assertEquals("alert('script');", $questionl10n->script);
        self::$testSurvey->delete();
        /* reset to default */
        App()->setConfig('filterxsshtml_forcedall', false);
        App()->setConfig('filterxsshtml_enablescript', '');
    }

    /**
     * @return void
     */
    public function testXssOffForcedSuperAdmin()
    {
        App()->setConfig('filterxsshtml_forcedall', false);
        App()->setConfig('filterxsshtml_allowforcedsuperadmin', true);
        $questionl10n = $this->importSurveyAndgetQuestionI10n();
        $this->assertEquals("Question:<script>alert('question');</script>", $questionl10n->question);
        $this->assertEquals("Help:<script>alert('help');</script>", $questionl10n->help);
        $this->assertEquals("alert('script');", $questionl10n->script);
        self::$testSurvey->delete();
        /* reset to default */
        App()->setConfig('filterxsshtml_forcedall', false);
        App()->setConfig('filterxsshtml_allowforcedsuperadmin', false);
    }

    /**
     * Import the survey and get the questioni10n
     * return \QuestionL10n
     */
    private function importSurveyAndgetQuestionI10n()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_XssAndScriptCheck.lss';
        self::importSurvey($surveyFile, 1);
        $questions = $this->getAllSurveyQuestions();
        return \QuestionL10n::model()->find(
            'qid = :qid and language = :language',
            [':qid' => $questions['Q00']->qid, ':language' => 'en']
        );
    }
}
