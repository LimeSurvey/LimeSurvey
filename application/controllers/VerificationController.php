<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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

/**
 * the Verification class, this is grouped with
 * other classes in the "limesurvey_yii" package and * is part of "controllers" subpackage
 * @package limesurvey_yii
 * @subpackage controllers
 */
class VerificationController extends LSYii_Controller
{
    function actionImage($sid)
    {
        $iSurveyID = (int) $sid;
        Yii::app()->loadHelper('database');
        $rootdir = Yii::app()->getConfig('rootdir');

        // header for png
        //Header("Content-Type: image/png");

        // Create Image
        $im = ImageCreate(75, 20);
        $white = ImageColorAllocate($im, 255, 255, 255);
        $black = ImageColorAllocate($im, 0, 0, 0);
        $red = ImageColorAllocate($im, 255, 0, 0);
        $blue = ImageColorAllocate($im, 0, 0, 255);
        $grey_shade = ImageColorAllocate($im, 204, 204, 204);

        // Create the random numberes
        srand((double) microtime() * 1000000);

        $num1 = rand(1, 5);
        $found = false;
        while ($found == false) {
            $num2 = rand(1, 100);
            if (preg_match('/^[0-9]+$/', $num2 / 5)) {
                $found = true;
                break;
            }
        }
        $font_c_rand = rand(1, 3);
        if ($font_c_rand == 1) {
            $font_color = $black;
        } else if ($font_c_rand == 2) {
            $font_color = $red;
        } else if ($font_c_rand == 3) {
            $font_color = $blue;
        }

        $font_rand = rand(1,5); //Maybe add other specific hard font
        switch ($font_rand) {
            case 1: $font = $rootdir."/assets/fonts/font-src/FreeSans.ttf"; break;
            case 2: $font = $rootdir."/assets/fonts/DejaVuSans.ttf"; break;
            case 3: $font = $rootdir."/assets/fonts/font-src/lato-v11-latin-700.ttf"; break;
            case 4: $font = $rootdir."/assets/fonts/font-src/news-cycle-v13-latin-regular.ttf"; break;
            case 5: $font = $rootdir."/assets/fonts/font-src/ubuntu-v9-latin-regular.ttf"; break;
        }

        $line_rand = rand(1, 3);
        if ($line_rand == 1) {
            $line_color = $black;
        } else if ($line_rand == 2) {
            $line_color = $red;
        } else if ($line_rand == 3) {
            $line_color = $blue;
        }

        // Fill image, make transparent
        ImageFill($im, 0, 0, $grey_shade);
        //imagecolortransparent ($im, $white);
        imageline($im, 0, 0, 0, 20, $line_color);
        imageline($im, 74, 0, 74, 19, $line_color);
        imageline($im, 0, 0, 74, 0, $line_color);
        imageline($im, 0, 19, 74, 19, $line_color);
        // Write math question in a nice TTF Font
        ImageTTFText($im, 10, 0, 3, 16, $font_color, $font, $num1." + ".$num2." =");

        // Display Image
        ImagePNG($im);
        ImageDestroy($im);

        // Add the answer to the session
        $_SESSION['survey_'.$iSurveyID]['secanswer'] = $num1 + $num2;
    }
}
