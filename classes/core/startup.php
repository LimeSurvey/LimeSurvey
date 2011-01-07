<?php

if(ob_get_contents() !== false)
{
   ob_clean();
};
ob_start();

@ini_set("session.bug_compat_warn", 0); //Turn this off until first "Next" warning is worked out

if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on')
{
    deregister_globals();
}


/*
 * Remove variables created by register_globals from the global scope
 * Thanks to Matt Kavanagh
 */
function deregister_globals()
{
    $not_unset = array(
        'GLOBALS'   => true,
        '_GET'      => true,
        '_POST'     => true,
        '_COOKIE'   => true,
        '_REQUEST'  => true,
        '_SERVER'   => true,
        '_SESSION'  => true,
        '_ENV'      => true,
        '_FILES'    => true
    );

    // Not only will array_merge and array_keys give a warning if
    // a parameter is not an array, array_merge will actually fail.
    // So we check if _SESSION has been initialised.
    if (!isset($_SESSION) || !is_array($_SESSION))
    {
        $_SESSION = array();
    }

    // Merge all into one extremely huge array; unset this later
    $input = array_merge(
    array_keys($_GET),
    array_keys($_POST),
    array_keys($_COOKIE),
    array_keys($_SERVER),
    array_keys($_SESSION),
    array_keys($_ENV),
    array_keys($_FILES)
    );

    foreach ($input as $varname)
    {
        if (isset($not_unset[$varname]))
        {
            // Hacking attempt. No point in continuing.
            exit;
        }

        unset($GLOBALS[$varname]);
    }

    unset($input);
}

/**
 * This function converts a standard # array to a PHP array without having to resort to JSON_decode which is available from 5.2x and up only
 *
 * @param string $json String with JSON data
 * @return array
 */
if ( !function_exists('json_decode') ){
    function json_decode($content, $assoc=false){
        global $homedir;
        require_once($homedir."/classes/json/JSON.php");
        if ( $assoc ){
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        } else {
            $json = new Services_JSON;
        }
        return $json->decode($content);
    }
}

if ( !function_exists('json_encode') ){
    function json_encode($content){
        global $homedir;
        require_once($homedir."/classes/json/JSON.php");
        $json = new Services_JSON;
        return $json->encode($content);
    }
}


?>
