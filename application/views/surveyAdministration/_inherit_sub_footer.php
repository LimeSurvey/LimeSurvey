<div class="row sub_footer">
    <div class="col-12 mt-5 mb-3">
        <div class="sub_footer_border"></div>
    </div>

    <div class="col-lg-6 col-12 d-flex ls-footer-label">
        <i class="ri-information-line me-2"></i>
        <p class="me-1">ᴵ</p>
        <div>
            <?= sprintf(
                gT("Inherited settings come from your %sglobal settings%s or %ssurvey group%s. Click %shere%s for more information about inherited settings."),
                '<a class="ls-link" href="' . $this->createUrl('/admin/globalsettings') . '" target="_blank">',
                '</a>',
                '<a class="ls-link" href="' . $this->createUrl('/surveyAdministration/listsurveys') . '" target="_blank">',
                '</a>',
                '<a class="ls-link" href="https://www.limesurvey.org/manual/Survey_settings_inheritance" target="_blank">',
                '</a>'
            ); ?>
        </div>
    </div>
</div>
