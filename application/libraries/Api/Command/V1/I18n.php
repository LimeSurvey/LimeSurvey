<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Models\Services\TranslationMoToJson;
use Permission;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class I18n implements CommandInterface
{
    use AuthPermissionTrait;

    protected ResponseFactory $responseFactory;
    protected Permission $permission;

    /**
     * Constructor
     *
     * @param ResponseFactory $responseFactory
     * @param Permission $permission
     */
    public function __construct(
        ResponseFactory $responseFactory,
        Permission $permission
    ) {
        $this->responseFactory = $responseFactory;
        $this->permission = $permission;
    }

    /**
     * Run survey detail command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $lang = (string)$request->getData('_id', 'en');

        $translations = [];
        // TODO: here is the place to get all the translations and convert them to the expected format
        $translations[$lang]['Structure'] = 'Structure_' . $lang;
        $transLateService = new TranslationMoToJson($lang);
        $translations[$lang]['translations'] = $transLateService->translateMoToJson();

        return $this->responseFactory
            ->makeSuccess(['surveyTranslations' => $translations]);
    }
}
