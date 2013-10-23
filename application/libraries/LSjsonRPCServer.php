<?php
    Yii::import('application.libraries.jsonRPCServer');
    class LSjsonRPCServer extends jsonRPCServer
    {
        /**
	 * This function handle a request binding it to a given object
	 *
	 * @param object $object
	 * @return boolean
	 */
	public static function handle($object) {
        // checks if a JSON-RCP request has been received
		if (
			$_SERVER['REQUEST_METHOD'] != 'POST' ||
			empty($_SERVER['CONTENT_TYPE']) ||
			$_SERVER['CONTENT_TYPE'] != 'application/json'
			) {
			// This is not a JSON-RPC request
			return false;
		}
		// reads the input data
		$request = json_decode(file_get_contents('php://input'),true);
		
		// extract parameters from request 
		$id = $request['id'];
		$method = $request['method'];
		$params = $request['params'];
		// order parameters to fit with the order defined in the method
		$ordered_params = self::order_params($method, $params);

        // executes the task on local object
		try {	
            $result = @call_user_func_array(array($object,$method),$ordered_params);
			if ($result!==false) {
				$response = array (
									'id' => $id,
									'result' => $result,
									'error' => NULL
									);
			} else {
				$response = array (
									'id' => $id,
									'result' => NULL,
									'error' => 'unknown method or incorrect parameters'
									);
			}
		} catch (Exception $e) {
			$response = array (
								'id' => $id,
								'result' => NULL,
								'error' => $e->getMessage()
								);
		}

		// output the response
		if (!empty($id)) { // notifications don't want response
			header('content-type: text/javascript');
			echo json_encode($response);
		}

		// finish
		return true;
	}

	/**
	 * For a given function "f" from remotecontrol_handle class
	 * returns an ordered list of parameters corresponding to "f" parameters
	 *
	 *  The function sorts parameters from $params 
	 *  and add missing parameters with their default value
	 * 
	 * @param string $method
	 * @param array $params 
	 * @return array of ordered params (if exception occurs, params are returned unchanged)
	 */
	private static function order_params($method_name, $params) {
		$ordered_params = array();
		try {
			$method = new ReflectionMethod('remotecontrol_handle', $method_name);				
			$method_parameters =  $method->getParameters();			
			foreach ($method_parameters as $param) {
				$key = $param->getName();
				if (array_key_exists($key, $params)) {
					$value = $params[$key];
					array_push($ordered_params, $value);						
				} else {
					array_push($ordered_params, $param->getDefaultValue());
				}
			}
			return $ordered_params;		
		} catch (Exception $e) {
			// e.g. : no method $method found in class remotecontrol_handle
			return $params ;
		}
	}
	
 }   
?>
