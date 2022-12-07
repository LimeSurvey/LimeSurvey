<?php

namespace ls\tests;

use LSYii_Application;
use LSHttpRequest;
use LSWebUser;
use CDbCriteria;
use PermissionInterface;
use PermissionTrait;
use LimeSurvey\Models\Services\PermissionManager;
use PHPUnit\Framework\TestCase;

class PermissionManagerTest extends TestCase
{
    public function testGetPermissionData()
    {
        $request = $this
            ->getMockBuilder(LSHttpRequest::class)
            ->getMock();

        $user = $this
            ->getMockBuilder(LSWebUser::class)
            ->getMock();

        $model = $this->getModel();

        // NB: Can't mock App.
        $app = new class extends LSYii_Application {
            public function __construct()
            {
                // Nothing to do
            }
        };

        $manager = new PermissionManager(
            $request,
            $user,
            $model,
            $app
        );
        $data = $manager->getPermissionData(1);
        $this->assertCount(0, $data);
    }

    /**
     * @return PermissionInterface
     */
    public function getModel()
    {
        return new class implements PermissionInterface {
            use PermissionTrait;

            public function getOwnerId()
            {
                return 0;
            }
            public static function getPermissionData()
            {
                // TODO: Return something meaningful.
                return [];
            }
            public static function getMinimalPermissionRead()
            {
                return null;
            }
            public function hasPermission($sPermission, $sCRUD = 'read', $iUserID = null)
            {
                return false;
            }
            public function getPrimaryKey()
            {
                return 'id';
            }
            public static function getPermissionCriteria($iUserID = null)
            {
                return new CDbCriteria();
            }
            public function withListRight($userid = null)
            {
                return $this;
            }
        };
    }
}
