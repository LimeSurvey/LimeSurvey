<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/20/15
 * Time: 11:14 AM
 */

namespace ls\models\questions;


class UploadQuestion extends \Question
{
    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        return ['upload-files'];
    }

}