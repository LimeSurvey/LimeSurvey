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
     * As Yii createUrl, but appends param ajax = 1 to url
     * Use when creating Ajax action links, like button clicks that
     * will open modals or save data.
     * @param string $route
     * @param array $params
     * @return string
     */
    public static function createUrl($route, array $params = array())
    {
        $params['ajax'] = 1;
        return App()->createUrl($route, $params);
    }
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
        self::echoString($output); // Encoded to json format when converted to string
    }

    /**
     * Success popup
     * @param string $msg
     * @return void
     */
    public static function outputSuccess($msg)
    {
        $output = new JsonOutputSuccess($msg);
        self::echoString($output);
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
        self::echoString($output);
    }

    /**
     * No permission popup
     * @return void
     */
    public static function outputNoPermission()
    {
        $output = new JsonOutputNoPermission();
        self::echoString($output);
    }

    /**
     * @return void
     */
    public static function outputNotLoggedIn()
    {
        $output = new JsonOutputNotLoggedIn();
        self::echoString($output);
    }

    /**
     * @param string $target
     * @return void
     */
    public static function outputHtml($html, $target)
    {
        $output = new JsonOutputHtml($html, $target);
        self::echoString($output);
    }

    /**
     * Echo $str with json header
     * @param string $str
     * @return void
     */
    private static function echoString($str)
    {
        header('Content-Type: application/json');
        echo $str;
        \Yii::app()->end();
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
     * @param string|null $result
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
            'ajaxHelper'       => true, // To help JS parse in some cases.
            'success'          => $this->success,
            'result'           => $this->result,
            'error'            => $this->error,
            'loggedIn'         => $this->loggedIn,
            'hasPermission'    => $this->hasPermission,
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
     * @return JsonOutputError
     */
    public function __construct($msg)
    {
        parent::__construct(null);
        $this->success = $msg;
    }
}

/**
 *
 */
class JsonOutputModal extends JsonOutput
{

    /**
     * @var string
     */
    public $html;

    /**
     *
     */
    public function __construct($html)
    {
        parent::__construct(null);
        $this->html = $html;
    }

    /**
     *
     * @return
     */
    public function __toString()
    {
        return json_encode(array(
            'html' => $this->html,
            'hasPermission' => $this->hasPermission,
            'loggedIn' => $this->loggedIn
        ));
    }
}

/**
 * Echo html for log in form modal body
 * This is a special case of JsonOutputModal, but with fixed html
 * Only used through JsonOutputNotLoggedIn in AdminController::run.
 */
class JsonOutputNotLoggedIn extends JsonOutputModal
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct(null);

        \Yii::import('application.controllers.admin.authentication', true);

        // Return success, failure or template data
        $result = \Authentication::prepareLogin();

        // This should not be possible here
        if (isset($result[0]) && $result[0] == 'success') {
            throw new \CException('Internal error: login form submitted');
        } elseif (isset($result[0]) && $result[0] == 'failed') {
            throw new \CException('Internal error: login form submitted');
        }

        $data = $result;
        $this->html = \Yii::app()->getController()->renderPartial('/admin/authentication/ajaxLogin', $data, true);

        $this->hasPermission = true;
        $this->loggedIn = false;
    }
}

/**
 * Echo HTML and put it in a <div> with id $target.
 */
class JsonOutputHtml extends JsonOutput
{

    /**
     * Content.
     * @var string
     */
    public $html;

    /**
     * ID of element to put HTML in.
     * @var string
     */
    public $target;

    /**
     * @param string $html
     * @param string $target ID of element to put HTML in.
     */
    public function __construct($html, $target)
    {
        $this->html    = $html;
        $this->target  = $target;
    }

    public function __toString()
    {
        return json_encode(
            array(
                'loggedIn'      => true,
                'hasPermission' => true,
                'success'       => true,
                'html'          => $this->html,
                'outputType'    => 'jsonoutputhtml',
                'target'        => $this->target
            )
        );
    }
}
