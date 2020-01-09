<?php
/**
 * @var $this AdminController
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyResponsesBrowse');

?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="container-fluid col-12">
        <h3><?php eT('Survey responses'); ?></h3>
        <!-- Display mode -->
        <div class="text-right in-title">
            <div class="pull-right">
                <div class="form text-right">
                    <form action="<?=App()->createUrl('/admin/responses/sa/browse/', ['surveyid' => $surveyid])?>" class="pjax" method="POST" id="change-display-mode-form">
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
                                'name' => 'displaymode',
                                'value'=> $state,
                                'selectOptions'=>array(
                                    'extended'=>gT('Extended'),
                                    'compact'=>gT('Compact')
                                    ),
                                'htmlOptions' => array(
                                    'classes' => 'selector__action-change-display-mode'
                                    )
                                )
                            );
                            ?>
                            <input type="hidden" name="surveyid" value="<?=$surveyid?>" />
                            <input type="hidden" name="<?=Yii::app()->request->csrfTokenName?>" value="<?=Yii::app()->request->csrfToken?>" />
                            <input type="submit" class="hidden" name="submit" value="submit" />
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <div class="ls-flex-row col-12">
            <div class="col-12 ls-flex-column">
                <div id='top-scroller' class="content-right scrolling-wrapper"    >
                <div id='fake-content'>&nbsp;</div>
                </div>
                <div id='bottom-scroller' class="content-right scrolling-wrapper"    >
                    <input type='hidden' name='dateFormatDetails' value='<?php echo json_encode($dateformatdetails); ?>' />
                    <input type='hidden' name='rtl' value='<?php echo getLanguageRTL($_SESSION['adminlang']) ? '1' : '0'; ?>' />

                    <?php if (Yii::app()->user->getState('sql_'.$surveyid) != null ):?>
                        <!-- Filter is on -->
                        <?php eT("Showing filtered results"); ?>

                        <a class="btn btn-default" href="<?php echo Yii::app()->createUrl('admin/responses', array("sa"=>'browse','surveyid'=>$surveyid, 'filters'=>'reset')); ?>" role="button">
                            <?php eT("View without the filter."); ?>
                            <span aria-hidden="true">&times;</span>
                        </a>

                    <?php endif;?>

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
                                'class'=>'bootstrap.widgets.TbButtonColumn',
                                'template'=>'{detail}{quexmlpdf}{edit}{downloadfiles}{deletefiles}{deleteresponse}',
                                //~ 'htmlOptions' => array('class' => 'text-left response-buttons'),
                                'buttons'=> $model->getGridButtons('responses-grid'),
                            ),
                            array(
                                'header' => 'id',
                                'name' => 'id',
                            ),

                            array(
                                'header' => 'seed',
                                'name' => 'seed'
                            )
                        );

                        if (!isset($filteredColumns) || in_array('lastpage', $filteredColumns)) {
                            $aColumns[] = array(
                                'header' => 'lastpage',
                                'name' => 'lastpage',
                                'type' => 'number',
                                'filter' => TbHtml::textField(
                                    'SurveyDynamic[lastpage]',
                                    $model->lastpage)
                            );
                        }
                        $filterableColumns['lastpage'] = 'lastpage';

                        if (!isset($filteredColumns) || in_array('completed', $filteredColumns)) {
                            $aColumns[] = array(
                                'header' => gT("completed"),
                                'name' => 'completed_filter',
                                'value' => '$data->completed',
                                'type' => 'raw',
                                'filter' => TbHtml::dropDownList(
                                    'SurveyDynamic[completed_filter]',
                                    $model->completed_filter,
                                    array('' => gT('All'), 'Y' => gT('Yes'), 'N' => gT('No')))
                            );
                        }
                        $filterableColumns['completed'] = gT("completed");

                        //add token to top of list if survey is not private
                        if ($bHaveToken) {
                            if (!isset($filteredColumns) || in_array('token', $filteredColumns)) {
                                $aColumns[] = array(
                                    'header' => 'token',
                                    'name' => 'token',
                                    'type' => 'raw',
                                    'value' => '$data->tokenForGrid',

                                );
                            }
                            $filterableColumns['token'] = 'token';

                            if (!isset($filteredColumns) || in_array('firstname', $filteredColumns)) {
                                $aColumns[] = array(
                                    'header' => gT("First name"),
                                    'name' => 'tokens.firstname',
                                    'id' => 'firstname',
                                    'type' => 'raw',
                                    'value' => '$data->firstNameForGrid',
                                    'filter' => TbHtml::textField(
                                        'SurveyDynamic[firstname_filter]',
                                        $model->firstname_filter)
                                );
                            }
                            $filterableColumns['firstname'] = gT("First name");

                            if (!isset($filteredColumns) || in_array('lastname', $filteredColumns)) {
                                $aColumns[] = array(
                                    'header' => gT("Last name"),
                                    'name' => 'tokens.lastname',
                                    'type' => 'raw',
                                    'id' => 'lastname',
                                    'value' => '$data->lastNameForGrid',
                                    'filter' => TbHtml::textField(
                                        'SurveyDynamic[lastname_filter]',
                                        $model->lastname_filter)
                                );
                            }
                            $filterableColumns['lastname'] = gT("Last name");

                            if (!isset($filteredColumns) || in_array('email', $filteredColumns)) {
                                $aColumns[] = array(
                                    'header' => gT("Email"),
                                    'name' => 'tokens.email',
                                    'id' => 'email',
                                    'filter' => TbHtml::textField(
                                        'SurveyDynamic[email_filter]',
                                        $model->email_filter)
                                );
                            }
                            $filterableColumns['email'] = gT("Email");
                        }

                        if (!isset($filteredColumns) || in_array('startlanguage', $filteredColumns)) {
                            $aColumns[] = array(
                                'header' => 'startlanguage',
                                'name' => 'startlanguage',
                            );
                        }
                        $filterableColumns['startlanguage'] = 'startlanguage';

                    // The column model must be built dynamically, since the columns will differ from survey to survey, depending on the questions.
                    // All other columns are based on the questions.
                    // An array to control unicity of $code (EM code)
                        foreach($model->metaData->columns as $column)
                        {
                            if(!in_array($column->name, $aDefaultColumns))
                            {
                                $colName = viewHelper::getFieldCode($fieldmap[$column->name],array('LEMcompat'=>true)); // This must be unique ......
                                $base64jsonFieldMap = base64_encode(json_encode($fieldmap[$column->name]));
                                /* flat and ellipsize all part of question (sub question etc …, separate by br . mantis #14301 */
                                $colDetails = viewHelper::getFieldText($fieldmap[$column->name],array('abbreviated'=>$model->ellipsize_header_value,'separator'=>array('<br>','')));
                                /* Here we strip all tags, and separate with hr since we allow html (in popover), maybe use only viewHelper::purified ? But remind XSS. mantis #14301 */
                                $colTitle = viewHelper::getFieldText($fieldmap[$column->name],array('afterquestion'=>"<hr>",'separator'=>array('','<br>')));

                                if (!isset($filteredColumns) || in_array($column->name, $filteredColumns)) {
                                    $aColumns[] = array(
                                        'header' => '<div data-toggle="popover" data-trigger="hover focus" data-placement="bottom" title="' . $colName . '" data-content="' . CHtml::encode($colTitle) . '" data-html="1" data-container="#responses-grid">' . $colName . ' <br/> ' . $colDetails . '</div>',
                                        'headerHtmlOptions' => array('style' => 'min-width: 350px;'),
                                        'name' => $column->name,
                                        'type' => 'raw',
                                        'value' => '$data->getExtendedData("' . $column->name . '", "' . $language . '", "' . $base64jsonFieldMap . '")',
                                    );
                                }
                                $filterableColumns[$column->name] = $colName . ': ' . viewHelper::getFieldText($fieldmap[$column->name]);
                            }
                        }

                        // create a modal to filter all columns
                        $filterColumns = App()->getController()->renderPartial('/admin/responses/modal_subviews/filterColumns', array('filterableColumns' => $filterableColumns, 'filteredColumns' => $filteredColumns), true, false);

                        $this->widget('ext.LimeGridView.LimeGridView', array(
                            'dataProvider'  => $model->search(),
                            'filter'        => $model,
                            'columns'       => $aColumns,
                            'itemsCssClass' =>'table-striped',
                            'id'            => 'responses-grid',
                            'ajaxUpdate'    => 'responses-grid',
                            'ajaxType'      => 'POST',
                            'afterAjaxUpdate'=>'js:function(id, data){ LS.resp.bindScrollWrapper(); onUpdateTokenGrid();$(".grid-view [data-toggle=\'popover\']").popover(); }',
                            'template'      => "<div class='push-grid-pager'>{items}\n</div><div id='ListPager'><div class=\"col-sm-12\" id=\"massive-action-container\">$massiveAction$filterColumns</div><div class=\"col-sm-12 pager-container ls-ba \">{pager}</div><div class=\"col-sm-12 summary-container\">{summary}</div></div>",
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

                <!-- To update rows per page via ajax setSession-->
                <?php

                $scriptVars = '
                    var postUrl = "'.Yii::app()->getController()->createUrl("admin/responses/", array("sa" => "setSession")).'"; // For massive export
                    ';
                $script = '
                    var postUrl = "'.Yii::app()->getController()->createUrl("admin/responses/", array("sa" => "setSession")).'"; // For massive export
                    jQuery(document).on("change", "#pageSize", function(){
                        $.fn.yiiGridView.update("responses-grid",{ data:{ pageSize: $(this).val() }});
                    });
                    $(".grid-view [data-toggle=\'popover\']").popover();
                    ';
                App()->getClientScript()->registerScript('listresponses', $scriptVars, LSYii_ClientScript::POS_BEGIN);
                App()->getClientScript()->registerScript('listresponses', $script, LSYii_ClientScript::POS_POSTSCRIPT);
                ?>
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

</div>
