<?php
namespace ls\models\questions;

use Response;

/**
 * Class ChoiceQuestion
 * @package ls\models\questions
 */
class ChoiceQuestion extends \Question
{
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
           'bool_other_comment_mandatory' => gT("'Other:' comment mandatory")
        ]);
    }

    /**
     * Returns this model's validation rules
     *
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['bool_other_comment_mandatory'], 'boolean'],
        ]);
    }
}