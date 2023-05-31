<?php

namespace LimeSurvey\Api\Command\Response;

use LimeSurvey\Api\Command\Response\{
    Response,
    Status,
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
        return $this->make(
            $data,
            new StatusSuccess()
        );
    }

    public function makeError($data = null): Response
    {
        return $this->make(
            $data,
            new StatusError()
        );
    }

    public function makeErrorNotFound($data = null): Response
    {
        return $this->make(
            $data,
            new StatusErrorNotFound()
        );
    }

    public function makeErrorBadRequest($data = null): Response
    {
        return $this->make(
            $data,
            new StatusErrorBadRequest()
        );
    }

    public function makeErrorUnauthorised($data = null): Response
    {
        return $this->make(
            $data,
            new StatusErrorUnauthorised()
        );
    }

    public function makeException(\Exception $e, $message = null): Response
    {
        return $this->make(
            array('status' => $message ?? $e->getMessage()),
            new StatusError()
        );
    }

    public function make($data, Status $status): Response
    {
        return new Response(
            $data,
            $status
        );
    }
}
