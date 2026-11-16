<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader\EMF;

use PhpOffice\WMF\Reader\ImagickTrait;

class Imagick extends ReaderAbstract
{
    use ImagickTrait;

    public function isEMF(): bool
    {
        return $this->isImagickFormat();
    }
}
