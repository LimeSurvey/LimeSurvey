<div class="row">
    <div class="col-md-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-md-6">
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
                    <div class="col-md-6">
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

    <div class="col-md-3">
        <div class="thumbnail" style="padding: 1em;">
            <img style="width: 50%;" src="<?php echo Yii::app()->baseUrl; ?>/installer/images/cloud-logo.svg" alt="LimeSurvey Cloud Logo">
            <div class="caption">
                <h3><?= gT("LimeSurvey Cloud"); ?></h3>
                <p>
                    <?= sprintf(gT("Subscribe to our %sLimeSurvey Cloud%s hosting and get:"), "<a target='_blank' href='https://www.limesurvey.org/'>", "</a>"); ?>
                    <ul>
                        <li><?= gT("Great performance"); ?></li>
                        <li><?= gT("Automatic updates"); ?></li>
                        <li><?= gT("GDPR-compliance"); ?></li>
                        <li><?= gT("Technical support"); ?></li>
                    </ul>
                </p>
                <p class="text-center">
                    <a href="https://www.limesurvey.org/pricing/" class="btn btn-primary btn-block" role="button" target="_blank">
                        <?= gT("Try now"); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
