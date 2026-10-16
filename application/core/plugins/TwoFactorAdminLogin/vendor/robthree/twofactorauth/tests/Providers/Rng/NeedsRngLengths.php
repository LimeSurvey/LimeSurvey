<?php

declare(strict_types=1);

namespace Tests\Providers\Rng;

trait NeedsRngLengths
{
    /** @var array<int> */
    protected $rngTestLengths = array(1, 16, 32, 256);
}
