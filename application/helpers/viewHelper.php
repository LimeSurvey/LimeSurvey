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
*	$Id$
*/

/**
 * General helper class for generating views.
 */
class viewHelper
{

    /**
     * getImageLink returns HTML needed for a link that consists of only an image with alt text.
     *
     * Usage: getImageLink('test.png', 'controller/action/params', 'Your description', 'optionalClass', '_blank')
     *
     * @return string
     * @param string $imgName the name of the image to use, adminImageUrl will be added to it
     * @param string $linkUrl Url we want to go to, uses CController->createUrl()
     * @param string $linkTxt Text to show for the link
     * @param string $linkTarget Optional target to use for the link
     * @param string $linkclass Optional class to add to the link
     * @param array  $attribs Optional array of attirbutes to set on the link
     */
    public static function getImageLink($imgName, $linkUrl, $linkTxt, $linkTarget = null, $linkClass = 'imagelink', $attribs = array())
    {
        if (!is_null($linkUrl) && $linkUrl != '#') {
            $linkUrl = Yii::app()->getController()->createUrl($linkUrl);
        } else {
            $linkUrl = "#";
        }
        $output = '<a href="' . $linkUrl;
        if (!empty($linkClass)) {
            $output .= '" class="' . $linkClass . '"';
        }
        if (!empty($linkTarget)) {
            $output .= ' target="' . $linkTarget . '"';
        }
        if (!empty($attribs)) {
            foreach($attribs as $attrib => $value) {
                $output .= ' ' . $attrib . '="' . str_replace('"', '&quot;', $value) . '"';
            }
        }
        $output .= '><img src="' . Yii::app()->getConfig('adminimageurl') . $imgName . '" alt="' . $linkTxt. '" title="' . $linkTxt. '"></a>';

        return $output;
    }

    /**
     * getFieldText returns complete field information text.
     *
     * Usage: getFieldText($field, $option)
     *
     * @return string
     * @param array $field the field information from createFieldMap
     * @param array $option option for filtering
     */
    public static function getFieldText($field, $option=array())
    {
        // Default options
        if(!isset($option['flat'])){$option['flat']=true;}

        if(isset($field['fieldname']))
        {
            $questiontext=$field['question'];
            if(isset($field['scale']) && $field['scale'])
            {
                $questiontext.="[{$field['scale']}]";
            }
            if(isset($field['subquestion']) && $field['subquestion'])
            {
                $questiontext.="[{$field['subquestion']}]";
            }
            if(isset($field['subquestion1']) && $field['subquestion1'])
            {
                $questiontext.="[{$field['subquestion1']}]";
            }
            if(isset($field['subquestion2']) && $field['subquestion2'])
            {
                $questiontext.="[{$field['subquestion2']}]";
            }
        }
        else
        {
            $questiontext="";
        }
        if ($option['flat'])
        {
            $questiontext=flattenText($questiontext,false,true);
        }
        return $questiontext;
    }

    /**
     * getFieldCode returns complete field information code.
     *
     * Usage: getFieldCode($field, $option)
     *
     * @return string
     * @param array $field the field information from createFieldMap
     * @param array $option option for filtering
     */
    public static function getFieldCode($field, $option=array())
    {
        if(isset($field['fieldname']))
        {
            if(isset($field['title']) && $field['title'])
            {
                $questioncode=$field['title'];
                if(isset($field['scale']) && $field['scale'])
                {
                    $scalenum=intval($field['scale_id'])+1;
                    $questioncode.="[".$scalenum."]";
                }
                if(isset($field['aid']) && $field['aid'])
                {
                    $questioncode.="[".$field['aid']."]";
                }
            }
            else
            {
                $questioncode=$field['fieldname'];
            }
        }
        else
        {
            $questioncode="";
        }
        return $questioncode;
    }
}
