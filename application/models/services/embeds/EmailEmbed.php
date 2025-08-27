<?php

namespace LimeSurvey\Models\Services\embeds;

use LimeSurvey\Models\Services\embeds\BaseEmbed;
use Survey;

class EmailEmbed extends BaseEmbed
{
    /**
     * Gets the HTML wrapper around the main structure
     * @param string $placeholder a text placeholder with a default value which will be replaced with the inner structure
     * @return string
     */
    protected function getWrapper(string $placeholder = "PLACEHOLDER")
    {
        return "";
    }

    /**
     * Renders the structure with the wrapper wrapped around it
     * @param string $placeholder a text placeholder with a default value which will be replaced with the inner structure
     * @return array|string
     */
    public function render(string $placeholder = "PLACEHOLDER")
    {
        $surveyId = $this->embedOptions['surveyId'] ?? null;
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $survey = Survey::model()->findByPk($surveyId);
        $surveyUrl = $survey->getSurveyUrl();
        $filename = "email_embed_" . $surveyId . ".png";
        $urlPrefix = "{$protocol}://{$host}/";
        $imageUrl = $urlPrefix . "upload/surveys/{$surveyId}/images/{$filename}";
        $logoUrl = $urlPrefix . "assets/images/logo_limesurvey_green.svg";
        $fontUrl = $urlPrefix . "assets/fonts/ibm-plex-sans/ibm-sans.css";
        return sprintf(
            '
            <div style="width:100%%; text-align:center;">
            <div style="margin: 0 40px; border: 1px solid #9094a7; border-radius: 1px">
                <a href="%1$s" target="_blank" >
                    <img src="%2$s" alt="Survey preview" style="max-width: 100%%; height: auto; display: block; margin: 0 auto;" />
                </a>
            </div>
                <a id="limesurvey_embed_branding" href="https://www.limesurvey.org" target="_blank" style="color:inherit; text-decoration:none; display:block; margin-top:8px;">
                    <div style="width:100%%; display:flex; justify-content:center; align-items:end;">
                        <span style="font-size:16px; padding-bottom:4px;">Made with</span>
                        <img src="%3$s" alt="LimeSurvey" style="height:2em; width:auto; display:inline-block; vertical-align:middle; margin-left:8px;" />
                    </div>
                </a>
            </div>
            <link href="%4$s" rel="stylesheet" type="text/css" />',
            $surveyUrl,
            $imageUrl,
            $logoUrl,
            $fontUrl
        );
    }
}
