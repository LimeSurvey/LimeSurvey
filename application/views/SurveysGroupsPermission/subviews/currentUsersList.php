<?php if(!empty($oExistingUsers)) : ?>
<h3 class="pagetitle h2"><?php eT('Current permissions:') ?></h3>
    <table class='table striped hoverAction' style="cursor: pointer;">
        <thead>
            <tr>
                <th><?= eT("Action") ?></th>
                <th><?= eT("Username") ?></th>
                <th><?= eT("Full name") ?></th>
                <?php foreach ($aDefinitionPermissions as $sPermission=>$aPermission) : ?>
                <th><?= $aPermission['title'] ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach($oExistingUsers as $oUser) : ?>
            <tr>
                <td>
                    <?php if ($model->hasPermission('permission', 'update')) : ?>
                        <a href="<?= $this->createUrl("surveysGroupsPermission/viewUser",array('id'=>$model->gsid, 'to' => $oUser->uid));?>" class="btn btn-default btn-sm" role="button">
                            <span class="fa fa-pencil text-success" aria-hidden="true" title="<?= gT("Edit permissions") ?>"><span>
                            <span class="sr-only"><?= gT("Edit permissions") ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ($model->hasPermission('permission', 'delete')) : ?>
                        <?php $deleteUrl = App()->createUrl("surveysGroupsPermission/deleteUser", array(
                            'id'=>$model->gsid,
                            'uid' => $oUser->uid
                        )); ?>
                        <?php /* @see https://bugs.limesurvey.org/view.php?id=16792 */ ?>
                        <a class="btn btn-default btn-sm" role="button"
                            data-target='#confirmation-modal' data-toggle='modal'
                            data-message='<?= gT("Are you sure you want to remove all permissions for this user?") ?>'
                            data-href='<?= $deleteUrl ?>'
                        ><span class="fa fa-trash text-warning" aria-hidden="true" title="<?= gT("Delete") ?>"><span>
                            <span class="sr-only"><?= gT("Delete") ?></span>
                        </a>
                    <?php endif; ?>
                </td>
                <td><?= CHtml::encode($oUser->full_name); ?></td>
                <td><?= CHtml::encode($oUser->users_name); ?></td>
                <?php foreach ($aDefinitionPermissions as $sPermission=>$aPermission) : ?>
                    <td class="text-center">
                    <?php if(!empty($aCurrentsUserRights[$oUser->uid][$sPermission])) : ?>
                        <span
                            data-toggle="tooltip" data-title="<?= implode(", ",$aCurrentsUserRights[$oUser->uid][$sPermission]) ?>"
                            class="fa fa-check <?= count($aCurrentsUserRights[$oUser->uid][$sPermission]) < $aPermission['maxCrud'] ? 'mixed' : "" ?>">
                        </span>
                        <span class="sr-only"><?= implode($aCurrentsUserRights[$oUser->uid][$sPermission]) ?></span>
                    <?php else : ?>
                        –
                    <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
