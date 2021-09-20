<?php
/**
 * @var AdminController $this
 * @var CActiveDataProvider $dataProvider
 * @var Surveymenu $model
 * @var SurveymenuEntries $entries_model
 */

$pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
$massiveAction = App()->getController()->renderPartial('/admin/surveymenu/massive_action/_selector', [], true, false);

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyMenus');

?>

<div class="col-lg-12">
    <div class="row">
        <!-- Tabs -->
        <ul class="nav nav-tabs" id="menueslist" role="tablist">
            <li class="active">
				<a href="#surveymenues" aria-controls="surveymenues" role="tab" data-toggle="tab">
                    <?php eT('Survey menus'); ?>
                </a>
            </li>
            <li>
				<a href="#surveymenuentries" aria-controls="surveymenuentries" role="tab" data-toggle="tab">
                    <?php eT('Survey menu entries'); ?>
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">

            <!-- Survey Menu -->
            <div id="surveymenues" class="tab-pane active">
                <div class="col-12 ls-space margin top-15">
                    <div class="col-12 ls-flex-item">
                        <?php
                        $this->widget(
                            'bootstrap.widgets.TbGridView',
                            [
                                'dataProvider'             => $model->search(),
                                'id'                       => 'surveymenu-grid',
                                'columns'                  => $model->getColumns(),
                                'filter'                   => $model,
                                'emptyText'                => gT('No customizable entries found.'),
                                'summaryText'              => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                                    gT('%s rows per page'),
                                    CHtml::dropDownList(
                                        'pageSize',
                                        $pageSize,
                                        Yii::app()->params['pageSizeOptions'],
                                        ['class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto']
                                    )
                                ),
                                'rowHtmlOptionsExpression' => '["data-surveymenu-id" => $data->id]',
                                'htmlOptions'              => ['class' => 'table-responsive grid-view-ls'],
                                'ajaxType'                 => 'POST',
                                'ajaxUpdate'               => 'surveymenu-grid',
                                'template'                 => "{items}\n<div id='tokenListPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                                'afterAjaxUpdate'          => 'surveyMenuFunctions',
                            ]
                        ); ?>
                    </div>
                </div>
            </div>

            <!-- Survey Menue Entries -->
            <div id="surveymenuentries" class="tab-pane">
                <?php App()->getController()->renderPartial('surveymenu_entries/index', ['model' => $entries_model]); ?>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="surveymenu_open_url_selected_entry" value="0"/>
<!-- modal! -->

<div class="modal fade" id="editcreatemenu" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        </div>
    </div>
</div>
<div class="modal fade" id="deletesurveymenumodal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php eT("Delete this survey menu?"); ?></h4>
            </div>
            <div class="modal-body">
                <?php eT("All menu entries of this menu will also be deleted."); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-dismiss="modal"><?php eT('Cancel'); ?></button>
                <button type="button" id="deletemodal-confirm" class="btn btn-danger"><?php eT('Delete'); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="restoremodalsurveymenu" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php eT("Really restore the default survey menus (survey menu entries)?"); ?></h4>
            </div>
            <div class="modal-body">
                <p>
                    <?php eT("All custom menus will be lost."); ?>
                </p>
                <p>
                    <?php eT("Please do a backup of the menu (menu entries) you want to keep."); ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php eT('Cancel'); ?>
                </button>
                <button
                        type="button"
                        id="reset-menus-confirm"
                        class="btn btn-danger"
                        data-urlmenu="<?=Yii::app()->getController()->createUrl('/admin/menus/sa/restore');?>"
                        data-urlmenuentry="<?= Yii::app()->getController()->createUrl('/admin/menuentries/sa/restore');?>"
                >
                    <?php eT('Restore default'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
	$('#menueslist a').on('shown.bs.tab', function () {
        var tabId = $(this).attr('href');
        $('.tab-dependent-button:not([data-tab="' + tabId + '"])').hide();
        $('.tab-dependent-button[data-tab="' + tabId + '"]').show();
    });
    $(document).on('ready pjax:scriptcomplete', function () {
        if (window.location.hash) {
            $('#menueslist').find('a[href=' + window.location.hash + ']').trigger('click');
        }
    });

    var surveyMenuFunctions = function () {
        getBindActionForSurveymenus('#editcreatemenu', 'surveymenu-grid', {
            loadSurveyEntryFormUrl: "<?php echo Yii::app()->urlManager->createUrl('/admin/menus/sa/getsurveymenuform') ?>",
            deleteEntryUrl: "<?php echo Yii::app()->getController()->createUrl('/admin/menus/sa/delete'); ?>"
        })
    }
    surveyMenuFunctions();

    var surveyMenuEntryFunctions = function () {
        getBindActionForSurveymenuEntries('#editcreatemenuentry', 'surveymenu-entries-grid', {
            loadSurveyEntryFormUrl: "<?php echo Yii::app()->urlManager->createUrl('/admin/menuentries/sa/getsurveymenuentryform') ?>",
            restoreEntriesUrl: "<?php echo Yii::app()->getController()->createUrl('/admin/menuentries/sa/restore'); ?>",
            reorderEntriesUrl: "<?php echo Yii::app()->getController()->createUrl('/admin/menuentries/sa/reorder'); ?>",
            deleteEntryUrl: "<?php echo Yii::app()->getController()->createUrl('/admin/menuentries/sa/delete'); ?>"
        })
    }
    surveyMenuEntryFunctions();
</script>

