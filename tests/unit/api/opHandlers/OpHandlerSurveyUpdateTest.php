<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;
use LimeSurvey\DI;
use LimeSurvey\ObjectPatch\{
    ObjectPatchException,
    Op\OpStandard,
    OpHandler\OpHandlerException};
use ls\tests\TestBaseClass;
use ls\tests\unit\services\SurveyAggregateService\GeneralSettings\GeneralSettingsMockSetFactory;

/**
 * @testdox OpHandlerSurveyUpdateTest
 */
class OpHandlerSurveyUpdateTest extends TestBaseClass
{
    /**
     * @testdox can handle update operation
     */
    public function testSurveyUpdateCanHandle()
    {
        $op = $this->getOp($this->getPropsValid());
        self::assertTrue($this->getOpHandler()->canHandle($op));
    }

    /**
     * @testdox can not handle create operation
     */
    public function testSurveyUpdateCanNotHandleCreate()
    {
        $op = $this->getOp($this->getPropsValid(), 'create');
        self::assertFalse($this->getOpHandler()->canHandle($op));
    }

    /**
     * @testdox validation doesn't hit when everything is fine
     */
    public function testOpValidationSuccess()
    {
        $op = $this->getOp(
            $this->getPropsValid()
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertEmpty($validation);
    }

    /**
     * @testdox validation hits on missing survey ID in context
     */
    public function testOpValidationError()
    {
        $op = $this->getOp(
            $this->getPropsValid(),
            'update',
            []
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
    }

    /**
     * @param array $props
     * @param string $type
     * @param array $context
     * @return OpStandard
     * @throws ObjectPatchException
     */
    private function getOp(
        $props = [],
        $type = 'update',
        $context = ['id' => 123456]
    ) {
        return OpStandard::factory(
            'survey',
            $type,
            12345,
            $props,
            $context
        );
    }

    /**
     * @return array
     */
    private function getPropsValid()
    {
        return [
            'expires' => '2020-01-01T00:00',
            'ipanonymize' => true,
        ];
    }

    /**
     * @return array
     */
    private function getPropsInvalid()
    {
        return [
            'expires' => '2020-01-01 00:00',
            'ipanonymize' => true,
            'ownerId' => 'OWNER',
        ];
    }

    /**
     * @return array
     */
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
            DI::getContainer()->get(TransformerInputSurvey::class)
        );
    }
}
