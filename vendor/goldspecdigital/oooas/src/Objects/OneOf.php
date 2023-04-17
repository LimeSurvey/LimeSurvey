<?php

declare(strict_types=1);

namespace GoldSpecDigital\ObjectOrientedOAS\Objects;

class OneOf extends SchemaComposition
{
    /**
     * @return string
     */
    protected function compositionType(): string
    {
        return 'oneOf';
    }
}
