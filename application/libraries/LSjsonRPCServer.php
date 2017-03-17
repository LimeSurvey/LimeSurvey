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
	public static function handle($object) {
        // checks if a JSON-RCP request has been received
		if (
			$_SERVER['REQUEST_METHOD'] != 'POST' ||
			empty($_SERVER['CONTENT_TYPE']) ||
			strpos($_SERVER['CONTENT_TYPE'], "application/json") === FALSE
			) {
			// This is not a JSON-RPC request
			return false;
		}
        
		// reads the input data
		$request = json_decode(file_get_contents('php://input'),true);
        // executes the task on local object
        if (is_null($request)) {
            // Can not decode the json, issue error
            $response = array (
                                'id' => null,
                                'result' => NULL,
                                'error' => sprintf('unable to decode malformed json')
                                );
        } else {
            try {
                $oMethod = new ReflectionMethod($object, $request['method']);
                $aArguments = array();
                foreach($oMethod->getParameters() as $oParam){
                    $sParamName = $oParam->getName();
                    $iParamPos = $oParam->getPosition();
                    if(array_key_exists($sParamName, $request['params'])) {
                        $aArguments[$iParamPos] = $request['params'][$sParamName];
                    } elseif ($oParam->isOptional()) {
                        $aArguments[$iParamPos] = $oParam->getDefaultValue();
                    } else {
                        throw new Exception('missing non-optional parameter '.$sParamName);
                    }
                }
                $result = @call_user_func_array(array($object,$request['method']),$aArguments);
                if ($result!==false) {
                    $response = array (
                                        'id' => $request['id'],
                                        'result' => $result,
                                        'error' => NULL
                                        );
                } else {
                    $response = array (
                                        'id' => $request['id'],
                                        'result' => NULL,
                                        'error' => 'unknown method or incorrect parameters'
                                        );
                }
            } catch (Exception $e) {
                $response = array (
                                    'id' => $request['id'],
                                    'result' => NULL,
                                    'error' => $e->getMessage()
                                    );
            }
        }

		// output the response
		if (is_null($request) || !empty($request['id'])) { // notifications don't want response
			header('content-type: text/javascript');
            BigData::json_echo($response);
		}

		// finish
		return true;
	}
    }
?>
