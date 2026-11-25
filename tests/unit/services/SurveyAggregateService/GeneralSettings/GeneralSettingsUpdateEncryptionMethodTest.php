<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurvey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyDetail;
use LimeSurvey\DI;
use ls\tests\TestBaseClass;
use Question;
use Survey;

/**
 * Encryption method in the survey settings and in the API of the new editor (#19645)
 */
class GeneralSettingsUpdateEncryptionMethodTest extends TestBaseClass
{
    /**
     * @param string $active 'Y' or 'N'
     * @return array{0: \LimeSurvey\Models\Services\SurveyAggregateService\GeneralSettings, 1: Survey}
     */
    private function makeGeneralSettings(string $active): array
    {
        $mockSet = (new GeneralSettingsMockSetFactory())->make();
        $mockSet->survey->setAttributes([
            'active' => $active,
            'encryption_method' => 'B',
        ], false);
        return [(new GeneralSettingsFactory())->make($mockSet), $mockSet->survey];
    }

    /** The method can be changed while the survey is not active. */
    public function testEncryptionMethodCanBeUpdatedWhenNotActive(): void
    {
        [$generalSettings, $survey] = $this->makeGeneralSettings('N');

        $generalSettings->update(1, ['encryption_method' => 'H']);

        $this->assertSame('H', $survey->getAttributes()['encryption_method']);
    }

    /** Responses are encrypted with the method : it is locked once the survey is active. */
    public function testEncryptionMethodIsLockedWhenActive(): void
    {
        [$generalSettings, $survey] = $this->makeGeneralSettings('Y');

        $generalSettings->update(1, ['encryption_method' => 'H']);

        $this->assertSame('B', $survey->getAttributes()['encryption_method']);
    }

    /** The API accepts encryptionMethod B, H or I, saved as encryption_method. */
    public function testApiInputEncryptionMethod(): void
    {
        $transformer = DI::getContainer()->get(TransformerInputSurvey::class);

        $this->assertSame('H', $transformer->transform(['encryptionMethod' => 'H'])['encryption_method'] ?? null);
        $this->assertTrue($transformer->validate(['encryptionMethod' => 'B']));
        $errors = $transformer->validate(['encryptionMethod' => 'X']);
        $this->assertIsArray($errors, 'Only B, H and I are valid.');
        $this->assertArrayHasKey('encryptionMethod', $errors);
    }

    /**
     * The API returns the method of the survey as encryptionMethod, the inherited value (I) resolved,
     * and if Hardened is not available because a quota uses an encrypted question.
     */
    public function testApiOutputEncryptionMethod(): void
    {
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_376789_quotas.lss');
        $quotaQuestion = Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'G02Q03']);
        $detailTransformer = DI::getContainer()->get(TransformerOutputSurveyDetail::class);
        $resolveInherited = new \ReflectionMethod($detailTransformer, 'setInheritedBetaOptions');
        $resolveInherited->setAccessible(true);
        $hardenedDisabled = new \ReflectionMethod($detailTransformer, 'isEncryptionHardenedDisabled');
        $hardenedDisabled->setAccessible(true);

        Survey::model()->updateByPk(self::$surveyId, ['encryption_method' => 'I']);
        Question::model()->updateByPk($quotaQuestion->qid, ['encrypted' => 'N']);
        $survey = $resolveInherited->invoke($detailTransformer, Survey::model()->findByPkNoCache(self::$surveyId));
        $this->assertSame($survey->getResolvedEncryptionMethod('I'), $survey->encryption_method, 'The inherited method is resolved.');
        $output = DI::getContainer()->get(TransformerOutputSurvey::class)->transform($survey);
        $this->assertSame($survey->encryption_method, $output['encryptionMethod']);
        $this->assertFalse($hardenedDisabled->invoke($detailTransformer, Survey::model()->findByPkNoCache(self::$surveyId)));

        Question::model()->updateByPk($quotaQuestion->qid, ['encrypted' => 'Y']);
        Survey::model()->updateByPk(self::$surveyId, ['encryption_method' => 'B']);
        $this->assertTrue(
            $hardenedDisabled->invoke($detailTransformer, Survey::model()->findByPkNoCache(self::$surveyId)),
            'Hardened is not available : a quota uses an encrypted question.'
        );

        /* Already Hardened : kept available */
        Survey::model()->updateByPk(self::$surveyId, ['encryption_method' => 'H']);
        $this->assertFalse($hardenedDisabled->invoke($detailTransformer, Survey::model()->findByPkNoCache(self::$surveyId)));
    }
}
