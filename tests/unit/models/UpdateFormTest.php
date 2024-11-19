<?php

namespace ls\tests;

use ls\tests\TestBaseClass;

class UpdateFormTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Make sure assets are not republished.
        $iAssetVersionNumber  = \Yii::app()->getConfig('assetsversionnumber');
        \SettingGlobal::setSetting('AssetsVersion', $iAssetVersionNumber);

        // Get superadmin permission.
        \Yii::app()->session['loginID'] = 1;

        // Set updatable option.
        \Yii::app()->setConfig('updatable', true);
    }

    protected function setUp(): void
    {
        // Refresh next update check variable.
        \Yii::app()->session['next_update_check'] = null;

        // Reset update results.
        \Yii::app()->session['update_result'] = null;
        \Yii::app()->session['unstable_update'] = null;
        \Yii::app()->session['security_update'] = null;
    }

    public function testStableSecurityUpdate()
    {
        $updateFormPartialMock = $this->createPartialMock(\UpdateForm::class, ['getUpdateInfo']);

        $updateInfo = new \stdClass();
        $updateInfo->result = true;
        $updateInfo->update = new \stdClass();

        $updateInfo->update->security_update = true;
        $updateInfo->update->branch = 'master';

        $updateFormPartialMock->expects($this->once())
                                ->method('getUpdateInfo')
                                ->willReturn($updateInfo);

        $updateNotification = $updateFormPartialMock->getUpdateNotification();

        $this->assertTrue($updateNotification->result, 'No result returned.');
        $this->assertTrue($updateNotification->security_update, 'The update should be a security one.');
        $this->assertFalse($updateNotification->unstable_update, 'The update should be a stable one.');

        $this->assertTrue(\Yii::app()->session['update_result'], 'No result returned.');
        $this->assertTrue(\Yii::app()->session['security_update'], 'The update should be a security one.');
        $this->assertFalse(\Yii::app()->session['unstable_update'], 'The update should be a stable one.');
    }

    public function testUnstableSecurityUpdate()
    {
        $updateFormPartialMock = $this->createPartialMock(\UpdateForm::class, ['getUpdateInfo']);

        $updateInfo = new \stdClass();
        $updateInfo->result = true;
        $updateInfo->update = new \stdClass();

        $updateInfo->update->security_update = true;
        $updateInfo->update->branch = 'branch';

        $updateFormPartialMock->expects($this->once())
                                ->method('getUpdateInfo')
                                ->willReturn($updateInfo);

        $updateNotification = $updateFormPartialMock->getUpdateNotification();

        $this->assertTrue($updateNotification->result, 'No result returned.');
        $this->assertTrue($updateNotification->security_update, 'The update should be a security one.');
        $this->assertTrue($updateNotification->unstable_update, 'The update should be an unstable one.');

        $this->assertTrue(\Yii::app()->session['update_result'], 'No result returned.');
        $this->assertTrue(\Yii::app()->session['security_update'], 'The update should be a security one.');
        $this->assertTrue(\Yii::app()->session['unstable_update'], 'The update should be an unstable one.');
    }

    public function testStableSecurityUpdates()
    {
        $updateFormPartialMock = $this->createPartialMock(\UpdateForm::class, ['getUpdateInfo']);

        $updateInfo = new \stdClass();
        $updateInfo->result = true;

        $updateInfo->updateOne = new \stdClass();

        $updateInfo->updateOne->security_update = true;
        $updateInfo->updateOne->branch = 'master';

        $updateInfo->updateTwo = new \stdClass();

        $updateInfo->updateTwo->security_update = true;
        $updateInfo->updateTwo->branch = 'master';

        $updateInfo->updateThree = new \stdClass();

        $updateInfo->updateThree->security_update = true;
        $updateInfo->updateThree->branch = 'master';

        $updateFormPartialMock->expects($this->once())
                                ->method('getUpdateInfo')
                                ->willReturn($updateInfo);

        $updateNotification = $updateFormPartialMock->getUpdateNotification();

        $this->assertTrue($updateNotification->result, 'No result returned.');
        $this->assertTrue($updateNotification->security_update, 'The update should be a security one.');
        $this->assertFalse($updateNotification->unstable_update, 'The update should be a stable one.');

        $this->assertTrue(\Yii::app()->session['update_result'], 'No result returned.');
        $this->assertTrue(\Yii::app()->session['security_update'], 'The update should be a security one.');
        $this->assertFalse(\Yii::app()->session['unstable_update'], 'The update should be a stable one.');
    }

    public function testUnstableSecurityUpdates()
    {
        $updateFormPartialMock = $this->createPartialMock(\UpdateForm::class, ['getUpdateInfo']);

        $updateInfo = new \stdClass();
        $updateInfo->result = true;

        $updateInfo->updateOne = new \stdClass();

        $updateInfo->updateOne->security_update = true;
        $updateInfo->updateOne->branch = 'master';

        $updateInfo->updateTwo = new \stdClass();

        $updateInfo->updateTwo->security_update = true;
        $updateInfo->updateTwo->branch = 'branch';

        $updateInfo->updateThree = new \stdClass();

        $updateInfo->updateThree->security_update = true;
        $updateInfo->updateThree->branch = 'master';

        $updateFormPartialMock->expects($this->once())
                                ->method('getUpdateInfo')
                                ->willReturn($updateInfo);

        $updateNotification = $updateFormPartialMock->getUpdateNotification();

        $this->assertTrue($updateNotification->result, 'No result returned.');
        $this->assertTrue($updateNotification->security_update, 'The update should be a security one.');
        $this->assertTrue($updateNotification->unstable_update, 'The update should be a stable one.');

        $this->assertTrue(\Yii::app()->session['update_result'], 'No result returned.');
        $this->assertTrue(\Yii::app()->session['security_update'], 'The update should be a security one.');
        $this->assertFalse(\Yii::app()->session['unstable_update'], 'The update should be a stable one.');
    }

    public function testStableNoSecurityUpdate()
    {
        $updateFormPartialMock = $this->createPartialMock(\UpdateForm::class, ['getUpdateInfo']);

        $updateInfo = new \stdClass();
        $updateInfo->result = true;
        $updateInfo->update = new \stdClass();

        $updateInfo->update->security_update = false;
        $updateInfo->update->branch = 'master';

        $updateFormPartialMock->expects($this->once())
                                ->method('getUpdateInfo')
                                ->willReturn($updateInfo);

        $updateNotification = $updateFormPartialMock->getUpdateNotification();

        $this->assertTrue($updateNotification->result, 'No result returned.');
        $this->assertFalse($updateNotification->security_update, 'The update should not be a security one.');
        $this->assertFalse($updateNotification->unstable_update, 'The update should be a stable one.');

        $this->assertTrue(\Yii::app()->session['update_result'], 'No result returned.');
        $this->assertFalse(\Yii::app()->session['security_update'], 'The update should not be a security one.');
        $this->assertFalse(\Yii::app()->session['unstable_update'], 'The update should be a stable one.');
    }

    public function testUnstableNoSecurityUpdate()
    {
        $updateFormPartialMock = $this->createPartialMock(\UpdateForm::class, ['getUpdateInfo']);

        $updateInfo = new \stdClass();
        $updateInfo->result = true;
        $updateInfo->update = new \stdClass();

        $updateInfo->update->security_update = false;
        $updateInfo->update->branch = 'branch';

        $updateFormPartialMock->expects($this->once())
                                ->method('getUpdateInfo')
                                ->willReturn($updateInfo);

        $updateNotification = $updateFormPartialMock->getUpdateNotification();

        $this->assertTrue($updateNotification->result, 'No result returned.');
        $this->assertFalse($updateNotification->security_update, 'The update should not be a security one.');
        $this->assertTrue($updateNotification->unstable_update, 'The update should not be a stable one.');

        $this->assertTrue(\Yii::app()->session['update_result'], 'No result returned.');
        $this->assertFalse(\Yii::app()->session['security_update'], 'The update should not be a security one.');
        $this->assertTrue(\Yii::app()->session['unstable_update'], 'The update should not be a stable one.');
    }

    public function testStableNoSecurityUpdates()
    {
        $updateFormPartialMock = $this->createPartialMock(\UpdateForm::class, ['getUpdateInfo']);

        $updateInfo = new \stdClass();
        $updateInfo->result = true;

        $updateInfo->updateOne = new \stdClass();

        $updateInfo->updateOne->security_update = false;
        $updateInfo->updateOne->branch = 'master';

        $updateInfo->updateTwo = new \stdClass();

        $updateInfo->updateTwo->security_update = false;
        $updateInfo->updateTwo->branch = 'master';

        $updateInfo->updateThree = new \stdClass();

        $updateInfo->updateThree->security_update = false;
        $updateInfo->updateThree->branch = 'master';

        $updateFormPartialMock->expects($this->once())
                                ->method('getUpdateInfo')
                                ->willReturn($updateInfo);

        $updateNotification = $updateFormPartialMock->getUpdateNotification();

        $this->assertTrue($updateNotification->result, 'No result returned.');
        $this->assertFalse($updateNotification->security_update, 'The update should not be a security one.');
        $this->assertFalse($updateNotification->unstable_update, 'The update should be a stable one.');

        $this->assertTrue(\Yii::app()->session['update_result'], 'No result returned.');
        $this->assertFalse(\Yii::app()->session['security_update'], 'The update should not be a security one.');
        $this->assertFalse(\Yii::app()->session['unstable_update'], 'The update should be a stable one.');
    }

    public function testUnstableNoSecurityUpdates()
    {
        $updateFormPartialMock = $this->createPartialMock(\UpdateForm::class, ['getUpdateInfo']);

        $updateInfo = new \stdClass();
        $updateInfo->result = true;

        $updateInfo->updateOne = new \stdClass();

        $updateInfo->updateOne->security_update = false;
        $updateInfo->updateOne->branch = 'master';

        $updateInfo->updateTwo = new \stdClass();

        $updateInfo->updateTwo->security_update = false;
        $updateInfo->updateTwo->branch = 'branch';

        $updateInfo->updateThree = new \stdClass();

        $updateInfo->updateThree->security_update = false;
        $updateInfo->updateThree->branch = 'master';

        $updateFormPartialMock->expects($this->once())
                                ->method('getUpdateInfo')
                                ->willReturn($updateInfo);

        $updateNotification = $updateFormPartialMock->getUpdateNotification();

        $this->assertTrue($updateNotification->result, 'No result returned.');
        $this->assertFalse($updateNotification->security_update, 'The update should not be a security one.');
        $this->assertTrue($updateNotification->unstable_update, 'The update should not be a stable one.');

        $this->assertTrue(\Yii::app()->session['update_result'], 'No result returned.');
        $this->assertFalse(\Yii::app()->session['security_update'], 'The update should not be a security one.');
        $this->assertFalse(\Yii::app()->session['unstable_update'], 'The update should not be a stable one.');
    }
}
