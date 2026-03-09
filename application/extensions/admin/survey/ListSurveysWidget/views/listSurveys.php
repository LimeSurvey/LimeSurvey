<?php
/*
* LimeSurvey
* Copyright (C) 2007-2016 The LimeSurvey Project Team / Carsten Schmitz
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
                        ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                    )
                ),
            'ajaxUpdate'            => 'survey-grid',
            'lsAfterAjaxUpdate'     => [
                'window.LS.doToolTip();',
                'bindListItemclick();',
                'switchStatusOfListActions();',
                'restoreFocusAfterSort();',
            ],
            'rowLink'               =>
                'App()->getConfig("editorEnabled") && Yii::app()->getConfig("debug")'
                . ' ? App()->createUrl("editorLink/index", ["route" => "survey/" . $data->sid]) '
                . ' : Yii::app()->createUrl("surveyAdministration/view/",array("iSurveyID"=>$data->sid))',
            // 'template'  => $this->template,
            'massiveActionTemplate' => $this->render('massive_actions/_selector', [], true, false),
            'columns'               => $this->model->getColumns(),
            'lsAdditionalColumns' => $this->model->getAdditionalColumns(),

        ]);
        ?>
        <?php
        // Keep focus on the clicked sort column after grid AJAX update instead of moving to select-all checkbox.
        // Re-attach listener after each update because #survey-grid is replaced on pagination/sort.
        App()->getClientScript()->registerScript(
            'ListSurveysWidget-restoreFocusAfterSort',
            "
        window._lastSortColumnIndex = null;
        var _sortFocusCaptureHandler = function(e) {
            var link = e.target.closest && e.target.closest('a.sort-link');
            if (link) {
                var th = link.closest('th');
                if (th) {
                    window._lastSortColumnIndex = Array.prototype.indexOf.call(th.parentNode.children, th);
                }
            }
        };
        function attachSortFocusCapture() {
            var grid = document.getElementById('survey-grid');
            if (!grid) return;
            grid.removeEventListener('click', _sortFocusCaptureHandler, true);
            grid.addEventListener('click', _sortFocusCaptureHandler, true);
        }
        function restoreFocusAfterSort() {
            if (window._lastSortColumnIndex != null) {
                var \$th = jQuery('#survey-grid table thead th').eq(window._lastSortColumnIndex);
                var \$link = \$th.find('a.sort-link');
                if (\$link.length) { \$link[0].focus(); }
                window._lastSortColumnIndex = null;
            }
            attachSortFocusCapture();
        }
        jQuery(function() { attachSortFocusCapture(); });
        ",
            LSYii_ClientScript::POS_POSTSCRIPT
        );
        ?>
    </div>
</div>
