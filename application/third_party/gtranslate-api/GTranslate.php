<?php

/**
* GTranslate - A class to comunicate with Google Translate(TM) Service
*               Google Translate(TM) API Wrapper
*               More info about Google(TM) service can be found on http://code.google.com/apis/ajaxlanguage/documentation/reference.html
* 		This code has o affiliation with Google (TM) , its a PHP Library that allows to comunicate with public a API
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @author Jose da Silva <jose@josedasilva.net>
* @since 2009/11/18
* @version 0.7.7
* @licence LGPL v3
*
* <code>
* <?
* require_once("GTranslate.php");
* try{
*	$gt = new Gtranslate;
*	echo $gt->english_to_german("hello world");
* } catch (GTranslateException $ge)
* {
*	echo $ge->getMessage();
* }
* ?>
* </code>
*/


/**
* Exception class for GTranslated Exceptions
*/

class GTranslateException extends Exception
{
	public function __construct($string) {
		parent::__construct($string, 0);
	}

	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}

class GTranslate
{
	/**
	* Google Translate(TM) Api endpoint
	* @access private
	* @var String 
	*/
	private $url = "https://www.googleapis.com/language/translate/v2";
	
        /**
        * Google Translate (TM) Api Version
        * @access private
        * @var String 
        */	
	private $api_version = "2";

        /**
        * Comunication Transport Method
 	* Available: http / curl
        * @access private
        * @var String 
        */
	private $request_type = "http";

        /**
        * Valid languages file
        * @access private
        * @var String 
        */
	private $valid_languages_file = array("languages.ini", "languages.v2.ini");

        /**
        * Path to available languages file
        * @access private
        * @var String 
        */
	private $available_languages_file 	= "languages.v2.ini";
	
        /**
        * Holder to the parse of the ini file
        * @access private
        * @var Array
        */
	private $available_languages = array();

	/**
	* Google Translate api key
 	* @access private 
	* @var string
	*/
	private $api_key = null;

	/**
	* Google request User IP
	* @access private
	* @var string	
	*/
	private $user_ip = null;

	/**
	* HTTP Url of the translated page
	* @access private
	* @var string
	*/
	private $http_referer	=	'';

        /**
        * Constructor sets up {@link $available_languages}
        */
	public function __construct()
	{
		$this->http_referer = (!empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '');
	}

	/**
	* Set language file to use
	* @access public
	* @param string $file
	*/	
	public function setLanguageFile($language_file)
	{		
		if(in_array($language_file, $this->valid_languages_file))
		{
			$this->available_languages_file = $language_file;
			return true;			
		}
		return false;
	}

	/**
	* Parse available language from language file
	* @access private
	*/	
	private function parseLanguageFile()
	{
		$this->available_languages = parse_ini_file($this->available_languages_file);
	}	
	
        /**
        * URL Formater to use on request
        * @access private
        * @param array $lang_pair
	* @param array $string
	* "returns String $url
        */

	private function urlFormat($lang_pair,$string)
	{
		$parameters = array(
			"q" => $string,
			"source" => $lang_pair[0],
            "target" => $lang_pair[1],
		);

		if(!empty($this->api_key))
		{
			$parameters["key"] = $this->api_key;
		}

		 else
        {
            $parameters["key"] = getGlobalSetting('googletranslateapikey');
        }

		$url  = "";

		foreach($parameters as $k=>$p)
		{
			$url 	.=	$k."=".urlencode($p)."&";
		}
		return $url;
	}

	/**
	* Define the request type
	* @access public
	* @param string $request_type
	* return boolean
	*/
	public function setRequestType($request_type = 'http') {
  		if (!empty($request_type)) {
	    		$this->request_type = $request_type;
			return true;
  		}
		return false;
	}

	/**
	* Define the Google Translate Api Key
 	* @access public
	* @param string $api_key
	* return boolean
	*/
	public function setApiKey($api_key) {
  		if (!empty($api_key)) {
	    		$this->api_key = $api_key;
			return true;
  		}
		return false;
	}
	
	/**
	* Define the User Ip for the query
 	* @access public
	* @param string $ip
	* return boolean
	*/
	public function setUserIp($ip) {
  		if (!empty($ip)) {
	    		$this->user_ip = $ip;
			return true;
  		}
		return false;
	}
	
	/**
	* Define the http referer for the translation
 	* @access public
	* @param string $utl
	* return boolean
	*/
	public function setHttpReferer($url) {
  		if (!empty($url)) {
	    		$this->http_referer = $url;
			return true;
  		}
		return false;
	}
	
        /**
        * Query the Google(TM) endpoint 
        * @access private
        * @param array $lang_pair
        * @param array $string
        * returns String $response
        */

	public function query($lang_pair,$string)
	{

		$query_url = $this->urlFormat($lang_pair,$string);
		$response = $this->{"request".ucwords($this->request_type)}($query_url);
		return $response;
	}

        /**
        * Query Wrapper for Http Transport 
        * @access private
        * @param String $url
        * returns String $response
        */

	private function requestHttp($url)
	{
        $fullurl = $this->url."?".$url;
        $return = file_get_contents($fullurl);
        $json = json_decode($return);
		return GTranslate::evalResponse($json);
		
	}

        /**     
        * Query Wrapper for Curl Transport 
        * @access private
        * @param String $url
        * returns String $response
        */

	private function requestCurl($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, $this->http_referer);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
		$body = curl_exec($ch);
		curl_close($ch);
		return GTranslate::evalResponse(json_decode($body));
	}

        /**     
        * Response Evaluator, validates the response
	* Throws an exception on error 
        * @access private
        * @param String $json_response
        * returns String $response
        */

	private function evalResponse($json_response)
	{
        if (isset($json_response->data->translations))
        {
            return $json_response->data->translations[0]->translatedText;
        }
        else
        {
            throw new GTranslateException("Unable to perform Translation:".$json_response->data);
        }
	}


        /**     
        * Validates if the language pair is valid
        * Throws an exception on error 
        * @access private
        * @param Array $languages
        * returns Array $response Array with formated languages pair
        */

	private function isValidLanguage($languages)
	{
		$language_list 	= $this->available_languages;

		$languages 		= 	array_map( "strtolower", $languages );
		$language_list_v  	= 	array_map( "strtolower", array_values($language_list) );
		$language_list_k 	= 	array_map( "strtolower", array_keys($language_list) );
		$valid_languages 	= 	false;

		if( TRUE == in_array($languages[0],$language_list_v) AND TRUE == in_array($languages[1],$language_list_v) )
		{
			$valid_languages 	= 	true;	
		}


		if( FALSE === $valid_languages AND TRUE == in_array($languages[0],$language_list_k) AND TRUE == in_array($languages[1],$language_list_k) )
		{
			$languages 	= 	array($language_list[strtoupper($languages[0])],$language_list[strtoupper($languages[1])]);
			$valid_languages        =       true;
		}

		if( FALSE === $valid_languages )
		{
			throw new GTranslateException("Unsupported languages (".$languages[0].",".$languages[1].")");
		}

		return $languages;
	}

        /**     
        * Magic method to understande translation comman
	* Evaluates methods like language_to_language
        * @access public
	* @param String $name
        * @param Array $args
        * returns String $response Translated Text
        */


	public function __call($name,$args)
	{
		if(empty($this->available_languages))
		{
			$this->parseLanguageFile();
		}
		
		$languages_list 	= 	explode("_to_",strtolower($name));
		$languages = $this->isValidLanguage($languages_list);

		$string 	= 	$args[0];
		return $this->query($languages,$string);
	}
}

?>
