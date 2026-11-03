<?php

/**
 * @var $model SurveymenuEntries
 */
$pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
?>
<div class="ls-flex-column">
    <h2 class="col-12 h3 pagetitle" ><?php eT('Menu entries') ?></h2>
    <div class="ls-flex-row">
        <div class="col-12 ls-flex-item">
            <?php
            $this->widget(
                'application.extensions.admin.grid.CLSGridView',
                [
                    'dataProvider' => $model->search(),
                    'id'           => 'surveymenu-entries-shortlist-grid',
                    'lsCaption'      => gT('Menu entries'),
                    'columns'      => $model->getShortListColumns(),
                    'emptyText'    => gT('No customizable entries found.'),
                    'lsPageSizeCurrentValue' => $pageSize,
                    'ajaxUpdate' => 'surveymenu-entries-shortlist-grid'
                ]
            );
            ?>
        </div>
    </div>
</div>


