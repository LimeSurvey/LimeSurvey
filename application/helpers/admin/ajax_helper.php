<?php

namespace ls\ajax;

/**
 * Ajax helper
 * This class will help to standardize the Ajax communication
 * between server and client.
 * See the manual page for more info: https://manual.limesurvey.org/Backend_Ajax_protocol
 *
 * @since 2016-09-27
 * @author Olle Härstedt
 */
class AjaxHelper
{
    /**
     * Echoes json with result set as $msg
     * This is the custom json, that expects to be
     * handled manually.
     * @param string $msg message
     * @return void
     */
    public static function output($msg)
    {
        $output = new JsonOutput($msg);
        echo $output;  // Encoded to json format when converted to string
    }

    /**
     * Success popup
     * @param string $msg
     * @return void
     */
    public static function outputSuccess($msg)
    {
        $output = new JsonOutputSuccess($msg);
        echo $output;
    }

    /**
     * Error popup
     * @param string $msg
     * @param int $code
     * @return void
     */
    public static function outputError($msg, $code = 0)
    {
        $output = new JsonOutputError($msg, $code);
        echo $output;
    }

    /**
     * No permission popup
     * @return void
     */
    public static function outputNoPermission()
    {
        $output = new JsonOutputNoPermission();
        echo $output;
    }
}

/**
 * Base class for json output
 * @since 2016-09-27
 * @author Olle Härstedt
 */
class JsonOutput
{
    /**
     * @var mixed
     */
    public $result;

    /**
     * Array like array('code' => 123, 'message' => 'Something went wrong.')
     * @var array|null
     */
    public $error;

    /**
     * Success message pop-up
     * @var string|null
     */
    public $success;

    /**
     * True if user is logged in
     * @var boolean
     */
    public $loggedIn;

    /**
     * True if user has permission
     * @var boolean
     */
    public $hasPermission;

    /**
     * Translated text of 'No permission'
     * @var string
     */
    public $noPermissionText;

    /**
     * 
     */
    public function __construct($result)
    {
        $this->result = $result;

        // Defaults
        $this->loggedIn = true;
        $this->hasPermission = true;

        // TODO: Check if user is logged in
    }

    /**
     * @return string Json encoded object
     */
    public function __toString()
    {
        return json_encode(array(
            'success' => $this->success,
            'result' => $this->result,
            'error' => $this->error,
            'loggedIn' => $this->loggedIn,
            'hasPermission' => $this->hasPermission,
            'noPermissionText' => gT('No permission')
        ));
    }
}

/**
 * Permission set to false
 * @since 2016-09-27
 * @author Olle Härstedt
 */
class JsonOutputNoPermission extends JsonOutput
{
    public function __construct()
    {
        parent::__construct(null);
        $this->hasPermission = false;
    }
}

/**
 * Set error in constructor, which will be
 * shown as a pop-up on client.
 * @since 2016-09-27
 * @author Olle Härstedt
 */
class JsonOutputError extends JsonOutput
{
    /**
     * @param string $msg
     * @param int $code
     * @return JsonOutputError
     */
    public function __construct($msg, $code = 0)
    {
        parent::__construct(null);
        $this->error = array(
            'message' => $msg,
            'code' => $code
        );
    }
}

/**
 * Set success message in constructor, which
 * will be shown as a pop-up on client.
 * @since 2016-09-27
 * @author Olle Härstedt
 */
class JsonOutputSuccess extends JsonOutput
{
    /**
     * @param string $msg
     * @param int $code
     * @return JsonOutputError
     */
    public function __construct($msg)
    {
        parent::__construct(null);
        $this->success = $msg;
    }
}
