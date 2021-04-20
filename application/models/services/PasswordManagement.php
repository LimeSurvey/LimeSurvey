<?php


namespace LimeSurvey\Models\Services;

/**
 * This class contains all functions for the process of password reset and creating new administration users
 * and sending email to those.
 *
 * All this functions were implemented in UserManagementController before.
 *
 *
 */
class PasswordManagement
{

    const MIN_PASSWORD_LENGTH = 8;

    const EMAIL_TYPE_REGISTRATION = 'registration';
    const EMAIL_TYPE_RESET_PW = 'resetPassword';

    /** @var $user \User */
    private $user;

    /**
     * PasswordManagement constructor.
     * @param $user \User
     */
    public function __construct($user){
        $this->user = $user;
    }

    /**
     * This function prepare the email template to send to the new created user
     *
     *
     * @return mixed $aAdminEmail array with subject and email body
     */
    public function generateAdminCreationEmail()
    {
        $aAdminEmail = [];
        $siteName = \Yii::app()->getConfig("sitename");
        $loginUrl = \Yii::app()->getController()->createAbsoluteUrl('admin/authentication/sa/newPassword/param/' . $this->user->validation_key);
        $siteAdminEmail = \Yii::app()->getConfig("siteadminemail");
        $emailSubject = \Yii::app()->getConfig("admincreationemailsubject");
        $emailTemplate = \Yii::app()->getConfig("admincreationemailtemplate");

        // authent is not delegated to web server or LDAP server
        if (\Yii::app()->getConfig("auth_webserver") === false && \Permission::model()->hasGlobalPermission('auth_db', 'read', $this->user->uid)) {
            // send password (if authorized by config)
            if (!\Yii::app()->getConfig("display_user_password_in_email") === true) {
                $password = "<p>" . gT("Please contact your LimeSurvey administrator for your password.") . "</p>";
            }
        }

        //Replace placeholder in Email subject
        $emailSubject = str_replace("{SITENAME}", $siteName, $emailSubject);
        $emailSubject = str_replace("{SITEADMINEMAIL}", $siteAdminEmail, $emailSubject);

        //Replace placeholder in Email body
        $emailTemplate = str_replace("{SITENAME}", $siteName, $emailTemplate);
        $emailTemplate = str_replace("{SITEADMINEMAIL}", $siteAdminEmail, $emailTemplate);
        $emailTemplate = str_replace("{FULLNAME}", $this->user->full_name, $emailTemplate);
        $emailTemplate = str_replace("{USERNAME}", $this->user->users_name, $emailTemplate);
        $emailTemplate = str_replace("{LOGINURL}", $loginUrl, $emailTemplate);

        $aAdminEmail['subject'] = $emailSubject;
        $aAdminEmail['body'] = $emailTemplate;

        return $aAdminEmail;
    }


    /**
     * Sets the validationKey and the validationKey expiration and
     * sends email to the user, containing the link to set/reset password.
     *
     * @return array message if sending email to user was successful
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendPasswordLinkViaEmail(){

        $success = true;
        $this->user->setValidationKey();
        $this->user->setValidationExpiration();
        $mailer = $this->sendAdminMail('registration');

        if ($mailer->getError()) {
            $sReturnMessage = \CHtml::tag("h4", array(), gT("Error"));
            $sReturnMessage .= \CHtml::tag("p", array(), sprintf(gT("Email to %s (%s) failed."),
                "<strong>" . $this->user->users_name . "</strong>", $this->user->email));
            $sReturnMessage .= \CHtml::tag("p", array(), $mailer->getError());
            $success = false;
        } else {
            // has to be sent again or no other way
            $sReturnMessage = \CHtml::tag("h4", array(), gT("Success"));
            $sReturnMessage .= \CHtml::tag("p", array(), sprintf(gT("Username : %s - Email : %s."),
                $this->user->users_name, $this->user->email));
            $sReturnMessage .= \CHtml::tag("p", array(), gT("An email with a generated link was sent to the user."));
        }

        return [
            'success' => $success,
            'sReturnMessage' => $sReturnMessage
        ];
    }

    /**
     * Send a link for the user to set a new password (forgot password functionality)
     *
     * @return string message for user
     */
    public function sendForgotPasswordEmailLink()
    {
        $mailer = new \LimeMailer();
        $mailer->emailType = 'passwordreminderadminuser';
        $mailer->addAddress($this->user->email, $this->user->full_name);
        $mailer->Subject = gT('User data');

        /* Body construct */
        $this->user->setValidationKey();
        $this->user->setValidationExpiration();
        $username = sprintf(gT('Username: %s'), $this->user->users_name);

        $linkToResetPage = \Yii::app()->getController()->createAbsoluteUrl('admin/authentication/sa/newPassword/param/' . $this->user->validation_key);
        $linkText = gT("Click here to set your password: ") . $linkToResetPage;
        $body   = array();
        $body[] = sprintf(gT('Your link to reset password %s'), \Yii::app()->getConfig('sitename'));
        $body[] = $username;
        $body[] = $linkText;
        $body   = implode("\n", $body);
        $mailer->Body = $body;
        /* Go to send email and set password*/
        if ($mailer->sendMessage()) {
            // For security reasons, we don't show a successful message
            $sMessage = gT('If the username and email address is valid and you are allowed to use the internal database authentication a new password has been sent to you.');
        } else {
            $sMessage = gT('Email failed');
        }

        return $sMessage;
    }

