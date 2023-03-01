<?php
/* @var $basePermissions array the base permissions a user could have */
/* @var $userCreatePermission bool true if current user has permission to set survey permission for other users */
/* @var $surveyid int */
/* @var $userList array users that could be added to survey permissions */
/* @var $userGroupList array usergroups that could be added to survey permissions */
/* @var $tableContent CActiveDataProvider dataProvider for the gridview (table) */
/* @var $oSurveyPermissions \LimeSurvey\Models\Services\SurveyPermissions */

?>
<div id='edit-permission' class='side-body position-relative  ls-settings-wrapper <?= getSideBodyClass(false) ?> "'>
    <?php echo viewHelper::getViewTestTag('surveyPermissions'); ?>
    <h1> <?= gT("Survey permissions") ?> </h1>
    <div class="row p-2 align-items-center">
        <div class="col-lg-3 col-12">
        </div>
        <div class="col-lg-9 col-12 align-items-center">
            <?php
            if ($userCreatePermission) {
                echo CHtml::form(
                    array("surveyPermissions/adduser/surveyid/{$surveyid}"),
                    'post',
                    array('class' => "form44")
                ); ?>

                <div class="row justify-content-md-end mb-2">
                    <label class='col-sm-1 col-md-offset-2  text-end control-label' for='uidselect'>
                        <?= gT("User") ?>:
                    </label>
                    <div class='col-sm-4'>
                        <select id='uidselect' name='uid' class='form-select'>
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
                    <input style='width: 15em;' class='btn btn-outline-secondary' type='submit' value='<?= gT("Add user") ?>' />
                    <input type='hidden' name='action' value='addsurveysecurity' />
                </div>
                </form>

                <?php
                echo CHtml::form(
                    array("surveyPermissions/addusergroup/surveyid/{$surveyid}"),
                    'post',
                    array('class' => "form44")
                ); ?>
                <div class="row justify-content-md-end">
                    <label class='col-sm-2 col-md-offset-2  text-end control-label' for='ugidselect'>
                        <?= gT("User group") ?>:
                    </label>
                    <div class='col-sm-4'>
                        <select id='ugidselect' name='ugid' class='form-select'>
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
                    <input style='width: 15em;' class='btn btn-outline-secondary' type='submit' value='<?= gT("Add group users") ?>' />
                    <input type='hidden' name='action' value='addusergroupsurveysecurity' />
                </div>
                </form>
            <?php }
            ?>
        </div>
    </div>
    <div class="row">
        <div class="container-fluid">
            <?php
            $baseColumns = [];
            foreach ($basePermissions as $sPermission => $aSubPermissions) {
                array_push(
                    $baseColumns,
                    [
                        'header'            => $aSubPermissions['title'],
                        'type'              => 'raw',
                        'value'             => function ($data) use ($oSurveyPermissions,  $sPermission,  $aSubPermissions) {
                            $result = $oSurveyPermissions->getTooltipAllPermissions($data->uid, $sPermission, $aSubPermissions);
                            if ($result['hasPermissions']) {
                                return CHtml::tag('div', [
                                    "data-bs-toggle" => "tooltip",
                                    'title' => ucfirst(implode(', ', $result['permissionCrudArray'])),
                                    'class' =>  $result['allPermissionsSet'] ? 'text-center' : 'text-center mixed'
                                ], '<i class="ri-check-fill"></i>');
                            }
                            return CHtml::tag('div', ['class' => 'text-center'], '&#8211');
                        },
                        'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                        'htmlOptions'       => ['class' => 'd-none d-sm-table-cell '],
                    ],
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
                    'id' => 'gridPanel',
                    'dataProvider' => $dataProvider,
                    'columns' => array_merge([
                        [
                            'header'           => gT('Username'),
                            'name'              => 'users_name',
                            'type'              => 'raw',
                            'value'             => '$data->user->users_name',
                            'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                            'htmlOptions'       => ['class' => 'd-none d-sm-table-cell'],
                        ],
                        [
                            'header'           => gT('User Group'),
                            'type'              => 'raw',
                            'value'             =>  function ($data) use ($oSurveyPermissions) {
                                $groupsStr = $oSurveyPermissions->getUserGroupNames($data->uid, App()->getConfig('usercontrolSameGroupPolicy'));
                                return implode(", ", $groupsStr);
                            },

                            'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                            'htmlOptions'       => ['class' => 'd-none d-sm-table-cell '],
                        ],
                        [
                            'header'           => gT('Full Name'),
                            'name'              => 'full_name',
                            'type'              => 'raw',
                            'value'             => '$data->user->full_name',
                            'headerHtmlOptions' => ['class' => 'd-none d-sm-table-cell text-nowrap'],
                            'htmlOptions'       => ['class' => 'd-none d-sm-table-cell'],
                        ]

                    ], $baseColumns),

                ]
            );
            ?>

        </div>
    </div>
<!-- TODO: duplicate, move grid into partial or other way around -->
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php
            $this->renderPartial('_overview_table', [
                'basePermissions' => $basePermissions,
                'tableContent' => $tableContent,
                'surveyid' => $surveyid,
                'oSurveyPermissions' => $oSurveyPermissions
            ]);
            ?>
        </div>
    </div>
    <?php $this->renderPartial('/surveyAdministration/_user_management_sub_footer'); ?>

</div>
