<?php
/*
* $Id$
*
* Copyright (c) 2002,2003 Free Software Foundation
* developed under the custody of the
* Open Web Application Security Project
* (http://www.owasp.org)
*
* This file is part of the PHP Filters.
* PHP Filters is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* PHP Filters is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
* See the GNU General Public License for more details.
*
* If you are not able to view the LICENSE, which should
* always be possible within a valid and working PHP Filters release,
* please write to the Free Software Foundation, Inc.,
* 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
* to get a copy of the GNU General Public License or to report a
* possible license violation.
*/
///////////////////////////////////////
// sanitize.inc.php
// Sanitization functions for PHP
// by: Gavin Zuchlinski, Jamie Pratt, Hokkaido
// webpage: http://libox.net
// Last modified: December 21, 2003
//
// Many thanks to those on the webappsec list for helping me improve these functions
///////////////////////////////////////
// Function list:
// sanitize_paranoid_string($string) -- input string, returns string stripped of all non
//           alphanumeric
// sanitize_system_string($string) -- input string, returns string stripped of special
//           characters
// sanitize_html_string($string) -- input string, returns string with html replacements
//           for special characters
// sanitize_int($integer) -- input integer, returns ONLY the integer (no extraneous
//           characters
// sanitize_float($float) -- input float, returns ONLY the float (no extraneous
//           characters)
// sanitize($input, $flags) -- input any variable, performs sanitization
//           functions specified in flags. flags can be bitwise
//           combination of PARANOID, SQL, SYSTEM, HTML, INT, FLOAT, LDAP,
//           UTF8
// sanitize_email($email) -- input any string, all non-email chars will be removed
// sanitize_user($string) -- total length check (and more ??)
// sanitize_userfullname($string) -- total length check (and more ??)
//
//
///////////////////////////////////////
//
// 20031121 jp - added defines for magic_quotes and register_globals, added ; to replacements
//               in sanitize_sql_string() function, created rudimentary testing pages
// 20031221 gz - added nice_addslashes and changed sanitize_sql_string to use it
// 20070213 lemeur - marked sanitize_sql_string as obsolete, should use db_quote instead
// 20071025 c_schmitz - added sanitize_email
// 20071032 lemeur - added sanitize_user and sanitize_userfullname
//
/////////////////////////////////////////

define("PARANOID", 1);
//define("SQL", 2);
define("SYSTEM", 4);
define("HTML", 8);
define("INT", 16);
define("FLOAT", 32);
define("LDAP", 64);
define("UTF8", 128);

// get register_globals ini setting - jp
$register_globals = (bool) ini_get('register_globals');
if ($register_globals == TRUE) { define("REGISTER_GLOBALS", 1); } else { define("REGISTER_GLOBALS", 0); }

// get magic_quotes_gpc ini setting - jp
$magic_quotes = (bool) ini_get('magic_quotes_gpc');
if ($magic_quotes == TRUE) { define("MAGIC_QUOTES", 1); } else { define("MAGIC_QUOTES", 0); }

// addslashes wrapper to check for gpc_magic_quotes - gz
function nice_addslashes($string)
{
	// if magic quotes is on the string is already quoted, just return it
	if(MAGIC_QUOTES)
	return $string;
	else
	return addslashes($string);
}


/**
*     1. Remove leading and trailing dots
*     2. Remove dodgy characters from filename, including spaces and dots except last.
*     3. Force extension if specified..
* 
* @param mixed $filename
* @param mixed $forceextension
* @return string
*/
function sanitize_filename($filename, $forceextension="")
{
    $defaultfilename = "none";
    $dodgychars = "[^0-9a-zA-z()_-]"; // allow only alphanumeric, underscore, parentheses and hyphen

    $filename = preg_replace("/^[.]*/","",$filename); // lose any leading dots
    $filename = preg_replace("/[.]*$/","",$filename); // lose any trailing dots
    $filename = $filename?$filename:$defaultfilename; // if filename is blank, provide default

    $lastdotpos=strrpos($filename, "."); // save last dot position
    $filename = preg_replace("/$dodgychars/","_",$filename); // replace dodgy characters
    $afterdot = "";
    if ($lastdotpos !== false) { // Split into name and extension, if any.
        $beforedot = substr($filename, 0, $lastdotpos);
    if ($lastdotpos < (strlen($filename) - 1))
        $afterdot = substr($filename, $lastdotpos + 1);
    }
    else // no extension
        $beforedot = $filename;

    if ($forceextension)
        $filename = $beforedot . "." . $forceextension;
    elseif ($afterdot)
        $filename = $beforedot . "." . $afterdot;
    else
        $filename = $beforedot;

    return $filename;
}


