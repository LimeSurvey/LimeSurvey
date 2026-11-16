<?php

namespace ls\tests\unit\helpers;

use LimeExpressionManager;
use ls\tests\TestBaseClass;

/**
 * @group em
 */
class LimeExpressionManagerReplacementFieldsTest extends TestBaseClass
{
    public function setUp(): void
    {
        parent::setUp();
        LimeExpressionManager::resetTempVars();
    }

    public function tearDown(): void
    {
        LimeExpressionManager::resetTempVars();
        parent::tearDown();
    }

    /**
     * @return array
     */
    private function getTempVars()
    {
        $property = new \ReflectionProperty(LimeExpressionManager::class, 'tempVars');
        $property->setAccessible(true);
        return $property->getValue(LimeExpressionManager::singleton());
    }

    public function testWithReplacementFieldsSetsFieldsDuringCallbackOnly()
    {
        LimeExpressionManager::updateReplacementFields(['GID' => 5, 'GROUPNAME' => 'Group']);

        $result = LimeExpressionManager::withReplacementFields(
            ['QID' => 12, 'GID' => 7],
            function () {
                $tempVars = $this->getTempVars();
                $this->assertSame(12, $tempVars['QID']['code']);
                $this->assertSame(7, $tempVars['GID']['code']);
                return 'result';
            }
        );

        $this->assertSame('result', $result);
        $tempVars = $this->getTempVars();
        $this->assertArrayNotHasKey('QID', $tempVars, 'Field not set before must be removed again');
        $this->assertSame(5, $tempVars['GID']['code'], 'Field set before must be restored');
        $this->assertSame('Group', $tempVars['GROUPNAME']['code'], 'Other fields must not be touched');
    }

    public function testWithReplacementFieldsRestoresFieldsChangedInsideCallback()
    {
        LimeExpressionManager::updateReplacementFields(['GID' => 5]);

        LimeExpressionManager::withReplacementFields(
            ['GID' => 7],
            function () {
                LimeExpressionManager::updateReplacementFields(['GID' => null]);
            }
        );

        $this->assertSame(5, $this->getTempVars()['GID']['code']);
    }

    public function testWithReplacementFieldsRestoresFieldsOnException()
    {
        LimeExpressionManager::updateReplacementFields(['GID' => 5]);

        try {
            LimeExpressionManager::withReplacementFields(
                ['GID' => 7],
                function () {
                    throw new \RuntimeException('test');
                }
            );
            $this->fail('Exception expected');
        } catch (\RuntimeException $e) {
            $this->assertSame('test', $e->getMessage());
        }

        $this->assertSame(5, $this->getTempVars()['GID']['code']);
    }
}
