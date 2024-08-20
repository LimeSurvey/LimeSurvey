<?php
/* @var $basePermissions array the base permissions a user could have */
/* @var $userCreatePermission bool true if current user has permission to set survey permission for other users */
/* @var $surveyid int */
/* @var $userList array users that could be added to survey permissions */
/* @var $userGroupList array user groups that could be added to survey permissions */
/* @var $tableContent CActiveDataProvider dataProvider for the gridview (table) */
/* @var $oSurveyPermissions \LimeSurvey\Models\Services\SurveyPermissions */

?>
<div id='edit-permission' class='side-body position-relative  ls-settings-wrapper"'>
    <?php echo viewHelper::getViewTestTag('surveyPermissions'); ?>
    <h1> <?= gT("Survey permissions") ?> </h1>
    <div class="row pt-2 pb-2 align-items-center">
        <div class="col-12 align-items-center">
            <?php
            if ($userCreatePermission) {
                echo CHtml::form(
                    ["surveyPermissions/adduser/surveyid/{$surveyid}"],
                    'post',
                    ['class' => "form44"]
                ); ?>

                <div class="row justify-content-md-end mb-2">
                    <label class='col-1 text-end control-label' for='uidselect'>
                        <?= gT("User") ?>:
                    </label>
                    <div class='col-4'>
                        <select style="width:100%;" id='uidselect' name='uid' class='form-select activate-search'>
                            <?php
                            if (count($userList) > 0) {
                                echo "<option value='-1' selected='selected'>" . gT("Please choose...") . "</option>";
                                foreach ($userList as $selectableUser) {
                                    echo "<option value='{$selectableUser['userid']}'>"
                                        . \CHtml::encode($selectableUser['usersname']) . " "
                                        . \CHtml::encode($selectableUser['fullname']) . "</option>\n";
                                }
                            } else {
                                echo "<option value='-1'>" . gT("None") . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-3">
                    <input class='btn btn-outline-secondary w-100' type='submit' value='<?= gT("Add user") ?>'/>
                    <input type='hidden' name='action' value='addsurveysecurity'/>
                    </div>
                </div>
                <?= CHtml::endForm() ?>

                <?php
                echo CHtml::form(
                    ["surveyPermissions/addusergroup/surveyid/{$surveyid}"],
                    'post',
                    ['class' => "form44"]
                ); ?>
                <div class="row justify-content-md-end">
                    <label class='col-2 text-end control-label' for='ugidselect'>
                        <?= gT("User group") ?>:
                    </label>
                    <div class='col-4'>
                        <select style="width:100%;" id='ugidselect' name='ugid' class='form-select activate-search'>
                            <?php
                            if (count($userGroupList) > 0) {
                                echo "<option value='-1' selected='selected'>" . gT("Please choose...") . "</option>";
                                foreach ($userGroupList as $userGroup) {
                                    echo "<option value='{$userGroup['ugid']}'>{$userGroup['name']}</option>";
                                }
                            } else {
                                echo "<option value='-1'>" . gT("None") . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-3">
                    <input class='btn btn-outline-secondary w-100' type='submit' value='<?= gT("Add group users") ?>'/>
                    <input type='hidden' name='action' value='addusergroupsurveysecurity'/>
                    </div>
                </div>
                <?= CHtml::endForm() ?>
            <?php }
            ?>
        </div>
    </div>
    <div class="row">
        <?php
        $baseColumns = [];
        foreach ($basePermissions as $sPermission => $aSubPermissions) {
            array_push(
                $baseColumns,
                [
                    'header'            => $aSubPermissions['title'],
                    'type'              => 'raw',
                    'value'             => function ($data) use ($oSurveyPermissions, $sPermission, $aSubPermissions) {
                        $result = $oSurveyPermissions->getTooltipAllPermissions($data->uid, $sPermission, $aSubPermissions);
                        if ($result['hasPermissions']) {
                            return CHtml::tag('div', [
                                "data-bs-toggle" => "tooltip",
                                'title'          => ucfirst(implode(', ', $result['permissionCrudArray'])),
                                'class'          => $result['allPermissionsSet'] ? 'text-center' : 'text-center mixed'
                            ], '<i class="ri-check-fill"></i>');
                        }
                        return CHtml::tag('div', ['class' => 'text-center'], '&#8211');
                    },
                    'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                    'htmlOptions'       => ['class' => 'd-none d-sm-table-cell '],
                ]
            );
        }
        array_push($baseColumns, [
            'header'            => gT('Action'),
            'name'              => 'actions',
            'value'             => '$data->buttons',
            'type'              => 'raw',
            'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
            'htmlOptions'       => ['class' => 'text-center ls-sticky-column'],
        ]);

        $this->widget(
            'application.extensions.admin.grid.CLSGridView',
            [
                'id'           => 'gridPanel',
                'dataProvider' => $dataProvider,
                'columns'      => array_merge([
                    [
                        'header'            => gT('Username'),
                        'name'              => 'users_name',
                        'type'              => 'raw',
                        'value'             => '$data->user->users_name',
                        'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                        'htmlOptions'       => ['class' => 'd-none d-sm-table-cell'],
                    ],
                    [
                        'header' => gT('User group'),
                        'type'   => 'raw',
                        'value'  => function ($data) use ($oSurveyPermissions) {
                            $groupsStr = $oSurveyPermissions->getUserGroupNames($data->uid,
                                App()->getConfig('usercontrolSameGroupPolicy'));
                            return implode(", ", $groupsStr);
                        },

                        'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                        'htmlOptions'       => ['class' => 'd-none d-sm-table-cell '],
                    ],
                    [
                        'header'            => gT('Full name'),
                        'name'              => 'full_name',
                        'type'              => 'raw',
                        'value'             => '$data->user->full_name',
                        'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                        'htmlOptions'       => ['class' => 'd-none d-sm-table-cell'],
                    ]

                ], $baseColumns),
                'lsAfterAjaxUpdate' => ['LS.UserManagement.bindButtons();']

            ]
        );
        ?>

    </div>
    <?php $this->renderPartial('/surveyAdministration/_user_management_sub_footer'); ?>

</div>
<div id='UserManagement-action-modal' class="modal fade UserManagement--selector--modal" tabindex="-1" role="dialog">
    <div id="usermanagement-modal-doalog" class="modal-dialog" role="document">
        <div class="modal-content">
        </div>
    </div>
</div>