    /**
     * Creates a random password through the core plugin
     *
     * @param int $length Length of the password
     * @return string
     */
    public static function getRandomPassword($length = self::MIN_PASSWORD_LENGTH)
    {
        $oGetPasswordEvent = new \PluginEvent('createRandomPassword');
        $oGetPasswordEvent->set('targetSize', $length);
        \Yii::app()->getPluginManager()->dispatchEvent($oGetPasswordEvent);

        return $oGetPasswordEvent->get('password');
    }

    /**
     * Resets the password for one user
     *
     * @param User $oUser User model
     * @param bool $sendMail Send a mail to the user
     * @return array [success, uid, username, password]
     * @throws CException
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function resetLoginData(&$oUser, $sendMail = false)
    {
        $newPassword = $this->getRandomPassword(8);
        $oUser->setPassword($newPassword);
        $success = true;
        if ($sendMail === true) {
            $aUser = $oUser->attributes;
            $aUser['rawPassword'] = $newPassword;
            $success = $this->sendAdminMail($aUser, 'resetPassword');
        }
        return [
            'success' => $success, 'uid' => $oUser->uid, 'username' => $oUser->users_name, 'password' => $newPassword,
        ];
    }

    /**
     * Send the registration email to a new survey administrator
     *
     * @param string $type   two types are available 'resetPassword' or 'registration', default is 'registration'
     * @param null $newPassword
     * @return \LimeMailer if send is successfull
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendAdminMail($type = self::EMAIL_TYPE_REGISTRATION)
    {
        $absolutUrl = \Yii::app()->getController()->createAbsoluteUrl("/admin");

        switch ($type) {
            case self::EMAIL_TYPE_RESET_PW:
                $renderArray = [
                    'surveyapplicationname' => \Yii::app()->getConfig("sitename"),
                    'emailMessage' => sprintf(gT("Hello %s,"), $this->user->full_name) . "<br />"
                        . sprintf(gT("This is an automated email to notify you that your login credentials for '%s' have been reset."), \Yii::app()->getConfig("sitename")),
                    'credentialsText' => gT("Here are your new credentials."),
                    'siteadminemail' => \Yii::app()->getConfig("siteadminemail"),
                    'linkToAdminpanel' => $absolutUrl,
                    'username' => $this->user->users_name,
                    //'password' => $this->user->ra$aUser['rawPassword'],
                    'mainLogoFile' => LOGO_URL,
                    'showPasswordSection' => \Yii::app()->getConfig("auth_webserver") === false && \Permission::model()->hasGlobalPermission('auth_db', 'read', $this->user->uid),
                    'showPassword' => (\Yii::app()->getConfig("display_user_password_in_email") === true),
                ];
                $subject = "[" . \Yii::app()->getConfig("sitename") . "] " . gT("Your login credentials have been reset");
                $body = \Yii::app()->getController()->renderPartial('partial/usernotificationemail', $renderArray, true);
                break;
            case self::EMAIL_TYPE_REGISTRATION:
            default:
                //Get email template from globalSettings
                $aAdminEmail = $this->generateAdminCreationEmail();
                $subject = $aAdminEmail["subject"];
                $body = $aAdminEmail["body"];
                break;
        }

        $emailType = "addadminuser";

        $oCurrentlyLoggedInUser = \User::model()->findByPk(\Yii::app()->user->id);

        $mailer = new \LimeMailer();
        $mailer->addAddress($this->user->email, $this->user->full_name);
        $mailer->Subject = $subject;
        $mailer->setFrom($oCurrentlyLoggedInUser->email, $oCurrentlyLoggedInUser->users_name);
        $mailer->Body = $body;
        $mailer->isHtml(true);
        $mailer->emailType = $emailType;
        $mailer->sendMessage();
        return $mailer;
    }

}