// paranoid sanitization -- only let the alphanumeric set through
function sanitize_paranoid_string($string, $min='', $max='')
{
   if (isset($string))
   {
   	$string = preg_replace("/[^_.a-zA-Z0-9]/", "", $string);
	$len = strlen($string);
	if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
	return FALSE;
	return $string;
   }
}

function sanitize_cquestions($string, $min='', $max='')
{
   if (isset($string))
   {
   	$string = preg_replace("/[^_.a-zA-Z0-9+#]/", "", $string);
	$len = strlen($string);
	if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
	return FALSE;
	return $string;
   }
}

function sanitize_email($email) {
// Handles now emails separated with a semikolon    
    $emailarray=explode(';',$email);
    for ($i = 0; $i <= count($emailarray)-1; $i++)
    {
      $emailarray[$i]=preg_replace('/[^a-zA-Z0-9;+_.@-]/i', '', $emailarray[$i]);
    }
    return implode(';',$emailarray);
}

// sanitize a string in prep for passing a single argument to system() (or similar)
function sanitize_system_string($string, $min='', $max='')
{
   if (isset($string))
   {
	$pattern = '/(;|\||`|>|<|&|^|"|'."\n|\r|'".'|{|}|[|]|\)|\()/i'; // no piping, passing possible environment variables ($),
	// seperate commands, nested execution, file redirection,
	// background processing, special commands (backspace, etc.), quotes
	// newlines, or some other special characters
	$string = preg_replace($pattern, '', $string);
	$string = '"'.preg_replace('/\$/', '\\\$', $string).'"'; //make sure this is only interpretted as ONE argument
	$len = strlen($string);
	if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))	return FALSE;
	return $string;
   }
}

function sanitize_xss_string($string)
{
    if (isset($string))  
    {
        $bad = array ('*','^','&','\'','-',';','\"','(',')','%','$','?');
        return str_replace($bad, '',$string);
    }
}



// sanitize a string for SQL input (simple slash out quotes and slashes)
function sanitize_sql_db_tablename($string)
{
	$bad = array ('*','^','&','\'','-',';','\"','(',')','%','$','?');
	return str_replace($bad, "",$string);
}

// sanitize a string for SQL input (simple slash out quotes and slashes)
function sanitize_ldap_string($string, $min='', $max='')
{
	$pattern = '/(\)|\(|\||&)/';
	$len = strlen($string);
	if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
	return FALSE;
	return preg_replace($pattern, '', $string);
}


// sanitize a string for HTML (make sure nothing gets interpretted!)
function sanitize_html_string($string)
{
	$pattern[0] = '/\&/';
	$pattern[1] = '/</';
	$pattern[2] = "/>/";
	$pattern[3] = '/\n/';
	$pattern[4] = '/"/';
	$pattern[5] = "/'/";
	$pattern[6] = "/%/";
	$pattern[7] = '/\(/';
	$pattern[8] = '/\)/';
	$pattern[9] = '/\+/';
	$pattern[10] = '/-/';
	$replacement[0] = '&amp;';
	$replacement[1] = '&lt;';
	$replacement[2] = '&gt;';
	$replacement[3] = '<br />';
	$replacement[4] = '&quot;';
	$replacement[5] = '&#39;';
	$replacement[6] = '&#37;';
	$replacement[7] = '&#40;';
	$replacement[8] = '&#41;';
	$replacement[9] = '&#43;';
	$replacement[10] = '&#45;';
	return preg_replace($pattern, $replacement, $string);
}

