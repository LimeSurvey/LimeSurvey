<?php
/** @var InstallerController $this */
/** @var InstallerConfigForm $model */

?>
<div class="row">
    <div class="col-lg-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-lg-9">
        <h2><?php echo $title; ?></h2>
            <legend><?php eT('Database creation'); ?></legend>
                <?php if (!$model->dbExists):?>
                    <?php  $this->renderPartial('/installer/nodatabase_view', ['model'=>$model]);?>
                <?php endif;?>
                <?php if (isset($adminoutputText)) echo $adminoutputText; ?>

            <div class="row">
                <div class="col-lg-4" >
                    <input class="btn btn-outline-secondary" type="button" value="<?php eT('Previous'); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/database"); ?>', '_top')" />
                </div>
                <div class="col-lg-4" style="text-align: center;">
                </div>
                <div class="col-lg-4" style="text-align: right;">
                    <?php
                        if (isset($next))
                        {
                            echo CHtml::form(array($next['action']), 'post');
                            echo CHtml::submitButton($next['label'], array(
                                'name' => $next['name'],
                                'class' => 'btn btn-outline-secondary'
                            ));
                            echo CHtml::endForm();
                        }
                    ?>

                </div>
