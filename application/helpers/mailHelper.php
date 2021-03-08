<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
* LimeSurvey
* Copyright (C) 2007-2021 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
 * General helper class for mailing
 */
class mailHelper
{

    const NEW_INSTANCE_MODE = 0;
    const PREVIOUS_INSTANCE_MODE = 1;

    /**
     * Instance of PHPMailer
     * @var PHPMailer\PHPMailer\PHPMailer
     */
    private static $mailer;

    /**
     * Mode for next getMailer() call
     * @var int
     */
    private static $mode = self::NEW_INSTANCE_MODE;

    /**
     * Indicates whether to set SMTP keep alive or not on next getMailer() call
     * @var bool
     */
    private static $smtpKeepAlive = false;

    /**
     * Returns a configured instance of PHPMailer.
     * 
     * By default, this method returns a new instance. To reuse the previous instance, the mode
     * must be set to mailHelper::NEW_INSTANCE_MODE using setModeForNext() before calling this method.
     * 
     * To use SMTP keep alive (off by default), indicate it beforehand using setSmtpKeepAliveForNext().
     * 
     * @return PHPMailer\PHPMailer\PHPMailer
     */
    public static function getMailer()
    {
        // Create instance of PHPMailer and configure
        if (self::$mailer == null || self::$mode == self::NEW_INSTANCE_MODE) {
            $mailer = self::getNewMailer();
            self::$mailer = $mailer;
        } else {
            // If reusing an mailer instance, clear some attributes
            self::$mailer->clearAllRecipients();
            self::$mailer->clearReplyTos();
            self::$mailer->clearAttachments();
            self::$mailer->clearCustomHeaders();
        }

        // Set SMTP keep alive if we need to
        if (self::$smtpKeepAlive && self::$mailer->Mailer == 'smtp') {
            self::$mailer->SMTPKeepAlive = true;
        }

        self::resetModeForNext();

        return self::$mailer;
    }

    /**
     * Resets both the "mode" and "smtp keep alive" properties for next getMailer() call
     */
    public static function resetModeForNext()
    {
        self::$mode = self::NEW_INSTANCE_MODE;
        self::$smtpKeepAlive = false;
    }

    /**
     * Sets the mode for next getMailer() call.
     * The mode will be automatically reset after the getMailer() call.
     * 
     * @param int $mode either mailHelper::NEW_INSTANCE_MODE or mailHelper::PREVIOUS_INSTANCE_MODE
     * @param array|null $options   additional options, like 'smtpKeepAlive' can be set passing an array
     */
    public static function setModeForNext($mode, $options = null)
    {
        if ($mode != self::NEW_INSTANCE_MODE && $mode != self::PREVIOUS_INSTANCE_MODE) {
            throw new InvalidArgumentException('Invalid mode');
        }
        self::$mode = $mode;

        // Set additional options if present
        if (is_array($options)) {
            if (array_key_exists('smtpKeepAlive', $options)) {
                self::$smtpKeepAlive = $options['smtpKeepAlive'];
            }
        }
    }

    /**
     * Indicates whether to set SMTP keep alive or not on next getMailer() call.
     * 
     * If $keepAlive is true, and the Email Method global setting is SMTP, the mailer
     * returned by getMailer() will have SMTPKeepAlive set.
     * 
     * This parameter is automatically reset after the getMailer() call.
     * 
     * @param bool $keepAlive
     */
    public static function setSmtpKeepAliveForNext($keepAlive = true)
    {
        self::$smtpKeepAlive = $keepAlive;
    }

    /**
     * Returns a new PHPMailer instance configured according to the global settings
     * 
     * @return PHPMailer\PHPMailer\PHPMailer
     */
    private static function getNewMailer()
    {
        $emailmethod = Yii::app()->getConfig('emailmethod');
        $emailsmtphost = Yii::app()->getConfig("emailsmtphost");
        $emailsmtpuser = Yii::app()->getConfig("emailsmtpuser");
        $emailsmtppassword = Yii::app()->getConfig("emailsmtppassword");
        $emailsmtpdebug = Yii::app()->getConfig("emailsmtpdebug");
        $emailsmtpssl = Yii::app()->getConfig("emailsmtpssl");
        $defaultlang = Yii::app()->getConfig("defaultlang");
        $emailcharset = Yii::app()->getConfig("emailcharset");

        require_once(APPPATH . '/third_party/phpmailer/load_phpmailer.php');
        $mailer = new PHPMailer\PHPMailer\PHPMailer;

        $mailer->SMTPAutoTLS = false;
        if (!$mailer->SetLanguage($defaultlang, APPPATH . '/third_party/phpmailer/language/')) {
            $mailer->SetLanguage('en', APPPATH . '/third_party/phpmailer/language/');
        }
        $mailer->CharSet = $emailcharset;
        if (isset($emailsmtpssl) && trim($emailsmtpssl) !== '' && $emailsmtpssl !== 0) {
            if ($emailsmtpssl === 1) {
                $mailer->SMTPSecure = "ssl";
            } else {
                $mailer->SMTPSecure = $emailsmtpssl;
            }
        }
        switch ($emailmethod) {
            case "qmail":
                $mailer->IsQmail();
                break;
            case "smtp":
                $mailer->IsSMTP();
                if ($emailsmtpdebug > 0) {
                    $mailer->SMTPDebug = $emailsmtpdebug;
                }
                if (strpos($emailsmtphost, ':') > 0) {
                    $mailer->Host = substr($emailsmtphost, 0, strpos($emailsmtphost, ':'));
                    $mailer->Port = (int) substr($emailsmtphost, strpos($emailsmtphost, ':') + 1);
                } else {
                    $mailer->Host = $emailsmtphost;
                }
                $mailer->Username = $emailsmtpuser;
                $mailer->Password = $emailsmtppassword;
                if (trim($emailsmtpuser) != "") {
                    $mailer->SMTPAuth = true;
                }

                break;
            case "sendmail":
                $mailer->IsSendmail();
                break;
            default:
                $mailer->IsMail();
        }

        return $mailer;
    }
}
