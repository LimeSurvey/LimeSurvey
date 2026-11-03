<?php

/**
 * @var $model Surveymenu
 */
$pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
?>

<div class="ls-flex-column">
    <h2 class="col-12 h3 pagetitle" ><?php eT('Survey menu') ?></h2>
    <div class="ls-flex-row">
        <div class="col-12 ls-flex-item">
            <?php
            $this->widget(
                'application.extensions.admin.grid.CLSGridView',
                [
                    'dataProvider'  => $model->search(),
                    'id'            => 'surveymenu-shortlist-grid',
                    'columns'       => $model->getShortListColumns(),
                    'lsCaption'       => gT('Survey menu'),
                    'emptyText'     => gT('No customizable entries found.'),
                    'lsPageSizeCurrentValue' => $pageSize,
                   'ajaxUpdate' => 'surveymenu-shortlist-grid'
                ]
            );
            ?>
        </div>
    </div>
</div>
