<div class="container-fluid">
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

        <?php if ($updateKey): ?>
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
                        <span data-bs-toggle="tooltip" title="<?php eT("Delete");?>" >
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
        <?php else: ?>
            <?php
            $aAccountOpen = '<a class="fw-bolder" href="https://account.limesurvey.org/get-your-free-comfortupdate-trial-key" target="_blank">';
            $aClose = '</a>';
            $aSignUpOpen = '<a class="fw-bolder" href="https://account.limesurvey.org/sign-up">';
            $message = sprintf(
                gT("You can get a free trial update key from %syour account on the limesurvey.org website%s."),
                $aAccountOpen,
                $aClose
            ) .
                '<br>' . sprintf(
                    gT("If you don't have an account on limesurvey.org, please %sregister first%s."),
                    $aSignUpOpen,
                    $aClose
                );
            App()->getController()->widget('ext.AlertWidget.AlertWidget', [
                'header' => gT(
                    'The LimeSurvey ComfortUpdate is a great feature to easily update to the latest version of LimeSurvey. To use it you will need an update key.'
                ),
                'text' => $message,
                'type' => 'info',
                'htmlOptions' => ['class' => 'w-75 m-auto'],
            ]);
            ?>
        <?php endif; ?>
    </div>
</div>
