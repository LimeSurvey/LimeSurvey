<div class="row">
    <div class="span3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>
    </div>
    <div class="span9">
        <?php echo CHtml::beginForm($this->createUrl('installer/database')); ?>
        <h2><?php echo $title; ?></h2>
        <p><?php echo $descp; ?></p>
        <div style="color:red; font-size:12px;">
            <?php echo CHtml::errorSummary($model, null, null, array('class' => 'errors')); ?>
        </div>
        <?php $clang->eT("Note: All fields marked with (*) are required."); ?>
        <fieldset>
            <legend><?php $clang->eT("Database configuration"); ?></legend>
            <table style="width: 100%; font-size:14px;">
                <tr>
                    <td style="width: 428px;">
                        <b><?php echo CHtml::activeLabelEx($model, 'dbtype', array('label' => $clang->gT("Database type"))); ?></b><br />
                        <div class="description-field"><?php $clang->eT("The type of your database management system"); ?> </div>
                    </td>
                    <td style="width: 224px;" align="right">
                        <?php echo CHtml::activeDropDownList($model, 'dbtype', $model->supported_db_types, array('required' => 'required', 'style' => 'width: 155px', 'autofocus' => 'autofocus')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 428px;">
                        <b><?php echo CHtml::activeLabelEx($model, 'dblocation', array('label' => $clang->gT("Database location"))); ?></b><br />
                        <div class="description-field"><?php $clang->eT('Set this to the IP/net location of your database server. In most cases "localhost" will work. You can force Unix socket with complete socket path.'); ?> </div>
                    </td>
                    <td style="width: 224px;" align="right"><?php echo CHtml::activeTextField($model, 'dblocation', array('required' => 'required')) ?></td>
                </tr>
                <tr>
                    <td style="width: 428px;">
                        <b><?php echo CHtml::activeLabelEx($model, 'dbuser', array('label' => $clang->gT("Database user"))); ?></b><br />
                        <div class="description-field"><?php $clang->eT('Your database server user name. In most cases "root" will work.'); ?></div>
                    </td>
                    <td style="width: 224px;" align="right"><?php echo CHtml::activeTextField($model, 'dbuser', array('required' => 'required')) ?></td>
                </tr>
                <tr>
                    <td style="width: 428px;">
                        <b><?php echo CHtml::activeLabelEx($model, 'dbpwd', array('label' => $clang->gT("Database password"))); ?></b><br />
                        <div class="description-field"><?php $clang->eT("Your database server password."); ?></div>
                    </td>
                    <td style="width: 224px;" align="right"><?php echo CHtml::activePasswordField($model, 'dbpwd') ?></td>
                </tr>
                <tr>
                    <td style="width: 428px;">
                        <b><?php echo CHtml::activeLabelEx($model, 'dbname', array('label' => $clang->gT("Database name"))); ?></b><br />
                        <div class="description-field"><?php $clang->eT("If the database does not yet exist it will be created (make sure your database user has the necessary permissions). In contrast, if there are existing LimeSurvey tables in that database they will be upgraded automatically after installation."); ?></div>
                    </td>
                    <td style="width: 224px;" align="right"><?php echo CHtml::activeTextField($model, 'dbname', array('required' => 'required')) ?></td>
                </tr>
                <tr>
                    <td style="width: 428px;">
                        <b><?php echo CHtml::activeLabelEx($model, 'dbprefix', array('label' => $clang->gT("Table prefix"))); ?></b><br />
                        <div class="description-field"><?php $clang->eT('If your database is shared, recommended prefix is "lime_" else you can leave this setting blank.'); ?></div>
                    </td>
                    <td style="width: 224px;" align="right"><?php echo CHtml::activeTextField($model, 'dbprefix', array('value' => 'lime_')) ?></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </fieldset>
        <div class="row">
            <div class="span3" >
                <input class="btn" type="button" value="<?php $clang->eT('Previous'); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/precheck"); ?>', '_top')" />
            </div>
            <div class="span3" style="text-align: center;">
            </div>
            <div class="span3" style="text-align: right;">
                <?php echo CHtml::submitButton($clang->gT("Next"), array('class' => 'btn')); ?>
            </div>
        </div>
        <?php echo CHtml::endForm(); ?>

    </div>
</div>


