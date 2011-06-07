<?php
/**
 * This class is responsible for translating tags (like {INSERTANS..., {IF
 * @author lime-nk Michal Kurzeja
 */
class dTexts
{
	/**
	 * This metod translates the given text and returns it
	 * @param $text
	 * @return String
	 */
	public static function run($text)
	{
		include_once('dFunctions/dFunctionInterface.php');
		preg_match_all('|\{([^{}]+)\}|i',$text,$functions);
		foreach($functions[1] as $id=>$str)
		{
			$data=explode(':',$str);
			$funcName=array_shift($data);
			try
			{
				$func = dTexts::loadFunction($funcName);
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
		$fileName='./classes/dTexts/dFunctions/dFunction'.$name.'.php';
		if(!file_exists($fileName))
		{
			throw new Exception('dFunction '.$name.' file not found!');
		}
		include_once($fileName);
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
