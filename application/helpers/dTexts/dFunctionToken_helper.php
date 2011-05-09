<?php

class dFunctionToken
{
	public function __construct()
	{
	}
	
	public function run($args)
	{
		global $surveyid;
	    if ($this->session->userdata('token') != '')
	    {
	        //Gather survey data for tokenised surveys, for use in presenting questions
	        $currenttoken=getTokenData($surveyid,$this->session->userdata('token'));
            //add data to session
            $newdata = array(
                'thistoken' => $currenttoken
            );
            $this->session->set_userdata($newdata);
	    }
	    $currenttoken = $this->session->userdata('thistoken');
	    if ($currenttoken)
	    {
	        if (!strcmp(strtolower($args[0]),'firstname'))
            {
                return $currenttoken['firstname'];
            }
            //return $_SESSION['thistoken']['firstname'];
	        if (!strcmp(strtolower($args[0]),'lastname'))
            {
                return $currenttoken['lastname'];
            }
            //return $_SESSION['thistoken']['lastname'];
	        if (!strcmp(strtolower($args[0]),'email'))
            {
                return $currenttoken['email'];
            }
            //return $_SESSION['thistoken']['email'];
	    }
	    else
	    {
	    	return "";
	    }
	
	    if(stripos($args[0],'attribute_')!==FALSE){
	        $attr_no=(int)str_replace('ATTRIBUTE_','',$args[0]);
	        if (isset($currenttoken['attribute_'.$attr_no]))  return $currenttoken['attribute_'.$attr_no];
	    }
	    
	    throw new Exception('TOKEN incorrect!');
	}
}
