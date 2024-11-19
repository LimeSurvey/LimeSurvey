<?php

namespace LimeSurvey\Models\Services;

use DateTime;

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
    const MIN_TIME_NEXT_FORGOT_PW_EMAIL = 5; //forgot pw email is send again, only after 5 min delay

    /** @var \User */
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
        /* Usage of Yii::app()->createAbsoluteUrl, disable publicurl, See mantis #19619 */
        $loginUrl = \Yii::app()->createAbsoluteUrl(
            'admin/authentication/sa/newPassword',
            ['param' => $this->user->validation_key]
        );
        $siteAdminEmail = \Yii::app()->getConfig("siteadminemail");
        $emailSubject = \Yii::app()->getConfig("admincreationemailsubject");
        $emailTemplate = \Yii::app()->getConfig("admincreationemailtemplate");

        //Replace placeholder in Email subject
        $emailSubject = str_replace("{SITENAME}", $siteName, (string) $emailSubject);
        $emailSubject = str_replace("{SITEADMINEMAIL}", $siteAdminEmail, $emailSubject);

        //Replace placeholder in Email body
        $emailTemplate = str_replace("{SITENAME}", $siteName, (string) $emailTemplate);
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
            $sReturnMessage .= \CHtml::tag(
                "p",
                array(),
                sprintf(
                    gT("Email to %s (%s) failed."),
                    "<strong>" . $this->user->users_name . "</strong>",
                    $this->user->email
                )
            );
            $sReturnMessage .= \CHtml::tag("p", array(), $mailer->getError());
            $success = false;
        } else {
            // has to be sent again or no other way
            $sReturnMessage = \CHtml::tag("h4", array(), gT("Success"));
            $sReturnMessage .= \CHtml::tag(
                "p",
                array(),
                sprintf(
                    gT("Username : %s - Email : %s."),
                    $this->user->users_name,
                    $this->user->email
                )
            );
            $sReturnMessage .= \CHtml::tag("p", array(), gT("An email with a generated link was sent to the user."));
        }

        return [
            'success' => $success,
            'sReturnMessage' => $sReturnMessage
        ];
    }

    /**
     * Checks if user is allowed to do the next sending of email for forgotten password.
     * This should only be the case all 5min (see self::MIN_TIME_NEXT_FORGOT_PW_EMAIL)
     *
     * @return bool true if user can send another email for forgotten pw, false otherwise
     * @throws \Exception
     */
    public function isAllowedToSendForgotPwEmail(\User $user): bool
    {
        $sendForgotPwIsAllowed = true;
        if ($user->last_forgot_email_password !== null) { //null means user has never clicked "Forget pw" before
            $now = new DateTime();
            $lastForgotPwEmail = new DateTime($user->last_forgot_email_password);
            $dateDiff = $now->diff($lastForgotPwEmail);
            //if time difference is greater then self::MIN_TIME_NEXT_FORGOT_PW_EMAIL, user can send new email
            $differenceDays = $dateDiff->format('%d');
            $differenceHours = $dateDiff->format('%h');
            $differenceMin = $dateDiff->format('%i');
            //calculate time difference in minutes
            $differenceInMinutes = ((int)$differenceDays * 24 * 60) + ((int)$differenceHours * 60) + (int)$differenceMin;
            $sendForgotPwIsAllowed = $differenceInMinutes > self::MIN_TIME_NEXT_FORGOT_PW_EMAIL;
        }

        return $sendForgotPwIsAllowed;
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
        //before setting new validationKey and date,check when was the last attempt
        if ($this->isAllowedToSendForgotPwEmail($this->user)) {
            $this->user->setValidationKey();
            $this->user->setValidationExpiration();
            $now = new DateTime();
            $this->user->last_forgot_email_password = $now->format('Y-m-d H:i:s');
            $this->user->save();
            $username = sprintf(gT('Username: %s'), $this->user->users_name);
            /* Usage of Yii::app()->createAbsoluteUrl, disable publicurl, See mantis #19619 */
            $linkToResetPage = \Yii::app()->createAbsoluteUrl(
                'admin/authentication/sa/newPassword/',
                ['param' => $this->user->validation_key]
            );
            $linkText = gT("Click here to set your password: ") . $linkToResetPage;
            $body = array();
            $body[] = sprintf(gT('Your link to reset password %s'), \Yii::app()->getConfig('sitename'));
            $body[] = $username;
            $body[] = $linkText;
            $body = implode("\n", $body);
            $mailer->Body = $body;
            /* Go to send email and set password*/
            if ($mailer->sendMessage()) {
                // For security reasons, we don't show a successful message
                $sMessage = sprintf(gt('If the username and email address is valid a password reminder email has been sent to you. This email can only be requested once in %d minutes.'), self::MIN_TIME_NEXT_FORGOT_PW_EMAIL);
            } else {
                $sMessage = gT('Email failed');
            }
        } else {
            $sMessage = sprintf(gt('If the username and email address is valid a password reminder email has been sent to you. This email can only be requested once in %d minutes.'), self::MIN_TIME_NEXT_FORGOT_PW_EMAIL);
        }

        return $sMessage;
    }

    /**
     * Creates a random password through the core plugin
     *
     * @todo it's fine to use static functions, until it is used only in controllers ...
     *
     * @param int $length Length of the password
     * @return string|null
     */
    public static function getRandomPassword($length = self::MIN_PASSWORD_LENGTH)
    {
        $oGetPasswordEvent = new \PluginEvent('createRandomPassword');
        $oGetPasswordEvent->set('targetSize', $length);
        \Yii::app()->getPluginManager()->dispatchEvent($oGetPasswordEvent);

        return $oGetPasswordEvent->get('password');
    }

    /**
     * Send the registration email to a new survey administrator
     *
     * @param string $type two types are available 'resetPassword' or 'registration', default is 'registration'
     *
     * @return \LimeMailer if send is successful
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function sendAdminMail($type = self::EMAIL_TYPE_REGISTRATION): \LimeMailer
    {
        switch ($type) {
            case self::EMAIL_TYPE_RESET_PW:
                $renderArray = $this->getRenderArray();
                $subject = "[" . \Yii::app()->getConfig("sitename") . "] " . gT(
                    "Your login credentials have been reset"
                );
                $body = \Yii::app()->getController()->renderPartial(
                    'partial/usernotificationemail',
                    $renderArray,
                    true
                );
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

        $mailer = new \LimeMailer();
        $mailer->addAddress($this->user->email, $this->user->full_name);
        $mailer->Subject = $subject;
        $mailer->setFrom(\Yii::app()->getConfig("siteadminemail"), \Yii::app()->getConfig("siteadminname"));
        $mailer->Body = $body;
        $mailer->isHtml(true);
        $mailer->emailType = $emailType;
        $mailer->sendMessage();
        return $mailer;
    }

    /**
     * @return array
     */
    public function getRenderArray()
    {
        /* Usage of Yii::app()->createAbsoluteUrl, disable publicurl, See mantis #19619 */
        $absoluteUrl = \Yii::app()->createAbsoluteUrl("/admin");
        $passwordResetUrl = \Yii::app()->createAbsoluteUrl(
            'admin/authentication/sa/newPassword',
            ['param' => $this->user->validation_key]
        );
        return [
            'surveyapplicationname' => \Yii::app()->getConfig("sitename"),
            'emailMessage' => sprintf(gT("Hello %s,"), $this->user->full_name) . "<br />"
            . sprintf(
                gT(
                    "This is an automated email to notify you that your login credentials for '%s' have been reset."
                ),
                \Yii::app()->getConfig("sitename")
            ),
            'credentialsText' => gT("Here are your new credentials."),
            'siteadminemail' => \Yii::app()->getConfig("siteadminemail"),
            'linkToAdminpanel' => $absoluteUrl,
            'username' => $this->user->users_name,
            'password' => $passwordResetUrl,
            'mainLogoFile' => \Yii::app()->createAbsoluteUrl(LOGO_URL),
            'showPasswordSection' => \Yii::app()->getConfig("auth_webserver") === false
            && \Permission::model() ->hasGlobalPermission('auth_db', 'read', $this->user->uid),
            'showPassword' => \Yii::app()->getConfig("display_user_password_in_email") === true,
        ];
    }
}
