<div class="row">
    <div class="col-md-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-md-9">
        <h2><?php echo $title; ?></h2>

        <?php echo CHtml::form(array("installer/welcome"), 'post', array('class' => 'form-vertical')); ?>
            <legend><?php eT('Language selection'); ?></legend>
            <div class="row">
                <div class='form-group'>
                    <div class="span6 col-md-12">
                        <?php
                            echo CHtml::label(gT('Please select your preferred language:'), 'installerLang', array('class' => 'control-label'));
                        ?>
                        <br/><br/>
                    </div>
                    <div class="col-md-3">
                        <?php
                            echo CHtml::dropDownList('installerLang', 'en', $languages, array('id' => 'installerLang', 'class'=>'form-control', 'encode' => false));
                        ?>
                        <br/><br/>
                    </div>
                </div>
            </div>
            <?php
            echo CHtml::tag('p', array(), gT('Your preferred language will be used through out the installation process.'));
            ?>
            <div class="row navigator">
                <div class="col-md-8"></div>
                <div class="col-md-4">
                    <input id="ls-start-installation" class='btn btn-default' type="submit" value="<?php eT('Start installation'); ?>" />
                </div>
            </div>
        <?php echo CHtml::endForm(); ?>
    </div>

</div>
