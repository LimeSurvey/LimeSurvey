<?php

namespace LimeSurvey\Api\Rest\V1;

use CJSON;
use CWebLogRoute;
use Exception;
use Yii;

use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusAbstract;
use LimeSurvey\Api\Rest\RestRendererInterface;

class RestRendererV1 implements RestRendererInterface
{
    public function returnResponse(Response $response)
    {
        $this->renderJSON(
            $response->getData(),
            $this->getHttpResponseCode($response->getStatus())
        );
    }

    public function returnException(Exception $exception)
    {
        $error = [];

        $error['code'] = get_class($exception);
        $error['message'] = $exception->getMessage();
        if (YII_DEBUG) {
            $error['file'] = $exception->getFile();
            $error['line'] = $exception->getLine();
            $error['stacktrace'] = $exception->getTraceAsString();
        }

        $this->renderJson(['error' => $error]);
    }

    /**
     * Return data to browser as JSON and end application.
     *
     * @param array $data
     * @param int $responseCode
     * @return void
     */
    protected function renderJSON($data, $responseCode = 200)
    {
        http_response_code($responseCode);
        header('Content-type: application/json');
        echo CJSON::encode($data);

        foreach (Yii::app()->log->routes as $route) {
            if ($route instanceof CWebLogRoute) {
                $route->enabled = false; // disable any weblogroutes
            }
        }
        Yii::app()->end();
    }

    /**
     * Get HTTP response code from command response status.
     *
     * @param StatusAbstract $status
     * @return void
     */
    protected function getHttpResponseCode(StatusAbstract $status)
    {
        $httpCode = 200;
        switch ($status->getCode()) {
            case 'success':
                $httpCode = 200;
                break;
            case 'success_created':
                $httpCode = 201;
                break;
            case 'error':
                $httpCode = 400;
                break;
            case 'error_unauthorised':
                $httpCode = 401;
                break;
            case 'error_bad_request':
                $httpCode = 400;
                break;
            case 'error_not_found':
                $httpCode = 404;
                break;
        }
        return $httpCode;
    }
}
