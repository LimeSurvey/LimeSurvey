<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;
use LimeSurvey\ObjectPatch\{
    Op\OpStandard,
    OpHandler\OpHandlerException
};
use ls\tests\TestBaseClass;
use ls\tests\unit\services\SurveyAggregateService\GeneralSettings\GeneralSettingsMockSetFactory;

class OpHandlerSurveyUpdateTest extends TestBaseClass
{
    public function testSurveyUpdateThrowsNoValuesException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $op = $this->getOp($this->getPropsNoValues());
        $this->getOpHandler()->handle($op);
    }

    public function testSurveyUpdateCanHandle()
    {
        $op = $this->getOp($this->getPropsValid());
        self::assertTrue($this->getOpHandler()->canHandle($op));
    }

    public function testSurveyUpdateCanNotHandleCreate()
    {
        $op = $this->getOp($this->getPropsValid(), 'create');
        self::assertFalse($this->getOpHandler()->canHandle($op));
    }

    private function getOp($props = [], $type = 'update')
    {
        return OpStandard::factory(
            'survey',
            $type,
            12345,
            $props,
            [
                'id' => 123456,
            ]
        );
    }

    private function getPropsValid()
    {
        return [
            'expires' => '2020-01-01 00:00',
            'ipanonymize' => true,
        ];
    }

    private function getPropsNoValues()
    {
        return [
            'xxx' => '2020-01-01 00:00',
            'yyy' => true,
        ];
    }

    /**
     * @return OpHandlerSurveyUpdate
     */
    private function getOpHandler()
    {
        $mockSet = (new GeneralSettingsMockSetFactory())->make();
        return new OpHandlerSurveyUpdate(
            $mockSet->modelSurvey,
            new TransformerInputSurvey()
        );
    }
}
