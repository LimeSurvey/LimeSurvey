<?php
namespace ls\models\questions;

use ls\interfaces\iSubQuestion;

class SubQuestion extends \Question implements iSubQuestion {
    /**
     * Returns this model's validation rules
     *
     */
    public function rules()
    {
        return [
            ['title', 'required'],
            ['title', 'length', 'min' => 1, 'max' => 5],

            ['title', 'match', 'pattern' => '/^[a-z0-9]*$/i',
                'message' => gT('Subquestion codes may only contain alphanumeric characters.'),
            ],
            ['relevance', 'safe'],
            ['question', 'safe']

        ];
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->getShortText();
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return "{$this->parent->title}_{$this->title}";
    }
}