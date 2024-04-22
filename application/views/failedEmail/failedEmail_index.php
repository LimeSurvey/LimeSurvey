<?php
/**
 * @var FailedEmail $failedEmailModel
 * @var int $pageSize
 * @var string $massiveAction
 */
?>
<?= viewHelper::getViewTestTag('surveyFailedEmail') ?>
    <div class='side-body <?php echo getSideBodyClass(false); ?>'>
        <h1 class="h1 pagetitle"> 
            <?php eT('Failed email notifications'); ?> : 
            <?php echo viewHelper::flatEllipsizeText($oSurvey->currentLanguageSettings->surveyls_title, TRUE, 60, '…') . " (" . gT("ID") . " " . $oSurvey->sid . ")"; ?>
        </h1>
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
