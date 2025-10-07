<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service_Console
 * @subpackage Exception
 * @version    $Id$
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @copyright  Copyright (c) 2009 - 2011, RealDolmen (http://www.realdolmen.com)
 * @license    http://phpazure.codeplex.com/license
 */

/**
* @see Zend_Service_Console_Command_ParameterSource_ParameterSourceInterface
*/
require_once 'Zend/Service/Console/Command/ParameterSource/ParameterSourceInterface.php';

/**
 * @category   Zend
 * @package    Zend_Service_Console
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @copyright  Copyright (c) 2009 - 2011, RealDolmen (http://www.realdolmen.com)
 * @license    http://phpazure.codeplex.com/license
 */
class Zend_Service_Console_Command_ParameterSource_StdIn
	implements Zend_Service_Console_Command_ParameterSource_ParameterSourceInterface
{
	/**
	 * Get value for a named parameter.
	 *
	 * @param mixed $parameter Parameter to get a value for
	 * @param array $argv Argument values passed to the script when run in console.
	 * @return bool|string|null
     */
	public function getValueForParameter($parameter, $argv = [])
	{
		// Default value
		$parameterValue = null;

		// Check STDIN for data
		if (ftell(STDIN) !== false) {
			// Read from STDIN
			$fs = fopen("php://stdin", "r");
			if ($fs !== false) {
				/*
				while (!feof($fs)) {
					$data = fread($fs, 1);
					var_dump($data);
					$parameterValue .= $data;
				} */
				$parameterValue = stream_get_contents($fs);
				fclose($fs);
			}

			// Remove ending \r\n
			$parameterValue = rtrim($parameterValue);

			if (strtolower($parameterValue) == 'true') {
				$parameterValue = true;
			} else if (strtolower($parameterValue) == 'false') {
				$parameterValue = false;
			}
		}

		// Done!
		return $parameterValue;
	}
}
