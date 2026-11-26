<?php

/*
 * LimeSurvey
 * Copyright (C) 2013-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Restricts the iteration of a model to the Twig sandbox property allow-list while a Twig template is rendered.
 *
 * CModel implements IteratorAggregate and iterates over all attributes of the model. The Twig sandbox only checks
 * direct property access, so without this restriction the "for" tag, the "merge" filter and every other Twig
 * construct that iterates an object would expose the attributes that are not in the allow-list of the sandbox
 * (for example the bounce account credentials of a survey).
 *
 * Outside of a Twig rendering the iteration is not changed.
 */
trait TwigSandboxIteratorTrait
{
    /**
     * Returns an iterator over the attributes of the model.
     *
     * While a Twig template is rendered, only the attributes allowed by the Twig sandbox for this model are iterated.
     *
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        $renderer = LSETwigViewRenderer::getRenderingRenderer();
        if ($renderer !== null) {
            // CMapIterator takes the array by reference
            $attributes = $renderer->getSandboxedAttributes($this);
            return new CMapIterator($attributes);
        }
        return parent::getIterator();
    }
}
