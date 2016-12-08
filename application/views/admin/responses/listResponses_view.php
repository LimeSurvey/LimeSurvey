<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT('Survey responses'); ?></h3>


    <!-- Display mode -->
    <div class="text-right in-title">
        <div class="pull-right">
            <div class="form text-right">
                <div class="form-group">

                    <label for="display-mode">
                        <?php
                            eT('Display mode:');
                        ?>
                    </label>
                    <?php
                        $state = Yii::app()->user->getState('responsesGridSwitchDisplayState') == "" ? 'compact' : Yii::app()->user->getState('responsesGridSwitchDisplayState');
                        $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup',
                        array(
                        'name' => 'display-mode',
                        'value'=> $state,
                        'selectOptions'=>array(
                            'extended'=>gT('Extended'),
                            'compact'=>gT('Compact')
                            ),
                        'htmlOptions' => array(
                            'data-url'=>App()->createUrl('/admin/responses/sa/set_grid_display/'),
                            'data-value' => $state
                            )
                        )
                    );
                    ?>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-sm-12">
            <div class="content-right scrolling-wrapper"    >
                <input type='hidden' name='dateFormatDetails' value='<?php echo json_encode($dateformatdetails); ?>' />
                <input type='hidden' name='rtl' value='<?php echo getLanguageRTL($_SESSION['adminlang']) ? '1' : '0'; ?>' />
                <?php
                    // the massive actions dropup button
                    $massiveAction = App()->getController()->renderPartial('/admin/responses/massive_actions/_selector', array(), true, false);

                    // The first few colums are fixed.
                    // Specific columns at start
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

                        /*
                        array(
                            'header' => 'seed',
                            'name' => 'seed'
                        ));
                        */

                        $aColumns[] = array(
                            'header'=>'lastpage',
                            'name'=>'lastpage',
                            'type'=>'number',
                            'filter'=>TbHtml::textField(
                                'SurveyDynamic[lastpage]',
                                $model->lastpage)
                        );

                        $aColumns[] = array(
                            'header'=>gT("completed"),
                            'name'=>'completed_filter',
                            'value'=>'$data->completed',
                            'type'=>'raw',
                            'filter'=>TbHtml::dropDownList(
                                'SurveyDynamic[completed_filter]',
                                $model->completed_filter,
                                array(''=>gT('All'),'Y'=>gT('Yes'),'N'=>gT('No')))
                        );

                        //add token to top of list if survey is not private
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
                                'id'=>'firstname',
                                'type'=>'raw',
                                'value'=>'$data->firstNameForGrid',
                                'filter'=>TbHtml::textField(
                                    'SurveyDynamic[firstname_filter]',
                                    $model->firstname_filter)
                            );

                            $aColumns[] = array(
                                'header'=>gT("Last name"),
                                'name'=>'tokens.lastname',
                                'type'=>'raw',
                                'id'=>'lastname',
                                'value'=>'$data->lastNameForGrid',
                                'filter'=>TbHtml::textField(
                                    'SurveyDynamic[lastname_filter]',
                                    $model->lastname_filter)
                            );

                            $aColumns[] = array(
                                'header'=>gT("Email"),
                                'name'=>'tokens.email',
                                'id'=>'email',
                                'filter'=>TbHtml::textField(
                                    'SurveyDynamic[email_filter]',
                                    $model->email_filter)
                            );
                        }

                        $aColumns[] = array(
                            'header'=>'startlanguage',
                            'name'=>'startlanguage',
                        );

                   // The column model must be built dynamically, since the columns will differ from survey to survey, depending on the questions.
                   // All other columns are based on the questions.
                   // An array to control unicity of $code (EM code)
                    foreach($model->metaData->columns as $column)
                    {
                        if(!in_array($column->name, $aDefaultColumns))
                        {
                            $colName = viewHelper::getFieldCode($fieldmap[$column->name],array('LEMcompat'=>true)); // This must be unique ......
                            $base64jsonFieldMap = base64_encode(json_encode($fieldmap[$column->name]));
                            $colDetails = '';

                            if(isset($fieldmap[$column->name]['subquestion'])){
                                $colDetails .=  '<em>'.$fieldmap[$column->name]['subquestion'].'</em><br/>';
                            }

                            if(isset($fieldmap[$column->name]['subquestion1'])){
                                $colDetails .=  '<em>'.$fieldmap[$column->name]['subquestion1'].'</em><br/>';
                            }

                            if(isset($fieldmap[$column->name]['subquestion2'])){
                                $colDetails .=  '<em>'.$fieldmap[$column->name]['subquestion2'].'</em><br/>';
                            }


                            $colDetails .= ellipsize($fieldmap[$column->name]['question'], $model->ellipsize_header_value);

                            $aColumns[]=
                                array(
                                    'header' => '<span data-toggle="tooltip" data-placement="bottom" title="'.quoteText(strip_tags($fieldmap[$column->name]['question'])).'">'.$colName.' <br/> '.$colDetails.'</span>',
                                    'headerHtmlOptions'=>array('style'=>'min-width: 350px;'),
                                    'name' => $column->name,
                                    'type' => 'raw',
                                    'value' => '$data->getExtendedData("'.$column->name.'", "'.$language.'", "'.$base64jsonFieldMap.'")',
                                );
                        }
                    }

                    $this->widget('bootstrap.widgets.TbGridView', array(
                        'dataProvider'  => $model->search(),
                        'filter'        => $model,
                        'columns'       => $aColumns,
                        'itemsCssClass' =>'table-striped',
                        'id'            => 'responses-grid',
                        'ajaxUpdate'    => true,
                        'ajaxType'      => 'POST',
                        'afterAjaxUpdate'=>'LS.resp.bindScrollWrapper',
                        'template'      => "{items}\n<div id='ListPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                        'summaryText'   => gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                Yii::app()->params['pageSizeOptions'],
                                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))
                            ),
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
</div>



<!-- Edit Token Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="editTokenModal">
    <div class="modal-dialog" style="width: 1100px">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php eT('Edit survey participant');?></h4>
            </div>
            <div class="modal-body">
                <!-- the ajax loader -->
                <div id="ajaxContainerLoading2" class="ajaxLoading" >
                    <p><?php eT('Please wait, loading data...');?></p>
                    <div class="preloader loading">
                        <span class="slice"></span>
                        <span class="slice"></span>
                        <span class="slice"></span>
                        <span class="slice"></span>
                        <span class="slice"></span>
                        <span class="slice"></span>
                    </div>
                </div>
                <div id="modal-content">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Close");?></button>
                <button type="button" class="btn btn-primary" id="save-edittoken"><?php eT("Save");?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div style="display: none;">
<?php
Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
    'name' => "no",
    'id'   => "no",
    'value' => '',
));
?>
</div>
