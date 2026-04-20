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
<!-- for filter columns with datepicker-->
<div style="display: none;">
    <?php
    $datePickerWidget = App()->getController()->widget(
        'ext.DateTimePickerWidget.DateTimePicker',
        [
            'name'          => "no",
            'id'            => "no_listResponses",
            'pluginOptions' => [
                'format'           => $dateformatdetails['jsdate'],
                'allowInputToggle' => true,
                'showClear'        => true,
                'locale'           => convertLStoDateTimePickerLocale(App()->session['adminlang'])
            ]
        ]
    );
    $datePickerWidgetConfig = str_replace('"', '', json_encode($datePickerWidget->getTempusConfigString()));
    $datePickerWidgetConfig = str_replace('\n', '', $datePickerWidgetConfig);

    ?>
</div>

<div class='side-body'>
    <!-- Display mode -->
    <form action="<?= App()->createUrl('/responses/browse/', ['surveyId' => $surveyid]) ?>" class="pjax text-end" method="POST"
          id="change-display-mode-form">
        <div class="mb-3">
            <label for="display-mode">
                <?php eT('Display mode:'); ?>
            </label>
            <?php $state = App()->user->getState('responsesGridSwitchDisplayState') == ""
                ? 'compact'
                : App()->user->getState('responsesGridSwitchDisplayState');
            $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                'name'          => 'displaymode',
                'checkedOption' => $state,
                'selectOptions' => [
                    'extended' => gT('Extended'),
                    'compact'  => gT('Compact')
                ],
                'htmlOptions'   => [
                    'classes' => 'selector__action-change-display-mode'
                ]
            ]); ?>
            <input type="hidden" name="<?= App()->request->csrfTokenName ?>" value="<?= CHtml::encode(App()->request->csrfToken) ?>"/>
            <input type="submit" class="d-none" name="submit" value="submit"/>
        </div>
    </form>

    <div class="content-right">
        <input type='hidden' id="dateFormatDetails" name='dateFormatDetails' value='<?php echo json_encode($dateformatdetails); ?>'/>
        <input type="hidden" id="locale" name="locale"
               value="<?= convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']) ?>"/>
        <input type='hidden' name='rtl' value='<?php echo getLanguageRTL($_SESSION['adminlang']) ? '1' : '0'; ?>'/>

        <?php if (!empty(App()->user->getState('sql_' . $surveyid))) : ?>
            <!-- Filter is on -->
            <?php eT("Showing filtered results"); ?>

            <a class="btn btn-outline-secondary"
               href="<?php echo Yii::app()->createUrl('responses/browse', ['surveyId' => $surveyid, 'filters' => 'reset']); ?>"
               role="button">
                <?php eT("View without the filter."); ?>
                <span aria-hidden="true">&times;</span>
            </a>

        <?php endif; ?>

        <?php
        // the massive actions dropup button
        $massiveAction = App()->getController()->renderPartial('/responses/massive_actions/_selector', [], true);


        // The first few columns are fixed.
        // Specific columns at start
        $aColumns = [
            [
                'id'                => 'id',
                'class'             => 'CCheckBoxColumn',
                'selectableRows'    => '100',
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ],
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
                    ['' => gT('All'), 'Y' => gT('Yes'), 'N' => gT('No')],
                    ['class' => 'form-select']
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
                    $encryptionSymbol = ' <span  data-bs-toggle="tooltip" title="' . $encryptionNotice . '" class="ri-key-2-fill text-success"></span>';
                }

                $colName = viewHelper::getFieldCode($fieldmap[$column->name], ['LEMcompat' => true]); // This must be unique ......
                $base64jsonFieldMap = base64_encode(json_encode($fieldmap[$column->name]));
                /* flat and ellipsize all part of question (sub question etc …, separate by br . mantis #14301 */
                $colDetails = viewHelper::getFieldText($fieldmap[$column->name],
                    ['abbreviated' => $model->ellipsize_header_value, 'separator' => ['<br>', '']]);
                /* Here we strip all tags, and separate with hr since we allow html (in popover), maybe use only viewHelper::purified ? But remind XSS. mantis #14301 */
                $colTitle = viewHelper::getFieldText($fieldmap[$column->name],
                    ['afterquestion' => "<hr>", 'separator' => ['', '<br>']]);

                if (!isset($filteredColumns) || in_array($column->name, $filteredColumns)) {
                    $encodedTitle = CHtml::encode($colTitle) == '' ? ' ' : CHtml::encode($colTitle);
                    $aColumns[] = [
                        'header'            => '<div data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="bottom" title="' . $colName . '" data-bs-content="' . $encodedTitle . '" data-bs-html="true" data-container="#responses-grid">' . $colName . ' <br/> ' . $colDetails . $encryptionSymbol . '</div>',
                        'headerHtmlOptions' => ['style' => 'min-width: 350px;'],
                        'name'              => $column->name,
                        'type'              => 'raw',
                        'filter'            => TbHtml::textField(
                            'SurveyDynamic[' . $column->name . ']',
                            $model->{$column->name}
                        ),
                        'value'             => '$data->getExtendedData("' . $column->name . '", "' . $language . '", "' . $base64jsonFieldMap . '")',
                    ];
                }
                $filterableColumns[$column->name] = $colName . ': ' . viewHelper::getFieldText($fieldmap[$column->name]);
            }
        }

        // create a modal to filter all columns
        $filterColumns = App()->getController()->renderPartial('/responses/modal_subviews/filterColumns',
            ['filterableColumns' => $filterableColumns, 'filteredColumns' => $filteredColumns, 'surveyId' => $surveyid], true);

        $aColumns[] = [
            'name'              => 'gridButtons',
            "type"              => 'raw',
            'filter'            => false,
            'header'            => gT('Action'),
            'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
            'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
            'htmlOptions'       => ['class' => 'text-center ls-sticky-column'],
        ];

        $this->widget(
            'application.extensions.admin.grid.CLSGridView',
            [
                'dataProvider'          => $model->search(),
                'filter'                => $model,
                'columns'               => $aColumns,
                'id'                    => 'responses-grid',
                'ajaxUpdate'            => 'responses-grid',
                'ajaxType'              => 'POST',
                'lsAfterAjaxUpdate'     => [
                    "afterAjaxResponsesReload();",
                    "onUpdateTokenGrid();",
                    '$("#responses-grid [data-bs-toggle=\'popover\']").popover();',
                    'bindListItemclick();',
                    'switchStatusOfListActions();'
                ],
                'massiveActionTemplate' => $massiveAction . $filterColumns,
                'summaryText'           => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                        gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                        )
                    ),
            ]
        );

        ?>

        <!-- To update rows per page via ajax setSession-->
        <?php

        $scriptVars = '
                    var postUrl = "' . Yii::app()->getController()->createUrl("responses/setSession") . '"; // For massive export
                    ';
        $script = '
                    var postUrl = "' . Yii::app()->getController()->createUrl("responses/setSession") . '"; // For massive export
                    $("#responses-grid [data-bs-toggle=\'popover\']").popover();
                    ';
        App()->getClientScript()->registerScript('listresponses', $scriptVars, LSYii_ClientScript::POS_BEGIN);
        App()->getClientScript()->registerScript('listresponses', $script, LSYii_ClientScript::POS_POSTSCRIPT);
        ?>
    </div>
</div>

<!-- Edit Token Modal -->
<?php // @todo Duplicate, original in application/views/admin/token/browse.php. Remove this? ?>
<div class="modal fade" tabindex="-1" role="dialog" id="editTokenModal">
    <div class="modal-dialog" style="width: 1100px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php eT('Edit survey participant'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php eT("Close"); ?></button>
                <button role="button" type="button" class="btn btn-primary" id="save-edittoken">
                    <?php eT("Save"); ?>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
