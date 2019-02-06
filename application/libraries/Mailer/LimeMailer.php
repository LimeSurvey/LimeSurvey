<?php

namespace LimeSurvey\Mailer;
use \Yii;
require_once(APPPATH.'/third_party/phpmailer/load_phpmailer.php');

/**
 * WIP
 */
class LimeMailer extends \PHPMailer\PHPMailer\PHPMailer
{
    /**
     * Singleton
     * @var LimeMailer
     */
    private static $instance = null;

    /* Current survey id */
    public $surveyId;
    /* Current token object */
    public $oToken;

    /* var boolean */
    public $mailDisable = false;

    /**
     * WIP Set all needed fixed in params
     */
    public function __construct()
    {
        /* Global configuration for ALL email of this LimeSurvey instance */
        $emailmethod = Yii::app()->getConfig('emailmethod');
        $emailsmtphost = Yii::app()->getConfig("emailsmtphost");
        $emailsmtpuser = Yii::app()->getConfig("emailsmtpuser");
        $emailsmtppassword = Yii::app()->getConfig("emailsmtppassword");
        $emailsmtpdebug = Yii::app()->getConfig("emailsmtpdebug");
        $emailsmtpssl = Yii::app()->getConfig("emailsmtpssl");
        $defaultlang = Yii::app()->getConfig("defaultlang");
        $emailcharset = Yii::app()->getConfig("emailcharset");
        if (Yii::app()->getConfig('demoMode')) {
            $this->mailDisable = true;
            return;
        }
        /* language and charset */
        if (!$this->SetLanguage(Yii::app()->getConfig("defaultlang"),APPPATH.'/third_party/phpmailer/language/'))
        {
            $this->SetLanguage('en',APPPATH.'/third_party/phpmailer/language/');
        }
        $this->CharSet = Yii::app()->getConfig("emailcharset");
    }

    /**
     * To get a singleton : some part are not needed to do X times
     */
    public static function getInstance($reset=true)
    {
        Yii::log("Call instance", 'info', 'application.Mailer.LimeMailer.getInstance');
        if (empty(self::$instance)) {
            Yii::log("New mailer instance", 'info', 'application.Mailer.LimeMailer.getInstance');
            self::$instance = new self;
            /* no need to reset if new */
            return self::$instance;
        }
        Yii::log("Existing mailer instance", 'info', 'application.Mailer.LimeMailer.getInstance');
        if($reset) {
            self::$instance->clearAddresses();
            self::$instance->clearCustomHeaders();
            self::$instance->clearAttachments();
        }
        return self::$instance;
    }

}
