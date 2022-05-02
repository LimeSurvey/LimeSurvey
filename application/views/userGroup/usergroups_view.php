<?php
/**
 * This file render the list of user groups
 * It use the Label Sets model search method to build the data provider.
 *
 * @var UserGroup $model the UserGroup model
 * @var int $pageSize
 */
?>
<div class="container-fluid">
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
                $this->widget('yiistrap.widgets.TbGridView',
                    [
                        'dataProvider'     => $model->searchMine(true),
                        'id'               => 'usergroups-grid-mine',
                        'emptyText'        => gT('No user groups found.'),
                        'htmlOptions'      => ['class' => 'table-responsive grid-view-ls'],
                        'selectionChanged' => "function(id){window.location='" . Yii::app()->urlManager->createUrl('userGroup/viewGroup/ugid') . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
                        'ajaxUpdate'       => 'usergroups-grid-mine',
                        'template'         => "{items}\n<div id='tokenListPager'><div class=\"col-md-4\" id=\"massive-action-container\"></div><div class=\"col-md-4 pager-container ls-ba \">{pager}</div><div class=\"col-md-4 summary-container\">{summary}</div></div>",
                        'summaryText'      => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                                gT('%s rows per page'),
                                CHtml::dropDownList(
                                    'pageSize',
                                    $pageSize,
                                    Yii::app()->params['pageSizeOptions'],
                                    [
                                        'class' => 'changePageSize form-control',
                                        'style' => 'display: inline; width: auto'
                                    ])),

                        'columns' => [

                            [
                                'header'      => gT('Actions'),
                                'name'        => 'actions',
                                'type'        => 'raw',
                                'value'       => '$data->buttons',
                                'htmlOptions' => ['class' => 'text-start'],
                            ],
                            [
                                'header'      => gT('User group ID'),
                                'name'        => 'usergroup_id',
                                'value'       => '$data->ugid',
                                'htmlOptions' => ['class' => ''],
                            ],


                            [
                                'header'      => gT('Name'),
                                'name'        => 'name',
                                'value'       => '$data->name',
                                'htmlOptions' => ['class' => ''],
                            ],

                            [
                                'header'      => gT('Description'),
                                'name'        => 'description',
                                'value'       => '$data->description',
                                'htmlOptions' => ['class' => ''],
                            ],

                            [
                                'header'      => gT('Owner'),
                                'name'        => 'owner',
                                'value'       => '$data->owner->users_name',
                                'htmlOptions' => ['class' => ''],
                            ],

                            [
                                'header'      => gT('Members'),
                                'name'        => 'members',
                                'value'       => '$data->countUsers',
                                'htmlOptions' => ['class' => ''],
                            ],
                        ],
                    ]);
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
                    $this->widget('yiistrap.widgets.TbGridView',
                        [
                            'dataProvider'     => $model->searchMine(false),
                            'id'               => 'usergroups-grid-belong-to',
                            'emptyText'        => gT('No user groups found.'),
                            'template'         => "{items}\n<div id='tokenListPager'><div class=\"col-md-4\" id=\"massive-action-container\"></div><div class=\"col-md-4 pager-container ls-ba \">{pager}</div><div class=\"col-md-4 summary-container\">{summary}</div></div>",
                            'summaryText'      => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                                    gT('%s rows per page'),
                                    CHtml::dropDownList(
                                        'pageSize',
                                        $pageSize,
                                        Yii::app()->params['pageSizeOptions'],
                                        [
                                            'class' => 'changePageSize form-control',
                                            'style' => 'display: inline; width: auto'
                                        ]
                                    )
                                ),
                            'columns'          => $model->columns,
                            'htmlOptions'      => ['class' => 'table-responsive grid-view-ls'],
                            'selectionChanged' => "function(id){window.location='" . Yii::app()->urlManager->createUrl('userGroup/viewGroup/ugid') . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
                            'ajaxUpdate'       => 'usergroups-grid-belong-to',
                        ]);
                }
                ?>
            </div>
        </div>

    </div>

    <div class="modal fade" tabindex="-1" id="delete-modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?php
                Yii::app()->getController()->renderPartial(
                    '/layouts/partial_modals/modal_header',
                    ['modalTitle' => gT('Delete this user group')]
                );
                ?>
                <div class="modal-body">
                    <?= CHtml::form(
                        ["userGroup/deleteGroup"],
                        'post',
                        ['class' => '', 'id' => 'delete-modal-form', 'name' => 'delete-modal-form']
                    ) ?>
                    <p><?= gT('Are you sure you want to delete this user group?') ?></p>
                    <input type="hidden" name="ugid" id="delete-ugid" value=""/>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-dismiss="modal"><?= gT('Cancel') ?></button>
                    <button type="button" class="btn btn-danger" id="confirm-deletion"><?= gT('Delete') ?></button>
                </div>
            </div>
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
        //Delete button
        $(document).ready(function () {
            $('.action__delete-group').on('click', function (event) {
                event.stopPropagation();
                event.preventDefault();
                $('#delete-modal').modal('show');

                $('#delete-ugid').val($(this).data('ugid'));

                $('#confirm-deletion').on('click', function () {
                    $('#delete-modal-form').submit();
                });
            });
        });
    });
</script>
