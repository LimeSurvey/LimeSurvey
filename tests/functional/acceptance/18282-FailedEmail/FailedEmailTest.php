<?php

namespace ls\tests;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\TimeOutException;
use FailedEmail;

class FailedEmailTest extends TestBaseClassWeb
{
    /**
     * Import survey in tests/surveys/.
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /* Login */
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }
        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }
        // Browser login.
        self::adminLogin($username, $password);

        $filename = self::$surveysFolder . '/survey_archive_2_basic_responses.lsa';
        self::importSurvey($filename);
    }

    /**
     * Test FailedEmail Grid buttons
     * @throws Exception
     */
    public function testGridButtons(): void
    {
        // TODO: Disable in epic until fixed
        $this->markTestSkipped();

        $urlManager = App()->urlManager;
        $web = self::$webDriver;

        $failedEmailModel = new FailedEmail();
        $failedEmailModel->recipient = 'test@example.com';
        $failedEmailModel->responseid = 1;
        $failedEmailModel->surveyid = self::$surveyId;
        $failedEmailModel->email_type = 'admin_notification';
        $failedEmailModel->language = 'en';
        $failedEmailModel->error_message = 'test error message display';
        $failedEmailModel->created = date('Y-m-d H:i:s');
        $failedEmailModel->status = FailedEmail::STATE_FAILED;
        $failedEmailModel->updated = date('Y-m-d H:i:s');
        $failedEmailModel->resend_vars = "{\"message_type\":\"alt\",\"Subject\":\"Response submission for survey Surveytest 1 Question\",\"uniqueid\":\"2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"boundary\":{\"1\":\"b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"2\":\"b2_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"3\":\"b3_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\"},\"MIMEBody\":\"This is a multi-part message in MIME format.\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\\r\\nContent-Type: text\\\/plain; charset=us-ascii\\r\\n\\r\\nHello,A new response was submitted for your survey 'Surveytest 1 Question'.Click the following link to see the individual response:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=responses\\\/view&surveyId=565531&id=34Click the following link to edit the individual response:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/dataentry\\\/sa\\\/editdata\\\/subaction\\\/edit\\\/surveyid\\\/565531\\\/id\\\/34View statistics by clicking here:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/statistics\\\/sa\\\/index\\\/surveyid\\\/565531\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\\r\\nContent-Type: text\\\/html; charset=us-ascii\\r\\n\\r\\n<html>Hello,<br \\\/><br \\\/>A new response was submitted for your survey 'Surveytest 1 Question'.<br \\\/><br \\\/>Click the following link to see the individual response:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=responses\\\/view&surveyId=565531&id=34<br \\\/><br \\\/>Click the following link to edit the individual response:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/dataentry\\\/sa\\\/editdata\\\/subaction\\\/edit\\\/surveyid\\\/565531\\\/id\\\/34<br \\\/><br \\\/>View statistics by clicking here:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/statistics\\\/sa\\\/index\\\/surveyid\\\/565531<\\\/html>\\r\\n\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg--\\r\\n\"}";
        $failedEmailModel->save(false);

        $urlManager->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlManager->createUrl('failedEmail/index/', ['surveyid' => self::$surveyId]);
        $web->get($url);
        $web->wait(5)->until(WebDriverExpectedCondition::urlIs($url));

        $web->dismissModal();
        $web->dismissModal();

        // Resend Email
        $resendEmail = $web->findElement(WebDriverBy::cssSelector('#failedemail-grid tbody tr:first-child [data-contentfile="resend_form"]'));
        $resendEmail->click();
        $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-action-modal--form')));
        $resendEmailModalSubmit = $web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #submitForm'));
        $resendEmailModalSubmit->click();
        // this can take up around 20 seconds per mail if the email server cant be reached
        $web->wait(30)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--resendresult')));
        $successModal = $web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--resendresult'));
        $this->assertTrue($successModal->isDisplayed());

        // Email Content
        $exitModal = $web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #exitForm'));
        $exitModal->click();
        $web->wait(5)->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('.modal-backdrop')));
        $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-grid:not(.grid-view-loading)')));
        $emailContent = $web->findElement(WebDriverBy::cssSelector('#failedemail-grid tbody tr:first-child [data-contentfile="email_content"]'));
        $emailContent->click();
        $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--emailcontent')));
        $emailContentModal = $web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--emailcontent'));
        $this->assertTrue($emailContentModal->isDisplayed());

        // Error message
        $exitModal = $web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #exitForm'));
        $exitModal->click();
        $web->wait(5)->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('.modal-backdrop')));
        $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-grid:not(.grid-view-loading)')));
        $errorMessage = $web->findElement(WebDriverBy::cssSelector('#failedemail-grid tbody tr:first-child [data-contentfile="email_error"]'));
        $errorMessage->click();
        $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--emailerror')));
        $errorMessageModal = $web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--emailerror'));
        $this->assertTrue($errorMessageModal->isDisplayed());

        // Delete Email
        $exitModal = $web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #exitForm'));
        $exitModal->click();
        $web->wait(5)->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('.modal-backdrop')));
        $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-grid:not(.grid-view-loading)')));
        $deleteEmail = $web->findElement(WebDriverBy::cssSelector('#failedemail-grid tbody tr:first-child [data-contentfile="delete_form"]'));
        $deleteEmail->click();
        $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-action-modal--form')));
        $deleteEmailModalSubmit = $web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #submitForm'));
        $deleteEmailModalSubmit->click();
        $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--deleteresult')));
        $successModal = $web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--deleteresult'));
        $this->assertTrue($successModal->isDisplayed());
    }

    /**
     * Test FailedEmail Massive Actions
     * @throws Exception
     */
    public function testMassiveActions(): void
    {
        // TODO: Disable in epic until fixed
        $this->markTestSkipped();

        $urlManager = App()->urlManager;
        $web = self::$webDriver;

        // prepare Massive Action
        $failedEmailModel = new FailedEmail();
        $failedEmailModel->recipient = 'test@example.com';
        $failedEmailModel->surveyid = self::$surveyId;
        $failedEmailModel->responseid = 1;
        $failedEmailModel->email_type = 'admin_notification';
        $failedEmailModel->language = 'en';
        $failedEmailModel->error_message = 'test error message display';
        $failedEmailModel->created = date('Y-m-d H:i:s');
        $failedEmailModel->status = FailedEmail::STATE_FAILED;
        $failedEmailModel->updated = date('Y-m-d H:i:s');
        $failedEmailModel->resend_vars = "{\"message_type\":\"alt\",\"Subject\":\"Response submission for survey Surveytest 1 Question\",\"uniqueid\":\"2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"boundary\":{\"1\":\"b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"2\":\"b2_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"3\":\"b3_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\"},\"MIMEBody\":\"This is a multi-part message in MIME format.\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\\r\\nContent-Type: text\\\/plain; charset=us-ascii\\r\\n\\r\\nHello,A new response was submitted for your survey 'Surveytest 1 Question'.Click the following link to see the individual response:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=responses\\\/view&surveyId=565531&id=34Click the following link to edit the individual response:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/dataentry\\\/sa\\\/editdata\\\/subaction\\\/edit\\\/surveyid\\\/565531\\\/id\\\/34View statistics by clicking here:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/statistics\\\/sa\\\/index\\\/surveyid\\\/565531\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\\r\\nContent-Type: text\\\/html; charset=us-ascii\\r\\n\\r\\n<html>Hello,<br \\\/><br \\\/>A new response was submitted for your survey 'Surveytest 1 Question'.<br \\\/><br \\\/>Click the following link to see the individual response:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=responses\\\/view&surveyId=565531&id=34<br \\\/><br \\\/>Click the following link to edit the individual response:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/dataentry\\\/sa\\\/editdata\\\/subaction\\\/edit\\\/surveyid\\\/565531\\\/id\\\/34<br \\\/><br \\\/>View statistics by clicking here:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/statistics\\\/sa\\\/index\\\/surveyid\\\/565531<\\\/html>\\r\\n\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg--\\r\\n\"}";
        $failedEmailModel->save(false);

        $failedEmailModel2 = new FailedEmail();
        $failedEmailModel2->recipient = 'test@example.com';
        $failedEmailModel2->surveyid = self::$surveyId;
        $failedEmailModel2->responseid = 2;
        $failedEmailModel2->email_type = 'admin_notification';
        $failedEmailModel2->language = 'en';
        $failedEmailModel2->error_message = 'test error message display';
        $failedEmailModel2->created = date('Y-m-d H:i:s');
        $failedEmailModel2->status = FailedEmail::STATE_FAILED;
        $failedEmailModel2->updated = date('Y-m-d H:i:s');
        $failedEmailModel2->resend_vars = "{\"message_type\":\"alt\",\"Subject\":\"Response submission for survey Surveytest 1 Question\",\"uniqueid\":\"2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"boundary\":{\"1\":\"b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"2\":\"b2_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"3\":\"b3_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\"},\"MIMEBody\":\"This is a multi-part message in MIME format.\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\\r\\nContent-Type: text\\\/plain; charset=us-ascii\\r\\n\\r\\nHello,A new response was submitted for your survey 'Surveytest 1 Question'.Click the following link to see the individual response:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=responses\\\/view&surveyId=565531&id=34Click the following link to edit the individual response:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/dataentry\\\/sa\\\/editdata\\\/subaction\\\/edit\\\/surveyid\\\/565531\\\/id\\\/34View statistics by clicking here:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/statistics\\\/sa\\\/index\\\/surveyid\\\/565531\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\\r\\nContent-Type: text\\\/html; charset=us-ascii\\r\\n\\r\\n<html>Hello,<br \\\/><br \\\/>A new response was submitted for your survey 'Surveytest 1 Question'.<br \\\/><br \\\/>Click the following link to see the individual response:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=responses\\\/view&surveyId=565531&id=34<br \\\/><br \\\/>Click the following link to edit the individual response:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/dataentry\\\/sa\\\/editdata\\\/subaction\\\/edit\\\/surveyid\\\/565531\\\/id\\\/34<br \\\/><br \\\/>View statistics by clicking here:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/statistics\\\/sa\\\/index\\\/surveyid\\\/565531<\\\/html>\\r\\n\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg--\\r\\n\"}";
        $failedEmailModel2->save(false);

        $urlManager->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlManager->createUrl('failedEmail/index/', ['surveyid' => self::$surveyId]);
        $web->get($url);
        $web->wait(5)->until(WebDriverExpectedCondition::urlIs($url));

        // Massive action Resend Email
        $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-grid:not(.grid-view-loading)')));
        $checkboxAll = $web->findElement(WebDriverBy::cssSelector('#failedemail-grid .checkbox-column [name="id_all"]'));
        $checkboxAll->click();
        $massiveAction = $web->findElement(WebDriverBy::cssSelector('#failedEmailActions .dropdown-toggle'));
        $massiveAction->click();
        $massiveActionResend = $web->findElement(WebDriverBy::cssSelector('#failedEmailActions [data-action="resend"]'));
        $massiveActionResend->click();
        try {
            $web->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-resend-1 .btn-ok')));
        } catch (TimeOutException $ex) {
            $body = $web->findElement(WebDriverBy::tagName('body'));
            var_dump($body->getText());
            throw $ex;
        }
        $massiveActionResendSubmit = $web->findElement(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-resend-1 .btn-ok'));
        $massiveActionResendSubmit->click();
        // this can take up around 20 seconds per mail if the email server cant be reached
        $web->wait(50)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-resend-1 #failedemail-action-modal--resendresult')));
        $massiveActionResendSuccess = $web->findElement(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-resend-1 #failedemail-action-modal--resendresult'));
        $this->assertTrue($massiveActionResendSuccess->isDisplayed());

        // Massive action Delete Email
        $exitModal = $web->findElement(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-resend-1 .modal-header .close'));
        $exitModal->click();
        $web->wait(5)->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('.modal-backdrop')));
        $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-grid:not(.grid-view-loading)')));
        $checkboxAll = $web->findElement(WebDriverBy::cssSelector('#failedemail-grid .checkbox-column [name="id_all"]'));
        $checkboxAll->click();
        $massiveAction = $web->findElement(WebDriverBy::cssSelector('#failedEmailActions .dropdown-toggle'));
        $massiveAction->click();
        $web->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedEmailActions [data-action="delete"]')));
        $massiveActionDelete = $web->findElement(WebDriverBy::cssSelector('#failedEmailActions [data-action="delete"]'));
        $massiveActionDelete->click();
        $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-delete-0 .btn-ok')));
        $massiveActionDeleteSubmit = $web->findElement(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-delete-0 .btn-ok'));
        $massiveActionDeleteSubmit->click();
        $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-delete-0 #failedemail-action-modal--deleteresult')));
        $massiveActionDeleteSuccess = $web->findElement(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-delete-0 #failedemail-action-modal--deleteresult'));
        $this->assertTrue($massiveActionDeleteSuccess->isDisplayed());
    }
}
