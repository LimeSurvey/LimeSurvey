<?php

namespace LimeSurvey\Models\Services;

/**
 * Provides question attribute definitions from question types
 */
class HiddenQuestionAttributeProvider extends QuestionAttributeProvider
{
    /**
     * Hidden attributes that only exist in some question themes, with whether
     * they are translated. The new editor sets them per answer option or
     * subquestion: JSON objects keyed by the option code.
     */
    private const THEME_ATTRIBUTES = [
        'image_select-listradio' => [
            'image_option_settings' => true,
            'image_option_alt' => true,
        ],
        'image_select-multiplechoice' => [
            'image_option_settings' => true,
            'image_option_alt' => true,
        ],
    ];

    /**
     * Returns the hidden attribute definitions of a question: The ones of all
     * question types, and the ones of its question theme.
     *
     * @param array<string,mixed> $options the fetcher options ("question", "questionType", "questionTheme")
     * @return array<string,array> The attribute definitions keyed by attribute name
     */
    #[\Override]
    public function getDefinitions($options = [])
    {
        /** @var string question type */
        $questionType = self::getQuestionType($options);
        if (empty($questionType)) {
            return [];
        }

        return array_merge(
            $this->getHiddenAttributes($questionType),
            $this->getThemeAttributes(self::getQuestionThemeName($options))
        );
    }

    /**
     * Returns question attributes from hardcoded array and convert it to array
     * @param string $questionType     the question type to retrieve the attributes for
     * @return array<string,array> The hidden attribute settings for this question type
     */
    protected function getHiddenAttributes($questionType)
    {
        $attributes = [];
        $unallowedQuestionTypes = [];
        $hiddenAttributes = ['image'];
        if (!in_array($questionType, $unallowedQuestionTypes)) {
            foreach ($hiddenAttributes as $hiddenAttribute) {
                $attributes[$hiddenAttribute] = self::getBaseDefinition();
                $attributes[$hiddenAttribute]['name'] = $hiddenAttribute;
            }
        }
        return $attributes;
    }

    /**
     * Returns the hidden attributes of a question theme.
     *
     * @param string $questionThemeName the question theme name, e.g. "image_select-listradio"
     * @return array<string,array> The hidden attribute settings for this question theme, empty for other themes
     */
    protected function getThemeAttributes($questionThemeName)
    {
        $attributes = [];
        foreach (self::THEME_ATTRIBUTES[$questionThemeName] ?? [] as $attributeName => $isTranslated) {
            $attributes[$attributeName] = self::getBaseDefinition();
            $attributes[$attributeName]['name'] = $attributeName;
            $attributes[$attributeName]['i18n'] = $isTranslated;
        }
        return $attributes;
    }

    /**
     * Returns the question theme the attributes are fetched for.
     *
     * @param array<string,mixed> $options the fetcher options: "questionTheme" overrides the theme of "question"
     * @return string The question theme name, or an empty string if unknown
     */
    private static function getQuestionThemeName($options)
    {
        if (!empty($options['questionTheme'])) {
            return (string)$options['questionTheme'];
        }
        if (!empty($options['question'])) {
            return (string)$options['question']->question_theme_name;
        }
        return '';
    }
}
