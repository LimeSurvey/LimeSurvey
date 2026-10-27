<?php

namespace ls\tests\unit\models;

use ls\tests\TestBaseClass;
use SettingsUser;
use LsDefaultDataSets;

/**
 * Default and fallback answer option and subquestion prefixes are empty (numeric codes).
 * @group models
 */
class DefaultUserSettingsTest extends TestBaseClass
{
    /**
     * @testdox default user settings seed empty prefixes, kept as empty strings (not null)
     */
    public function testDefaultPrefixesAreEmptyStrings(): void
    {
        $defaults = array_column(LsDefaultDataSets::getDefaultUserSettings(), 'stg_value', 'stg_name');
        $this->assertArrayHasKey('answeroptionprefix', $defaults);
        $this->assertArrayHasKey('subquestionprefix', $defaults);
        $this->assertSame('', $defaults['answeroptionprefix']);
        $this->assertSame('', $defaults['subquestionprefix']);
    }

    /**
     * @testdox a missing prefix setting is not stored and has no AO/SQ fallback in the model
     */
    public function testMissingSettingHasNoLetterFallback(): void
    {
        $this->assertNull(SettingsUser::getUserSettingValue('answeroptionprefix', 999999));
        $question = new \Question();
        $question->sid = 0;
        $this->assertSame('1', $question->getEmptySubquestion()->title);
    }
}
