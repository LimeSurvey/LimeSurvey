<?php
/**
 * This class is responsible for translating tags (like {INSERTANS..., {IF
 * @author lime-nk Michal Kurzeja
 */
class Dtexts
{
	/**
	 * This metod translates the given text and returns it
	 * @param $text
	 * @return String
	 */
	public static function run($text)
	{
		//include_once('dFunctions/dFunctionInterface.php');
		$CI =& get_instance();
        preg_match_all('|\{([^{}]+)\}|i',$text,$functions);
		foreach($functions[1] as $id=>$str)
		{
			$data=explode(':',$str);
			$funcName=array_shift($data);
			try
			{
				//$func = dTexts::loadFunction($funcName);
				$CI->load->helper('dTexts/dFunction'.$funcName);
                $className='dFunction'.$funcName;
                $func = new $classname();
                $newStr = $func->run($data);
				$text = str_replace($functions[0][$id],$newStr,$text);
			}
			catch(Exception $e)
			{
				//TODO: logging
			}
		}
		return $text;
	}

	/**
	 * Loader for the adequate "worker" class
	 * @param $name Worker name
	 * @return dFunctionInterface
	 */
	public static function loadFunction($name)
	{
		$name=ucfirst(strtolower($name));
        $CI =& get_instance();
        $CI->load->config('lsconfig.php');
        $rootdir = $this->config->item('rootdir');
		$fileName=$rootdir.'application/libraries/dFunction'.$name.'.php';
		if(!file_exists($fileName))
		{
			throw new Exception('dFunction '.$name.' file not found!');
		}
		$CI->load->library('dFunction',$name);
        //include_once($fileName);
		$className='dFunction'.$name;
		if(!class_exists($className))
		{
			throw new Exception('dFunction '.$name.' class not found!');
		}
		$class = new $className();
		if(!($class instanceof dFunctionInterface))
		{
			throw new Exception('dFunction '.$name.' class should implement dFunctionInterface!');
		}
		return $class;
	}

}
