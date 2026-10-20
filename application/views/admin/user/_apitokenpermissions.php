<?php

/**
 * Permission grid of an API token, for the global permissions or the permissions of one survey.
 * Only the permission details the user has are shown.
 *
 * @var array  $permissions See UserApiToken::getPermissionGrid()
 * @var string $namePrefix  Prefix of the checkbox names, e.g. ApiTokenPermission[global][0]
 * @var string $idPrefix    Prefix of the element IDs, unique on the page
 * @var string $caption     Caption of the table
 */

$crudLabels = [
    'create' => gT("Create"),
    'read' => gT("View/read"),
    'update' => gT("Update"),
    'delete' => gT("Delete"),
    'import' => gT("Import"),
    'export' => gT("Export"),
];
?>
<?php if (empty($permissions)) : ?>
    <p><?php eT("You don't have any permissions that could be given to the API token here."); ?></p>
<?php else : ?>
<div class="table-responsive">
    <table class="table table-hover apitoken-permissions">
        <caption class="visually-hidden"><?php echo CHtml::encode($caption); ?></caption>
        <thead>
            <tr>
                <th scope="col"><?php eT("Permission"); ?></th>
                <?php foreach ($crudLabels as $crudLabel) : ?>
                    <th scope="col" class="text-center"><?php echo $crudLabel; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($permissions as $permission => $permissionData) : ?>
                <?php
                // Titles and descriptions of permissions can contain HTML entities, show them as plain text
                $permissionTitle = flattenText($permissionData['title'], false, true);
                $descriptionId = CHtml::getIdByName("{$idPrefix}-{$permission}-description");
                ?>
                <tr>
                    <th scope="row">
                        <?php echo CHtml::encode($permissionTitle); ?>
                        <div id="<?php echo $descriptionId; ?>" class="form-text fw-normal"><?php echo CHtml::encode(flattenText($permissionData['description'], false, true)); ?></div>
                    </th>
                    <?php foreach ($crudLabels as $crud => $crudLabel) : ?>
                        <td class="text-center">
                            <?php if (isset($permissionData['cruds'][$crud])) : ?>
                                <?php $checkboxId = CHtml::getIdByName("{$idPrefix}-{$permission}-{$crud}"); ?>
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="<?php echo $checkboxId; ?>"
                                    name="<?php echo CHtml::encode("{$namePrefix}[{$permission}][{$crud}]"); ?>"
                                    value="1"
                                    aria-describedby="<?php echo $descriptionId; ?>"
                                    <?php echo $permissionData['cruds'][$crud] ? 'checked' : ''; ?>
                                />
                                <label class="visually-hidden" for="<?php echo $checkboxId; ?>">
                                    <?php echo CHtml::encode(sprintf(gT("%s: %s", 'unescaped'), $permissionTitle, $crudLabel)); ?>
                                </label>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
