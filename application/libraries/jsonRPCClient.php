<?php
/*
 * Copyright 2007 Sergio Vaccaro <sergio@inservibile.org>
 * Copyright 2012, 2015 Johannes Weberhofer <jweberhofer@weberhofer.at>
 *
 * This file is part of JSON-RPC PHP.
 *
 * JSON-RPC PHP is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * JSON-RPC PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with JSON-RPC PHP; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

/**
 * The object of this class are generic jsonRPC 1.0 clients
 *
 * @see http://json-rpc.org/wiki/specification
 * @license GPLv2+
 * @author sergio <jsonrpcphp@inservibile.org>
 * @author Johannes Weberhofer <jweberhofer@weberhofer.at>
 */

class jsonRPCClient
{

    /**
     * Debug state
     *
     * @var boolean
     */
    private $debug = false;

    /**
     * The server URL
     *
     * @var string
     */
    private $url;

    /**
     * Proxy to be used
     *
     * @var string
     */
    private $proxy = null;

    /**
     * The request id
     *
     * @var integer
     */
    private $id;

    /**
     * If true, notifications are performed instead of requests
     *
     * @var boolean
     */
    private $notification = false;

    /**
     * If false, requests will be forced to use fopen() instead.
     * This option is specific in case of cURL is callable but may not practically unsable.
     *
     * @var boolean
     */
    private $enableCurl = true;

    /**
     * Takes the connection parameters
     *
     * @param string $url
     * @param boolean $debug
     * @param string $proxy
     */
    public function __construct($url, $debug = false, $proxy = null)
    {
        $this->url = $url;
        $this->proxy = $proxy;
        $this->debug = ($this->debug === true);
        // message id
        $this->id = 1;
    }

    /**
     * Sets the notification state of the object.
     * In this state, notifications are performed, instead of requests.
     *
     * @param boolean $notification
     */
    public function setRPCNotification($notification)
    {
        empty($notification) ? $this->notification = false : $this->notification = true;
    }

    /**
     * Performs a jsonRCP request and gets the results as an array
     *
     * @param string $method
     * @param array $params
     * @return array
     */
    public function __call($method, $params)
    {

        // check
        if (! is_scalar($method)) {
            throw new \Exception('Method name has no scalar value');
        }

        // check
        if (is_array($params)) {
            // no keys
            $params = array_values($params);
        } else {
            throw new \Exception('Params must be given as array');
        }

        // sets notification or request task
        if ($this->notification) {
            $currentId = null;
        } else {
            $currentId = $this->id;
        }

        // prepares the request
        $request = array(
            'method' => $method,
            'params' => $params,
            'id' => $currentId
        );
        $request = json_encode($request);
        if ($this->debug) {
            echo '***** Request *****' . "\n" . $request . "\n";
        }

        // performs the HTTP POST
        if ($this->enableCurl && is_callable('curl_init')) {
            // use curl when available; solves problems with allow_url_fopen
            $ch = curl_init($this->url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-type: application/json'
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            if ($this->proxy !== null && trim($this->proxy) !== '') {
                curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
            }
            $response = curl_exec($ch);
            if ($response === false) {
                throw new \Exception('Unable to connect to ' . $this->url);
            }
        } else {
            $opts = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/json',
                    'content' => $request
                )
            );
            $context = stream_context_create($opts);

            if ($fp = fopen($this->url, 'r', false, $context)) {
                $response = '';
                while ($row = fgets($fp)) {
                    $response .= trim($row) . "\n";
                }
            } else {
                throw new \Exception('Unable to connect to ' . $this->url);
            }
        }
        if ($this->debug) {
            echo '***** Response *****' . "\n" . $response . "\n" . '***** End of Response *****' . "\n\n";
        }
        $response = json_decode($response, true);

        // final checks and return
        if (! $this->notification) {
            // check
            if ($response['id'] != $currentId) {
                throw new \Exception('Incorrect response id: ' . $response['id'] . ' (request id: ' . $currentId . ')');
            }
            if (array_key_exists('error', $response) && $response['error'] !== null) {
                throw new \Exception('Request error: ' . json_encode($response['error']));
            }

            return $response['result'];
        } else {
            return true;
        }
    }

    /**
     * Enable cURL when performs a jsonRCP request.
     */
    public function enableCurl()
    {
        $this->enableCurl = true;
    }

    /**
     * Disable cURL when performs a jsonRCP request.
     */
    public function disableCurl()
    {
        $this->enableCurl = false;
    }

    public function call($method, $params)
    {
        return $this->__call($method, $params);
    }
}
