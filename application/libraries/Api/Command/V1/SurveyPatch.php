<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\V1\SurveyPatch\PatcherSurvey;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Auth\CommandAuthInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use DI\FactoryInterface;

class SurveyPatch implements CommandInterface
{
    use AuthPermissionTrait;

    protected CommandAuthInterface $commandAuth;
    protected FactoryInterface $diFactory;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param AuthSession $commandAuth
     * @param FactoryInterface $diFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        CommandAuthInterface $commandAuth,
        FactoryInterface $diFactory,
        ResponseFactory $responseFactory
    ) {
        $this->commandAuth = $commandAuth;
        $this->diFactory = $diFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run survey patch command
     *
     * Apply patch and respond with update patch to be applied to the source (if any).
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $id = (string) $request->getData('_id');
        $patch = $request->getData('patch');

        if (
            !$this->commandAuth
            || !$this->commandAuth
                ->isAuthenticated($request)
        ) {
            return $this->responseFactory
                ->makeErrorUnauthorised();
        }

        $patcher = $this->diFactory->make(
            PatcherSurvey::class
        );
        try {
            $returnedData = $patcher->applyPatch($patch, ['id' => $id]);
        } catch (ObjectPatchException $e) {
            return $this->responseFactory->makeErrorBadRequest(
                $e->getMessage()
            );
        }

        return $this->responseFactory
            ->makeSuccess($returnedData);
    }
}
