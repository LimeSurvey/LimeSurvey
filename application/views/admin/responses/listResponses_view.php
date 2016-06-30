
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT('Survey responses'); ?></h3>

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
                            'name'=>'completed_filter',
                            'value'=>'$data->completed',
                            'filter'=>TbHtml::dropDownList(
                                'SurveyDynamic[completed_filter]',
                                $model->completed_filter,
                                array(''=>gT('all'),'Y'=>gT('Yes'),'N'=>gT('No')))
                        );

                        if ($bHaveToken)
                        {
                            $aColumns[] = array(
                                'header'=>'token',
                                'name'=>'token',
                                'type'=>'raw',
                                'value'=>'$data->tokenForGrid',

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
                        'ajaxUpdate' => false,
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
