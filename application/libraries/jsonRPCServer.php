<?php
/*
 * Copyright 2007 Sergio Vaccaro <sergio@inservibile.org>
 * Copyright 2012,2015 Johannes Weberhofer <jweberhofer@weberhofer.at>
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
 * This class build a json-RPC Server 1.0
 *
 * @see http://json-rpc.org/wiki/specification
 * @license GPLv2+
 * @author sergio <jsonrpcphp@inservibile.org>
 * @author Johannes Weberhofer <jweberhofer@weberhofer.at>
 */

class jsonRPCServer
{

    /**
     * This function handle a request binding it to a given object
     *
     * @param object $object
     * @return boolean
     */
    public static function handle($object)
    {
        // allow only POST-JSON-RCP requests
        if (! $_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Status: 405 Method Not Allowed');
            header('Allow: POST');
            echo "405 Method Not Allowed; Allow only: POST";
        } elseif (! isset($_SERVER['CONTENT_TYPE']) || substr($_SERVER['CONTENT_TYPE'], 0, 16) !== 'application/json') {
            header('Status: 406 Not Acceptable');
            header('Content-Type: application/json');
            echo "Status: 406 Not Acceptable; Valid Content-Type application/json";
        } else {
            // read the input data
            $request = json_decode(file_get_contents('php://input'), true);
            // executes the task on local object
            $response = array(
                'id' => $request['id'],
                'result' => null,
                'error' => null
            );
            try {
                $response['result'] = call_user_func_array(array(
                    $object,
                    $request['method']
                ), $request['params']);
            } catch (\Exception $e) {
                $response['result'] = null;
                $response['error'] = $e->getMessage();
            }

            // output the response, don't respond on notifications
            if ($request['id'] !== null) {
                header('content-type: text/javascript; charset=utf-8');
                echo json_encode($response);
            }
            return true;
        }
        return false;
    }
}
