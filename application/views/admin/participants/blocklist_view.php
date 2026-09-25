<?php
/**
 * @var AdminController $this
 * @var string $blocklistallsurveys
 * @var string $blocklistnewsurveys
 * @var string $blockaddingtosurveys
 * @var string $hideblocklisted
 * @var string $deleteblocklisted
 * @var string $allowunblocklist
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('participantsBlocklistControl');

?>
<script src="<?php echo Yii::app()->getConfig('adminscripts') . "userControl.js" ?>" type="text/javascript"></script>
<div id="pjax-content">
    <div class="row">
        <div class="col-12 list-surveys">
            <div id='usercontrol-1'>
                <?php
                if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                    $attribute = ['class' => 'col-lg-6 offset-lg-1 '];
                    echo CHtml::beginForm($this->createUrl('/admin/participants/sa/storeBlocklistValues'), 'post', $attribute);
                    $options = ['Y' => gT('Yes', 'unescaped'), 'N' => gT('No', 'unescaped')];
                    ?>
                    <div class="row ls-space margin top-10 bottom-10">
                        <div class="mb-3">
                            <label class='form-label col-md-8'>
                                <?php eT('Blocklist all current surveys for participant once the global field is set'); ?>
                            </label>
                            <div class='col-md-3'>
                                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                    'name'          => 'blocklistallsurveys',
                                    'ariaLabel'     => gT('Blocklist all current surveys for participant once the global field is set'),
                                    'checkedOption' => $blocklistallsurveys === 'Y' ? '1' : 0,
                                    'selectOptions' => [
                                        '1' => gT('Yes'),
                                        '0' => gT('No'),
                                    ],
                                ]); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row ls-space margin top-10 bottom-10">
                        <div class="mb-3">
                            <label class='form-label col-md-8'>
                                <?php eT('Blocklist all newly created surveys for participant once the global field is set'); ?>
                            </label>
                            <div class='col-md-3'>
                                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                    'name'          => 'blocklistnewsurveys',
                                    'ariaLabel'     => gT('Blocklist all newly created surveys for participant once the global field is set'),
                                    'checkedOption' => $blocklistnewsurveys === 'Y' ? '1' : 0,
                                    'selectOptions' => [
                                        '1' => gT('Yes'),
                                        '0' => gT('No'),
                                    ],
                                ]); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row ls-space margin top-10 bottom-10">
                        <div class="mb-3">
                            <label class='form-label col-md-8'>
                                <?php eT('Prevent blocklisted participants from being added to a survey'); ?>
                            </label>
                            <div class='col-md-3'>
                                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                    'name'          => 'blockaddingtosurveys',
                                    'ariaLabel'     => gT('Prevent blocklisted participants from being added to a survey'),
                                    'checkedOption' => $blockaddingtosurveys === 'Y' ? '1' : 0,
                                    'selectOptions' => [
                                        '1' => gT('Yes'),
                                        '0' => gT('No'),
                                    ],
                                ]); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row ls-space margin top-10 bottom-10">
                        <div class="mb-3">
                            <label class='form-label col-md-8'>
                                <?php eT('Hide blocklisted participants'); ?>
                            </label>
                            <div class='col-md-3'>
                                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                    'name'          => 'hideblocklisted',
                                    'ariaLabel'     => gT('Hide blocklisted participants'),
                                    'checkedOption' => $hideblocklisted === 'Y' ? '1' : 0,
                                    'selectOptions' => [
                                        '1' => gT('Yes'),
                                        '0' => gT('No'),
                                    ],
                                ]); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row ls-space margin top-10 bottom-10">
                        <div class="mb-3">
                            <label class='form-label col-md-8'>
                                <?php eT('Delete globally blocklisted participant from the database'); ?>
                            </label>
                            <div class='col-md-3'>
                                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                    'name'          => 'deleteblocklisted',
                                    'ariaLabel'     => gT('Delete globally blocklisted participant from the database'),
                                    'checkedOption' => $deleteblocklisted === 'Y' ? '1' : 0,
                                    'selectOptions' => [
                                        '1' => gT('Yes'),
                                        '0' => gT('No'),
                                    ],
                                ]); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row ls-space margin top-10 bottom-10">
                        <div class="mb-3">
                            <label class='form-label col-md-8'>
                                <?php eT('Allow participant to remove himself/herself from blocklist'); ?>
                            </label>
                            <div class='col-md-3'>
                                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                    'name'          => 'allowunblocklist',
                                    'ariaLabel'     => gT('Allow participant to remove himself/herself from blocklist'),
                                    'checkedOption' => $allowunblocklist === 'Y' ? '1' : 0,
                                    'selectOptions' => [
                                        '1' => gT('Yes'),
                                        '0' => gT('No'),
                                    ],
                                ]); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row ls-space margin top-25 bottom-10">
                        <div class="mb-3">
                            <div class='col-md-8'>
                            </div>
                            <div class='col-md-3'>
                                <?php echo CHtml::submitButton('submit', ['value' => gT('Save'), 'class' => 'btn btn-primary col-12']); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    echo CHtml::endForm();
                } else {
                    echo "<div class='messagebox ui-corner-all'>" . gT("We are sorry but you don't have permissions to do this.") . "</div>";
                }
                ?>
            </div>
        </div>
    </div>
    <span id="locator" data-location="blocklist">&nbsp;</span>
</div>


