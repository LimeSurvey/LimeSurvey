<?php

namespace LimeSurvey\Models\Services;

use SimpleXMLElement;

/**
 * Reads the editor (React) question type list from application/views/survey/questions/react/questiontypes.xml:
 * The categories of the question type selector and, per question theme, its category, order,
 * editor-specific title and flags.
 */
class ReactQuestionTypeConfig
{
    public const FILE = 'questiontypes.xml';

    private string $file;

    /** @var array<int, array{name: string, title: string, icon: string}>|null */
    private ?array $categories = null;

    /** @var array<string, array{category: string, sortOrder: int, title: string, type: string, devOnly: bool}> */
    private array $themes = [];

    /**
     * Creates a reader for the questiontypes.xml of a directory. The file is read on first use.
     *
     * @param string|null $directory Directory of questiontypes.xml, defaults to the react overlay directory
     */
    public function __construct(?string $directory = null)
    {
        $directory = $directory
            ?? \Yii::app()->getConfig('rootdir') . '/application/views/survey/questions/react';
        $this->file = $directory . '/' . self::FILE;
    }

    /**
     * Returns the categories of the question type selector, in order. Titles are not translated.
     *
     * @return array<int, array{name: string, title: string, icon: string}> Categories with name, title and icon name
     */
    public function getCategories(): array
    {
        $this->load();
        return $this->categories ?? [];
    }

    /**
     * Returns the listed question themes keyed by theme name. Title and type are empty strings when the
     * XML doesn't set them, sortOrder is the position inside the category. Titles are not translated.
     *
     * @return array<string, array{category: string, sortOrder: int, title: string, type: string, devOnly: bool}> Listed themes keyed by theme name
     */
    public function getThemes(): array
    {
        $this->load();
        return $this->themes;
    }

    /**
     * Reads questiontypes.xml once. A missing or malformed file results in an empty list.
     *
     * @return void
     */
    private function load(): void
    {
        if ($this->categories !== null) {
            return;
        }
        $this->categories = [];

        $xml = $this->readXml();
        if ($xml === null || !isset($xml->categories->category)) {
            return;
        }

        foreach ($xml->categories->category as $category) {
            $categoryName = trim((string) $category->name);
            if ($categoryName === '') {
                continue;
            }
            $this->categories[] = [
                'name' => $categoryName,
                'title' => trim((string) $category->title),
                'icon' => trim((string) $category->icon),
            ];
            if (!isset($category->themes->theme)) {
                continue;
            }
            $sortOrder = 0;
            foreach ($category->themes->theme as $theme) {
                $themeName = trim((string) $theme->name);
                // A theme belongs to one category: The first entry wins
                if ($themeName === '' || isset($this->themes[$themeName])) {
                    continue;
                }
                $devOnly = trim((string) $theme->devOnly);
                $this->themes[$themeName] = [
                    'category' => $categoryName,
                    'sortOrder' => $sortOrder++,
                    'title' => trim((string) $theme->title),
                    'type' => trim((string) $theme->type),
                    'devOnly' => $devOnly !== '' && $devOnly !== '0',
                ];
            }
        }
    }

    /**
     * Parses questiontypes.xml without raising libxml warnings (turned into errors by Yii).
     *
     * @return SimpleXMLElement|null Null when the file is missing or malformed
     */
    private function readXml(): ?SimpleXMLElement
    {
        if (!is_file($this->file)) {
            return null;
        }
        $useInternalErrors = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_file($this->file);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($useInternalErrors);
        }

        return $xml === false ? null : $xml;
    }
}
