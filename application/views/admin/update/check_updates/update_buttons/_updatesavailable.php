<?php
/**
 * This view display the buttons "use ComfortUpdate".
 * It is injected by the javascript inside the li#udapteButtonsContainer, in the _checkButton view.
 * @var obj updateInfos the update information provided by the update server
 * @var obj $clang : the translate object, now moved to global function TODO : remove it
 */
?>
<?php
    // First we check if the server provided a specific HTML message
    if(isset($updateInfos->html))
    {
        if($updateInfos->html != "")
            echo $updateInfos->html;
        // And we unset this html message for the loop on update versions don't crush on it
        unset($updateInfos->html);
    }
?>

<?php if(isset($updateInfos->alert)): // First we check if the server provided a specific alert message ?>
    <?php if($updateInfos->alert != ""):?>
        <!-- Alert from server -->
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => $updateInfos->alert,
            'type' => 'warning',
        ]);
        ?>
    <?php endif; ?>
<?php endif; ?>


<div>
    <strong id="ls-updates"><?php echo gT('The following LimeSurvey updates are available:');?></strong>
</div>
<br/>
<br/>


<table aria-describedby="ls-updates" class="items table w-75 m-auto">
    <!-- header -->
    <thead>
        <tr>
            <th>
                <?php eT('LimeSurvey version'); ?>
            </th>
            <th>
                <?php eT('Branch'); ?>
            </th>
            <th>
                <?php eT('Update type'); ?>
            </th>
            <th>

            </th>
        </tr>
    </thead>

    <!-- rows for each version -->
    <?php foreach ($updateInfos as $aUpdateVersion):?>
        <?php $aUpdateVersion = (array) $aUpdateVersion;?>
        <tr>

            <!-- update version -->
            <td>
                 <?php
                     // display infos about the update. e.g : "2.05+ (150508) (stable)"
                     echo $aUpdateVersion['versionnumber'];?> (<?php echo $aUpdateVersion['build'];?>)

                <?php if(isset($aUpdateVersion['html'])):?>
                    <?php if($aUpdateVersion['html']!=''):?>
                        <?php echo $aUpdateVersion['html'];?>
                    <?php endif;?>
                <?php endif;?>
            </td>

            <!-- stable / unstable -->
            <?php if (!in_array($aUpdateVersion['branch'], ['master','5.x','3.x-LTS'])):?>
                <td class="text-danger">
                    <?php  eT('unstable'); ?>
                </td>
            <?php else: ?>
                <td>
                    <?php eT('stable');?>
                </td>
            <?php endif;?>

            <!-- security / regular -->
            <?php if($aUpdateVersion['security_update']):?>
            <td class="text-danger">
                    <?php eT("Security update");?>
            </td>
            <?php else: ?>
            <td>
                <?php eT("Regular update");?>
            </td>
            <?php endif; ?>

            <!-- button -->
            <td class="text-end">
                <!-- The form launching an update process. First step is the welcome message. The form is not submitted, but catch by the javascript inserted in the end of this file -->
                <?php echo CHtml::beginForm(App()->createUrl('admin/update/sa/getwelcome'), 'post', array('class'=>'launchUpdateForm')); ?>
                    <?php echo CHtml::hiddenField('destinationBuild' , $aUpdateVersion['build']); ?>

                    <!-- the button launching the update -->
                    <button type="submit" class="btn btn-sm btn-outline-secondary ajax_button launch_update">
                        <span class="ri-shield-check-fill text-success"></span>
                        <?php eT("Use ComfortUpdate");?>
                    </button>
                <?php
                $this->widget(
                    'ext.ButtonWidget.ButtonWidget',
                    [
                        'name' => 'download-version',
                        'id' => 'download-version',
                        'text' => gT('Download'),
                        'icon' => 'ri-download-fill',
                        'link' => 'https://community.limesurvey.org/downloads/',
                        'htmlOptions' => [
                            'class' => 'ajax_button btn btn-sm btn-outline-secondary',
                            'target' => '_blank',
                        ],
                    ]
                );
                ?>
                 <?php echo CHtml::endForm(); ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- this javascript code manage the step changing. It will catch the form submission, then load the comfortupdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('.launchUpdateForm').comfortUpdateNextStep({'step': 0});
</script>
