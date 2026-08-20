
<?php
$this->widget(
    'application.extensions.admin.grid.CLSGridView',
    [
        'id'           => 'saved-grid',
        'ajaxUpdate'   => 'saved-grid',
        'dataProvider' => $model->search(),
        'columns'      => $model->columns,
        'filter'       => $model,
        'ajaxType'     => 'POST',
        'htmlOptions'  => ['class' => 'table-responsive grid-view-ls'],
        'emptyText'    => gT('No customizable entries found.'),
        'lsPageSizeCurrentValue' => $savedResponsesPageSize,
    ]
);
