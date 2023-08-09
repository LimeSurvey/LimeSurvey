<?php

/* @var $basePermissions array the base permissions a user could have */
/* @var $tableContent Permission[] dataProvder for the gridview (table) */
/* @var $surveyid int */
/* @var $oSurveyPermissions \LimeSurvey\Models\Services\SurveyPermissions */

?>
<table class='surveysecurity table table-striped table-hover'>
    <thead>
    <tr>
        <th> <?= gT("Action") ?> </th>
        <th> <?= gT("Username") ?> </th>
        <th> <?= gT("User group") ?> </th>
        <th> <?= gT("Full name") ?> </th>
        <?php foreach ($basePermissions as $sPermission => $aSubPermissions) {
            echo "<th>" . $aSubPermissions['title'] . "</th>\n";
        } ?>
    </tr>
    </thead>

    <tbody>
    <?php
    foreach ($tableContent as $content) {
        /** @var $content Permission */
        //button column
        ?>
        <tr>
            <td class='text-center button-column'>
                <div class="icon-btn-row">
                    <?php if (Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'update')) { ?>
                        <a class="btn btn-outline-secondary btn-sm"
                           href="<?php echo Yii::app()->createUrl("surveyPermissions/settingsPermissions/", [
                               'surveyid' => $surveyid,
                               'action' => 'user',
                               'id' => $content->uid
                           ]); ?>" data-bs-toggle='tooltip' title="<?= gT("Edit permissions") ?>">
                            <span class='ri-pencil-fill text-success'></span>
                        </a>
                    <?php } ?>
                    <?php if (Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'delete')) {
                        $deleteUrl = App()->createUrl("surveyPermissions/deleteUserPermissions/");
                        $deleteConfirmMessage = gT("Are you sure you want to delete this entry?"); ?>
                        <span data-bs-toggle='tooltip' title=" <?= gT("Delete") ?> ">
                    <a
                        data-bs-target='#confirmation-modal'
                        data-bs-toggle='modal'
                        data-btntext="Delete"
                        data-title="<?php echo gt('Delete user survey permissions') ?>"
                        data-btnclass='btn-danger'
                        data-message="<?php echo $deleteConfirmMessage; ?>"
                        data-post-url="<?php echo $deleteUrl; ?>"
                        data-post-datas='<?php echo json_encode(['surveyid' => $surveyid, 'userid' => $content->uid]); ?>'
                        type='submit'
                        class='btn btn-outline-secondary btn-sm'>
                        <span class='ri-delete-bin-fill text-danger'></span>
                    </a>
                </span>
                    <?php } ?>
                </div>
            </td>
            <td><?php echo $content->user->users_name ?></td>
            <td>
                <?php
                $groupsStr = $oSurveyPermissions->getUserGroupNames($content->uid, Yii::app()->getConfig('usercontrolSameGroupPolicy'));
                echo implode(", ", $groupsStr);
                ?>
            </td>
            <td><?php echo $content->user->full_name ?></td>
            <?php
            // permission columns
            foreach ($basePermissions as $sPermission => $aSubPermissions) {
                $userPerm = $oSurveyPermissions->getUsersSurveyPermissionEntity($content->uid, $sPermission);
                ?>
                <td class='text-center'>
                    <?php $result = $oSurveyPermissions->getTooltipAllPermissions($content->uid, $sPermission, $aSubPermissions); ?>
                    <?php if ($result['hasPermissions']) : ?>
                        <div data-bs-toggle="tooltip" title="<?= ucfirst(implode(', ', $result['permissionCrudArray'])) ?>"
                             class="ri-check-fill <?= $result['allPermissionsSet'] ? '' : 'mixed' ?>">
                            &nbsp;
                        </div>
                    <?php else : ?>
                        <div>&#8211;</div>
                    <?php endif; ?>
                </td>
            <?php } ?>
        </tr>
    <?php }
    // ?>
    </tbody>
</table>

