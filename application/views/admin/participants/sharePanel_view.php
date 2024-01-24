<?php
/**
 * @var AdminController $this
 * @var ParticipantShare $model
 * @var string $massiveAction
 * @var int $pageSizeShareParticipantView
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('participantsSharePanel');

?>
<div id="pjax-content">
    <div class="col-12 list-surveys">
        <div class="row">
            <?php
                $this->widget('application.extensions.admin.grid.CLSGridView', [
                    'id' => 'share_central_participants',
                    'dataProvider' => $model->search(),
                    'columns' => $model->columns,
                    'filter' => $model,
                    'rowHtmlOptionsExpression' => '["data-participant_id" => $data->participant_id, "data-share_uid" => $data->share_uid]',
                    'massiveActionTemplate' => $massiveAction,
                    'emptyText'                => gT('No shared participants found.'),
                    'ajaxType' => 'POST',
                    'afterAjaxUpdate' => 'LS.CPDB.bindButtons',
                    'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' '
                        . sprintf(
                            gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSizeShareParticipantView',
                                $pageSizeShareParticipantView,
                                App()->params['pageSizeOptions'],
                                array('class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto')
                            )
                        ),
                ]);
            ?>
        </div>
    </div>
    <span id="locator" data-location="sharepanel">&nbsp;</span>
</div>
