<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputUserPermissions extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'create_p' => ['key' => 'create', 'formatter' => ['intToBool' => true]],
            'read_p' => ['key' => 'read', 'formatter' => ['intToBool' => true]],
            'update_p' => ['key' => 'update', 'formatter' => ['intToBool' => true]],
            'delete_p' => ['key' => 'delete', 'formatter' => ['intToBool' => true]],
            'import_p' => ['key' => 'import', 'formatter' => ['intToBool' => true]],
            'export_p' => ['key' => 'export', 'formatter' => ['intToBool' => true]],
        ]);
    }

    public function transform($data, $options = [])
    {
        $permissions = ['global' => [], 'survey' => []];
        if (!empty($data)) {
            foreach ($data as $permission) {
                $permissionType = $permission['permission'];
                unset($permission['permission']);

                $permissionTransformed = parent::transform($permission, $options);
                if ($permission['entity'] === 'global') {
                    $permissions['global'][$permissionType] = $permissionTransformed;
                }
                if ($permission['entity'] === 'survey') {
                    $permissions['survey'][$permission['entity_id']][$permissionType] = $permissionTransformed;
                }
            }
        }

        return $permissions;
    }
}
