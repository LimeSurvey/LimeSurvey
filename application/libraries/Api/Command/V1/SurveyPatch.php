<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\V1\SurveyPatch\PatcherSurvey;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use DI\FactoryInterface;

class SurveyPatch implements CommandInterface
{
    use AuthPermissionTrait;

    protected ?AuthSession $authSession = null;
    protected ?FactoryInterface $diFactory = null;
    protected ?ResponseFactory $responseFactory = null;

    /**
     * Constructor
     *
     * @param FactoryInterface $diFactory
     */
    public function __construct(
        AuthSession $authSession,
        FactoryInterface $diFactory,
        ResponseFactory $responseFactory
    )
    {
        $this->authSession = $authSession;
        $this->diFactory = $diFactory;
        $this->responseFactory = $responseFactory;
    }

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
            (
                $response = $this->authSession
                    ->checkKey($sessionKey)
            ) !== true
        ) {
            return $response;
        }

        $patcher = $this->diFactory->make(
            PatcherSurvey::class,
            ['id' => $id]
        );
        try {
            $patcher->applyPatch($patch);
        } catch (ObjectPatchException $e) {
            return $this->responseFactory->makeErrorBadRequest(
                $e->getMessage()
            );
        }

        return $this->responseFactory
            ->makeSuccess(true);
    }

}
