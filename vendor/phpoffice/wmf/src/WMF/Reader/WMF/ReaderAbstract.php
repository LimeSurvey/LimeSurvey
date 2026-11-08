<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader\WMF;

use PhpOffice\WMF\Reader\ReaderAbstract as ReaderAbstractBase;

abstract class ReaderAbstract extends ReaderAbstractBase implements ReaderInterface
{
    protected function getFormat(): string
    {
        return 'wmf';
    }
}
