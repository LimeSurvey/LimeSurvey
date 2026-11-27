<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use ls\tests\TestBaseClass;
use LimeSurvey\Models\Services\Exception\PersistErrorException;

class GeneralSettingsLanguageTest extends TestBaseClass
{
    /**
     * Make a mock set with a survey using the given languages
     *
     * @param string $language Base language of the survey
     * @param string $additionalLanguages Space separated additional languages of the survey
     * @return GeneralSettingsMockSet
     */
    private function makeMockSet($language, $additionalLanguages)
    {
        $mockSet = (new GeneralSettingsMockSetFactory())->make();
        $mockSet->survey->setAttributes([
            'sid' => 1,
            'active' => 'N',
            'language' => $language,
            'additional_languages' => $additionalLanguages
        ]);
        return $mockSet;
    }

    /**
     * An unknown base language is refused and the survey keeps its language
     */
    public function testUnknownBaseLanguageIsRefused()
    {
        $mockSet = $this->makeMockSet('en', '');
        $generalSettings = (new GeneralSettingsFactory())->make($mockSet);

        try {
            $generalSettings->update(1, ['language' => 'invalid']);
            $this->fail('PersistErrorException was not thrown');
        } catch (PersistErrorException $e) {
            $this->assertTrue($mockSet->survey->hasErrors('language'));
        }
    }

    /**
     * An empty base language is refused
     */
    public function testEmptyBaseLanguageIsRefused()
    {
        $mockSet = $this->makeMockSet('en', '');
        $generalSettings = (new GeneralSettingsFactory())->make($mockSet);

        $this->expectException(PersistErrorException::class);
        $generalSettings->update(1, ['language' => '']);
    }

    /**
     * An unknown additional language is refused
     */
    public function testUnknownAdditionalLanguageIsRefused()
    {
        $mockSet = $this->makeMockSet('en', '');
        $generalSettings = (new GeneralSettingsFactory())->make($mockSet);

        try {
            $generalSettings->update(1, [
                'language' => 'en',
                'additional_languages' => ['de', 'invalid']
            ]);
            $this->fail('PersistErrorException was not thrown');
        } catch (PersistErrorException $e) {
            $this->assertTrue($mockSet->survey->hasErrors('additional_languages'));
        }
    }

    /**
     * Known languages are saved
     */
    public function testKnownLanguagesAreSaved()
    {
        $mockSet = $this->makeMockSet('en', '');
        $generalSettings = (new GeneralSettingsFactory())->make($mockSet);

        $generalSettings->update(1, [
            'language' => 'de',
            'additional_languages' => ['en', 'pt-BR']
        ]);

        $this->assertEquals('de', $mockSet->survey->language);
        $this->assertEquals('en pt-BR', $mockSet->survey->additional_languages);
    }

    /**
     * Languages the survey already had stay allowed even if they are not known (any more)
     */
    public function testExistingUnknownLanguagesStayAllowed()
    {
        $mockSet = $this->makeMockSet('oldlang', 'en otherold');
        $generalSettings = (new GeneralSettingsFactory())->make($mockSet);

        $generalSettings->update(1, [
            'language' => 'oldlang',
            'additional_languages' => ['en', 'otherold'],
            'admin' => 'Administrator'
        ]);

        $this->assertEquals('oldlang', $mockSet->survey->language);
        $this->assertEquals('en otherold', $mockSet->survey->additional_languages);
        $this->assertEquals('Administrator', $mockSet->survey->admin);
    }
}
