<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use ls\tests\TestBaseClass;

/**
 * @group services
 */
class GeneralSettingsTokenLengthTest extends TestBaseClass
{
    /**
     * Test that an out-of-range access code length is passed to the model unchanged,
     * so model validation can reject it instead of it being silently replaced.
     *
     * @return void
     */
    public function testUpdateKeepsOutOfRangeTokenLength()
    {
        $mockSet = (new GeneralSettingsMockSetFactory())->make();

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'tokenlength' => 15,
        ]);

        $generalSettings = (new GeneralSettingsFactory())->make($mockSet);

        $generalSettings->update(1, [
            'tokenlength' => '4'
        ]);

        $this->assertEquals(
            4,
            $mockSet->survey->tokenlength
        );
    }
}
