<div class="col-lg-12 list-surveys" id="comfortUpdateGeneralWrap">
    <h3>
        <span id="comfortUpdateIcon" class="icon-shield text-success"></span>
        <?php eT('ComfortUpdate'); ?>
        <?php if(YII_DEBUG):?>
            <small>Server:<em class="text-warning"> <?php echo Yii::app()->getConfig("comfort_update_server_url");?></em></small>
        <?php endif;?>
    </h3>

    <?php if($updateKey): ?>

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
                             <?php if(!App()->getConfig('hide_update_key')):?>
                                 <?php echo $updateKey; ?>
                             <?php else:?>
                                 <em>XXXXXXXXXXX</em>
                             <?php endif;?>
                        </td>
                        <td>
                            <?php echo convertToGlobalSettingFormat($updateKeyInfos->validuntil); ?>
                        </td>
                        <td>
                            <?php echo $updateKeyInfos->remaining_updates; ?>
                        </td>
                        <td>
                            <a data-href="<?php echo App()->createUrl('/admin/update/sa/delete_key');?>" class="btn btn-default" data-toggle="modal" data-target="#confirmation-modal" data-tooltip="true" title="<?php eT("Delete");?>" >
                                <span class="text-danger fa fa-trash"></span>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    <?php else:?>
        <div class="jumbotron message-box ">
            <h2 class="text-success">Pwet</h2>
            <p class="lead">
            <?php eT('The LimeSurvey ComfortUpdate is a great feature to easily update to the latest version of LimeSurvey. To use it you will need an update key.');?>
            </p>
            <p>
                <?php
                    $aopen  = '<a href="https://www.limesurvey.org/get-your-free-comfortupdate-trial-key" target="_blank">';
                    $aclose = '</a>';
                ?>
                <?php echo sprintf(gT("You can get a free trial update key from %syour account on the limesurvey.org website%s."),$aopen, $aclose); ?>
                <?php
                    $aopen  = '<a href="https://www.limesurvey.org/sign-up">';
                    $aclose = '</a>';
                    ?><br>
                <?php echo sprintf(gT("If you don't have an account on limesurvey.org, please %sregister first%s."),$aopen, $aclose);?></p>

            </p>
        </div>
    <?php endif;?>
</div>
