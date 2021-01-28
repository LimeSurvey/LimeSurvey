<?php

namespace ls\tests;

use LSYii_Application;
use LSHttpRequest;
use LSWebUser;
use PermissionInterface;
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
            public function __construct() {}
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
            public function getOwnerId() {
                return 0;
            }
            public static function getPermissionData() {
                // TODO: Return something meaningful.
                return [];
            }
            public static function getMinimalPermissionRead() {
                return null;
            }
            public function hasPermission($sPermission, $sCRUD = 'read', $iUserID = null) {
                return false;
            }
            public function getPrimaryKey() {
                return 'id';
            }
        };

    }
}
