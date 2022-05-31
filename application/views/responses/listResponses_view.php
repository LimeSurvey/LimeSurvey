<?php
/**
 * @var $this AdminController
 * @var $surveyid int
 * @var $dateformatdetails array
 * @var $model SurveyDynamic
 * @var $bHaveToken bool
 * @var $language string
 * @var $pageSize int
 * @var $fieldmap array
 * @var $filteredColumns array
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyResponsesBrowse');

?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="col-12">
        <h3><?php eT('Survey responses'); ?></h3>
        <!-- Display mode -->
        <div class="text-right in-title">
            <div class="pull-right">
                <div class="form text-right">
                    <form action="<?= App()->createUrl('/responses/browse/', ['surveyId' => $surveyid]) ?>" class="pjax" method="POST" id="change-display-mode-form">
                        <div class="form-group">
                            <label for="display-mode">
                                <?php
                                eT('Display mode:');
                                ?>
                            </label>
                            <?php
                            $state = App()->user->getState('responsesGridSwitchDisplayState') == "" ? 'compact' : App()->user->getState('responsesGridSwitchDisplayState');
                            $this->widget(
                                'yiiwheels.widgets.buttongroup.WhButtonGroup',
                                [
                                    'name'          => 'displaymode',
                                    'value'         => $state,
                                    'selectOptions' => [
                                        'extended' => gT('Extended'),
                                        'compact'  => gT('Compact')
                                    ],
                                    'htmlOptions'   => [
                                        'classes' => 'selector__action-change-display-mode'
                                    ]
                                ]
                            );
                            ?>
                            <input type="hidden" name="surveyid" value="<?= $surveyid ?>"/>
                            <input type="hidden" name="<?= Yii::app()->request->csrfTokenName ?>" value="<?= Yii::app()->request->csrfToken ?>"/>
                            <input type="submit" class="hidden" name="submit" value="submit"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <div class="ls-flex-row col-12">
            <div class="col-12 ls-flex-column">
                <div id='top-scroller' class="content-right scrolling-wrapper">
                    <div id='fake-content'>&nbsp;</div>
                </div>
                <div id='bottom-scroller' class="content-right scrolling-wrapper">
                    <input type='hidden' name='dateFormatDetails' value='<?php echo json_encode($dateformatdetails); ?>'/>
                    <input type='hidden' name='rtl' value='<?php echo getLanguageRTL($_SESSION['adminlang']) ? '1' : '0'; ?>'/>

                    <?php if (!empty(App()->user->getState('sql_' . $surveyid))) : ?>
                        <!-- Filter is on -->
                        <?php eT("Showing filtered results"); ?>

                        <a class="btn btn-default" href="<?php echo Yii::app()->createUrl('responses/browse', ['surveyId' => $surveyid, 'filters' => 'reset']); ?>" role="button">
                            <?php eT("View without the filter."); ?>
                            <span aria-hidden="true">&times;</span>
                        </a>

                    <?php endif; ?>

                    <?php
                    // the massive actions dropup button
                    $massiveAction = App()->getController()->renderPartial('/responses/massive_actions/_selector', [], true);


                    // The first few colums are fixed.
                    // Specific columns at start
                    $aColumns = [
                        [
                            'id'             => 'id',
                            'class'          => 'CCheckBoxColumn',
                            'selectableRows' => '100',
                        ],
                        [
                            'name' => 'gridButtons',
                            "type" => 'raw',
                            'filter' => false,
                            'header'      => gT('Action'),
                            'htmlOptions' => ['class' => 'icon-btn-row'],
                        ],
//                        [
//                            'header'      => gT('Action'),
//                            'class'       => 'bootstrap.widgets.TbButtonColumn',
//                            'template'    => '{edit}{detail}{quexmlpdf}{downloadfiles}{deletefiles}<span data-toggle="tooltip" title="' . gT("Delete this response") . '">{deleteresponse}</span>',
//                            'htmlOptions' => ['class' => 'icon-btn-row'],
//                            'buttons'     => $model->getGridButtons(),
//                        ],
                        [
                            'header' => 'id',
                            'name'   => 'id',
                        ],

                        [
                            'header' => 'seed',
                            'name'   => 'seed'
                        ]
                    ];

                    if (!isset($filteredColumns) || in_array('lastpage', $filteredColumns)) {
                        $aColumns[] = [
                            'header' => 'lastpage',
                            'name'   => 'lastpage',
                            'type'   => 'number',
                            'filter' => TbHtml::textField(
                                'SurveyDynamic[lastpage]',
                                $model->lastpage
                            )
                        ];
                    }
                    $filterableColumns['lastpage'] = 'lastpage';

                    if (!isset($filteredColumns) || in_array('completed', $filteredColumns)) {
                        $aColumns[] = [
                            'header' => gT("completed"),
                            'name'   => 'completed_filter',
                            'value'  => '$data->completed',
                            'type'   => 'raw',
                            'filter' => TbHtml::dropDownList(
                                'SurveyDynamic[completed_filter]',
                                $model->completed_filter,
                                ['' => gT('All'), 'Y' => gT('Yes'), 'N' => gT('No')]
                            )
                        ];
                    }
                    $filterableColumns['completed'] = gT("completed");

                    //add token to top of list if survey is not private
                    if ($bHaveToken) {
                        if (!isset($filteredColumns) || in_array('token', $filteredColumns)) {
                            $aColumns[] = [
                                'header' => 'token',
                                'type'   => 'raw',
                                'name'   => 'token',
                                'value'  => '$data->tokenForGrid',
                            ];
                        }
                        $filterableColumns['token'] = 'token';

                        if (!isset($filteredColumns) || in_array('firstname', $filteredColumns)) {
                            $aColumns[] = [
                                'header' => gT("First name"),
                                'name'   => 'tokens.firstname',
                                'id'     => 'firstname',
                                'value'  => '$data->firstNameForGrid',
                                'filter' => TbHtml::textField(
                                    'SurveyDynamic[firstname_filter]',
                                    $model->firstname_filter
                                )
                            ];
                        }
                        $filterableColumns['firstname'] = gT("First name");

                        if (!isset($filteredColumns) || in_array('lastname', $filteredColumns)) {
                            $aColumns[] = [
                                'header' => gT("Last name"),
                                'name'   => 'tokens.lastname',
                                'id'     => 'lastname',
                                'value'  => '$data->lastNameForGrid',
                                'filter' => TbHtml::textField(
                                    'SurveyDynamic[lastname_filter]',
                                    $model->lastname_filter
                                )
                            ];
                        }
                        $filterableColumns['lastname'] = gT("Last name");

                        if (!isset($filteredColumns) || in_array('email', $filteredColumns)) {
                            $aColumns[] = [
                                'header' => gT("Email"),
                                'name'   => 'tokens.email',
                                'id'     => 'email',
                                'filter' => TbHtml::textField(
                                    'SurveyDynamic[email_filter]',
                                    $model->email_filter
                                )
                            ];
                        }
                        $filterableColumns['email'] = gT("Email");
                    }

                    if (!isset($filteredColumns) || in_array('startlanguage', $filteredColumns)) {
                        $aColumns[] = [
                            'header' => 'startlanguage',
                            'name'   => 'startlanguage',
                        ];
                    }
                    $filterableColumns['startlanguage'] = 'startlanguage';
                    $encryptionNotice = gT("This field is encrypted and can only be searched by exact match. Please enter the exact value you are looking for.");

                    // The column model must be built dynamically, since the columns will differ from survey to survey, depending on the questions.
                    // All other columns are based on the questions.
                    // An array to control unicity of $code (EM code)
                    foreach ($model->metaData->columns as $column) {
                        if (!in_array($column->name, $model->defaultColumns)) {
                            /* Add encryption symbole to question title for table header (if question is encrypted) */
                            $encryptionSymbol = '';
                            if (isset($fieldmap[$column->name]['encrypted']) && $fieldmap[$column->name]['encrypted'] === 'Y') {
                                $encryptionSymbol = ' <span  data-toggle="tooltip" title="' . $encryptionNotice . '" class="fa fa-key text-success"></span>';
                            }

                            $colName = viewHelper::getFieldCode($fieldmap[$column->name], ['LEMcompat' => true]); // This must be unique ......
                            $base64jsonFieldMap = base64_encode(json_encode($fieldmap[$column->name]));
                            /* flat and ellipsize all part of question (sub question etc …, separate by br . mantis #14301 */
                            $colDetails = viewHelper::getFieldText($fieldmap[$column->name], ['abbreviated' => $model->ellipsize_header_value, 'separator' => ['<br>', '']]);
                            /* Here we strip all tags, and separate with hr since we allow html (in popover), maybe use only viewHelper::purified ? But remind XSS. mantis #14301 */
                            $colTitle = viewHelper::getFieldText($fieldmap[$column->name], ['afterquestion' => "<hr>", 'separator' => ['', '<br>']]);

                            if (!isset($filteredColumns) || in_array($column->name, $filteredColumns)) {
                                $aColumns[] = [
                                    'header'            => '<div data-toggle="popover" data-trigger="hover focus" data-placement="bottom" title="' . $colName . '" data-content="' . CHtml::encode($colTitle) . '" data-html="1" data-container="#responses-grid">' . $colName . ' <br/> ' . $colDetails . $encryptionSymbol . '</div>',
                                    'headerHtmlOptions' => ['style' => 'min-width: 350px;'],
                                    'name'              => $column->name,
                                    'type'              => 'raw',
                                    'value'             => '$data->getExtendedData("' . $column->name . '", "' . $language . '", "' . $base64jsonFieldMap . '")',
                                ];
                            }
                            $filterableColumns[$column->name] = $colName . ': ' . viewHelper::getFieldText($fieldmap[$column->name]);
                        }
                    }

                    // create a modal to filter all columns
                    $filterColumns = App()->getController()->renderPartial('/responses/modal_subviews/filterColumns', ['filterableColumns' => $filterableColumns, 'filteredColumns' => $filteredColumns, 'surveyId' => $surveyid], true);

                    $this->widget(
                        'ext.LimeGridView.LimeGridView',
                        [
                            'dataProvider'    => $model->search(),
                            'filter'          => $model,
                            'columns'         => $aColumns,
                            //'htmlOptions'     => ['class' => 'table-responsive'],
                            'id'              => 'responses-grid',
                            'ajaxUpdate'      => 'responses-grid',
                            'ajaxType'        => 'POST',
                            'afterAjaxUpdate' => 'js:function(id, data){ LS.resp.bindScrollWrapper(); onUpdateTokenGrid();$(".grid-view [data-toggle=\'popover\']").popover(); }',
                            'template'        => "{items}\n<div id='reponsesListPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction$filterColumns</div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                            'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                                gT('%s rows per page'),
                                CHtml::dropDownList(
                                    'pageSize',
                                    $pageSize,
                                    Yii::app()->params['pageSizeOptions'],
                                    ['class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto']
                                )
                            ),
                        ]
                    );

                    ?>
                </div>

                <!-- To update rows per page via ajax setSession-->
                <?php

                $scriptVars = '
                    var postUrl = "' . Yii::app()->getController()->createUrl("responses/setSession") . '"; // For massive export
                    ';
                $script = '
                    var postUrl = "' . Yii::app()->getController()->createUrl("responses/setSession") . '"; // For massive export
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
                    <h4 class="modal-title"><?php eT('Edit survey participant'); ?></h4>
                </div>
                <div class="modal-body">
                    <!-- the ajax loader -->
                    <div id="ajaxContainerLoading2" class="ajaxLoading">
                        <p><?php eT('Please wait, loading data...'); ?></p>
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
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Close"); ?></button>
                    <button type="button" class="btn btn-primary" id="save-edittoken"><?php eT("Save"); ?></button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <div style="display: none;">
        <?php
        Yii::app()->getController()->widget(
            'yiiwheels.widgets.datetimepicker.WhDateTimePicker',
            [
                'name'  => "no",
                'id'    => "no",
                'value' => '',
            ]
        );
        ?>
    </div>
</div>
