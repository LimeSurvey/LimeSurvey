<div class="row">
    <div class="col-md-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-md-9">
        <h2><?php echo $title; ?></h2>
        <p><?php echo $descp; ?></p>
            <h3><?php eT('Database creation'); ?></h3>
                <?php if (isset($adminoutputText)) echo $adminoutputText; ?>

            <div class="row">
                <div class="col-md-4" >
                    <input class="btn btn-default" type="button" value="<?php eT('Previous'); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/database"); ?>', '_top')" />
                </div>
                <div class="col-md-4" style="text-align: center;">
                </div>
                <div class="col-md-4" style="text-align: right;">
                    <?php
                        if (isset($next))
                        {
                            echo CHtml::form(array($next['action']), 'post');
                            echo CHtml::submitButton($next['label'], array(
                                'name' => $next['name'],
                                'class' => 'btn btn-default'
                            ));
                            echo CHtml::endForm();
                        }
                    ?>

                </div>
