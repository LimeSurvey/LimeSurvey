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
 */
class Index extends Survey_Common_Action
{
    public function run()
    {

        $event = new PluginEvent('modifyStartpage');

        App()->getPluginManager()->dispatchEvent($event);

        $event->set('viewName', $event->get('viewName'));
        $event->set('viewUrl', $event->get('viewUrl'));


        if (Yii::app()->session['just_logged_in'])
        {
            if(!is_null($event->get('viewName'))){
                $this->_renderWrappedTemplate($event->get('viewUrl'), $event->get('viewName'));

            }else{
                $aViewUrls = array('message' => array(
                    'title' => gT("Logged in"),
                    'message' => Yii::app()->session['loginsummary']

                ));
                $this->_renderWrappedTemplate('/admin/super', $aViewUrls);
            }
            unset(Yii::app()->session['just_logged_in'], Yii::app()->session['loginsummary']);
        }
        elseif (count(getSurveyList(true)) == 0)
		{
            if(!is_null($event->get('viewName')))
            {
                $this->_renderWrappedTemplate($event->get('viewUrl'), $event->get('viewName'));

            }else{
                $this->_renderWrappedTemplate('/admin/super', 'firststeps');

            }
		}
        else
        {
            $this->getController()->redirect(array('admin/survey/sa/index'));
        }
    }
}
