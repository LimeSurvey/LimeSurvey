<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Qr;

use function base64_decode;
use function preg_match;

trait HandlesDataUri
{
    /**
     * @return array<string, string>|null
     */
    private function DecodeDataUri(string $datauri): ?array
    {
        if (preg_match('/data:(?P<mimetype>[\w\.\-\+\/]+);(?P<encoding>\w+),(?P<data>.*)/', $datauri, $m) === 1) {
            return array(
                'mimetype' => $m['mimetype'],
                'encoding' => $m['encoding'],
                'data' => base64_decode($m['data'], true),
            );
        }

        return null;
    }
}
