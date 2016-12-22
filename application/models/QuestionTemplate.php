<?php

if (!defined('BASEPATH'))
    die('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
  */


class QuestionTemplate extends CFormModel
{

    /**
     * Called from admin, to generate the template list for a given question type
     */
    static public function getQuestionTemplateList($type)
    {
        $sUserQTemplateRootDir  = Yii::app()->getConfig("userquestiontemplaterootdir");
        $aQuestionTemplates     = array();

        $aQuestionTemplates['core'] = gT('Default');

        $aTypeToFolder  = self::getTypeToFolder($type);
        $sFolderName    = $aTypeToFolder[$type];

        if ($sUserQTemplateRootDir && is_dir($sUserQTemplateRootDir) ){

            $handle = opendir($sUserQTemplateRootDir);
            while (false !== ($file = readdir($handle))){
                // Maybe $file[0] != "." to hide Linux hidden directory
                if (!is_file("$sUserQTemplateRootDir/$file") && $file != "." && $file != ".." && $file!=".svn"){

                        if (is_dir("$sUserQTemplateRootDir/$file/survey/questions/answer/$sFolderName")){
                            $templateName = $file;
                            $aQuestionTemplates[$file] = $templateName;
                        }
                    }
                }
        }
        return $aQuestionTemplates;
    }

    /**
     * Correspondance between question type and the view folder name
     * Rem: should be in question model. We keep it here for easy access
     */
    static public function getTypeToFolder()
    {
        return array(
            "1" => 'arrays/dualscale',
            "5" => '5pointchoice',
            "A" => 'arrays/5point',
            "B" => 'arrays/10point',
            "C" => 'arrays/yesnouncertain',
            "D" => 'date',
            "E" => 'arrays/increasesamedecrease',
            "F" => 'arrays/array',
            "G" => 'gender',
            "H" => 'arrays/column',
            "I" => 'language',
            "K" => 'multiplenumeric',
            "L" => 'listradio',
            "M" => 'multiplechoice',
            "N" => 'numerical',
            "O" => 'list_with_comment',
            "P" => 'multiplechoice_with_comments',
            "Q" => 'multipleshorttext',
            "R" => 'ranking',
            "S" => 'shortfreetext',
            "T" => 'longfreetext',
            "U" => 'longfreetext',
            "X" => 'boilerplate',
            "Y" => 'yesno',
            "!" => 'list_dropdown',
            ":" => 'arrays/multiflexi',
            ";" => 'arrays/texts',
            "|" => 'file_upload',
            "*" => 'equation',
        );
    }

}
