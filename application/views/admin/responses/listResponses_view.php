
<script type='text/javascript'>
    var strdeleteconfirm='<?php eT('Do you really want to delete this response?', 'js'); ?>';
    var strDeleteAllConfirm='<?php eT('Do you really want to delete all marked responses?', 'js'); ?>';
    var noFilesSelectedForDeletion = '<?php eT('Please select at least one file for deletion', 'js'); ?>';
</script>

<script type='text/javascript'>
    var sCaption ='';
    var sWarningMsg = "<?php eT("Warning", 'js') ?>";
    var sSelectRowMsg = "<?php eT("Please select at least one response.", 'js') ?>";
    var sSelectColumns ='<?php eT("Select columns",'js');?>';
    var sRecordText = '<?php eT("View {0} - {1} of {2}",'js');?>';
    var sPageText = '<?php eT("Page {0} of {1}",'js');?>';
    var sLoadText = '<?php eT("Loading...",'js');?>';
    var sDelTitle = '<?php eT("Delete selected response(s)",'js');?>';
    var sDelCaption = '<?php eT("Delete",'js');?>';
    var sSearchCaption = '<?php eT("Filter...",'js');?>';
    var sOperator1= '<?php eT("equal",'js');?>';
    var sOperator2= '<?php eT("not equal",'js');?>';
    var sOperator3= '<?php eT("less",'js');?>';
    var sOperator4= '<?php eT("less or equal",'js');?>';
    var sOperator5= '<?php eT("greater",'js');?>';
    var sOperator6= '<?php eT("greater or equal",'js');?>';
    var sOperator7= '<?php eT("begins with",'js');?>';
    var sOperator8= '<?php eT("does not begin with",'js');?>';
    var sOperator9= '<?php eT("is in",'js');?>';
    var sOperator10= '<?php eT("is not in",'js');?>';
    var sOperator11= '<?php eT("ends with",'js');?>';
    var sOperator12= '<?php eT("does not end with",'js');?>';
    var sOperator13= '<?php eT("contains",'js');?>';
    var sOperator14= '<?php eT("does not contain",'js');?>';
    var sOperator15= '<?php eT("is null",'js');?>';
    var sOperator16= '<?php eT("is not null",'js');?>';
    var sFind= '<?php eT("Filter",'js');?>';
    var sReset= '<?php eT("Reset",'js');?>';
    var sSelectColumns= '<?php eT("Select columns",'js');?>';
    var sSubmit= '<?php eT("Save",'js');?>';

    var sCancel = '<?php eT("Cancel",'js');?>';
    var sSearchTitle ='<?php eT("Filter responses",'js');?>';
    var sRefreshTitle ='<?php eT("Reload responses list",'js');?>';
    var delBtnCaption ='<?php eT("Delete",'js');?>';
    var sEmptyRecords ='<?php eT("There are currently no responses.",'js');?>';
    var jsonBaseUrl = "<?php echo App()->createUrl('/admin/responses', array('surveyid'=>$surveyid, 'browselang'=>$language)); ?>";
    var jsonUrl = "<?php echo App()->createUrl('/admin/responses', array('sa'=> 'getResponses_json', 'surveyid' => $surveyid,'browselang'=>$language)); ?>";
    var jsonActionUrl = "<?php echo App()->createUrl('/admin/responses', array('sa'=> 'actionResponses', 'surveyid' => $surveyid,'browselang'=>$language)); ?>";

    var colNames = <?php echo $column_names_txt; ?>;
    var colModels = <?php echo $column_model_txt; ?>;
    <?php if($hasUpload) { ?>
        var sDownLoad='<?php eT("Download files"); ?>' ;
        var sDownLoadMarked='<?php eT("Download marked files"); ?>' ;
        var sDownLoadAll='<?php eT("Download all files"); ?>' ;
        var sConfirmationArchiveMessage='<?php eT("This function creates a ZIP archive of several survey archives and can take some time - please be patient! Do you want to continue?",'js');?>';
    <?php } ?>
