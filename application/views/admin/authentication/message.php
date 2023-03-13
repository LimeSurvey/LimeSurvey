<!--@TODO is this view even used?-->
<div class="container-fluid welcome">
    <div class="row text-center">
        <div class="col-xxl-3 offset-xl-4 col-md-6 offset-md-3">
            <div class="card card-primary login-panel" id="panel-1">
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="card-body">
                        <div class="d-flex justify-content-center">
                            <img alt='logo' id="profile-img" class="profile-img-card img-fluid mx-auto" src="<?php echo LOGO_URL; ?>"/>
                        </div>
                    </div>

                    <!-- Action Name -->
                    <div class="row login-title login-content">
                        <div class="col-12">
                            <h3><?php
                                eT('Recover your password'); ?></h3>
                        </div>
                    </div>

                    <!-- Form -->
                    <?php
                    echo CHtml::form(
                        ["admin/authentication/sa/forgotpassword"],
                        'post',
                        ['id' => 'forgotpassword', 'name' => 'forgotpassword']
                    ); ?>
                    <div class="row login-content login-content-form">
                        <div class="col-12">
                            <?php
                            $this->widget('ext.AlertWidget.AlertWidget', [
                                'text' => $message,
                                'type' => 'info',
                            ]);
                            ?>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="row login-submit login-content">
                        <div class="col-12">
                            <a href='<?php
                            echo $this->createUrl("/admin/authentication/sa/login"); ?>'><?php
                                eT('Continue'); ?></a>
                        </div>
                    </div>
                    <?php
                    echo CHtml::endForm(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
