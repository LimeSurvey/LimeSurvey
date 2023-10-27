<?php
/**
 * @var $this UserManagementController
 * @var $dataProvider CActiveDataProvider
 * @var $model User
 * @var string $massiveAction
 * @var string $pageSize selected pagesize
 **/

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('usersIndex');

?>

<?php if (!Permission::model()->hasGlobalPermission('users', 'read')) : ?>
    <div class="row">
        <div class="col-12">
            <h2><?= gT("We are sorry but you don't have permissions to do this.") ?></h2>
        </div>
    </div>
    <?php App()->end(); ?>
<?php endif; ?>

    <?php
    $this->widget('application.extensions.admin.grid.CLSGridView',
        [
            'id' => 'usermanagement--identity-gridPanel',
            'dataProvider' => $model->search(),
            'columns' => $model->getManagementColums(),
            'massiveActionTemplate' => $massiveAction,
            'lsAfterAjaxUpdate' => [
                'bindListItemclick();',
                'LS.UserManagement.bindButtons();',
                'showDeactivatedUserTooltip();'
            ],
            'filter' => $model,
            'summaryText' => gT('Displaying {start}-{end} of {count} result(s).') . ' '
                . sprintf(
                    gT('%s rows per page'),
                    CHtml::dropDownList(
                        'pageSize',
                        $pageSize,
                        App()->params['pageSizeOptions'],
                        ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                    )
                ),
        ]
    );
    ?>

<!-- To update rows per page via ajax -->
<script type="text/javascript">
    jQuery(function ($) {
        jQuery(document).on("change", '#pageSize', function () {
            $.fn.yiiGridView.update('usermanagement--identity-gridPanel', {data: {pageSize: $(this).val()}});
        });
    });
    //show tooltip for gridview icons
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    function showDeactivatedUserTooltip() {
        $('#usermanagement--identity-gridPanel #bottom-scroller table .activation').each(function (i, item) {
            if (item.innerHTML == '0'){
                var tr = item.closest('tr')
                tr.classList += ' disabled';
                tr.setAttribute('data-toggle', 'tooltip');
                tr.setAttribute('data-placement', 'top');
                tr.setAttribute('title', '<?= gT("Deactivated user") ?>');
            }
        });
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    }
    $(document).on('ready pjax:scriptcomplete', function(){
        showDeactivatedUserTooltip()
    });

</script>
<div id='UserManagement-action-modal' class="modal fade UserManagement--selector--modal" tabindex="-1" role="dialog">
    <div id="usermanagement-modal-doalog" class="modal-dialog" role="document">
        <div class="modal-content">
        </div>
    </div>
</div>
