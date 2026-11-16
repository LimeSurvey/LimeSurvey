<?php

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
 * Logs each RemoteControl (JSON-RPC/XML-RPC) call as one JSON line to the 'remotecontrol' log category,
 * written to tmp/runtime/remotecontrol.log when 'rpc_log' is enabled in config.php.
 *
 * Passwords are removed, session keys are replaced by a short hash and long values are shortened,
 * but survey, participant and response data is logged as sent.
 */
class RemoteControlLogger
{
    /** @var int Strings in the request parameters longer than this are shortened */
    private const MAX_PARAM_LENGTH = 500;

    /** @var int Number of bytes of the response kept in the log */
    private const MAX_RESPONSE_LENGTH = 4096;

    /** @var string[] Names of the RPC method parameters holding a session key */
    private const SESSION_KEY_PARAMS = ['sSessionKey', 'sessionKey'];

    /** @var float */
    private $startTime;

    /** @var string Start of the response sent to the client */
    private $response = '';

    /** @var int Total size of the response sent to the client */
    private $responseSize = 0;

    /**
     * Starts capturing the response; call it before the request is handled
     *
     * @return void
     */
    public function start()
    {
        $this->startTime = microtime(true);
        // Small chunk size: Big exports are still streamed to the client instead of being kept in memory
        ob_start([$this, 'captureOutput'], 8192);
    }

    /**
     * Output buffer callback: Keeps the start of the response and passes all output through unchanged
     *
     * @param string $buffer
     * @return string
     */
    public function captureOutput($buffer)
    {
        $this->responseSize += strlen($buffer);
        if (strlen($this->response) < self::MAX_RESPONSE_LENGTH) {
            $this->response .= substr($buffer, 0, self::MAX_RESPONSE_LENGTH - strlen($this->response));
        }
        return $buffer;
    }

    /**
     * Sends the response to the client and writes the log line; call it after the request is handled
     *
     * @param remotecontrol_handle $handler The object the RPC methods are called on
     * @param string $rpcType 'json' or 'xml'
     * @return void
     */
    public function finish($handler, $rpcType)
    {
        ob_end_flush();
        try {
            [$method, $params] = $this->readRequest($rpcType);
            $response = $this->response;
            if ($method === 'get_session_key') {
                // Same characters and length as the key created by get_session_key
                $response = preg_replace_callback(
                    '/[A-Za-z0-9_~]{32}/',
                    function ($match) {
                        return $this->hashSessionKey($match[0]);
                    },
                    $response
                );
            }
            $entry = [
                'ip' => App()->getRequest()->getUserHostAddress(),
                'protocol' => $rpcType,
                'method' => $method,
                'params' => $this->cleanParams($handler, $method, $params),
                'duration_ms' => (int) round((microtime(true) - $this->startTime) * 1000),
                'response_size' => $this->responseSize,
                'response' => $response,
            ];
            Yii::log(
                json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
                CLogger::LEVEL_INFO,
                'remotecontrol'
            );
        } catch (Throwable $e) {
            // The response is already sent: Never let logging break the API call
            Yii::log('Could not log the RemoteControl call: ' . $e->getMessage(), CLogger::LEVEL_WARNING, 'remotecontrol');
        }
    }

    /**
     * Reads the called method and its parameters from the raw request body
     *
     * @param string $rpcType 'json' or 'xml'
     * @return array The method name (null if unreadable) and the list of parameters
     */
    private function readRequest($rpcType)
    {
        if ($rpcType === 'xml') {
            $request = new Zend_XmlRpc_Request_Http();
            return [$request->getMethod(), (array) $request->getParams()];
        }
        $request = json_decode(file_get_contents('php://input'), true);
        return [$request['method'] ?? null, (array) ($request['params'] ?? [])];
    }

    /**
     * Removes passwords, hashes session keys and shortens long values of the request parameters
     *
     * @param remotecontrol_handle $handler
     * @param mixed $method
     * @param array $params Positional or named parameters
     * @return array The parameters, keyed by parameter name where the method is known
     */
    private function cleanParams($handler, $method, array $params)
    {
        $names = [];
        if (is_string($method) && method_exists($handler, $method)) {
            foreach ((new ReflectionMethod($handler, $method))->getParameters() as $parameter) {
                $names[] = $parameter->getName();
            }
        }
        $cleaned = [];
        foreach ($params as $key => $value) {
            $name = is_string($key) ? $key : ($names[$key] ?? $key);
            if ($name === 'password') {
                $value = '***';
            } elseif (in_array($name, self::SESSION_KEY_PARAMS, true) && is_string($value)) {
                $value = $this->hashSessionKey($value);
            } else {
                $value = $this->shorten($value);
            }
            $cleaned[$name] = $value;
        }
        return $cleaned;
    }

    /**
     * Replaces a session key by a short hash: Calls of the same session can be matched without the key being usable
     *
     * @param string $sessionKey
     * @return string
     */
    private function hashSessionKey($sessionKey)
    {
        return 'key#' . substr(hash('sha256', $sessionKey), 0, 8);
    }

    /**
     * Shortens long strings, also inside arrays
     *
     * @param mixed $value
     * @return mixed
     */
    private function shorten($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'shorten'], $value);
        }
        if (is_string($value) && strlen($value) > self::MAX_PARAM_LENGTH) {
            return substr($value, 0, self::MAX_PARAM_LENGTH) . sprintf('... (%d bytes)', strlen($value));
        }
        return $value;
    }
}
