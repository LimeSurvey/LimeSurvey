<?php

class dFunctionToken implements dFunctionInterface
{
	public function __construct()
	{
	}
	
	public function run($args)
	{
		global $surveyid;
	    if (isset($_SESSION['token']) && $_SESSION['token'] != '')
	    {
	        //Gather survey data for tokenised surveys, for use in presenting questions
	        $_SESSION['thistoken']=getTokenData($surveyid, $_SESSION['token']);
	    }
	
	    if (isset($_SESSION['thistoken']))
	    {
	        if (!strcmp(strtolower($args[0]),'firstname')) return $_SESSION['thistoken']['firstname'];
	        if (!strcmp(strtolower($args[0]),'lastname')) return $_SESSION['thistoken']['lastname'];
	        if (!strcmp(strtolower($args[0]),'email')) return $_SESSION['thistoken']['email'];
	    }
	    else
	    {
	    	return "";
	    }
	
	    if(stripos($args[0],'attribute_')!==FALSE){
	        $attr_no=(int)str_replace('ATTRIBUTE_','',$args[0]);
	        if (isset($_SESSION['thistoken']['attribute_'.$attr_no]))  return $_SESSION['thistoken']['attribute_'.$attr_no];
	    }
	    
	    throw new Exception('TOKEN incorrect!');
	}
}
