
<li class="nav-item dropdown">
    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" id="helpDropdown" aria-expanded="false" role="button">
        <span class="fa fa-question-circle" ></span>
        <?php eT('Help');?>
    </a>
    <ul class="dropdown-menu larger-dropdown" aria-labelledby="helpDropdown">
        <?php $this->renderPartial( "/admin/super/_tutorial_menu", []); ?>
        <li class="dropdown-divider"></li>
        <li>
            <a href="http://manual.limesurvey.org/" target="_blank" class="dropdown-item">
                <span class="fa fa-question-circle" ></span>
                <?php eT('LimeSurvey Manual');?>
                <i class="fa fa-external-link  float-end"></i>
            </a>
        </li>
        <li>
            <a href="https://forums.limesurvey.org" target="_blank" class="dropdown-item">
                <span class="fa-stack halfed">
                    <span class="fa fa-comment fa-stack-1x" ></span>
                    <span class="fa fa-group fa-inverse fa-stack-1x halfed" ></span>
                </span>
                <?php eT('LimeSurvey Forums');?>
                <i class="fa fa-external-link  float-end"></i>
            </a>
        </li>
        <li class="dropdown-divider"></li>
        <li>
            <a href="https://bugs.limesurvey.org/" target="_blank" class="dropdown-item">
                <span class="fa fa-bug" ></span>
                <?php eT('Report bugs');?>
                <i class="fa fa-external-link  float-end"></i>
            </a>
        </li>
        <li>
            <a href="https://limesurvey.org/" target="_blank" class="dropdown-item">
                <span class="fa fa-star" ></span>
                <?php eT('LimeSurvey Homepage');?>
                <i class="fa fa-external-link  float-end"></i>
            </a>
        </li>
    </ul>
</li>
