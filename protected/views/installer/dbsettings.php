<p><?php echo $descp; ?></p>
    <h3><?php eT('Database creation'); ?></h3>
        <?php if (isset($adminoutputText)) echo $adminoutputText; ?>

    <div class="row">
        <div class="span3" >
            <input class="btn" type="button" value="<?php eT('Previous'); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/database"); ?>', '_top')" />
        </div>
        <div class="span3" style="text-align: center;">
        </div>
        <div class="span3" style="text-align: right;">
            <?php
                if (isset($next))
                {
                    echo CHtml::form(array($next['action']), 'post');
                    echo CHtml::submitButton(gT($next['label']), array(
                        'name' => $next['name'],
                        'class' => 'btn'
                    ));
                    echo CHtml::endForm();
                }
            ?>

        </div>
    </div>
