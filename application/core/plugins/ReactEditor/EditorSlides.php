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
        ];
    }
}
