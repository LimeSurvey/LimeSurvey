<div class="row">
    <div class="col-lg-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-md-6">
        <h2><?php echo $title; ?></h2>

        <?php echo CHtml::form(array("installer/welcome"), 'post', array('class' => 'form-vertical')); ?>
            <legend><?php eT('Language selection'); ?></legend>
            <div class="row">
                <div class='mb-3'>
                    <div class="span6 col-12">
                        <?php
                            echo CHtml::label(gT('Please select your preferred language:'), 'installerLang', array('class' => 'form-label'));
                        ?>
                        <br/><br/>
                    </div>
                    <div class="col-md-6">
                        <?php
                            echo CHtml::dropDownList('installerLang', 'en', $languages, array('id' => 'installerLang', 'class'=>'form-select', 'encode' => false));
                        ?>
                        <br/><br/>
                    </div>
                </div>
            </div>
            <?php
            echo CHtml::tag('p', array(), gT('Your preferred language will be used through out the installation process.'));
            ?>
            <div class="row navigator">
                <div class="col-lg-8"></div>
                <div class="col-lg-4">
                    <input id="ls-start-installation" class='btn btn-outline-secondary' role="button" type="submit" value="<?php eT('Start installation'); ?>" />
                </div>
            </div>
        <?php echo CHtml::endForm(); ?>
    </div>

    <div class="col-md-3">
        <div class="thumbnail" style="padding: 1em;">
            <img class="rounded mx-auto d-block m-3" style="width: 50%;" src="<?php echo Yii::app()->baseUrl; ?>/installer/images/cloud-logo.svg" alt="GititSurvey Cloud Logo">
            <div class="caption">
                <h3>GititSurvey Cloud</h3>
                <p>
                    <?= sprintf(gT("Subscribe to our %sLimeSurvey Cloud%s hosting and get:"), "<a target='_blank' href='https://www.gitit-tech.com/'>", "</a>"); ?>
                    <ul>
                        <li><?= gT("Great performance"); ?></li>
                        <li><?= gT("Automatic updates"); ?></li>
                        <li><?= gT("GDPR-compliance"); ?></li>
                        <li><?= gT("Technical support"); ?></li>
                    </ul>
                </p>
                <p class="text-center d-grid gap-2">
                    <a href="https://www.gitit-tech.com/pricing/" class="btn btn-primary btn-block" role="button" target="_blank">
                        <?= gT("Try now"); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
