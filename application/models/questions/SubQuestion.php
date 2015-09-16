<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 5/22/15
 * Time: 4:44 PM
 */

namespace ls\models\questions;


class SubQuestion extends \Question {
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

}