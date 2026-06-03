<?php

namespace ReactEditor;

/**
 * Provides the slide definitions for the editor version modal slider.
 * Each slide contains: image, title and description.
 */
class EditorSlides
{
    /**
     * @return array<int, array{image: string, title: string, description: string}>
     */
    public static function getSlides(): array
    {
        $baseUrl = \Yii::app()->baseUrl;

        return [
            [
                'image'       => $baseUrl . '/assets/images/new_editor_image.png',
                'title'       => gT('Easy survey building'),
                'description' => gT(
                    'Create and organize questions effortlessly, and create a survey in minutes.'
                ),
            ],
            [
               'image'       => $baseUrl . '/assets/images/new_editor_image_2.jpg',
               'title'       => sprintf(
                    gT('AI %sBeta%s'),
                    "<span class='editor-slider-beta-badge'> ",
                    "</span>"
                ),
                'description' => sprintf(
                        '%s ' . gT('Create surveys faster and smarter with built-in AI.') . '<br/>' . gT('Rewrite, polish, or improve any question for clean and sharp survey content in a matter of seconds.'),                    sprintf(
                        "<div class='ai-helper-hint-wrapper'><img src='%s/assets/images/pencil_ai.svg' class='icon' /><span class='text'>%s</span></div><br/>", 
                        $baseUrl, 
                        gT('Type “/” to open AI helper menu')
                    )
                ),
            ],
        ];
    }
}
