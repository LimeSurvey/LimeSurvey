<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 11:47 AM
 */

namespace ls\models\questions;

/**
 * Class FixedArrayQuestion
 * Base class for array questions that have fixed answers.
 * @package ls\models\questions
 */
abstract class FixedArrayQuestion extends BaseArrayQuestion
{


    /**
     * Returns the number of scales for answers.
     * @return int Range: {0, 1, 2}
     */
    public function getAnswerScales()
    {
        return 0;
    }

}