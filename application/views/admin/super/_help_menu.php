
<li class="nav-item dropdown">
    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" id="helpDropdown" aria-expanded="false" role="button">
        <!-- <i class="ri-question-fill"></i> -->
        <?php eT('Help');?>
    </a>
    <ul class="dropdown-menu larger-dropdown" aria-labelledby="helpDropdown">
        <?php $this->renderPartial( "/admin/super/_tutorial_menu", []); ?>
        <li class="dropdown-divider"></li>
        <li>
            <a href="http://manual.gitit-tech.com/" target="_blank" class="dropdown-item">
                <!-- <i class="ri-question-fill"></i> -->
                <?php eT('GititSurvey Manual');?>
                <i class=" ri-external-link-fill  float-end"></i>
            </a>
        </li>
        <li>
            <a href="https://forums.gitit-tech.com" target="_blank" class="dropdown-item">
                <span class="fa-stack halfed">
                    <span class="ri-chat-3-fill fa-stack-1x" ></span>
                    <span class="ri-group-fill fa-inverse fa-stack-1x halfed" ></span>
                </span>
                <?php eT('GititSurvey Forums');?>
                <i class=" ri-external-link-fill  float-end"></i>
            </a>
        </li>
        <li class="dropdown-divider"></li>
        <li>
            <a href="https://bugs.gitit-tech.com/" target="_blank" class="dropdown-item">
                <span class="ri-bug-fill" ></span>
                <?php eT('Report bugs');?>
                <i class=" ri-external-link-fill  float-end"></i>
            </a>
        </li>
        <li>
            <a href="https://gitit-tech.com/" target="_blank" class="dropdown-item">
                <span class="ri-star-fill" ></span>
                <?php eT('GititSurvey Homepage');?>
                <i class=" ri-external-link-fill  float-end"></i>
            </a>
        </li>
    </ul>
</li>
