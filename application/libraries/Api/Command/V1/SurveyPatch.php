<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request
};
use LimeSurvey\Api\Command\Mixin\{
    CommandResponseTrait,
    Auth\AuthSessionTrait,
    Auth\AuthPermissionTrait
};

use LimeSurvey\JsonPatch\Patcher\PatcherSurvey;
use LimeSurvey\JsonPatch\JsonPatchException;

class SurveyPatch implements CommandInterface
{
    use AuthSessionTrait;
    use AuthPermissionTrait;
    use CommandResponseTrait;

    /**
     * Run survey patch command
     *
     * Apply patch and respond with update patch to be applied to the source (if any).
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        $id = (string) $request->getData('_id');
        $patch = $request->getData('patch');

        if (
            ($response = $this->checkKey($sessionKey)) !== true
        ) {
            return $response;
        }

        $patcher = new PatcherSurvey($id);
        try {
            $patcher->applyPatch($patch);
        } catch (JsonPatchException $e) {
            return $this->responseErrorBadRequest(
                $e->getMessage()
            );
        }

        return $this->responseSuccess();
    }

}
