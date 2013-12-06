<div class="row">
    <div class="span3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>
    </div>
    <div class="span9">
        <h2><?php echo $title; ?></h2>

        <?php echo CHtml::form(array("installer/welcome"), 'post'); ?>
            <fieldset>
            <legend><?php $clang->eT('Language selection'); ?></legend>
            <div class="row">
                <div class="span6">
                    <?php
                        echo CHtml::label($clang->gT('Please select your preferred language:'), 'installerLang');
                    ?>
                </div>
                <div class="span3">
                    <?php
                        echo CHtml::dropDownList('installerLang', 'en', $languages, array('id' => 'installerLang', 'encode' => false));
                    ?>
                </div>
            </div>
            <?php
            echo CHtml::tag('p', array(), $clang->gT('Your preferred language will be used through out the installation process.'));
            ?>
            </fieldset>
            <div class="row navigator">
                <div class="span3"></div>
                <div class="span3"></div>
                <div class="span3">
                    <input class='btn' type="submit" value="<?php $clang->eT('Start installation'); ?>" />
                </div>
            </div>
        <?php echo CHtml::endForm(); ?>
    </div>
    
</div>
