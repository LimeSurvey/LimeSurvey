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
    <?php //todo here we must show the data from db ...
    foreach ($tableContent as $content) {
        /** @var $content Permission */
        //button column
        ?>
    <tr>
        <td class='icon-btn-row'>
        <?php if (Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'update')) {?>
            <a class="btn btn-default btn-sm green-border" href="<?php echo Yii::app()->createUrl("surveyPermissions/settingsPermissions/", [
                'surveyid' => $surveyid,
                'action' => 'user',
                'id' => $content->uid
            ]);?>" data-toggle='tooltip' title="<?= gT("Edit permissions")?>">
                <span class='fa fa-pencil text-success'></span>
            </a>
        <?php }?>
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'delete')) {
                $deleteUrl = App()->createUrl("surveyPermissions/deleteUserPermissions/", array(
                'surveyid' => $surveyid,
                'uid' => $content->uid
                ));
                $deleteConfirmMessage = gT("Are you sure you want to delete this entry?"); ?>
                <span data-toggle='tooltip' title=" <?= gT("Delete") ?> ">
                    <a
                        data-target='#confirmation-modal'
                        data-toggle='modal'
                        data-btntext="Confirm"
                        data-message="<?php echo $deleteConfirmMessage;?>"
                        data-post-url="<?php echo $deleteUrl;?>"
                        type='submit'
                        class='btn-sm btn btn-default'>
                        <span class='fa fa-trash text-danger'></span>
                    </a>
                </span>
            <?php }?>
        </td>
        <td><?php echo $content->user->users_name?></td>
        <td> the uers group</td>
        <td><?php echo $content->user->full_name?></td>
        <?php
        // permission columns
        foreach ($basePermissions as $sPermission => $aSubPermissions) {
            $userPerm = $oSurveyPermissions->getUsersSurveyPermissionEntity(
                $content->uid,
                $sPermission,
            );
            $allPermsSet = true; ?>
        <td class='text-center' >
            <?php
            $title = '';
            $allIn = true;
            if ($userPerm !== null) {
                if ($aSubPermissions['create']) {
                    if ($userPerm->create_p == 1) {
                        $title .= ' create';
                    } else {
                        $allIn = false;
                    }
                }
                if ($aSubPermissions['read']) {
                    if ($userPerm->read_p == 1) {
                        $title .= ' read';
                    } else {
                        $allIn = false;
                    }
                }
                if ($aSubPermissions['update']) {
                    if ($userPerm->update_p == 1) {
                        $title .= ' update';
                    } else {
                        $allIn = false;
                    }
                }
                if ($aSubPermissions['delete']) {
                    if ($userPerm->delete_p == 1) {
                        $title .= ' delete';
                    } else {
                        $allIn = false;
                    }
                }
                if ($aSubPermissions['import']) {
                    if ($userPerm->import_p == 1) {
                        $title .= ' import';
                    } else {
                        $allIn = false;
                    }
                }
                if ($aSubPermissions['export']) {
                    if ($userPerm->export_p == 1) {
                        $title .= ' export';
                    } else {
                        $allIn = false;
                    }
                }
                if ($allIn) {
                    $appendClass = 'class="fa fa-check ">&nbsp;</div>';
                } else {
                    $appendClass = 'class="fa fa-check mixed">&nbsp;</div>';
                }
                echo "<div data-toggle='tooltip' title='" . $title . "'" . $appendClass;
            } else {
                echo '<div>&#8211;</div>';
            }
            ?>

         </td>
        <?php }
        // create the title 'create, read ...'
        ?>
    </tr>
    <?php    }
    // ?>
    </tbody>
</table>

