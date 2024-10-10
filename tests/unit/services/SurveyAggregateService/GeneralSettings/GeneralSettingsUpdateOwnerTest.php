<?php

namespace ls\tests\unit\services\SurveyAggregateService\GeneralSettings;

use Mockery;
use Permission;
use ls\tests\TestBaseClass;

class GeneralSettingsUpdateOwnerTest extends TestBaseClass
{
    /** @var User */
    private static $dummyOwner;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /**
         * GeneralSettings service relies on getUserList() to check if the owner is
         * visible to the current user. That function is not mockable, so we need to
         * create a dummy user that will be visible to the current user.
         */
        self::$dummyOwner = self::createUserWithPermissions(
            [
                "users_name" => "updateownertest",
                "full_name" => "updateownertest",
                "email" => "updateownertest@example.com",
                "lang" => "auto",
                "password" => "updateownertest",
                "parent_id" => 123,
            ],
            [
                'auth_db' => [
                    'read' => 'on'
                ],
            ]
        );
    }

    public function testCanUpdateOwnerIdIfUserIsCurrentOwner()
    {
        $mockSet = (new GeneralSettingsMockSetFactory)->make();

        $mockSet->modelPermission->shouldReceive('hasGlobalPermission')
            ->andReturn(false);

        $mockSet->session['loginID'] = 123;

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'owner_id' => 123
        ], false);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'owner_id' => self::$dummyOwner->uid
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals(self::$dummyOwner->uid, $attributes['owner_id']);
    }

    public function testCanNotUpdateOwnerIdIfUserIsNotCurrentOwner()
    {
        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();
        $modelPermission->shouldReceive('hasSurveyPermission')
            ->andReturn(true);
        $modelPermission->shouldReceive('hasGlobalPermission')
            ->andReturn(false);

        $mockSet = (new GeneralSettingsMockSetFactory)->make();
        $mockSet->modelPermission = $modelPermission;

        $mockSet->session['loginID'] = self::$dummyOwner->uid;

        $mockSet->survey->setAttributes([
            'sid' => 1,
            'owner_id' => 123
        ], false);

        $generalSettings = (new GeneralSettingsFactory)->make($mockSet);

        $generalSettings->update(1, [
            'owner_id' => self::$dummyOwner->uid
        ]);

        $attributes = $mockSet->survey->getAttributes();

        $this->assertEquals(123, $attributes['owner_id']);
    }
}

