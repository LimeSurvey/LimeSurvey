<?php $this->render("/installer/header_view", compact('progressValue', 'clang')); ?>
<?php echo CHtml::beginForm($this->createUrl('installer/database')); ?>

<div class="container_6">

    <?php $this->render('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>

    <div class="grid_4 table">

        <p class="maintitle"><?php echo $title; ?></p>

        <div style="-moz-border-radius:15px; border-radius:15px; " >
            <p><?php echo $descp; ?></p>
            <hr />
            <div style="color:red; font-size:12px;">
                <?php echo CHtml::errorSummary($model, null, null, array('class' => 'errors')); ?>
            </div>
            <br /><?php $clang->eT("Note: All fields marked with (*) are required."); ?>
            <br />


            <fieldset class="content-table">
                <legend class="content-table-heading"><?php $clang->eT("Database configuration"); ?></legend>
                <table style="width: 672px; font-size:14px;">
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
                            <div class="description-field"><?php $clang->eT('Set this to the IP/net location of your database server. In most cases "localhost" will work.'); ?> </div>
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

        </div>
    </div>
</div>

<div class="container_6">
    <div class="grid_2">&nbsp;</div>
    <div class="grid_4 demo">
        <br/>
        <table style="width: 694px; background: #ffffff;">
            <tbody>
                <tr>
                    <td align="left" style="width: 33%;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="<?php $clang->eT("Previous"); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/precheck"); ?>', '_top')" /></td>
                    <td align="center" style="width: 34%;"></td>
                    <td align="right" style="width: 33%;"><?php echo CHtml::submitButton($clang->gT("Next"), array('class' => 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php echo CHtml::endForm(); ?>

<?php $this->render("/installer/footer_view"); ?>
