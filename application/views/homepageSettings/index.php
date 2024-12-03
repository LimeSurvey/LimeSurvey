<?php
/* @var AdminController $this */

/* @var CActiveDataProvider $dataProvider */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('homepageSettings');

App()->getClientScript()->registerScript(
    'HomepageSettingsBSSwitcher',
    LSYii_ClientScript::POS_POSTSCRIPT
);

?>

<div class="row">
    <div class="col-12 list-surveys">
        <!-- Tabs -->
        <ul class="nav nav-tabs" id="boxeslist">
            <li class="nav-item">
                <a class="nav-link active" href='#boxes' data-bs-toggle="tab">
                    <?php eT('Buttons') ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href='#boxsettings' data-bs-toggle="tab">
                    <?php eT('Settings'); ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <!-- Boxes -->
            <div id="boxes" class="tab-pane fade show active">
                <?php $this->widget(
                    'application.extensions.admin.grid.CLSGridView',
                    [
                        'id' => 'boxes-grid',
                        'dataProvider' => $dataProviderBox->search(),
                        'pager' => [
                            'class' => 'application.extensions.admin.grid.CLSYiiPager',
                        ],
                        'summaryText' => gT('Displaying {start}-{end} of {count} result(s).') . ' '
                            . sprintf(
                                gT('%s rows per page'),
                                CHtml::dropDownList(
                                    'boxes-pageSize',
                                    Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']),
                                    Yii::app()->params['pageSizeOptions'],
                                    array('class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto')
                                )
                            ),
                        'columns' => [
                            [
                                'header' => gT('Position'),
                                'name' => 'position',
                                'value' => '$data->position',
                                'htmlOptions' => ['class' => ''],
                            ],
                            [
                                'header' => gT('Title'),
                                'name' => 'title',
                                'value' => '$data->title',
                                'htmlOptions' => ['class' => ''],
                            ],
                            [
                                'header' => gT('Icon'),
                                'name' => 'icon',
                                'value' => '$data->getSpanIcon()',
                                'type' => 'raw',
                                'htmlOptions' => ['class' => ''],
                            ],
                            [
                                'header' => gT('Description'),
                                'name' => 'desc',
                                'value' => '$data->desc',
                                'htmlOptions' => ['class' => ''],
                            ],
                            [
                                'header' => gT('URL'),
                                'name' => 'url',
                                'value' => '$data->url',
                                'htmlOptions' => ['class' => ''],
                            ],
                            [
                                'header' => gT('User group'),
                                'name' => 'url',
                                'value' => '$data->usergroupname',
                                'htmlOptions' => ['class' => ''],
                            ],
                            [
                                'header' => gT('Action'),
                                'name' => 'actions',
                                'value' => '$data->buttons',
                                'type' => 'raw',
                                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                                'htmlOptions'       => ['class' => 'text-center button-column ls-sticky-column'],
                            ],
                        ],
                    ]
                ); ?>
            </div>
            <!-- Box Settings -->
            <div id="boxsettings" class="tab-pane fade">

                <div class="row">
<!--                    <label class="col-md-2 col-form-label">--><?php //eT("Display logo:"); ?><!-- </label>-->
<!--                    <div class="col-md-2">-->
<!--                        --><?php //$this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
//                            'name'          => 'show_logo',
//                            'checkedOption' => $bShowLogo,
//                            'selectOptions' => [
//                                '1' => gT('On'),
//                                '0' => gT('Off'),
//                            ]
//                        ]); ?>
<!--                        <input type="hidden" id="show_logo-url" data-url="--><?php //echo App()->createUrl('homepageSettings/toggleShowLogoStatus'); ?><!--"/>-->
<!--                    </div>-->

                    <label class="col-md-4 col-form-label"><?php eT("Show last visited survey and question:"); ?> </label>
                    <div class="col-md-2">
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'show_last_survey_and_question',
                            'checkedOption' => $bShowLastSurveyAndQuestion,
                            'selectOptions' => [
                                '1' => gT('On'),
                                '0' => gT('Off'),
                            ],
                        ]); ?>
                        <input type="hidden" id="show_last_survey_and_question-url" data-url="<?php echo App()->createUrl('homepageSettings/toggleShowLastSurveyAndQuestion'); ?>"/>
                    </div>

                    <br/><br/>
                </div>

                <div class="row">
