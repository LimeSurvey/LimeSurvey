<?php
/**
 * Sample for a LEM extension to add new functions for LimeSurvey ExpressionManager
 * Copyright (C) 2018 orvil (O. Villani)
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * 
 * Howto:
 * 1. Create (if not already existing) a new folder below the path '/application/extensions/' in your LS installation named 'emexpressions'.
 *    So path is now /application/extensions/emexpressions
 * 
 * 2. create in this (new) subfolder another folder with the name of your extension [in this case eg. 'sampleextension']
 *    This is your extension-folder
 * 
 * 3. create or copy in your extension-folder a *.php file with exactly the same name as the folder has, but with '.php' at the end
 *    This is your extension php file [in this case 'sampleextension.php']
 * *
 * 4. This extension php file has to contain a class with exactly the same name as the extension-folder / extension php file has
 *    This is the extension class [ in this case 'sampleextension']
 * 
 * 5. The every extension class requires: 
 * 		- a private variable named $RDP_ValidFunctions
 * 		- a constructor where the additional EM functions are defined (the same way as they are in em_core_helper.php)
 * 		- a function called 'newRDP_ValidFunctions()' where the property 'RDP_ValidFunctions' is returned
 * 
 * 6. In the extension php file but OUTSIDE(!) of the class definition implement those functions you need and 
 *    that are defined for RDP_ValidFunctions
 * 
 * 7. Create or copy in this extension-folder a *.js file with exactly the same name as the folder has, but with '.js' at the end
 *    This is the extension javaScript file [in this case 'sampleextension.js']
 *    The extension javaScript file has to have equivalent functions to the additional php functions in extension php file
 * 	  This is necesarry for surveys running in view-by-group and all-in-one mode
 */ 

 
// class has to have the same name as the file (except the .php at the end)
class sampleextension
{
	private $RDP_ValidFunctions; // names and # params of valid functions
	
	// define ADDITIONAL functions here, inside the constructor, in the same way as they are defined in em_core_helper.php
	function __construct()
	{
		$this->RDP_ValidFunctions = array(
			'sayHello' => array('sayHello', 'sayHello', gT('says Hello'), 'string sayHello(toWhom)', '',1),
			'sayBye'   => array('sayBye',   'sayBye',   gT('says Bye'),   'string sayBye(toWhom)',   '',1),
			//'yourFunction'   => array('phpFuncName', 'jsFuncName', gT('info to show on mouseover'), 'string yourFunction(param)', '', nrOfAllowedParam),			
		);
	}
		
	// function 'newRDP_ValidFunctions' will be used to register the new EM definitions
	// don't change the name of this function!
	function newRDP_ValidFunctions()
	{
		return $this->RDP_ValidFunctions;
	}
}
	
// place additional (registered) functions for EM below ///////////////////////////////////////////////////////////////////
function sayHello($msg)
{	
	return "Hello " . $msg;
}
	
function sayBye($msg)
{	
	return "Good Bye " . $msg . ". " . notRegistered();
}

// place functions NOT to be registered for EM below ///////////////////////////////////////////////////////////////////////////

// This is an example for a function not registered into RDP_ValidFunctions
// This means, you can't call this function via EM, so {notRegistered()} will not work inside LS
// but you can call it via a registered function - see function sayBye()
function notRegistered()
{
	return "Result is 42!";
}

?>
