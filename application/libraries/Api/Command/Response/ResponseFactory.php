<?php

namespace LimeSurvey\Api\Command\Response;

use LimeSurvey\Api\Command\Response\{
    Response,
    Status\StatusSuccess,
    Status\StatusError,
    Status\StatusErrorNotFound,
    Status\StatusErrorBadRequest,
    Status\StatusErrorUnauthorised
};

class ResponseFactory
{
    public function makeSuccess($data = null): Response
    {
        return new Response(
            $data,
            new StatusSuccess()
        );
    }

    public function makeError($data = null): Response
    {
        return new Response(
            $data,
            new StatusError()
        );
    }

    public function makeErrorNotFound($data = null): Response
    {
        return new Response(
            $data,
            new StatusErrorNotFound()
        );
    }

    public function makeErrorBadRequest($data = null): Response
    {
        return new Response(
            $data,
            new StatusErrorBadRequest()
        );
    }

    public function makeErrorUnauthorised($data = null): Response
    {
        return new Response(
            $data,
            new StatusErrorUnauthorised()
        );
    }

    public function makeexception(\Exception $e, $message = null): Response
    {
        return new Response(
            array('status' => $message ?? $e->getMessage()),
            new StatusError()
        );
    }
}
