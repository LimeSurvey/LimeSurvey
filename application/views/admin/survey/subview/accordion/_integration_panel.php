<?php
/**
 * @var $this AdminController
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
  <div id='panelintegration' class=" tab-pane fade in" >
    <div class="container-center">
        <!--div class="row table-responsive">
            <table id="urlparams" class='table dataTable table-hover table-borders' >
            <thead><tr>
                <th></th><th><?php eT('Action');?></th><th><?php eT('Parameter');?></th><th><?php eT('Target question');?></th><th></th><th></th><th></th>
            </tr></thead>
            </table>
            <input type='hidden' id='allurlparams' name='allurlparams' value='' />
        </div-->
        <div class="row">
            <div class="col-lg-12 ls-flex ls-flex-row">
                <div class="ls-flex-item text-left">
                    <button class="btn btn-success" id="addParameterButton"><?= gT('Add URL parameter') ?></button>
                </div>
                <div class="ls-flex-item text-right">
                    <!-- Search Box -->
                    <div class="form-inline">
                        <div class="form-group">
                            <label class="control-label text-right" for="search_query">Search:</label>
                            <input class="form-control" name="search_query" id="search_query" type="text">
                        </div>
                        <button class="btn btn-success" type="button" id="searchParameterButton" data-update-url="<?= $updateUrl ?>"><?= gT('Search', 'unescaped') ?></button>
                        <a href="<?= $updateUrl ?>" class="btn btn-warning"><?= gT('Reset') ?></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row table-responsive">
        <?php
            $this->widget(
                'ext.LimeGridView.LimeGridView',
                [
                    'id'              => 'urlparams',
                    'dataProvider'    => $model->search(),
                    'emptyText'       => gT('No parameters defined'),
                    'htmlOptions'     => ['class' => 'table-responsive grid-view-ls'],
                    'template'        => "{items}\n<div id='questiongroupListPager'><div class=\"col-sm-4\" id=\"massive-action-container\"></div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
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
                    'columns'         => [

                        // Action buttons (defined in model)
                        [
                            'header'      => gT('Action'),
                            'name'        => 'actions',
                            'type'        => 'raw',
                            'value'       => '$data->buttons',
                            'htmlOptions' => ['class' => ''],
                        ],
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
                            'value'  => '$data->questionTitle'
                        ],

                    ],
                    'ajaxUpdate'      => 'urlparams',
                    //'afterAjaxUpdate' => 'bindPageSizeChange'
                ]
            );
            ?>
            <input type='hidden' id='allurlparams' name='allurlparams' value='' />
        </div>
    </div>
</div>

<?php  
    App()->getClientScript()->registerScript('IntegrationPanel-variables', " 
    window.PanelIntegrationData = ".json_encode($jsData).";
    ", LSYii_ClientScript::POS_BEGIN ); 
?> 

<!-- Modal box to add a parameter -->
<!--div data-copy="submitsurveybutton"></div-->
<?php $this->renderPartial('addPanelIntegrationParameter_view', array('questions' => $questions)); ?>
