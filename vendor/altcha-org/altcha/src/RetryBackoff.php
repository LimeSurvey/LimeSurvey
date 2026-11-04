<?php

namespace AltchaOrg\Altcha;

enum RetryBackoff
{
    case Fixed;
    case Exponential;
}
