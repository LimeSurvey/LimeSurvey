<div class="row sub_footer">
    <div class="col-12 mt-5 mb-3">
        <div class="sub_footer_border" ></div>
    </div>

    <div class="col-lg-6 col-12 d-flex ls-footer-label">
        <i class="ri-information-line me-2"></i>
        <p class="me-1">ᴵ</p>
        <div>
            <?php
            eT(" Inherited settings come from your")
            ?>
            <a href="<?php echo $this->createUrl("/admin/globalsettings"); ?>" target="_blank"> <?php eT("global settings") ?> </a> <?php eT("or") ?>
            <a href="<?php echo $this->createUrl("/surveyAdministration/listsurveys"); ?>"  target="_blank"><?php eT("survey group") ?></a>. <br />
            <?php eT("Click") ?> <a href="https://manual.limesurvey.org/Survey_settings_inheritance"  target="_blank" > <?php eT("here") ?></a> <?php eT("for more information about inherited settings.") ?>
        </div>
    </div>