<?php

namespace ls\tests;

use FailedLoginAttempt;

class FailedLoginAttempTest extends TestBaseClass
{
    public function testAddDeleteAttemp()
    {
        // Save Ip
        $ip = substr(getRealIPAddress(), 0, 40);

        // Make sure there are no records for the ip
        FailedLoginAttempt::model()->deleteAttempts(FailedLoginAttempt::TYPE_LOGIN);
        $this->assertNull(FailedLoginAttempt::model()->findByAttributes((array('ip' => $ip))));

        // Verify that the try counter increases by one
        FailedLoginAttempt::model()->addAttempt();
        $data = FailedLoginAttempt::model()->findByAttributes((array('ip' => $ip)));
        $this->assertEquals(1, $data->number_attempts);

        // Verify that the try counter increases by one
        FailedLoginAttempt::model()->addAttempt();
        $data = FailedLoginAttempt::model()->findByAttributes((array('ip' => $ip)));
        $this->assertEquals(2, $data->number_attempts);

        // Verify that all records are deleted
        FailedLoginAttempt::model()->deleteAttempts(FailedLoginAttempt::TYPE_LOGIN);
        $this->assertNull(FailedLoginAttempt::model()->findByAttributes((array('ip' => $ip))));
    }

    public function testIsLockedOut()
    {
        $maxLoginAttempt = \Yii::app()->getConfig('maxLoginAttempt');

        // Verify that the user has attempts available
        FailedLoginAttempt::model()->deleteAttempts(FailedLoginAttempt::TYPE_LOGIN);
        for ($i = 0; $i < $maxLoginAttempt - 1; $i++) {
            FailedLoginAttempt::model()->addAttempt();
            $this->assertFalse(FailedLoginAttempt::model()->isLockedOut(FailedLoginAttempt::TYPE_LOGIN));
        }

        // Verify that the user has no attempts available
        FailedLoginAttempt::model()->addAttempt();  
        $this->assertTrue(FailedLoginAttempt::model()->isLockedOut(FailedLoginAttempt::TYPE_LOGIN));
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        FailedLoginAttempt::model()->deleteAttempts(FailedLoginAttempt::TYPE_LOGIN);

        parent::tearDownAfterClass();
    }    
}
