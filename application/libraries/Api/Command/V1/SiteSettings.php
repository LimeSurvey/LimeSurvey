<?php

namespace LimeSurvey\Api\Command\V1;

use AdminTheme;
use Yii;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};

/**
 * Site Settings
 *
 * Site settings are different from global settings
 * because this endpoint is public.
 */
class SiteSettings implements CommandInterface
{
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run settings command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        return $this->responseFactory
            ->makeSuccess([
                'siteName' => Yii::app()->getConfig('sitename'),
                'timezone' => date_default_timezone_get(),
                'languages' => getLanguageDataRestricted(),
                'dateFormats' => getDateFormatData(),
                'maximumFileUploadSize' => getMaximumFileUploadSize(),
                // The global admin theme; the editor uses a user's personal admin theme setting instead if there is one,
                // and loads the theme tokens (the colours as CSS variables) of that theme
                'adminTheme' => Yii::app()->getConfig('admintheme'),
                'adminThemeTokens' => AdminTheme::getThemeTokensUrls(),
            ]);
    }
}
