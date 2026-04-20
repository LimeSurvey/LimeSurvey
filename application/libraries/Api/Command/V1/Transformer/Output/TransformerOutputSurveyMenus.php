<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputSurveyMenus extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'id' => true,
            'parent_id' => 'parentId',
            'user_id' => 'userId',
            'name' => true,
            'ordering' => true,
            'level' => true,
            'title' => true,
            'position' => 'position',
            'description' => 'description',
            'showincollapse' => 'showInCollapse',
            'active' => 'active',
            'created_at' => 'createdAt',
            'created_by' => 'createdBy',
            'changed_at' => 'changedAt',
            'changed_by' => 'changedBy'

        ]);
    }
}
