<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* $Id: statistics_function.php 10193 2011-06-05 12:20:37Z c_schmitz $
*
*/



function getSurveyDefaultSettings()
{
    return array(
    'active'=>'N',
    'allowjumps'               => 'N',
    'format'                   => 'G', //Group-by-group mode
    'template'                 => $this->config->item('defaulttemplate'),
    'allowsave'                => 'Y',
    'allowprev'                => 'N',
    'nokeyboard'               => 'N',
    'printanswers'             => 'N',
    'publicstatistics'         => 'N',
    'publicgraphs'             => 'N',
    'public'                   => 'Y',
    'autoredirect'             => 'N',
    'tokenlength'              => 15,
    'allowregister'            => 'N',
    'usecookie'                => 'N',
    'usecaptcha'               => 'D',
    'htmlemail'                => 'Y',
    'emailnotificationto'      => '',
    'anonymized'               => 'N',
    'datestamp'                => 'N',
    'ipaddr'                   => 'N',
    'refurl'                   => 'N',
    'tokenanswerspersistence'  => 'N',
    'alloweditaftercompletion' => 'N',
    'assesments'               => 'N',
    'startdate'                => '',
    'savetimings'              => 'N',
    'expires'                  => '',
    'showqnumcode'             => 'X',
    'showwelcome'              => 'Y',
    'emailresponseto'          => '',
    'assessments'              => 'N',
    'navigationdelay'          => 0);
}