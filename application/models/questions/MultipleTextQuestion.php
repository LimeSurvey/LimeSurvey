<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 5/11/15
 * Time: 5:12 PM
 */

namespace ls\models\questions;


class MultipleTextQuestion extends TextQuestion
{
    public function getSubQuestionScales()
    {
        return 1;
    }

}