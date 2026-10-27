<?php

namespace ls\tests\unit\helpers;

use LimeSurvey\Helpers\ConfigMergeHelper;
use ls\tests\TestBaseClass;

/**
 * Test merging the user configuration into the internal configuration.
 */
class ConfigMergeHelperTest extends TestBaseClass
{
    /**
     * Page size options set in the user configuration replace the internal ones instead of being appended.
     */
    public function testPageSizeOptionsAreReplaced()
    {
        $internalConfig = [
            'params' => [
                'defaultPageSize' => 10,
                'pageSizeOptions' => [5 => 5, 10 => 10, 20 => 20, 50 => 50],
                'pageSizeOptionsTokens' => [5 => 5, 10 => 10, 25 => 25, 50 => 50],
            ],
        ];
        $userConfig = [
            'params' => [
                'defaultPageSize' => 20,
                'pageSizeOptions' => [10 => 10, 20 => 20, 1000 => 1000],
                'pageSizeOptionsTokens' => [25 => 25, 5000 => 5000],
            ],
        ];

        $result = ConfigMergeHelper::merge($internalConfig, $userConfig);

        $this->assertSame([10 => 10, 20 => 20, 1000 => 1000], $result['params']['pageSizeOptions']);
        $this->assertSame([25 => 25, 5000 => 5000], $result['params']['pageSizeOptionsTokens']);
        $this->assertSame(20, $result['params']['defaultPageSize']);
    }

    /**
     * Without page size options in the user configuration the internal ones are kept, and other settings still merge.
     */
    public function testInternalPageSizeOptionsAreKept()
    {
        $internalConfig = [
            'params' => [
                'pageSizeOptions' => [5 => 5, 10 => 10],
                'pageSizeOptionsTokens' => [5 => 5, 25 => 25],
            ],
            'components' => [
                'db' => ['charset' => 'utf8mb4', 'emulatePrepare' => true],
            ],
        ];
        $userConfig = [
            'params' => [
                'defaultPageSize' => 50,
            ],
            'components' => [
                'db' => ['connectionString' => 'mysql:host=localhost'],
            ],
        ];

        $result = ConfigMergeHelper::merge($internalConfig, $userConfig);

        $this->assertSame([5 => 5, 10 => 10], $result['params']['pageSizeOptions']);
        $this->assertSame([5 => 5, 25 => 25], $result['params']['pageSizeOptionsTokens']);
        $this->assertSame(50, $result['params']['defaultPageSize']);
        $this->assertSame(
            ['charset' => 'utf8mb4', 'emulatePrepare' => true, 'connectionString' => 'mysql:host=localhost'],
            $result['components']['db']
        );
    }
}
