<?php
/**
 * @var $this AdminController
 * @var $model SurveyURLParameter
 *
 * Right accordion, integration panel
 * Use datatables, needs surveysettings.js
 */
$yii = Yii::app();
$controller = $yii->getController();
$pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyPanelIntegration');
?>
<!-- Datatable translation-data -->
<!-- Container -->
<div id='panelintegration' class="tab-pane fade show active">
        <div class="row">
            <div class="col-lg-12 ls-flex ls-flex-row">
                <div class="ls-flex-item text-start">
                    <button
                            class="btn btn-primary"
                            id="addParameterButton"
                            data-bs-toggle="modal"
                            data-bs-target="#dlgEditParameter">
                        <?= gT('Add URL parameter') ?>
                    </button>
                </div>
                <div class="ls-flex-item justify-content-end row row-cols-lg-auto g-1 align-items-center mb-3">
                    <!-- Search Box -->
                    <div class="col-12">
                        <label class="control-label text-right" for="search_query">Search:</label>
                    </div>
                    <div class="col-12">
                        <input class="form-control" name="search_query" id="search_query" type="text">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="button" id="searchParameterButton"><?= gT('Search', 'unescaped') ?></button>
                        <a href="<?= $updateUrl ?>" class="btn btn-warning" role="button"><?= gT('Reset') ?></a>
                    </div>
                </div>
            </div>
        </div>
    <div class="row table-responsive">
        <?php
            $this->widget(
                'application.extensions.admin.grid.CLSGridView',
                [
                    'id' => 'urlparams',
                    'dataProvider'    => $model->search(),
                    'emptyText'       => gT('No parameters defined'),
                    'htmlOptions'     => ['class' => 'table-responsive grid-view-ls'],
                    'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                        gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            [
                                'class' => 'changePageSize form-control',
                                'style' => 'display: inline; width: auto'
                            ]
                        )
                    ),

                    // Columns to dispplay
                    'columns' => [
                        // Parameter
                        [
                            'header' => gT('Parameter'),
                            'name'   => 'parameter',
                            'value'  => '$data->parameter'
                        ],
                        // Target Question
                        [
                            'header' => gT('Target question'),
                            'name'   => 'target_question',
                            'value'  => '$data->questionTitle',
                            'type'=>'raw'
                        ],
                        // Action buttons (defined in model)
                        [
                            'header'      => gT('Action'),
                            'name'        => 'actions',
                            'type'        => 'raw',
                            'value'       => '$data->buttons',
                            'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                            'htmlOptions'       => ['class' => 'text-center ls-sticky-column'],
                        ],
                    ],
                    'ajaxUpdate' => 'urlparams',
                    'lsAfterAjaxUpdate' => [],
                    'rowHtmlOptionsExpression' => '["data-id" => $data->id, "data-parameter" => $data->parameter, "data-qid" => $data->targetqid, "data-sqid" => $data->targetsqid]',
                ]
            );
            ?>
    </div>
</div>

<?php
App()->getClientScript()->registerScript(
    'IntegrationPanel-variables',
    "window.PanelIntegrationData = " . json_encode($jsData) . ";
     window.sEnterValidParam = '" . gT('You have to enter a valid parameter name.', 'js') . "';",
    LSYii_ClientScript::POS_BEGIN
);
?>

<!-- Modal box to add a parameter -->
<!--div data-copy="submitsurveybutton"></div-->
<?php  $this->renderPartial('addPanelIntegrationParameter_view', ['questions' => $questions]); ?>