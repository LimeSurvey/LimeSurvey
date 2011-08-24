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
    // TMSWhite
	public static function run($text,$questionNum=NULL)
	{
        return LimeExpressionManager::ProcessString($text,$questionNum,NULL,true);
	}
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
