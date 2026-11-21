<?php

/**
 * RenderClass for the Huge free text question.
 * Renders the same textarea as the Long free text question, with its own view.
 * @see RenderLongFreeText
 */
class RenderHugeFreeText extends RenderLongFreeText
{
    /**
     * Get the main view of the question.
     *
     * @return string
     */
    public function getMainView()
    {
        return '/survey/questions/answer/hugefreetext/answer';
    }
}
