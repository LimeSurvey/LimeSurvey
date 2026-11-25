<?php

namespace LimeSurvey\Models\Services;

/**
 * Provides question attribute definitions from question types
 */
class HiddenQuestionAttributeProvider extends QuestionAttributeProvider
{
    /**
     * Hidden attributes that only exist in some question themes, with whether
     * they are translated. The new editor sets them as JSON objects: The image
     * settings and alt texts per answer option or subquestion, keyed by the
     * option code, and the image settings that images added later get
     * (image_option_defaults, one for all languages).
     */
    private const THEME_ATTRIBUTES = [
        'image_select-listradio' => [
            'image_option_settings' => true,
            'image_option_alt' => true,
            'image_option_defaults' => false,
        ],
        'image_select-multiplechoice' => [
            'image_option_settings' => true,
            'image_option_alt' => true,
            'image_option_defaults' => false,
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
            $this->getThemeAttributes(self::getQuestionTheme($options))
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
     * The values are JSON objects, so they are not XSS filtered: HTMLPurifier
     * would entity-encode the texts and the expression filter would break the
     * JSON on curly braces. The theme templates validate every number and
     * escape the alt text on output instead.
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
            $attributes[$attributeName]['xssfilter'] = false;
        }
        return $attributes;
    }
}
