<?php

declare(strict_types=1);

namespace GoldSpecDigital\ObjectOrientedOAS\Objects;

class AllOf extends SchemaComposition
{
    /**
     * @return string
     */
    protected function compositionType(): string
    {
        return 'allOf';
    }
}
