<?php

namespace LimeSurvey\Models\Services;

/**
 * This class contains all functions for the process of password reset and creating new administration users
 * and sending email to those with a link to set the password.
 *
 * All this functions were implemented in UserManagementController before.
 */
class PasswordManagement
{
    // NB: PHP 7.0 does not support class constant visibility
    const MIN_PASSWORD_LENGTH = 8;
    const EMAIL_TYPE_REGISTRATION = 'registration';
    const EMAIL_TYPE_RESET_PW = 'resetPassword';

    /** @var $user \User */
    private $user;

    /**
     * PasswordManagement constructor.
     * @param $user \User
     */
    public function __construct(\User $user)
    {
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
        $adminEmail = [];
        $siteName = \Yii::app()->getConfig("sitename");
        $url = 'admin/authentication/sa/newPassword/param/' . $this->user->validation_key;
        $loginUrl = \Yii::app()->getController()->createAbsoluteUrl($url);
        $siteAdminEmail = \Yii::app()->getConfig("siteadminemail");
        $emailSubject = \Yii::app()->getConfig("admincreationemailsubject");
        $emailTemplate = \Yii::app()->getConfig("admincreationemailtemplate");

        //Replace placeholder in Email subject
        $emailSubject = str_replace("{SITENAME}", $siteName, $emailSubject);
        $emailSubject = str_replace("{SITEADMINEMAIL}", $siteAdminEmail, $emailSubject);

        //Replace placeholder in Email body
        $emailTemplate = str_replace("{SITENAME}", $siteName, $emailTemplate);
        $emailTemplate = str_replace("{SITEADMINEMAIL}", $siteAdminEmail, $emailTemplate);
        $emailTemplate = str_replace("{FULLNAME}", $this->user->full_name, $emailTemplate);
        $emailTemplate = str_replace("{USERNAME}", $this->user->users_name, $emailTemplate);
        $emailTemplate = str_replace("{LOGINURL}", $loginUrl, $emailTemplate);

        $adminEmail['subject'] = $emailSubject;
        $adminEmail['body'] = $emailTemplate;

        return $adminEmail;
    }


    /**
     * Sets the validationKey and the validationKey expiration and
     * sends email to the user, containing the link to set/reset password.
     *
     * @param string $emailType this could be 'registration' or 'resetPassword' (see const in this class)
     *
     * @return array message if sending email to user was successful
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendPasswordLinkViaEmail(string $emailType): array
    {

        $success = true;
        $this->user->setValidationKey();
        $this->user->setValidationExpiration();
        $mailer = $this->sendAdminMail($emailType);

        if ($mailer->getError()) {
            $sReturnMessage = \CHtml::tag("h4", array(), gT("Error"));
            $sReturnMessage .= \CHtml::tag("p", array(), sprintf(
                gT("Email to %s (%s) failed."),
                "<strong>" . $this->user->users_name . "</strong>",
                $this->user->email
            ));
            $sReturnMessage .= \CHtml::tag("p", array(), $mailer->getError());
            $success = false;
        } else {
            // has to be sent again or no other way
            $sReturnMessage = \CHtml::tag("h4", array(), gT("Success"));
            $sReturnMessage .= \CHtml::tag("p", array(), sprintf(
                gT("Username : %s - Email : %s."),
                $this->user->users_name,
                $this->user->email
            ));
            $sReturnMessage .= \CHtml::tag("p", array(), gT("An email with a generated link was sent to the user."));
        }

        return [
            'success' => $success,
            'sReturnMessage' => $sReturnMessage
        ];
    }

    /**
     * Send a link to email of the user to set a new password (forgot password functionality)
     *
     * @return string message for user
     */
    public function sendForgotPasswordEmailLink(): string
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
     * @todo it's fine to use static functions, until it is used only in controllers ...
     *
     * @param int $length Length of the password
     * @return string
     */
    public static function getRandomPassword($length = self::MIN_PASSWORD_LENGTH): string
    {
        $oGetPasswordEvent = new \PluginEvent('createRandomPassword');
        $oGetPasswordEvent->set('targetSize', $length);
        \Yii::app()->getPluginManager()->dispatchEvent($oGetPasswordEvent);

        return $oGetPasswordEvent->get('password');
    }

    /**
     * Send the registration email to a new survey administrator
     *
     * @param string $type   two types are available 'resetPassword' or 'registration', default is 'registration'
     *
     * @return \LimeMailer if send is successful
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendAdminMail($type = self::EMAIL_TYPE_REGISTRATION): \LimeMailer
    {
        $absolutUrl = \Yii::app()->getController()->createAbsoluteUrl("/admin");

        switch ($type) {
            case self::EMAIL_TYPE_RESET_PW:
                $passwordResetUrl = \Yii::app()->getController()->createAbsoluteUrl('admin/authentication/sa/newPassword/param/' . $this->user->validation_key);
                $renderArray = [
                    'surveyapplicationname' => \Yii::app()->getConfig("sitename"),
                    'emailMessage' => sprintf(gT("Hello %s,"), $this->user->full_name) . "<br />"
                        . sprintf(gT("This is an automated email to notify you that your login credentials for '%s' have been reset."), \Yii::app()->getConfig("sitename")),
                    'credentialsText' => gT("Here are your new credentials."),
                    'siteadminemail' => \Yii::app()->getConfig("siteadminemail"),
                    'linkToAdminpanel' => $absolutUrl,
                    'username' => $this->user->users_name,
                    'password' => $passwordResetUrl,
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
