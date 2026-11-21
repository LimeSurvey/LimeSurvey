<?php

namespace LimeSurvey\Api\Command\Response;

use LimeSurvey\Api\Command\Response\{
    Response,
    Status,
    Status\StatusSuccess,
    Status\StatusSuccessNoContent,
    Status\StatusError,
    Status\StatusErrorNotFound,
    Status\StatusErrorBadRequest,
    Status\StatusErrorUnauthorised,
    Status\StatusErrorForbidden,
    Status\StatusErrorConflict,
};

class ResponseFactory
{
    /**
     * @param mixed $data
     */
    public function makeSuccess($data = null): Response
    {
        return $this->make(
            $data,
            new StatusSuccess()
        );
    }

    /**
     *
     */
    public function makeSuccessNoContent(): Response
    {
        return $this->make(
            null,
            new StatusSuccessNoContent()
        );
    }

    /**
     * @param mixed $data
     */
    public function makeError($data = null): Response
    {
        return $this->make(
            $data,
            new StatusError()
        );
    }

    /**
     * @param mixed $data
     */
    public function makeErrorNotFound($data = null): Response
    {
        return $this->make(
            $data,
            new StatusErrorNotFound()
        );
    }

    /**
     * @param mixed $data
     */
    public function makeErrorBadRequest($data = null): Response
    {
        return $this->make(
            $data,
            new StatusErrorBadRequest()
        );
    }

    /**
     * @param mixed $data
     */
    public function makeErrorUnauthorised($data = null): Response
    {
        return $this->make(
            $data,
            new StatusErrorUnauthorised()
        );
    }

    /**
     * @param mixed $data
     */
    public function makeErrorForbidden($data = null): Response
    {
        return $this->make(
            $data,
            new StatusErrorForbidden()
        );
    }

    /**
     * Make a response for a request that conflicts with the current state of the resource.
     *
     * @param mixed $data Error data
     * @return Response The "conflict" response
     */
    public function makeErrorConflict($data = null): Response
    {
        return $this->make(
            $data,
            new StatusErrorConflict()
        );
    }

    /**
     * @param \Exception $e
     * @param ?string $message
     */
    public function makeException(\Exception $e, $message = null): Response
    {
        return $this->make(
            array('status' => $message ?? $e->getMessage()),
            new StatusError()
        );
    }

    /**
     * @param mixed $data
     * @param Status $status
     */
    public function make($data, Status $status): Response
    {
        return new Response(
            $data,
            $status
        );
    }
}
