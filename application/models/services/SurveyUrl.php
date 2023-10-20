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
     * Initialise class variables.
     *
     * @param string $language
     * @param array $params Optional parameters to include in the URL.
     * @param bool $preferShortUrl If true, tries to return the short URL instead of the traditional one.
     */
    public function __construct(string $language, array $params = [], bool $preferShortUrl = true)
    {
        $this->language = $language;
        $this->urlParams = $params;
        $this->preferShortUrl = $preferShortUrl;
    }

    /**
     * Returns the survey URL with the specified params.
     * If $preferShortUrl is true (default) and an alias is available, it returns the short
     * version of the URL.
     *
     * @param int $surveyId the survey id
     * @param \SurveyLanguageSetting[] $surveyLanguageSettings
     * @param string|null $alias the survey alias for this specific language
     *
     * @return string
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
     * Check if there is another language with the same alias. If it does, we need to include
     * the 'lang' parameter in the URL.
     *
     * @param string $alias
     * @param \SurveyLanguageSetting[] $surveyLanguageSettings
     * @return string
     */
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
