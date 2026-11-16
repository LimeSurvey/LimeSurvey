<?php
/**
 * This file render the list of user groups
 * It use the Label Sets model search method to build the data provider.
 *
 * @var UserGroup $model the UserGroup model
 * @var int $pageSize
 */

?>
<div class="col-12">

    <h2 class="h4"><?php
    if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
        eT('My user groups');
    }
    ?>
    </h2>

    <div class="row">
        <div class="col-12">
            <?php
            $this->widget(
                'application.extensions.admin.grid.CLSGridView',
                [
                    'id' => 'usergroups-grid-mine',
                    'lsCaption' => gT('My user groups'),
                    'dataProvider' => $model->searchMine(true),
                    'columns' => $model->getManagementButtons(),
                    'emptyText' => gT('No user groups found.'),
                    'ajaxUpdate' => 'usergroups-grid-mine',
                    'lsPageSizeCurrentValue' => $pageSize,
                ]
            );
            ?>
        </div>
</div>

    <h2 class="h4"><?php
    if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
        eT('Groups to which I belong');
    }
    ?>
    </h2>

    <div class="row">
        <div class="col-12">
            <?php
            if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                $this->widget(
                    'application.extensions.admin.grid.CLSGridView',
                    [
                        'dataProvider' => $model->searchMine(false),
                        'id' => 'usergroups-grid-belong-to',
                        'lsCaption' => gT('Groups to which I belong'),
                        'emptyText' => gT('No user groups found.'),
                        'lsPageSizeCurrentValue' => $pageSize,
                        'columns' => $model->columns,
                        'selectionChanged' => "function(id){window.location='" . Yii::app()->urlManager->createUrl('userGroup/viewGroup/ugid') . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
                        'ajaxUpdate' => 'usergroups-grid-belong-to',
                    ]
                );
            }
            ?>
        </div>
    </div>

</div>
