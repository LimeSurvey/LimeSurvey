<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Models\Services\ReactQuestionTypeConfig;

/**
 * Returns the question types of the editor: Every installed question theme, merged with the
 * categories, order and editor-specific titles from react/questiontypes.xml.
 */
class QuestionTypeList implements CommandInterface
{
    protected ResponseFactory $responseFactory;
    protected ReactQuestionTypeConfig $reactQuestionTypeConfig;

    /**
     * Creates the command with its dependencies.
     *
     * @param ResponseFactory $responseFactory Factory of the command response
     * @param ReactQuestionTypeConfig $reactQuestionTypeConfig Reader of react/questiontypes.xml
     */
    public function __construct(
        ResponseFactory $responseFactory,
        ReactQuestionTypeConfig $reactQuestionTypeConfig
    ) {
        $this->responseFactory = $responseFactory;
        $this->reactQuestionTypeConfig = $reactQuestionTypeConfig;
    }

    /**
     * Returns the selector categories and the question types keyed by theme name.
     *
     * @param Request $request The request, it has no parameters
     * @return Response Success response with the categories and question types
     */
    public function run(Request $request): Response
    {
        return $this->responseFactory->makeSuccess([
            'success' => true,
            'categories' => $this->getCategories(),
            'questionTypes' => $this->getQuestionTypes(),
        ]);
    }

    /**
     * Returns the selector categories in order, with translated titles.
     *
     * @return array<int, array{name: string, title: string, icon: string}> Categories with name, title and icon name
     */
    protected function getCategories(): array
    {
        return array_map(function (array $category) {
            $category['title'] = $this->translate($category['title']);
            return $category;
        }, $this->reactQuestionTypeConfig->getCategories());
    }

    /**
     * Returns every question theme of the question_themes table, plus the editor-only types of
     * questiontypes.xml, keyed by theme name.
     *
     * @return array<string, array<string, mixed>> Question types keyed by theme name
     */
    protected function getQuestionTypes(): array
    {
        $overlays = $this->reactQuestionTypeConfig->getThemes();

        $result = [];
        // A theme name is only unique per extended question type: For a name used twice, the core theme wins
        /** @var \QuestionTheme[] $questionThemes */
        $questionThemes = \QuestionTheme::model()->findAll(['order' => 'core_theme DESC, id ASC']);
        foreach ($questionThemes as $questionTheme) {
            $name = (string) $questionTheme->name;
            if (isset($result[$name])) {
                continue;
            }
            $overlay = $overlays[$name] ?? null;
            $settings = json_decode((string) $questionTheme->settings, true);
            $result[$name] = $this->makeQuestionType($name, $overlay, [
                'type' => (string) $questionTheme->question_type,
                'title' => (string) $questionTheme->title,
                'group' => $this->translate((string) $questionTheme->group),
                'extends' => (string) $questionTheme->extends,
                'coreTheme' => (bool) $questionTheme->core_theme,
                'visible' => $questionTheme->visible === 'Y',
                // An object, also when empty, so that JSON has the same shape for every theme
                'settings' => (object) (is_array($settings) ? $settings : []),
                'editorOnly' => false,
            ]);
        }

        foreach ($overlays as $name => $overlay) {
            // Listed themes that are not installed are skipped, unless they are editor-only types
            if (isset($result[$name]) || $overlay['type'] === '') {
                continue;
            }
            $result[$name] = $this->makeQuestionType($name, $overlay, [
                'type' => $overlay['type'],
                'title' => $name,
                'group' => '',
                'extends' => '',
                'coreTheme' => true,
                'visible' => true,
                'settings' => new \stdClass(),
                'editorOnly' => true,
            ]);
        }

        return $result;
    }

    /**
     * Merges the questiontypes.xml entry of a theme into its data: The editor title wins over the theme title.
     *
     * @param string $name Question theme name
     * @param array{category: string, sortOrder: int, title: string, type: string, devOnly: bool}|null $overlay
     * @param array<string, mixed> $data Theme data, with the untranslated theme title
     * @return array<string, mixed> Question type with translated title, category, sort order and dev-only flag
     */
    private function makeQuestionType(string $name, ?array $overlay, array $data): array
    {
        $title = $overlay !== null && $overlay['title'] !== '' ? $overlay['title'] : $data['title'];

        return array_merge(['name' => $name], $data, [
            'title' => $this->translate($title),
            'category' => $overlay['category'] ?? null,
            'sortOrder' => $overlay['sortOrder'] ?? null,
            'devOnly' => $overlay['devOnly'] ?? false,
        ]);
    }

    /**
     * Translates a text for React, which escapes on render. An empty text stays empty
     * (gettext would return the catalog header).
     *
     * @param string $text Text in English, as written in the XML or the question theme
     * @return string Translation into the current language, not HTML-escaped
     */
    private function translate(string $text): string
    {
        return $text === '' ? '' : gT($text, 'unescaped');
    }
}
