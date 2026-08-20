<?php
/**
 * @var FailedEmail $failedEmailModel
 * @var int $pageSize
 * @var string $massiveAction
 */
?>
<?= viewHelper::getViewTestTag('surveyFailedEmail') ?>
    <div class='side-body'>
        <h3><?php eT("Failed email notifications"); ?></h3>
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => gT("Please note that failed email notifications will be automatically deleted after 30 days."),
            'type' => 'info',
        ]);
        ?>
        <!-- Grid -->
        <div class="row">
            <div class="content-right">
                <?php
                $this->widget('application.extensions.admin.grid.CLSGridView', [
                    'dataProvider' => $failedEmailModel->search(),
                    'filter' => $failedEmailModel,
                    'id' => 'failedemail-grid',
                    'emptyText' => gT('No failed email notifications found'),
                    'massiveActionTemplate' => $massiveAction,
                    'lsPageSizeCurrentValue'  => $pageSize,
                    'lsPageSizeOptions'       => App()->params['pageSizeOptionsTokens'],
                    'htmlOptions' => ['class' => 'table-responsive grid-view-ls'],
                    'columns' => $failedEmailModel->getColumns(),
                    'ajaxUpdate' => 'failedemail-grid',
                    'ajaxType' => 'POST',
                    'lsAfterAjaxUpdate' => ['bindListItemclick();', 'LS.FailedEmail.bindButtons();']
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
