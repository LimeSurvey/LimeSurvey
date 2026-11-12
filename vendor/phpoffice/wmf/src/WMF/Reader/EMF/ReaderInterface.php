<?php

namespace PhpOffice\WMF\Reader\EMF;

use PhpOffice\WMF\Reader\ReaderInterface as ReaderInterfaceBase;

interface ReaderInterface extends ReaderInterfaceBase
{
    public function isEMF(): bool;
}
