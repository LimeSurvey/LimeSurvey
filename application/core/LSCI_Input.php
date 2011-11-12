<?php
class LSCI_Input extends CI_Input {

    function __construct()
    {
        parent::__construct();
    }

	/**
	* Clean Keys
	*
	* This is a helper function. To prevent malicious users
	* from trying to exploit keys we make sure that keys are
	* only named with alpha-numeric text and a few other items.
	*
	* @access	private
	* @param	string
	* @return	string
	*/
	function _clean_input_keys($str)
	{
		if ( ! preg_match("/^[a-z0-9:_\/#-]+$/i", $str))
		{
			var_dump($str);
			exit('Disallowed Key Characters.');
		}

		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE)
		{
			$str = $this->uni->clean_string($str);
		}

		return $str;
	}
	
	// --------------------------------------------------------------------

	/**
	* Fetch an item from the POST array using LimeSurvey's returnglobal
	*
	* @access	public
	* @param	string
	* @param	bool
	* @return	string
	*/	
	function post($index = NULL, $xss_clean = FALSE)
	{
		// Check if a field has been provided
		if ($index === NULL AND ! empty($_POST))
		{
			$post = array();

			// Loop through the full _POST array and return it
			foreach (parent::post($index, $xss_clean) as $key=>$value)
			{
				$post[$key] = returnglobal($key, $value);
			}
			return $post;
		}

		return returnglobal($index, parent::post($index, $xss_clean));
	}
}