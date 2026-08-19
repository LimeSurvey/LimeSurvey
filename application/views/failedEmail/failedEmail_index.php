<?php
/**
 * @var FailedEmail $failedEmailModel
 * @var int $pageSize
 * @var int $surveyId
 * @var array $permissions
 */

use actions\FailedEmailMassiveActions;

require_once Yii::getPathOfAlias('application.extensions.admin.grid.FloatingActionsWidget.actions.FailedEmailMassiveActions') . '.php';
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
                // Floating action bar (cross-page selection, fixed at bottom)
                $this->widget('ext.admin.grid.FloatingActionsWidget.FloatingActionsWidget', [
                    'pk'       => 'id',
                    'gridId'   => 'failedemail-grid',
                    'aActions' => FailedEmailMassiveActions::getActions($surveyId, $permissions),
                ]);

                $this->widget('application.extensions.admin.grid.CLSGridView', [
                    'dataProvider' => $failedEmailModel->search(),
                    'filter' => $failedEmailModel,
                    'id' => 'failedemail-grid',
                    'emptyText' => gT('No failed email notifications found'),
                    'showSelectionBar' => false,
                    'summaryText' => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                        gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            App()->params['pageSizeOptionsTokens'],
                            ['class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto']
                        )
                    ),
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