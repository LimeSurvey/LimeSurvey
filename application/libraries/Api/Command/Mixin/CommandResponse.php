<?php

namespace LimeSurvey\Api\Command\Mixin;

use LimeSurvey\Api\Command\Response\{
    Response,
    Status\StatusSuccess,
    Status\StatusError,
    Status\StatusErrorNotFound,
    Status\StatusErrorBadRequest,
    Status\StatusErrorUnauthorised
};

trait CommandResponse
{
    protected function responseSuccess($data = null): Response
    {
        return new Response(
            $data,
            new StatusSuccess()
        );
    }

    protected function responseError($data = null): Response
    {
        return new Response(
            $data,
            new StatusError()
        );
    }

    protected function responseErrorNotFound($data = null): Response
    {
        return new Response(
            $data,
            new StatusErrorNotFound()
        );
    }

    protected function responseErrorBadRequest($data = null): Response
    {
        return new Response(
            $data,
            new StatusErrorBadRequest()
        );
    }

    protected function responseErrorUnauthorised($data = null): Response
    {
        return new Response(
            $data,
            new StatusErrorUnauthorised()
        );
    }

    protected function responseException(\Exception $e, $message = null): Response
    {
        return new Response(
            array('status' => $message ?? $e->getMessage()),
            new StatusError()
        );
    }
}
