<div class="row">
    <div class="col-md-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-md-9">
        <h2><?php echo $title; ?></h2>

        <?php echo CHtml::form(array("installer/welcome"), 'post'); ?>
            <fieldset>
            <legend><?php eT('Language selection'); ?></legend>
            <div class="row">
                <div class="span6">
                    <?php
                        echo CHtml::label(gT('Please select your preferred language:'), 'installerLang');
                    ?>
                </div>
                <div class="col-md-3">
                    <?php
                        echo CHtml::dropDownList('installerLang', 'en', $languages, array('id' => 'installerLang', 'class'=>'form-control', 'encode' => false));
                    ?>
                </div>
            </div>
            <?php
            echo CHtml::tag('p', array(), gT('Your preferred language will be used through out the installation process.'));
            ?>
            </fieldset>
            <div class="row navigator">
                <div class="col-md-3"></div>
                <div class="col-md-3"></div>
                <div class="col-md-3">
                    <input class='btn btn-default' type="submit" value="<?php eT('Start installation'); ?>" />
                </div>
            </div>
        <?php echo CHtml::endForm(); ?>
    </div>

</div>
