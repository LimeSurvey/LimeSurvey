<div class="col-12 list-surveys" id="comfortUpdateGeneralWrap">
    <div class="pagetitle h3">
        <span id="comfortUpdateIcon" class="ri-shield-check-fill text-success"></span>
        <?php eT('ComfortUpdate'); ?>
        <?php if (YII_DEBUG): ?>
            <small>
                Server:
                <em class="text-primary">
                    <?= Yii::app()->getConfig("comfort_update_server_url"); ?>
                </em>
            </small>
        <?php endif; ?>
    </div>

    <table aria-label="<?= gT('Your update key:') ?>" class="items table w-75 m-auto">
        <!-- header -->
        <thead>
        <tr>
            <th>
                <?php eT('Your update key:'); ?>
            </th>
            <th>
                <?php eT('Valid until:'); ?>
            </th>
            <th>
                <?php eT('Remaining updates:'); ?>
            </th>
            <th>
            </th>
        </tr>
        </thead>

        <tbody>
            <tr>
                <td>
                    <?php if (!App()->getConfig('hide_update_key')): ?>
                        <?php echo $updateKey; ?>
                    <?php else: ?>
                        <em>XXXXXXXXXXX</em>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    if ($updateKeyInfos->result === false) {
                        eT($updateKeyInfos->error);
                    } else {
                        echo convertToGlobalSettingFormat($updateKeyInfos->validuntil);
                    }
                    ?>
                </td>
                <td>
                <?php
                if ($updateKeyInfos->result === false) {
                    echo '-';
                } else {
                    echo $updateKeyInfos->remaining_updates;
                };
                ?>
                </td>
                <td>
                    <span data-bs-toggle="tooltip" title="<?php eT("Delete key");?>" >
                        <a
                                data-post-url="<?= App()->createUrl('/admin/update/sa/deleteKey');?>"
                                class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal"
                                data-bs-target="#confirmation-modal">
                            <span class="ri-delete-bin-fill text-danger"></span>
                        </a>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
    <?php
    if ($updateKeyInfos->result === false) {
        App()->getController()->widget('ext.AlertWidget.AlertWidget', [
                'header' => gT(
                    'Key expired or invalid?'
                ),
                'text' => 'To enter a new key, please delete the current one first.',
                'type' => 'info',
                'htmlOptions' => ['class' => 'w-75 m-auto'],
            ]);
    }
    ?>
</div>
