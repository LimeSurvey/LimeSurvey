<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 11:30 AM
 */

namespace ls\models\questions;


/**
 * Class ArrayQuestion
 * The default array question that uses answers and subquestions.
 * @package ls\models\questions
 */
class ArrayQuestion extends BaseArrayQuestion
{
    /**
     * Returns the number of scales for answers.
     * @return int Range: {0, 1, 2}
     */
    public function getAnswerScales()
    {
        return 1;
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['array-flexible-row'];
    }


}