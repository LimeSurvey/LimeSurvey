<?php

namespace ls\tests;

use LimeSurvey\DI;
use LimeSurvey\Models\Services\SurveyActivate;
use Question;
use Survey;
use SurveysGroups;
use SurveysGroupsettings;
use Yii;

/**
 * Encryption method of the survey (#19645) : Basic (fixed nonce) or Hardened (random nonce),
 * inherited from the survey group, not Hardened when a quota uses an encrypted question,
 * fixed when the survey is activated.
 */
class SurveyEncryptionMethodTest extends TestBaseClass
{
    /** @var SurveysGroups */
    private static $surveysGroup;

    /** @var SurveysGroupsettings */
    private static $surveysGroupSettings;

    /** @var Question Question used by the "Europe-quota" */
    private static $quotaQuestion;

    /**
     * Import a survey with quotas, in a survey group using the hardened method.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveysGroup = new SurveysGroups();
        $surveysGroup->name = 'group_19645';
        $surveysGroup->sortorder = 0;
        $surveysGroup->created_by = 1;
        $surveysGroup->title = 'Survey group for issue 19645';
        $surveysGroup->owner_id = 1;
        $surveysGroup->alwaysavailable = 1;
        $surveysGroup->save();
        self::$surveysGroup = $surveysGroup;

        $surveysGroupSettings = new SurveysGroupsettings();
        $surveysGroupSettings->gsid = $surveysGroup->gsid;
        $surveysGroupSettings->owner_id = -1;
        $surveysGroupSettings->encryption_method = 'H';
        $surveysGroupSettings->save();
        self::$surveysGroupSettings = $surveysGroupSettings;

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_376789_quotas.lss');
        self::$quotaQuestion = Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'G02Q03']);
        self::setQuotaQuestionEncrypted(false);
        self::resetSurvey('I');
    }

    /**
     * Delete the test survey, then its survey group.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::$surveysGroupSettings->delete();
        self::$surveysGroup->delete();
    }

    /**
     * Set the survey in the test group with this encryption method, and reload it
     * @param string $encryptionMethod
     * @return Survey
     */
    private static function resetSurvey(string $encryptionMethod): Survey
    {
        Survey::model()->updateByPk(self::$surveyId, ['gsid' => self::$surveysGroup->gsid, 'encryption_method' => $encryptionMethod]);
        Survey::model()->resetCache();
        self::$testSurvey = Survey::model()->findByPk(self::$surveyId);
        return self::$testSurvey;
    }

    /**
     * @param bool $encrypted
     * @return void
     */
    private static function setQuotaQuestionEncrypted(bool $encrypted): void
    {
        Question::model()->updateByPk(self::$quotaQuestion->qid, ['encrypted' => $encrypted ? 'Y' : 'N']);
    }

    /** Basic uses a fixed nonce : the same value always gives the same ciphertext (filter and search). */
    public function testBasicEncryptionIsDeterministic(): void
    {
        $encrypted = \LSActiveRecord::encryptSingle('secret', 'B');

        $this->assertNotSame('secret', $encrypted);
        $this->assertSame($encrypted, \LSActiveRecord::encryptSingle('secret', 'B'));
        $this->assertSame('secret', \LSActiveRecord::decryptSingle($encrypted, 'B'));
    }

    /** Hardened uses a random nonce : the same value never gives the same ciphertext. */
    public function testHardenedEncryptionUsesARandomNonce(): void
    {
        $encrypted = \LSActiveRecord::encryptSingle('secret', 'H');

        $this->assertNotSame('secret', $encrypted);
        $this->assertNotSame($encrypted, \LSActiveRecord::encryptSingle('secret', 'H'));
        $this->assertNotSame(\LSActiveRecord::encryptSingle('secret', 'B'), $encrypted);
        $this->assertSame('secret', \LSActiveRecord::decryptSingle($encrypted, 'H'));
    }

    /** Decryption tries both methods : data encrypted before a method change stay readable. */
    public function testDecryptionTriesBothMethods(): void
    {
        $this->assertSame('secret', \LSActiveRecord::decryptSingle(\LSActiveRecord::encryptSingle('secret', 'B'), 'H'));
        $this->assertSame('secret', \LSActiveRecord::decryptSingle(\LSActiveRecord::encryptSingle('secret', 'H'), 'B'));
    }

    /** A value that can not be decrypted with any method throws, or returns false when asked. */
    public function testWrongValueCanNotBeDecrypted(): void
    {
        $sodium = Yii::app()->sodium;
        $sodium->setEncryptionMethod('H');
        $this->assertFalse($sodium->decrypt('not an encrypted value', true));

        $this->expectException(\SodiumException::class);
        $sodium->decrypt('not an encrypted value');
    }

    /** Inherit (I) uses the method of the survey group. */
    public function testInheritUsesTheMethodOfTheSurveyGroup(): void
    {
        $survey = self::resetSurvey('I');

        $this->assertSame('H', $survey->getResolvedEncryptionMethod());
        $this->assertSame('B', $survey->getResolvedEncryptionMethod('B'), 'The method of the survey is used when set.');
    }

