<?php

namespace LimeSurvey\Libraries\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputSurveyMenuItems extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        // all columns from MenuItems table
        $this->setDataMap([
            'id' => true,
            'menu_id' => 'menuId',
            'user_id' => 'userId',
            'ordering' => true,
            'name' => true,
            'title' => true,
            'menu_title' => 'menuTitle',
            'menu_description' => 'menuDescription',
            'menu_icon' => 'menuIcon',
            'menu_class' => 'menuClass',
            'menu_link' => 'menuLink',
            'action' => true,
            'template' => true,
            'partial' => true,
            'classes' => true,
            'permission' => true,
            'permission_grade' => 'permissionGrade',
            'data' => true,
            'getdatamethod' => 'getDataMethod',
            'language' => true,
            'showincollapse' => 'showInCollapse',
            'active' => true,
            'changed_at' => 'changedAt',
            'changed_by' => 'changedBy',
            'created_at' => 'createdAt',
            'created_by' => 'createdBy',
        ]);
    }
}
