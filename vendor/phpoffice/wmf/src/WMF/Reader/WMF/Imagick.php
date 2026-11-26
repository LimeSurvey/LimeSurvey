<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader\WMF;

use PhpOffice\WMF\Reader\ImagickTrait;

class Imagick extends ReaderAbstract
{
    use ImagickTrait;

    public function isWMF(): bool
    {
        return $this->isImagickFormat();
    }
}
