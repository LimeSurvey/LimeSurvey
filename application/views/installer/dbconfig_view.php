<?php
/**
 * @var InstallerConfigForm $model
 * @var string $title
 * @var string $descp
 */

Yii::app()->clientScript->registerScript('dbType', "
$( document ).ready(function() {
    checkDbType();
    checkDbEngine();
});

$('#InstallerConfigForm_dbtype').change(function(){
    checkDbType();
});

$('#InstallerConfigForm_dbengine').change(function(){
    checkDbEngine();
});

function checkDbType(){
    if($('#InstallerConfigForm_dbtype').val() == '".InstallerConfigForm::DB_TYPE_MYSQL."') {
        $('#InstallerConfigForm_dbengine_row').show();
    } else if($('#InstallerConfigForm_dbtype').val() == '".InstallerConfigForm::DB_TYPE_MYSQLI."') {
        $('#InstallerConfigForm_dbengine_row').show();
    } else {
        $('#InstallerConfigForm_dbengine_row').hide();
    }
}

function checkDbEngine(){
    if($('#InstallerConfigForm_dbengine').val() == '".InstallerConfigForm::ENGINE_TYPE_INNODB."') {
        $('#innodb-warning').addClass('d-flex');
        $('#innodb-warning').removeClass('d-none');
    } else {
        $('#innodb-warning').addClass('d-none');
        $('#innodb-warning').removeClass('d-flex');
    }
}

");

?>

<div class="row">
    <div class="col-lg-4">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-lg-8">
        <?= CHtml::beginForm($this->createUrl('installer/database'), 'post', array('class' => '')); ?>
        <h2><?= $title; ?></h2>
        <p><?= $descp; ?></p>
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', ['errorSummaryModel' => $model]);
        ?>
        <hr/>
        <p><?php eT("Note: All fields marked with (*) are required."); ?></p>
        <legend><?php eT("Database configuration"); ?></legend>

        <div id="InstallerConfigForm_dbtype_row" class="mb-3">
            <?= CHtml::activeLabelEx($model, 'dbtype'); ?>
            <?= CHtml::activeDropDownList($model, 'dbtype', $model->supportedDbTypes, ['required' => 'required', 'class'=>'form-control', 'autofocus' => 'autofocus']); ?>
            <div class="help-block"><?= $model->attributeHints()['dbtype'] ?></div>
        </div>

        <div id="InstallerConfigForm_dbengine_row" class="mb-3">
            <?= CHtml::activeLabelEx($model, 'dbengine'); ?>
            <?= CHtml::activeDropDownList($model, 'dbengine', $model->dbEngines, array('prompt'=>gT("Select"), 'autocomplete'=>'off', 'class' => 'form-control')); ?>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => gT('Warning! Using InnoDB instead of MyISAM will reduce the possible maximum number of questions in your surveys. Please read more about MyISAM vs InnoDB table column limitations in our manual before selecting InnoDB.'),
                'type' => 'warning',
                'htmlOptions' => ['id' => 'innodb-warning'],
            ]);
            ?>
        </div>

        <div id="InstallerConfigForm_dblocation_row" class="mb-3">
            <?= CHtml::activeLabelEx($model, 'dblocation'); ?>
            <?= CHtml::activeTextField($model, 'dblocation',['class' => 'form-control']); ?>
            <div class="help-block"><?= $model->attributeHints()['dblocation'] ?></div>
        </div>

        <div id="InstallerConfigForm_dbuser_row" class="mb-3">
            <?= CHtml::activeLabelEx($model, 'dbuser'); ?>
            <?= CHtml::activeTextField($model, 'dbuser',['class' => 'form-control']); ?>
            <div class="help-block"><?= $model->attributeHints()['dbuser'] ?></div>
        </div>

        <div id="InstallerConfigForm_dbpwd_row" class="mb-3">
            <?= CHtml::activeLabelEx($model, 'dbpwd'); ?>
            <?= CHtml::activePasswordField($model, 'dbpwd',['class' => 'form-control']); ?>
            <div class="help-block"><?= $model->attributeHints()['dbpwd'] ?></div>
        </div>

        <div id="InstallerConfigForm_dbname_row" class="mb-3">
            <?= CHtml::activeLabelEx($model, 'dbname'); ?>
            <?= CHtml::activeTextField($model, 'dbname',['class' => 'form-control']); ?>
            <div class="help-block"><?= $model->attributeHints()['dbname'] ?></div>
        </div>

        <div id="InstallerConfigForm_dbname_row" class="mb-3">
            <?= CHtml::activeLabelEx($model, 'dbprefix'); ?>
            <?= CHtml::activeTextField($model, 'dbprefix',['class' => 'form-control']); ?>
            <div class="help-block"><?= $model->attributeHints()['dbprefix'] ?></div>
        </div>

        <div class="row">
            <div class="col-lg-4" >
                <input id="ls-previous" class="btn btn-outline-secondary" type="button" value="<?php eT("Previous"); ?>" onclick="window.open('<?php echo $this->createUrl("installer/precheck"); ?>', '_top')" />
            </div>
            <div class="col-lg-4" style="text-align: center;">
            </div>
            <div class="col-lg-4" style="text-align: right;">
                <?php echo CHtml::submitButton(gT("Next", "unescaped"), array("class" => "btn btn-outline-secondary", "id" => "ls-next")); ?>
            </div>
        </div>
        <?php echo CHtml::endForm(); ?>

    </div>
</div>


