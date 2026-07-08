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
        $floatingActions = require(__DIR__ . '/floating_actions/_actions.php');
        $this->widget('ext.admin.grid.FloatingActionsWidget.FloatingActionsWidget', [
            'pk'       => 'sid',
            'gridId'   => 'survey-grid',
            'aActions' => $floatingActions,
        ]);
        ?>
        <?php

        $surveyGrid = $this->widget('application.extensions.admin.grid.CLSGridView', [
            'dataProvider'          => $this->model->search(),
            // Number of row per page selection
            'id'                    => 'survey-grid',
            'caption'               => gT('List of surveys'),
            'emptyText'             => gT('No surveys found.'),
            'summaryText'           => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                    gT('%s rows per page'),
                    CHtml::dropDownList(
                        'surveygrid--pageSize',
                        $this->pageSize,
                        Yii::app()->params['pageSizeOptions'],
                        ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto',
                         'aria-label' => gT('Change page size')]
                    )
                ),
            'ajaxUpdate'            => 'survey-grid',
            'lsAfterAjaxUpdate'     => [
                'window.LS.doToolTip();',
                'LS.restoreFocusAfterSort("survey-grid");',
            ],
            'rowLink'               =>
                'Yii::app()->createUrl("surveyAdministration/view/",array("iSurveyID"=>$data->sid))',
            // 'template'  => $this->template,
            'showSelectionBar'      => false,
            'columns'               => $this->model->getColumns(),
            'lsAdditionalColumns' => $this->model->getAdditionalColumns(),

        ]);
        ?>
    </div>
</div>
