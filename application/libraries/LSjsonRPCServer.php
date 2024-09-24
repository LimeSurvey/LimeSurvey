<?php

    Yii::import('application.libraries.BigData', true);
    Yii::import('application.libraries.jsonRPCServer');
class LSjsonRPCServer extends jsonRPCServer
{
    /**
     * This function handle a request binding it to a given object
     *
     * @param remotecontrol_handle $object
     * @return boolean
     */
    public static function handle($object)
    {
        // checks if a JSON-RCP request has been received
        if (
            $_SERVER['REQUEST_METHOD'] != 'POST' ||
            empty($_SERVER['CONTENT_TYPE']) ||
            strpos((string) $_SERVER['CONTENT_TYPE'], "application/json") === false
        ) {
            // This is not a JSON-RPC request
            return false;
        }

        // reads the input data
        $request = json_decode(file_get_contents('php://input'), true);
        // executes the task on local object
        if (is_null($request)) {
            // Can not decode the json, issue error
            $response = array(
                                'id' => null,
                                'result' => null,
                                'error' => sprintf('unable to decode malformed json')
                                );
        } else {
            try {
                $result = @call_user_func_array(array($object, $request['method']), $request['params']);
                if ($result !== false) {
                    $response = array(
                                        'id' => $request['id'],
                                        'result' => $result,
                                        'error' => null
                                        );
                } else {
                    $response = array(
                                        'id' => $request['id'],
                                        'result' => null,
                                        'error' => 'unknown method or incorrect parameters'
                                        );
                }
            } catch (Exception $e) {
                $response = array(
                                    'id' => $request['id'],
                                    'result' => null,
                                    'error' => $e->getMessage()
                                    );
            }
        }

        // output the response
        if (is_null($request) || !empty($request['id'])) {
// notifications don't want response
            header('content-type: application/json');
            BigData::json_echo($response);
        }

        // finish
        return true;
    }
}
