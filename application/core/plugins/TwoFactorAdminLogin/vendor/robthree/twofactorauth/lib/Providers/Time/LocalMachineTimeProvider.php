<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Time;

class LocalMachineTimeProvider implements ITimeProvider
{
    public function getTime()
    {
        return time();
    }
}
