<?php
/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
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
 * @var $this ListSurveysWidget
 */

?>

<!-- Grid -->
<div class="row">
    <div class="col-12">
        <?php
        // Render the floating action bar (cross-page selection, fixed at bottom)
        $floatingActions = \actions\SurveyListMassiveActions::getActions();
        $this->widget('ext.admin.grid.FloatingActionsWidget.FloatingActionsWidget', [
            'pk'           => 'sid',
            'gridId'       => 'survey-grid',
            'aActions'     => $floatingActions,
            'selectAllUrl' => Yii::app()->createUrl('surveyAdministration/getAllSurveyIds'),
        ]);
        ?>
        <?php

        $surveyGrid = $this->widget('application.extensions.admin.grid.CLSGridView', [
            'dataProvider'          => $this->model->search(),
            // Number of row per page selection
            'id'                    => 'survey-grid',
            'lsCaption'               => gT('List of surveys'),
            'emptyText'             => gT('No surveys found.'),
            'lsPageSizeCurrentValue'  => $this->pageSize,
            'ajaxUpdate'            => 'survey-grid',
            'lsAfterAjaxUpdate'     => [
                'window.LS.doToolTip();',
                'LS.restoreFocusAfterSort("survey-grid");',
            ],
            'lsRowLink'               =>
                'Yii::app()->createUrl("surveyAdministration/view/",array("iSurveyID"=>$data->sid))',
            'lsShowSelectionBar'      => false,
            'columns'               => $this->model->getColumns(),
            'lsAdditionalColumns' => $this->model->getAdditionalColumns(),

        ]);
        ?>
    </div>
</div>
