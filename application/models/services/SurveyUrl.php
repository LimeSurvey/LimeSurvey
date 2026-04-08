<?php

namespace LimeSurvey\Models\Services;

/**
 * This class creates a valid survey url to run the survey.
 * It includes the feature to use an alias for the survey.
 */
class SurveyUrl
{
    /**
     * @var string
     */
    private $language;

    /**
     * @var array
     */
    private $urlParams;

    /**
     * @var bool
     */
    private $preferShortUrl;


    /**
     * Create a SurveyUrl configured with a target language, extra URL parameters, and a short-URL preference.
     *
     * @param string $language Language code used when generating URLs.
     * @param array $params Optional additional query/path parameters to include in generated URLs.
     * @param bool $preferShortUrl When true, prefer generating alias (short) URLs when an alias is available.
     */
    public function __construct(string $language, array $params = [], bool $preferShortUrl = true)
    {
        $this->language = $language;
        $this->urlParams = $params;
        $this->preferShortUrl = $preferShortUrl;
    }

    /**
     * Generate a survey run URL for the configured language, using the survey alias when preferred and available.
     *
     * @param int $surveyId The survey identifier.
     * @param \SurveyLanguageSetting[] $surveyLanguageSettings Array of language-specific survey settings used to detect alias collisions.
     * @param string|null $alias The survey alias for this language, or null if none.
     * @return string The absolute URL to access the survey.
     */
    public function getUrl(int $surveyId, $surveyLanguageSettings, string $alias = null)
    {
        if ($this->preferShortUrl && $alias !== null) {
            return $this->createURLWithAlias($alias, $surveyLanguageSettings);
        }

        // If short url is not preferred or no alias is found, return a traditional URL
        $urlParams = array_merge($this->urlParams, ['sid' => $surveyId, 'lang' => $this->language]);
        return \Yii::app()->createAbsoluteUrl('survey/index', $urlParams);
    }

    /**
     * Builds an absolute survey URL from a short alias and ensures the language parameter is included when another language uses the same alias.
     *
     * Iterates provided language settings to detect alias collisions; if a different language shares the alias, the current language is added to URL parameters. Then constructs the final URL according to the application's URL format and returns it.
     *
     * @param string $alias The survey alias (short URL segment).
     * @param \SurveyLanguageSetting[] $surveyLanguageSettings Array of language settings keyed by language code.
     * @return string The assembled absolute URL for the survey. */
    private function createURLWithAlias(string $alias, $surveyLanguageSettings)
    {
        foreach ($surveyLanguageSettings as $otherLang => $settings) {
            if ($otherLang == $this->language || empty($settings->surveyls_alias)) {
                continue;
            }
            if ($settings->surveyls_alias == $alias) {
                $this->urlParams['lang'] = $this->language;
                break;
            }
        }

        // Create the URL according to the configured format
        $urlManager = \Yii::app()->getUrlManager();
        $urlFormat = $urlManager->getUrlFormat();
        if ($urlFormat == \CUrlManager::GET_FORMAT) {
            $url = \Yii::app()->getBaseUrl(true);
            $this->urlParams = [$urlManager->routeVar => $alias] + $this->urlParams;
        } else {
            $url = \Yii::app()->getBaseUrl(true) . '/' . $alias;
        }
        $query = $urlManager->createPathInfo($this->urlParams, '=', '&');
        if (!empty($query)) {
            $url .= "?" . $query;
        }
        return $url;
    }
}
