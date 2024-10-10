<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputSurveyOwner extends TransformerOutputActiveRecord
{
    public function __construct()
    {
        $this->setDataMap([
            'uid' => true,
            'users_name' => 'name',
            'full_name' => 'fullName',
            'parent_id' => 'parentId',
            'lang' => true,
            'email' => true,
            'htmleditormode' => 'htmlEditorMode',
            'templateeditormode' => 'templateEditorMode',
            'questionselectormode' => 'questionSelectorMode',
            'dateformat' => ['key' => 'dateFormat', 'type' => 'int'],
            'last_login' => [
                'key' => 'lastLogin',
                'formatter' => ['dateTimeToJson' => ['revert' => true]]
            ],
            'created' => [
                'formatter' => ['dateTimeToJson' => ['revert' => true]]
            ],
            'modified' => [
                'formatter' => ['dateTimeToJson' => ['revert' => true]]
            ],
            'user_status' => 'userStatus',
        ]);
    }
}
