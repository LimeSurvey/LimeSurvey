<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use ls\tests\TestBaseClass;

class GeneralSettingsUpdateYnFieldTypeTest extends TestBaseClass
{
    public function testAcceptsY()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'N'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'usecookie' => 'Y'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('Y', $attributes['usecookie']);
    }

    public function testAcceptsN()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'Y'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'usecookie' => 'N'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('N', $attributes['usecookie']);
    }

    public function testAcceptsI()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'Y'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'usecookie' => 'I'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('I', $attributes['usecookie']);
    }

    public function testAcceptsFalsyZero()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'Y'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'usecookie' => 0
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('N', $attributes['usecookie']);
    }

    public function testAcceptsFalsyStringZero()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'Y'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'usecookie' => '0'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('N', $attributes['usecookie']);
    }

    public function testAcceptsFalsyEmpty()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'Y'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'usecookie' => ''
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('N', $attributes['usecookie']);
    }

    public function testAcceptsNoneFalsyOne()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'N'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'usecookie' => 1
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('Y', $attributes['usecookie']);
    }

    public function testAcceptsNoneFalsyStringOne()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->survey->setAttributes([
            'usecookie' => 'N'
        ]);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'usecookie' => '1'
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals('Y', $attributes['usecookie']);
    }
}
