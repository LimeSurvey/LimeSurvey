<?php

class dFunctionHide
{
	public function __construct()
	{
	}
	
	public function run($args)
	{
		
		$funcName=array_shift($args);
		try
		{
			//$func = dTexts::loadFunction($funcName);
			$CI =& get_instance();
            $CI->load->helper('dTexts/dFunction'.$funcName);
            $className='dFunction'.$funcName;
            $func = new $className();
            $newStr = $func->run($args);
			if(strtolower($newStr)=='true'){
				$id=time().rand(0,100);
                $data['id'] = $id;
				$hideJS=$CI->load->view('libraries/dTexts/hideJS_view.php',$data,true);
                return $hideJS;			
			}
		}
		catch(Exception $e)
		{
			throw $e;
		}
		return '';
		
	}
}
