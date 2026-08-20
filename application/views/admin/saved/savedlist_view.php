<?php
/**
 * @var string       $sSurveyName
 * @var int          $iSurveyId
 * @var SavedControl $model
 * @var int          $savedResponsesPageSize
 */
?>

<div class='side-body'>
    <h3>
        <?php eT('Saved responses'); ?>
        <small><?php echo flattenText($sSurveyName) . ' ' . sprintf(gT('ID: %s'), $iSurveyId); ?></small>
    </h3>

    <div class="row">
        <div class="col-12 content-right">
            <?php
            $this->widget('application.extensions.admin.grid.CLSGridView', [
                    'id'           => 'saved-grid',
                    'ajaxUpdate'   => 'saved-grid',
                    'dataProvider' => $model->search(),
                    'columns'      => $model->columns,
                    'filter'       => $model,
                    'ajaxType'     => 'POST',
                    'htmlOptions'  => ['class' => 'table-responsive grid-view-ls'],
                    'emptyText'    => gT('No customizable entries found.'),
                    'lsPageSizeCurrentValue' => $savedResponsesPageSize,
                ]);
            ?>
        </div>
    </div>
</div>