</script>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT('Survey responses'); ?></h3>
    <div class="row">
        <div id="displayResponsesContainer" class="content-right" style="overflow-x: scroll; padding-bottom: 2em">
            <table id="displayresponses"></table> <div id="pager" style="position: relative;"></div>
        </div>
    </div>

<?php
$columns = array_keys($model->metaData->columns);
$columns[array_search('column_name', $columns)] = array(
    'name' => 'column_name',
    'value' => '',
);
?>

    <div class="row">
            <div class="content-right scrolling-wrapper"    >
                <?php

                    $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);

                    $bHaveToken=$surveyinfo['anonymized'] == "N" && tableExists('tokens_' . $iSurveyId) && Permission::model()->hasSurveyPermission($iSurveyId,'tokens','read');// Boolean : show (or not) the token
                    $massiveAction = App()->getController()->renderPartial('/admin/responses/massive_actions/_selector', array(), true, false);

                    $aDefaultColumns = array('id', 'token', 'submitdate', 'lastpage','startlanguage');

                    $aColumns = array(
                        array(
                            'id'=>'id',
                            'class'=>'CCheckBoxColumn',
                            'selectableRows' => '100',
                        ),

                        array(
                            'header' => '',
                            'name' => 'actions',
                            'id'=>'action',
                            'value'=>'$data->buttons',
                            'type'=>'raw',
                            'htmlOptions' => array('class' => 'text-left'),
                            'filter'=>false,
                        ),

                        array(
                            'header' => 'id',
                            'name' => 'id',
                        ));

                        $aColumns[] = array(
                            'header'=>'lastpage',
                            'name'=>'lastpage',
                        );

                        $aColumns[] = array(
                            'header'=>gT("completed"),
                            'name'=>'submitdate',
                            'value'=>'$data->completed'
                        );

                        if ($bHaveToken)
                        {
                            $aColumns[] = array(
                                'header'=>'token',
                                'name'=>'token',
                                'type'=>'raw',
                                'value'=>'$data->tokenForGrid'
                            );

                            $aColumns[] = array(
                                'header'=>gT("First name"),
                                'name'=>'tokens.firstname',
                                'id'=>'firstname'

                            );

                            $aColumns[] = array(
                                'header'=>gT("Last name"),
                                'name'=>'tokens.lastname',
                                'id'=>'lastname'
                            );

                            $aColumns[] = array(
                                'header'=>gT("Email"),
                                'name'=>'tokens.email',
                                'id'=>'email'
                            );
                        }

                        $aColumns[] = array(
                            'header'=>'startlanguage',
                            'name'=>'startlanguage',
                        );


                    $fieldmap=createFieldMap($surveyid, 'full', true, false, $language);
                    foreach($model->metaData->columns as $column)
                    {
                        if(!in_array($column->name, $aDefaultColumns))
                        {
                            $colName = viewHelper::getFieldCode($fieldmap[$column->name],array('LEMcompat'=>true));
                            $base64jsonFieldMap = base64_encode(json_encode($fieldmap[$column->name]));

                            $aColumns[]=
                                array(
                                    'header' => $colName,
                                    'name' => $column->name,
                                    'type' => 'raw',
                                    'value' => '$data->getExtendedData("'.$column->name.'", "'.$language.'", "'.$base64jsonFieldMap.'")',
                                );
                        }
                    }

                    $this->widget('bootstrap.widgets.TbGridView', array(
                        'dataProvider' => $model->search(),
                        'filter'=>$model,
                        'columns' => $aColumns,
                        'itemsCssClass' =>'table-striped',
                        'id' => 'responses-grid',
                        'ajaxUpdate' => true,
                        'template'  => "{items}\n<div id='ListPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                        'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                Yii::app()->params['pageSizeOptions'],
                                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),
                    ));

                ?>
            </div>
            <!-- To update rows per page via ajax -->
            <script type="text/javascript">
                jQuery(function($) {
                    jQuery(document).on("change", '#pageSize', function(){
                        $.fn.yiiGridView.update('responses-grid',{ data:{ pageSize: $(this).val() }});
                    });
                });
            </script>

    </div>
</div>
