<?php

namespace LimeSurvey\Models\Services\embeds;

use LimeSurvey\Models\Services\embeds\BaseEmbed;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use QuestionGroup;
use Question;
use LimeSurvey\Models\Services\FileUploadService;
use LimeSurvey\DI;

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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function render(string $placeholder = "PLACEHOLDER")
    {
        // TODO: check if the bypass in surveyIndex for action previewquestion & popuppreview is okay ( line 109 )
        $surveyId = $this->embedOptions['surveyId'] ?? null;
        $oFirstGroup = QuestionGroup::model()->findByAttributes(
            ['sid' => $surveyId],
            ['order' => 'group_order ASC']
        );
        $oFirstQuestion = Question::model()->primary()->findByAttributes(
            ['gid' => $oFirstGroup->gid],
            ['order' => 'question_order ASC']
        );
        $gid = $oFirstGroup->gid;
        $qid = $oFirstQuestion->qid;

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $surveyUrl = "{$protocol}://{$host}/index.php/{$surveyId}";

        $params = [
            'action' => 'previewquestion',
            'sid' => $surveyId,
            'gid' => $gid,
            'qid' => $qid,
            'popuppreview' => 'true'
        ];
        $query = http_build_query(array_filter($params));
        $firstQuestionPreviewUrl = $surveyUrl . '?' . $query;
        try {
            $capabilities = DesiredCapabilities::firefox();
            $options = new FirefoxOptions();
            $options->addArguments(['-headless']);
            $capabilities->setCapability(FirefoxOptions::CAPABILITY, $options);
            $address = getenv('WEBDRIVERHOST') ?: 'firefox'; // firefox is hostname for selenium in ls-docker-dev-env
            $serverUrl = 'http://' . $address . ':4444/wd/hub';
            $driver = RemoteWebDriver::create($serverUrl, $capabilities, 5000); // TODO: check whether we have selenium in cloud servers otherwise use webdriver executable ( gecko )
            $driver->get($firstQuestionPreviewUrl);
            $element = $driver->findElement(WebDriverBy::cssSelector('form#limesurvey'));
            $screenshot = $element->takeElementScreenshot();
            $driver->quit();

            $filename = "email_embed_" . $surveyId . ".png";
            $uploadPath = App()->getConfig( // should we stored in upload directory or somewhere else?
                'uploaddir'
            ) . "/surveys/{$surveyId}/screenshots/";
            $filePath = $uploadPath . $filename;
            if (!is_dir($uploadPath)) {
                @mkdir($uploadPath);
            }
            file_put_contents($filePath, $screenshot);
            $imageUrl = "{$protocol}://{$host}/upload/surveys/{$surveyId}/screenshots/{$filename}";
            $logoUrl = "{$protocol}://{$host}/assets/images/__Limesurvey_logo.png";

            return sprintf(
                '<div style="width:100%%; text-align:center;">
                    <a href="%1$s" target="_blank" style="display:inline-block;">
                        <img src="%2$s" alt="Survey preview" style="max-width:600px; display:block; margin:0 auto;" />
                    </a>
                    <a href="https://www.limesurvey.org" target="_blank" style="color:inherit; text-decoration:none; display:block; margin-top:8px;">
                        <div style="width:100%%; display:flex; justify-content:center; align-items:end;">
                            <span style="font-size:16px; padding-bottom:2px;">Made with</span>
                            <img src="%3$s" alt="LimeSurvey" style="height:2em; width:auto; display:inline-block; vertical-align:middle; margin-left:6px;" />
                        </div>
                    </a>
                </div>',
                $surveyUrl,
                $imageUrl,
                $logoUrl
            );
        } catch (\Exception $e) {
            return 'Error capturing survey preview: ' . $e->getMessage();
        }
    }
}
