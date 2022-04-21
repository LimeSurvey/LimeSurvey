<div class="table-responsive">
    <table class='table table-hover table-permissions-set'>
        <thead>
        <tr>
            <th></th>
            <th>
                <?php eT("Permission"); ?>
            </th>
            <th>
                <input type="checkbox" class="markall" name='markall'/>
                <input type='button' id='btnToggleAdvanced' value='<<' class='btn btn-outline-secondary'/>
            </th>
            <th class='extended'><?php eT("Create"); ?></th>
            <th class='extended'><?php eT("View/read"); ?></th>
            <th class='extended'><?php eT("Update"); ?></th>
            <th class='extended'><?php eT("Delete"); ?></th>
            <th class='extended'><?php eT("Import"); ?></th>
            <th class='extended'><?php eT("Export"); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($aPermissions as $sPermission => $aCurrentPermissions) : ?>
            <tr>
                <td><?= $aCurrentPermissions['description'] ?></td>
                <td><?= $aCurrentPermissions['title'] ?></td>
                <td><?php echo CHtml::checkBox("all_$sPermission", false, ['class' => 'markrow']) ?></td>
                <?php foreach ($aCurrentPermissions['current'] as $sKey => $aValues) : ?>
                    <td class='extended'>
                        <?php if ($aCurrentPermissions[$sKey] && !$aValues['forced']) {
                            echo CHtml::checkBox(
                                "set[{$aCurrentPermissions['entity']}][{$sPermission}][$sKey]",
                                $aValues['checked'],
                                [
                                    'value'              => 1,
                                    'data-indeterminate' => $aValues['indeterminate'],
                                    'id'                 => CHtml::getIdByName("set[{$aCurrentPermissions['entity']}][{$sPermission}][$sKey]"),
                                    'uncheckValue'       => 0,
                                    /* See issue #14551 : https://bugs.limesurvey.org/view.php?id=14551 */
                                    'disabled'           => $aValues['disabled'],
                                ]
                            );
                        } ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
App()->getClientScript()->registerPackage('jquery-tablesorter');
    App()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(Yii::getPathOfAlias('ext.UserPermissionsWidget.assets') . '/script.js'));
?>
