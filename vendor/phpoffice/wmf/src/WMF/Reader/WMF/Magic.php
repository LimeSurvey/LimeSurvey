<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader\WMF;

use PhpOffice\WMF\Reader\MagicTrait;
use PhpOffice\WMF\Reader\WMF\Imagick as ImagickReader;

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

    public function isWMF(): bool
    {
        $backend = $this->getBackend();

        return $backend instanceof ReaderInterface && $backend->isWMF();
    }
}
