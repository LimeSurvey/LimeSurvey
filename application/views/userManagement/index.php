<?php
/**
 * @var $this UserManagementController
 * @var $dataProvider CActiveDataProvider
 * @var $model User
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
    $this->widget(
        'application.extensions.admin.grid.CLSGridView',
        [
            'id' => 'usermanagement--identity-gridPanel',
            'ajaxUpdate' => 'usermanagement--identity-gridPanel',
            'dataProvider' => $model->search(),
            'columns' => $model->getManagementColums(),
            'lsAdditionalColumns' => $model->getAdditionalColumns(),
            'lsShowSelectionBar' => false,
            'lsCaption' => gT('User management'),
            'lsAfterAjaxUpdate' => [
                'LS.UserManagement.bindButtons();',
                'showDeactivatedUserTooltip();'
            ],
            'filter' => $model,
            'lsPageSizeCurrentValue' => $pageSize,
        ]
    );
    ?>

    <!-- Floating Actions Widget for User Management -->
    <?php
    require_once Yii::getPathOfAlias('application.extensions.admin.grid.FloatingActionsWidget.actions.UserManagementMassiveActions') . '.php';
    $aActions = \actions\UserManagementMassiveActions::getActions();
    $this->widget(
        'ext.admin.grid.FloatingActionsWidget.FloatingActionsWidget',
        [
            'pk'       => 'uid',
            'gridId'   => 'usermanagement--identity-gridPanel',
            'aActions' => $aActions,
        ]
    );
    ?>

<!-- To update rows per page via ajax -->
<script type="text/javascript">
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        </div>
    </div>
</div>
