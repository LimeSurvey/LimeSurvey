<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use LimeSurvey\Models\Services\TranslationMoToJson;
use Permission;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class I18nRefreshLanguages implements CommandInterface
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
     * Run i18nRefreshLanguages command to fetch all language data translated
     * for one language
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $lang = (string)$request->getData('_id', 'en');
        App()->setLanguage($lang);
        $data =  getLanguageData();
        return $this->responseFactory
            ->makeSuccess($data);
    }
}
