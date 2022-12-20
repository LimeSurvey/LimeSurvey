<div class="container-fluid">
    <div class="col-12 list-surveys" id="comfortUpdateGeneralWrap">
        <h3>
            <span id="comfortUpdateIcon" class="ri-shield-check-fill text-success"></span>
            <?php eT('ComfortUpdate'); ?>
            <?php if (YII_DEBUG): ?>
                <small>Server:<em class="text-warning"> <?php echo Yii::app()->getConfig("comfort_update_server_url"); ?></em></small>
            <?php endif; ?>
        </h3>

        <?php if ($updateKey): ?>

            <div class="tab-pane  in active" style="width: 75%; margin: auto;">
                <table class="items table">
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
                                <a data-post-url="<?php echo App()->createUrl('/admin/update/sa/deleteKey');?>" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#confirmation-modal">
                                    <span class="ri-delete-bin-fill text-danger"></span>
                                </a>
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
        <?php else: ?>
            <div class="jumbotron message-box ">
                <h2 class="text-success">Pwet</h2>
                <p class="lead">
                    <?php eT('The LimeSurvey ComfortUpdate is a great feature to easily update to the latest version of LimeSurvey. To use it you will need an update key.'); ?>
                </p>
                <p>
                    <?php
                    $aopen = '<a href="https://account.limesurvey.org/get-your-free-comfortupdate-trial-key" target="_blank">';
                    $aclose = '</a>';
                    ?>
                    <?php echo sprintf(gT("You can get a free trial update key from %syour account on the limesurvey.org website%s."), $aopen, $aclose); ?>
                    <?php
                    $aopen = '<a href="https://account.limesurvey.org/sign-up">';
                    $aclose = '</a>';
                    ?><br>
                    <?php echo sprintf(gT("If you don't have an account on limesurvey.org, please %sregister first%s."), $aopen, $aclose); ?></p>

                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
