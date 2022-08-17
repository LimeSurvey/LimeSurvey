<?php
/**
 * @var FailedEmail $failedEmailModel
 * @var int $pageSize
 * @var string $massiveAction
 */
?>
<?= viewHelper::getViewTestTag('surveyFailedEmail') ?>
    <div class='side-body <?php echo getSideBodyClass(false); ?>'>
        <h3><?php eT("Failed e-mail notifications"); ?></h3>
        <p class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
            <span class="fa fa-info-circle"></span>
            <?php eT("Note that entries in this table will be automatically deleted after 30 days."); ?>
        </p>
        <!-- Grid -->
        <div class="row">
            <div class="content-right">
                <?php

                $this->widget('ext.LimeGridView.LimeGridView', [
                    'dataProvider'    => $failedEmailModel->search(),
                    'filter'          => $failedEmailModel,
                    'id'              => 'failedemail-grid',
                    'emptyText'       => gT('No failed e-mail notifications found'),
                    'template'        => "<div class='push-grid-pager'>{items}\n</div><div id='emailFailedEmailPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                    'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                App()->params['pageSizeOptionsTokens'],
                                ['class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto'])),
                    'htmlOptions'     => ['class' => 'table-responsive grid-view-ls'],
                    'columns'         => $failedEmailModel->getColumns(),
                    'ajaxUpdate'      => 'failedemail-grid',
                    'ajaxType'        => 'POST',
                    'afterAjaxUpdate' => 'js:function(id, data){ bindListItemclick(); LS.FailedEmail.bindButtons();}'
                ]);
                ?>
            </div>
        </div>
    </div>
    <div id="failedemail-action-modal" class="modal fade" role="dialog">
        <div id="failedemail-action-modal--dialog" class="modal-dialog" role="document">
            <div class="modal-content">
            </div>
        </div>
    </div>
<?php
