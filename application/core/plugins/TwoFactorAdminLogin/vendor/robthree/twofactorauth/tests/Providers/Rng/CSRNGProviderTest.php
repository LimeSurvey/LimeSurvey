<?php

declare(strict_types=1);

namespace Tests\Providers\Rng;

use PHPUnit\Framework\TestCase;
use RobThree\Auth\Providers\Rng\CSRNGProvider;

class CSRNGProviderTest extends TestCase
{
    use NeedsRngLengths;

    /**
     * @requires function random_bytes
     */
    public function testCSRNGProvidersReturnExpectedNumberOfBytes(): void
    {
        if (function_exists('random_bytes')) {
            $rng = new CSRNGProvider();
            foreach ($this->rngTestLengths as $l) {
                $this->assertSame($l, strlen($rng->getRandomBytes($l)));
            }
            $this->assertTrue($rng->isCryptographicallySecure());
        } else {
            $this->expectNotToPerformAssertions();
        }
    }
}