<!--                    <label class="col-md-2 col-form-label">--><?php //eT("Show survey list:"); ?><!-- </label>-->
<!--                    <div class="col-md-2">-->
<!--                        --><?php //$this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
//                            'name'          => 'show_survey_list',
//                            'checkedOption' => $bShowSurveyList,
//                            'selectOptions' => [
//                                '1' => gT('On'),
//                                '0' => gT('Off'),
//                            ],
//                        ]); ?>
<!--                        <input type="hidden" id="show_survey_list-url" data-url="--><?php //echo App()->createUrl('homepageSettings/toggleShowSurveyList'); ?><!--"/>-->
<!--                    </div>-->
<!---->
                    <label class="col-md-4 col-form-label"><?php eT("Show search box on survey list:"); ?> </label>
                    <div class="col-md-2">
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'show_survey_list_search',
                            'checkedOption' => $bShowSurveyListSearch,
                            'selectOptions' => [
                                '1' => gT('On'),
                                '0' => gT('Off'),
                            ],
                        ]); ?>
                        <input type="hidden" id="show_survey_list_search-url" data-url="<?php echo App()->createUrl('homepageSettings/toggleShowSurveyListSearch'); ?>"/>
                    </div>
<!---->
<!--                    <br/><br/>-->
                </div>

<!--                <div class="row">-->
<!--                    <label class="col-md-2 col-form-label">--><?php //eT("Wrap container around boxes"); ?><!-- </label>-->
<!--                    <div class="col-md-2">-->
<!--                        --><?php //$this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
//                            'name'          => 'boxes_in_container',
//                            'checkedOption' => $bBoxesInContainer,
//                            'selectOptions' => [
//                                '1' => gT('On'),
//                                '0' => gT('Off'),
//                            ],
//                        ]); ?>
<!--                        <input type="hidden" id="boxes_in_container-url" data-url="--><?php //echo App()->createUrl('homepageSettings/changeBoxesInContainer'); ?><!--"/>-->
<!--                    </div>-->
<!--                    <br/><br/>-->
<!--                    <br/><br/>-->
<!--                </div>-->

<!--                <div class="row">-->
<!--                    <label class="col-md-2 col-form-label">--><?php //eT("Boxes by row:"); ?><!--</label>-->
<!--                    <div class="col-md-1">-->
<!--                        <input class="form-control" type="number" id="iBoxesByRow" value="--><?php //echo $iBoxesByRow; ?><!--" max="6" min="0" name="boxes_by_row"/>-->
<!--                    </div>-->
<!--                    <label class="col-md-2 offset-md-1 col-form-label">--><?php //eT("Box orientation:"); ?><!--</label>-->
<!--                    <div class="col-md-1">-->
<!--                        <select class="form-select" id="iBoxesOffset" name="boxes_offset">-->
<!--                            <option value="1" --><?php //if ($iBoxesOffset == '1') {
//                                echo "selected";
//                            } ?><!-- >--><?php //eT('Left to right') ?><!--</option>-->
<!--                            <option value="2" --><?php //if ($iBoxesOffset == '2') {
//                                echo "selected";
//                            } ?><!-- >--><?php //eT('Right to left') ?><!--</option>-->
<!--                            <option value="3" --><?php //if ($iBoxesOffset == '3') {
//                                echo "selected";
//                            } ?><!-- >--><?php //eT('Centered') ?><!--</option>-->
<!--                        </select>-->
<!--                    </div>-->
<!--                    <div class="col-md-3">-->
<!--                        <input type="hidden" id="boxesupdatemessage" data-ajaxsuccessmessage="--><?php //eT('Box settings updated!'); ?><!--"/>-->
<!--                        <input type="hidden" id="boxeserrormessage" data-ajaxerrormessage="--><?php //eT('Error while updating box settings!'); ?><!--"/>-->
<!--                    </div>-->
<!--                    <br/><br/><br/><br/>-->
<!--                </div>-->
            </div>
        </div>
    </div>
</div>

<script>
    $('#boxeslist a').click(function (e) {
        window.location.hash = $(this).attr('href');
        // Hide the save button for boxes tab
        let tabName = $(this).tab().attr('href');
        if (tabName === '#boxes') {
            $('#save_boxes_setting').hide();
        } else {
            $('#save_boxes_setting').show();
        }
    });
    $(document).on('ready pjax:scriptcomplete', function () {
        // Default behaviour: hide the save button
        $('#save_boxes_setting').hide();
        if (window.location.hash) {
            $('#boxeslist').find('a[href=' + window.location.hash + ']').trigger('click');
        }
    });
</script>
<script type="text/javascript">
    jQuery(function ($) {
        // To update rows per page via ajax
        $(document).on("change", '#boxes-pageSize', function () {
            $.fn.yiiGridView.update('boxes-grid', {data: {pageSize: $(this).val()}});
        });
    });
</script>
