<?php

namespace ls\tests;

use LimeSurvey\Models\Services\PasswordManagement;

/**
 * Tests the core password validation rules and random password generator
 * (moved from the former PasswordRequirement plugin, bug #20551).
 */
class PasswordValidationTest extends TestBaseClass
{
    public function testValidatePasswordRulesValid()
    {
        // No rules: anything is accepted.
        $this->assertEmpty(PasswordManagement::validatePasswordRules('a', []));

        $rules = ['min' => 8, 'max' => 0, 'lower' => 1, 'upper' => 1, 'numeric' => 1, 'symbol' => 1];
        $this->assertEmpty(
            PasswordManagement::validatePasswordRules('abcAB12@#', $rules),
            'A password meeting every rule should not produce errors.'
        );
    }

    public function testValidatePasswordRulesInvalid()
    {
        $this->assertNotEmpty(
            PasswordManagement::validatePasswordRules('short', ['min' => 8]),
            'Too short password should fail.'
        );
        $this->assertNotEmpty(
            PasswordManagement::validatePasswordRules('abcdefghij', ['max' => 8]),
            'Too long password should fail.'
        );
        $this->assertNotEmpty(
            PasswordManagement::validatePasswordRules('abcdefgh', ['upper' => 1]),
            'Missing uppercase should fail.'
        );
        $this->assertNotEmpty(
            PasswordManagement::validatePasswordRules('ABCDEFGH', ['lower' => 1]),
            'Missing lowercase should fail.'
        );
        $this->assertNotEmpty(
            PasswordManagement::validatePasswordRules('abcdefgh', ['numeric' => 1]),
            'Missing digit should fail.'
        );
        $this->assertNotEmpty(
            PasswordManagement::validatePasswordRules('abcdefgh1', ['symbol' => 1]),
            'Missing special character should fail.'
        );
    }

    public function testGenerateRandomPasswordHonorsRules()
    {
        $previous = \Yii::app()->getConfig('passwordValidationRules');
        \Yii::app()->setConfig('passwordValidationRules', [
            'min' => 12, 'max' => 0, 'lower' => 0, 'upper' => 1, 'numeric' => 1, 'symbol' => 1,
        ]);

        $password = PasswordManagement::generateRandomPassword(8);
        $this->assertGreaterThanOrEqual(12, strlen($password), 'Length should be raised to the configured minimum.');
        $this->assertMatchesRegularExpression('/[A-Z]/', $password, 'Should contain an uppercase letter.');
        $this->assertMatchesRegularExpression('/[0-9]/', $password, 'Should contain a digit.');
        $this->assertMatchesRegularExpression('/[^\w]/', $password, 'Should contain a special character.');

        // The generated password must itself pass validation against the same rules.
        $this->assertEmpty(
            PasswordManagement::validatePasswordRules($password, \Yii::app()->getConfig('passwordValidationRules')),
            'Generated password should satisfy the configured rules.'
        );

        \Yii::app()->setConfig('passwordValidationRules', $previous);
    }
}
