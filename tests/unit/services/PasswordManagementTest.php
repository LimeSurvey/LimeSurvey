<?php

namespace ls\tests;

use LimeSurvey\Models\Services\PasswordManagement;
use Yii;

/**
 * Tests for the admin creation email of PasswordManagement.
 */
class PasswordManagementTest extends TestBaseClass
{
    /** @var array<string, mixed> Original config values */
    private static $originalConfig = [];

    /** @var string[] Config keys changed by the tests */
    private static $configKeys = [
        'publicurl',
        'sitename',
        'siteadminemail',
        'admincreationemailsubject',
        'admincreationemailtemplate',
    ];

    /**
     * Save the config values changed by the tests.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        foreach (self::$configKeys as $key) {
            self::$originalConfig[$key] = Yii::app()->getConfig($key);
        }
        Yii::app()->setConfig('publicurl', 'http://example.org/');
        Yii::app()->setConfig('sitename', 'Test site');
        Yii::app()->setConfig('siteadminemail', 'admin@example.org');
    }

    /**
     * Restore the config values changed by the tests.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        foreach (self::$originalConfig as $key => $value) {
            Yii::app()->setConfig($key, $value);
        }
        parent::tearDownAfterClass();
    }

    /**
     * Get an unsaved user with a validation key.
     *
     * @return \User
     */
    private function getUser(): \User
    {
        $user = new \User();
        $user->users_name = 'newadmin';
        $user->full_name = 'New Admin';
        $user->email = 'newadmin@example.org';
        $user->validation_key = 'abcdef123456';
        return $user;
    }

    /**
     * Get a HTML LimeMailer prepared like PasswordManagement::sendAdminMail() does for registration.
     *
     * @param PasswordManagement $passwordManagement
     * @return \LimeMailer
     */
    private function getRegistrationMailer(PasswordManagement $passwordManagement): \LimeMailer
    {
        $mailer = new \LimeMailer();
        $mailer->isHtml(true);
        $mailer->addAndReplaceReplacement($passwordManagement->getAdminCreationEmailReplacements());
        return $mailer;
    }

    /**
     * Simple placeholders are replaced, {LOGINURL} stays a plain URL usable in href.
     *
     * @return void
     */
    public function testPlaceholdersAreReplaced(): void
    {
        Yii::app()->setConfig('admincreationemailsubject', 'Welcome to {SITENAME}');
        Yii::app()->setConfig('admincreationemailtemplate', '<p>{FULLNAME} ({USERNAME}) {SITEADMINEMAIL}</p><a href="{LOGINURL}">Set password</a>');
        $passwordManagement = new PasswordManagement($this->getUser());
        $rawEmail = $passwordManagement->getRawAdminCreationEmail();
        $mailer = $this->getRegistrationMailer($passwordManagement);

        $this->assertSame('Welcome to Test site', $mailer->doReplacements($rawEmail['subject']));
        $body = $mailer->doReplacements($rawEmail['body']);
        $this->assertStringContainsString('New Admin (newadmin) admin@example.org', $body);
        $this->assertMatchesRegularExpression('#<a href="http://example\.org/[^"]*abcdef123456[^"]*">Set password</a>#', $body);
    }

    /**
     * The barebone URL @@LOGINURL@@ is replaced by the plain login URL.
     *
     * @return void
     */
    public function testBareboneLoginUrlIsReplaced(): void
    {
        Yii::app()->setConfig('admincreationemailtemplate', '<p><a href="@@LOGINURL@@">Set password</a> Copy this link: @@LOGINURL@@</p>');
        $passwordManagement = new PasswordManagement($this->getUser());
        $rawEmail = $passwordManagement->getRawAdminCreationEmail();
        $body = $this->getRegistrationMailer($passwordManagement)->doReplacements($rawEmail['body']);

        $this->assertStringNotContainsString('@@LOGINURL@@', $body);
        $this->assertMatchesRegularExpression('#<a href="http://example\.org/[^"]*abcdef123456[^"]*">Set password</a> Copy this link: http://example\.org/\S*abcdef123456#', $body);
    }

    /**
     * Expressions using the placeholders are evaluated by Expression Manager.
     *
     * @return void
     */
    public function testExpressionManagerIsUsed(): void
    {
        Yii::app()->setConfig('admincreationemailtemplate', '<p>{if(USERNAME == "newadmin", "Hello boss", "Hello")}</p>');
        $passwordManagement = new PasswordManagement($this->getUser());
        $rawEmail = $passwordManagement->getRawAdminCreationEmail();
        $body = $this->getRegistrationMailer($passwordManagement)->doReplacements($rawEmail['body']);

        $this->assertStringContainsString('Hello boss', $body);
    }
}
