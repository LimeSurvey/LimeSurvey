<?php
namespace ls\models;

class DefaultValue extends ActiveRecord
{
    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{defaultvalues}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return array
     */
    public function primaryKey()
    {
        return array('qid', 'specialtype', 'scale_id', 'sqid', 'language');
    }

    /**
     * Relations with questions
     *
     * @access public
     * @return array
     */
    public function relations()
    {
        $alias = $this->getTableAlias();

        return array(
            'question' => array(
                self::HAS_ONE,
                'ls\models\Question',
                '',
                'on' => "$alias.qid = question.qid",
            ),
        );
    }

    function insertRecords($data)
    {
        $values = new self;
        foreach ($data as $k => $v) {
            $values->$k = $v;
        }

        return $values->save();
    }
}
