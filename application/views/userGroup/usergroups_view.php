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
                            'template' => "{items}\n<div id='tokenListPager'><div class=\"col-md-4\" id=\"massive-action-container\"></div><div class=\"col-md-4 pager-container ls-ba \">{pager}</div><div class=\"col-md-4 summary-container\">{summary}</div></div>",
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
                    <button
                    	type="button" 
                    	class="btn btn-cancel" 
                    	data-bs-dismiss="modal">
                    	<?= gT('Cancel') ?>
                    </button>
                    <button 
                    	type="button" 
                    	class="btn btn-danger" 
                    	id="confirm-deletion">
                    	<?= gT('Delete') ?>
                    	</button>
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
