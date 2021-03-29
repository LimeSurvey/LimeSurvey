
<li class="dropdown larger-dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <span class="fa fa-question-circle" ></span>
        <?php eT('Help');?>
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu larger-dropdown" id="help-dropdown">
        <?php $this->renderPartial( "/admin/super/_tutorial_menu", []); ?>
        <li class="divider"></li>
        <li>
            <a href="http://manual.limesurvey.org/" target="_blank">
                <span class="fa fa-question-circle" ></span>
                <?php eT('LimeSurvey Manual');?>
                <i class="fa fa-external-link  pull-right"></i>
            </a>
        </li>
        <li>
            <a href="https://forums.limesurvey.org" target="_blank">
                <span class="fa-stack halfed">
                    <span class="fa fa-comment fa-stack-1x" ></span>
                    <span class="fa fa-group fa-inverse fa-stack-1x halfed" ></span>
                </span>
                <?php eT('LimeSurvey Forums');?>
                <i class="fa fa-external-link  pull-right"></i>
            </a>
        </li>
        <li class="divider"></li>
        <li>
            <a href="https://bugs.limesurvey.org/" target="_blank">
                <span class="fa fa-bug" ></span>
                <?php eT('Report bugs');?>
                <i class="fa fa-external-link  pull-right"></i>
            </a>
        </li>
        <li>
            <a href="https://limesurvey.org/" target="_blank">
                <span class="fa fa-star" ></span>
                <?php eT('LimeSurvey Homepage');?>
                <i class="fa fa-external-link  pull-right"></i>
            </a>
        </li>
    </ul>
</li>
