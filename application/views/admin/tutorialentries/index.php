<?php
$pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('tutorialentries');

?>
<div class="ls-flex-column ls-space padding left-35 right-35">
    <div class="col-12 h1 pagetitle">
        <?php eT('Tutorial entries')?>
    </div>
    <div class="col-12">
        <a role="button" class="btn btn-primary float-end col-xs-6 col-sm-3 col-md-2" id="createnewtutorialentry">
            &nbsp;
            <?php eT('New') ?>
        </a>
        <?php if (Permission::model()->hasGlobalPermission('superadmin', 'read')): ?>
            <a class="btn btn-danger float-end ls-space margin right-10 col-xs-6 col-sm-3 col-md-2"
               href="#restoremodal" data-bs-toggle="modal">
                <i class="ri-refresh-line"></i>&nbsp;
                <?php eT('Reset'); ?>
            </a>
        <?php endif; ?>
    </div>
    <div class="col-12 ls-space margin top-15">
        <div class="col-12 ls-flex-item">
            <?php $this->widget('application.extensions.admin.grid.CLSGridView', [
                'dataProvider'             => $model->search(),
                // Number of row per page selection
                'id'                       => 'tutorial-grid',
                'columns'                  => $model->getColumns(),
                'filter'                   => $model,
                'emptyText'                => gT('No customizable entries found.'),
                'summaryText'              => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                        )
                    ),
                'rowHtmlOptionsExpression' => '["data-tutorialentry-id" => $data->teid]',
                'htmlOptions'              => ['class' => 'table-responsive grid-view-ls'],
                'ajaxType'                 => 'POST',
                'ajaxUpdate'               => 'tutorial-grid',
                'lsAfterAjaxUpdate'        => ['bindAction()'],
            ]);
            ?>
        </div>
    </div>
</div>
