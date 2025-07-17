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
     * Run i18n command to fetch all translation data for one language
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $data = [];
        $lang = (string)$request->getData('_id', 'en');
        App()->setLanguage($lang);
        $transLateService = new TranslationMoToJson($lang);
        $translations = $transLateService->translateMoToJson();
        if (is_array($translations) && array_key_exists('', $translations)) {
            unset($translations['']);
        }
        $data['translations'] = $translations;
        $data['languages'] =  getLanguageData();
        return $this->responseFactory
            ->makeSuccess([$data]);
    }
}
