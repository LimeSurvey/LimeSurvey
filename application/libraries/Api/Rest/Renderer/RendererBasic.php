<?php

namespace LimeSurvey\Api\Rest\Renderer;

use CJSON;
use CWebLogRoute;
use Exception;
use Yii;
use LSYii_Application;
use LimeSurvey\Api\Rest\Renderer\RendererInterface;
use LimeSurvey\Api\Command\Response\{
    Response,
    Status
};

class RendererBasic implements RendererInterface
{
    /**
     * Return Response
     *
     * @param Response $response
     * @return void
     */
    public function returnResponse(Response $response)
    {
        $this->renderJSON(
            $response->getData(),
            $this->getHttpResponseCode($response->getStatus())
        );
    }

    /**
     * Return Exception Response
     *
     * @param Exception $exception
     * @return void
     */
    public function returnException(Exception $exception)
    {
        $error = [];

        $error['code'] = get_class($exception);
        // @todo replace global constant with injected config
        /** @psalm-suppress all */
        if (YII_DEBUG) {
            $error['message'] = $exception->getMessage();
            $error['file'] = $exception->getFile();
            $error['line'] = $exception->getLine();
            $error['stacktrace'] = $exception->getTraceAsString();
        }

        $this->renderJson(['error' => $error], 500);
    }

    /**
     * Return data to browser as JSON and end application.
     *
     * @param ?array $data
     * @param int $responseCode
     * @return void
     */
    protected function renderJSON($data, $responseCode = 200)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, *');
        header('Access-Control-Allow-Methods: *');
        http_response_code($responseCode);

        if ($data !== null) {
            header('Content-type: application/json');
            echo CJSON::encode($data);
        }

        /** @var LSYii_Application */
        $app = Yii::app();
        foreach ($app->log->routes as $route) {
            if ($route instanceof CWebLogRoute) {
                $route->enabled = false; // disable any weblogroutes
            }
        }
        $app->end();
    }

    /**
     * Get HTTP response code from command response status.
     *
     * @param Status $status
     * @return int
     */
    protected function getHttpResponseCode(Status $status)
    {
        $httpCode = 200;
        switch ($status->getCode()) {
            case 'success':
                $httpCode = 200;
                break;
            case 'success_no_content':
                $httpCode = 204;
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
            case 'error_forbidden':
                $httpCode = 403;
                break;
        }
        return $httpCode;
    }
}
