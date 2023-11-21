<?php

namespace LimeSurvey\Api\Rest\Endpoint;

use CHttpRequest;
use Yii;
use DI\FactoryInterface;
use LimeSurvey\Api\{
    Command\Options,
    Rest\Endpoint,
    ApiException
};

class EndpointFactory
{
    protected FactoryInterface $diFactory;

    /**
     * @param FactoryInterface $diFactory
     */
    public function __construct(FactoryInterface $diFactory)
    {
        $this->diFactory = $diFactory;
    }

    /**
     * Create
     *
     * @param CHttpRequest $request
     * @return Endpoint
     */
    public function create(CHttpRequest $request)
    {
        $endpointConfig = $this->getEndpointConfig($request);
        return $this->diFactory->make(Endpoint::class, [
            'config' => $endpointConfig,
            'commandParams' => $this->getCommandParams(
                $endpointConfig,
                $request
            )
        ]);
    }

    /**
     * Get Endpoint Config
     *
     * @param CHttpRequest $request
     * @throws ApiException
     * @return array
     */
    protected function getEndpointConfig(CHttpRequest $request)
    {
        $endpointConfig = [];
        if ($request->getRequestType() == 'OPTIONS') {
            // OPTIONS has a standard command class
            // - to handle CORS preflight requests
            $endpointConfig = [
                'commandClass' => Options::class
            ];
        } else {
            $endpointConfig = $this->parseEndpointConfig($request);
        }

        if (!$endpointConfig) {
            throw new ApiException('Endpoint not configured');
        }
        if (!isset($endpointConfig['commandClass'])) {
            throw new ApiException('Command class not specified');
        }
        if (!class_exists($endpointConfig['commandClass'])) {
            throw new ApiException('Invalid command class');
        }

        return $endpointConfig;
    }

    /**
     * Parse Endpoint Config
     *
     * @param CHttpRequest $request
     * @throws ApiException
     * @return array
     */
    protected function parseEndpointConfig(CHttpRequest $request)
    {
        // rest config contains specification of all endpoints
        $restConfig = Yii::app()->getConfig('rest');
        $apiVersion = $request->getParam('_api_version');
        $entity = $request->getParam('_entity');
        $id = $request->getParam('_id', null);
        $requestMethod = $request->getRequestType();

        $endpointConfig = [];
        // lookup the endpoint config matching the http request
        foreach ($restConfig as $key => $config) {
            $keyParts = explode('/', $key);

            $keyApiVersion = null;
            $keyEntity = null;
            $keyId = null;
            $keyPartsCount = count($keyParts);
            if ($keyPartsCount == 2) {
                [$keyApiVersion, $keyEntity] = $keyParts;
            } elseif ($keyPartsCount == 3) {
                [$keyApiVersion, $keyEntity, $keyId] = $keyParts;
            }

            $keyId = !empty($keyId)
                ? ltrim($keyId, '$')
                : false;

            if (
                $keyApiVersion == $apiVersion
                && $keyEntity == $entity
                && (
                    isset($config[$requestMethod])
                    && is_array($config[$requestMethod])
                )
                && (false === $keyId || !is_null($id))
            ) {
                $endpointConfig = $config[$requestMethod];

                $endpointConfig['apiVersion'] = $apiVersion;
                break;
            }
        }

        return $endpointConfig;
    }

    /**
     * Get Command Params
     *
     * Parse REST API command params from HTTP request.
     *
     * @param array $endpoint
     * @param CHttpRequest $request
     * @return array
     */
    public function getCommandParams($endpoint, CHttpRequest $request)
    {
        $params = [];

        // REST route defines optional param '_id'
        if ($id = $request->getParam('_id')) {
            $params['_id'] = $id;
        }

        $content = $request->getRestParams();
        $query = $this->getParams($endpoint, $request);
        $headers = getAllHeaders();
        $source = [
            '_content' => $content,
            '_query' => $query
        ];

        // The order of this array is important
        // - headers parsed by the server should always override
        // - query params and post data
        return array_merge(
            $query,
            $params,
            $content,
            $headers,
            $source
        );
    }

    /**
     * Get Query Params
     *
     * Parse REST config params from the HTTP request.
     *
     * @param array $endpoint
     * @param CHttpRequest $request
     * @return array
     */
    protected function getParams($endpoint, CHttpRequest $request)
    {
        $result = [];
        if (
            isset($endpoint['params'])
            && is_array($endpoint['params'])
        ) {
            foreach ($endpoint['params'] as $paramName => $options) {
                if (!$options) {
                    continue; // turned off
                }
                $default = is_array($options) && isset($options['default'])
                    ? $options['default']
                    : null;
                $result[$paramName] = $request->getParam(
                    $paramName,
                    $default
                );
            }
        }
        return $result;
    }
}
