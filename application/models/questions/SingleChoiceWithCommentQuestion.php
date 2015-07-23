<?php
namespace ls\models\questions;

/**
 * @package ls\models\questions
 */
class SingleChoiceWithCommentQuestion extends SingleChoiceQuestion
{
    /**
     * @return array Column definitions for SingleChoiceQuestion type(s)
     */
    public function getColumns()
    {
        $result = parent::getColumns();
        $result[$this->sgqa . 'comment'] = 'text';
        return $result;
    }

    public function getFields() {
        $result = parent::getFields();


        $result[$this->sgqa . 'other'] = [
            'fieldname' => $this->getSgqa() . 'other',
            'type' => $this->type,
            'qid' => $this->primaryKey,
            'sid' => $this->sid,
            'gid' => $this->gid,
            'aid' => '',
            'title' => $this->title,
            'question' => $this->question,
            'group_name' => $this->group->group_name,
            'mandatory' => $this->bool_mandatory,
            'hasconditions' => count($this->conditions) > 0,
            'usedinconditions' => count($this->conditionsAsTarget) > 0,
            'subquestion' => gT("Comment"),
            'defaultvalue' => '@todo'

        ];
    }


}