<?php
/*
* LimeSurvey
* Copyright (C) 2024 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* @author Denis Chenu
* @version 1.0
*/

/**
 * This widget display show the toitle of the page
 */
class PageTitle extends CWidget
{
    /* @var \Survey reaquired */
    public $model;
    /* @var string the primary title
     * if sprointf is true
     * - no question and no group : SID survey->id 
     * - Group and no question : GID and group->text (with ellispize) (WIP)
     * - Question : QID and question->tile (WIP)
     **/
    public $title;

    /** @inheritdoc */
    public function run()
    {
        $this->render('SurveyTitle');
    }
}
