<?php
### Including
//including LimeSurvey configs, for database variables and more... 
include("../../config-defaults.php");
include("../../config.php");
require_once(dirname(__FILE__).'/../../common.php');

### Error Handling
// simple debug Option
error_reporting(E_ALL);

//specialized debug option, true for own debuglog
$japhDebug = true;
$japhDebugLog = "japh.log";

// error log enabled, hint(.../apache/logs/error.log) this is very handy while developing, since SOAP does not echo php error messages to the client
// it's also recommended to set this in productive environment
ini_set("log_errors", "1");


### Caching
//we don't like caching while testing, so we disable it... 
//for productiv use it's recommended to set this to 1 or comment it out for webserver default
ini_set("soap.wsdl_cache_enabled", "0"); 


### Security
// enable for ssl connections 
// IMPORTANT NOTE: You have to change the url to the japh.server.php in the japh.wsdl manually, too!)
//$japhOverSSL=true; //default: false

// enable if you use a certificate for the Connections
// IMPORTANT NOTE: your Client need the same certificate to connect with.
$useCert=false; //default: false
// path to your local certificate
$sslCert='cacert.pem';


### Variables
// Since this variable isn't per default in Limesurveys config.php any more, we need to set it ourselfs 
//(the setting in LS config-defaults.php is not working for japh... no idea why) 
// set the LS installation Dir relative to document root
$relativeurl		=   "/limesource/limesurvey"; //default: "/limesurvey"

// path to the wsdl definition for this server... normally it is in the same directory, so you don't need to change it. 
$wsdl= $homedir."/japh/japh.wsdl"; //default: $homedir."/japh.wsdl";

/**
 * These are the Dirs where the prepared surveys lay. 
 * one for the core surveys, 
 * one for addable modules
 */
$coreDir = "./studiply/";
$modDir = "./studiply_mod/";


//seperator for Tokens in sInsertToken function
$sJaphSeparator = ","; //default: ","

//set the Seperators for Participant Datasets in sInsertParticipants 
$sDatasetSeperator = "::"; //default: "::"
$sDatafieldSeperator = ";"; //default: ";"

?>