    /** A survey group inheriting too uses the global settings. */
    public function testInheritedGroupUsesTheGlobalSettings(): void
    {
        $survey = self::resetSurvey('I');
        SurveysGroupsettings::model()->updateByPk(self::$surveysGroup->gsid, ['encryption_method' => 'I']);
        $globalSettings = SurveysGroupsettings::model()->findByPk(0);
        $expected = $globalSettings->encryption_method === 'H' ? 'H' : 'B';

        try {
            $this->assertSame($expected, $survey->getResolvedEncryptionMethod());
        } finally {
            SurveysGroupsettings::model()->updateByPk(self::$surveysGroup->gsid, ['encryption_method' => 'H']);
        }
    }

    /** Hardened encrypted answers can not be counted : no Hardened when a quota uses an encrypted question. */
    public function testHardenedIsRefusedWhenAQuotaUsesAnEncryptedQuestion(): void
    {
        self::setQuotaQuestionEncrypted(true);
        $survey = self::resetSurvey('B');
        try {
            $this->assertTrue(\Quota::hasEncryptedQuestion(self::$surveyId));

            $survey->encryption_method = 'H';
            $this->assertFalse($survey->validate(['encryption_method']));
            $this->assertArrayHasKey('encryption_method', $survey->getErrors());

            /* Inherit is refused too : the survey group uses Hardened */
            $survey->clearErrors();
            $survey->encryption_method = 'I';
            $this->assertFalse($survey->validate(['encryption_method']), 'Inherited hardened method must be refused too.');
        } finally {
            self::setQuotaQuestionEncrypted(false);
        }
    }

    /** Without an encrypted question in a quota, Hardened can be used. */
    public function testHardenedIsAllowedWithoutEncryptedQuotaQuestion(): void
    {
        $survey = self::resetSurvey('B');

        $this->assertFalse(\Quota::hasEncryptedQuestion(self::$surveyId));
        $survey->encryption_method = 'H';
        $this->assertTrue($survey->validate(['encryption_method']), json_encode($survey->getErrors()));
    }

    /** An unchanged method is not validated : the other settings of an existing survey can still be saved. */
    public function testUnchangedMethodIsNotValidated(): void
    {
        self::setQuotaQuestionEncrypted(true);
        try {
            $survey = self::resetSurvey('H');
            $this->assertTrue($survey->validate(['encryption_method']));
        } finally {
            self::setQuotaQuestionEncrypted(false);
        }
    }

    /** A survey inheriting Hardened with an encrypted question in a quota is set to Basic (survey import). */
    public function testForceBasicIfHardenedQuota(): void
    {
        self::setQuotaQuestionEncrypted(true);
        try {
            $survey = self::resetSurvey('I');
            $this->assertTrue($survey->forceBasicIfHardenedQuota());
            $this->assertSame('B', Survey::model()->findByPkNoCache(self::$surveyId)->encryption_method);

            /* Basic is not changed */
            $this->assertFalse(self::resetSurvey('B')->forceBasicIfHardenedQuota());
        } finally {
            self::setQuotaQuestionEncrypted(false);
            self::resetSurvey('I');
        }
    }

    /** Surveys inheriting Hardened with an encrypted question in a quota are set to Basic (group or global change). */
    public function testForceBasicForInheritedHardenedQuota(): void
    {
        self::setQuotaQuestionEncrypted(true);
        try {
            self::resetSurvey('I');
            $this->assertSame([(int) self::$surveyId], Survey::forceBasicForInheritedHardenedQuota([self::$surveyId], false));
            $this->assertSame('B', Survey::model()->findByPkNoCache(self::$surveyId)->encryption_method);

            /* Survey not inheriting : not changed */
            self::resetSurvey('H');
            $this->assertSame([], Survey::forceBasicForInheritedHardenedQuota([self::$surveyId], false));
        } finally {
            self::setQuotaQuestionEncrypted(false);
            self::resetSurvey('I');
        }
    }

    /**
     * The activation fixes the inherited method : responses are encrypted with it,
     * a later change of the survey group does not change it.
     * Keep it as the last test : the survey is activated.
     */
    public function testActivationFixesTheInheritedMethod(): void
    {
        self::resetSurvey('I');
        Yii::app()->session['loginID'] = 1;

        $surveyActivate = DI::getContainer()->get(SurveyActivate::class);
        $result = $surveyActivate->activate(self::$surveyId, [], true);
        Yii::app()->db->schema->refresh();

        $this->assertArrayNotHasKey('error', $result, json_encode($result['error'] ?? null));
        $survey = Survey::model()->findByPkNoCache(self::$surveyId);
        self::$testSurvey = $survey;
        $this->assertSame('Y', $survey->active);
        $this->assertSame('H', $survey->encryption_method, 'The inherited method must be saved in the survey.');

        SurveysGroupsettings::model()->updateByPk(self::$surveysGroup->gsid, ['encryption_method' => 'B']);
        try {
            $this->assertSame('H', Survey::model()->findByPkNoCache(self::$surveyId)->getResolvedEncryptionMethod());
        } finally {
            SurveysGroupsettings::model()->updateByPk(self::$surveysGroup->gsid, ['encryption_method' => 'H']);
        }
    }
}
