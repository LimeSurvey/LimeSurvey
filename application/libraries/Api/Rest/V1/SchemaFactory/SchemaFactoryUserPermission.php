<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryUserPermission
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        $aGlobalPermissions = [
            'participantpanel',
            'labelsets',
            'settings',
            'surveysgroups',
            'surveys',
            'templates',
            'usergroups',
            'users',
            'superadmin',
            'auth_db'
        ];

        $aSurveyPermissions = [
            'assessments',
            'quotas',
            'statistics',
            'survey',
            'surveyactivation',
            'surveycontent',
            'surveylocale',
            'surveysecurity',
            'surveysettings',
            'tokens',
            'translations',
        ];

        $aGlobalSchemaObj = array_map(fn($p) => AllOf::create($p)->schemas((new SchemaFactoryPermission())->make()), $aGlobalPermissions);
        $aSurveySchemaObj = array_map(fn($p) => AllOf::create($p)->schemas((new SchemaFactoryPermission())->make()), $aSurveyPermissions);

        return Schema::create()
            ->title('User Permissions')
            ->description('User Permissions')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::object('permissions')->properties(
                    Schema::object('global')->properties(
                        ...$aGlobalSchemaObj
                    ),
                    Schema::object('surveys')->properties(
                        // ENTITY_ID
                        Schema::object('entity_id')->properties(
                            ...$aSurveySchemaObj
                        )
                    )
                )
            );
    }
}