// make int int!
function sanitize_int($integer, $min='', $max='')
{
	$int = preg_replace("#[^0-9]#", "", $integer);
	if((($min != '') && ($int < $min)) || (($max != '') && ($int > $max)))
    {
	    return FALSE;
    }
    if ($int=='') 
    {
        return null;
    }
	return $int;
}

// sanitize a username
// TODO: define the exact format of the username
// allow for instance 0-9a-zA-Z@_-.
function sanitize_user($string)
{
	$username_length=64;
	$string=mb_substr($string,0,$username_length);
	return $string;
}

// sanitize a username
// TODO: define the exact format of the username
// allow for instance 0-9a-zA-Z@_-.
function sanitize_userfullname($string)
{
	$username_length=50;
	$string=mb_substr($string,0,$username_length);
	return $string;
}

function sanitize_labelname($string)
{
	$username_length=100;
	$string=mb_substr($string,0,$username_length);
	return $string;
}

// make float float!
function sanitize_float($float, $min='', $max='')
{
	$float = floatval($float);
	if((($min != '') && ($float < $min)) || (($max != '') && ($float > $max)))
	return FALSE;
	return $float;
}

// glue together all the other functions
function sanitize($input, $flags, $min='', $max='')
{
	if($flags & PARANOID) $input = sanitize_paranoid_string($input, $min, $max);
	if($flags & INT) $input = sanitize_int($input, $min, $max);
	if($flags & FLOAT) $input = sanitize_float($input, $min, $max);
	if($flags & HTML) $input = sanitize_html_string($input, $min, $max);
	if($flags & LDAP) $input = sanitize_ldap_string($input, $min, $max);
	if($flags & SYSTEM) $input = sanitize_system_string($input, $min, $max);
	return $input;
}

function check_paranoid_string($input, $min='', $max='')
{
	if($input != sanitize_paranoid_string($input, $min, $max))
	return FALSE;
	return TRUE;
}

function check_int($input, $min='', $max='')
{
	if($input != sanitize_int($input, $min, $max))
	return FALSE;
	return TRUE;
}

function check_float($input, $min='', $max='')
{
	if($input != sanitize_float($input, $min, $max))
	return FALSE;
	return TRUE;
}

function check_html_string($input, $min='', $max='')
{
	if($input != sanitize_html_string($input, $min, $max))
	return FALSE;
	return TRUE;
}


function check_ldap_string($input, $min='', $max='')
{
	if($input != sanitize_string($input, $min, $max))
	return FALSE;
	return TRUE;
}

function check_system_string($input, $min='', $max='')
{
	if($input != sanitize_system_string($input, $min, $max, TRUE))
	return FALSE;
	return TRUE;
}

// glue together all the other functions
function check($input, $flags, $min='', $max='')
{
	$oldput = $input;
	if($flags & UTF8) $input = my_utf8_decode($input);
	if($flags & PARANOID) $input = sanitize_paranoid_string($input, $min, $max);
	if($flags & INT) $input = sanitize_int($input, $min, $max);
	if($flags & FLOAT) $input = sanitize_float($input, $min, $max);
	if($flags & HTML) $input = sanitize_html_string($input, $min, $max);
	if($flags & LDAP) $input = sanitize_ldap_string($input, $min, $max);
	if($flags & SYSTEM) $input = sanitize_system_string($input, $min, $max, TRUE);
	if($input != $oldput)
	return FALSE;
	return TRUE;
}

function sanitize_languagecode($codetosanitize) {
    return preg_replace('/[^a-z0-9-]/i', '', $codetosanitize); 
}

function sanitize_languagecodeS($codestringtosanitize) {
	$codearray=explode(" ",trim($codestringtosanitize));
	$codearray=array_map("sanitize_languagecode",$codearray);
	return implode(" ",$codearray);
}

function sanitize_token($codetosanitize) {
    return preg_replace('/[^_a-z0-9-]/i', '', $codetosanitize); 
}

function sanitize_signedint($integer, $min='', $max='')
{
    $int  = (int) $integer; 

    if((($min != '') && ($int < $min)) || (($max != '') && ($int > $max)))
    { 
        return FALSE;                              // Oops! Outside limits.
    }

    return $int;
};

?>
