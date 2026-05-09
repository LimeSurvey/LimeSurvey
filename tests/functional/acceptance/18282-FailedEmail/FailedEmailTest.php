<?php

namespace ls\tests;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\TimeoutException;
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

        $urlManager = App()->urlManager;
        $web = self::$webDriver;

        $resendVars = "{\"message_type\":\"alt\",\"Subject\":\"Response submission for survey Surveytest 1 Question\",\"uniqueid\":\"2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"boundary\":{\"1\":\"b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"2\":\"b2_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"3\":\"b3_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\"},\"MIMEBody\":\"This is a multi-part message in MIME format.\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\\r\\nContent-Type: text\\\/plain; charset=us-ascii\\r\\n\\r\\nHello,A new response was submitted for your survey 'Surveytest 1 Question'.Click the following link to see the individual response:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=responses\\\/view&surveyId=565531&id=34Click the following link to edit the individual response:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/dataentry\\\/sa\\\/editdata\\\/subaction\\\/edit\\\/surveyid\\\/565531\\\/id\\\/34View statistics by clicking here:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/statistics\\\/sa\\\/index\\\/surveyid\\\/565531\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\\r\\nContent-Type: text\\\/html; charset=us-ascii\\r\\n\\r\\n<html>Hello,<br \\\/><br \\\/>A new response was submitted for your survey 'Surveytest 1 Question'.<br \\\/><br \\\/>Click the following link to see the individual response:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=responses\\\/view&surveyId=565531&id=34<br \\\/><br \\\/>Click the following link to edit the individual response:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/dataentry\\\/sa\\\/editdata\\\/subaction\\\/edit\\\/surveyid\\\/565531\\\/id\\\/34<br \\\/><br \\\/>View statistics by clicking here:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/statistics\\\/sa\\\/index\\\/surveyid\\\/565531<\\\/html>\\r\\n\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg--\\r\\n\"}";

        $urlManager->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlManager->createUrl('failedEmail/index/', ['surveyid' => self::$surveyId]);

        // Helper: create a fresh failed email record
        $createRecord = function () use ($resendVars) {
            // Clean up any leftover records
            FailedEmail::model()->deleteAllByAttributes(['surveyid' => self::$surveyId]);
            $m = new FailedEmail();
            $m->recipient = 'test@example.com';
            $m->responseid = 1;
            $m->surveyid = self::$surveyId;
            $m->email_type = 'admin_notification';
            $m->language = 'en';
            $m->error_message = 'test error message display';
            $m->created = date('Y-m-d H:i:s');
            $m->status = FailedEmail::STATE_FAILED;
            $m->updated = date('Y-m-d H:i:s');
            $m->resend_vars = $resendVars;
            $saved = $m->save(false);
            if (!$saved) {
                throw new \RuntimeException('Failed to save FailedEmail fixture: ' . json_encode($m->getErrors()));
            }
        };

        // Helper: navigate to page and wait for grid action dropdown
        $loadPage = function () use ($web, $url) {
            $web->get($url);
            $web->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#failedemail-grid tbody tr:first-child .ls-dropdown-toggle')
                )
            );
        };

        // Helper: open an action from the dropdown by contentFile value
        $openAction = function (string $contentFile) use ($web) {
            $web->findElement(WebDriverBy::cssSelector('#failedemail-grid tbody tr:first-child .ls-dropdown-toggle'))->click();
            $web->wait(5)->until(WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('.dropdown-menu.show')
            ));
            // Ensure click handlers are bound, then click
            $web->executeScript(
                "LS.FailedEmail.bindButtons(); document.querySelector('.failedemail-action-modal-open[data-contentFile=\"" . $contentFile . "\"]').click();"
            );
            // Wait for modal to appear
            $web->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('#failedemail-action-modal.show')
            ));
        };

        // --- Resend Email ---
        $createRecord();
        $loadPage();
        $openAction('resend_form');
        $web->wait(15)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-action-modal--form')));
        // Uncheck "delete after resend" so the record stays
        $web->executeScript("document.getElementById('deleteAfterResend').checked = false;");
        $web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #submitForm'))->click();
        $web->wait(30)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--resendresult')));
        $this->assertTrue($web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--resendresult'))->isDisplayed());

        // --- Email Content (fresh record + page load) ---
        $createRecord();
        $loadPage();
        $openAction('email_content');
        $web->wait(15)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--emailcontent')));
        $this->assertTrue($web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--emailcontent'))->isDisplayed());

        // --- Error message (fresh record + page load) ---
        $createRecord();
        $loadPage();
        $openAction('email_error');
        $web->wait(15)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--emailerror')));
        $this->assertTrue($web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--emailerror'))->isDisplayed());

        // --- Delete Email (fresh record + page load) ---
        $createRecord();
        $loadPage();
        $openAction('delete_form');
        $web->wait(15)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-action-modal--form')));
        $web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #submitForm'))->click();
        $web->wait(15)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--deleteresult')));
        $this->assertTrue($web->findElement(WebDriverBy::cssSelector('#failedemail-action-modal #failedemail-action-modal--deleteresult'))->isDisplayed());
    }

    /**
     * Test FailedEmail Massive Actions
     * @throws Exception
     */
    public function testMassiveActions(): void
    {

        $urlManager = App()->urlManager;
        $web = self::$webDriver;

        $resendVars = "{\"message_type\":\"alt\",\"Subject\":\"Response submission for survey Surveytest 1 Question\",\"uniqueid\":\"2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"boundary\":{\"1\":\"b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"2\":\"b2_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\",\"3\":\"b3_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\"},\"MIMEBody\":\"This is a multi-part message in MIME format.\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\\r\\nContent-Type: text\\\/plain; charset=us-ascii\\r\\n\\r\\nHello,A new response was submitted for your survey 'Surveytest 1 Question'.Click the following link to see the individual response:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=responses\\\/view&surveyId=565531&id=34Click the following link to edit the individual response:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/dataentry\\\/sa\\\/editdata\\\/subaction\\\/edit\\\/surveyid\\\/565531\\\/id\\\/34View statistics by clicking here:http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/statistics\\\/sa\\\/index\\\/surveyid\\\/565531\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg\\r\\nContent-Type: text\\\/html; charset=us-ascii\\r\\n\\r\\n<html>Hello,<br \\\/><br \\\/>A new response was submitted for your survey 'Surveytest 1 Question'.<br \\\/><br \\\/>Click the following link to see the individual response:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=responses\\\/view&surveyId=565531&id=34<br \\\/><br \\\/>Click the following link to edit the individual response:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/dataentry\\\/sa\\\/editdata\\\/subaction\\\/edit\\\/surveyid\\\/565531\\\/id\\\/34<br \\\/><br \\\/>View statistics by clicking here:<br \\\/>http:\\\/\\\/127.0.0.1:8083\\\/index.php?r=admin\\\/statistics\\\/sa\\\/index\\\/surveyid\\\/565531<\\\/html>\\r\\n\\r\\n\\r\\n--b1_2xVoMczyB0mqe8SPHO9qAsO0AKGd5jvqY76gJT8bNg--\\r\\n\"}";

        // Helper: create two fresh failed email records
        $createRecords = function () use ($resendVars) {
            FailedEmail::model()->deleteAllByAttributes(['surveyid' => self::$surveyId]);
            foreach ([1, 2] as $responseId) {
                $m = new FailedEmail();
                $m->recipient = 'test@example.com';
                $m->surveyid = self::$surveyId;
                $m->responseid = $responseId;
                $m->email_type = 'admin_notification';
                $m->language = 'en';
                $m->error_message = 'test error message display';
                $m->created = date('Y-m-d H:i:s');
                $m->status = FailedEmail::STATE_FAILED;
                $m->updated = date('Y-m-d H:i:s');
                $m->resend_vars = $resendVars;
                $saved = $m->save(false);
                if (!$saved) {
                    throw new \RuntimeException('Failed to save FailedEmail fixture (responseId=' . $responseId . '): ' . json_encode($m->getErrors()));
                }
            }
        };

        $urlManager->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlManager->createUrl('failedEmail/index/', ['surveyid' => self::$surveyId]);

        // --- Massive action Resend Email ---
        $createRecords();
        $web->get($url);
        $web->wait(15)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#failedemail-grid [name="id_all"]')));
        // Click select-all checkbox and trigger change event to enable massive action button
        $web->findElement(WebDriverBy::cssSelector('#failedemail-grid [name="id_all"]'))->click();
        $web->executeScript("$('.grid-view-ls input[type=\"checkbox\"]').trigger('change');");
        $web->wait(15)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#failedEmailActions [data-bs-toggle="dropdown"]:not([disabled])')));
        $web->findElement(WebDriverBy::cssSelector('#failedEmailActions [data-bs-toggle="dropdown"]'))->click();
        $web->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedEmailActions [data-action="resend"]')));
        $web->findElement(WebDriverBy::cssSelector('#failedEmailActions [data-action="resend"]'))->click();
        $web->wait(15)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-resend-1 .btn-ok')));
        $web->findElement(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-resend-1 .btn-ok'))->click();
        // this can take up around 20 seconds per mail if the email server cant be reached
        $web->wait(50)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-resend-1 #failedemail-action-modal--resendresult')));
        $this->assertTrue($web->findElement(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-resend-1 #failedemail-action-modal--resendresult'))->isDisplayed());

        // --- Massive action Delete Email (fresh records + page load) ---
        $createRecords();
        $web->get($url);
        $web->wait(15)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#failedemail-grid [name="id_all"]')));
        $web->findElement(WebDriverBy::cssSelector('#failedemail-grid [name="id_all"]'))->click();
        $web->executeScript("$('.grid-view-ls input[type=\"checkbox\"]').trigger('change');");
        $web->wait(15)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#failedEmailActions [data-bs-toggle="dropdown"]:not([disabled])')));
        $web->findElement(WebDriverBy::cssSelector('#failedEmailActions [data-bs-toggle="dropdown"]'))->click();
        $web->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#failedEmailActions [data-action="delete"]')));
        $web->findElement(WebDriverBy::cssSelector('#failedEmailActions [data-action="delete"]'))->click();
        $web->wait(15)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-delete-0 .btn-ok')));
        $web->findElement(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-delete-0 .btn-ok'))->click();
        $web->wait(30)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-delete-0 #failedemail-action-modal--deleteresult')));
        $this->assertTrue($web->findElement(WebDriverBy::cssSelector('#massive-actions-modal-failedemail-grid-delete-0 #failedemail-action-modal--deleteresult'))->isDisplayed());
    }
}
