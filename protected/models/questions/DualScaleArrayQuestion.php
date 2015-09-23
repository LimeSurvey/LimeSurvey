<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 12:26 PM
 */

namespace ls\models\questions;


class DualScaleArrayQuestion extends BaseArrayQuestion{
    /**
     * Returns the number of scales for answers.
     * @return int Range: {0, 1, 2}
     */
    public function getAnswerScales()
    {
        return 2;
    }


    public function getColumns()
    {
        $result = [];
        foreach(parent::getColumns() as $name => $type) {
            $result["{$name}_0"] = $type;
            $result["{$name}_1"] = $type;
        }
        return $result;
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     *
     */
    public function getClasses()
    {
        return ['array-flexible-dual-scale'];
    }

}
