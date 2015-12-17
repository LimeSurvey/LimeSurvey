<?php
namespace ls\models;

use Yii;

class LabelSet extends ActiveRecord
{
    /**
     * Returns the table's name
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{labelsets}}';
    }

    /**
     * Returns this model's validation rules
     *
     */
    public function rules()
    {
        return [
            ['label_name', 'length', 'min' => 1, 'max' => 100],
            ['label_name', 'required'],
            ['languageArray', 'required'],
        ];
    }

    public function getLanguageArray()
    {
        return explode(',', $this->languages);
    }
    public function setLanguageArray(array $value)
    {
        $this->languages = implode(',', $value);
    }

    public function relations()
    {
        return [
            'labels' => [self::HAS_MANY, Label::class, 'lid']
        ];
    }


}
