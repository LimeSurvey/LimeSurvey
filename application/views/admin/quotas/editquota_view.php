<?php
/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var string $lang */
/* @var Quota $oQuota */
/* @var CActiveForm $form */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-lg-12 content-right">
            <h3>
                <?php eT("Edit quota");?>
            </h3>

            <?php $form = $this->beginWidget('CActiveForm', array(
                'id'=>'editquota',
                //'action'=>array("admin/quotas/sa/modifyquota/surveyid/{$oSurvey->primaryKey}"),
                'enableClientValidation'=>true,
                'clientOptions'=>array(
                    'validateOnSubmit'=>true,
                    'afterValidate'=>'js:yiiFix.ajaxSubmit.afterValidate'
                ),
            )); ?>
            <?php echo $form->errorSummary($oQuota); ?>

            <?php echo $form->hiddenField($oQuota,'id'); ?>
            <div class="form-group">
                <?php echo $form->labelEx($oQuota,'name',array('class'=>'control-label col-sm-3')); ?>
                <div class='col-sm-9'>
                    <?php echo $form->textField($oQuota,'name',array('class'=>'form-control')); ?>
                    <?php echo $form->error($oQuota,'name'); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($oQuota,'qlimit',array('class'=>'control-label col-sm-3')); ?>
                <div class='col-sm-9'>
                    <?php echo $form->textField($oQuota,'qlimit',array('class'=>'form-control')); ?>
                    <?php echo $form->error($oQuota,'qlmit'); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($oQuota,'action',array('class'=>'control-label col-sm-3')); ?>
                <div class='col-sm-9'>
                    <?php echo $form->dropDownList($oQuota,'action',
                        array(
                            1 =>gT("Terminate survey"),
                            2 =>gT("Terminate survey with warning"),
                        ),
                        array('class'=>'form-control')); ?>
                    <?php echo $form->error($oQuota,'action'); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($oQuota,'autoload_url',array('class'=>'control-label col-sm-3')); ?>
                <div class='col-sm-9'>
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'model' => $oQuota,
                        'attribute' => 'autoload_url',
                        'onLabel'=>gT('Yes'),
                        'offLabel' => gT('No')));
                    ?>
                    <?php echo $form->error($oQuota,'autoload_url'); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($oQuota,'active',array('class'=>'control-label col-sm-3')); ?>
                <div class='col-sm-9'>
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'model' => $oQuota,
                        'attribute' => 'active',
                        'onLabel'=>gT('Yes'),
                        'offLabel' => gT('No')));
                    ?>
                    <?php echo $form->error($oQuota,'active'); ?>
                </div>
            </div>

                <!-- Language tabs -->
                <ul class="nav nav-tabs">
                    <?php foreach ($langs as $lang): ?>
                        <li role="presentation" <?php if ($lang==$baselang){echo 'class="active"';}?>>
                            <a data-toggle="tab" href="#edittxtele<?php echo $lang ?>">
                                <?php echo getLanguageNameFromCode($lang,false); ?>
                                <?php if ($lang==$baselang) {echo '('.gT("Base language").')';} ;?>
                            </a>
                        </li>
                    <?php endforeach?>
                </ul>
                <div class='tab-content'>
                <?php foreach ($aTabContents as $i => $sTabContent)
                {
                    echo CHtml::tag(
                        'div',
                        array(
                            'id' => 'edittxtele' . $i,
                            'class' => 'tab-pane fade in' . ($i == $baselang ? ' active ' : ''),
                        ),
                        $sTabContent
                    );
                }?>
                </div>

                <input type="submit" name="submit" class="hidden" />
                <input type="hidden" name="sid" value="<?php echo $surveyid;?>" />
                <input type="hidden" name="action" value="quotas" />
                <input type="hidden" name="subaction" value="modifyquota" />
                <input type="hidden" name="quota_id" value="<?php echo $oQuota['id'];?>" />

            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>
