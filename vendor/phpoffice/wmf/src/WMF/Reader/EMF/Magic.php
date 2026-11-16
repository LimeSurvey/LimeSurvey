<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader\EMF;

use PhpOffice\WMF\Reader\EMF\Imagick as ImagickReader;
use PhpOffice\WMF\Reader\MagicTrait;

class Magic extends ReaderAbstract
{
    use MagicTrait;

    /**
     * Backends sorted by priority
     *
     * @var array<string>
     */
    protected $backends = [
        ImagickReader::class,
        GD::class,
    ];

    protected function isBackend(string $backend): bool
    {
        return is_a($backend, ReaderInterface::class, true);
    }

    public function isEMF(): bool
    {
        $backend = $this->getBackend();

        return $backend instanceof ReaderInterface && $backend->isEMF();
    }
}
