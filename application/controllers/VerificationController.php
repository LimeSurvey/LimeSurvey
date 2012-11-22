<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 *  $Id$
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
        $iSurveyID=(int)$sid;
        Yii::app()->loadHelper('database');
        $sRootDir = Yii::app()->getConfig('rootdir');

        // header for png
        Header("Content-Type: image/png");

        // Create Image
        $oImage = ImageCreate(75, 20);
        $oColorBlack = ImageColorAllocate($oImage, 0, 0, 0);
        $oColorRed = ImageColorAllocate($oImage, 255, 0, 0);
        $oColorBlue = ImageColorAllocate($oImage, 0, 0, 255);
        $oColorGrey = ImageColorAllocate($oImage, 204, 204, 204);

        // Create the random numberes
        srand((double)microtime()*1000000);

        $iRandomNumber1 = rand(1,5);
        $bFound = false;
        while ($bFound == false)
        {
            $iRandomNumber2 = rand(1,100);
            if (preg_match('/^[0-9]+$/', $iRandomNumber2/5))
            {
                $bFound = true;
                break;
            }
        }
        $iRandomFontColor = rand(1,3);
        if ($iRandomFontColor == 1)
        {
            $oFontColor = $oColorBlack;
        } else if ($iRandomFontColor == 2)
        {
            $oFontColor = $oColorRed;
        } else if ($iRandomFontColor == 3)
        {
            $oFontColor = $oColorBlue;
        }

        $iRandomFontName = rand(1,2);//Maybe add other specific hard font
        if ($iRandomFontName == 1)
        {
            $sFont = $sRootDir."/fonts/FreeSans.ttf";
        } else {
            $sFont = $sRootDir."/fonts/DejaVuSans.ttf";
        }

        $iRandomLineColor = rand(1,3);
        if ($iRandomLineColor == 1)
        {
            $oLineColor = $oColorBlack;
        } else if ($iRandomLineColor == 2)
        {
            $oLineColor = $oColorRed;
        } else if ($iRandomLineColor == 3)
        {
            $oLineColor = $oColorBlue;
        }

        // Fill image, make transparent
        ImageFill($oImage, 0, 0, $oColorGrey);
        //imagecolortransparent ($im, $white);
        imageline($oImage,0,0,0,20,$oLineColor);
        imageline($oImage,74,0,74,19,$oLineColor);
        imageline($oImage,0,0,74,0,$oLineColor);
        imageline($oImage,0,19,74,19,$oLineColor);
        // Write math question in a nice TTF Font
        ImageTTFText($oImage, 10, 0, 3, 16,$oFontColor, $sFont,  $iRandomNumber1." + ".$iRandomNumber2." =" );

        // Display Image
        ImagePNG($oImage);
        ImageDestroy($oImage);

        // Add the answer to the session
        $_SESSION['survey_'.$iSurveyID]['secanswer']  = $iRandomNumber1+$iRandomNumber2;
    }
}

