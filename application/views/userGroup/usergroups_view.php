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

    <div class="h4"><?php
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            eT('My user groups');
        }
        ?>
    </div>

    <div class="row">
        <div class="col-12">
            <?php
            $this->widget('application.extensions.admin.grid.CLSGridView',
                [
                    'id' => 'usergroups-grid-mine',
                    'dataProvider' => $model->searchMine(true),
                    'columns' => $model->getManagementButtons(),
                    'emptyText' => gT('No user groups found.'),
                    'ajaxUpdate' => 'usergroups-grid-mine',
                    'summaryText' => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                            gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                App()->params['pageSizeOptions'],
                                [
                                    'class' => 'changePageSize form-select',
                                    'style' => 'display: inline; width: auto'
                                ]
                            )
                        ),
                ]
            );
            ?>
        </div>
    </div>

    <div class="h4"><?php
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            eT('Groups to which I belong');
        }
        ?>
    </div>

    <div class="row">
        <div class="col-12">
            <?php
            if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                $this->widget('application.extensions.admin.grid.CLSGridView',
                    [
                        'dataProvider' => $model->searchMine(false),
                        'id' => 'usergroups-grid-belong-to',
                        'emptyText' => gT('No user groups found.'),
                        'summaryText' => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                                gT('%s rows per page'),
                                CHtml::dropDownList(
                                    'pageSize',
                                    $pageSize,
                                    Yii::app()->params['pageSizeOptions'],
                                    [
                                        'class' => 'changePageSize form-select',
                                        'style' => 'display: inline; width: auto'
                                    ]
                                )
                            ),
                        'columns' => $model->columns,
                        'selectionChanged' => "function(id){window.location='" . Yii::app()->urlManager->createUrl('userGroup/viewGroup/ugid'
                            ) . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
                        'ajaxUpdate' => 'usergroups-grid-belong-to',
                    ]
                );
            }
            ?>
        </div>
    </div>

</div>

<script type="text/javascript">
    jQuery(function ($) {
        // To update rows per page via ajax
        $(document).on("change", '#pageSize', function () {
            $.fn.yiiGridView.update('usergroups-grid-mine', {data: {pageSize: $(this).val()}});
            $.fn.yiiGridView.update('usergroups-grid-belong-to', {data: {pageSize: $(this).val()}});
        });
    });
</script>
